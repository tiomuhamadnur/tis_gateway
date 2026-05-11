# TIS Gateway — MRT Jakarta CP108

Gateway service untuk mengambil data failure dari Train Information System (TIS)
Sumitomo CP108 via UDP, lalu mengekspornya ke CSV, PDF, dan mengirimnya ke CMS Laravel.

---

## Arsitektur Sistem

```
TIS Hardware (CCU/MON)
        │  UDP port 262
        ▼
Python Gateway (main.py)
        │  HTTP POST (JSON)
        ▼
Laravel CMS (tis_api_laravel/)
        │
        ├── Database MySQL (failure_records, failure_sessions)
        └── Web Dashboard (/failures)
```

---

## Persyaratan Sistem

| Komponen | Versi Minimum | Keterangan |
|---|---|---|
| Python | 3.7+ | Gateway TIS |
| PHP | 8.1+ | Laravel CMS |
| MySQL / SQLite | 5.7+ / 3.x | Database CMS |
| Composer | 2.x | Dependency PHP |

---

## Struktur Proyek

```
tis_gateway/
├── config/
│   ├── settings.py          # Konfigurasi terpusat (network, output, cloud, log)
│   └── equipment_map.py     # 125+ fault code, equipment map, guidance
│
├── protocol/
│   ├── udp_client.py        # UDP socket handler (send/receive/retry)
│   ├── commands.py          # Builder packet CMD 0x20 / 0x32 / 0x34 / 0x36
│   └── session.py           # Orkestrasi: handshake → download → parse
│
├── parsers/
│   ├── bcd.py               # Decode timestamp BCD 6-byte dari TIS
│   ├── record_parser.py     # Parse 20-byte raw record → FailureRecord dataclass
│   └── response_parser.py   # Validasi & deserialisasi UDP response packet
│
├── exporter/
│   ├── csv_exporter.py      # Export CSV (format identik PTU Sumitomo)
│   └── pdf_exporter.py      # Export PDF tabular
│
├── uploader/
│   └── cloud_uploader.py    # HTTP POST ke Laravel API (JSON + multipart)
│
├── utils/
│   ├── logger.py            # Logging terpusat dengan rotasi harian
│   └── checksum.py          # Kalkulasi & verifikasi checksum UDP packet
│
├── tests/
│   ├── mock_tis.py          # Mock TIS server (tanpa kereta fisik)
│   ├── dummy_upload.py      # Kirim 1 sesi dummy ke API (CLI)
│   ├── dummy_sessions.py    # Kirim 15 sesi dummy multi-trainset
│   ├── dummy_one_session.py # Kirim 1 sesi 200 record tersebar 3 hari
│   └── test_parser.py       # Unit test parser dengan data PCAP nyata
│
├── tis_api_laravel/         # Laravel CMS (dashboard + REST API)
├── docs/                    # Dokumen referensi & sample data
├── main.py                  # Entry point gateway
├── .env                     # Konfigurasi environment (tidak di-commit)
└── requirements.txt         # Python dependencies
```

---

## Setup

### 1. Python Gateway

```bash
# Install dependencies
pip install -r requirements.txt

# Salin dan sesuaikan konfigurasi
cp .env.example .env
```

Edit `.env`:

```env
TIS_HOST=192.168.x.x        # IP CCU/MON kereta
OUTPUT_DIR=./output
CLOUD_API_URL=http://127.0.0.1:8000
TIS_API_KEY=tiomuhamadnur
CLOUD_ENABLED=true           # true = upload otomatis tiap run
LOG_LEVEL=DEBUG              # DEBUG untuk development, INFO untuk produksi
```

### 2. Laravel CMS

```bash
cd tis_api_laravel

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed

php artisan serve   # berjalan di http://127.0.0.1:8000
```

---

## Cara Pakai

### Kontrol Upload

Upload ke CMS dikontrol lewat dua cara (keduanya bisa dipakai bersamaan):

| Cara | Kapan dipakai |
|---|---|
| `CLOUD_ENABLED=true` di `.env` | Default on/off per environment (development vs produksi) |
| Flag `--upload` saat runtime | Override sesaat tanpa ubah `.env` |

Upload aktif jika **salah satu** bernilai true. Untuk nonaktifkan sementara saat `.env` sudah set `CLOUD_ENABLED=true`, hapus atau ubah nilainya ke `false`.

### Command Line Options

| Opsi | Tipe | Default | Keterangan |
|---|---|---|---|
| `--rake-id` | int | auto-detect | Override nomor formasi kereta |
| `--host` | str | dari `.env` | IP TIS (CCU/MON) |
| `--port` | int | `262` | Port TIS |
| `--local-port` | int | `263` | Port lokal gateway |
| `--output-dir` | str | `./output` | Direktori output file |
| `--no-csv` | flag | — | Skip export CSV |
| `--no-pdf` | flag | — | Skip export PDF |
| `--upload` | flag | — | Aktifkan upload ke CMS (override `.env`) |
| `--raw` | flag | — | Simpan raw bytes (debug) |

