# TIS Gateway — Blueprint (Cetak Biru)

## 1. Overview

TIS Gateway adalah aplikasi Python yang berfungsi sebagai bridge antara Train Information System (TIS) Sumitomo CP108 dengan sistem eksternal. Aplikasi ini mengambil data failure records dari TIS via protokol UDP proprietary, kemudian mengekspornya ke format CSV dan PDF yang kompatibel dengan PTU (Portable Test Unit) Sumitomo.

## 2. Arsitektur Aplikasi

### 2.1 Komponen Utama

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   main.py       │    │   protocol/     │    │   parsers/      │
│                 │    │                 │    │                 │
│ - CLI Interface │───▶│ - session.py    │───▶│ - response_     │
│ - Orchestration │    │ - udp_client.py │    │   parser.py     │
│                 │    │ - commands.py   │    │ - record_       │
│                 │    │                 │    │   parser.py     │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   exporter/     │    │   uploader/     │    │   config/       │
│                 │    │                 │    │                 │
│ - csv_exporter  │    │ - cloud_        │    │ - settings.py   │
│ - pdf_exporter  │    │   uploader      │    │ - equipment_map │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### 2.2 Dependency Flow

```
main.py
├── config.settings (konfigurasi)
├── protocol.session.TISSession
│   ├── protocol.udp_client.UDPClient
│   ├── protocol.commands (build packets)
│   ├── parsers.response_parser (parse responses)
│   └── parsers.record_parser (parse records)
├── exporter.* (generate output)
└── uploader.* (upload to cloud)
```

## 3. Spesifikasi Protokol TIS

### 3.1 UDP Socket Configuration

- **Local Port**: 263 (PTU listen port)
- **Remote Port**: 262 (TIS send port)
- **Protocol**: UDP (connectionless)
- **Buffer Size**: 4096 bytes
- **Timeout**: 3 detik per request

### 3.2 Packet Structure

#### Header (8 bytes)
```
Byte 0:   Prefix 0x02
Byte 1:   Command ID
Byte 2-3: Sequence / counter
Byte 4-5: Additional flags
Byte 6-7: Checksum
```

#### Command Types
- `0x20`: Handshake
- `0x32`: Metadata Request
- `0x34`: Data Set B Request
- `0x36`: Failure Records Request

### 3.3 Command Packets (confirmed dari PCAP dudu_sniffing_tis_ts5.pcapng)

#### CMD 0x20 - Handshake
```
PTU→TIS Request (12B, fixed):
  02 20 00 00 00 00 00 00 00 03 23 00
  (rake_id TIDAK dikirim — packet selalu fixed)

TIS→PTU Response (128B):
  Header  [0-7]:  02 20 00 54 00 80 a0 88
  Payload [8-..]: 60 00 00 FF FF [rake_id] 00 00 ...
                                  ↑
                              payload[5] = rake_id
                              (confirmed TS5: 0x05 = Rake 5)
```

#### CMD 0x32 - Metadata
```
PTU→TIS Request (8B per page):
  02 32 [page] 00 00 03 [0x31-page+1] [page]

TIS→PTU Response (18B per page):
  Header (8B) + Data (8B) + Checksum (2B)
  BUKAN 256B seperti asumsi awal.
```

#### CMD 0x34 - Data Set B
```
PTU→TIS Request (8B per page):
  02 34 [page] [page-1] 00 03 [0x37-page+1] [page]

TIS→PTU Response (26B per page):
  Header (8B) + Data (16B) + Checksum (2B)
```

