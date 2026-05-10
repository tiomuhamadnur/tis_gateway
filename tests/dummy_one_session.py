"""
tests/dummy_one_session.py
===========================
Kirim 1 sesi dummy: 1 trainset, 200 records tersebar di 3 hari berbeda.
Record dibuat berpasangan Occur(0) → Recover(1) agar pairing bekerja.

Jalankan:
    python tests/dummy_one_session.py
    python tests/dummy_one_session.py --rake-id 3
"""

import argparse
import json
import sys
import urllib.request
import urllib.error
from datetime import datetime, timedelta
import random

API_BASE = "http://127.0.0.1:8000"
API_KEY  = "tiomuhamadnur"
ENDPOINT = "/api/failures"

# Catalog fault: (equipment_code, equipment_name, fault_code, fault_name, notch)
FAULT_CATALOG = [
    # Heavy
    (1,  "TIS",   121, "ESA",      "EB"),
    (2,  "ATO",   200, "ATOAT",    "B4"),
    (2,  "ATO",   211, "LBVRS1F",  "N"),
    (2,  "ATO",   212, "LBVRS2F",  "P2"),
    (3,  "VVVF1", 300, "NVCPS",    "EB"),
    (4,  "VVVF2", 301, "NVCPS2",   "EB"),
    (5,  "APS",   400, "NAPCPS",   "EB"),
    (5,  "APS",   419, "IVFR",     "EB"),
    (6,  "BECU",  500, "NBPS",     "EB"),
    (6,  "BECU",  502, "BECUMAJ",  "EB"),
    (10, "DOOR",  905, "DLE",      "EB"),
    (10, "DOOR",  906, "OPE",      "EB"),
    # Light
    (7,  "ACE",   600, "NACCPS",   "EB"),
    (8,  "PID",   700, "NPICPS",   "EB"),
    (8,  "PID",   701, "PIDAT",    "EB"),
    (9,  "PA",    806, "DATASA",   "EB"),
    (9,  "PA",    801, "PAAT",     "EB"),
    (11, "VMI",  1000, "NVMCPS",   "EB"),
    (19, "Radio",1103, "CMA",      "B6"),
    (20, "CCTV", 1203, "WCE",      "P1"),
    (20, "CCTV", 1204, "CVE",      "N"),
]

# Distribusi record per hari: (fraksi, in_service)
# Hari 1 = 2 hari lalu, depo + dinas pagi
# Hari 2 = kemarin, dinas penuh
# Hari 3 = hari ini, depo pagi
DAY_SLOTS = [
    {"label": "2 hari lalu", "days_ago": 2, "hour_start": 6,  "hour_end": 22, "in_service": True,  "count": 70},
    {"label": "kemarin",     "days_ago": 1, "hour_start": 5,  "hour_end": 23, "in_service": True,  "count": 80},
    {"label": "hari ini",    "days_ago": 0, "hour_start": 5,  "hour_end": 16, "in_service": False, "count": 50},
]


def random_ts(days_ago: int, hour_start: int, hour_end: int) -> datetime:
    """Buat timestamp acak dalam rentang jam yang diberikan."""
    base = datetime.now().replace(hour=0, minute=0, second=0, microsecond=0)
    base -= timedelta(days=days_ago)
    total_minutes = (hour_end - hour_start) * 60
    offset = timedelta(hours=hour_start, minutes=random.randint(0, total_minutes))
    return base + offset


def make_fault_pair(block_no_occur, block_no_recover, slot, car_no, fault):
    """
    Buat pasangan Occur(0) + Recover(1) untuk satu fault event.
    Occur terjadi lebih dulu, Recover beberapa detik/menit kemudian.
    """
    eq_code, eq_name, fault_code, fault_name, notch = fault
    days_ago   = slot["days_ago"]
    h_start    = slot["hour_start"]
    h_end      = slot["hour_end"]
    in_service = slot["in_service"]

    ts_occur = random_ts(days_ago, h_start, h_end)

    # Durasi fault: 5 detik s/d 30 menit (lebih realistis)
    duration_sec = random.randint(5, 1800)
    ts_recover   = ts_occur + timedelta(seconds=duration_sec)

    if in_service:
        speed    = random.randint(10, 85)
        loc_m    = random.randint(100, 15000)
        train_id = "%04d" % random.randint(100, 1611)
        ov       = random.choice([1500, 1520, 1560, 1580, 1600, 1620])
        notch    = random.choice(["N", "B1", "B2", "B4", "P1", "P2", "P3"])
    else:
        speed    = 0
        loc_m    = 0
        train_id = "FFFF"
        ov       = random.choice([0, 10, 20])
        notch    = "EB"

    occur = {
        "block_no":       block_no_occur,
        "timestamp":      ts_occur.strftime("%Y-%m-%dT%H:%M:%S"),
        "car_no":         car_no,
        "occur_recover":  0,
        "train_id":       train_id,
        "location_m":     loc_m,
        "equipment_code": eq_code,
        "equipment_name": eq_name,
        "fault_code":     fault_code,
        "fault_name":     fault_name,
        "notch":          notch,
        "speed_kmh":      speed,
        "overhead_v":     ov,
    }

    recover = {
        "block_no":       block_no_recover,
        "timestamp":      ts_recover.strftime("%Y-%m-%dT%H:%M:%S"),
        "car_no":         car_no,
        "occur_recover":  1,
        "train_id":       train_id,
        "location_m":     loc_m + random.randint(-100, 100) if in_service else 0,
        "equipment_code": eq_code,
        "equipment_name": eq_name,
        "fault_code":     fault_code,
        "fault_name":     fault_name,
        "notch":          notch,
        "speed_kmh":      speed,
        "overhead_v":     ov,
    }

    return [occur, recover]


