"""
parsers/record_parser.py
========================
Parse raw bytes CMD 0x36 payload menjadi FailureRecord.

RECORD LAYOUT — 20 bytes per record (confirmed dari PCAP TS5 + cross-ref CSV):
  [0-5]   Timestamp BCD (YY MM DD HH MM SS)          — CONFIRMED
  [6]     ??? (always 0x00 di depot; purpose unknown) — UNKNOWN
  [7]     Notch byte → NOTCH_MAP                      — BEST GUESS (depot=0x00=EB, belum konfirmasi kereta jalan)
  [8]     Status byte: bit[7:4]=occur/recover          — CONFIRMED (0x00>>4=0,0x11>>4=1)
  [9-10]  Location [m] int16 big-endian               — BEST GUESS (depot=0 ✓; sign belum konfirmasi)
  [11]    Car ID (direct: 0x01-0x06 = Car 1-6)        — CONFIRMED
  [12]    Equipment code                               — CONFIRMED
  [13]    Fault sub-index (internal TIS)               — UNCONFIRMED
  [14-15] Fault code uint16 big-endian                — CONFIRMED
  [16]    Overhead Voltage raw (× 10 = Volt)          — CONFIRMED (0x01→10V, 0x08→80V)
  [17]    Speed [km/h]                                — CONFIRMED (depot=0 ✓)
  [18-19] Train ID uint16 big-endian (0xFFFF=depot)   — CONFIRMED

NOTE: Sebelumnya FF FF dianggap sebagai separator — ini SALAH.
      FF FF = field Train ID di bytes [18-19].
      RECORD_SIZE harus 20, bukan 18.

Confidence level: C=Confirmed  G=Best-guess  U=Unknown
"""

from dataclasses import dataclass, field
from datetime import datetime
from typing import Optional, List

from parsers.bcd import decode_timestamp, is_valid_timestamp
from config.equipment_map import (
    get_equipment_name, get_fault_name,
    get_notch_label,
)
from utils.logger import get_logger

log = get_logger(__name__)


RECORD_SIZE = 20   # bytes per record (termasuk train_id di [18-19])


# ─────────────────────────────────────────────
# FIELD OFFSETS — verified dari PCAP TS5
# ─────────────────────────────────────────────
OFF_TIMESTAMP  = 0   # [C] 6 bytes BCD
OFF_UNKNOWN6   = 6   # [U] 1 byte, always 0x00 di depot
OFF_NOTCH      = 7   # [G] 1 byte → NOTCH_MAP (0x00=EB for depot; unconfirmed for moving)
OFF_STATUS     = 8   # [C] 1 byte: high-nibble = occur/recover
OFF_LOCATION   = 9   # [G] 2 bytes int16 big-endian (0 for depot)
OFF_CAR_ID     = 11  # [C] 1 byte direct (0x01-0x06 = Car 1-6)
OFF_EQUIP      = 12  # [C] 1 byte equipment code
OFF_FAULT_SUB  = 13  # [U] 1 byte fault sub-index
OFF_FAULT_CODE = 14  # [C] 2 bytes uint16 big-endian
OFF_OV_RAW     = 16  # [C] 1 byte × 10 = Overhead Voltage [V]
OFF_SPEED      = 17  # [C] 1 byte speed [km/h]
OFF_TRAIN_ID   = 18  # [C] 2 bytes uint16 big-endian (0xFFFF = depot/unknown)