#### CMD 0x36 - Failure Records
```
PTU→TIS Request (10B per page, 0-based):
  02 36 00 00 00 00 [page] 03 35 [page]

TIS→PTU Response (112B per page):
  Header (8B) + Payload (102B) + Checksum (2B)

Payload structure (102B):
  [0]      Start marker  0x00
  [1..100] 5 records × 20 bytes
  [101]    End marker    0x03

Record structure (20B, confirmed via PCAP TS5 depot + TS13 moving-train + cross-ref CSV PTU asli):
  [0-5]  Timestamp BCD (YY MM DD HH MM SS)
  [6-7]  Location [m] signed int16 big-endian  ✅ CONFIRMED (0x2173=8563m, 0xFFA7=-89m, 0x0000=depot)
  [8]    Status byte:                          ✅ CONFIRMED
           bit[4] = occur/recover (0=Occur, 1=Recover)
           bit[0] = EB asserted (override notch ke EB — wire 657R)
           bit[7] = ??? (set saat train moving; hipotesis)
  [9]    Notch step → decode_notch()           ✅ CONFIRMED
           0x00/0x80 = Neutral (depot/coast)
           0x01..0x10 = A_P1..A_P16 (ATO Power step)
           0x81..0x90 = A_B1..A_B16 (ATO Brake step)
  [10]   Notch mode → decode_notch()           ✅ CONFIRMED
           0x80 = Auto mode (Neutral/A_P/A_B)
           0x40 = Manual Brake (M_B; step encoding belum penuh)
           0x08 = Manual Power (M_P; step encoding belum penuh)
           0x00 = EB-mode resting (kombinasi dengan b8 bit-0)
  [11]   Car ID direct (0x01-0x06 = Car 1-6)
  [12]   Equipment code
  [13]   Fault sub-index
  [14-15] Fault code uint16 big-endian
  [16]   Overhead Voltage raw (× 10 = Volt)
  [17]   Speed [km/h]
  [18-19] Train ID BCD (0xFFFF = depot; e.g. 0x07 0x29 → "0729")
```

## 4. Struktur Data

### 4.1 FailureRecord Dataclass

```python
@dataclass
class FailureRecord:
    block_no: int           # Nomor urut (Block.No di CSV PTU)
    timestamp: datetime     # Waktu failure (BCD decoded)
    car_no: int             # Nomor car (1-6, direct dari byte[11])
    occur_recover: int      # 0=Occur, 1=Recover (dari byte[8] bit-4)
    train_id: int           # Train Set ID (0xFFFF = depot)
    location_m: int         # Posisi di track [m] signed int16 BE (bytes[6-7])
    equipment_code: int     # Kode equipment (1-23)
    fault_sub: int          # Sub-index internal TIS
    fault_code: int         # Kode fault numeric
    status_byte: int        # Raw byte[8] (bit-4=occ/rec, bit-0=EB-asserted)
    notch_step: int         # Raw byte[9] (step level)
    notch_mode: int         # Raw byte[10] (mode: 0x80=Auto, 0x40=M_B, 0x08=M_P)
    speed_kmh: int          # Kecepatan [km/h]
    overhead_v: int         # Tegangan catenary [V] (raw × 10)
    raw_bytes: bytes        # Raw 20B untuk debugging
    # notch_label = property → decode_notch(status_byte, notch_step, notch_mode)
```

### 4.2 ParsedPacket Dataclass

```python
@dataclass
class ParsedPacket:
    cmd: int           # Command byte (0x20, 0x32, dll)
    seq: int           # Sequence number (bytes 2-3)
    page: int          # Page index (bytes 6-7)
    payload: bytes     # Data tanpa header dan checksum
    raw: bytes         # Full raw bytes
    is_heartbeat: bool
    checksum_ok: bool
```

### 4.3 Configuration Classes

```python
@dataclass
class NetworkConfig:
    tis_host: str = "127.0.0.1"
    tis_port: int = 262
    local_port: int = 263
    recv_timeout_sec: float = 3.0
    max_retries: int = 3
    retry_delay_sec: float = 0.5
    recv_buffer_size: int = 4096

@dataclass
class SessionConfig:
    cmd32_pages: int = 6
    cmd34_pages: int = 6
    cmd36_pages: int = 40
    records_per_page: int = 5
    polls_per_page: int = 3
    poll_interval_sec: float = 0.1
    post_handshake_delay_sec: float = 0.1
```

## 5. Flow Komunikasi Detail

### 5.1 Sesi Lengkap

