/**
 * TIS Gateway — ESP32 Firmware
 *
 * Hardware : ESP32 + W5500 USR-ES1 (Ethernet/LAN) + MicroSD
 * Protocol : TIS Sumitomo CP108 — UDP proprietary
 * Function : Ambil failure records dari TIS via Ethernet,
 *            kirim JSON ke CMS via WiFi.
 *            SD card sebagai buffer retry jika WiFi/CMS down.
 *
 * Libraries required (install via Library Manager):
 *   - Ethernet_Generic  by Khoi Hoang
 *   - ArduinoJson       by Benoit Blanchon (v6.x)
 *   - SD                (built-in Arduino/ESP32)
 */

#include <SPI.h>
#include <Ethernet_Generic.h>
#include <EthernetUdp_Generic.h>
#include <SD.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// ============================================================
// [USER CONFIG] — Sesuaikan bagian ini
// ============================================================

// --- PIN ---
#define PIN_W5500_CS     5
#define PIN_W5500_INT    26
#define PIN_W5500_RST    27
#define PIN_SD_CS        4

// --- Ethernet (jaringan TIS) ---
byte   ETH_MAC[]      = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
IPAddress ETH_LOCAL_IP(192, 168, 1, 200);   // IP ESP32 di LAN TIS
IPAddress TIS_IP      (192, 168, 1,   1);   // IP server TIS → sesuaikan

// --- WiFi (jaringan CMS) ---
const char* WIFI_SSID     = "YOUR_SSID";
const char* WIFI_PASSWORD = "YOUR_PASSWORD";

// --- CMS API ---
const char* CMS_API_URL   = "http://192.168.x.x/api/tis/records";
const char* CMS_API_KEY   = "";          // isi jika pakai Bearer token

// --- Session interval ---
#define SESSION_INTERVAL_MS  (5UL * 60UL * 1000UL)   // 5 menit

// ============================================================
// [TIS PROTOCOL CONSTANTS] — jangan ubah kecuali ada data baru
// ============================================================

#define TIS_REMOTE_PORT    262
#define TIS_LOCAL_PORT     263
#define UDP_BUF_SIZE       256    // buffer cukup untuk response terbesar (128B HS)
#define RECV_TIMEOUT_MS    3000
#define MAX_RETRIES        3
#define RETRY_DELAY_MS     500

#define CMD32_PAGES        6
#define CMD34_PAGES        6
#define CMD36_PAGES        40
#define RECORDS_PER_PAGE   5
#define POLLS_PER_PAGE     3
#define POLL_INTERVAL_MS   100
#define POST_HS_DELAY_MS   100

#define HS_RESP_SIZE       128
#define CMD32_RESP_SIZE    18
#define CMD34_RESP_SIZE    26
#define CMD36_RESP_SIZE    112
#define RECORD_SIZE        20
#define CMD36_PAYLOAD_SZ   102   // 1 start + 100 data + 1 end

// --- Handshake packet (fixed 12B, confirmed dari PCAP) ---
const uint8_t PKT_HANDSHAKE[] = {
    0x02, 0x20, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00,
    0x00, 0x03, 0x23, 0x00
};

// ============================================================
// [SD CONFIG]
// ============================================================
#define SD_RETRY_DIR       "/retry"
#define HTTP_TIMEOUT_MS    10000

// ============================================================
// DATA STRUCTURES
// ============================================================

struct FailureRecord {
    int      block_no;
    uint8_t  year, month, day, hour, minute, second;
    uint8_t  car_no;
    uint8_t  occur_recover;
    uint16_t train_id;
    int16_t  location_m;
    uint8_t  equipment_code;
    uint8_t  fault_sub;
    uint16_t fault_code;
    uint8_t  notch_byte;     // ⚠ best-guess — belum dikonfirmasi dari moving train
    uint8_t  speed_kmh;
    uint16_t overhead_v_raw; // × 10 = Volt
};

// ============================================================
// GLOBAL STATE
// ============================================================

EthernetUDP udp;
uint8_t     udpBuf[UDP_BUF_SIZE];
int         rakeId       = 0;
int         blockCounter = 0;
bool        sdAvailable  = false;

// ============================================================
// UTILITY
// ============================================================

// BCD decode: byte 0x26 → 26 (bukan 38)
inline uint8_t bcdDecode(uint8_t b) {
    return (b >> 4) * 10 + (b & 0x0F);
}

