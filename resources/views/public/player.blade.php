<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi portal | Zaraúz FC</title>
    <style>
        :root { font-family: Inter, system-ui, sans-serif; color: #17382f; background: #f0f4f1; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 22px; }
        .wrap { max-width: 760px; margin: auto; }
        .hero { display: flex; align-items: center; gap: 16px; background: #063f32; color: white; padding: 20px 22px; border-radius: 18px; }
        .hero img { width: 76px; height: 76px; object-fit: contain; background: white; border-radius: 12px; }
        .hero h1 { margin: 0 0 4px; letter-spacing: .04em; }
        .hero p { margin: 0; color: #d5e6dc; }
        .panel { margin-top: 18px; background: white; border: 1px solid #dce7df; border-radius: 16px; padding: 22px; box-shadow: 0 8px 28px #0b3b2610; }
        h2 { color: #0b4939; margin: 0 0 14px; font-size: 21px; }
        .muted { color: #65756d; }
        .hello { margin: 22px 0 5px; font-size: 27px; color: #103e31; }
        .item { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 13px 0; border-bottom: 1px solid #e8eee9; }
        .item:last-child { border-bottom: 0; }
        .amount { white-space: nowrap; font-weight: 800; }
        .tag { display: inline-block; border-radius: 999px; padding: 5px 10px; font-size: 12px; font-weight: 750; }
        .tag-paid { color: #166534; background: #dcfce7; }
        .tag-pending { color: #92400e; background: #fef3c7; }
        .tag-rejected { color: #9f1239; background: #ffe4e6; }
        .notice { border-radius: 10px; padding: 12px 14px; margin: 0 0 14px; }
        .notice-success { color: #14532d; background: #dcfce7; }
        .notice-error { color: #991b1b; background: #fee2e2; }
        .notice-info { color: #164e63; background: #ecfeff; }
        .field { margin-bottom: 14px; }
        label { display: block; font-weight: 700; margin-bottom: 6px; font-size: 14px; }
        input, select, textarea { width: 100%; min-height: 44px; padding: 10px 12px; border: 1px solid #cbd8cf; border-radius: 9px; background: #fff; color: #17382f; font: inherit; }
        textarea { min-height: 82px; resize: vertical; }
        input:focus, select:focus, textarea:focus { outline: 2px solid #10a477; border-color: #10a477; }
        .help { font-size: 12px; color: #687970; margin: 5px 0 0; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        button { width: 100%; border: 0; border-radius: 10px; padding: 13px 16px; background: #009b70; color: white; font-weight: 800; font-size: 15px; cursor: pointer; }
        button:hover { background: #087b5d; }
        .security { font-size: 12px; color: #687970; text-align: center; line-height: 1.5; margin: 14px 0 0; }
        ul.errors { margin: 0; padding-left: 20px; }
        @media (max-width: 600px) {
            body { padding: 12px; }
            .hero { padding: 16px; }
            .hero img { width: 62px; height: 62px; }
            .panel { padding: 17px; }
            .grid { grid-template-columns: 1fr; gap: 0; }
            .hello { font-size: 23px; }
        }
    </style>
</head>
<body>
<main class="wrap">
    <header class="hero">
        <img src="{{ asset('images/zarauz-fc.webp') }}" alt="Escudo Zaraúz FC">
        <div>
            <h1>ZARAÚZ FC</h1>
            <p>Portal personal del jugador</p>
        </div>
    </header>

    <h2 class="hello">{{ $player->nickname ?: $player->name }}</h2>
    <p class="muted">Consulta tus mensualidades y envía tus avisos de pago sin iniciar sesión.</p>

    @if (session('success'))
        <div class="notice notice-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="notice notice-error">
            <strong>No pudimos enviar el aviso:</strong>
            <ul class="errors">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="panel">
        <h2>Registrar un pago</h2>
        <p class="muted">Informa el pago que realizaste. Tesorería revisará los datos antes de confirmarlo.</p>
        <div class="notice notice-info">
            Cuota mensual de referencia: <strong>&#36;{{ number_format($monthlyAmount, 0, ',', '.') }}</strong>.
            El monto aplicado se toma de la tarifa configurada para el período seleccionado.
        </div>

        <form method="post" action="{{ route('public.player.payment-notices.store', ['token' => $player->public_token]) }}" enctype="multipart/form-data">
            @csrf
            <div class="grid">
                <div class="field">
                    <label for="period">Mes de la cuota *</label>
                    <input id="period" name="period" type="month" value="{{ old('period', now()->format('Y-m')) }}" max="{{ now()->format('Y-m') }}" required>
                </div>
                <div class="field">
                    <label for="paid_at">Fecha en que pagaste *</label>
                    <input id="paid_at" name="paid_at" type="date" value="{{ old('paid_at', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required>
                </div>
            </div>
            <div class="field">
                <label for="method">Medio de pago *</label>
                <select id="method" name="method" required>
                    <option value="">Selecciona una opción</option>
                    <option value="transferencia" @selected(old('method') === 'transferencia')>Transferencia</option>
                    <option value="efectivo" @selected(old('method') === 'efectivo')>Efectivo</option>
                    <option value="otro" @selected(old('method') === 'otro')>Otro</option>
                </select>
            </div>
            <div class="field">
                <label for="reference">N.º de operación / referencia</label>
                <input id="reference" name="reference" type="text" maxlength="255" value="{{ old('reference') }}" placeholder="Ej.: número de comprobante o transferencia">
            </div>
            <div class="field">
                <label for="attachment">Comprobante fotográfico</label>
                <input id="attachment" name="attachment" type="file" accept="image/jpeg,image/png,image/webp">
                <p class="help">Opcional, pero recomendado. JPG, PNG o WebP. Máximo 2 MB.</p>
            </div>
            <div class="field">
                <label for="notes">Observaciones</label>
                <textarea id="notes" name="notes" maxlength="1000" placeholder="Información adicional para tesorería">{{ old('notes') }}</textarea>
            </div>
            <button type="submit">Enviar aviso de pago</button>
            <p class="security">Enviar este formulario no confirma automáticamente el pago. Tesorería revisará la información antes de incorporarlo al historial oficial.</p>
        </form>
    </section>

    @if ($submissions->isNotEmpty())
        <section class="panel">
            <h2>Mis avisos de pago</h2>
            @foreach ($submissions as $submission)
                @php
                    $periodLabel = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $submission->period . '-01')->locale('es')->translatedFormat('F Y');
                @endphp
                <div class="item">
                    <div>
                        <strong>{{ ucfirst($periodLabel) }}</strong>
                        <div class="muted">Enviado: {{ $submission->created_at?->format('d/m/Y H:i') }}</div>
                        @if ($submission->status === 'rejected' && $submission->rejection_reason)
                            <div class="muted">{{ $submission->rejection_reason }}</div>
                        @endif
                    </div>
                    @if ($submission->status === 'pending')
                        <span class="tag tag-pending">En revisión</span>
                    @elseif ($submission->status === 'approved')
                        <span class="tag tag-paid">Aprobado</span>
                    @else
                        <span class="tag tag-rejected">Rechazado</span>
                    @endif
                </div>
            @endforeach
        </section>
    @endif

    <section class="panel">
        <h2>Mis pagos confirmados</h2>
        @forelse ($payments as $payment)
            @php
                $periodLabel = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $payment->period . '-01')->locale('es')->translatedFormat('F Y');
            @endphp
            <div class="item">
                <div>
                    <strong>{{ ucfirst($periodLabel) }}</strong>
                    <div class="muted">{{ $payment->paid_at?->format('d/m/Y') ?? 'Fecha no informada' }}</div>
                </div>
                <div style="text-align:right">
                    <div class="amount">&#36;{{ number_format($payment->amount, 0, ',', '.') }}</div>
                    <span class="tag tag-paid">Confirmado</span>
                </div>
            </div>
        @empty
            <p class="muted">Todavía no tienes pagos confirmados.</p>
        @endforelse
    </section>

    <p class="security">Este enlace es personal. No lo compartas: permite consultar tu historial y enviar avisos de pago a tu nombre. Si tienes dudas, contacta al tesorero de Zaraúz FC.</p>
</main>
</body>
</html>
