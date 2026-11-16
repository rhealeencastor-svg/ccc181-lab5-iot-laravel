#include <Arduino.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <DHT.h>
#include <time.h>
#include <math.h>

#if __has_include("config.h")
#include "config.h"
#else
#include "config.example.h"
#endif

DHT dht(DHT_PIN, DHT_TYPE);
const unsigned long INTERVAL_MS = 30000;
unsigned long lastReading = 0;
unsigned long lastReconnect = 0;
bool configured = false;

bool hasConfiguration() {
    return String(WIFI_SSID) != "YOUR_WIFI_NAME" && strlen(WIFI_SSID) > 0 &&
           String(DEVICE_TOKEN) != "YOUR_DEVICE_TOKEN_FROM_LARAVEL_ENV" && strlen(DEVICE_TOKEN) > 0;
}

void sendReading() {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("Wi-Fi disconnected; skipping reading.");
        return;
    }

    float temperature;
    float humidity;
    if (USE_SIMULATED_READINGS) {
        temperature = 28.0f + 2.0f * sinf(millis() / 60000.0f);
        humidity = 65.0f + 5.0f * cosf(millis() / 60000.0f);
        Serial.println("SIMULATED reading (not a physical sensor measurement).");
    } else {
        temperature = dht.readTemperature();
        humidity = dht.readHumidity();
    }

    if (!isfinite(temperature) || !isfinite(humidity)) {
        Serial.println("Sensor read failed. Check sensor type, DATA pin, power, and pull-up resistor.");
        return;
    }

    String payload = "{\"device_id\":\"";
    payload += DEVICE_ID;
    payload += "\",\"temperature\":";
    payload += String(temperature, 2);
    payload += ",\"humidity\":";
    payload += String(humidity, 2);

    struct tm timeInfo;
    if (getLocalTime(&timeInfo, 10)) {
        char timestamp[25];
        strftime(timestamp, sizeof(timestamp), "%Y-%m-%dT%H:%M:%SZ", &timeInfo);
        payload += ",\"recorded_at\":\"";
        payload += timestamp;
        payload += "\"";
    } else {
        Serial.println("NTP not ready; sending without optional recorded_at.");
    }
    payload += "}";

    WiFiClient client;
    HTTPClient http;
    http.setTimeout(5000);
    if (!http.begin(client, SERVER_URL)) {
        Serial.println("Cannot initialize HTTP request. Check SERVER_URL.");
        return;
    }
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Accept", "application/json");
    http.addHeader("Authorization", String("Bearer ") + DEVICE_TOKEN);

    int status = http.POST(payload);
    Serial.printf("Temperature: %.2f C | Humidity: %.2f %% | HTTP status: %d\n", temperature, humidity, status);
    if (status > 0) {
        Serial.println(http.getString());
    } else {
        Serial.println(http.errorToString(status));
    }
    http.end();
}

void setup() {
    Serial.begin(115200);
    dht.begin();
    configured = hasConfiguration();
    if (!configured) {
        Serial.println("Copy include/config.example.h to include/config.h and set Wi-Fi, server IP, and token.");
        return;
    }
    WiFi.mode(WIFI_STA);
    WiFi.setAutoReconnect(true);
    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
    Serial.println("Connecting to Wi-Fi...");
    unsigned long started = millis();
    while (WiFi.status() != WL_CONNECTED && millis() - started < 20000) {
        delay(250);
    }
    if (WiFi.status() == WL_CONNECTED) {
        Serial.print("ESP32 IP: ");
        Serial.println(WiFi.localIP());
    } else {
        Serial.println("Connection pending; will retry automatically.");
    }
    configTime(0, 0, "pool.ntp.org", "time.google.com");
    lastReading = millis() - INTERVAL_MS;
    lastReconnect = millis();
}

void loop() {
    if (!configured) {
        delay(250);
        return;
    }
    unsigned long now = millis();
    if (WiFi.status() != WL_CONNECTED && now - lastReconnect >= INTERVAL_MS) {
        lastReconnect = now;
        WiFi.disconnect();
        WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
        Serial.println("Retrying Wi-Fi...");
    }
    if (now - lastReading >= INTERVAL_MS) {
        lastReading = now;
        sendReading();
    }
    delay(10);
}
