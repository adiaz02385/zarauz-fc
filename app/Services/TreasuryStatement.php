<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\SpecialIncome;
use Illuminate\Support\Carbon;

class TreasuryStatement
{
    /**
     * Calculate the cash movement for one calendar month.
     */
    public function forPeriod(string $period): array
    {
        return $this->forPeriodRange($period, $period);
    }

    /**
     * Calculate the cash movement for an inclusive range of calendar months.
     *
     * Monthly dues are attributed to the month they cover (payments.period).
     * Special incomes and expenses are attributed to their transaction dates.
     * Special event contributions are intentionally excluded from this statement.
     */
    public function forPeriodRange(string $startPeriod, string $endPeriod): array
    {
        $start = Carbon::createFromFormat('!Y-m', $startPeriod)
            ->startOfMonth()
            ->toDateString();
        $end = Carbon::createFromFormat('!Y-m', $endPeriod)
            ->endOfMonth()
            ->toDateString();

        $monthlyIncome = (int) Payment::query()
            ->whereBetween('period', [$startPeriod, $endPeriod])
            ->sum('amount');

        $specialIncome = (int) SpecialIncome::query()
            ->whereBetween('income_date', [$start, $end])
            ->sum('amount');

        $expensesTotal = (int) Expense::query()
            ->whereBetween('expense_date', [$start, $end])
            ->sum('amount');

        $totalIncome = $monthlyIncome + $specialIncome;

        return [
            'monthlyIncome' => $monthlyIncome,
            'specialIncome' => $specialIncome,
            'totalIncome' => $totalIncome,
            'expensesTotal' => $expensesTotal,
            'netBalance' => $totalIncome - $expensesTotal,
        ];
    }
}
