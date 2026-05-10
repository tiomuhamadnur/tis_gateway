"""
tests/dummy_sessions.py
========================
Kirim 15 dummy failure sessions ke Laravel API (http://127.0.0.1:8000).

Setiap sesi mewakili satu download dari satu trainset MRT Jakarta.
Data tersebar di 15 dari 16 trainset, dengan tanggal berbeda-beda.

Jalankan dari root project:
    python tests/dummy_sessions.py
"""

import json
import sys
import urllib.request
import urllib.error
from datetime import datetime, timedelta
import random

API_BASE    = "http://127.0.0.1:8000"
API_KEY     = "tiomuhamadnur"
ENDPOINT    = "/api/failures"

# ── Fault catalog dari TIS Maintenance Manual Sumitomo ────────────
# (equipment_code, equipment_name, fault_code, fault_name, classification, base_notch)
FAULT_CATALOG = [
    # Heavy — ATO
    (2,  "ATO",    200,  "ATOAT",    "Heavy", "B4"),
    (2,  "ATO",    211,  "LBVRS1F",  "Heavy", "N"),
    (2,  "ATO",    212,  "LBVRS2F",  "Heavy", "P2"),
    (2,  "ATO",    214,  "LBTISF",   "Heavy", "B6"),
    # Heavy — VVVF
    (3,  "VVVF1",  300,  "NVCPS",    "Heavy", "EB"),
    (4,  "VVVF2",  301,  "NVCPS2",   "Heavy", "EB"),
    # Heavy — BECU (brake)
    (6,  "BECU",   500,  "NBPS",     "Heavy", "EB"),
    (6,  "BECU",   502,  "BECUMAJ",  "Heavy", "EB"),
    (6,  "BECU",   513,  "BRKUNREL", "Heavy", "EB"),
    # Heavy — DOOR
    (10, "DOOR",   905,  "DLE",      "Heavy", "EB"),
    (10, "DOOR",   906,  "OPE",      "Heavy", "EB"),
    (10, "DOOR",   907,  "CLE",      "Heavy", "EB"),
    # Heavy — TIS
    (1,  "TIS",    121,  "ESA",      "Heavy", "B2"),
    (1,  "TIS",    127,  "CCUAT",    "Heavy", "EB"),
    # Heavy — APS
    (5,  "APS",    400,  "NAPCPS",   "Heavy", "EB"),
    (5,  "APS",    419,  "IVFR",     "Heavy", "EB"),
    # Light — PA
    (9,  "PA",     806,  "DATASA",   "Light", "EB"),
    (9,  "PA",     801,  "PAAT",     "Light", "EB"),
    (9,  "PA",     802,  "CFCA",     "Light", "EB"),
    # Light — PID
    (8,  "PID",    700,  "NPICPS",   "Light", "EB"),
    (8,  "PID",    701,  "PIDAT",    "Light", "EB"),
    # Light — ACE
    (7,  "ACE",    600,  "NACCPS",   "Light", "EB"),
    # Light — CCTV
    (20, "CCTV",   1203, "WCE",      "Light", "P1"),
    (20, "CCTV",   1204, "CVE",      "Light", "N"),
    # Light — Radio
    (19, "Radio",  1103, "CMA",      "Light", "B6"),
    (19, "Radio",  1100, "NTRCPS",   "Light", "EB"),
    # Light — VMI
    (11, "VMI",    1000, "NVMCPS",   "Light", "EB"),
]

HEAVY_FAULTS = [f for f in FAULT_CATALOG if f[4] == "Heavy"]
LIGHT_FAULTS = [f for f in FAULT_CATALOG if f[4] == "Light"]


def pick_fault(prefer_heavy=False):
    """Pilih fault secara acak, dengan bias sesuai prefer_heavy."""
    pool = HEAVY_FAULTS if (prefer_heavy and random.random() < 0.7) else FAULT_CATALOG
    return random.choice(pool)


def make_record(block_no: int, base_time: datetime, in_service: bool) -> dict:
    """Buat satu failure record realistis."""
    eq_code, eq_name, fc, fn, cls, base_notch = pick_fault(prefer_heavy=in_service)

    ts = base_time - timedelta(minutes=block_no * 2 + random.randint(0, 3))

    if in_service:
        # Kereta bergerak: ada speed, location, notch bervariasi
        speed    = random.randint(10, 85)
        loc_m    = random.randint(-500, 15000)  # -500 = depo, 0-15000 = di lintasan
        notch    = random.choice(["N", "B1", "B2", "B4", "P1", "P2", "P3", "EB"])
        train_id = "%04d" % random.randint(1, 1611)
        ov       = random.choice([220, 600, 750, 1500])  # tegangan catenary volt
    else:
        # Kereta di depo: speed=0, loc=0, notch=EB, train_id=FFFF
        speed    = 0
        loc_m    = 0
        notch    = base_notch
        train_id = "FFFF"
        ov       = random.choice([0, 10, 20])

    return {
        "block_no":       block_no,
        "timestamp":      ts.strftime("%Y-%m-%dT%H:%M:%S"),
        "car_no":         random.randint(1, 6),
        "occur_recover":  1 if (random.random() < 0.25) else 0,  # 75% occur
        "train_id":       train_id,
        "location_m":     loc_m,
        "equipment_code": eq_code,
        "equipment_name": eq_name,
        "fault_code":     fc,
        "fault_name":     fn,
        "notch":          notch,
        "speed_kmh":      speed,
        "overhead_v":     ov,
    }


