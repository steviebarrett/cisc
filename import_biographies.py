#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Import DOCX biographies into the `composer` and `informant` tables.
------------
- Recursively scans:
    - composers DOCX root
    - informants DOCX root
- Extracts:
    - biography_text
    - biography_html
- Updates:
    - composer.biography_text / composer.biography_html
    - informant.biography_text / informant.biography_html

DB update behaviour
-------------------
- default: fill only blanks (won't overwrite existing biography_text/html)
- --overwrite: always replace

Usage
-----
python3 import_biographies.py \
  --composer-root "/path/to/docs/Bios/composers" \
  --informant-root "/path/to/docs/Bios/informants" \
  --db "cisc" \
  --user "USER" \
  --password "PASS"

Optional:
  --dry-run
  --overwrite
  --composer-id-regex "..."
  --informant-id-regex "..."

Deps
----
pip install python-docx pymysql
"""

import argparse
import html
import re
import sys
from dataclasses import dataclass
from pathlib import Path
from typing import Optional, Tuple, List

import pymysql
from docx import Document
from docx.oxml.ns import qn


# Broad defaults; adjust if your filename patterns are stricter.
DEFAULT_COMPOSER_ID_REGEX = r"([A-Z][A-Z0-9_-]{2,})"
DEFAULT_INFORMANT_ID_REGEX = r"([A-Z][A-Z0-9_-]{2,})"


@dataclass
class Extracted:
    entity_id: str
    text: str
    html: str
    source_path: str


def extract_entity_id(filename: str, id_regex: str) -> Optional[str]:
    """
    Find an entity ID anywhere in filename using the provided regex.
    Returns canonical uppercase for DB matching.
    """
    m = re.search(id_regex, filename, flags=re.IGNORECASE)
    return m.group(1).upper() if m else None


def paragraph_to_html_lines(p) -> List[str]:
    """
    Return a list of HTML-safe 'lines' from a DOCX paragraph, splitting on Word line breaks.
    Preserves <strong>, <em>, <u>, and escapes everything else.
    """
    lines: List[str] = [""]

    for r in p.runs:
        for child in r._r:
            if child.tag in (qn("w:br"), qn("w:cr")):
                lines.append("")
                continue

            if child.tag == qn("w:t"):
                t = child.text or ""
                if not t:
                    continue

                esc = html.escape(t, quote=False)

                if r.bold:
                    esc = f"<strong>{esc}</strong>"
                if r.italic:
                    esc = f"<em>{esc}</em>"
                if r.underline:
                    esc = f"<u>{esc}</u>"

                lines[-1] += esc

    if len(lines) == 1 and lines[0] == "":
        raw = p.text or ""
        if raw:
            return [html.escape(raw, quote=False)]
        return [""]

    return lines


def docx_to_text_and_html(docx_path: Path) -> Tuple[str, str]:
    """
    Produce:
      - plain text: blocks separated by blank lines
      - sanitised html: blocks as <p> with <br> within block
        + blank lines as <p><br></p>
    """
    doc = Document(str(docx_path))

    plain_blocks: List[str] = []
    html_blocks: List[str] = []

    current_plain_lines: List[str] = []
    current_html_lines: List[str] = []

    def flush_block():
        nonlocal current_plain_lines, current_html_lines
        if current_plain_lines or current_html_lines:
            plain_blocks.append("\n".join(current_plain_lines).rstrip())
            html_inner = "<br>".join(current_html_lines)
            html_blocks.append(f"<p>{html_inner}</p>")
            current_plain_lines = []
            current_html_lines = []

    for p in doc.paragraphs:
        raw = p.text or ""

        if raw.strip() == "":
            flush_block()
            html_blocks.append("<p><br></p>")
            plain_blocks.append("")
            continue

        html_lines = paragraph_to_html_lines(p)
        raw_lines = raw.split("\n") if "\n" in raw else [raw]

        for idx, hline in enumerate(html_lines):
            pline = raw_lines[idx] if idx < len(raw_lines) else ""
            if (pline.strip() == "") and (hline.strip() == ""):
                flush_block()
                html_blocks.append("<p><br></p>")
                plain_blocks.append("")
            else:
                current_plain_lines.append(pline)
                current_html_lines.append(hline)

    flush_block()

    text_out = "\n\n".join([b for b in plain_blocks if b is not None]).strip()
    html_out = "\n".join([b for b in html_blocks if b is not None]).strip()

    return text_out, html_out


def connect_mysql(host: str, port: int, user: str, password: str, db: str):
    return pymysql.connect(
        host=host,
        port=port,
        user=user,
        password=password,
        database=db,
        charset="utf8mb4",
        autocommit=False,
        cursorclass=pymysql.cursors.DictCursor,
    )


def ensure_entity_exists(cur, table: str, id_col: str, entity_id: str) -> bool:
    sql = f"SELECT 1 FROM {table} WHERE {id_col}=%s LIMIT 1"
    cur.execute(sql, (entity_id,))
    return cur.fetchone() is not None


def update_entity(cur, table: str, id_col: str, ex: Extracted, overwrite: bool):
    if overwrite:
        sql = f"""
            UPDATE {table}
            SET biography_text=%s,
                biography_html=%s
            WHERE {id_col}=%s
        """
        cur.execute(sql, (ex.text, ex.html, ex.entity_id))
    else:
        sql = f"""
            UPDATE {table}
            SET biography_text = CASE
                    WHEN biography_text IS NULL OR biography_text = '' THEN %s
                    ELSE biography_text
                END,
                biography_html = CASE
                    WHEN biography_html IS NULL OR biography_html = '' THEN %s
                    ELSE biography_html
                END
            WHERE {id_col}=%s
        """
        cur.execute(sql, (ex.text, ex.html, ex.entity_id))


def iter_docx_files(root: Path):
    for p in root.rglob("*.docx"):
        if p.name.startswith("~$"):
            continue
        yield p


def process_folder(
    conn,
    root: Path,
    table: str,
    id_col: str,
    id_regex: str,
    overwrite: bool,
    dry_run: bool,
    commit_every: int,
):
    docx_files = list(iter_docx_files(root))

    summary = {
        "root": str(root),
        "table": table,
        "files_found": len(docx_files),
        "parsed": 0,
        "updated": 0,
        "would_update": 0,
        "skipped_empty": 0,
        "no_id": [],
        "not_in_db": [],
    }

    if not docx_files:
        return summary

    with conn.cursor() as cur:
        for path in docx_files:
            entity_id = extract_entity_id(path.name, id_regex)
            if not entity_id:
                summary["no_id"].append(str(path))
                continue

            text, html_out = docx_to_text_and_html(path)
            summary["parsed"] += 1

            if not text.strip():
                summary["skipped_empty"] += 1
                continue

            if not ensure_entity_exists(cur, table, id_col, entity_id):
                summary["not_in_db"].append((entity_id, str(path)))
                continue

            ex = Extracted(
                entity_id=entity_id,
                text=text,
                html=html_out,
                source_path=str(path),
            )

            if dry_run:
                print(f"[DRY] would update {table}.{entity_id} from {path}")
                summary["would_update"] += 1
            else:
                update_entity(cur, table, id_col, ex, overwrite=overwrite)
                summary["updated"] += cur.rowcount

                if summary["updated"] and (summary["updated"] % commit_every == 0):
                    conn.commit()

    return summary


def print_summary(summary):
    print("\n=== Import summary ===")
    print(f"Table: {summary['table']}")
    print(f"Root: {summary['root']}")
    print(f"DOCX files found: {summary['files_found']}")
    print(f"DOCX parsed (had ID): {summary['parsed']}")
    print(f"Rows updated: {summary['updated']}")
    print(f"Rows that WOULD update: {summary['would_update']}")
    print(f"Skipped (empty biography): {summary['skipped_empty']}")
    print(f"Files with no ID match: {len(summary['no_id'])}")
    print(f"IDs not found in DB: {len(summary['not_in_db'])}")

    if summary["no_id"]:
        print("\nFirst 20 files with no ID match:")
        for p in summary["no_id"][:20]:
            print("  -", p)

    if summary["not_in_db"]:
        print("\nFirst 20 IDs not found in DB:")
        for entity_id, p in summary["not_in_db"][:20]:
            print(f"  - {entity_id}: {p}")


def main():
    ap = argparse.ArgumentParser(description="Import DOCX biographies into composer and informant tables.")

    ap.add_argument("--composer-root", required=True, help="Root composers biography DOCX directory.")
    ap.add_argument("--informant-root", required=True, help="Root informants biography DOCX directory.")

    ap.add_argument(
        "--composer-id-regex",
        default=DEFAULT_COMPOSER_ID_REGEX,
        help=f"Regex to extract composer_id from filename (default: {DEFAULT_COMPOSER_ID_REGEX})",
    )
    ap.add_argument(
        "--informant-id-regex",
        default=DEFAULT_INFORMANT_ID_REGEX,
        help=f"Regex to extract informant_id from filename (default: {DEFAULT_INFORMANT_ID_REGEX})",
    )

    ap.add_argument("--host", default="127.0.0.1")
    ap.add_argument("--port", type=int, default=3306)
    ap.add_argument("--db", required=True)
    ap.add_argument("--user", required=True)
    ap.add_argument("--password", required=True)

    ap.add_argument(
        "--overwrite",
        action="store_true",
        help="Overwrite existing biography_text/html (default fills only blanks).",
    )
    ap.add_argument(
        "--dry-run",
        action="store_true",
        help="Parse files and report actions, but do not update DB.",
    )
    ap.add_argument("--commit-every", type=int, default=50, help="Commit every N updated rows.")
    args = ap.parse_args()

    composer_root = Path(args.composer_root).expanduser().resolve()
    informant_root = Path(args.informant_root).expanduser().resolve()

    for label, root in [("composer-root", composer_root), ("informant-root", informant_root)]:
        if not root.exists() or not root.is_dir():
            print(f"ERROR: --{label} is not a directory: {root}", file=sys.stderr)
            sys.exit(2)

    conn = connect_mysql(args.host, args.port, args.user, args.password, args.db)
    try:
        composer_summary = process_folder(
            conn=conn,
            root=composer_root,
            table="composer",
            id_col="composer_id",
            id_regex=args.composer_id_regex,
            overwrite=args.overwrite,
            dry_run=args.dry_run,
            commit_every=args.commit_every,
        )

        informant_summary = process_folder(
            conn=conn,
            root=informant_root,
            table="informant",
            id_col="informant_id",
            id_regex=args.informant_id_regex,
            overwrite=args.overwrite,
            dry_run=args.dry_run,
            commit_every=args.commit_every,
        )

        if args.dry_run:
            conn.rollback()
        else:
            conn.commit()

    finally:
        conn.close()

    print_summary(composer_summary)
    print_summary(informant_summary)


if __name__ == "__main__":
    main()