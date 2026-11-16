# Lab 5 testing report

## Preparation checks

| Check | Actual result | Status |
| --- | --- | --- |
| PHP source syntax | 27 PHP source files passed php -l | Pass |
| Composer project configuration | composer validate --strict passed | Pass |
| Full Laravel feature tests | All 11 feature tests passed in GitHub Actions run #1 (SQLite in memory) | Pass |
| ESP32 compilation | Initial build exposed a configuration-header name collision; renamed to greenhouse_config.h and awaiting the next run | Pending |
| Physical ESP32, sensor, Wi-Fi and MySQL integration | No hardware demonstration performed | Not run |

GitHub Actions runs the Laravel feature tests and both ESP32 builds after pushes. Its result verifies those automated checks only, not physical hardware or your local MySQL setup. Inspect the Actions tab and record the actual run outcome.

## Automated feature coverage

The feature suite uses an isolated in-memory SQLite database. It checks:

- Valid POST returns 201 and saves the record.
- Offset device timestamps are normalized to UTC.
- Omitted optional timestamp is accepted.
- Missing/wrong tokens return 401 and save no row.
- Blank configured token fails closed.
- Missing fields, out-of-range values, nonnumeric values, long/invalid device IDs, and invalid dates return 422.
- Range boundaries are accepted.
- Empty history returns an empty array; empty latest returns 404.
- History is limited to 20, newest first.
- Empty and populated dashboard states work; device text is escaped.

## Manual demonstration checklist

Replace **Not run** with your real actual result and Pass/Fail after testing. Keep credentials out of screenshots.

| Test case | Expected result | Actual result | Status |
| --- | --- | --- | --- |
| Run migration against local MySQL | sensor_readings table created | Not run | Pending |
| Valid Postman reading | 201; one row in MySQL | Not run | Pending |
| temperature 150, humidity 120 | 422; no new row | Not run | Pending |
| Missing or incorrect token | 401; no new row | Not run | Pending |
| GET history and latest | Correct newest records | Not run | Pending |
| Open dashboard before any submissions | Clear empty state | Not run | Pending |
| ESP32 joins Wi-Fi | Serial Monitor shows connection | Not run | Pending |
| Real DHT sensor measurement | Plausible temperature/humidity, not simulation | Not run | Pending |
| ESP32 submits reading | Serial Monitor shows HTTP 201 | Not run | Pending |
| Refresh dashboard | Values, device ID and last-received time update | Not run | Pending |
| Wi-Fi interruption | Failure logged; reconnect attempted | Not run | Pending |

## Submission evidence

Capture the successful migration, table/records in MySQL, Postman 201/401/422 responses, Serial Monitor with real measurements and 201 (or explicitly labeled simulation if permitted), and populated dashboard. Include the architecture and API guide from docs/API.md, explain the data flow in your own words, and submit your completed results with the source code. Instructor approval determines whether a hardware-free demonstration is sufficient.
