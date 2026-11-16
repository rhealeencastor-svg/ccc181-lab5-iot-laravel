# CCC181 Lab 5: ESP32 + Laravel

A Smart Greenhouse Monitoring System for CCC181 Laboratory No. 5. An ESP32 sends temperature and humidity to a Laravel API. MySQL stores the readings, and a Blade dashboard shows the latest measurement and 20 recent readings.

## Programs

- The repository root is the Laravel 12 application.
- `esp32` is the PlatformIO program for one ESP32-WROOM or ESP32-S3.
- `docs` contains the API guide, wiring, testing checklist, and integration notes.
- `tests/Feature/SensorReadingTest.php` tests the API and dashboard using an isolated in-memory database.

This is a new Lab 5 starter. It does not contain or certify your existing spices CRUD / Lab 4 application. See [integration notes](docs/INTEGRATION.md) before combining projects.

## Run Laravel first

Install PHP 8.2+ with MySQL and SQLite extensions, Composer, and MySQL (XAMPP is fine). No Node.js build is needed for this dashboard.

1. Clone this repo and open its folder in VS Code:

   ```bash
   git clone https://github.com/rhealeencastor-svg/ccc181-lab5-iot-laravel.git
   cd ccc181-lab5-iot-laravel
   ```

2. Install dependencies and prepare local configuration:

   ```bash
   composer install
   php scripts/configure.php
   php artisan key:generate
   ```

3. Start MySQL. Create an empty database named `iot_laravel` using phpMyAdmin or your MySQL client. Edit `DB_*` settings in `.env` to match your local database. The example assumes local MySQL, user `root`, and a blank password; change these if your setup differs.

4. Create the table and start the server:

   ```bash
   php artisan migrate
   php artisan serve
   ```

5. Open http://127.0.0.1:8000/iot-dashboard. It will be empty until you submit a reading. Follow [the API guide](docs/API.md) to test with Postman before connecting hardware.

The configuration script generates a random `IOT_DEVICE_TOKEN` in your local `.env` and preserves an existing token. Use that same token in Postman and the ESP32 configuration. Keep the real `.env` and `esp32/include/greenhouse_config.h` private; Git ignores both. After changing `.env`, run `php artisan config:clear` and restart the server. The app uses `config('iot.device_token')`, so device authentication also works with Laravel's configuration cache.

Composer resolves dependencies for PHP 8.2 compatibility to avoid the PHP-version mismatch encountered in the earlier CRUD setup. Keep the generated `composer.lock` when working as a group.

## Connect one ESP32

Install PlatformIO in VS Code. Open the `esp32` folder as a separate project.

1. Copy `esp32/include/greenhouse_config.example.h` to `esp32/include/greenhouse_config.h`.
2. Set your 2.4 GHz Wi-Fi name and password, computer LAN IP in `SERVER_URL`, and the matching Laravel device token. Use an identifier such as `ESP32-GREENHOUSE-01` with letters, digits, and hyphens.
3. Select `esp32-wroom` for an ESP32-WROOM board or `esp32-s3` for an ESP32-S3-DevKitC-1. Build before uploading.
4. Wire the DHT22 or DHT11 as shown in [the ESP32 guide](docs/ESP32.md). Set `DHT_TYPE` to match it.
5. For the ESP32 connection, stop the localhost-only server and run:

   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

6. Keep the computer and ESP32 on the same trusted Wi-Fi network. Upload, then open Serial Monitor at **115200 baud**. A saved reading returns **HTTP 201**.
7. Refresh the dashboard to see the new reading. The sketch sends every 30 seconds.

The firmware compiles with placeholder configuration, but intentionally does not connect until you add your local settings. If you do not have a sensor, set `USE_SIMULATED_READINGS` to `true`; Serial Monitor labels those readings as simulated. Do not present simulated values as real sensor evidence.

## Test

```bash
composer test
```

Tests cover valid readings, invalid data, authentication, empty configuration, timestamp conversion, the latest 20 records, and dashboard rendering/escaping. They use SQLite in memory and do not touch your MySQL data.

The GitHub Actions workflow runs the PHP tests and builds both ESP32 board targets. See the actual results in the **Actions** tab. The manual checklist and report are in [docs/TESTING.md](docs/TESTING.md).

## Progress

The Laravel API, sensor migration/model, dashboard, ESP32 firmware, configuration examples, and feature tests are implemented. Local PHP syntax and Composer configuration checks are recorded in the testing report. All 11 Laravel feature tests and both ESP32-WROOM/S3 firmware builds passed in [GitHub Actions run #2](https://github.com/rhealeencastor-svg/ccc181-lab5-iot-laravel/actions/runs/43903643194). No physical board has been uploaded or tested, and MySQL/Postman demonstrations and report screenshots remain to be collected.

## Next steps

1. Run Laravel and test valid, invalid, and unauthorized requests in Postman.
2. Configure and upload one ESP32.
3. Confirm HTTP 201, MySQL storage, and dashboard history.
4. Record real results and screenshots for the laboratory report.

## Group commits

Pull before editing. In VS Code Source Control, review and stage your changes, write a short commit message, then push. Each member should use their own GitHub account. Saving a file does not create a commit.

## Source

Requirements follow the supplied *Laboratory No. 5 — Integration of ESP32 / IoT with Laravel Web Application* handout. The app skeleton/configuration follows [Laravel's official 12.x skeleton](https://github.com/laravel/laravel/tree/12.x), under the MIT license. The IoT implementation, dashboard, tests, documentation, and firmware were added for this repository.
