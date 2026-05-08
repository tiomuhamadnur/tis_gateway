"""
tests/mock_tis.py
==================
Mock TIS server untuk testing gateway TANPA perlu koneksi ke kereta.
Membalas semua command dengan response yang identik dengan pcap asli.

Jalankan di terminal terpisah:
    python tests/mock_tis.py

Lalu test gateway:
    python main.py --host 127.0.0.1 --rake-id 5
"""

import socket
import time
import threading
import argparse

# ─────────────────────────────────────────────
# SAMPLE RESPONSE DATA (dari pcap asli)
# ─────────────────────────────────────────────

HANDSHAKE_RESP = bytes.fromhex(
    "022000540080a088600000ffff0500000002000000030000000003042c2c2c2c2c2c2c00"
    "0000000100000000005511555f111111110000f084000000000000000000ab3c43380081"
    "837e00000000ff01ff0300000044045500111111110000f084000000000000004700004a"
    "40410082818300000000a003c001000000034d00"
)

CMD32_RESP = {
    1: bytes.fromhex("023201550003e9033801de01060000036400"),
    2: bytes.fromhex("023202560003ea000000000000000000"),
    3: bytes.fromhex("023203570003eb000000000000000000"),
    4: bytes.fromhex("023204580003ec000000000000000000"),
    5: bytes.fromhex("023205590003ed000000000000000000"),
    6: bytes.fromhex("0232065a0003ee030000000000000000"),
}

CMD34_RESP = {
    1: bytes.fromhex("0234015b00c81c2000000010100e0000001945000000c8038300"),
    2: bytes.fromhex("0234025c00c80000000000000000000000000000000000000000"),
    3: bytes.fromhex("0234035d00c80000000000000000000000000000000000000000"),
    4: bytes.fromhex("0234045e00c80000000000000000000000000000000000000000"),
    5: bytes.fromhex("0234055f00c80000000000000000000000000000000000000000"),
    6: bytes.fromhex("0234066000c80036000000000000000000000000000000000000"),
}

# Template CMD 0x36 response — 5 records per page, semua record sama (test data)
SAMPLE_RECORD_PAGE = bytes.fromhex(
    "023600610005000000"
    "260507160407000001000006090503260100"  # record 1
    "ffff"
    "260507160405000011000006090503260800"  # record 2
    "ffff"
    "260507160400000001000002080502bc0000"  # record 3
    "ffff"
    "260507160400000001000002070502580000"  # record 4
    "ffff"
    "260507160400000001000002060501f40000"  # record 5
    "ffff"
    "039b00"
)

HEARTBEAT = b'\x00' * 256


def build_cmd36_response(page: int, seq: int) -> bytes:
    """Build CMD 0x36 response dengan seq number dan page yang benar."""
    raw = bytearray(SAMPLE_RECORD_PAGE)
    raw[2] = (seq >> 8) & 0xFF
    raw[3] = seq & 0xFF
    raw[6] = 0x00
    raw[7] = page & 0xFF
    return bytes(raw)


# ─────────────────────────────────────────────
# MOCK SERVER
# ─────────────────────────────────────────────
class MockTISServer:
    def __init__(self, host: str = "127.0.0.1", port: int = 262, client_port: int = 263):
        self.host        = host
        self.port        = port
        self.client_port = client_port
        self._running    = False
        self._seq        = 0x60  # sequence counter untuk 0x36 response

    def run(self):
        """Jalankan mock server (blocking)."""
        sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        sock.bind((self.host, self.port))
        sock.settimeout(1.0)
        self._running = True

        print(f"[MockTIS] Listening on {self.host}:{self.port}")
        print(f"[MockTIS] Mengirim heartbeat setiap 10 detik...")

        # Thread heartbeat
        hb_thread = threading.Thread(
            target=self._heartbeat_loop,
            args=(sock,),
            daemon=True,
        )
        hb_thread.start()

        try:
            while self._running:
                try:
                    data, addr = sock.recvfrom(4096)
                    resp = self._handle(data)
                    if resp:
                        sock.sendto(resp, (addr[0], self.client_port))
                except socket.timeout:
                    continue
        except KeyboardInterrupt:
            print("\n[MockTIS] Dihentikan")
        finally:
            sock.close()

    def _handle(self, data: bytes):
        """Tentukan response berdasarkan command yang diterima."""
        if len(data) < 2:
            return None

        cmd = data[1]
        print(f"[MockTIS] ← RX CMD=0x{cmd:02X} len={len(data)}B  [{data[:8].hex()}]")

        if cmd == 0x20:
            print(f"[MockTIS]   → Handshake response")
            return HANDSHAKE_RESP

        elif cmd == 0x32:
            page = data[2] if len(data) > 2 else 1
            resp = CMD32_RESP.get(page, CMD32_RESP[1])
            print(f"[MockTIS]   → CMD32 page {page}")
            return resp

        elif cmd == 0x34:
            page = data[2] if len(data) > 2 else 1
            resp = CMD34_RESP.get(page, CMD34_RESP[1])
            print(f"[MockTIS]   → CMD34 page {page}")
            return resp

        elif cmd == 0x36:
            page = data[6] if len(data) > 6 else 0
            resp = build_cmd36_response(page, self._seq)
            self._seq = (self._seq + 1) & 0xFF
            print(f"[MockTIS]   → CMD36 page=0x{page:02X} seq={self._seq}")
            return resp

        print(f"[MockTIS]   → CMD tidak dikenal, diabaikan")
        return None

    def _heartbeat_loop(self, sock: socket.socket):
        """Kirim heartbeat ke client setiap 10 detik."""
        while self._running:
            time.sleep(10)
            try:
                sock.sendto(HEARTBEAT, ("127.0.0.1", self.client_port))
                print("[MockTIS] → Heartbeat terkirim")
            except Exception:
                pass


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Mock TIS Server untuk testing")
    parser.add_argument("--host",        default="127.0.0.1", help="Bind address")
    parser.add_argument("--port",        type=int, default=262, help="Port TIS (default 262)")
    parser.add_argument("--client-port", type=int, default=263, help="Port client/gateway (default 263)")
    args = parser.parse_args()

    server = MockTISServer(args.host, args.port, args.client_port)
    server.run()
