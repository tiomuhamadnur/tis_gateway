"""
exporter/pdf_exporter.py
=========================
Generate file PDF format identik dengan output PTU Sumitomo.
Sumber referensi: dudu_ts_5.pdf

Layout:
  - Header: "Failure History(Formation Record) RakeID:{N} {datetime} Page:{N}/{total}"
  - Tabel: No | Time | CarNo | Location | Equipment | Failure Code |
            Command | Speed | Overhead_V | TrainID
  - 50 records per halaman
"""

import os
from datetime import datetime
from typing import List, Optional

from reportlab.lib import colors
from reportlab.lib.pagesizes import A4, landscape
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import mm
from reportlab.platypus import (
    SimpleDocTemplate, Table, TableStyle, Paragraph,
    Spacer, PageBreak,
)
from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_RIGHT

from parsers.record_parser import FailureRecord
from config.settings import config
from utils.logger import get_logger

log = get_logger(__name__)

RECORDS_PER_PAGE = 50

# Warna
COL_HEADER  = colors.HexColor("#003366")
COL_ROW_ODD = colors.HexColor("#F5F8FC")
COL_ROW_EVN = colors.white


class PDFExporter:
    """Export list FailureRecord ke PDF format PTU Sumitomo."""

    # Kolom header PDF (sesuai referensi dudu_ts_5.pdf)
    COLUMNS = [
        "No", "Time", "CarNo", "Location", "Equipment",
        "Failure Code", "Command", "Speed", "Overhead_V", "TrainID",
    ]

    # Lebar kolom (mm) — total harus ≤ 267mm (A4 landscape usable width)
    COL_WIDTHS = [10, 32, 14, 18, 18, 22, 18, 14, 20, 18]

    def export(
        self,
        records: List[FailureRecord],
        rake_id: int,
        read_time: Optional[datetime] = None,
        output_dir: Optional[str] = None,
        filename: Optional[str] = None,
    ) -> str:
        """
        Export records ke PDF.
        Returns: path file PDF yang dibuat.
        """
        read_time  = read_time or datetime.now()
        output_dir = output_dir or config.output.output_dir
        os.makedirs(output_dir, exist_ok=True)

        filename = filename or self._generate_filename(read_time, rake_id)
        filepath = os.path.join(output_dir, filename)

        doc = SimpleDocTemplate(
            filepath,
            pagesize=landscape(A4),
            leftMargin=15*mm, rightMargin=15*mm,
            topMargin=15*mm, bottomMargin=15*mm,
        )

        styles = getSampleStyleSheet()
        title_style = ParagraphStyle(
            "TitleStyle",
            parent=styles["Normal"],
            fontSize=9,
            fontName="Helvetica-Bold",
            alignment=TA_LEFT,
            textColor=colors.HexColor("#003366"),
        )
        footer_style = ParagraphStyle(
            "FooterStyle",
            parent=styles["Normal"],
            fontSize=7,
            fontName="Helvetica",
            alignment=TA_RIGHT,
            textColor=colors.grey,
        )

        # Bagi records per halaman
        chunks = [
            records[i:i + RECORDS_PER_PAGE]
            for i in range(0, max(len(records), 1), RECORDS_PER_PAGE)
        ]
        total_pages = max(len(chunks), 1)

        story = []

        for page_no, chunk in enumerate(chunks, 1):
            # ── Title bar ────────────────────────────────────────
            title_text = (
                f"Failure History(Formation Record)   "
                f"RakeID: {rake_id}   "
                f"{read_time.strftime('%d-%b-%y %I:%M:%S %p')}   "
                f"Page: {page_no}/{total_pages}"
            )
            story.append(Paragraph(title_text, title_style))
            story.append(Spacer(1, 3*mm))

            # ── Table ────────────────────────────────────────────
            table_data = [self.COLUMNS]  # header row

            for rec in chunk:
                table_data.append([
                    str(rec.block_no).zfill(3),
                    rec.timestamp_str,
                    f"{rec.car_no:02d}",
                    str(rec.location_m),
                    rec.equipment_name,
                    rec.fault_name,
                    rec.notch_label,
                    str(rec.speed_kmh),
                    str(rec.overhead_v),
                    rec.train_id_str,
                ])

            col_widths_pt = [w * mm for w in self.COL_WIDTHS]

            tbl = Table(table_data, colWidths=col_widths_pt, repeatRows=1)
            tbl.setStyle(self._table_style(len(chunk)))
            story.append(tbl)

            # ── Footer spacer + page break ───────────────────────
            story.append(Spacer(1, 3*mm))
            if page_no < total_pages:
                story.append(PageBreak())

        doc.build(story)
        log.info(f"PDF disimpan: {filepath} ({len(records)} records, {total_pages} hal)")
        return filepath

    def _table_style(self, num_data_rows: int) -> TableStyle:
        """Buat TableStyle — zebrastripe rows, header biru."""
        style = [
            # Header row
            ("BACKGROUND",   (0, 0), (-1, 0), COL_HEADER),
            ("TEXTCOLOR",    (0, 0), (-1, 0), colors.white),
            ("FONTNAME",     (0, 0), (-1, 0), "Helvetica-Bold"),
            ("FONTSIZE",     (0, 0), (-1, 0), 7),
            ("ALIGN",        (0, 0), (-1, 0), "CENTER"),
            ("BOTTOMPADDING",(0, 0), (-1, 0), 4),
            ("TOPPADDING",   (0, 0), (-1, 0), 4),
            # Data rows
            ("FONTNAME",     (0, 1), (-1, -1), "Helvetica"),
            ("FONTSIZE",     (0, 1), (-1, -1), 7),
            ("ALIGN",        (0, 1), (-1, -1), "CENTER"),
            ("TOPPADDING",   (0, 1), (-1, -1), 3),
            ("BOTTOMPADDING",(0, 1), (-1, -1), 3),
            # Grid
            ("GRID",         (0, 0), (-1, -1), 0.3, colors.lightgrey),
            ("LINEBELOW",    (0, 0), (-1, 0), 1, COL_HEADER),
        ]
        # Zebra stripe
        for i in range(1, num_data_rows + 1):
            bg = COL_ROW_ODD if i % 2 == 1 else COL_ROW_EVN
            style.append(("BACKGROUND", (0, i), (-1, i), bg))

        return TableStyle(style)

    def _generate_filename(self, dt: datetime, rake_id: int) -> str:
        prefix = config.output.filename_prefix
        date   = dt.strftime("%y%m%d")
        return f"{prefix}{date}_{rake_id:03d}.pdf"
