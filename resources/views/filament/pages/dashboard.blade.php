<x-filament-panels::page>
    @php
        $statement = $this->getStatement();
        $periodLabel = \Illuminate\Support\Carbon::createFromFormat('!Y-m', $period)
            ->locale('es')
            ->translatedFormat('F Y');
        $balanceIsNegative = $statement['netBalance'] < 0;
    @endphp

    <style>
        #treasury-dashboard {
            --td-green: #10b981;
            --td-green-soft: rgba(16, 185, 129, .13);
            --td-orange: #fb923c;
            --td-orange-soft: rgba(251, 146, 60, .13);
            --td-surface: #303638;
            --td-surface-raised: #383f42;
            --td-border: rgba(203, 213, 225, .13);
            --td-muted: #a9b4ba;
            color: #f8fafc;
            display: flex;
            flex-direction: column;
            gap: 22px;
            width: 100%;
        }

        #treasury-dashboard *,
        #treasury-dashboard *::before,
        #treasury-dashboard *::after {
            box-sizing: border-box;
        }

        #treasury-dashboard .td-hero {
            align-items: center;
            background: linear-gradient(120deg, #063f32 0%, #07533f 58%, #0c6048 100%);
            border: 1px solid rgba(110, 231, 183, .18);
            border-radius: 18px;
            box-shadow: 0 12px 28px rgba(0, 0, 0, .16);
            color: #fff;
            display: flex;
            flex-wrap: wrap;
            gap: 22px;
            justify-content: space-between;
            padding: 25px 28px;
        }

        #treasury-dashboard .td-eyebrow {
            color: #b7ead5;
            font-size: 13px;
            font-weight: 650;
            letter-spacing: .045em;
            margin: 0 0 7px;
            text-transform: uppercase;
        }

        #treasury-dashboard .td-title {
            color: #fff;
            font-size: clamp(24px, 3vw, 32px);
            font-weight: 800;
            letter-spacing: -.025em;
            line-height: 1.15;
            margin: 0;
        }

        #treasury-dashboard .td-subtitle {
            color: #d2e9df;
            font-size: 14px;
            line-height: 1.5;
            margin: 8px 0 0;
        }

        #treasury-dashboard .td-period-box {
            background: rgba(255, 255, 255, .09);
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 13px;
            min-width: 220px;
            padding: 12px 14px;
        }

        #treasury-dashboard .td-period-label {
            color: #d2e9df;
            display: block;
            font-size: 11px;
            font-weight: 750;
            letter-spacing: .07em;
            margin: 0 0 8px;
            text-transform: uppercase;
        }

        #treasury-dashboard .td-period-input {
            appearance: auto;
            background: #fff;
            border: 1px solid rgba(255, 255, 255, .35);
            border-radius: 8px;
            color: #172033;
            display: block;
            font: inherit;
            font-size: 14px;
            font-weight: 650;
            min-height: 40px;
            outline: none;
            padding: 8px 11px;
            width: 100%;
        }

        #treasury-dashboard .td-period-input:focus {
            box-shadow: 0 0 0 3px rgba(167, 243, 208, .3);
        }

        #treasury-dashboard .td-metrics {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        #treasury-dashboard .td-card {
            background: var(--td-surface);
            border: 1px solid var(--td-border);
            border-radius: 16px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, .10);
            min-width: 0;
            padding: 20px 21px;
        }

        #treasury-dashboard .td-card-top {
            align-items: center;
            display: flex;
            gap: 11px;
        }

        #treasury-dashboard .td-icon {
            align-items: center;
            border-radius: 10px;
            display: inline-flex;
            flex: 0 0 38px;
            height: 38px;
            justify-content: center;
            width: 38px;
        }

        #treasury-dashboard .td-icon svg {
            height: 19px;
            width: 19px;
        }

        #treasury-dashboard .td-icon--income {
            background: var(--td-green-soft);
            color: #34d399;
        }

        #treasury-dashboard .td-icon--expense {
            background: var(--td-orange-soft);
            color: #fb923c;
        }

        #treasury-dashboard .td-icon--balance {
            background: rgba(96, 165, 250, .13);
            color: #93c5fd;
        }

        #treasury-dashboard .td-card-label {
            color: #c4cdd2;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.4;
        }

        #treasury-dashboard .td-value {
            font-size: clamp(25px, 2.6vw, 32px);
            font-variant-numeric: tabular-nums;
            font-weight: 850;
            letter-spacing: -.035em;
            line-height: 1.2;
            margin: 18px 0 0;
            overflow-wrap: anywhere;
        }

        #treasury-dashboard .td-value--income {
            color: #34d399;
        }

        #treasury-dashboard .td-value--expense {
            color: #fb923c;
        }

        #treasury-dashboard .td-value--balance {
            color: #6ee7b7;
        }

        #treasury-dashboard .td-value--negative {
            color: #fb7185;
        }

        #treasury-dashboard .td-card-help {
            color: var(--td-muted);
            font-size: 12px;
            line-height: 1.5;
            margin: 8px 0 0;
        }

        #treasury-dashboard .td-breakdown {
            background: var(--td-surface);
            border: 1px solid var(--td-border);
            border-radius: 16px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, .10);
            overflow: hidden;
        }

        #treasury-dashboard .td-breakdown-head {
            align-items: center;
            border-bottom: 1px solid var(--td-border);
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: space-between;
            padding: 18px 21px;
        }

        #treasury-dashboard .td-section-title {
            color: #f8fafc;
            font-size: 16px;
            font-weight: 750;
            margin: 0;
        }

        #treasury-dashboard .td-period-chip {
            background: rgba(148, 163, 184, .13);
            border: 1px solid var(--td-border);
            border-radius: 999px;
            color: #d2dbe0;
            font-size: 12px;
            font-weight: 650;
            padding: 6px 11px;
            text-transform: capitalize;
        }

        #treasury-dashboard .td-rows {
            margin: 0;
            padding: 0;
        }

        #treasury-dashboard .td-row {
            align-items: center;
            border-bottom: 1px solid var(--td-border);
            display: flex;
            gap: 18px;
            justify-content: space-between;
            padding: 16px 21px;
        }

        #treasury-dashboard .td-row:last-child {
            border-bottom: 0;
        }

        #treasury-dashboard .td-row-label {
            color: #f1f5f9;
            font-size: 14px;
            font-weight: 650;
            margin: 0;
        }

        #treasury-dashboard .td-row-help {
            color: var(--td-muted);
            font-size: 12px;
            line-height: 1.5;
            margin: 4px 0 0;
        }

        #treasury-dashboard .td-row-amount {
            flex: 0 0 auto;
            font-size: 14px;
            font-variant-numeric: tabular-nums;
            font-weight: 750;
            white-space: nowrap;
        }

        #treasury-dashboard .td-row-amount--income {
            color: #34d399;
        }

        #treasury-dashboard .td-row-amount--expense {
            color: #fb923c;
        }

        #treasury-dashboard .td-total {
            align-items: center;
            background: var(--td-surface-raised);
            border-top: 1px solid var(--td-border);
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: space-between;
            padding: 18px 21px;
        }

        #treasury-dashboard .td-total-label {
            color: #f8fafc;
            font-size: 14px;
            font-weight: 750;
        }

        #treasury-dashboard .td-total-value {
            color: #6ee7b7;
            font-size: 22px;
            font-variant-numeric: tabular-nums;
            font-weight: 850;
            letter-spacing: -.02em;
        }

        #treasury-dashboard .td-total-value--negative {
            color: #fb7185;
        }

        #treasury-dashboard .td-note {
            color: var(--td-muted);
            font-size: 12px;
            line-height: 1.65;
            margin: -5px 1px 0;
        }

        @media (max-width: 900px) {
            #treasury-dashboard .td-metrics {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            #treasury-dashboard .td-card:last-child {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 600px) {
            #treasury-dashboard {
                gap: 15px;
            }

            #treasury-dashboard .td-hero {
                align-items: stretch;
                border-radius: 14px;
                padding: 19px;
            }

            #treasury-dashboard .td-period-box {
                min-width: 0;
                width: 100%;
            }

            #treasury-dashboard .td-metrics {
                grid-template-columns: minmax(0, 1fr);
                gap: 12px;
            }

            #treasury-dashboard .td-card:last-child {
                grid-column: auto;
            }

            #treasury-dashboard .td-card {
                padding: 17px;
            }

            #treasury-dashboard .td-value {
                font-size: 27px;
                margin-top: 13px;
            }

            #treasury-dashboard .td-breakdown-head,
            #treasury-dashboard .td-row,
            #treasury-dashboard .td-total {
                padding-left: 16px;
                padding-right: 16px;
            }

            #treasury-dashboard .td-row {
                align-items: flex-start;
                flex-direction: column;
                gap: 8px;
            }

            #treasury-dashboard .td-row-amount {
                white-space: normal;
            }
        }
    </style>

    <div id="treasury-dashboard">
        <section class="td-hero">
            <div>
                <p class="td-eyebrow">Zaraúz FC · Tesorería</p>
                <h2 class="td-title">Estado de cuenta</h2>
                <p class="td-subtitle">Resumen de ingresos, gastos y saldo del período seleccionado.</p>
            </div>

            <div class="td-period-box">
                <label class="td-period-label" for="treasury-period">Período consultado</label>
                <input
                    id="treasury-period"
                    class="td-period-input"
                    type="month"
                    wire:model.live="period"
                >
            </div>
        </section>

        <div class="td-metrics">
            <section class="td-card">
                <div class="td-card-top">
                    <span class="td-icon td-icon--income" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 17l6-6 4 4 8-9"></path><path d="M15 6h6v6"></path>
                        </svg>
                    </span>
                    <span class="td-card-label">Ingresos del período</span>
                </div>
                <p class="td-value td-value--income">$ {{ number_format($statement['totalIncome'], 0, ',', '.') }}</p>
                <p class="td-card-help">Mensualidades e ingresos especiales</p>
            </section>

            <section class="td-card">
                <div class="td-card-top">
                    <span class="td-icon td-icon--expense" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 7l6 6 4-4 8 9"></path><path d="M15 18h6v-6"></path>
                        </svg>
                    </span>
                    <span class="td-card-label">Gastos del período</span>
                </div>
                <p class="td-value td-value--expense">$ {{ number_format($statement['expensesTotal'], 0, ',', '.') }}</p>
                <p class="td-card-help">Egresos registrados en tesorería</p>
            </section>

            <section class="td-card">
                <div class="td-card-top">
                    <span class="td-icon td-icon--balance" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3v18"></path><path d="M17 7H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </span>
                    <span class="td-card-label">Saldo del período</span>
                </div>
                <p class="td-value {{ $balanceIsNegative ? 'td-value--negative' : 'td-value--balance' }}">
                    $ {{ number_format($statement['netBalance'], 0, ',', '.') }}
                </p>
                <p class="td-card-help">Ingresos menos gastos</p>
            </section>
        </div>

        <section class="td-breakdown">
            <div class="td-breakdown-head">
                <h3 class="td-section-title">Desglose de movimientos</h3>
                <span class="td-period-chip">{{ $periodLabel }}</span>
            </div>

            <dl class="td-rows">
                <div class="td-row">
                    <div>
                        <dt class="td-row-label">Mensualidades</dt>
                        <dd class="td-row-help">Pagos asociados al mes seleccionado</dd>
                    </div>
                    <dd class="td-row-amount td-row-amount--income">
                        + $ {{ number_format($statement['monthlyIncome'], 0, ',', '.') }}
                    </dd>
                </div>

                <div class="td-row">
                    <div>
                        <dt class="td-row-label">Ingresos especiales</dt>
                        <dd class="td-row-help">Aportes, donaciones y otros ingresos extraordinarios del mes</dd>
                    </div>
                    <dd class="td-row-amount td-row-amount--income">
                        + $ {{ number_format($statement['specialIncome'], 0, ',', '.') }}
                    </dd>
                </div>

                <div class="td-row">
                    <div>
                        <dt class="td-row-label">Gastos</dt>
                        <dd class="td-row-help">Egresos con fecha dentro del mes seleccionado</dd>
                    </div>
                    <dd class="td-row-amount td-row-amount--expense">
                        − $ {{ number_format($statement['expensesTotal'], 0, ',', '.') }}
                    </dd>
                </div>
            </dl>

            <div class="td-total">
                <span class="td-total-label">Saldo neto de {{ $periodLabel }}</span>
                <strong class="td-total-value {{ $balanceIsNegative ? 'td-total-value--negative' : '' }}">
                    $ {{ number_format($statement['netBalance'], 0, ',', '.') }}
                </strong>
            </div>
        </section>

        <p class="td-note">
            Criterio de cálculo: las mensualidades se imputan al período al que corresponden; los ingresos especiales y los gastos se consideran según su fecha de ingreso o registro.
            El saldo mostrado corresponde al movimiento neto del mes, no al saldo histórico acumulado de caja.
        </p>
    </div>
</x-filament-panels::page>
