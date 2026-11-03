<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\SpecialEventPayment;
use Illuminate\Support\Carbon;

class TreasuryStatement
{
    /**
     * Calculate the cash movement for a selected calendar month.
     *
     * Monthly dues are attributed to the month they cover (payments.period).
     * Event contributions and expenses are attributed to their transaction dates.
     */
    public function forPeriod(string $period): array
    {
        $month = Carbon::createFromFormat('!Y-m', $period);
        $start = $month->copy()->startOfMonth()->toDateString();
        $end = $month->copy()->endOfMonth()->toDateString();

        $monthlyIncome = (int) Payment::query()
            ->where('period', $period)
            ->sum('amount');

        $eventIncome = (int) SpecialEventPayment::query()
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount');

        $expensesTotal = (int) Expense::query()
            ->whereBetween('expense_date', [$start, $end])
            ->sum('amount');

        $totalIncome = $monthlyIncome + $eventIncome;

        return [
            'monthlyIncome' => $monthlyIncome,
            'eventIncome' => $eventIncome,
            'totalIncome' => $totalIncome,
            'expensesTotal' => $expensesTotal,
            'netBalance' => $totalIncome - $expensesTotal,
        ];
    }
}
