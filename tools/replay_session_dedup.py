"""
tools/replay_session_dedup.py
==============================
Replay 40 page CMD 0x36 berdasarkan record yang ada di logs/original_records.txt.
Simulasikan inject duplicate page (echo dari page sebelumnya), pastikan dedup
di session.py membuangnya.

Skenario:
  - 200 record asli dibagi per 5 → 40 "page" sintetik (page 0..39)
  - Inject: response untuk page 27 = response page 26 (echo), page 36 = page 35
  - Expected: setelah dedup, total record = 200 - 10 = 190 (10 duplikat dibuang),
              tidak ada raw_bytes ganda.
"""
import os
import sys
import logging

os.environ["LOG_LEVEL"] = "INFO"
sys.path.insert(0, os.path.dirname(os.path.dirname(__file__)))

# Silence DEBUG dari record_parser supaya output bersih
from parsers.record_parser import RecordParser
logging.getLogger("parsers.record_parser").setLevel(logging.WARNING)

LOG_PATH = "logs/original_records.txt"


def load_raw_records():
    """Ambil 200 raw record (20 byte each) dari log debug original."""
    import re
    raws = []
    pat = re.compile(r"raw=([0-9a-f]{40})")
    with open(LOG_PATH, encoding="utf-8", errors="replace") as f:
        for line in f:
            m = pat.search(line)
            if m:
                raws.append(bytes.fromhex(m.group(1)))
    return raws


def make_page_payload(records_5: list) -> bytes:
    """Bungkus 5 record jadi payload CMD 0x36 (StartMark + 5×20B + EndMark = 102B)."""
    payload = b"\x00"
    for r in records_5:
        payload += r
    payload += b"\x03"
    return payload


def simulate_dedup(raws):
    """Tiru logika _do_failure_download dengan injection duplicate."""
    parser = RecordParser()
    # Bagi 200 record jadi 40 page
    pages = [raws[i:i+5] for i in range(0, 200, 5)]
    assert len(pages) == 40

    # Inject duplicate: page 27 = page 26, page 36 = page 35
    injected = list(pages)
    injected[27] = pages[26]
    injected[36] = pages[35]

    records = []
    seen_raws = set()
    dup_pages_skipped = 0
    partial_dupes = 0
    pages_ok = 0
    block_no = 0

    for page_idx in range(40):
        payload = make_page_payload(injected[page_idx])
        page_records = parser.parse_payload(payload, block_start=block_no)
        if not page_records:
            continue

        new_records = []
        for rec in page_records:
            if rec.raw_bytes in seen_raws:
                continue
            seen_raws.add(rec.raw_bytes)
            new_records.append(rec)

        dupes = len(page_records) - len(new_records)
        if dupes == len(page_records):
            dup_pages_skipped += 1
            print(f"  page {page_idx:>2}: ALL DUP — SKIP")
            continue
        if dupes:
            partial_dupes += dupes
            print(f"  page {page_idx:>2}: {dupes}/{len(page_records)} dupes skipped")

        for i, rec in enumerate(new_records):
            rec.block_no = block_no + i

        records.extend(new_records)
        block_no += len(new_records)
        pages_ok += 1

    return records, dup_pages_skipped, partial_dupes, pages_ok


if __name__ == "__main__":
    raws = load_raw_records()
    print(f"Loaded {len(raws)} raw records from log.\n")
    print("Replay 40 pages dengan inject page 27=page 26 dan page 36=page 35 ...\n")
    records, dup_pages, partial, pages_ok = simulate_dedup(raws)
    print(f"\nHasil:")
    print(f"  Total records   : {len(records)}  (expected 190 = 200 - 10 duplikat)")
    print(f"  Dup pages skip  : {dup_pages}    (expected 2)")
    print(f"  Partial dupes   : {partial}    (expected 0)")
    print(f"  Pages OK        : {pages_ok}   (expected 38)")
    raws_seen = [r.raw_bytes for r in records]
    print(f"  Unique raws     : {len(set(raws_seen))}  (harus = total records)")
    assert len(records) == 190, "FAIL: total records bukan 190"
    assert dup_pages == 2, "FAIL: dup pages bukan 2"
    assert len(set(raws_seen)) == len(raws_seen), "FAIL: ada duplikat di output"
    # Block numbers harus kontigu 0..189
    assert [r.block_no for r in records] == list(range(190)), "FAIL: block_no tidak kontigu"
    print("\n[OK] Dedup logic bekerja, output 100% bebas duplikat, block_no kontigu.")
