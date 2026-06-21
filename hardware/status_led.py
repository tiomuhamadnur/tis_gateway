"""
hardware/status_led.py
======================
Status LED controller untuk gateway.

Tujuan utama:
  - feature flag via .env
  - backend pluggable (mock / sysfs)
  - default aman: tidak mempengaruhi proses utama bila error
"""

import atexit
import os
import threading
import time
from dataclasses import dataclass
from typing import Optional, Tuple

from config.settings import config
from utils.logger import get_logger

log = get_logger(__name__)


@dataclass
class LedPins:
    red: Optional[int]
    yellow: Optional[int]
    green: Optional[int]


class _BaseBackend(object):
    def set_outputs(self, red: bool, yellow: bool, green: bool):
        raise NotImplementedError

    def close(self):
        return None


class _MockBackend(_BaseBackend):
    def __init__(self):
        self._last = None

    def set_outputs(self, red: bool, yellow: bool, green: bool):
        state = (red, yellow, green)
        if state != self._last:
            log.debug(
                "[LED:MOCK] red=%s yellow=%s green=%s",
                int(red), int(yellow), int(green)
            )
            self._last = state


class _SysfsBackend(_BaseBackend):
    SYSFS_BASE = "/sys/class/gpio"

    def __init__(self, pins: LedPins, active_low: bool = False):
        self._pins = pins
        self._active_low = active_low
        self._exported = []
        self._value_paths = {}
        self._direction_paths = {}
        self._supported = os.path.isdir(self.SYSFS_BASE)
        if not self._supported:
            raise RuntimeError("sysfs gpio path tidak ditemukan")
        self._setup_pin("red", pins.red)
        self._setup_pin("yellow", pins.yellow)
        self._setup_pin("green", pins.green)

    def _setup_pin(self, name: str, pin: Optional[int]):
        if pin is None:
            return
        gpio_name = "gpio%d" % pin
        gpio_path = os.path.join(self.SYSFS_BASE, gpio_name)
        if not os.path.isdir(gpio_path):
            self._write(self.SYSFS_BASE + "/export", str(pin))
            self._exported.append(pin)
            time.sleep(0.05)
        self._write(os.path.join(gpio_path, "direction"), "out")
        self._direction_paths[name] = os.path.join(gpio_path, "direction")
        self._value_paths[name] = os.path.join(gpio_path, "value")

    def _write(self, path: str, value: str):
        with open(path, "w") as handle:
            handle.write(value)

    def _map_value(self, value: bool) -> str:
        actual = not value if self._active_low else value
        return "1" if actual else "0"

    def set_outputs(self, red: bool, yellow: bool, green: bool):
        try:
            if "red" in self._value_paths:
                self._write(self._value_paths["red"], self._map_value(red))
            if "yellow" in self._value_paths:
                self._write(self._value_paths["yellow"], self._map_value(yellow))
            if "green" in self._value_paths:
                self._write(self._value_paths["green"], self._map_value(green))
        except Exception as exc:
            log.warning("[LED:SYSFS] write gagal: %s", exc)

    def close(self):
        try:
            self.set_outputs(False, False, False)
        finally:
            for pin in self._exported:
                try:
                    self._write(self.SYSFS_BASE + "/unexport", str(pin))
                except Exception:
                    pass


