"""
parsers/record_parser.py
========================
Parse raw bytes CMD 0x36 payload menjadi FailureRecord.

RECORD LAYOUT — 20 bytes per record (confirmed dari PCAP TS5 + TS13 moving-train + cross-ref CSV PTU asli):
  [0-5]   Timestamp BCD (YY MM DD HH MM SS)          — CONFIRMED
  [6-7]   Location [m] signed int16 big-endian        — CONFIRMED (TS13: 0x2173=8563m ✓; 0x1F0F=7951m ✓; 0xFFA7=-89m ✓)
  [8]     Status byte:                                — CONFIRMED
            bit[4]=occur/recover (0=Occur, 1=Recover)
            bit[0]=EB asserted (override notch ke EB)
            bit[7]=??? (set saat moving, hipotesis)
  [9]     Notch step byte  → decode_notch()           — CONFIRMED via (b8,b9,b10) tuple
  [10]    Notch mode byte  → decode_notch()           — CONFIRMED (0x80=Auto/Neutral, 0x40=M_Brake, 0x08=M_Power, 0x00=EB-mode)
  [11]    Car ID (direct: 0x01-0x06 = Car 1-6)        — CONFIRMED
  [12]    Equipment code                               — CONFIRMED
  [13]    Fault sub-index (internal TIS)               — UNCONFIRMED
  [14-15] Fault code uint16 big-endian                — CONFIRMED
  [16]    Overhead Voltage raw (× 10 = Volt)          — CONFIRMED (0x01→10V, 0x08→80V)
  [17]    Speed [km/h]                                — CONFIRMED (depot=0 ✓)
  [18-19] Train ID BCD (0xFFFF=depot/unknown)         — CONFIRMED (BCD: 0x07 0x29 → "0729")

NOTE: Sebelumnya FF FF dianggap sebagai separator — ini SALAH.
      FF FF = field Train ID di bytes [18-19].
      RECORD_SIZE harus 20, bukan 18.

Confidence level: C=Confirmed  G=Best-guess  U=Unknown
"""

from dataclasses import dataclass, field
from datetime import datetime
from typing import Optional, List

from parsers.bcd import decode_timestamp, is_valid_timestamp, bcd_byte
from config.equipment_map import (
    get_equipment_name, get_fault_name,
    decode_notch,
)
from utils.logger import get_logger

log = get_logger(__name__)


RECORD_SIZE = 20   # bytes per record (termasuk train_id di [18-19])


