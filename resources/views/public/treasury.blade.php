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
        .hero { background: #101b32; color: white; padding: 26px; border-radius: 20px; }
        .hero h1 { margin: 0 0 5px; font-size: 28px; }
        .muted { color: #64748b; }
        .hero .muted { color: #cbd5e1; }
        .cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin: 18px 0; }
        .card, .panel { background: white; border: 1px solid #e5eaf0; border-radius: 16px; padding: 18px; }
        .value { font-size: 26px; font-weight: 800; margin-top: 8px; }
        .green { color: #15803d; }
        .orange { color: #c2410c; }
        .bar { height: 10px; background: #e8edf2; border-radius: 20px; overflow: hidden; margin-top: 14px; }
        .bar span { display: block; height: 100%; background: #16a34a; }
        .row { display: flex; justify-content: space-between; gap: 12px; padding: 12px 0; border-bottom: 1px solid #edf0f4; }
        .row:last-child { border: 0; }
        .status { font-size: 12px; font-weight: 700; border-radius: 20px; padding: 5px 9px; background: #dcfce7; color: #166534; }
        .pending { background: #ffedd5; color: #9a3412; }
        input, button { padding: 9px; border: 1px solid #cbd5e1; border-radius: 9px; background: white; }
        @media (max-width: 650px) { .wrap { padding: 14px; } .cards { grid-template-columns: 1fr; } .value { font-size: 23px; } }
    </style>
</head>
<body>
<main class="wrap">
    <header class="hero">
        <h1>ZARAÚZ FC</h1>
        <div class="muted">Tesorería · Estado de mensualidades</div>
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
            <div class="muted">Cuotas por completar</div>
            <div class="value orange">${{ number_format(max(0, ($players->count() * $amount) - $received), 0, ',', '.') }}</div>
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
            · Cuota: ${{ number_format($amount, 0, ',', '.') }}
        </p>

        <h3>Estado general</h3>
        @foreach ($players as $player)
            @php
                $sum = $player->payments->sum('amount');
                $isPaid = $sum >= $amount;
            @endphp
            <div class="row">
                <span>{{ $player->nickname ?: $player->name }}</span>
                <span>
                    <strong>${{ number_format($sum, 0, ',', '.') }}</strong>
                    <span class="status {{ $isPaid ? '' : 'pending' }}">{{ $isPaid ? 'Al día' : 'Pendiente' }}</span>
                </span>
            </div>
        @endforeach
    </section>

    <p class="muted">Información de tesorería de Zaraúz FC. Para consultas personales, utiliza el enlace privado entregado por el tesorero.</p>
</main>
</body>
</html>
