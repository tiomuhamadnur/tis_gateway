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
Byte 0-1: Command ID (big-endian)
Byte 2-3: Packet Length (big-endian)
Byte 4-5: Sequence Number (big-endian)
Byte 6-7: Checksum (CRC-16-CCITT)
```

#### Command Types
- `0x20`: Handshake
- `0x32`: Metadata Request
- `0x34`: Data Set B Request
- `0x36`: Failure Records Request

### 3.3 Command Packets

#### CMD 0x20 - Handshake
```
Request:  8 bytes header + 4 bytes data (rake_id)
Response: 8 bytes header + ACK/NACK
```

#### CMD 0x32 - Metadata
```
Request:  8 bytes header + 4 bytes (rake_id + page_num)
Response: 8 bytes header + 256 bytes metadata
Pages: 6 pages total
```

#### CMD 0x34 - Data Set B
```
Request:  8 bytes header + 4 bytes (rake_id + page_num)
Response: 8 bytes header + 256 bytes data
Pages: 6 pages total
```

#### CMD 0x36 - Failure Records
```
Request:  8 bytes header + 4 bytes (rake_id + page_num)
Response: 8 bytes header + 256 bytes (5 records × 51 bytes + padding)
Pages: 40 pages total (200 records)
```

## 4. Struktur Data

### 4.1 FailureRecord Dataclass

```python
@dataclass
class FailureRecord:
    timestamp: datetime       # Waktu failure (BCD decoded)
    equipment_code: int       # Kode equipment (0-255)
    fault_code: int          # Kode fault (0-255)
    car_number: int          # Nomor car (1-6)
    notch_level: int         # Level notch (0-4)
    speed: int               # Kecepatan (km/h)
    voltage: float           # Voltage (V)
    current: float           # Current (A)
    temperature: int         # Temperature (°C)
    raw_bytes: bytes         # Raw data untuk debugging
```

### 4.2 ParsedPacket Dataclass

```python
@dataclass
class ParsedPacket:
    command: int
    length: int
    sequence: int
    checksum: int
    data: bytes
    is_valid: bool
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

2. Handshake Phase
   ├── Send CMD 0x20 (1 attempt)
   ├── Wait response (timeout 3s)
   ├── Validate checksum
   └── Extract session parameters

3. Download Phase
   ├── CMD 0x32: 6 pages metadata
   │   ├── Poll each page 3x
   │   ├── Validate each response
   │   └── Accumulate metadata
   │
   ├── CMD 0x34: 6 pages data set B
   │   ├── Poll each page 3x
   │   ├── Validate each response
   │   └── Accumulate data
   │
   └── CMD 0x36: 40 pages failure records
       ├── Poll each page 3x
       ├── Parse 5 records per page
       ├── Validate checksums
       └── Accumulate 200 records

4. Export Phase
   ├── Generate CSV (if enabled)
   ├── Generate PDF (if enabled)
   └── Save raw bytes (if debug)

5. Upload Phase (if enabled)
   ├── Prepare JSON payload
   ├── POST to cloud API
   └── Retry on failure

6. Cleanup
   └── Close UDP socket
