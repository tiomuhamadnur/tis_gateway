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
# RECORD PARSER
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
    parser  = RecordParser()
    records = parser.parse_payload(SAMPLE_PAYLOAD)
    row = records[0].to_csv_row()
    assert len(row) == 16  # 16 kolom sesuai format PTU
    assert row[11] == 9    # equipment code
    assert row[12] == 806  # fault code


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
        test_response_parser_cmd36,
        test_response_parser_heartbeat,
        test_response_parser_invalid,
    ]
    passed = 0
    for t in tests:
        try:
            t()
            print(f"  ✅ {t.__name__}")
            passed += 1
        except AssertionError as e:
            print(f"  ❌ {t.__name__}: {e}")
        except Exception as e:
            print(f"  💥 {t.__name__}: {e}")

    print(f"\n{passed}/{len(tests)} tests passed")