```
1. Initialize UDP Socket
   ├── Bind to local_port (263)
   └── Set timeout & buffer

2. Handshake Phase (CMD 0x20)
   ├── Send fixed 12B packet (NO rake_id in request)
   ├── Wait response 128B
   ├── Extract rake_id dari payload[5]  ← auto-detection
   └── Log [HS] rake_id auto-detected

3. Download Phase
   ├── CMD 0x32: 6 pages metadata (18B each)
   ├── CMD 0x34: 6 pages dataset B (26B each)
   └── CMD 0x36: 40 pages failure records
       ├── Poll each page 3x
       ├── Parse 5 records × 20B per page
       └── Accumulate 200 records

4. Export Phase
   ├── Generate CSV (if enabled)
   ├── Generate PDF (if enabled)
   └── Save raw bytes (if --raw)

5. Upload Phase (if --upload)
   ├── POST JSON payload
   └── Upload CSV/PDF files

6. Cleanup
   └── Close UDP socket
```

### 5.2 Error Handling

- **Network Timeout**: Retry up to 3x dengan delay 0.5s
- **Checksum Invalid**: Discard packet, retry
- **Invalid Response**: Log error, continue to next page
- **Socket Error**: Reinitialize socket, retry
- **Export Failure**: Log error, continue (don't fail session)
- **rake_id = 0**: Exit dengan error jika tidak ada user override

## 6. Output Specifications

### 6.1 CSV Format

```
Name:,MRTJ Failure History(Formation)
RakeID,5
CarID,-
CarNo,-
ReadTime,26-05-07 16:08:16
DataSize,15
DataCount,200
-,
-,  (×10 baris)
,,,,,,,,,,,,,,,,

Block.No,Year,Month,Day,Hour,Minute,Second,CarNo,Train ID,Occur/Recover,
  Location[m],Failure Equipment,Fault Code,Notch,Speed[km/h],Overhead Voltage[V]
0,26,05,07,16,04,07,06,FFFF,0,0,9,806,EB,0,10
...
```

- **Encoding**: UTF-8
- **Filename**: D{YYMMDD}_{rake_id:03d}.csv

### 6.2 PDF Format

- **Page Size**: A4, Portrait
- **Font**: Helvetica 10pt
- **Header**: MRT Jakarta TIS Report - Rake {rake_id}
- **Footer**: Generated by TIS Gateway v1.0

### 6.3 Cloud API Payload

```json
{
  "rake_id": 5,
  "timestamp": "2024-01-01T12:00:00Z",
  "records": [
    {
      "block_no": 0,
      "timestamp": "2026-05-07T16:04:07",
      "car_no": 6,
      "occur_recover": 0,
      "train_id": "FFFF",
      "location_m": 0,
      "equipment_code": 9,
      "fault_code": 806,
      "notch": "EB",
      "speed_kmh": 0,
      "overhead_v": 10
    }
  ]
}
```

## 7. Equipment & Fault Mapping

Semua mapping ada di `config/equipment_map.py`. Sumber data:
1. Chapter 16 TIS Maintenance Manual (SRR-RST-GEN-0104-16D)
2. Attachment 8 — Failure Guidance List of TIS (BH22001)
3. Cross-reference dengan output PTU asli (D260507_282.csv, dudu_ts_5.pdf)

### 7.1 Equipment Codes

| Code | Name           | Fault Range |
|------|----------------|-------------|
| 1    | TIS            | 100–199     |
| 2    | ATO            | 200–299     |
| 3    | VVVF1          | 300–399     |
| 4    | VVVF2          | 300–399     |
| 5    | APS            | 400–499     |
| 6    | BECU           | 500–599     |
| 7    | ACE            | 600–699     |
| 8    | PID            | 700–799     |
| 9    | PA             | 800–899     |
| 10   | DOOR           | 900–999     |
| 11   | VMI            | 1000–1099   |
| 19   | Radio          | 1100–1199   |
| 20   | CCTV           | 1200–1299   |
| 21   | BatteryCharger | 1300–1399   |
| 22   | Compressor     | 1400–1499   |
| 23   | DataRecorder   | 1500–1599   |

### 7.2 Fault Codes

125+ fault codes tersimpan sebagai `FaultInfo(abbrev, description, confidence)`.

```python
class FaultInfo(NamedTuple):
    abbrev: str       # Kode singkat, e.g. "ESA", "LBVRS1F"
    description: str  # Deskripsi lengkap dari manual
    confidence: str   # "C"=Confirmed, "E"=Extracted, "R"=Range-only
```

### 7.3 Failure Guidance & Classification

```python
get_failure_guidance(fault_code)    # → string instruksi untuk driver/OCC
get_fault_classification(fault_code) # → "Heavy" | "Light" | "Info"
```

## 8. Logging & Monitoring

### 8.1 Log Format

Log menggunakan prefix bracket `[TAG]` agar mudah di-grep dan dikirim ke Claude untuk analisa.

```
[SESSION_START] host=192.168.1.100:262 local=263 rake_id=auto-detect
[HS] Kirim handshake CMD 0x20...
[HS_RAW] len=128 hex=0220005400...
[HS] rake_id auto-detected = 5 (payload[5]=0x05)
[HS] OK — response 128B checksum_ok=True
[CMD32] Metadata 6 pages...
[CMD32] selesai 6/6 pages OK
[CMD34] Dataset B 6 pages...
[CMD34] selesai 6/6 pages OK
[CMD36] Failure records 40 pages × 3 polls...
[CMD36] page=0x00 records=5 blk=1..5
...
[CMD36] selesai — total 200 records dari 40/40 pages
[SESSION_RESULT] success=True rake_id=5(auto) discovered=5 records=200 pages=40 duration=12.3s error=none
[MAIN] CSV: ./output/D260507_005.csv
[DONE] rake_id=5 records=200 files=2
```

### 8.2 Debug Mode

Jalankan dengan `LOG_LEVEL=DEBUG` untuk output per-record:

```
[REC] blk=0 ts=260507_160407 car=6 occ=0 tid=FFFF loc=0 eq=9 fault=806 notch=EB spd=0 ov=10 | raw=2605071604070000010000060905032601...
```

### 8.3 Performance Metrics

- **Session Duration**: Total time dari handshake sampai cleanup
- **Packet Success Rate**: Valid responses / total attempts
- **Retry Count**: Average retries per page

## 9. Testing Strategy

### 9.1 Unit Tests

- **parsers/test_parser.py**: Test BCD decoding, record parsing
- **exporter/test_exporter.py**: Test CSV/PDF generation
- **protocol/test_commands.py**: Test packet building

### 9.2 Integration Tests

- **tests/mock_tis.py**: Mock UDP server dengan responses valid
- **End-to-end**: Full session dengan mock server

## 10. Deployment & Operations

### 10.1 Environment Variables

```bash
TIS_HOST=192.168.1.100
TIS_PORT=262
LOCAL_PORT=263
OUTPUT_DIR=/data/tis_output
CLOUD_API_URL=https://api.mrtjkt.com/tis
TIS_API_KEY=secret-key-here
LOG_LEVEL=INFO
```

### 10.2 Operasional Harian

```bash
# Normal — rake_id auto-detect dari TIS
python main.py

# Jika auto-detect gagal
python main.py --rake-id 5

# Debug full
LOG_LEVEL=DEBUG python main.py --raw
```

---

## 11. GAP TRACKING — Field Confirmation Status

Status konfirmasi setiap field ditetapkan dari analisa PCAP `dudu_sniffing_tis_ts5.pcapng`
(TS5, kereta di depo, 200 records) vs output PTU asli `D260507_282.csv`.

Legend: ✅ Confirmed  ⚠ Best Guess (belum konfirmasi kereta jalan)  ❌ Bug  🔲 Unknown

### 11.1 Protokol

| Item | Status | Catatan |
|------|--------|---------|
| Handshake packet (PTU→TIS) | ✅ | Fixed 12B, tanpa rake_id |
| Handshake response (TIS→PTU) | ✅ | 128B; payload[5] = rake_id |
| CMD 0x32 response size | ✅ | 18B (bukan 256B seperti asumsi awal) |
| CMD 0x34 response size | ✅ | 26B |
| CMD 0x36 response size | ✅ | 112B; payload 102B |
| Record size per slot | ✅ | 20B (bukan 18B; FF FF = train_id, bukan separator) |
| Pages per command | ⚠ | 6/6/40 dari TS5; belum konfirmasi trainset lain |

### 11.2 Record Fields

| Byte | Field | Status | Catatan |
|------|-------|--------|---------|
| [0-5] | Timestamp BCD | ✅ | Cross-ref CSV: 2026-05-07 16:04:07 ✓ |
| [6-7] | Location [m] int16 BE | ✅ | TS13 2026-06-05: 0x2173=8563m ✓, 0xFFA7=-89m ✓, 0x0000=depot ✓ — diverifikasi 196/196 record |
| [8] bit-4 | Occur/Recover | ✅ | (byte>>4)&1: 0x00→0(Occur) ✓, 0x11→1(Recover) ✓ |
| [8] bit-0 | EB asserted (override) | ✅ | TS13: bit-0=1 → notch=EB regardless of [9]/[10] (wire 657R Emergency Brake) |
| [8] bit-7 | Moving flag (hipotesis) | ⚠ | Set saat train bergerak (lokasi != 0); belum dipakai parser |
| [9] | Notch step | ✅ | 0x00/0x80=Neutral, 0x01..0x10=A_Pn, 0x81..0x90=A_Bn — TS13 verified |
| [10] | Notch mode | ✅ | 0x80=Auto, 0x40=Manual Brake, 0x08=Manual Power, 0x00=EB-mode — TS13 verified |
| [11] | Car ID | ✅ | Direct value 0x01-0x06 = Car 1-6, cross-ref CSV ✓ |
| [12] | Equipment code | ✅ | 0x09=PA ✓, 0x08=PID ✓, 0x02=ATO ✓, 0x14=CCTV ✓ |
| [13] | Fault sub-index | 🔲 | Ikut dalam output tapi belum di-validate dari manual |
| [14-15] | Fault code uint16 | ✅ | 0x0326=806 ✓, 0x02BC=700 ✓, 0x00D4=212 ✓ |
| [16] | Overhead V raw | ✅ | ×10 = Volt: 0x01→10V ✓, 0x08→80V ✓ |
| [17] | Speed [km/h] | ✅ | 0x00=0 untuk depot ✓ |
| [18-19] | Train ID BCD | ✅ | 0xFFFF=depot ✓; 0x07 0x29→"0729" ✓; 0x08 0x02→"0802" ✓ |

### 11.3 Prioritas Konfirmasi Berikutnya

Setelah TS13 2026-06-05 (moving train, 200 records vs PTU asli) → semua field utama sudah ✅.
Sisa yang perlu PCAP tambahan:

1. **Manual notch step >1** — TS13 hanya lihat M_B1 dan M_P1. Encoding step 2-7 (brake) dan 2-4 (power) belum dikonfirmasi
2. **byte[8] bit-7** — hipotesis "moving flag"; perlu konfirmasi semantiknya
3. **Fault sub-index [13]** — selalu 0x0D di sample yang ada; perlu manual cross-ref

### 11.4 Fitur yang Belum Diimplementasi

| Fitur | Priority | Notes |
|-------|----------|-------|
| Manual notch step >1 mapping | Low | TS13 cuma confirm step 1; tunggu PCAP dengan manual driving aktif |
| CMD 0x32 metadata parsing | Low | Data didownload tapi tidak diparse |
| CMD 0x34 dataset B parsing | Low | Data didownload tapi tidak diparse |
| Mock TIS yang realistic | High | Untuk testing tanpa kereta fisik |
| Duplicate session detection | Medium | TS13 output punya record ganda di blk 135+ (GAP-4); investigasi root cause |

---

## 12. Security Considerations

### 12.1 Network Security

- **Firewall**: Restrict UDP traffic to known IPs
- **VLAN**: Isolate TIS network segment

### 12.2 Data Protection

- **Encryption**: TLS 1.3 untuk cloud upload
- **Access Control**: API key via environment variable `TIS_API_KEY`
- **Audit Logging**: All operations logged

## 13. Future Enhancements

- **Web Dashboard**: GUI untuk monitoring dan control
- **Database Integration**: Persistent storage untuk trend analysis
- **Real-time Monitoring**: Continuous polling mode
- **Multi-rake**: Parallel processing jika ada multiple TIS units
