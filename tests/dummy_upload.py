"""
tests/dummy_upload.py
======================
Script untuk menguji koneksi ke API Laravel tanpa perlu kereta fisik.
Membuat failure records palsu lalu menguploadnya ke endpoint /api/failures.

Jalankan dari root project:
    python tests/dummy_upload.py
    python tests/dummy_upload.py --count 10
    python tests/dummy_upload.py --rake-id 3 --count 5
"""

import argparse
import json
import os
import sys
import urllib.request
import urllib.error
from datetime import datetime, timedelta
import random

# Pastikan root project ada di path
sys.path.insert(0, os.path.dirname(os.path.dirname(__file__)))

from config.settings import config


# ── Sample data untuk variasi record ──────────────────────────────
SAMPLE_FAULTS = [
    # (equipment_code, equipment_name, fault_code, fault_name, notch)
    (9,  "PA",    806,  "DATASA",  "EB"),
    (9,  "PA",    801,  "PAAT",    "EB"),
    (2,  "ATO",   200,  "ATOAT",   "B4"),
    (2,  "ATO",   211,  "LBVRS1F", "N"),
    (2,  "ATO",   212,  "LBVRS2F", "P2"),
    (10, "DOOR",  905,  "DLE",     "EB"),
    (10, "DOOR",  907,  "CLE",     "EB"),
    (1,  "TIS",   121,  "ESA",     "B2"),
    (6,  "BECU",  500,  "NBPS",    "EB"),
    (20, "CCTV",  1203, "WCE",     "P1"),
    (19, "Radio", 1103, "CMA",     "B6"),
    (3,  "VVVF1", 300,  "NVCPS",   "EB"),
    (8,  "PID",   700,  "NPICPS",  "EB"),
]


def make_record(block_no, base_time):
    """Buat satu failure record palsu."""
    eq_code, eq_name, fault_code, fault_name, notch = random.choice(SAMPLE_FAULTS)
    ts = base_time - timedelta(minutes=block_no * 2 + random.randint(0, 1))
    return {
        "block_no":       block_no,
        "timestamp":      ts.strftime("%Y-%m-%dT%H:%M:%S"),
        "car_no":         random.randint(1, 6),
        "occur_recover":  random.choice([0, 0, 0, 1]),  # lebih banyak occur
        "train_id":       "FFFF",
        "location_m":     0,
        "equipment_code": eq_code,
        "equipment_name": eq_name,
        "fault_code":     fault_code,
        "fault_name":     fault_name,
        "notch":          notch,
        "speed_kmh":      0,
        "overhead_v":     random.choice([0, 10, 20, 80]),
    }


def upload(rake_id, count):
    """Upload dummy records ke API."""
    base_time = datetime.now()
    records = [make_record(i, base_time) for i in range(count)]

    payload = {
        "rake_id":      rake_id,
        "read_time":    base_time.strftime("%Y-%m-%dT%H:%M:%S"),
        "record_count": len(records),
        "records":      records,
    }

    url     = config.cloud.api_base_url.rstrip("/") + config.cloud.endpoint_failures
    api_key = config.cloud.api_key
    body    = json.dumps(payload).encode("utf-8")
    headers = {
        "Content-Type":  "application/json",
        "Authorization": "Bearer " + api_key,
        "User-Agent":    "TIS-Gateway/1.0-dummy",
    }

    print("\n" + "=" * 55)
    print("  URL     : " + url)
    print("  rake_id : " + str(rake_id))
    print("  records : " + str(count))
    print("  api_key : " + api_key)
    print("=" * 55)

    # Tampilkan preview 2 record pertama
    print("\nPreview records:")
    for r in records[:2]:
        print("  blk=%3d ts=%s  car=%d  %s  %s  notch=%s" % (
            r["block_no"], r["timestamp"], r["car_no"],
            r["equipment_name"], r["fault_name"], r["notch"],
        ))
    if count > 2:
        print("  ... (+%d records lainnya)" % (count - 2))

    print("\nMengirim ke %s ..." % url)

    try:
        req = urllib.request.Request(url, data=body, headers=headers, method="POST")
        with urllib.request.urlopen(req, timeout=10) as resp:
            result = json.loads(resp.read())
            print("\n[OK] Berhasil! HTTP %d" % resp.status)
            print("  session_id : %s" % result.get("session_id", "-"))
            print("  received   : %s" % result.get("received", "-"))
            print("  status     : %s" % result.get("status", "-"))
            return True

    except urllib.error.HTTPError as e:
        body_err = e.read().decode("utf-8", errors="replace")
        print("\n[FAIL] HTTP Error %d: %s" % (e.code, e.reason))
        try:
            err_json = json.loads(body_err)
            print("  " + json.dumps(err_json, indent=2)[:500])
        except Exception:
            print("  " + body_err[:500])
    except urllib.error.URLError as e:
        print("\n[FAIL] Tidak bisa konek ke %s" % url)
        print("  %s" % e.reason)
        print("\n  -> Pastikan Laravel berjalan:")
        print("     cd tis_api_laravel && php artisan serve")
    except Exception as e:
        print("\n[FAIL] Error: %s" % e)

    return False


def main():
    parser = argparse.ArgumentParser(description="Dummy upload ke TIS API")
    parser.add_argument("--rake-id", type=int, default=5,
                        help="Rake ID (default: 5)")
    parser.add_argument("--count", type=int, default=10,
                        help="Jumlah records (default: 10)")
    args = parser.parse_args()

    ok = upload(args.rake_id, args.count)
    sys.exit(0 if ok else 1)


if __name__ == "__main__":
    main()
