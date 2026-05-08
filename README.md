# TIS Gateway — MRT Jakarta CP108

Gateway service untuk mengambil data failure dari Train Information System (TIS)
Sumitomo CP108 via UDP, lalu mengekspornya ke CSV, PDF, dan cloud API.

## Struktur Proyek

```
tis_gateway/
├── config/
│   ├── settings.py          # Semua konfigurasi terpusat (IP, port, timeout, dll)
│   └── equipment_map.py     # Mapping kode equipment & fault code → nama
│
├── protocol/
│   ├── udp_client.py        # Low-level UDP socket handler
│   ├── commands.py          # Builder untuk semua command packet (0x20, 0x32, 0x34, 0x36)
│   └── session.py           # Orkestrasi sesi: handshake → download → disconnect
│
├── parser/
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
└── requirements.txt
```

## Cara Pakai

```bash
# Install dependencies
pip install -r requirements.txt

# Edit konfigurasi
nano config/settings.py

# Jalankan satu sesi (ambil data dari satu kereta)
python main.py --rake-id 5

# Jalankan dengan output ke file
python main.py --rake-id 5 --output-dir ./output

# Test tanpa kereta (pakai mock TIS)
python tests/mock_tis.py &
python main.py --rake-id 5 --host 127.0.0.1
```

## Flow Komunikasi

```
1. Handshake  (CMD 0x20)  — 1x
2. Metadata   (CMD 0x32)  — 6 pages
3. Data Set B (CMD 0x34)  — 6 pages  
4. Failure    (CMD 0x36)  — 40 pages × 5 records = 200 records
5. Export     → CSV + PDF
6. Upload     → Cloud API
```
