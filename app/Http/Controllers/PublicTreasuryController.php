<?php

namespace App\Http\Controllers;

use App\Models\MonthlyFee;
use App\Models\Player;
use Illuminate\Http\Request;

class PublicTreasuryController
{
    public function index(Request $request)
    {
        $period = $request->query('period', now()->format('Y-m'));
        abort_unless(preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period), 404);

        $fee = MonthlyFee::where('period', $period)->first();
        $amount = $fee?->amount ?? (int) config('zarauz.monthly_fee', 10000);

        $players = Player::query()
            ->where('active', true)
            ->with(['payments' => fn ($query) => $query->where('period', $period)])
            ->orderBy('name')
            ->get();

        $paid = $players->filter(
            fn (Player $player) => $player->payments->sum('amount') >= $amount
        )->count();

        $received = $players->sum(
            fn (Player $player) => $player->payments->sum('amount')
        );

        $pending = $players->sum(
            fn (Player $player) => max(0, $amount - $player->payments->sum('amount'))
        );

        return view('public.treasury', compact(
            'period',
            'amount',
            'players',
            'paid',
            'received',
            'pending',
        ));
    }

    public function player(string $token)
    {
        $player = Player::query()
            ->where('public_token', $token)
            ->where('active', true)
            ->firstOrFail();

        $payments = $player->payments()
            ->orderByDesc('period')
            ->orderByDesc('paid_at')
            ->get();

        return view('public.player', compact('player', 'payments'));
    }
}
