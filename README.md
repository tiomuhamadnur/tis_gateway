# TIS Gateway — MRT Jakarta CP108

Gateway service untuk mengambil data failure dari Train Information System (TIS)
Sumitomo CP108 via UDP, lalu mengekspornya ke CSV, PDF, dan cloud API.

> 📋 **Cetak Biru Detail**: Lihat [BLUEPRINT.md](BLUEPRINT.md) untuk spesifikasi teknis lengkap, arsitektur, dan protokol komunikasi.

## Persyaratan Sistem

- **Python**: 3.7+ (direkomendasikan 3.8+ untuk performa optimal)
- **OS**: Windows/Linux/macOS
- **Dependencies**: Lihat `requirements.txt`
- **Network**: Akses UDP ke TIS server (default port 262)

## Setup Awal

### 1. Clone atau Download Proyek

```bash
git clone <repository-url>
cd tis_gateway
```

### 2. Install Python Dependencies

```bash
pip install -r requirements.txt
```

**Catatan**: Jika menggunakan virtual environment, aktifkan dulu:

```bash
python -m venv venv
venv\Scripts\activate  # Windows
source venv/bin/activate  # Linux/Mac
pip install -r requirements.txt
```

### 3. Konfigurasi Environment

Edit file `config/settings.py` untuk menyesuaikan environment:

#### Parameter Wajib:
- `tis_host`: IP address TIS server (default: "127.0.0.1")
- `tis_port`: Port TIS untuk menerima data (default: 262)
- `local_port`: Port lokal gateway untuk listen (default: 263)

#### Parameter Opsional:
- `output_dir`: Direktori output file (default: "./output")
- `export_csv`: Enable/disable export CSV (default: True)
- `export_pdf`: Enable/disable export PDF (default: True)
- `cloud.enabled`: Enable/disable upload ke cloud (default: False)
- `cloud.api_base_url`: URL base cloud API
- `log.level`: Level logging (DEBUG/INFO/WARNING/ERROR)

#### Override via Environment Variable:
```bash
export TIS_HOST=192.168.1.100
export TIS_PORT=262
export OUTPUT_DIR=/path/to/output
export CLOUD_API_URL=https://api.example.com
export LOG_LEVEL=DEBUG
```

### 4. Verifikasi Setup

```bash
# Test import modules
python -c "from config.settings import config; print('Config OK')"

# Test mock server (untuk development)
python tests/mock_tis.py &
python main.py --rake-id 5 --host 127.0.0.1
```

## Struktur Proyek

```
tis_gateway/
├── config/
│   ├── settings.py          # Semua konfigurasi terpusat (IP, port, timeout, dll)
│   └── equipment_map.py     # 125+ fault codes dengan deskripsi, guidance, & klasifikasi
│
├── protocol/
│   ├── udp_client.py        # Low-level UDP socket handler
│   ├── commands.py          # Builder untuk semua command packet (0x20, 0x32, 0x34, 0x36)
│   └── session.py           # Orkestrasi sesi: handshake → download → disconnect
│
├── parsers/
│   ├── bcd.py               # Decode BCD timestamp
│   ├── record_parser.py     # Parse raw bytes → FailureRecord dataclass
│   └── response_parser.py   # Parse response packet per command type
│
├── exporter/
│   ├── csv_exporter.py      # Generate CSV (format identik Sumitomo PTU)
│   └── pdf_exporter.py      # Generate PDF (format identik Sumitomo PTU)
│
├── uploader/
│   └── cloud_uploader.py    # Kirim data ke cloud REST API
│
├── utils/
│   ├── logger.py            # Logging terpusat
│   └── checksum.py          # Kalkulasi & verifikasi checksum paket
│
├── tests/
│   ├── test_parser.py       # Unit test parser dengan data pcap nyata
│   ├── test_exporter.py     # Unit test output CSV & PDF
│   └── mock_tis.py          # Mock TIS server untuk testing tanpa kereta
│
├── main.py                  # Entry point — jalankan satu sesi download
├── requirements.txt         # Python dependencies
├── README.md                # Dokumentasi ini
└── BLUEPRINT.md             # Cetak biru detail aplikasi
```

