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
        .wrap { max-width: 1040px; margin: auto; padding: 24px; }
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
        button { cursor: pointer; }
        .share { margin-top: 16px; border-color: #15803d; color: #166534; font-weight: 650; }
        .account-panel { margin-top: 18px; }
        .account-heading { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .account-heading h2 { margin: 0; color: #123f32; font-size: 20px; }
        .account-period { font-size: 13px; color: #64748b; text-transform: capitalize; }
        .account-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 16px; }
        .account-item { border: 1px solid #e5eaf0; border-radius: 12px; padding: 16px; background: #f8fafc; }
        .account-item .label { color: #64748b; font-size: 13px; }
        .account-item .amount { margin-top: 7px; font-size: 23px; line-height: 1.2; font-weight: 800; font-variant-numeric: tabular-nums; }
        .account-item.income .amount { color: #15803d; }
        .account-item.expense .amount { color: #c2410c; }
        .account-item.balance .amount { color: #15803d; }
        .account-item.balance.negative .amount { color: #c2410c; }
        .account-breakdown { margin: 14px 0 0; color: #64748b; font-size: 13px; line-height: 1.6; }
        .roster-panel { margin-top: 18px; }
        .roster-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 12px; }
        .roster-header h2 { margin: 0; color: #123f32; font-size: 20px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 560px; }
        th, td { text-align: left; padding: 12px 10px; border-bottom: 1px solid #edf0f4; }
        th { font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
        td { font-size: 14px; }
        .money { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .status { display: inline-flex; align-items: center; border-radius: 999px; padding: 5px 10px; font-size: 12px; font-weight: 750; white-space: nowrap; }
        .status-paid { color: #166534; background: #dcfce7; }
        .status-partial { color: #92400e; background: #fef3c7; }
        .status-pending { color: #9f1239; background: #ffe4e6; }
        .legend { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
        .legend .status { font-weight: 650; }
        @media (max-width: 650px) {
            .wrap { padding: 14px; }
            .cards { grid-template-columns: 1fr; }
            .account-grid { grid-template-columns: 1fr; }
            .value { font-size: 23px; }
            .hero { padding: 16px; }
            .hero img { width: 76px; height: 76px; }
        }
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
            <div class="value green">\${{ number_format($received, 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="muted">Jugadores al día</div>
            <div class="value">{{ $paid }} / {{ $players->count() }}</div>
        </div>
        <div class="card">
            <div class="muted">Saldo pendiente total</div>
            <div class="value orange">\${{ number_format($pending, 0, ',', '.') }}</div>
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
            · Cuota mensual: \${{ number_format($amount, 0, ',', '.') }}
        </p>
        <a class="share" href="https://wa.me/?text={{ rawurlencode('Estado de tesorería Zaraúz FC: ' . url()->full()) }}" target="_blank" rel="noopener">
            Compartir este mes por WhatsApp
        </a>
    </section>

    <section class="panel account-panel">
        <div class="account-heading">
            <h2>Estado de cuenta actual</h2>
            <span class="account-period">
                {{ \Illuminate\Support\Carbon::createFromFormat('!Y-m', $period)->locale('es')->translatedFormat('F Y') }}
            </span>
        </div>

        <div class="account-grid">
            <div class="account-item income">
                <div class="label">Ingresos del período</div>
                <div class="amount">$ {{ number_format($totalIncome, 0, ',', '.') }}</div>
            </div>
            <div class="account-item expense">
                <div class="label">Gastos del período</div>
                <div class="amount">$ {{ number_format($expensesTotal, 0, ',', '.') }}</div>
            </div>
            <div class="account-item balance {{ $netBalance < 0 ? 'negative' : '' }}">
                <div class="label">Saldo del período</div>
                <div class="amount">$ {{ number_format($netBalance, 0, ',', '.') }}</div>
            </div>
        </div>

        <p class="account-breakdown">
            Ingresos por mensualidades: $ {{ number_format($monthlyIncome, 0, ',', '.') }}
            · Ingresos especiales: $ {{ number_format($specialIncome, 0, ',', '.') }}.
            El saldo corresponde a ingresos menos gastos del mes consultado.
        </p>
    </section>

    <section class="panel roster-panel">
        <div class="roster-header">
            <h2>Estado de jugadores — {{ $period }}</h2>
            <span class="muted">{{ $players->count() }} jugadores activos</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Jugador</th>
                        <th>Estado</th>
                        <th class="money">Pagado</th>
                        <th class="money">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($players as $player)
                        @php
                            $playerPaid = (int) $player->payments->sum('amount');
                            $balance = max(0, $amount - $playerPaid);

                            if ($playerPaid >= $amount) {
                                $statusLabel = 'Al día';
                                $statusClass = 'status-paid';
                            } elseif ($playerPaid > 0) {
                                $statusLabel = 'Abono parcial';
                                $statusClass = 'status-partial';
                            } else {
                                $statusLabel = 'Pendiente';
                                $statusClass = 'status-pending';
                            }
                        @endphp
                        <tr>
                            <td><strong>{{ $player->nickname ?: $player->name }}</strong></td>
                            <td><span class="status {{ $statusClass }}">{{ $statusLabel }}</span></td>
                            <td class="money">\${{ number_format($playerPaid, 0, ',', '.') }}</td>
                            <td class="money">\${{ number_format($balance, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="muted">No hay jugadores activos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="legend">
            <span class="status status-paid">Al día</span>
            <span class="status status-partial">Abono parcial</span>
            <span class="status status-pending">Pendiente</span>
        </div>
        <p class="muted">El estado corresponde al mes seleccionado y considera la suma de los pagos y abonos registrados. Este listado es visible públicamente.</p>
    </section>
</main>
</body>
</html>