// Checksum: sum semua byte kecuali 2 byte terakhir, dibandingkan
// dengan 2 byte terakhir sebagai uint16 big-endian.
// ⚠ Algoritma belum dikonfirmasi — adjust jika checksum_ok selalu false
bool verifyChecksum(uint8_t* buf, int len) {
    if (len < 3) return false;
    uint16_t calc = 0;
    for (int i = 0; i < len - 2; i++) calc += buf[i];
    uint16_t recv = ((uint16_t)buf[len - 2] << 8) | buf[len - 1];
    return (calc == recv);
}

// ============================================================
// PACKET BUILDERS
// ============================================================

void buildCmd32(uint8_t* pkt, uint8_t page) {
    pkt[0] = 0x02; pkt[1] = 0x32;
    pkt[2] = page;  pkt[3] = 0x00;
    pkt[4] = 0x00;  pkt[5] = 0x03;
    pkt[6] = 0x32 - page;   // formula dari analisa PCAP
    pkt[7] = page;
}

void buildCmd34(uint8_t* pkt, uint8_t page) {
    pkt[0] = 0x02; pkt[1] = 0x34;
    pkt[2] = page;
    pkt[3] = (page == 0) ? 0x00 : (uint8_t)(page - 1);
    pkt[4] = 0x00;  pkt[5] = 0x03;
    pkt[6] = (uint8_t)(0x38 - page);   // 0x37 - page + 1
    pkt[7] = page;
}

void buildCmd36(uint8_t* pkt, uint8_t page) {
    pkt[0] = 0x02; pkt[1] = 0x36;
    pkt[2] = 0x00; pkt[3] = 0x00;
    pkt[4] = 0x00; pkt[5] = 0x00;
    pkt[6] = page;
    pkt[7] = 0x03; pkt[8] = 0x35;
    pkt[9] = page;
}

// ============================================================
// UDP SEND + RECEIVE (with retry)
// ============================================================

// Returns: bytes read on success, -1 on failure
int udpSendRecv(uint8_t* req, int reqLen, int expectedLen, int retries = MAX_RETRIES) {
    for (int attempt = 0; attempt < retries; attempt++) {
        udp.beginPacket(TIS_IP, TIS_REMOTE_PORT);
        udp.write(req, reqLen);
        udp.endPacket();

        unsigned long t0 = millis();
        while (millis() - t0 < RECV_TIMEOUT_MS) {
            int pktSize = udp.parsePacket();
            if (pktSize > 0) {
                int n = udp.read(udpBuf, UDP_BUF_SIZE);
                if (n == expectedLen && udpBuf[0] == 0x02) {
                    return n;
                }
            }
            delay(10);
        }

        Serial.printf("[UDP] timeout attempt=%d/%d\n", attempt + 1, retries);
        delay(RETRY_DELAY_MS);
    }
    return -1;
}

// ============================================================
// TIS SESSION PHASES
// ============================================================

bool runHandshake() {
    Serial.println("[HS] Kirim CMD 0x20...");

    int n = udpSendRecv((uint8_t*)PKT_HANDSHAKE, sizeof(PKT_HANDSHAKE), HS_RESP_SIZE);
    if (n < 0) {
        Serial.println("[HS] FAILED — no response");
        return false;
    }

    // rake_id: header 8B + payload[5] = byte index 13
    rakeId = udpBuf[13];
    if (rakeId == 0) {
        Serial.println("[HS] ERROR — rake_id=0, abort");
        return false;
    }

    bool csOk = verifyChecksum(udpBuf, n);
    Serial.printf("[HS] OK — rake_id=%d checksum=%s\n", rakeId, csOk ? "OK" : "WARN");
    delay(POST_HS_DELAY_MS);
    return true;
}

void runCmd32() {
    Serial.printf("[CMD32] Metadata %d pages...\n", CMD32_PAGES);
    uint8_t pkt[8];
    int ok = 0;

    for (uint8_t page = 0; page < CMD32_PAGES; page++) {
        buildCmd32(pkt, page);
        for (int poll = 0; poll < POLLS_PER_PAGE; poll++) {
            if (udpSendRecv(pkt, 8, CMD32_RESP_SIZE, 1) > 0) { ok++; break; }
            delay(POLL_INTERVAL_MS);
        }
    }
    Serial.printf("[CMD32] %d/%d pages OK\n", ok, CMD32_PAGES);
}