## Cara Pakai

### Command Line Options

```bash
python main.py [OPTIONS]

Options:
  --rake-id INTEGER     ID kereta (1-99) [required]
  --host TEXT           IP TIS server [default: dari config]
  --port INTEGER        Port TIS server [default: dari config]
  --output-dir TEXT     Direktori output [default: dari config]
  --no-csv              Skip export CSV
  --no-pdf              Skip export PDF
  --no-upload           Skip upload ke cloud
  --help                Show help message
```

### Contoh Penggunaan

```bash
# Jalankan dengan konfigurasi default
python main.py --rake-id 5

# Jalankan dengan host spesifik
python main.py --rake-id 5 --host 192.168.1.100 --port 262

# Jalankan dengan output custom, skip PDF
python main.py --rake-id 5 --output-dir /tmp/tis_output --no-pdf

# Test dengan mock server (development)
python tests/mock_tis.py &
python main.py --rake-id 5 --host 127.0.0.1
```

### Output Files

Script akan generate file dengan format:
- `D{rake_id}{date}{time}.csv` — Data failure dalam format CSV
- `D{rake_id}{date}{time}.pdf` — Report PDF dengan tabel dan grafik
- `D{rake_id}{date}{time}.bin` — Raw bytes response (jika `export_raw=True`)

### Logging

Log akan ditulis ke:
- Console (stdout/stderr)
- File: `logs/tis_gateway_{date}.log` (jika `log_to_file=True`)

## Troubleshooting

### Error: "No module named 'parsers'"
- Pastikan semua `__init__.py` ada di setiap subfolder
- Jalankan: `python -c "import parsers; print('OK')"`

### Error: "Connection refused" / "No response from TIS"
- Cek IP dan port TIS server
- Pastikan firewall mengizinkan UDP traffic
- Test konektivitas: `nc -u -z {host} {port}`

### Error: "Permission denied" pada output directory
- Cek permission write pada direktori output
- Buat direktori jika belum ada: `mkdir -p {output_dir}`

### Error: "reportlab not found"
- Install ulang: `pip install reportlab==3.6.13`
- Untuk Python 3.7, gunakan reportlab < 4.0

### Performance Issue
- Tingkatkan `recv_timeout_sec` jika network lambat
- Kurangi `max_retries` jika timeout sering terjadi
- Monitor log untuk bottleneck

## Flow Komunikasi

```
1. Handshake  (CMD 0x20)  — 1x
2. Metadata   (CMD 0x32)  — 6 pages
3. Data Set B (CMD 0x34)  — 6 pages
4. Failure    (CMD 0x36)  — 40 pages × 5 records = 200 records
5. Export     → CSV + PDF
6. Upload     → Cloud API
```

## Equipment & Fault Map

`config/equipment_map.py` mendecode semua field dari raw packet:

| Helper | Input | Output |
|--------|-------|--------|
| `get_fault_abbrev(fault_code)` | int | Abbreviation, e.g. `"ESA"` |
| `get_fault_description(fault_code)` | int | Deskripsi lengkap |
| `get_fault_classification(fault_code)` | int | `"Heavy"` / `"Light"` |
| `get_failure_guidance(fault_code)` | int | Instruksi penanganan |
| `get_equipment_by_fault_code(fault_code)` | int | `(eq_code, eq_name)` |
| `lookup_complete(fault_code, car_id, notch, occur)` | int × 4 | Dict lengkap |

Confidence level setiap fault code: `C` = confirmed dari PTU output, `E` = extracted dari manual, `R` = range-only.

## Development

### Running Tests