### Contoh Penggunaan

**Setup `.env` development (upload aktif secara default):**

```env
TIS_HOST=192.168.1.1
CLOUD_API_URL=http://127.0.0.1:8000
CLOUD_ENABLED=true
LOG_LEVEL=DEBUG
```

| Skenario | Perintah |
|---|---|
| Normal — colok LAN ke TIS, jalankan | `python main.py` |
| Rake ID tidak terdeteksi otomatis | `python main.py --rake-id 5` |
| Override IP TIS sementara | `python main.py --host 192.168.1.100` |
| Hanya export lokal, tanpa upload | Set `CLOUD_ENABLED=false` di `.env` |
| Upload sesaat tanpa ubah `.env` | `python main.py --upload` |
| Hanya CSV, skip PDF | `python main.py --no-pdf` |
| Debug raw bytes per record | `python main.py --raw` |
| Test dengan mock TIS server | `python tests/mock_tis.py` lalu `python main.py --host 127.0.0.1` |

---

## Alur Eksekusi

| Fase | Perintah TIS | Halaman | Keterangan |
|---|---|---|---|
| 1. Handshake | CMD `0x20` | 1 | Auto-detect `rake_id` dari respons TIS |
| 2. Metadata | CMD `0x32` | 6 | Download metadata sesi |
| 3. Dataset B | CMD `0x34` | 6 | Download dataset tambahan |
| 4. Failure records | CMD `0x36` | 40 × 3 poll | Download 200 failure records (5 record/page) |
| 5. Export | — | — | Generate CSV dan/atau PDF ke `./output/` |
| 6. Upload | HTTP POST | — | Kirim JSON + file ke Laravel jika `CLOUD_ENABLED=true` atau `--upload` |

### Nama File Output

| Format | Contoh |
|---|---|
| CSV | `D260507_005.csv` |
| PDF | `D260507_005.pdf` |
| RAW (debug) | `D260507_005.bin` |

---

## Equipment & Fault Map

Semua decoding dilakukan oleh `config/equipment_map.py` (Python) dan `TisEquipmentMap.php` (Laravel).

### Equipment Code

| Code | Nama | Klasifikasi Range |
|---|---|---|
| 1 | TIS | Heavy (100–199) |
| 2 | ATO | Heavy (200–299) |
| 3 | VVVF1 | Heavy (300–399) |
| 4 | VVVF2 | Heavy (300–399) |
| 5 | APS | Heavy (400–499) |
| 6 | BECU | Heavy (500–599) |
| 7 | ACE | Light (600–699) |
| 8 | PID | Light (700–799) |
| 9 | PA | Light (800–899) |
| 10 | DOOR | Heavy (900–999) |
| 11 | VMI | Light (1000–1099) |
| 19 | Radio | Light (1100–1199) |
| 20 | CCTV | Light (1200–1299) |

### Klasifikasi Fault

| Klasifikasi | Dasar | Contoh Equipment |
|---|---|---|
| **Heavy** | Fault code range equipment safety-critical | ATO, BECU, DOOR, VVVF, APS |
| **Light** | Fault code range equipment non-safety | PA, PID, ACE, CCTV, Radio |
| **Info** | Fault code di luar semua range | Fallback otomatis |

---

## Fitur Pairing Occur ↔ Recover

Setiap fault event di TIS direkam dalam dua baris:
- `occur_recover = 0` → **Occur** (fault mulai terjadi)
- `occur_recover = 1` → **Recover** (fault selesai / pulih)

Gateway menyimpan keduanya ke database. Setelah upload, `FaultPairingService` secara otomatis mencocokkan pasangan Occur–Recover berdasarkan `fault_code + car_no` yang sama, lalu menghitung **durasi fault** dalam detik.

| Kolom | Keterangan |
|---|---|
| `paired_record_id` | FK ke record pasangan (Occur↔Recover) |
| `duration_seconds` | Durasi fault (null = masih aktif / belum resolve) |

Dashboard di `/failures` menampilkan:

| Occurred At | Recovered At | Duration | Equipment | Fault | … |
|---|---|---|---|---|---|
| 08 Mei 2026 14:32:15 | 08 Mei 2026 14:35:42 | 3m 27s | BECU | NBPS | … |
| 08 Mei 2026 16:04:07 | *Still Active* | — | DOOR | OPE | … |

---

## Testing & Dummy Data

| Script | Keterangan | Perintah |
|---|---|---|
| `tests/mock_tis.py` | Simulasi hardware TIS di localhost | `python tests/mock_tis.py` |
| `tests/dummy_upload.py` | Kirim N record dummy (CLI) | `python tests/dummy_upload.py --count 50` |
| `tests/dummy_one_session.py` | 1 sesi, 200 record, 3 hari berbeda | `python tests/dummy_one_session.py --rake-id 5` |
| `tests/dummy_sessions.py` | 15 sesi multi-trainset | `python tests/dummy_sessions.py` |
| `tests/test_parser.py` | Unit test parser dengan data PCAP nyata | `python -m pytest tests/test_parser.py` |