void runCmd34() {
    Serial.printf("[CMD34] Dataset B %d pages...\n", CMD34_PAGES);
    uint8_t pkt[8];
    int ok = 0;

    for (uint8_t page = 0; page < CMD34_PAGES; page++) {
        buildCmd34(pkt, page);
        for (int poll = 0; poll < POLLS_PER_PAGE; poll++) {
            if (udpSendRecv(pkt, 8, CMD34_RESP_SIZE, 1) > 0) { ok++; break; }
            delay(POLL_INTERVAL_MS);
        }
    }
    Serial.printf("[CMD34] %d/%d pages OK\n", ok, CMD34_PAGES);
}

// ============================================================
// RECORD PARSER
// ============================================================

FailureRecord parseRecord(uint8_t* rec, int blockNo) {
    FailureRecord r = {};
    r.block_no       = blockNo;

    // [0-5] Timestamp BCD
    r.year           = bcdDecode(rec[0]);
    r.month          = bcdDecode(rec[1]);
    r.day            = bcdDecode(rec[2]);
    r.hour           = bcdDecode(rec[3]);
    r.minute         = bcdDecode(rec[4]);
    r.second         = bcdDecode(rec[5]);

    // [6] Unknown — skip
    // [7] Notch byte (best-guess, EB=0x00 saat depot)
    r.notch_byte     = rec[7];

    // [8] hi-nibble = occur/recover
    r.occur_recover  = (rec[8] >> 4) & 0x01;

    // [9-10] Location [m] int16 big-endian
    r.location_m     = (int16_t)(((uint16_t)rec[9] << 8) | rec[10]);

    // [11] Car ID direct (1-6)
    r.car_no         = rec[11];

    // [12] Equipment code
    r.equipment_code = rec[12];

    // [13] Fault sub-index
    r.fault_sub      = rec[13];

    // [14-15] Fault code uint16 big-endian
    r.fault_code     = ((uint16_t)rec[14] << 8) | rec[15];

    // [16] Overhead Voltage raw (× 10 = Volt)
    r.overhead_v_raw = rec[16];

    // [17] Speed [km/h]
    r.speed_kmh      = rec[17];

    // [18-19] Train ID uint16 big-endian (0xFFFF = depot)
    r.train_id       = ((uint16_t)rec[18] << 8) | rec[19];

    return r;
}

// ============================================================
// JSON BUILDER (1 batch = 5 records per page)
// ============================================================

String buildJson(FailureRecord* records, int count) {
    StaticJsonDocument<2048> doc;

    doc["rake_id"] = rakeId;

    // Gunakan timestamp record pertama sebagai session marker
    char sesTs[24];
    snprintf(sesTs, sizeof(sesTs), "20%02d-%02d-%02dT%02d:%02d:%02d",
             records[0].year, records[0].month, records[0].day,
             records[0].hour, records[0].minute, records[0].second);
    doc["timestamp"] = sesTs;

    JsonArray arr = doc.createNestedArray("records");
    for (int i = 0; i < count; i++) {
        FailureRecord& r = records[i];
        JsonObject obj   = arr.createNestedObject();

        char recTs[24];
        snprintf(recTs, sizeof(recTs), "20%02d-%02d-%02dT%02d:%02d:%02d",
                 r.year, r.month, r.day, r.hour, r.minute, r.second);

        obj["block_no"]       = r.block_no;
        obj["timestamp"]      = recTs;
        obj["car_no"]         = r.car_no;
        obj["occur_recover"]  = r.occur_recover;
        obj["train_id"]       = (r.train_id == 0xFFFF) ? "FFFF" : String(r.train_id);
        obj["location_m"]     = r.location_m;
        obj["equipment_code"] = r.equipment_code;
        obj["fault_sub"]      = r.fault_sub;
        obj["fault_code"]     = r.fault_code;
        obj["notch_byte"]     = r.notch_byte;
        obj["speed_kmh"]      = r.speed_kmh;
        obj["overhead_v"]     = (int)r.overhead_v_raw * 10;
    }

    String out;
    serializeJson(doc, out);
    return out;
}

// ============================================================
// HTTP POST TO CMS
// ============================================================

