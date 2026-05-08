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
