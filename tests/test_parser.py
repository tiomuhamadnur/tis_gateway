"""
tests/test_parser.py
=====================
Unit test untuk parser — menggunakan data nyata dari pcap.
Jalankan: python -m pytest tests/ -v
"""

import sys
import os
sys.path.insert(0, os.path.dirname(os.path.dirname(__file__)))

from datetime import datetime
from parsers.bcd import bcd_byte, decode_timestamp
from parsers.record_parser import RecordParser
from parsers.response_parser import ResponseParser
from config.equipment_map import get_notch_label


# ─────────────────────────────────────────────
# BCD DECODER
# ─────────────────────────────────────────────
def test_bcd_byte_basic():
    assert bcd_byte(0x26) == 26
    assert bcd_byte(0x05) == 5
    assert bcd_byte(0x16) == 16
    assert bcd_byte(0x00) == 0
    assert bcd_byte(0x59) == 59

def test_decode_timestamp_from_pcap():
    """Test dengan bytes nyata dari pcap record 1."""
    raw = bytes([0x26, 0x05, 0x07, 0x16, 0x04, 0x07])
    ts  = decode_timestamp(raw)
    assert ts == datetime(2026, 5, 7, 16, 4, 7)

def test_decode_timestamp_offset():
    """Test dengan offset di tengah bytes."""
    raw = bytes([0x00, 0x26, 0x05, 0x07, 0x16, 0x04, 0x07])
    ts  = decode_timestamp(raw, offset=1)
    assert ts == datetime(2026, 5, 7, 16, 4, 7)


# ─────────────────────────────────────────────
# RECORD PARSER — Data Depot (dari PCAP TS5)
# ─────────────────────────────────────────────
# Payload CMD 0x36: StartMark(1B) + 5×Record(20B) + EndMark(1B) = 102B
# "ffff" di akhir tiap record = Train ID bytes [18-19] = 0xFFFF (depot), BUKAN separator
SAMPLE_PAYLOAD = bytes.fromhex(
    "00"
    "260507160407000001000006090503260100ffff"  # Record 1: ts=260507-160407 car=6 eq=9 fc=806
    "260507160405000011000006090503260800ffff"  # Record 2: ts=260507-160405 car=6 eq=9 fc=806 recover
    "260507160400000001000002080502bc0000ffff"  # Record 3: ts=260507-160400 car=2 eq=8 fc=700
    "260507160400000001000002070502580000ffff"  # Record 4: ts=260507-160400 car=2 eq=7 fc=600
    "260507160400000001000002060501f40000ffff"  # Record 5: ts=260507-160400 car=2 eq=6 fc=500
    "03"
)

def test_record_parser_count():
    """Parser harus menghasilkan 5 records dari sample payload."""
    parser  = RecordParser()
    records = parser.parse_payload(SAMPLE_PAYLOAD)
    assert len(records) == 5

def test_record_parser_timestamp():
    parser  = RecordParser()
    records = parser.parse_payload(SAMPLE_PAYLOAD)
    assert records[0].timestamp == datetime(2026, 5, 7, 16, 4, 7)
    assert records[1].timestamp == datetime(2026, 5, 7, 16, 4, 5)

def test_record_parser_equipment():
    parser  = RecordParser()
    records = parser.parse_payload(SAMPLE_PAYLOAD)
    assert records[0].equipment_code == 9
    assert records[0].equipment_name == "PA"
    assert records[2].equipment_code == 8
    assert records[2].equipment_name == "PID"

def test_record_parser_fault_code():
    parser  = RecordParser()
    records = parser.parse_payload(SAMPLE_PAYLOAD)
    assert records[0].fault_code == 806
    assert records[0].fault_name == "DATASA"
    assert records[2].fault_code == 700
    assert records[2].fault_name == "NPICPS"

def test_record_parser_block_numbers():
    """Block numbers harus increment mulai dari block_start."""
    parser  = RecordParser()
    records = parser.parse_payload(SAMPLE_PAYLOAD, block_start=10)
    assert records[0].block_no == 10
    assert records[4].block_no == 14

def test_record_to_dict():
    parser  = RecordParser()
    records = parser.parse_payload(SAMPLE_PAYLOAD)
    d = records[0].to_dict()
    assert d["fault_code"]     == 806
    assert d["equipment_name"] == "PA"
    assert d["fault_name"]     == "DATASA"

def test_record_to_csv_row():
    """to_csv_row() harus mengembalikan 16 kolom (trailing '' ada di exporter, bukan di sini)."""
    parser  = RecordParser()
    records = parser.parse_payload(SAMPLE_PAYLOAD)
    row = records[0].to_csv_row()
    assert len(row) == 16   # 16 kolom dari to_csv_row(); exporter tambahkan '' secara terpisah
    assert row[11] == 9     # equipment code di index 11
    assert row[12] == 806   # fault code di index 12