```bash
# Unit tests
python -m pytest tests/

# Test dengan mock server
python tests/mock_tis.py &
python main.py --rake-id 5 --host 127.0.0.1
```

### Code Quality

```bash
# Format code
black .

# Lint code
flake8 .

# Type check
mypy .
```

---

## Backend API Specification

Bagian ini adalah referensi untuk membangun backend API yang menerima data dari TIS Gateway.
Gateway berperan sebagai **producer** (pengirim), backend berperan sebagai **consumer** (penerima & penyimpan).

### Arsitektur Backend

```
TIS Gateway (Python)
        │
        │  HTTP POST (JSON / multipart)
        │  Authorization: Bearer {TIS_API_KEY}
        ▼
Backend API Server
        │
        ├── Database (PostgreSQL / MySQL)
        ├── File Storage (CSV / PDF)
        └── View / Dashboard
```

---

### Autentikasi

Semua request dari gateway menggunakan **Bearer Token** di header:

```
Authorization: Bearer {TIS_API_KEY}
```

Backend harus memvalidasi token ini di setiap endpoint. Token dikonfigurasi via environment variable `TIS_API_KEY` di sisi gateway.

---

### Endpoints yang Harus Diimplementasi

#### POST `/v1/failures` — Terima Failure Records

Dipanggil gateway setiap selesai satu sesi download dari TIS.

**Request Headers:**
```
Content-Type: application/json
Authorization: Bearer {TIS_API_KEY}
```

**Request Body:**
```json
{
  "rake_id": 5,
  "read_time": "2026-05-07T16:08:16",
  "record_count": 200,
  "records": [
    {
      "block_no": 10,
      "timestamp": "2026-05-07T14:32:00",
      "car_no": 3,
      "occur_recover": "O",
      "train_id": "0005",
      "location_m": 1234,
      "equipment_code": 8,
      "equipment_name": "PA",
      "fault_code": 806,
      "fault_name": "DATASA",
      "notch": "N2",
      "speed_kmh": 45,
      "overhead_v": 750.5
    }
  ]
}
```

**Field `occur_recover`:**
- `"O"` = Occurrence (fault terjadi)
- `"R"` = Recovery (fault pulih)

**Response `201 Created`:**
```json
{
  "session_id": "uuid-or-integer",
  "received": 200,
  "status": "ok"
}
```

---

#### POST `/v1/files` — Terima Upload File (CSV / PDF)

Dipanggil gateway untuk upload file hasil export. Request berformat `multipart/form-data`.

**Request Fields:**
| Field     | Type | Keterangan                                         |
|-----------|------|----------------------------------------------------|
| `rake_id` | int  | ID kereta                                          |
| `file`    | file | File CSV (`text/csv`) atau PDF (`application/pdf`) |

**Response `201 Created`:**
```json
{
  "file_id": "uuid-or-integer",
  "filename": "D05260507_1608.csv",
  "status": "ok"
}
```

---

#### GET `/v1/failures` — List Sesi Download

Untuk view dashboard / history.

**Query Parameters:**
| Param      | Type | Default | Keterangan               |
|------------|------|---------|--------------------------|
| `rake_id`  | int  | —       | Filter per kereta        |
| `from`     | date | —       | Tanggal mulai (ISO 8601) |
| `to`       | date | —       | Tanggal akhir            |
| `page`     | int  | 1       | Pagination               |
| `per_page` | int  | 20      | Jumlah per halaman       |

**Response `200 OK`:**
```json
{
  "total": 42,
  "page": 1,
  "per_page": 20,
  "sessions": [
    {
      "session_id": 1,
      "rake_id": 5,
      "read_time": "2026-05-07T16:08:16",
      "record_count": 200,
      "created_at": "2026-05-07T16:08:20"
    }
  ]
}
```

---

#### GET `/v1/failures/{session_id}` — Detail Sesi + Records

