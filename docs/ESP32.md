# ESP32 setup

Use **one board**: either ESP32-WROOM or ESP32-S3-DevKitC-1. You do not need all the boards from the Smart Home lab.

## Wiring

Disconnect power while wiring. Check the labels/pin diagram for your exact sensor module.

| Sensor connection | ESP32 connection |
| --- | --- |
| VCC / + | 3.3V |
| DATA / OUT | GPIO 4 |
| GND / - | GND |

A bare four-pin DHT sensor generally needs a 4.7–10 kΩ pull-up resistor between DATA and 3.3V. Modules may already have one. Identify pins from the module labels or its manufacturer's diagram; do not assume all modules share a pin order. Keep DATA at 3.3V logic. Power the ESP32 through USB.

## PlatformIO in VS Code

Open the `esp32` folder. Copy `include/greenhouse_config.example.h` to `include/greenhouse_config.h` and edit only the local copy.

| Setting | What to enter |
| --- | --- |
| WIFI_SSID / WIFI_PASSWORD | Your 2.4 GHz Wi-Fi credentials |
| SERVER_URL | `http://COMPUTER_LAN_IP:8000/api/iot/readings` |
| DEVICE_TOKEN | Same IOT_DEVICE_TOKEN as Laravel's local .env |
| DEVICE_ID | An identifier such as ESP32-GREENHOUSE-01 |
| DHT_PIN | 4 for the supplied wiring |
| DHT_TYPE | DHT22 or DHT11, matching your sensor |
| USE_SIMULATED_READINGS | false for a real sensor; true only for a labeled simulation |

Do not use `localhost` or `127.0.0.1` as the ESP32's server address: they refer to the ESP32 itself. Find the computer's Wi-Fi LAN address in macOS System Settings → Wi-Fi → Details → TCP/IP, or run `ipconfig` on Windows.

CLI alternatives to VS Code's Build and Upload buttons:

```bash
cd esp32
pio run -e esp32-wroom
pio run -e esp32-wroom -t upload
pio device monitor --baud 115200
```

Use `-e esp32-s3` instead for the S3. Do not upload until the correct board environment and USB port are selected. PlatformIO downloads the pinned platform and Adafruit libraries on first build.

## Expected behavior

The sketch checks local configuration, connects to Wi-Fi, reads the sensor, and sends JSON every 30 seconds. It attempts Wi-Fi reconnection when disconnected. NTP provides an optional UTC device timestamp; without NTP, readings can still be sent with the server's receipt time. Failed sensor reads are skipped. The sketch does not queue or replay lost readings.

Serial Monitor displays measurement values and HTTP status. **201** means saved; **401** means token mismatch; **422** means invalid input. Negative HTTP codes usually mean the server cannot be reached. Check LAN IP, port 8000, server binding, and the firewall on your trusted network.

This is a local HTTP lab example. Hardware behavior and your exact sensor/board still need verification.

## Arduino IDE alternative

PlatformIO is the primary layout, matching the earlier VS Code lab workflow. For Arduino IDE, create a sketch folder named `GreenhouseMonitor`, copy `src/main.cpp` into `GreenhouseMonitor.ino`, and copy your local `include/greenhouse_config.h` into that folder. Install the Espressif ESP32 board package, Adafruit DHT sensor library, and Adafruit Unified Sensor. Select the matching board and serial port. Then compile and upload. Keep that local credential file out of Git.