class StatusIndicator(object):
    """
    Controller status LED.

    Mode yang dipakai app:
      boot, idle, waiting_interval, retry_pending, tis_unreachable,
      cloud_unreachable, handshake, downloading, generating, uploading,
      success, error, shutdown
    """

    def __init__(self, indicator_config=None):
        self.config = indicator_config or config.indicator
        self._lock = threading.RLock()
        self._stop = threading.Event()
        self._mode = "off"
        self._mode_started = time.monotonic()
        self._mode_hold_sec = None
        self._fallback_mode = "idle"
        self._last_rendered = None
        self._backend = None
        self._enabled = False
        self._thread = None

        if not self.config.enabled:
            return

        pins = LedPins(
            red=self.config.red_pin,
            yellow=self.config.yellow_pin,
            green=self.config.green_pin,
        )
        if pins.red is None or pins.yellow is None or pins.green is None:
            log.warning("[LED] LED_ENABLED=true tetapi pin belum lengkap; LED dimatikan")
            return

        try:
            if self.config.backend == "sysfs":
                self._backend = _SysfsBackend(pins, active_low=self.config.active_low)
            else:
                self._backend = _MockBackend()
            self._enabled = True
            self._thread = threading.Thread(target=self._run, name="StatusIndicator", daemon=True)
            self._thread.start()
            atexit.register(self.close)
            self.idle()
            log.info(
                "[LED] enabled backend=%s pins=(R:%s Y:%s G:%s) active_low=%s",
                self.config.backend,
                self.config.red_pin,
                self.config.yellow_pin,
                self.config.green_pin,
                self.config.active_low,
            )
        except Exception as exc:
            self._enabled = False
            self._backend = None
            log.warning("[LED] inisialisasi gagal, LED dinonaktifkan: %s", exc)

    def enabled(self) -> bool:
        return self._enabled

    def close(self):
        with self._lock:
            self._stop.set()
            self._mode = "off"
            self._render((False, False, False))
            backend = self._backend
            self._backend = None
        if backend:
            try:
                backend.close()
            except Exception:
                pass

    def _set_mode(self, mode: str, hold_sec: Optional[float] = None, fallback: str = "idle"):
        if not self._enabled:
            return
        with self._lock:
            self._mode = mode
            self._mode_started = time.monotonic()
            self._mode_hold_sec = hold_sec
            self._fallback_mode = fallback

    def boot(self):
        self._set_mode("boot")

    def idle(self):
        self._set_mode("idle")

    def waiting_interval(self):
        self._set_mode("waiting_interval")

    def retry_pending(self):
        self._set_mode("retry_pending")

    def tis_unreachable(self):
        self._set_mode("tis_unreachable")

    def cloud_unreachable(self):
        self._set_mode("cloud_unreachable")

    def handshake(self):
        self._set_mode("handshake")

    def downloading(self):
        self._set_mode("downloading")

    def generating(self):
        self._set_mode("generating")

    def uploading(self):
        self._set_mode("uploading")

    def success(self):
        self._set_mode("success", hold_sec=self.config.success_pulse_sec, fallback="idle")

    def error(self):
        self._set_mode("error", hold_sec=self.config.error_hold_sec, fallback="idle")

    def shutdown(self):
        self._set_mode("off")
        self.close()

    def _run(self):
        interval = max(0.1, float(self.config.blink_interval_sec))
        sleep_sec = max(0.05, interval / 2.0)
        while not self._stop.is_set():
            try:
                outputs = self._resolve_outputs()
                self._render(outputs)
            except Exception as exc:
                log.warning("[LED] render error: %s", exc)
            self._stop.wait(sleep_sec)

    def _resolve_outputs(self) -> Tuple[bool, bool, bool]:
        with self._lock:
            mode = self._mode
            started = self._mode_started
            hold_sec = self._mode_hold_sec
            fallback = self._fallback_mode

        elapsed = time.monotonic() - started
        if hold_sec is not None and elapsed >= hold_sec:
            with self._lock:
                self._mode = fallback
                self._mode_started = time.monotonic()
                self._mode_hold_sec = None
                self._fallback_mode = "idle"
            mode = fallback
            started = time.monotonic()
            elapsed = 0.0

        return self._pattern_for_mode(mode, elapsed)

    def _pattern_for_mode(self, mode: str, elapsed: float) -> Tuple[bool, bool, bool]:
        blink = max(0.1, float(self.config.blink_interval_sec))
        tick = int(elapsed / blink)

        if mode == "off":
            return (False, False, False)
        if mode == "boot":
            state = (tick % 2) == 0
            return (state, state, state)
        if mode in ("idle", "network_ok"):
            return (False, False, True)
        if mode in ("waiting_interval", "retry_pending"):
            return (False, (tick % 2) == 0, False)
        if mode == "tis_unreachable":
            return (True, False, False)
        if mode == "cloud_unreachable":
            return ((tick % 2) == 0, False, False)
        if mode in ("handshake", "downloading", "uploading"):
            return (False, (tick % 2) == 0, False)
        if mode == "generating":
            return (False, False, (tick % 2) == 0)
        if mode == "success":
            return (False, False, True)
        if mode == "error":
            return (True, False, False)
        return (False, False, True)

    def _render(self, outputs: Tuple[bool, bool, bool]):
        if outputs == self._last_rendered:
            return
        self._last_rendered = outputs
        if self._backend:
            self._backend.set_outputs(outputs[0], outputs[1], outputs[2])


class NullStatusIndicator(object):
    """No-op indicator saat LED dimatikan."""

    def enabled(self) -> bool:
        return False

    def close(self):
        return None

    def boot(self):
        return None

    def idle(self):
        return None

    def waiting_interval(self):
        return None

    def retry_pending(self):
        return None

    def tis_unreachable(self):
        return None

    def cloud_unreachable(self):
        return None

    def handshake(self):
        return None

    def downloading(self):
        return None

    def generating(self):
        return None

    def uploading(self):
        return None

    def success(self):
        return None

    def error(self):
        return None

    def shutdown(self):
        return None


def create_status_indicator(indicator_config=None):
    cfg = indicator_config or config.indicator
    if not cfg.enabled:
        return NullStatusIndicator()
    return StatusIndicator(cfg)