**Response `200 OK`:**
```json
{
  "session_id": 1,
  "rake_id": 5,
  "read_time": "2026-05-07T16:08:16",
  "record_count": 200,
  "records": [
    {
      "block_no": 10,
      "timestamp": "2026-05-07T14:32:00",
      "car_no": 3,
      "occur_recover": "O",
      "train_id": "0005",
      "location_m": 1234,
      "equipment_code": 8,
      "equipment_name": "PA",
      "fault_code": 806,
      "fault_name": "DATASA",
      "notch": "N2",
      "speed_kmh": 45,
      "overhead_v": 750.5
    }
  ]
}
```

---

#### GET `/v1/dashboard` — Ringkasan Statistik (Evaluasi)

Endpoint utama untuk halaman evaluasi / monitoring.

**Response `200 OK`:**
```json
{
  "total_sessions": 42,
  "total_records": 8400,
  "last_upload": "2026-05-07T16:08:16",
  "by_rake": [
    { "rake_id": 5, "session_count": 10, "record_count": 2000 }
  ],
  "by_equipment": [
    { "equipment_name": "PA",    "count": 340 },
    { "equipment_name": "VVVF1", "count": 210 }
  ],
  "by_classification": {
    "Heavy": 120,
    "Light": 7980,
    "Info":  300
  },
  "recent_heavy_faults": [
    {
      "timestamp": "2026-05-07T14:32:00",
      "rake_id": 5,
      "car_no": 3,
      "equipment_name": "VVVF1",
      "fault_code": 305,
      "fault_name": "OVCF"
    }
  ]
}
```

---

#### GET `/v1/analytics/trend` — Tren Fault per Periode

**Query Parameters:** `rake_id`, `from`, `to`, `group_by` (day / week / month)

**Response `200 OK`:**
```json
{
  "group_by": "day",
  "data": [
    { "date": "2026-05-01", "count": 45 },
    { "date": "2026-05-02", "count": 38 }
  ]
}
```

---

#### GET `/v1/health` — Health Check

```json
{ "status": "ok", "version": "1.0" }
```

---

### Struktur Database

#### Tabel `sessions`

Setiap POST ke `/v1/failures` membuat satu baris di sini.

| Kolom          | Tipe        | Keterangan                         |
|----------------|-------------|------------------------------------|
| `id`           | INT PK      | Auto-increment                     |
| `rake_id`      | INT         | ID kereta (1–99)                   |
| `read_time`    | DATETIME    | Waktu baca dari TIS (dari gateway) |
| `record_count` | INT         | Jumlah record diterima             |
| `upload_ip`    | VARCHAR(45) | IP gateway pengirim                |
| `status`       | ENUM        | `received` / `processed` / `error`|
| `created_at`   | DATETIME    | Waktu diterima server              |

---

#### Tabel `failure_records`

Satu baris per satu failure record dalam satu sesi.

| Kolom            | Tipe        | Keterangan                                  |
|------------------|-------------|---------------------------------------------|
| `id`             | INT PK      | Auto-increment                              |
| `session_id`     | INT FK      | → `sessions.id`                             |
| `block_no`       | INT         | Nomor block di TIS (urutan record)          |
| `timestamp`      | DATETIME    | Waktu fault terjadi (dari BCD TIS)          |
| `car_no`         | TINYINT     | Nomor car (1–6)                             |
| `occur_recover`  | CHAR(1)     | `O` = occurrence, `R` = recovery           |
| `train_id`       | VARCHAR(10) | ID formasi kereta (e.g. `"0005"`)           |
| `location_m`     | INT         | Posisi kereta dalam meter                   |
| `equipment_code` | SMALLINT    | Kode equipment (1–23, lihat equipment map)  |
| `equipment_name` | VARCHAR(30) | Nama equipment (e.g. `"PA"`, `"VVVF1"`)    |
| `fault_code`     | SMALLINT    | Kode fault (100–1599)                       |
| `fault_name`     | VARCHAR(30) | Singkatan fault (e.g. `"DATASA"`, `"ESA"`) |
| `notch`          | VARCHAR(5)  | Level notch (e.g. `"N2"`, `"B3"`)          |
| `speed_kmh`      | SMALLINT    | Kecepatan kereta saat fault (km/h)          |
| `overhead_v`     | FLOAT       | Tegangan overhead wire (Volt)               |

