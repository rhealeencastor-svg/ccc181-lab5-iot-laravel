# API and data flow

```mermaid
flowchart TD
    E["ESP32 + DHT sensor"] -->|"HTTP POST + bearer token"| L["Laravel API"]
    L -->|"Validate and save"| D["MySQL sensor_readings"]
    D -->|"Latest 20 readings"| B["Blade dashboard"]
```

The ESP32 never connects directly to MySQL. Laravel authenticates the device, validates input, and writes the record.

## Endpoints

| Method | Path | Purpose | Authentication |
| --- | --- | --- | --- |
| POST | `/api/iot/readings` | Save one reading | Device bearer token |
| GET | `/api/iot/readings` | Latest 20, newest first | Open for classroom testing |
| GET | `/api/iot/readings/latest` | Latest reading | Open for classroom testing |
| GET | `/iot-dashboard` | Latest values and history | Open for classroom testing |

These HTTP/open-read examples are for a trusted local classroom network. Before internet deployment, use HTTPS and protect reads and the dashboard with user authentication. Keep the Laravel development server on the local network.

## Submit with Postman

Method: **POST**

URL: `http://127.0.0.1:8000/api/iot/readings`

Headers:

| Header | Value |
| --- | --- |
| Content-Type | application/json |
| Accept | application/json |
| Authorization | Bearer YOUR_LOCAL_IOT_DEVICE_TOKEN |

Copy the token from your local `.env` into Postman's local Authorization/Bearer Token field. Do not put it in shared screenshots or exported collections.

Body → raw → JSON:

```json
{
  "device_id": "ESP32-GREENHOUSE-01",
  "temperature": 28.5,
  "humidity": 65.25,
  "recorded_at": "2026-11-16T08:00:00+08:00"
}
```

For live measurements use the current device time, or omit `recorded_at` if its clock is not synchronized. The server always records its own receipt timestamp. Device timestamps are normalized to UTC; the dashboard displays times in `Asia/Manila` by default.

Expected: **201 Created** with `message` and `data` containing `id`, `device_id`, `temperature`, `humidity`, `recorded_at`, `created_at`, and `updated_at`.

## Validation

| Field | Rules |
| --- | --- |
| device_id | Required string, at most 50 characters |
| temperature | Required numeric, -40 through 85 °C |
| humidity | Required numeric, 0 through 100 % |
| recorded_at | Optional/nullable date-time; ISO 8601 recommended |

Invalid input returns **422** with `message` and field-specific `errors`. It does not create a row. Missing or incorrect tokens return **401** before validation. A blank server token also rejects submissions.

The temperature range follows the handout's DHT22 example. For a DHT11, its physical operating range is narrower; set the correct sensor type and explain that difference in your demonstration.

## Read

- `GET http://127.0.0.1:8000/api/iot/readings` returns a JSON array of at most 20 records, newest ID first. An empty database returns `[]`.
- `GET http://127.0.0.1:8000/api/iot/readings/latest` returns one record, or **404** with a message when empty.

The dashboard uses the same ordering and limit. Click **Refresh readings** to load updated data.

## Database

The migration creates `sensor_readings` with an ID, 50-character device ID, two decimal measurements, optional device timestamp, Laravel receipt/update timestamps, and an index on device ID plus creation time. No sample readings are seeded, so the dashboard never pretends to display a real device before one submits data.