bool postToCMS(const String& json) {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("[HTTP] WiFi down — skip");
        return false;
    }

    HTTPClient http;
    http.begin(CMS_API_URL);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Accept", "application/json");
    if (strlen(CMS_API_KEY) > 0) {
        http.addHeader("Authorization", String("Bearer ") + CMS_API_KEY);
    }
    http.setTimeout(HTTP_TIMEOUT_MS);

    int code = http.POST(json);
    String resp = http.getString();
    http.end();

    if (code == 200 || code == 201) {
        Serial.printf("[HTTP] OK code=%d\n", code);
        return true;
    }

    Serial.printf("[HTTP] FAILED code=%d resp=%s\n", code, resp.c_str());
    return false;
}

// ============================================================
// SD CARD — Save & Retry
// ============================================================

void saveToSD(const String& json, uint8_t page) {
    if (!sdAvailable) {
        Serial.println("[SD] Not available — data lost for this batch");
        return;
    }

    char path[48];
    snprintf(path, sizeof(path), "%s/r%03d_p%02d.json", SD_RETRY_DIR, rakeId, page);

    File f = SD.open(path, FILE_WRITE);
    if (f) {
        f.print(json);
        f.close();
        Serial.printf("[SD] Buffered: %s (%d bytes)\n", path, json.length());
    } else {
        Serial.printf("[SD] ERROR: cannot write %s\n", path);
    }
}

void retryFromSD() {
    if (!sdAvailable) return;
    if (!SD.exists(SD_RETRY_DIR)) return;

    File dir = SD.open(SD_RETRY_DIR);
    if (!dir || !dir.isDirectory()) return;

    Serial.println("[SD_RETRY] Checking pending batches...");
    int total = 0, success = 0;

    File entry = dir.openNextFile();
    while (entry) {
        if (!entry.isDirectory()) {
            String name = String(entry.name());
            String path = String(SD_RETRY_DIR) + "/" + name;

            // Read file content
            String payload = "";
            payload.reserve(entry.size());
            while (entry.available()) {
                payload += (char)entry.read();
            }
            entry.close();
            total++;

            if (postToCMS(payload)) {
                SD.remove(path.c_str());
                success++;
                Serial.printf("[SD_RETRY] OK + deleted: %s\n", path.c_str());
            } else {
                Serial.printf("[SD_RETRY] Still failed: %s\n", name.c_str());
            }
        } else {
            entry.close();
        }
        entry = dir.openNextFile();
    }
    dir.close();

    if (total > 0) {
        Serial.printf("[SD_RETRY] %d/%d batches flushed\n", success, total);
    }
}

// ============================================================
// TIS SESSION: CMD 0x36 — Failure Records (main data loop)
// ============================================================

void runCmd36() {
    Serial.printf("[CMD36] Failure records %d pages × %d polls...\n",
                  CMD36_PAGES, POLLS_PER_PAGE);

    uint8_t pkt[10];
    FailureRecord batch[RECORDS_PER_PAGE];

    for (uint8_t page = 0; page < CMD36_PAGES; page++) {
        buildCmd36(pkt, page);

        bool pageOk = false;

        for (int poll = 0; poll < POLLS_PER_PAGE && !pageOk; poll++) {
            int n = udpSendRecv(pkt, 10, CMD36_RESP_SIZE, 1);
            if (n != CMD36_RESP_SIZE) {
                delay(POLL_INTERVAL_MS);
                continue;
            }

            // Validate payload markers
            // Response layout: Header(8B) | payload(102B) | Checksum(2B)
            // Payload: [0]=0x00 | records(100B) | [101]=0x03
            uint8_t* payload = udpBuf + 8;
            if (payload[0] != 0x00 || payload[CMD36_PAYLOAD_SZ - 1] != 0x03) {
                Serial.printf("[CMD36] page=0x%02X bad markers\n", page);
                delay(POLL_INTERVAL_MS);
                continue;
            }

            // Parse 5 records dari payload[1..100]
            for (int r = 0; r < RECORDS_PER_PAGE; r++) {
                blockCounter++;
                batch[r] = parseRecord(payload + 1 + (r * RECORD_SIZE), blockCounter);
            }

            Serial.printf("[CMD36] page=0x%02X blk=%d..%d\n",
                          page, blockCounter - RECORDS_PER_PAGE + 1, blockCounter);

            // POST ke CMS; jika gagal simpan ke SD
            String json = buildJson(batch, RECORDS_PER_PAGE);
            if (!postToCMS(json)) {
                saveToSD(json, page);
            }

            pageOk = true;
        }

        if (!pageOk) {
            Serial.printf("[CMD36] page=0x%02X FAILED — skip\n", page);
        }
    }

    Serial.printf("[CMD36] selesai — total %d records\n", blockCounter);
}

