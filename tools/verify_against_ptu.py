"""
tools/verify_against_ptu.py
============================
Replay raw bytes dari log debug, decode dengan parser baru,
bandingkan dengan output PTU asli untuk timeframe overlap.

Output: report per-field match/mismatch + summary.
"""
import os
import re
import sys
import logging

# Set LOG_LEVEL sebelum import supaya logger pakai level rendah
os.environ["LOG_LEVEL"] = "ERROR"

sys.path.insert(0, os.path.dirname(os.path.dirname(__file__)))

from parsers.record_parser import RecordParser

# Force-disable semua logging setelah import
logging.disable(logging.CRITICAL)
for name in list(logging.Logger.manager.loggerDict.keys()):
    logging.getLogger(name).setLevel(logging.CRITICAL)
    logging.getLogger(name).propagate = False
    for h in logging.getLogger(name).handlers:
        h.setLevel(logging.CRITICAL)

LOG_PATH  = "logs/original_records.txt"   # 200 raw record extracted dari debug log asli
ORIG_CSV  = "output/D260605_366 (original PTU App).csv"
OUR_OFFSET = 4   # our_block - 4 = orig_block (4 record baru di top kita)


def load_orig_csv():
    rows = {}
    with open(ORIG_CSV, encoding="utf-8") as f:
        for line in f:
            parts = [p.strip() for p in line.split(",")]
            if len(parts) < 16 or not parts[0].isdigit():
                continue
            blk = int(parts[0])
            rows[blk] = {
                "ts":    f"{parts[1]}{parts[2]}{parts[3]}_{parts[4]}{parts[5]}{parts[6]}",
                "car":   int(parts[7]),
                "tid":   parts[8],
                "occ":   int(parts[9]),
                "loc":   int(parts[10]),
                "eq":    int(parts[11]),
                "fault": int(parts[12]),
                "notch": parts[13],
                "spd":   int(parts[14]),
                "ov":    int(parts[15]),
            }
    return rows


def replay_log_records():
    parser = RecordParser()
    records = []
    pat = re.compile(r"\[REC\] blk=(\d+) .*raw=([0-9a-f]{40})")
    with open(LOG_PATH, encoding="utf-8", errors="replace") as f:
        for line in f:
            m = pat.search(line)
            if not m:
                continue
            blk = int(m.group(1))
            raw = bytes.fromhex(m.group(2))
            # Bungkus jadi payload (StartMark + record + EndMark) supaya parse_payload jalan
            payload = b"\x00" + raw + b"\x03"
            recs = parser.parse_payload(payload, block_start=blk)
            if recs:
                records.append(recs[0])
    return records


def compare(our_records, orig_rows):
    """Match by content key (ts+car+tid+eq+fault) — robust terhadap duplicate session."""
    fields = ["occ", "loc", "notch", "spd", "ov"]   # ts/car/tid/eq/fault dipakai sebagai key
    mismatches = {f: 0 for f in fields}
    examples = {f: [] for f in fields}

    # Index orig by content key
    orig_index = {}
    for blk, row in orig_rows.items():
        key = (row["ts"], row["car"], row["tid"], row["eq"], row["fault"])
        orig_index[key] = (blk, row)

    matched = 0
    unmatched_ours = 0

    for rec in our_records:
        key = (
            rec.timestamp.strftime("%y%m%d_%H%M%S"),
            rec.car_no,
            rec.train_id_str,
            rec.equipment_code,
            rec.fault_code,
        )
        if key not in orig_index:
            unmatched_ours += 1
            continue
        orig_blk, orig = orig_index[key]
        matched += 1
        ours = {
            "occ":   rec.occur_recover,
            "loc":   rec.location_m,
            "notch": rec.notch_label,
            "spd":   rec.speed_kmh,
            "ov":    rec.overhead_v,
        }
        for f in fields:
            if ours[f] != orig[f]:
                mismatches[f] += 1
                if len(examples[f]) < 5:
                    examples[f].append(
                        f"  our_blk={rec.block_no} orig_blk={orig_blk} key={key}  "
                        f"ours={ours[f]!r}  orig={orig[f]!r}"
                    )

    print(f"\nMatched {matched} record pairs by content key.")
    print(f"Our records tanpa match di orig (likely newer): {unmatched_ours}")
    print(f"\n{'Field':<8} {'Mismatches':<12} {'%match':<8}")
    print("-" * 30)
    all_clean = True
    for f in fields:
        pct = 100.0 * (matched - mismatches[f]) / matched if matched else 0
        if mismatches[f]:
            all_clean = False
        print(f"{f:<8} {mismatches[f]:<12} {pct:.1f}%")
        for ex in examples[f]:
            print(ex)
    if all_clean:
        print("\n[OK] Semua field 100% match dengan original PTU output.")


if __name__ == "__main__":
    orig = load_orig_csv()
    print(f"Loaded {len(orig)} original PTU records.")
    ours = replay_log_records()
    print(f"Replayed {len(ours)} records dari log.")
    compare(ours, orig)