def make_occur_only(block_no: int, slot: dict, car_no: int, fault: tuple) -> dict:
    """Buat Occur tanpa Recover — fault masih aktif / belum resolved."""
    eq_code, eq_name, fault_code, fault_name, notch = fault
    ts = random_ts(slot["days_ago"], slot["hour_start"], slot["hour_end"])

    in_service = slot["in_service"]
    if in_service:
        speed    = random.randint(10, 85)
        loc_m    = random.randint(100, 15000)
        train_id = "%04d" % random.randint(100, 1611)
        ov       = random.choice([1500, 1560, 1600])
        notch    = random.choice(["N", "B2", "P1", "P3"])
    else:
        speed, loc_m, train_id, ov, notch = 0, 0, "FFFF", 10, "EB"

    return {
        "block_no":       block_no,
        "timestamp":      ts.strftime("%Y-%m-%dT%H:%M:%S"),
        "car_no":         car_no,
        "occur_recover":  0,
        "train_id":       train_id,
        "location_m":     loc_m,
        "equipment_code": eq_code,
        "equipment_name": eq_name,
        "fault_code":     fault_code,
        "fault_name":     fault_name,
        "notch":          notch,
        "speed_kmh":      speed,
        "overhead_v":     ov,
    }


def build_records(total):
    """
    Buat 200 records tersebar di 3 hari.
    ~70% paired (Occur+Recover), ~30% Occur saja (masih aktif).
    """
    records   = []
    block_no  = 0

    for slot in DAY_SLOTS:
        remaining = slot["count"]

        # Hitung berapa pasang dan berapa occur-only
        # Pasang butuh 2 slot, occur-only butuh 1 slot
        # Target: 70% dari slot ini = paired, sisanya occur-only
        n_pairs    = int(remaining * 0.70) // 2   # tiap pair = 2 records
        n_singles  = remaining - (n_pairs * 2)

        for _ in range(n_pairs):
            fault  = random.choice(FAULT_CATALOG)
            car_no = random.randint(1, 6)
            pair   = make_fault_pair(block_no, block_no + 1, slot, car_no, fault)
            records.extend(pair)
            block_no += 2

        for _ in range(n_singles):
            fault  = random.choice(FAULT_CATALOG)
            car_no = random.randint(1, 6)
            records.append(make_occur_only(block_no, slot, car_no, fault))
            block_no += 1

    # Pastikan tepat 200 (trim atau tambah jika perlu)
    random.shuffle(records)
    for i, r in enumerate(records):
        r["block_no"] = i

    return records[:total]


def post_session(rake_id: int) -> bool:
    records   = build_records(200)
    read_time = datetime.now()

    payload = {
        "rake_id":      rake_id,
        "read_time":    read_time.strftime("%Y-%m-%dT%H:%M:%S"),
        "record_count": len(records),
        "records":      records,
    }

    url     = API_BASE + ENDPOINT
    body    = json.dumps(payload).encode("utf-8")
    headers = {
        "Content-Type":  "application/json",
        "Authorization": "Bearer " + API_KEY,
        "User-Agent":    "TIS-Gateway/1.0-dummy",
    }

    # Hitung statistik sebelum kirim
    pairs   = sum(1 for r in records if r["occur_recover"] == 0)
    singles = sum(1 for r in records if r["occur_recover"] == 1)
    days    = sorted(set(r["timestamp"][:10] for r in records))

    print("=" * 65)
    print("  TIS Gateway — Dummy One Session")
    print(f"  Target   : {url}")
    print(f"  rake_id  : TS-{rake_id:02d}")
    print(f"  Records  : {len(records)} total "
          f"({pairs} Occur, {singles} Recover)")
    print(f"  Tanggal  : {' | '.join(days)}")
    print("=" * 65)

    try:
        req = urllib.request.Request(url, data=body, headers=headers, method="POST")
        with urllib.request.urlopen(req, timeout=30) as resp:
            result = json.loads(resp.read())
            print(f"\n  [OK] HTTP {resp.status}")
            print(f"  session_id : {result.get('session_id', '-')}")
            print(f"  received   : {result.get('received', '-')}")
            print(f"  status     : {result.get('status', '-')}")
            print("=" * 65)
            return True

    except urllib.error.HTTPError as e:
        body_err = e.read().decode("utf-8", errors="replace")
        print(f"\n  [FAIL] HTTP {e.code}: {e.reason}")
        try:
            err = json.loads(body_err)
            print("  " + json.dumps(err, ensure_ascii=False, indent=2)[:500])
        except Exception:
            print("  " + body_err[:500])

    except urllib.error.URLError as e:
        print(f"\n  [FAIL] Tidak bisa konek ke {API_BASE}")
        print(f"  {e.reason}")
        print("\n  -> Jalankan Laravel dulu:")
        print("     cd tis_api_laravel && php artisan serve")

    except Exception as e:
        print(f"\n  [FAIL] {e}")

    return False


def main():
    parser = argparse.ArgumentParser(description="Dummy 1 sesi ke TIS API")
    parser.add_argument("--rake-id", type=int, default=5,
                        help="Rake ID trainset (default: 5)")
    args = parser.parse_args()

    ok = post_session(args.rake_id)
    sys.exit(0 if ok else 1)


if __name__ == "__main__":
    main()