---

## Troubleshooting

| Error | Penyebab | Solusi |
|---|---|---|
| `rake_id tidak diketahui` | TIS tidak kirim nomor formasi di handshake | Tambah `--rake-id <nomor>` |
| `Connection refused / No response` | IP TIS salah atau firewall | Cek `TIS_HOST` di `.env`, pastikan port 262 terbuka |
| `No module named 'parsers'` | Python path tidak benar | Jalankan dari root project: `cd tis_gateway && python main.py` |
| `reportlab not found` | Dependency belum install | `pip install -r requirements.txt` |
| `HTTP 401 dari CMS` | API key tidak cocok | Samakan `TIS_API_KEY` di `.env` dengan config Laravel |
| `HTTP 422 dari CMS` | Payload tidak valid | Cek log Laravel: `php artisan log:show` |
| Laravel tidak jalan | Server belum distart | `cd tis_api_laravel && php artisan serve` |

---

## REST API CMS

API dilindungi Bearer Token (`TIS_API_KEY`). Header wajib di semua request:

```
Authorization: Bearer {TIS_API_KEY}
Content-Type: application/json
```

### Endpoints

| Method | Endpoint | Keterangan |
|---|---|---|
| `POST` | `/api/failures` | Terima failure records dari gateway |
| `GET` | `/api/failures` | List semua sesi (filter: rake_id, from, to) |
| `GET` | `/api/failures/{session_id}` | Detail satu sesi + semua record-nya |
| `POST` | `/api/files` | Upload file CSV / PDF (multipart) |
| `GET` | `/api/dashboard` | Statistik agregat dashboard |
| `GET` | `/api/analytics/trend` | Tren fault per hari/minggu/bulan |
| `GET` | `/api/analytics/pareto` | Analisis Pareto fault code |
| `GET` | `/api/health` | Health check |

### Contoh POST `/api/failures`

```json
{
  "rake_id": 5,
  "read_time": "2026-05-08T16:08:16",
  "record_count": 200,
  "records": [
    {
      "block_no": 0,
      "timestamp": "2026-05-08T14:32:00",
      "car_no": 3,
      "occur_recover": 0,
      "train_id": "FFFF",
      "location_m": 0,
      "equipment_code": 9,
      "equipment_name": "PA",
      "fault_code": 806,
      "fault_name": "DATASA",
      "notch": "EB",
      "speed_kmh": 0,
      "overhead_v": 10
    }
  ]
}
```

### Validasi Field

| Field | Tipe | Aturan |
|---|---|---|
| `rake_id` | int | Wajib |
| `read_time` | datetime | Wajib, format ISO 8601 |
| `records` | array | Wajib, min 1 item |
| `car_no` | int | 1–6 |
| `occur_recover` | int | `0` (Occur) atau `1` (Recover) |
| `equipment_code` | int | 1–23 |
| `fault_code` | int | 100–1599 |
| `speed_kmh` | int | ≥ 0 |
| `overhead_v` | int | ≥ 0 |

---

## Skema Database

### Tabel `failure_sessions`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT PK | Auto-increment |
| `session_id` | UUID | Identifier unik per sesi |
| `rake_id` | VARCHAR | Nomor formasi kereta |
| `read_time` | DATETIME | Waktu baca dari TIS |
| `download_date` | DATETIME | Waktu diterima server |
| `total_records` | INT | Jumlah record dalam sesi |
| `status` | VARCHAR | `completed` / `pending` / `failed` |

### Tabel `failure_records`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT PK | Auto-increment |
| `session_id` | BIGINT FK | → `failure_sessions.id` |
| `block_no` | INT | Nomor urut record di TIS |
| `timestamp` | DATETIME | Waktu fault (dari BCD TIS) |
| `car_no` | TINYINT | Nomor car (1–6) |
| `occur_recover` | TINYINT | `0` = Occur, `1` = Recover |
| `paired_record_id` | BIGINT FK NULL | FK ke record pasangannya |
| `duration_seconds` | INT NULL | Durasi fault (null = masih aktif) |
| `train_id` | VARCHAR(10) | ID formasi (`FFFF` = di depo) |
| `location_m` | INT | Posisi kereta (meter dari origin) |
| `equipment_code` | TINYINT | Kode equipment (1–23) |
| `equipment_name` | VARCHAR(50) | Nama equipment |
| `fault_code` | SMALLINT | Kode fault (100–1599) |
| `fault_abbrev` | VARCHAR(20) | Singkatan fault (e.g. `DATASA`) |
| `fault_description` | TEXT | Deskripsi lengkap |
| `classification` | VARCHAR(10) | `Heavy` / `Light` / `Info` |
| `guidance` | TEXT | Instruksi penanganan |
| `notch` | VARCHAR(10) | Level notch (e.g. `EB`, `B2`) |
| `speed_kmh` | SMALLINT | Kecepatan saat fault (km/h) |
| `overhead_v` | SMALLINT | Tegangan catenary (Volt) |
