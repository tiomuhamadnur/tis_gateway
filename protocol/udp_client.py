"""
protocol/udp_client.py
=======================
Low-level UDP socket handler.
Tanggung jawab: send, receive, retry — tidak tahu soal protokol TIS.
"""

import socket
import time
from typing import Optional

from config.settings import config
from utils.logger import get_logger

log = get_logger(__name__)


class UDPClient:
    """
    UDP socket handler untuk komunikasi ke TIS.
    Gunakan sebagai context manager:

        with UDPClient() as client:
            client.send(data)
            resp = client.receive()
    """

    def __init__(
        self,
        host: Optional[str] = None,
        port: Optional[int] = None,
        local_port: Optional[int] = None,
    ):
        self.host       = host       or config.network.tis_host
        self.port       = port       or config.network.tis_port
        self.local_port = local_port or config.network.local_port
        self._sock: Optional[socket.socket] = None

    # ── Context manager ────────────────────────────────────────────
    def __enter__(self):
        self.connect()
        return self

    def __exit__(self, *_):
        self.close()

    # ── Lifecycle ─────────────────────────────────────────────────
    def connect(self):
        """Buka UDP socket dan bind ke local port."""
        self._sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        self._sock.settimeout(config.network.recv_timeout_sec)
        self._sock.bind(("0.0.0.0", self.local_port))
        log.info(f"UDP socket terbuka — local port {self.local_port}, target {self.host}:{self.port}")

    def close(self):
        """Tutup socket."""
        if self._sock:
            self._sock.close()
            self._sock = None
            log.info("UDP socket ditutup")

    # ── Send / Receive ─────────────────────────────────────────────
    def send(self, data: bytes) -> int:
        """Kirim bytes ke TIS. Kembalikan jumlah byte terkirim."""
        if not self._sock:
            raise RuntimeError("Socket belum terbuka. Panggil connect() dulu.")
        sent = self._sock.sendto(data, (self.host, self.port))
        log.debug(f"→ TX {sent}B  [{data[:8].hex()}...]")
        return sent

    def receive(self) -> Optional[bytes]:
        """
        Terima satu UDP datagram dari TIS.
        Kembalikan None jika timeout atau error.
        """
        if not self._sock:
            raise RuntimeError("Socket belum terbuka.")
        try:
            data, addr = self._sock.recvfrom(config.network.recv_buffer_size)
            log.debug(f"← RX {len(data)}B from {addr}  [{data[:8].hex()}...]")
            return data
        except socket.timeout:
            log.debug("Receive timeout")
            return None
        except OSError as e:
            log.warning(f"Socket error saat receive: {e}")
            return None

    def send_and_receive(self, data: bytes) -> Optional[bytes]:
        """
        Kirim data dan tunggu response.
        Otomatis retry sesuai config.
        """
        for attempt in range(1, config.network.max_retries + 1):
            self.send(data)
            resp = self.receive()
            if resp is not None:
                return resp
            log.warning(f"Tidak ada response (attempt {attempt}/{config.network.max_retries})")
            if attempt < config.network.max_retries:
                time.sleep(config.network.retry_delay_sec)
        log.error("Semua retry habis — tidak ada response dari TIS")
        return None

    def drain(self, max_packets: int = 10):
        """
        Buang semua packet yang mungkin sudah antre di buffer.
        Berguna sebelum mulai sesi baru.
        """
        if not self._sock:
            return
        old_timeout = self._sock.gettimeout()
        self._sock.settimeout(0.1)
        count = 0
        while count < max_packets:
            try:
                self._sock.recvfrom(config.network.recv_buffer_size)
                count += 1
            except socket.timeout:
                break
        self._sock.settimeout(old_timeout)
        if count:
            log.debug(f"Drain: {count} packet dibuang dari buffer")