```

### 5.2 Error Handling

- **Network Timeout**: Retry up to 3x dengan delay 0.5s
- **Checksum Invalid**: Discard packet, retry
- **Invalid Response**: Log error, continue to next page
- **Socket Error**: Reinitialize socket, retry
- **Export Failure**: Log error, continue (don't fail session)

## 6. Output Specifications

### 6.1 CSV Format

```
Timestamp,Equipment,Fault,Car,Notch,Speed,Voltage,Current,Temperature
2024-01-01 12:00:00,TCU,OVERCURRENT,3,2,45,750.5,150.2,65
...
```

- **Encoding**: UTF-8
- **Delimiter**: Comma
- **Header**: Yes
- **Filename**: D{rake_id}{date}{time}.csv

### 6.2 PDF Format

- **Page Size**: A4
- **Orientation**: Portrait
- **Font**: Helvetica 10pt
- **Table**: Equipment, Fault, Car, Time, Speed, Voltage, Current, Temp
- **Header**: MRT Jakarta TIS Report - Rake {rake_id}
- **Footer**: Generated by TIS Gateway v1.0

### 6.3 Cloud API Payload

```json
{
  "rake_id": 5,
  "timestamp": "2024-01-01T12:00:00Z",
  "records": [
    {
      "timestamp": "2024-01-01T12:00:00Z",
      "equipment_code": 1,
      "fault_code": 10,
      "car_number": 3,
      "notch_level": 2,
      "speed": 45,
      "voltage": 750.5,
      "current": 150.2,
      "temperature": 65
    }
  ]
}
```

## 7. Equipment & Fault Mapping

### 7.1 Equipment Codes

```python
EQUIPMENT_MAP = {
    0x01: "TCU",      # Train Control Unit
    0x02: "BCU",      # Brake Control Unit
    0x03: "DCU",      # Door Control Unit
    0x04: "HVAC",     # Heating Ventilation Air Conditioning
    0x05: "PIS",      # Passenger Information System
    # ... more mappings
}
```

### 7.2 Fault Codes

```python
FAULT_MAP = {
    0x01: "OVERCURRENT",
    0x02: "OVERVOLTAGE",
    0x03: "UNDERVOLTAGE",
    0x04: "SHORT_CIRCUIT",
    0x05: "OPEN_CIRCUIT",
    # ... more mappings
}
```

## 8. Logging & Monitoring

### 8.1 Log Levels

- **DEBUG**: Detailed packet dumps, timing info
- **INFO**: Session progress, success/failure counts
- **WARNING**: Retries, timeouts, validation warnings
- **ERROR**: Fatal errors, connection failures

### 8.2 Log Format

```
2024-01-01 12:00:00,123 [INFO] protocol.session — Handshake successful
2024-01-01 12:00:01,456 [DEBUG] protocol.udp_client — Sent packet: 0x20 len=12
2024-01-01 12:00:02,789 [WARNING] protocol.udp_client — Timeout, retry 1/3
```

### 8.3 Performance Metrics

- **Session Duration**: Total time from handshake to cleanup
- **Packet Success Rate**: Valid responses / total attempts
- **Retry Count**: Average retries per page
- **Throughput**: Records processed per second

## 9. Testing Strategy

### 9.1 Unit Tests

- **parsers/test_parser.py**: Test BCD decoding, record parsing
- **exporter/test_exporter.py**: Test CSV/PDF generation
- **protocol/test_commands.py**: Test packet building

### 9.2 Integration Tests

- **tests/mock_tis.py**: Mock UDP server dengan responses valid
- **End-to-end**: Full session dengan mock server

### 9.3 Test Data

- **PCAP captures**: Real network traffic dari TIS
- **Golden files**: Expected CSV/PDF output
- **Edge cases**: Invalid packets, timeouts, corrupted data

## 10. Deployment & Operations

### 10.1 Environment Variables

```bash
TIS_HOST=192.168.1.100
TIS_PORT=262
LOCAL_PORT=263
OUTPUT_DIR=/data/tis_output
CLOUD_API_URL=https://api.mrtjkt.com/tis
API_KEY=secret-key-here
LOG_LEVEL=INFO
LOG_DIR=/var/log/tis_gateway
```

### 10.2 Monitoring

- **Health Check**: Periodic test handshake
- **Alerting**: Email/SMS on consecutive failures
- **Metrics**: Prometheus/Grafana integration
- **Log Aggregation**: ELK stack

### 10.3 Backup & Recovery

- **Data Backup**: Daily backup of output files
- **Config Backup**: Version controlled settings
- **Log Rotation**: 30 days retention
- **Failover**: Multiple gateway instances

## 11. Security Considerations

### 11.1 Network Security

- **Firewall**: Restrict UDP traffic to known IPs
- **VLAN**: Isolate TIS network segment
- **Monitoring**: IDS/IPS for anomalous traffic

### 11.2 Data Protection

- **Encryption**: TLS 1.3 untuk cloud upload
- **Access Control**: API key authentication
- **Audit Logging**: All operations logged
- **Data Sanitization**: No sensitive data in logs

### 11.3 Code Security

- **Input Validation**: Validate all network input
- **Dependency Scanning**: Regular vulnerability checks
- **Code Review**: Mandatory for all changes
- **Secrets Management**: Environment variables for keys

## 12. Future Enhancements

### 12.1 Features

- **Real-time Monitoring**: Continuous data streaming
- **Web Dashboard**: GUI untuk monitoring dan control
- **Multi-threading**: Parallel processing multiple rakes
- **Database Integration**: Persistent storage
- **REST API**: Programmatic access

### 12.2 Performance

- **Async I/O**: Non-blocking UDP operations
- **Connection Pooling**: Reuse connections
- **Caching**: Metadata caching
- **Compression**: Compress large payloads

### 12.3 Reliability

- **Circuit Breaker**: Fail fast on persistent errors
- **Retry Logic**: Exponential backoff
- **Health Checks**: Automatic recovery
- **Load Balancing**: Multiple gateway instances