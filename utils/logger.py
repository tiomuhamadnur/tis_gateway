"""
utils/logger.py
================
Logger terpusat. Semua module pakai get_logger(__name__).
"""

import logging
import logging.handlers
import os
import time
from typing import Dict, Tuple, Optional
from config.settings import config

# ── Throttle cache ───────────────────────────────────────────────────
_last_log: Dict[str, Tuple[float, str]] = {}


def log_throttled(
    logger: logging.Logger,
    level: int,
    msg: str,
    interval_sec: float = 60.0,
    *args,
    **kwargs,
):
    """Log pesan yang sama (template) maksimal 1x per interval_sec detik."""
    now = time.time()
    entry = _last_log.get(msg)
    if entry and (now - entry[0]) < interval_sec:
        return
    _last_log[msg] = (now, msg)
    logger.log(level, msg, *args, **kwargs)


# ── Rotating file handler with size fallback ─────────────────────────
def _build_file_handler(log_path: str) -> logging.Handler:
    """Priority: TimedRotatingFileHandler (midnight), fallback size-based."""
    os.makedirs(os.path.dirname(log_path), exist_ok=True)
    try:
        return logging.handlers.TimedRotatingFileHandler(
            filename=log_path,
            when="midnight",
            backupCount=config.log.log_retention_days,
            encoding="utf-8",
        )
    except Exception:
        # Fallback: size-based rotation
        max_bytes = getattr(config.log, "max_log_bytes", 10 * 1024 * 1024)
        backup_count = getattr(config.log, "log_backup_count", 5)
        return logging.handlers.RotatingFileHandler(
            filename=log_path,
            maxBytes=max_bytes,
            backupCount=backup_count,
            encoding="utf-8",
        )


def _setup_root_logger():
    level = getattr(logging, config.log.level.upper(), logging.INFO)
    fmt   = "%(asctime)s [%(levelname)s] %(name)s — %(message)s"
    handlers = [logging.StreamHandler()]

    if config.log.log_to_file:
        fh = _build_file_handler(
            os.path.join(config.log.log_dir, "tis_gateway.log")
        )
        handlers.append(fh)

    logging.basicConfig(level=level, format=fmt, handlers=handlers)


_setup_root_logger()


def get_logger(name: str) -> logging.Logger:
    """Ambil logger untuk module tertentu."""
    return logging.getLogger(name)