# ─────────────────────────────────────────────
# FIELD OFFSETS — verified dari PCAP TS5
# ─────────────────────────────────────────────
OFF_TIMESTAMP  = 0   # [C] 6 bytes BCD
OFF_LOCATION   = 6   # [C] 2 bytes signed int16 BE [m] (TS13 confirmed: 0x2173=8563m, 0xFFA7=-89m)
OFF_STATUS     = 8   # [C] 1 byte: bit[4]=occur/recover, bit[0]=EB-asserted override
OFF_NOTCH_STEP = 9   # [C] 1 byte step level (A_Pn: 0x01..0x10; A_Bn: 0x81..0x90; 0x80=Neutral/Manual)
OFF_NOTCH_MODE = 10  # [C] 1 byte mode (0x80=Auto/Neutral, 0x40=M_Brake, 0x08=M_Power, 0x00=EB-mode)
OFF_CAR_ID     = 11  # [C] 1 byte direct (0x01-0x06 = Car 1-6)
OFF_EQUIP      = 12  # [C] 1 byte equipment code
OFF_FAULT_SUB  = 13  # [U] 1 byte fault sub-index
OFF_FAULT_CODE = 14  # [C] 2 bytes uint16 big-endian
OFF_OV_RAW     = 16  # [C] 1 byte × 10 = Overhead Voltage [V]
OFF_SPEED      = 17  # [C] 1 byte speed [km/h]
OFF_TRAIN_ID   = 18  # [C] 2 bytes BCD (0xFF 0xFF = depot/unknown sentinel)


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
    status_byte: int = 0     # raw byte[8] (bit[4]=occ/rec, bit[0]=EB)
    notch_step: int = 0      # raw byte[9]
    notch_mode: int = 0      # raw byte[10]
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
        return decode_notch(self.status_byte, self.notch_step, self.notch_mode)

    @property
    def notch_byte(self) -> int:
        return self.notch_step

    @property
    def train_id_str(self) -> str:
        if self.train_id == 0xFFFF:
            return "FFFF"
        return "%04d" % self.train_id

    @property
    def timestamp_str(self) -> str:
        return self.timestamp.strftime("%d/%m/%y %H:%M:%S")

    @classmethod
    def from_dict(cls, d: dict) -> "FailureRecord":
        ts = d.get("timestamp", datetime.now().isoformat())
        if isinstance(ts, str):
            ts = datetime.fromisoformat(ts)
        tid = d.get("train_id", "FFFF")
        if isinstance(tid, str):
            tid = 0xFFFF if tid == "FFFF" else int(tid)
        return cls(
            block_no=d.get("block_no", 0),
            timestamp=ts,
            car_no=d.get("car_no", 0),
            occur_recover=d.get("occur_recover", 0),
            train_id=tid,
            location_m=d.get("location_m", 0),
            equipment_code=d.get("equipment_code", 0),
            fault_sub=d.get("fault_sub", 0),
            fault_code=d.get("fault_code", 0),
            status_byte=d.get("status_byte", 0),
            notch_step=d.get("notch_step", 0),
            notch_mode=d.get("notch_mode", 0),
            speed_kmh=d.get("speed_kmh", 0),
            overhead_v=d.get("overhead_v", 0),
        )

    def to_dict(self) -> dict:
        return {
            "block_no":       self.block_no,
            "timestamp":      self.timestamp.isoformat(),
            "car_no":         self.car_no,
            "occur_recover":  self.occur_recover,
            "train_id":       self.train_id_str,
            "location_m":     self.location_m,
            "equipment_code": self.equipment_code,
            "fault_sub":      self.fault_sub,
            "equipment_name": self.equipment_name,
            "fault_code":     self.fault_code,
            "fault_name":     self.fault_name,
            "status_byte":    self.status_byte,
            "notch_step":     self.notch_step,
            "notch_mode":     self.notch_mode,
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
            "eq=%d/%s fault=%d sub=0x%02X notch=%s spd=%d ov=%d "
            "| b8=0x%02X b9=0x%02X b10=0x%02X raw=%s"
        ) % (
            self.block_no,
            self.timestamp.strftime("%y%m%d_%H%M%S"),
            self.car_no, self.occur_recover, self.train_id_str,
            self.location_m,
            self.equipment_code, self.equipment_name,
            self.fault_code, self.fault_sub,
            self.notch_label,
            self.speed_kmh, self.overhead_v,
            self.status_byte, self.notch_step, self.notch_mode,
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
        loc_raw     = (raw[OFF_LOCATION] << 8) | raw[OFF_LOCATION + 1]   # uint16 BE
        location    = loc_raw - 0x10000 if loc_raw >= 0x8000 else loc_raw  # → signed int16
        status      = raw[OFF_STATUS]
        occur       = (status >> 4) & 0x01
        notch_step  = raw[OFF_NOTCH_STEP]
        notch_mode  = raw[OFF_NOTCH_MODE]
        car_no      = raw[OFF_CAR_ID]                                   # direct: 1-6
        equip_code  = raw[OFF_EQUIP]
        fault_sub   = raw[OFF_FAULT_SUB]
        fault_code  = (raw[OFF_FAULT_CODE] << 8) | raw[OFF_FAULT_CODE + 1]
        overhead_v  = raw[OFF_OV_RAW] * 10
        speed       = raw[OFF_SPEED]
        tid_hi      = raw[OFF_TRAIN_ID]
        tid_lo      = raw[OFF_TRAIN_ID + 1]
        if tid_hi == 0xFF and tid_lo == 0xFF:
            train_id = 0xFFFF
        else:
            train_id = bcd_byte(tid_hi) * 100 + bcd_byte(tid_lo)

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
            status_byte    = status,
            notch_step     = notch_step,
            notch_mode     = notch_mode,
            speed_kmh      = speed,
            overhead_v     = overhead_v,
            raw_bytes      = raw,
        )
