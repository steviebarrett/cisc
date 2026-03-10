#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Import DOCX biographies into the `composer` and `informant` tables.

Behaviour
---------
For each composer/informant row in the DB:
1. Prefer the filename stored in biography_doc
2. Look for that file under the relevant root folder
3. If not found, fall back to matching files by ID regex
4. Extract biography_text + biography_html
5. Update the row

Updates
-------
- composer.biography_text / composer.biography_html
- informant.biography_text / informant.biography_html

Default write behaviour
-----------------------
- fills blanks only
- use --overwrite to replace existing values

Usage
-----
python3 import_biographies.py \
  --composer-root "/path/to/docs/Bios/composers" \
  --informant-root "/path/to/docs/Bios/informants" \
  --db "cisc" \
  --user "USER" \
  --password "PASS"

Optional
--------
  --dry-run
  --overwrite
  --composer-id-regex "..."
  --informant-id-regex "..."
  --no-fallback-by-id

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
from typing import Optional, Tuple, List, Dict

import pymysql
from docx import Document
from docx.oxml.ns import qn


DEFAULT_COMPOSER_ID_REGEX = r"([A-Z][A-Z0-9_-]{2,})"
DEFAULT_INFORMANT_ID_REGEX = r"([A-Z][A-Z0-9_-]{2,})"


@dataclass
class Extracted:
    entity_id: str
    text: str
    html: str
    source_path: str


def extract_entity_id(filename: str, id_regex: str) -> Optional[str]:
    m = re.search(id_regex, filename, flags=re.IGNORECASE)
    return m.group(1).upper() if m else None


def paragraph_to_html_lines(p) -> List[str]:
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


def normalise_filename(name: str) -> str:
    return Path(name).name.strip().lower()


def build_file_indexes(root: Path):
    files = list(iter_docx_files(root))

    by_basename: Dict[str, Path] = {}
    by_stem: Dict[str, Path] = {}
    all_files = files[:]

    for p in files:
        by_basename[p.name.lower()] = p
        by_stem[p.stem.lower()] = p

    return all_files, by_basename, by_stem


def resolve_doc_path(
    biography_doc: Optional[str],
    entity_id: str,
    all_files: List[Path],
    by_basename: Dict[str, Path],
    by_stem: Dict[str, Path],
    id_regex: str,
    fallback_by_id: bool,
) -> Tuple[Optional[Path], str]:
    """
    Resolution order:
    1. exact basename match against biography_doc
    2. stem match against biography_doc without extension
    3. fallback scan by entity_id in filename
    """
    if biography_doc:
        doc_name = biography_doc.strip()
        if doc_name:
            key = normalise_filename(doc_name)
            if key in by_basename:
                return by_basename[key], "biography_doc basename"

            stem_key = Path(doc_name).stem.strip().lower()
            if stem_key in by_stem:
                return by_stem[stem_key], "biography_doc stem"

    if fallback_by_id:
        matches = []
        for p in all_files:
            found_id = extract_entity_id(p.name, id_regex)
            if found_id == entity_id:
                matches.append(p)

        if len(matches) == 1:
            return matches[0], "fallback id match"

        if len(matches) > 1:
            matches.sort(key=lambda p: len(p.name))
            return matches[0], "fallback id match (multiple candidates, shortest name chosen)"

    return None, "not found"


def fetch_entities(cur, table: str, id_col: str):
    sql = f"""
        SELECT {id_col} AS entity_id,
               biography_doc,
               biography_text,
               biography_html
        FROM {table}
        ORDER BY {id_col}
    """
    cur.execute(sql)
    return cur.fetchall()


def process_table(
    conn,
    root: Path,
    table: str,
    id_col: str,
    id_regex: str,
    overwrite: bool,
    dry_run: bool,
    commit_every: int,
    fallback_by_id: bool,
):
    all_files, by_basename, by_stem = build_file_indexes(root)

    summary = {
        "root": str(root),
        "table": table,
        "files_found": len(all_files),
        "db_rows_seen": 0,
        "parsed": 0,
        "updated": 0,
        "would_update": 0,
        "skipped_empty": 0,
        "missing_doc": [],
        "resolved_by_fallback": [],
    }

    with conn.cursor() as cur:
        rows = fetch_entities(cur, table, id_col)

        for row in rows:
            entity_id = (row.get("entity_id") or "").strip()
            biography_doc = row.get("biography_doc")
            summary["db_rows_seen"] += 1

            if not entity_id:
                continue

            path, how = resolve_doc_path(
                biography_doc=biography_doc,
                entity_id=entity_id,
                all_files=all_files,
                by_basename=by_basename,
                by_stem=by_stem,
                id_regex=id_regex,
                fallback_by_id=fallback_by_id,
            )

            if path is None:
                summary["missing_doc"].append((entity_id, biography_doc))
                continue

            if how.startswith("fallback"):
                summary["resolved_by_fallback"].append((entity_id, str(path)))

            text, html_out = docx_to_text_and_html(path)
            summary["parsed"] += 1

            if not text.strip():
                summary["skipped_empty"] += 1
                continue

            ex = Extracted(
                entity_id=entity_id,
                text=text,
                html=html_out,
                source_path=str(path),
            )

            if dry_run:
                print(f"[DRY] would update {table}.{entity_id} from {path} ({how})")
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
    print(f"DB rows seen: {summary['db_rows_seen']}")
    print(f"DOCX parsed: {summary['parsed']}")
    print(f"Rows updated: {summary['updated']}")
    print(f"Rows that WOULD update: {summary['would_update']}")
    print(f"Skipped (empty biography): {summary['skipped_empty']}")
    print(f"Missing/unresolved docs: {len(summary['missing_doc'])}")
    print(f"Resolved by fallback ID matching: {len(summary['resolved_by_fallback'])}")

    if summary["missing_doc"]:
        print("\nFirst 20 missing/unresolved docs:")
        for entity_id, doc in summary["missing_doc"][:20]:
            print(f"  - {entity_id}: biography_doc={doc!r}")

    if summary["resolved_by_fallback"]:
        print("\nFirst 20 rows resolved by fallback ID matching:")
        for entity_id, path in summary["resolved_by_fallback"][:20]:
            print(f"  - {entity_id}: {path}")


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
    ap.add_argument(
        "--no-fallback-by-id",
        action="store_true",
        help="Disable fallback matching by ID when biography_doc does not resolve.",
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
        composer_summary = process_table(
            conn=conn,
            root=composer_root,
            table="composer",
            id_col="composer_id",
            id_regex=args.composer_id_regex,
            overwrite=args.overwrite,
            dry_run=args.dry_run,
            commit_every=args.commit_every,
            fallback_by_id=not args.no_fallback_by_id,
        )

        informant_summary = process_table(
            conn=conn,
            root=informant_root,
            table="informant",
            id_col="informant_id",
            id_regex=args.informant_id_regex,
            overwrite=args.overwrite,
            dry_run=args.dry_run,
            commit_every=args.commit_every,
            fallback_by_id=not args.no_fallback_by_id,
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