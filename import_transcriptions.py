#!/usr/bin/env python3
# -*- coding: utf-8 -*-


# usage: python3 import_transcriptions.py --root "/path/to/Transcriptions" --db "cisc" --user "YOURUSER" --password "YOURPASS"
# ... and add   --dry-run   at the end if desired


import argparse
import html
import os
import re
import sys
from dataclasses import dataclass
from pathlib import Path
from typing import Optional, Tuple, List

import pymysql
from docx import Document


DEFAULT_ID_REGEX = r"(GF\d{3}i\d{2})"  # matches GF207i08 anywhere in the filename


@dataclass
class Extracted:
    recording_id: str
    text: str
    html: str
    source_path: str


def extract_recording_id(filename: str, id_regex: str) -> Optional[str]:
    m = re.search(id_regex, filename, flags=re.IGNORECASE)
    if not m:
        return None
    # Normalize to canonical case (your IDs appear uppercase in DB)
    return m.group(1).upper()


def docx_to_text_and_html(docx_path: Path) -> Tuple[str, str]:
    """
    Produce:
      - plain text: paragraphs joined by '\n'
      - sanitised html: only <p>, <br>, <strong>, <em>, <u> tags, with text escaped.
    """
    doc = Document(str(docx_path))

    plain_paras: List[str] = []
    html_paras: List[str] = []

    for p in doc.paragraphs:
        # Skip completely empty paragraphs (but keep intentional blank lines in plain text modestly)
        raw = p.text or ""
        if raw.strip() == "":
            # You can choose to keep blank paragraph markers if you want.
            continue

        plain_paras.append(raw)

        # Build sanitised HTML from runs, preserving bold/italic/underline.
        # We escape text, and we only emit tags we explicitly choose.
        run_bits: List[str] = []
        for r in p.runs:
            t = r.text or ""
            if not t:
                continue

            esc = html.escape(t, quote=False)

            # Preserve basic emphasis as tags (nesting kept simple)
            if r.bold:
                esc = f"<strong>{esc}</strong>"
            if r.italic:
                esc = f"<em>{esc}</em>"
            if r.underline:
                esc = f"<u>{esc}</u>"

            # Word sometimes includes soft line breaks; represent them as <br>
            esc = esc.replace("\n", "<br>")
            run_bits.append(esc)

        # If runs gave us nothing (rare), fall back to escaped paragraph text
        inner = "".join(run_bits) if run_bits else html.escape(raw, quote=False)
        html_paras.append(f"<p>{inner}</p>")

    text_out = "\n".join(plain_paras).strip()
    html_out = "\n".join(html_paras).strip()
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


def ensure_recording_exists(cur, recording_id: str) -> bool:
    cur.execute("SELECT 1 FROM recording WHERE recording_id=%s LIMIT 1", (recording_id,))
    return cur.fetchone() is not None


def update_recording(cur, ex: Extracted, overwrite: bool):
    """
    By default (overwrite=False):
      - only fills transcription_text/html if currently NULL/empty
    If overwrite=True:
      - always replaces content
    """
    if overwrite:
        cur.execute(
            """
            UPDATE recording
            SET transcription_text=%s,
                transcription_html=%s
            WHERE recording_id=%s
            """,
            (ex.text, ex.html, ex.recording_id),
        )
    else:
        cur.execute(
            """
            UPDATE recording
            SET transcription_text = CASE
                    WHEN transcription_text IS NULL OR transcription_text = '' THEN %s
                    ELSE transcription_text
                END,
                transcription_html = CASE
                    WHEN transcription_html IS NULL OR transcription_html = '' THEN %s
                    ELSE transcription_html
                END
            WHERE recording_id=%s
            """,
            (ex.text, ex.html, ex.recording_id),
        )


def iter_docx_files(root: Path):
    # recursive search
    for p in root.rglob("*.docx"):
        # ignore temp files like "~$something.docx"
        if p.name.startswith("~$"):
            continue
        yield p


def main():
    ap = argparse.ArgumentParser(description="Import DOCX transcriptions into recording table.")
    ap.add_argument("--root", required=True, help="Root Transcriptions directory (recursive).")
    ap.add_argument("--id-regex", default=DEFAULT_ID_REGEX, help=f"Regex to extract recording_id (default: {DEFAULT_ID_REGEX})")

    ap.add_argument("--host", default="127.0.0.1")
    ap.add_argument("--port", type=int, default=3306)
    ap.add_argument("--db", required=True)
    ap.add_argument("--user", required=True)
    ap.add_argument("--password", required=True)

    ap.add_argument("--overwrite", action="store_true", help="Overwrite existing transcription_text/html (default fills only blanks).")
    ap.add_argument("--dry-run", action="store_true", help="Parse files and report actions, but do not update DB.")
    ap.add_argument("--commit-every", type=int, default=50, help="Commit every N updated rows.")
    args = ap.parse_args()

    root = Path(args.root).expanduser().resolve()
    if not root.exists() or not root.is_dir():
        print(f"ERROR: --root is not a directory: {root}", file=sys.stderr)
        sys.exit(2)

    docx_files = list(iter_docx_files(root))
    if not docx_files:
        print(f"No .docx files found under: {root}")
        return

    no_id = []
    not_in_db = []
    parsed = 0
    updated = 0
    skipped_empty = 0

    conn = connect_mysql(args.host, args.port, args.user, args.password, args.db)
    try:
        with conn.cursor() as cur:
            for i, path in enumerate(docx_files, start=1):
                rid = extract_recording_id(path.name, args.id_regex)
                if not rid:
                    no_id.append(str(path))
                    continue

                text, html_out = docx_to_text_and_html(path)
                parsed += 1

                if not text.strip():
                    skipped_empty += 1
                    continue

                if not ensure_recording_exists(cur, rid):
                    not_in_db.append((rid, str(path)))
                    continue

                ex = Extracted(
                    recording_id=rid,
                    text=text,
                    html=html_out,
                    source_path=str(path),
                )

                if args.dry_run:
                    print(f"[DRY] would update {rid} from {path}")
                else:
                    update_recording(cur, ex, overwrite=args.overwrite)
                    updated += cur.rowcount  # should be 1

                    if updated % args.commit_every == 0:
                        conn.commit()

            if args.dry_run:
                conn.rollback()
            else:
                conn.commit()

    finally:
        conn.close()

    print("\n=== Import summary ===")
    print(f"Root: {root}")
    print(f"DOCX files found: {len(docx_files)}")
    print(f"DOCX parsed (had recording_id): {parsed}")
    print(f"Rows updated: {updated} {'(dry-run)' if args.dry_run else ''}")
    print(f"Skipped (empty transcription): {skipped_empty}")
    print(f"Files with no recording_id in filename: {len(no_id)}")
    print(f"recording_id not found in DB: {len(not_in_db)}")

    if no_id:
        print("\nFirst 20 files with no ID match:")
        for p in no_id[:20]:
            print("  -", p)

    if not_in_db:
        print("\nFirst 20 IDs not found in DB:")
        for rid, p in not_in_db[:20]:
            print(f"  - {rid}: {p}")


if __name__ == "__main__":
    main()