// ============================================================
// WIFI
// ============================================================

void connectWiFi() {
    if (WiFi.status() == WL_CONNECTED) return;

    Serial.printf("[WIFI] Connecting to %s", WIFI_SSID);
    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

    for (int i = 0; i < 20 && WiFi.status() != WL_CONNECTED; i++) {
        delay(500);
        Serial.print(".");
    }

    if (WiFi.status() == WL_CONNECTED) {
        Serial.printf("\n[WIFI] Connected — IP: %s\n",
                      WiFi.localIP().toString().c_str());
    } else {
        Serial.println("\n[WIFI] FAILED — SD buffer mode only");
    }
}

// ============================================================
// FULL TIS SESSION
// ============================================================

bool runTISSession() {
    Serial.printf("\n[SESSION_START] host=%s:%d local=%d\n",
                  TIS_IP.toString().c_str(), TIS_REMOTE_PORT, TIS_LOCAL_PORT);

    rakeId       = 0;
    blockCounter = 0;

    udp.begin(TIS_LOCAL_PORT);
    unsigned long t0 = millis();

    bool ok = runHandshake();
    if (!ok) {
        udp.stop();
        return false;
    }

    runCmd32();
    runCmd34();
    runCmd36();

    udp.stop();

    float duration = (millis() - t0) / 1000.0f;
    Serial.printf("[SESSION_RESULT] rake_id=%d records=%d duration=%.1fs\n",
                  rakeId, blockCounter, duration);
    return true;
}

// ============================================================
// SETUP
// ============================================================

void setup() {
    Serial.begin(115200);
    delay(1000);
    Serial.println("\n========== TIS Gateway ESP32 v1.0 ==========");

    // Pastikan kedua CS HIGH sebelum SPI init
    pinMode(PIN_W5500_CS, OUTPUT); digitalWrite(PIN_W5500_CS, HIGH);
    pinMode(PIN_SD_CS,    OUTPUT); digitalWrite(PIN_SD_CS,    HIGH);

    SPI.begin();

    // W5500 hardware reset
    pinMode(PIN_W5500_RST, OUTPUT);
    digitalWrite(PIN_W5500_RST, LOW);
    delay(100);
    digitalWrite(PIN_W5500_RST, HIGH);
    delay(200);

    // Init Ethernet (W5500)
    Ethernet.init(PIN_W5500_CS);
    Serial.print("[ETH] Init...");
    if (Ethernet.begin(ETH_MAC) == 0) {
        Ethernet.begin(ETH_MAC, ETH_LOCAL_IP);
        Serial.printf(" static %s\n", Ethernet.localIP().toString().c_str());
    } else {
        Serial.printf(" DHCP %s\n", Ethernet.localIP().toString().c_str());
    }

    // Init SD
    Serial.print("[SD] Init...");
    if (SD.begin(PIN_SD_CS)) {
        sdAvailable = true;
        if (!SD.exists(SD_RETRY_DIR)) SD.mkdir(SD_RETRY_DIR);
        Serial.println(" OK");
    } else {
        Serial.println(" FAILED — retry buffer disabled");
    }

    // WiFi
    connectWiFi();

    // Flush pending SD batches dari session sebelumnya
    retryFromSD();

    Serial.println("[SETUP] Ready\n");
}

// ============================================================
// LOOP
// ============================================================

void loop() {
    connectWiFi();
    runTISSession();
    retryFromSD();

    // Tunggu interval sebelum sesi berikutnya
    Serial.printf("[LOOP] Next session in %lu sec\n", SESSION_INTERVAL_MS / 1000);
    unsigned long waitUntil = millis() + SESSION_INTERVAL_MS;
    while (millis() < waitUntil) {
        if (WiFi.status() != WL_CONNECTED) connectWiFi();
        delay(5000);
    }
}