def post_session(rake_id: int, read_time: datetime, record_count: int,
                 in_service: bool = False, label: str = "") -> bool:
    """POST satu sesi ke /api/failures dan tampilkan hasilnya."""
    records = [make_record(i, read_time, in_service) for i in range(record_count)]

    payload = {
        "rake_id":      rake_id,
        "read_time":    read_time.strftime("%Y-%m-%dT%H:%M:%S"),
        "record_count": record_count,
        "records":      records,
    }

    url     = API_BASE + ENDPOINT
    body    = json.dumps(payload).encode("utf-8")
    headers = {
        "Content-Type":  "application/json",
        "Authorization": "Bearer " + API_KEY,
        "User-Agent":    "TIS-Gateway/1.0-dummy",
    }

    context = "service" if in_service else "depot"
    print(f"  [POST] TS-{rake_id:02d} | {record_count:3d} records | "
          f"{read_time.strftime('%Y-%m-%d %H:%M')} | {context:<7} | {label}")

    try:
        req = urllib.request.Request(url, data=body, headers=headers, method="POST")
        with urllib.request.urlopen(req, timeout=15) as resp:
            result = json.loads(resp.read())
            sid    = result.get("session_id", "?")[:8]
            recv   = result.get("received", "?")
            print(f"         OK  HTTP {resp.status} | session={sid}... | received={recv}")
            return True

    except urllib.error.HTTPError as e:
        body_err = e.read().decode("utf-8", errors="replace")
        print(f"         FAIL  HTTP {e.code}: {e.reason}")
        try:
            err = json.loads(body_err)
            print("         " + json.dumps(err, ensure_ascii=False)[:200])
        except Exception:
            print("         " + body_err[:200])
        return False

    except urllib.error.URLError as e:
        print(f"         ERROR  Tidak bisa konek ke {API_BASE}")
        print(f"         {e.reason}")
        print("         -> Pastikan Laravel sudah berjalan: php artisan serve")
        return False

    except Exception as e:
        print(f"         ERROR  {e}")
        return False


def main():
    now = datetime.now()

    # 15 sesi tersebar di berbagai trainset dan tanggal
    # Format: (rake_id, days_ago, hour, record_count, in_service, label)
    sessions = [
        # ── Minggu ini ──────────────────────────────────────────────
        (5,  0,  7,  200, False, "Depo pagi, download rutin harian"),
        (3,  1,  8,   15, True,  "Kegagalan ATO saat dinas pagi"),
        (12, 1, 13,    8, False, "Pemeriksaan siang di depo"),
        (7,  2,  6,  200, False, "Download awal shift pagi"),
        (16, 2, 19,   23, True,  "Fault BECU saat operasi sore"),

        # ── Minggu lalu ─────────────────────────────────────────────
        (1,  7, 21,  200, False, "Download akhir hari operasional"),
        (9,  8,  7,   42, True,  "Multiple fault DOOR jam sibuk pagi"),
        (14, 9, 14,  200, False, "Rutin download siang hari"),
        (6, 10, 16,   11, True,  "Fault VVVF saat akselerasi"),
        (11, 11, 8,  200, False, "Download shift pagi"),

        # ── 2 Minggu lalu ───────────────────────────────────────────
        (2,  14, 9,   67, True,  "Gangguan PA & Radio saat dinas"),
        (8,  15, 7,  200, False, "Download harian depo Lebak Bulus"),
        (4,  18, 11,  34, True,  "Fault ATO saat interlock PSD"),
        (15, 21, 8,  200, False, "Pemeriksaan berkala bulanan"),

        # ── Awal bulan ──────────────────────────────────────────────
        (10, 28, 6,  200, False, "Download backup sebelum overhaul"),
    ]

    print("=" * 70)
    print("  TIS Gateway — Dummy Session Sender")
    print(f"  Target : {API_BASE}{ENDPOINT}")
    print(f"  API Key: {API_KEY}")
    print(f"  Total  : {len(sessions)} sesi")
    print("=" * 70)

    ok = 0
    fail = 0

    for rake_id, days_ago, hour, count, in_service, label in sessions:
        read_time = now - timedelta(days=days_ago)
        read_time = read_time.replace(hour=hour, minute=random.randint(0, 59), second=0)
        success   = post_session(rake_id, read_time, count, in_service, label)
        if success:
            ok += 1
        else:
            fail += 1

    print("=" * 70)
    print(f"  Selesai: {ok} berhasil, {fail} gagal dari {len(sessions)} sesi")
    print("=" * 70)

    sys.exit(0 if fail == 0 else 1)


if __name__ == "__main__":
    main()
