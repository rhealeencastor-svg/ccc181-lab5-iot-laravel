#pragma once

// Copy to config.h in this folder and edit the copy. Git ignores config.h.
#define WIFI_SSID "YOUR_WIFI_NAME"
#define WIFI_PASSWORD "YOUR_WIFI_PASSWORD"
#define SERVER_URL "http://192.168.1.10:8000/api/iot/readings"
#define DEVICE_TOKEN "YOUR_DEVICE_TOKEN_FROM_LARAVEL_ENV"
#define DEVICE_ID "ESP32-GREENHOUSE-01"
#define DHT_PIN 4
#define DHT_TYPE DHT22

// Use DHT11 above if that is your actual sensor.
// true generates explicitly simulated measurements when no sensor is available.
#define USE_SIMULATED_READINGS false