# ─────────────────────────────────────────────
# DATA CLASS
# ─────────────────────────────────────────────
@dataclass
class FailureRecord:
    """Satu record failure dari TIS. Setara dengan satu baris di CSV/PDF output PTU."""

    block_no: int = 0
    timestamp: datetime = field(default_factory=datetime.now)
    car_no: int = 0
    occur_recover: int = 0
    train_id: int = 0xFFFF
    location_m: int = 0
    equipment_code: int = 0
    fault_sub: int = 0
    fault_code: int = 0
    notch_byte: int = 0
    speed_kmh: int = 0
    overhead_v: int = 0
    raw_bytes: bytes = b''

    @property
    def equipment_name(self) -> str:
        return get_equipment_name(self.equipment_code)

    @property
    def fault_name(self) -> str:
        return get_fault_name(self.equipment_code, self.fault_code)

    @property
    def notch_label(self) -> str:
        return get_notch_label(self.notch_byte)

    @property
    def train_id_str(self) -> str:
        if self.train_id == 0xFFFF:
            return "FFFF"
        return "%04d" % self.train_id

    @property
    def timestamp_str(self) -> str:
        return self.timestamp.strftime("%d/%m/%y %H:%M:%S")

    def to_dict(self) -> dict:
        return {
            "block_no":       self.block_no,
            "timestamp":      self.timestamp.isoformat(),
            "car_no":         self.car_no,
            "occur_recover":  self.occur_recover,
            "train_id":       self.train_id_str,
            "location_m":     self.location_m,
            "equipment_code": self.equipment_code,
            "equipment_name": self.equipment_name,
            "fault_code":     self.fault_code,
            "fault_name":     self.fault_name,
            "notch":          self.notch_label,
            "speed_kmh":      self.speed_kmh,
            "overhead_v":     self.overhead_v,
        }

    def to_csv_row(self) -> List:
        ts = self.timestamp
        return [
            self.block_no,
            ts.strftime("%y"),
            ts.strftime("%m"),
            ts.strftime("%d"),
            ts.strftime("%H"),
            ts.strftime("%M"),
            ts.strftime("%S"),
            "%02d" % self.car_no,
            self.train_id_str,
            self.occur_recover,
            self.location_m,
            self.equipment_code,
            self.fault_code,
            self.notch_label,
            self.speed_kmh,
            self.overhead_v,
        ]

    def debug_line(self) -> str:
        """Satu baris ringkas untuk log DEBUG — format mudah dibaca manusia dan Claude."""
        return (
            "[REC] blk=%d ts=%s car=%d occ=%d tid=%s loc=%d "
            "eq=%d fault=%d notch=%s spd=%d ov=%d "
            "| raw=%s"
        ) % (
            self.block_no,
            self.timestamp.strftime("%y%m%d_%H%M%S"),
            self.car_no, self.occur_recover, self.train_id_str,
            self.location_m, self.equipment_code, self.fault_code,
            self.notch_label, self.speed_kmh, self.overhead_v,
            self.raw_bytes.hex(),
        )


# ─────────────────────────────────────────────
# PARSER
# ─────────────────────────────────────────────
class RecordParser:
    """Parse raw payload CMD 0x36 menjadi list FailureRecord."""

    def parse_payload(
        self, payload: bytes, block_start: int = 0
    ) -> List[FailureRecord]:
        """
        Parse payload CMD 0x36 (tanpa header 8B dan checksum 2B).

        Struktur payload:
          0x00  [start marker]
          [20B record] × N
          0x03  [end marker]

        Catatan: FF FF di bytes [18-19] adalah field Train ID,
                 BUKAN separator antar record.
        """
        records: List[FailureRecord] = []
        skipped = 0
        pos = 1          # lewati start marker 0x00
        block_no = block_start
        payload_end = len(payload) - 1  # -1 untuk end marker 0x03

        while pos + RECORD_SIZE <= payload_end:
            raw = payload[pos:pos + RECORD_SIZE]

            if not is_valid_timestamp(raw, OFF_TIMESTAMP):
                log.debug(
                    "[SKIP] pos=%d raw=%s (invalid timestamp)",
                    pos, raw.hex(),
                )
                skipped += 1
                pos += RECORD_SIZE
                continue

            rec = self._parse_record(raw, block_no)
            records.append(rec)
            log.debug(rec.debug_line())

            block_no += 1
            pos += RECORD_SIZE

        if skipped:
            log.debug("[PARSE_PAYLOAD] %d records, %d skipped", len(records), skipped)

        return records

    def _parse_record(self, raw: bytes, block_no: int) -> FailureRecord:
        """Parse satu record 20 byte."""
        timestamp   = decode_timestamp(raw, OFF_TIMESTAMP)
        notch_byte  = raw[OFF_NOTCH]
        status      = raw[OFF_STATUS]
        occur       = (status >> 4) & 0x01
        loc_raw     = (raw[OFF_LOCATION] << 8) | raw[OFF_LOCATION + 1]
        location    = loc_raw - 65536 if loc_raw > 32767 else loc_raw
        car_no      = raw[OFF_CAR_ID]                                     # direct: 1-6
        equip_code  = raw[OFF_EQUIP]
        fault_sub   = raw[OFF_FAULT_SUB]
        fault_code  = (raw[OFF_FAULT_CODE] << 8) | raw[OFF_FAULT_CODE + 1]
        overhead_v  = raw[OFF_OV_RAW] * 10
        speed       = raw[OFF_SPEED]
        train_id    = (raw[OFF_TRAIN_ID] << 8) | raw[OFF_TRAIN_ID + 1]

        if car_no == 0 or car_no > 6:
            log.warning(
                "[REC] blk=%d car_id byte=0x%02x di luar range 1-6 — raw=%s",
                block_no, car_no, raw.hex(),
            )

        return FailureRecord(
            block_no       = block_no,
            timestamp      = timestamp,
            car_no         = car_no,
            occur_recover  = occur,
            train_id       = train_id,
            location_m     = location,
            equipment_code = equip_code,
            fault_sub      = fault_sub,
            fault_code     = fault_code,
            notch_byte     = notch_byte,
            speed_kmh      = speed,
            overhead_v     = overhead_v,
            raw_bytes      = raw,
        )
