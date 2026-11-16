<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Spice Greenhouse Monitor</title>
    <style>
        :root { color-scheme: light; --ink:#18382d; --muted:#65776d; --line:#dce5db; --paper:#fffef9; }
        * { box-sizing:border-box; }
        body { margin:0; background:#f2f4eb; color:var(--ink); font-family:system-ui,-apple-system,sans-serif; }
        main { max-width:1160px; margin:auto; padding:48px 24px; }
        .eyebrow { font-size:12px; font-weight:700; letter-spacing:.15em; text-transform:uppercase; color:#64774c; }
        header { display:flex; gap:24px; align-items:center; justify-content:space-between; margin-bottom:30px; }
        h1 { margin:9px 0; font-size:clamp(28px,5vw,42px); letter-spacing:-.04em; }
        p { color:var(--muted); line-height:1.6; }
        a.refresh { display:inline-block; padding:12px 18px; border-radius:9px; background:#254f3c; color:white; text-decoration:none; white-space:nowrap; }
        .cards { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; margin:28px 0; }
        .card,.history { background:var(--paper); border:1px solid var(--line); border-radius:16px; padding:24px; }
        .card h2 { font-size:13px; margin:0 0 18px; font-weight:600; color:var(--muted); }
        .value { font-size:37px; font-weight:650; letter-spacing:-.04em; }
        .value small { font-size:19px; font-weight:400; }
        .device,.received { font-size:17px; font-weight:600; line-height:1.5; overflow-wrap:anywhere; }
        .hint { margin:14px 0 0; font-size:12px; }
        .history h2 { margin:0; font-size:21px; letter-spacing:-.02em; }
        .sub { font-size:13px; margin:6px 0 24px; }
        .table-wrap { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; font-size:13px; }
        th { text-align:left; color:var(--muted); font-weight:500; }
        td,th { padding:15px 12px; border-bottom:1px solid var(--line); white-space:nowrap; }
        td:first-child,th:first-child { padding-left:0; }
        tbody tr:last-child td { border:0; }
        .empty { padding:28px 0 16px; }
        .empty strong { font-size:18px; }
        footer { margin-top:22px; color:var(--muted); font-size:12px; }
        @media(max-width:850px) { .cards { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media(max-width:480px) { main { padding:28px 16px; } header { align-items:flex-start; flex-direction:column; } .cards { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<main>
    <header>
        <div>
            <div class="eyebrow">CCC181 · Smart Greenhouse</div>
            <h1>Spice Greenhouse Monitor</h1>
            <p>Temperature and humidity from your ESP32, in one place.</p>
        </div>
        <a class="refresh" href="{{ route('iot.dashboard') }}">Refresh readings</a>
    </header>
    <section class="cards" aria-label="Latest reading">
        <article class="card">
            <h2>Temperature</h2>
            <div class="value">{{ $latest ? number_format($latest->temperature, 2) : '--' }} <small>°C</small></div>
            <p class="hint">Latest sensor reading</p>
        </article>
        <article class="card">
            <h2>Humidity</h2>
            <div class="value">{{ $latest ? number_format($latest->humidity, 2) : '--' }} <small>%</small></div>
            <p class="hint">Relative humidity</p>
        </article>
        <article class="card">
            <h2>Device ID</h2>
            <div class="device">{{ $latest?->device_id ?? 'No device yet' }}</div>
            <p class="hint">Most recent reporting device</p>
        </article>
        <article class="card">
            <h2>Last received</h2>
            <div class="received">{{ $latest ? $latest->created_at->timezone($displayTimezone)->format('M d, Y · h:i:s A') : 'Waiting for data' }}</div>
            <p class="hint">{{ $displayTimezone }}</p>
        </article>
    </section>
    <section class="history" aria-label="Reading history">
        <h2>Reading history</h2>
        <p class="sub">Latest 20 readings · newest first · times shown in {{ $displayTimezone }}</p>
        @if ($readings->isEmpty())
            <div class="empty">
                <strong>No sensor readings yet.</strong>
                <p>Send a reading from your ESP32 or Postman, then refresh this page.</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Device</th><th>Temperature</th><th>Humidity</th><th>Recorded at</th><th>Received at</th></tr></thead>
                    <tbody>
                    @foreach ($readings as $reading)
                        <tr>
                            <td>{{ $reading->id }}</td>
                            <td>{{ $reading->device_id }}</td>
                            <td>{{ number_format($reading->temperature, 2) }} °C</td>
                            <td>{{ number_format($reading->humidity, 2) }} %</td>
                            <td>{{ $reading->recorded_at?->timezone($displayTimezone)->format('M d, h:i:s A') ?? 'Not provided' }}</td>
                            <td>{{ $reading->created_at->timezone($displayTimezone)->format('M d, h:i:s A') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
    <footer>ESP32 + Laravel + MySQL · Click Refresh readings to load new measurements.</footer>
</main>
</body>
</html>
