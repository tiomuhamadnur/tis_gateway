# TIS Gateway — ESP32 Firmware

Firmware Arduino untuk ESP32 yang berfungsi sebagai hardware gateway antara
**Train Information System (TIS) Sumitomo CP108** dengan **CMS (Laravel)**.

---

## Daftar Isi

1. [Arsitektur Sistem](#1-arsitektur-sistem)
2. [Hardware Requirements](#2-hardware-requirements)
3. [Wiring Diagram](#3-wiring-diagram)
4. [Library yang Dibutuhkan](#4-library-yang-dibutuhkan)
5. [Konfigurasi Firmware](#5-konfigurasi-firmware)
6. [Alur Data (Data Flow)](#6-alur-data-data-flow)
7. [Logic Detail](#7-logic-detail)
8. [Format JSON ke CMS](#8-format-json-ke-cms)
9. [SD Card — Retry Buffer](#9-sd-card--retry-buffer)
10. [CMS API Endpoint Specification](#10-cms-api-endpoint-specification)
11. [Troubleshooting](#11-troubleshooting)
12. [Known Limitations & Field Status](#12-known-limitations--field-status)

---

## 1. Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────┐
│                     ESP32                               │
│                                                         │
│  ┌──────────────┐   SPI    ┌─────────────┐             │
│  │  W5500       │◄────────►│   ESP32     │             │
│  │  USR-ES1     │          │   MCU       │             │
│  └──────┬───────┘          └──────┬──────┘             │
│         │ Ethernet                │ WiFi               │
│         │ (LAN TIS)               │ (LAN CMS)          │
└─────────┼─────────────────────────┼────────────────────┘
          │                         │
          ▼                         ▼
  ┌───────────────┐         ┌───────────────┐
  │  TIS Server   │         │  CMS Laravel  │
  │  Sumitomo     │         │  (Laravel API)│
  │  CP108        │         │               │
  │  UDP :262     │         │  POST /api/   │
  └───────────────┘         │  tis/records  │
                            └───────────────┘

  ┌──────────────┐
  │  MicroSD     │  ← buffer sementara jika WiFi/CMS down
  │  (SPI)       │     retry otomatis saat koneksi pulih
  └──────────────┘
```

**Prinsip kerja:**
- **W5500 (Ethernet)** terhubung ke jaringan TIS (kabel LAN) — menjalankan protokol UDP proprietary Sumitomo
- **WiFi built-in ESP32** terhubung ke jaringan CMS — mengirim data JSON via HTTP POST
- **MicroSD** menyimpan batch yang gagal dikirim, di-retry saat koneksi pulih
- Kedua antarmuka jaringan **aktif bersamaan** di subnet berbeda

---

## 2. Hardware Requirements

| Komponen | Spesifikasi | Catatan |
|---|---|---|
| Mikrokontroler | ESP32 (38-pin atau 30-pin) | Pastikan ada pin SPI (GPIO 18/19/23) |
| Ethernet Module | **W5500 USR-ES1** (Mini SPI to LAN) | 3.3V logic, hardwired MAC |
| MicroSD Module | Generic SPI MicroSD adapter | 3.3V VCC atau dengan regulator |
| MicroSD Card | Kapasitas bebas (min 128MB) | Format FAT32 |
| Kabel LAN | Cat5e/Cat6 RJ45 | Menghubungkan W5500 ke switch TIS |
| Power | 3.3V stabil | Gunakan regulator jika dari 5V USB |

---

## 3. Wiring Diagram

### 3.1 Overview Koneksi

```
ESP32                    W5500 USR-ES1
─────                    ─────────────
3.3V  ────────────────►  VCC
GND   ────────────────►  GND
GPIO18 (SCK)  ────────►  SCK
GPIO19 (MISO) ────────►  MISO (SO)
GPIO23 (MOSI) ────────►  MOSI (SI)
GPIO5  (CS)   ────────►  CS  (NSS)
GPIO26 (INT)  ◄────────  INT
GPIO27 (RST)  ────────►  RST


ESP32                    MicroSD Module
─────                    ─────────────
3.3V  ────────────────►  VCC  (atau 5V jika ada regulator di modul)
GND   ────────────────►  GND
GPIO18 (SCK)  ────────►  SCK  (shared bus)
GPIO19 (MISO) ────────►  MISO (shared bus)
GPIO23 (MOSI) ────────►  MOSI (shared bus)
GPIO4  (CS)   ────────►  CS
```

### 3.2 Tabel Pin Lengkap

| ESP32 GPIO | Fungsi | Terhubung ke | Catatan |
|---|---|---|---|
| 3.3V | Power | W5500 VCC, SD VCC | Jangan pakai 5V — W5500 tidak toleran |
| GND | Ground | W5500 GND, SD GND | Common ground |
| GPIO 18 | SPI SCK | W5500 SCK, SD SCK | Shared SPI clock |
| GPIO 19 | SPI MISO | W5500 MISO, SD MISO | Shared MISO |
| GPIO 23 | SPI MOSI | W5500 MOSI, SD MOSI | Shared MOSI |
| GPIO 5 | W5500 CS | W5500 CS (NSS) | Chip Select W5500 |
| GPIO 4 | SD CS | SD CS | Chip Select SD |
| GPIO 26 | W5500 INT | W5500 INT | Interrupt (opsional, belum dipakai aktif) |
| GPIO 27 | W5500 RST | W5500 RST | Hardware reset W5500 |

### 3.3 Diagram ASCII Lengkap

```
                         ┌─────────────────────┐
                         │       ESP32          │
                         │                      │
             3.3V ───────┤ 3V3                  │
              GND ───────┤ GND                  │
                         │                      │
    W5500 CS ────────────┤ GPIO5   GPIO23 ──────┼─── MOSI ─┬─ W5500
    SD   CS ─────────────┤ GPIO4   GPIO19 ──────┼─── MISO ─┤  USR-ES1
                         │         GPIO18 ──────┼─── SCK  ─┤
    W5500 INT ───────────┤ GPIO26               │          └─ MicroSD
    W5500 RST ───────────┤ GPIO27               │
                         │                      │
                         │         [WiFi Antenna]│──── 2.4GHz → CMS
                         └─────────────────────┘
```

> **Penting:** Pastikan tidak ada dua modul yang menarik CS secara bersamaan.
> Firmware sudah menginisialisasi kedua CS pin ke HIGH sebelum SPI aktif.

---

## 4. Library yang Dibutuhkan

Install semua library berikut melalui **Arduino Library Manager** (`Sketch > Include Library > Manage Libraries`):

| Library | Author | Versi Minimum | Fungsi |
|---|---|---|---|
| `Ethernet_Generic` | Khoi Hoang | 2.7.0+ | Driver W5500 untuk ESP32 |
| `ArduinoJson` | Benoit Blanchon | 6.21.0+ | Serialize JSON payload |
| `SD` | Arduino | Built-in ESP32 | Akses MicroSD via SPI |

Library bawaan yang sudah tersedia di ESP32 Arduino Core:
- `SPI` — bus komunikasi hardware
- `WiFi` — koneksi WiFi built-in
- `HTTPClient` — HTTP POST ke CMS

### Instalasi Board ESP32

Jika belum, tambahkan ESP32 board di Arduino IDE:
1. File → Preferences → Additional Board Manager URLs
2. Tambahkan: `https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json`
3. Tools → Board → Board Manager → cari "esp32" → Install

### Board Setting yang Disarankan

| Setting | Value |
|---|---|
| Board | ESP32 Dev Module |
| Upload Speed | 921600 |
| CPU Frequency | 240MHz |
| Flash Size | 4MB |
| Partition Scheme | Default 4MB |
| PSRAM | Disabled |

---

## 5. Konfigurasi Firmware

Buka `tis_gateway.ino` dan ubah bagian **`[USER CONFIG]`**:

```cpp
// --- PIN (sesuaikan jika menggunakan wiring berbeda) ---
#define PIN_W5500_CS     5
#define PIN_W5500_INT    26
#define PIN_W5500_RST    27
#define PIN_SD_CS        4

// --- Ethernet: IP ESP32 di jaringan TIS ---
IPAddress ETH_LOCAL_IP(192, 168, 1, 200);   // ← sesuaikan subnet TIS
IPAddress TIS_IP      (192, 168, 1,   1);   // ← IP server TIS

// --- WiFi: jaringan yang bisa akses CMS ---
const char* WIFI_SSID     = "YOUR_SSID";
const char* WIFI_PASSWORD = "YOUR_PASSWORD";

// --- Endpoint CMS ---
const char* CMS_API_URL   = "http://192.168.x.x/api/tis/records";
const char* CMS_API_KEY   = "";   // isi jika pakai Bearer token

// --- Interval antar sesi ---
#define SESSION_INTERVAL_MS  (5UL * 60UL * 1000UL)   // default: 5 menit
```

> **IP Address:** W5500 mencoba DHCP terlebih dahulu. Jika DHCP gagal,
> otomatis menggunakan `ETH_LOCAL_IP` sebagai static IP.
> Pastikan static IP tidak konflik dengan device lain di jaringan TIS.

---

## 6. Alur Data (Data Flow)

```
setup()
  │
  ├─ Init SPI bus
  ├─ Hardware reset W5500
  ├─ Init Ethernet (W5500) → DHCP atau static IP
  ├─ Init SD card → cek/buat folder /retry
  ├─ Connect WiFi
  └─ retryFromSD() → flush batch pending dari sesi sebelumnya
        │
loop() ─┘
  │
  ├─ connectWiFi() jika putus
  │
  ├─ runTISSession()
  │     │
  │     ├─ [1] udp.begin(263) — buka socket UDP lokal
  │     │
  │     ├─ [2] runHandshake() — CMD 0x20
  │     │       Send: 12B fixed packet
  │     │       Recv: 128B response
  │     │       Extract: rake_id dari byte[13] (header 8B + payload[5])
  │     │
  │     ├─ [3] runCmd32() — CMD 0x32 Metadata
  │     │       Loop 6 pages, 3 polls each
  │     │       Data didownload tapi tidak diparse (tidak dibutuhkan)
  │     │
  │     ├─ [4] runCmd34() — CMD 0x34 Dataset B
  │     │       Loop 6 pages, 3 polls each
  │     │       Data didownload tapi tidak diparse (tidak dibutuhkan)
  │     │
  │     └─ [5] runCmd36() — CMD 0x36 Failure Records
  │             Loop 40 pages:
  │               ├─ Send 10B request dengan page number
  │               ├─ Recv 112B response
  │               │     Header  (8B)
  │               │     Payload (102B): [0x00] + 5×20B records + [0x03]
  │               │     Checksum (2B)
  │               ├─ Parse 5 records → FailureRecord[]
  │               ├─ buildJson() → JSON string
  │               ├─ postToCMS() → HTTP POST
  │               └─ jika gagal → saveToSD(page)
  │
  ├─ retryFromSD()
  │     ├─ Buka dir /retry di SD
  │     ├─ Untuk setiap file .json:
  │     │     ├─ Read content
  │     │     ├─ postToCMS()
  │     │     └─ jika sukses → hapus file
  │     └─ Report: N/M batches flushed
  │
  └─ delay(SESSION_INTERVAL_MS) — tunggu sebelum sesi berikutnya
        (sambil cek WiFi setiap 5 detik)
```

---

## 7. Logic Detail

### 7.1 Handshake (CMD 0x20)

```
PTU → TIS:  02 20 00 00 00 00 00 00 00 03 23 00  (12B fixed)
TIS → PTU:  [Header 8B] [Payload 120B]
             └── payload[5] = rake_id (byte index 13 total)
```

- Firmware mengirim packet fixed, tidak ada variabel di dalamnya
- `rake_id` di-extract otomatis dari response byte ke-13
- Jika `rake_id == 0`, sesi dibatalkan (abort)

### 7.2 CMD 0x32 & 0x34 — Metadata & Dataset B

- Loop 6 halaman masing-masing
- Firmware **hanya mengakui** response (ACK) tanpa mem-parse isinya
- Diperlukan untuk menjaga state sesi TIS agar CMD 0x36 bisa berjalan

### 7.3 CMD 0x36 — Failure Records

Ini adalah loop utama pengambilan data:

```
Per page (40 pages total):
  ┌─ Build packet: 02 36 00 00 00 00 [page] 03 35 [page]
  ├─ Kirim ke TIS_IP:262 dari local port 263
  ├─ Terima 112B response:
  │     [0-7]    Header
  │     [8]      Start marker 0x00
  │     [9-108]  5 records × 20B
  │     [109]    End marker 0x03
  │     [110-111] Checksum
  ├─ Validasi marker 0x00 dan 0x03
  ├─ Parse 5 records
  ├─ Build JSON
  ├─ POST ke CMS
  └─ Jika POST gagal → simpan ke SD /retry/r{rake}_p{page}.json
```

Retry per-poll: max 3 polls per page dengan interval 100ms.
Retry per-packet (UDP): max 1 attempt per poll (total 3 attempts).

### 7.4 Record Parsing (20 Bytes per Record)

```
Offset  Field              Decode
──────  ─────────────────  ──────────────────────────────────
[0]     Year (BCD)         bcdDecode: 0x26 → 26
[1]     Month (BCD)        bcdDecode: 0x05 → 5
[2]     Day (BCD)          bcdDecode: 0x07 → 7
[3]     Hour (BCD)         bcdDecode: 0x16 → 16
[4]     Minute (BCD)       bcdDecode: 0x04 → 4
[5]     Second (BCD)       bcdDecode: 0x07 → 7
[6]     Unknown            skip
[7]     Notch byte         raw (⚠ best-guess)
[8]     Status             hi-nibble >> 4 = occur(0)/recover(1)
[9-10]  Location [m]       int16 big-endian
[11]    Car ID             direct (0x01-0x06 = Car 1-6)
[12]    Equipment code     direct (1-23)
[13]    Fault sub-index    direct
[14-15] Fault code         uint16 big-endian
[16]    Overhead V raw     × 10 = Volt
[17]    Speed [km/h]       direct
[18-19] Train ID           uint16 big-endian (0xFFFF = depot)
```

### 7.5 Checksum

Algoritma yang diimplementasikan: **sum semua byte kecuali 2 byte terakhir**,
dibandingkan dengan 2 byte terakhir sebagai uint16 big-endian.

> ⚠ Algoritma ini **belum dikonfirmasi** dari PCAP. Jika `checksum=WARN`
> selalu muncul di Serial monitor tapi data benar, checksum tidak kritis
> untuk operasi (data tetap diparse). Adjust di fungsi `verifyChecksum()`
> jika algoritma berbeda ditemukan.

### 7.6 SPI Bus Sharing

W5500 dan MicroSD berbagi bus SPI yang sama (MOSI/MISO/SCK).
Kedua library mengelola CS masing-masing secara internal.
Firmware menginisialisasi kedua CS ke HIGH sebelum SPI aktif:

```cpp
pinMode(PIN_W5500_CS, OUTPUT); digitalWrite(PIN_W5500_CS, HIGH);
pinMode(PIN_SD_CS,    OUTPUT); digitalWrite(PIN_SD_CS,    HIGH);
SPI.begin();
```

Tidak ada konflik selama tidak ada transaksi SPI yang dibuka bersamaan
(keduanya berjalan sequentially di single-core loop).

### 7.7 Session Timing

```
Estimasi waktu 1 sesi penuh (40 pages):

Handshake           :   ~3s  (1 round-trip + delay)
CMD 0x32 (6 pages)  :   ~6s  (6 × 1 round-trip × 3 polls max)
CMD 0x34 (6 pages)  :   ~6s
CMD 0x36 (40 pages) :   ~40-60s  (40 × 3 polls + HTTP POST per page)
                         ──────
Total               :   ~55-75s per sesi

SESSION_INTERVAL_MS default = 5 menit → interval antar sesi ~4 menit idle
```

---

## 8. Format JSON ke CMS

Firmware mengirim **satu HTTP POST per page (5 records)**, bukan semua 200 records sekaligus.
Ini lebih hemat RAM dan lebih fault-tolerant.

### 8.1 Payload per POST

```json
{
  "rake_id": 5,
  "timestamp": "2026-05-07T16:04:07",
  "records": [
    {
      "block_no": 1,
      "timestamp": "2026-05-07T16:04:07",
      "car_no": 6,
      "occur_recover": 0,
      "train_id": "FFFF",
      "location_m": 0,
      "equipment_code": 9,
      "fault_sub": 3,
      "fault_code": 806,
      "notch_byte": 0,
      "speed_kmh": 0,
      "overhead_v": 10
    }
    // ... 4 records lainnya
  ]
}
```

### 8.2 Field Descriptions

| Field | Type | Keterangan |
|---|---|---|
| `rake_id` | int | Nomor rake/trainset (auto-detect dari TIS) |
| `timestamp` | string ISO8601 | Timestamp record pertama di batch ini |
| `records[].block_no` | int | Nomor urut record (1-200 per sesi) |
| `records[].timestamp` | string ISO8601 | Waktu failure/recover |
| `records[].car_no` | int | Nomor car (1-6) |
| `records[].occur_recover` | int | 0=Occur, 1=Recover |
| `records[].train_id` | string | "FFFF"=depot, angka=Train Set ID |
| `records[].location_m` | int | Posisi di track [meter], signed |
| `records[].equipment_code` | int | Kode equipment (1-23) |
| `records[].fault_sub` | int | Sub-index fault internal TIS |
| `records[].fault_code` | int | Kode fault numeric |
| `records[].notch_byte` | int | Raw notch byte (⚠ interpretasi belum final) |
| `records[].speed_kmh` | int | Kecepatan [km/h] |
| `records[].overhead_v` | int | Tegangan catenary [Volt] |

---

## 9. SD Card — Retry Buffer

### 9.1 Struktur File

```
SD Card Root/
└── retry/
    ├── r005_p00.json    ← rake 5, page 0 (5 records)
    ├── r005_p01.json    ← rake 5, page 1
    └── r005_p07.json    ← dst.
```

Nama file: `r{rake:03d}_p{page:02d}.json`

### 9.2 Retry Flow

```
Saat sesi selesai:
  retryFromSD()
    ├─ Buka /retry directory
    ├─ Untuk setiap .json file:
    │     ├─ Baca isi file ke RAM
    │     ├─ Coba POST ke CMS
    │     ├─ Sukses → hapus file dari SD
    │     └─ Gagal → biarkan, retry di sesi berikutnya
    └─ Report: N/M files flushed
```

### 9.3 Kapasitas & Estimasi

Worst case (WiFi down total, 1 sesi = 40 files):
- Per file: ~800 bytes JSON (5 records)
- 40 files: ~32KB per sesi
- SD 1GB: bisa menampung ribuan sesi

Retry terjadi setiap awal **dan** akhir sesi. Begitu WiFi/CMS pulih,
semua pending data otomatis ter-flush tanpa intervensi manual.

---

## 10. CMS API Endpoint Specification

### 10.1 Endpoint

```
POST /api/tis/records
Content-Type: application/json
Accept: application/json
Authorization: Bearer {CMS_API_KEY}   (jika diaktifkan)
```

### 10.2 Response yang Diharapkan

| HTTP Code | Artinya | Aksi Firmware |
|---|---|---|
| 200 OK | Data diterima | ✅ Lanjut ke page berikutnya |
| 201 Created | Data diterima | ✅ Lanjut ke page berikutnya |
| 4xx Client Error | Payload salah | ⚠ Tetap disimpan ke SD (bisa jadi bug firmware) |
| 5xx Server Error | CMS error | ⚠ Simpan ke SD, retry sesi berikutnya |
| Timeout / No response | WiFi/network down | ⚠ Simpan ke SD, retry sesi berikutnya |

### 10.3 Contoh Laravel Route

```php
// routes/api.php
Route::post('/tis/records', [TisRecordController::class, 'store']);
```

```php
// app/Http/Controllers/TisRecordController.php
public function store(Request $request)
{
    $validated = $request->validate([
        'rake_id'   => 'required|integer',
        'timestamp' => 'required|string',
        'records'   => 'required|array|min:1',
        'records.*.block_no'       => 'required|integer',
        'records.*.timestamp'      => 'required|string',
        'records.*.car_no'         => 'required|integer',
        'records.*.occur_recover'  => 'required|integer|in:0,1',
        'records.*.fault_code'     => 'required|integer',
        // ... dst.
    ]);

    foreach ($validated['records'] as $rec) {
        TisRecord::create([
            'rake_id'        => $validated['rake_id'],
            'block_no'       => $rec['block_no'],
            'recorded_at'    => $rec['timestamp'],
            'car_no'         => $rec['car_no'],
            'occur_recover'  => $rec['occur_recover'],
            'fault_code'     => $rec['fault_code'],
            // ... dst.
        ]);
    }

    return response()->json(['status' => 'ok', 'count' => count($validated['records'])], 201);
}
```

---

## 11. Troubleshooting

### ETH init gagal / IP 0.0.0.0

- Periksa wiring CS, SCK, MISO, MOSI ke W5500
- Pastikan W5500 mendapat 3.3V (bukan 5V)
- Cek RST pin: firmware melakukan hardware reset 100ms sebelum init
- Pastikan library `Ethernet_Generic` terinstall, bukan `Ethernet` default

### rake_id selalu 0

- TIS tidak merespon handshake
- Cek IP TIS di konfigurasi: `TIS_IP`
- Pastikan ESP32 (via W5500) dan TIS berada di subnet yang sama
- Cek apakah ada firewall/VLAN yang memblokir UDP port 262/263

### WiFi tidak konek

- Periksa SSID dan password
- ESP32 hanya support WiFi 2.4GHz (tidak support 5GHz)
- Pastikan access point tidak memblokir client baru

### SD tidak terdeteksi

- Periksa wiring CS SD (GPIO 4)
- Format SD sebagai FAT32
- Pastikan tidak ada konflik CS: kedua CS harus HIGH saat SPI init
- Firmware tetap berjalan tanpa SD, tapi batch yang gagal POST akan hilang

### HTTP code 0 / Connection refused

- Periksa `CMS_API_URL` — pastikan IP dan port benar
- Pastikan CMS bisa diakses dari jaringan WiFi yang dipakai
- Cek apakah Laravel running dan endpoint sudah terdaftar di routes

### Data tidak ter-parse dengan benar (timestamp aneh)

- Firmware menggunakan BCD decode untuk timestamp
- Jika timestamp output aneh (misal tahun 38 padahal 2026), kemungkinan
  TIS mengirim BCD tapi ada byte yang bukan BCD-valid → cek raw bytes

### Serial Monitor output

Buka Serial Monitor di Arduino IDE (`Ctrl+Shift+M`), baud rate **115200**.

Contoh output normal:

```
========== TIS Gateway ESP32 v1.0 ==========
[ETH] Init... DHCP 192.168.1.200
[SD] Init... OK
[WIFI] Connecting to MyWiFi........
[WIFI] Connected — IP: 192.168.10.55
[SD_RETRY] Checking pending batches...

[SESSION_START] host=192.168.1.1:262 local=263
[HS] Kirim CMD 0x20...
[HS] OK — rake_id=5 checksum=OK
[CMD32] Metadata 6 pages...
[CMD32] 6/6 pages OK
[CMD34] Dataset B 6 pages...
[CMD34] 6/6 pages OK
[CMD36] Failure records 40 pages × 3 polls...
[CMD36] page=0x00 blk=1..5
[HTTP] OK code=201
[CMD36] page=0x01 blk=6..10
[HTTP] OK code=201
...
[CMD36] selesai — total 200 records
[SESSION_RESULT] rake_id=5 records=200 duration=62.4s
[LOOP] Next session in 300 sec
```

---

## 12. Known Limitations & Field Status

Status konfirmasi field mengacu pada analisa PCAP `dudu_sniffing_tis_ts5.pcapng`
(TS5, kereta di depo, 200 records).

| Field | Status | Catatan |
|---|---|---|
| Timestamp BCD | ✅ Confirmed | Cross-ref CSV PTU asli ✓ |
| rake_id (byte[13]) | ✅ Confirmed | payload[5] = 0x05 = Rake 5 ✓ |
| Car ID (byte[11]) | ✅ Confirmed | Direct 1-6 ✓ |
| Equipment code (byte[12]) | ✅ Confirmed | 0x09=PA, 0x02=ATO ✓ |
| Fault code (byte[14-15]) | ✅ Confirmed | 0x0326=806 ✓ |
| Occur/Recover (byte[8] hi-nibble) | ✅ Confirmed | 0=Occur, 1=Recover ✓ |
| Overhead Voltage (byte[16]) | ✅ Confirmed | ×10=Volt ✓ |
| Speed (byte[17]) | ✅ Confirmed | 0=0 di depot ✓ |
| Train ID (byte[18-19]) | ✅ Confirmed | 0xFFFF=depot ✓ |
| Notch byte (byte[7]) | ⚠ Best-guess | 0x00=EB saat depot; belum ada data moving train |
| Location [m] (byte[9-10]) | ⚠ Best-guess | 0=depot ✓; sign & scale factor belum dikonfirmasi |
| Checksum algorithm | ⚠ Best-guess | Tidak kritis untuk operasi, hanya logging |
| byte[6] | 🔲 Unknown | Selalu 0x00 di depot |

**Data yang dibutuhkan untuk konfirmasi field ⚠:**
Jalankan firmware saat kereta dalam service (moving), capture Serial Monitor output,
dan cross-ref dengan output PTU asli dari session yang sama.

---

*Firmware version: 1.0 | Protocol reference: TIS Sumitomo CP108 | BLUEPRINT.md rev: 2026-05*
