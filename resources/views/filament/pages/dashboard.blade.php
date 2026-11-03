<x-filament-panels::page>
    @php
        $statement = $this->getStatement();
        $periodLabel = \Illuminate\Support\Carbon::createFromFormat('!Y-m', $period)
            ->locale('es')
            ->translatedFormat('F Y');
    @endphp

    <div class="space-y-6">
        <section class="rounded-2xl bg-primary-900 p-5 text-white shadow-sm sm:p-7">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-100">Zaraúz FC · Tesorería</p>
                    <h2 class="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">Estado de cuenta</h2>
                    <p class="mt-2 text-sm text-primary-100">Resumen de ingresos, gastos y saldo del período seleccionado.</p>
                </div>

                <div class="rounded-xl border border-white/20 bg-white/10 p-3">
                    <label for="period" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-primary-100">
                        Período consultado
                    </label>
                    <input
                        id="period"
                        type="month"
                        wire:model.live="period"
                        class="w-full rounded-lg border-0 bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm focus:ring-2 focus:ring-white sm:min-w-52"
                    >
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-arrow-trending-up class="h-5 w-5 text-success-600 dark:text-success-400" />
                    Ingresos del período
                </div>
                <p class="mt-3 text-3xl font-extrabold tracking-tight text-success-700 dark:text-success-400">
                    $ {{ number_format($statement['totalIncome'], 0, ',', '.') }}
                </p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Mensualidades y cuotas de eventos</p>
            </section>

            <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-arrow-trending-down class="h-5 w-5 text-danger-600 dark:text-danger-400" />
                    Gastos del período
                </div>
                <p class="mt-3 text-3xl font-extrabold tracking-tight text-danger-700 dark:text-danger-400">
                    $ {{ number_format($statement['expensesTotal'], 0, ',', '.') }}
                </p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Egresos registrados en tesorería</p>
            </section>

            <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-scale class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                    Saldo del período
                </div>
                <p class="mt-3 text-3xl font-extrabold tracking-tight {{ $statement['netBalance'] < 0 ? 'text-danger-700 dark:text-danger-400' : 'text-primary-700 dark:text-primary-300' }}">
                    $ {{ number_format($statement['netBalance'], 0, ',', '.') }}
                </p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Ingresos menos gastos</p>
            </section>
        </div>

        <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-white/10 sm:px-6">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-base font-bold text-gray-950 dark:text-white">Desglose de movimientos</h3>
                    <span class="text-sm capitalize text-gray-500 dark:text-gray-400">{{ $periodLabel }}</span>
                </div>
            </div>

            <dl class="divide-y divide-gray-100 dark:divide-white/5">
                <div class="flex items-center justify-between gap-4 px-5 py-4 sm:px-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-900 dark:text-white">Mensualidades</dt>
                        <dd class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pagos asociados al mes seleccionado</dd>
                    </div>
                    <dd class="shrink-0 text-sm font-semibold tabular-nums text-success-700 dark:text-success-400">
                        + $ {{ number_format($statement['monthlyIncome'], 0, ',', '.') }}
                    </dd>
                </div>

                <div class="flex items-center justify-between gap-4 px-5 py-4 sm:px-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-900 dark:text-white">Cuotas de eventos especiales</dt>
                        <dd class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pagos recibidos durante el mes seleccionado</dd>
                    </div>
                    <dd class="shrink-0 text-sm font-semibold tabular-nums text-success-700 dark:text-success-400">
                        + $ {{ number_format($statement['eventIncome'], 0, ',', '.') }}
                    </dd>
                </div>

                <div class="flex items-center justify-between gap-4 px-5 py-4 sm:px-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-900 dark:text-white">Gastos</dt>
                        <dd class="mt-1 text-xs text-gray-500 dark:text-gray-400">Egresos con fecha dentro del mes seleccionado</dd>
                    </div>
                    <dd class="shrink-0 text-sm font-semibold tabular-nums text-danger-700 dark:text-danger-400">
                        − $ {{ number_format($statement['expensesTotal'], 0, ',', '.') }}
                    </dd>
                </div>
            </dl>

            <div class="flex flex-col gap-2 bg-gray-50 px-5 py-4 dark:bg-gray-800/60 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <span class="text-sm font-bold text-gray-900 dark:text-white">Saldo neto de {{ $periodLabel }}</span>
                <span class="text-xl font-extrabold tabular-nums {{ $statement['netBalance'] < 0 ? 'text-danger-700 dark:text-danger-400' : 'text-primary-700 dark:text-primary-300' }}">
                    $ {{ number_format($statement['netBalance'], 0, ',', '.') }}
                </span>
            </div>
        </section>

        <p class="text-xs leading-5 text-gray-500 dark:text-gray-400">
            Criterio de cálculo: las mensualidades se imputan al período al que corresponden; las cuotas de eventos y los gastos se consideran según su fecha efectiva de pago o registro.
            El saldo mostrado corresponde al movimiento neto del mes, no al saldo histórico acumulado de caja.
        </p>
    </div>
</x-filament-panels::page>
