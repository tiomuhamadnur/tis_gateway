"""
utils/logger.py
================
Logger terpusat. Semua module pakai get_logger(__name__).
"""

import logging
import logging.handlers
import os
from config.settings import config


def _setup_root_logger():
    level = getattr(logging, config.log.level.upper(), logging.INFO)
    fmt   = "%(asctime)s [%(levelname)s] %(name)s — %(message)s"
    handlers = [logging.StreamHandler()]

    if config.log.log_to_file:
        os.makedirs(config.log.log_dir, exist_ok=True)
        fh = logging.handlers.TimedRotatingFileHandler(
            filename=os.path.join(config.log.log_dir, "tis_gateway.log"),
            when="midnight",
            backupCount=config.log.log_retention_days,
            encoding="utf-8",
        )
        handlers.append(fh)

    logging.basicConfig(level=level, format=fmt, handlers=handlers)


_setup_root_logger()


def get_logger(name: str) -> logging.Logger:
    """Ambil logger untuk module tertentu."""
    return logging.getLogger(name)
