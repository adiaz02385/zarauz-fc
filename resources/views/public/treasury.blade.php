<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zaraúz FC | Tesorería</title>
    <style>
        :root { font-family: Inter, system-ui, sans-serif; color: #172033; background: #f3f5f8; }
        * { box-sizing: border-box; }
        body { margin: 0; }
        .wrap { max-width: 980px; margin: auto; padding: 24px; }
        .hero { background: #063f32; color: white; padding: 22px 26px; border-radius: 20px; display: flex; align-items: center; gap: 18px; }
        .hero img { width: 94px; height: 94px; object-fit: contain; background: #fff; border-radius: 14px; }
        .hero h1 { margin: 0 0 5px; font-size: 28px; }
        .muted { color: #64748b; }
        .hero .muted { color: #d8e8df; }
        .cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin: 18px 0; }
        .card, .panel { background: white; border: 1px solid #e5eaf0; border-radius: 16px; padding: 18px; }
        .value { font-size: 26px; font-weight: 800; margin-top: 8px; }
        .green { color: #15803d; }
        .orange { color: #c2410c; }
        .bar { height: 10px; background: #e8edf2; border-radius: 20px; overflow: hidden; margin-top: 14px; }
        .bar span { display: block; height: 100%; background: #16a34a; }
        input, button, .share { padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 9px; background: white; color: #172033; text-decoration: none; display: inline-block; }
        .share { margin-top: 16px; border-color: #15803d; color: #166534; font-weight: 650; }
        @media (max-width: 650px) { .wrap { padding: 14px; } .cards { grid-template-columns: 1fr; } .value { font-size: 23px; } .hero { padding: 16px; } .hero img { width: 76px; height: 76px; } }
    </style>
</head>
<body>
<main class="wrap">
    <header class="hero">
        <img src="{{ asset('images/zarauz-fc.webp') }}" alt="Escudo Zaraúz FC">
        <div>
            <h1>ZARAÚZ FC</h1>
            <div class="muted">Tesorería · Estado general de mensualidades</div>
        </div>
    </header>

    <section class="cards">
        <div class="card">
            <div class="muted">Recaudado en el período</div>
            <div class="value green">${{ number_format($received, 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="muted">Jugadores al día</div>
            <div class="value">{{ $paid }} / {{ $players->count() }}</div>
        </div>
        <div class="card">
            <div class="muted">Saldo pendiente total</div>
            <div class="value orange">${{ number_format($pending, 0, ',', '.') }}</div>
        </div>
    </section>

    <section class="panel">
        <form method="get">
            <label for="period"><strong>Mes consultado</strong></label>
            <input id="period" name="period" type="month" value="{{ $period }}">
            <button type="submit">Consultar</button>
        </form>

        <div class="bar">
            <span style="width: {{ $players->count() ? min(100, round($paid / $players->count() * 100)) : 0 }}%"></span>
        </div>

        <p class="muted">
            {{ $players->count() ? round($paid / $players->count() * 100) : 0 }}% de jugadores con cuota completa
            · Cuota mensual: ${{ number_format($amount, 0, ',', '.') }}
        </p>
        <p class="muted">Este resumen muestra únicamente cifras agregadas. Cada jugador puede consultar su historial desde el enlace individual entregado por el tesorero.</p>

        <a class="share" href="https://wa.me/?text={{ rawurlencode('Estado de tesorería Zaraúz FC: ' . url()->full()) }}" target="_blank" rel="noopener">
            Compartir resumen por WhatsApp
        </a>
    </section>
</main>
</body>
</html>