**Index yang direkomendasikan:**
```sql
INDEX idx_session   (session_id)
INDEX idx_rake_time (session_id, timestamp)
INDEX idx_fault     (fault_code)
INDEX idx_equip     (equipment_code)
```

---

#### Tabel `uploaded_files`

Menyimpan metadata file CSV/PDF yang diupload gateway.

| Kolom               | Tipe         | Keterangan                |
|---------------------|--------------|---------------------------|
| `id`                | INT PK       | Auto-increment            |
| `session_id`        | INT FK NULL  | → `sessions.id` (nullable)|
| `rake_id`           | INT          | ID kereta                 |
| `original_filename` | VARCHAR(100) | e.g. `D05260507_1608.csv` |
| `stored_path`       | VARCHAR(255) | Path file di server       |
| `file_type`         | ENUM         | `csv` / `pdf`             |
| `file_size_bytes`   | INT          | Ukuran file               |
| `uploaded_at`       | DATETIME     | Waktu upload              |

---

#### Tabel `rakes` (Master Data)

| Kolom     | Tipe        | Keterangan           |
|-----------|-------------|----------------------|
| `id`      | INT PK      | Auto-increment       |
| `rake_id` | INT UNIQUE  | ID dari TIS (1–99)   |
| `name`    | VARCHAR(50) | Nama rangkaian       |
| `active`  | BOOLEAN     | Status operasional   |
| `notes`   | TEXT NULL   | Catatan tambahan     |

---

#### Relasi Antar Tabel

```
rakes (1) ──── (N) sessions (1) ──── (N) failure_records
                        │
                        └──── (N) uploaded_files
```

---

### HTTP Response Codes

| Code | Kondisi                                       |
|------|-----------------------------------------------|
| 200  | OK — GET berhasil                             |
| 201  | Created — POST berhasil, resource dibuat      |
| 400  | Bad Request — body tidak valid / field kurang |
| 401  | Unauthorized — API key tidak valid            |
| 404  | Not Found — session_id tidak ditemukan        |
| 422  | Unprocessable — data gagal validasi business  |
| 500  | Internal Server Error                         |

---

### Validasi yang Harus Dilakukan Backend

| Field           | Validasi                                     |
|-----------------|----------------------------------------------|
| `rake_id`       | Integer 1–99                                 |
| `read_time`     | ISO 8601 datetime, tidak boleh di masa depan |
| `record_count`  | Harus cocok dengan panjang array `records`   |
| `car_no`        | Integer 1–6                                  |
| `occur_recover` | Hanya `"O"` atau `"R"`                       |
| `equipment_code`| Harus ada di equipment map (1–23)            |
| `fault_code`    | Integer 100–1599                             |
| `speed_kmh`     | Integer 0–200                                |
| `overhead_v`    | Float 0–1500                                 |

---

### Checklist Implementasi Backend

- [ ] Autentikasi Bearer Token di semua endpoint
- [ ] `POST /v1/failures` — simpan session + records ke DB
- [ ] `POST /v1/files` — simpan file CSV/PDF ke storage
- [ ] `GET /v1/failures` — list dengan filter & pagination
- [ ] `GET /v1/failures/{id}` — detail session + records
- [ ] `GET /v1/dashboard` — statistik agregat
- [ ] `GET /v1/analytics/trend` — tren per periode
- [ ] `GET /v1/health` — health check
- [ ] Validasi semua field input
- [ ] Index database untuk query analytics
- [ ] Log setiap request dari gateway (audit trail)