# ─────────────────────────────────────────────
# RECORD PARSER — Location signed int8
# ─────────────────────────────────────────────
def _make_payload(record_bytes: bytes) -> bytes:
    """Bungkus 20-byte record dengan StartMark/EndMark."""
    assert len(record_bytes) == 20
    return b'\x00' + record_bytes + b'\x03'

# 20-byte template: lokasi depot (byte[7]=0x00)
_DEPOT_RECORD = bytes.fromhex("260507160407000001000006090503260100ffff")

def test_location_depot_zero():
    """Byte[7]=0x00 → location=0m (depot/stationary)."""
    rec = RecordParser().parse_payload(_make_payload(_DEPOT_RECORD))[0]
    assert rec.location_m == 0

def test_location_negative_signed():
    """Byte[7]=0xBE (190 unsigned) → signed int8 = -66m. Dikonfirmasi dari Block-10 PCAP TS5."""
    raw = bytearray(_DEPOT_RECORD)
    raw[7] = 0xBE   # OFF_LOCATION
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.location_m == -66

def test_location_small_positive():
    """Byte[7]=0x3A = 58 (positive signed int8 = +58m)."""
    raw = bytearray(_DEPOT_RECORD)
    raw[7] = 0x3A
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.location_m == 58

def test_location_max_negative():
    """Byte[7]=0x80 = 128 unsigned → signed int8 = -128m (batas bawah)."""
    raw = bytearray(_DEPOT_RECORD)
    raw[7] = 0x80
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.location_m == -128

def test_location_max_positive():
    """Byte[7]=0x7F = 127 (batas atas signed int8 = +127m)."""
    raw = bytearray(_DEPOT_RECORD)
    raw[7] = 0x7F
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.location_m == 127


# ─────────────────────────────────────────────
# RECORD PARSER — Train ID BCD + 0xFFFF sentinel
# ─────────────────────────────────────────────
def test_train_id_depot_ffff():
    """Bytes [18-19]=0xFF 0xFF → train_id=0xFFFF → train_id_str='FFFF' (depot/unknown)."""
    rec = RecordParser().parse_payload(_make_payload(_DEPOT_RECORD))[0]
    assert rec.train_id == 0xFFFF
    assert rec.train_id_str == "FFFF"

def test_train_id_bcd_decode():
    """Bytes [18-19]=0x07 0x29 → BCD → 7*100+29=729 → '0729'."""
    raw = bytearray(_DEPOT_RECORD)
    raw[18] = 0x07   # OFF_TRAIN_ID hi
    raw[19] = 0x29   # OFF_TRAIN_ID lo
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.train_id == 729
    assert rec.train_id_str == "0729"

def test_train_id_bcd_leading_zero():
    """Bytes [18-19]=0x00 0x05 → BCD → 0*100+5=5 → '0005'."""
    raw = bytearray(_DEPOT_RECORD)
    raw[18] = 0x00
    raw[19] = 0x05
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.train_id == 5
    assert rec.train_id_str == "0005"


# ─────────────────────────────────────────────
# RECORD PARSER — Occur/Recover dari byte[8]
# ─────────────────────────────────────────────
def test_occur_recover_occur():
    """Byte[8]=0x00 → high nibble=0 → occur_recover=0 (Occur)."""
    raw = bytearray(_DEPOT_RECORD)
    raw[8] = 0x00
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.occur_recover == 0

def test_occur_recover_recover():
    """Byte[8]=0x11 → high nibble=1 → occur_recover=1 (Recover). Dari record 2 PCAP depot."""
    raw = bytearray(_DEPOT_RECORD)
    raw[8] = 0x11
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.occur_recover == 1


# ─────────────────────────────────────────────
# NOTCH_MAP — seluruh range yang dikonfirmasi
# ─────────────────────────────────────────────
def test_notch_eb():
    """0x00 → 'EB' (Emergency Brake). Dikonfirmasi dari depot PCAP."""
    assert get_notch_label(0x00) == "EB"

def test_notch_neutral():
    """0x80 → 'Neutral'. Dikonfirmasi dari Block-10 PTU comparison."""
    assert get_notch_label(0x80) == "Neutral"

def test_notch_ato_brake_range():
    """0x81–0x88 → A_B1–A_B8 (ATO brake notches). Dari PTU comparison pattern."""
    assert get_notch_label(0x81) == "A_B1"
    assert get_notch_label(0x85) == "A_B5"   # dikonfirmasi dari PTU
    assert get_notch_label(0x86) == "A_B6"   # dikonfirmasi dari PTU
    assert get_notch_label(0x88) == "A_B8"

