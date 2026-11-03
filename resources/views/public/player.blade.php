<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi cuenta | Zaraúz FC</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f3f5f8; color: #172033; margin: 0; padding: 22px; }
        .box { max-width: 680px; margin: auto; background: white; padding: 24px; border-radius: 18px; }
        .muted { color: #64748b; }
        .item { padding: 13px 0; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; gap: 12px; }
    </style>
</head>
<body>
<main class="box">
    <h1>ZARAÚZ FC</h1>
    <p class="muted">Estado personal de mensualidades</p>
    <h2>{{ $player->nickname ?: $player->name }}</h2>

    @forelse ($payments as $payment)
        <div class="item">
            <span>{{ $payment->period }} · {{ $payment->paid_at->format('d/m/Y') }}</span>
            <strong>${{ number_format($payment->amount, 0, ',', '.') }}</strong>
        </div>
    @empty
        <p>No hay pagos registrados.</p>
    @endforelse

    <p class="muted">Si encuentras una diferencia, contacta al tesorero.</p>
</main>
</body>
</html>