def test_notch_ato_power_confirmed():
    """0x04='A_P4', 0x10='A_P16'. Dikonfirmasi dari PTU comparison."""
    assert get_notch_label(0x04) == "A_P4"
    assert get_notch_label(0x10) == "A_P16"

def test_notch_ato_power_full_range():
    """0x01–0x10 → A_P1–A_P16 (VOBC 17 steps dari manual Att10 TEXT49)."""
    for n in range(1, 17):
        label = get_notch_label(n)
        assert label == f"A_P{n}", f"byte 0x{n:02X}: expected A_P{n}, got {label}"

def test_notch_unknown_fallback():
    """Byte yang tidak dikenal → fallback 'N{hex}' (bukan error)."""
    label = get_notch_label(0x91)   # Di luar A_B16 (max ATO brake = 0x90)
    assert label == "N91"
    label = get_notch_label(0x11)   # Di luar A_P16 (max ATO power = 0x10)
    assert label == "N11"

def test_notch_via_record():
    """Parsing notch byte[9] secara end-to-end via RecordParser."""
    raw = bytearray(_DEPOT_RECORD)
    raw[9] = 0x80   # OFF_NOTCH = Neutral
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.notch_label == "Neutral"
    assert rec.notch_byte == 0x80


# ─────────────────────────────────────────────
# OVERHEAD VOLTAGE scaling
# ─────────────────────────────────────────────
def test_overhead_voltage_scaling():
    """Byte[16] = OV_raw × 10 = Volt. Manual: 'Line Voltage [10V:bit]'."""
    raw = bytearray(_DEPOT_RECORD)
    raw[16] = 0x01   # OFF_OV_RAW = 1 → 10V
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.overhead_v == 10

    raw[16] = 0x08   # 8 → 80V
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.overhead_v == 80

    raw[16] = 0x00   # 0 → 0V (depot/no overhead)
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.overhead_v == 0


# ─────────────────────────────────────────────
# RESPONSE PARSER
# ─────────────────────────────────────────────
# Full 112B packet: Header(8) + StartMark(1) + 5×Record(20) + EndMark(1) + Checksum(2)
SAMPLE_CMD36_PACKET = bytes.fromhex(
    "023600610005000000"
    "260507160407000001000006090503260100ffff"
    "260507160405000011000006090503260800ffff"
    "260507160400000001000002080502bc0000ffff"
    "260507160400000001000002070502580000ffff"
    "260507160400000001000002060501f40000ffff"
    "039b00"
)

def test_response_parser_cmd36():
    parser = ResponseParser()
    packet = parser.parse(SAMPLE_CMD36_PACKET)
    assert packet is not None
    assert packet.cmd  == 0x36
    assert packet.seq  == 0x0061
    assert packet.page == 0x0000
    assert not packet.is_heartbeat

def test_response_parser_heartbeat():
    parser    = ResponseParser()
    heartbeat = b'\x00' * 256
    packet    = parser.parse(heartbeat)
    assert packet is not None
    assert packet.is_heartbeat

def test_response_parser_invalid():
    parser = ResponseParser()
    assert parser.parse(b'') is None
    assert parser.parse(b'\x01\x02') is None  # prefix salah


if __name__ == "__main__":
    # Jalankan tanpa pytest
    tests = [
        test_bcd_byte_basic,
        test_decode_timestamp_from_pcap,
        test_decode_timestamp_offset,
        test_record_parser_count,
        test_record_parser_timestamp,
        test_record_parser_equipment,
        test_record_parser_fault_code,
        test_record_parser_block_numbers,
        test_record_to_dict,
        test_record_to_csv_row,
        test_location_depot_zero,
        test_location_negative_signed,
        test_location_small_positive,
        test_location_max_negative,
        test_location_max_positive,
        test_train_id_depot_ffff,
        test_train_id_bcd_decode,
        test_train_id_bcd_leading_zero,
        test_occur_recover_occur,
        test_occur_recover_recover,
        test_notch_eb,
        test_notch_neutral,
        test_notch_ato_brake_range,
        test_notch_ato_power_confirmed,
        test_notch_ato_power_full_range,
        test_notch_unknown_fallback,
        test_notch_via_record,
        test_overhead_voltage_scaling,
        test_response_parser_cmd36,
        test_response_parser_heartbeat,
        test_response_parser_invalid,
    ]
    passed = 0
    for t in tests:
        try:
            t()
            print(f"  OK {t.__name__}")
            passed += 1
        except AssertionError as e:
            print(f"  FAIL {t.__name__}: {e}")
        except Exception as e:
            print(f"  ERROR {t.__name__}: {e}")

    print(f"\n{passed}/{len(tests)} tests passed")
