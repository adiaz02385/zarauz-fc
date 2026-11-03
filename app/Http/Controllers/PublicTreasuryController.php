<?php

namespace App\Http\Controllers;

use App\Models\MonthlyFee;
use App\Models\Payment;
use App\Models\PaymentSubmission;
use App\Models\Player;
use App\Services\TreasuryStatement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PublicTreasuryController
{
    public function index(Request $request)
    {
        // Keep the previous ?period=YYYY-MM query parameter working as a
        // one-month shortcut while the public form uses start/end periods.
        $startPeriod = $request->query(
            'start_period',
            $request->query('period', now()->format('Y-m'))
        );
        $endPeriod = $request->query('end_period', $startPeriod);

        $validPeriod = fn ($value): bool => is_string($value)
            && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value) === 1;

        abort_unless(
            $validPeriod($startPeriod)
            && $validPeriod($endPeriod)
            && $startPeriod <= $endPeriod,
            404
        );

        $startMonth = Carbon::createFromFormat('!Y-m', $startPeriod)->startOfMonth();
        $endMonth = Carbon::createFromFormat('!Y-m', $endPeriod)->startOfMonth();
        $startDate = $startMonth->toDateString();
        $endDate = $endMonth->copy()->endOfMonth()->toDateString();

        // Sum the monthly fee configured for each month in the selected range.
        // Months without an explicit fee use the club's configured default.
        $defaultMonthlyFee = (int) config('zarauz.monthly_fee', 10000);
        $monthlyFees = MonthlyFee::query()
            ->whereBetween('period', [$startPeriod, $endPeriod])
            ->pluck('amount', 'period');

        $amount = 0;
        $monthCount = 0;

        for ($month = $startMonth->copy(); $month->lessThanOrEqualTo($endMonth); $month->addMonth()) {
            $monthPeriod = $month->format('Y-m');
            $amount += (int) ($monthlyFees->get($monthPeriod) ?? $defaultMonthlyFee);
            $monthCount++;
        }

        $players = Player::query()
            ->where('active', true)
            ->with(['payments' => fn ($query) => $query
                ->whereBetween('period', [$startPeriod, $endPeriod])])
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

        $statement = app(TreasuryStatement::class)
            ->forPeriodRange($startPeriod, $endPeriod);

        $periodLabel = ucfirst($startMonth->copy()->locale('es')->translatedFormat('F Y'));

        if ($startPeriod !== $endPeriod) {
            $periodLabel .= ' - ' . $endMonth->copy()->locale('es')->translatedFormat('F Y');
        }

        return view('public.treasury', array_merge(
            compact(
                'startPeriod',
                'endPeriod',
                'periodLabel',
                'monthCount',
                'amount',
                'players',
                'paid',
                'received',
                'pending',
            ),
            $statement,
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

        $submissions = $player->paymentSubmissions()
            ->orderByDesc('created_at')
            ->get();

        $monthlyAmount = (int) (
            MonthlyFee::query()
                ->where('period', now()->format('Y-m'))
                ->value('amount')
            ?? config('zarauz.monthly_fee', 10000)
        );

        return view('public.player', compact('player', 'payments', 'submissions', 'monthlyAmount'));
    }

    public function storePaymentNotice(Request $request, string $token): RedirectResponse
    {
        $player = Player::query()
            ->where('public_token', $token)
            ->where('active', true)
            ->firstOrFail();

        $data = $request->validate([
            'period' => [
                'required',
                'date_format:Y-m',
                'regex:/^\d{4}-(0[1-9]|1[0-2])$/',
            ],
            'paid_at' => ['required', 'date', 'before_or_equal:today'],
            'method' => ['required', Rule::in(['transferencia', 'efectivo', 'otro'])],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'attachment' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'period.date_format' => 'Selecciona un período válido.',
            'paid_at.before_or_equal' => 'La fecha de pago no puede ser futura.',
            'attachment.image' => 'El comprobante debe ser una imagen.',
            'attachment.mimes' => 'El comprobante debe estar en formato JPG, PNG o WebP.',
            'attachment.max' => 'El comprobante no puede superar los 2 MB.',
        ]);

        $periodDate = Carbon::createFromFormat('Y-m-d', $data['period'] . '-01')->startOfMonth();

        if ($periodDate->greaterThan(now()->startOfMonth())) {
            throw ValidationException::withMessages([
                'period' => 'No puedes informar el pago de un mes futuro.',
            ]);
        }

        $period = $data['period'];

        if (Payment::query()
            ->where('player_id', $player->id)
            ->where('period', $period)
            ->exists()) {
            throw ValidationException::withMessages([
                'period' => 'Tesorería ya tiene un pago confirmado para este período.',
            ]);
        }

        if (PaymentSubmission::query()
            ->where('player_id', $player->id)
            ->where('period', $period)
            ->where('status', PaymentSubmission::STATUS_PENDING)
            ->exists()) {
            throw ValidationException::withMessages([
                'period' => 'Ya tienes un aviso pendiente de revisión para este período.',
            ]);
        }

        $amount = (int) (
            MonthlyFee::query()->where('period', $period)->value('amount')
            ?? config('zarauz.monthly_fee', 10000)
        );

        $attachmentPath = $request->file('attachment')?->store('payment-submissions', 'local');
        $previousAttachmentPath = null;

        try {
            DB::transaction(function () use (
                $player,
                $period,
                $data,
                $amount,
                $attachmentPath,
                &$previousAttachmentPath,
            ): void {
                Player::query()->whereKey($player->id)->lockForUpdate()->firstOrFail();

                if (Payment::query()
                    ->where('player_id', $player->id)
                    ->where('period', $period)
                    ->exists()) {
                    throw ValidationException::withMessages([
                        'period' => 'Tesorería ya tiene un pago confirmado para este período.',
                    ]);
                }

                $submission = PaymentSubmission::query()
                    ->where('player_id', $player->id)
                    ->where('period', $period)
                    ->lockForUpdate()
                    ->first();

                if ($submission?->status === PaymentSubmission::STATUS_PENDING) {
                    throw ValidationException::withMessages([
                        'period' => 'Ya tienes un aviso pendiente de revisión para este período.',
                    ]);
                }

                if ($submission?->status === PaymentSubmission::STATUS_APPROVED) {
                    throw ValidationException::withMessages([
                        'period' => 'Este período ya fue aprobado por tesorería.',
                    ]);
                }

                $attributes = [
                    'player_id' => $player->id,
                    'period' => $period,
                    'amount' => $amount,
                    'paid_at' => $data['paid_at'],
                    'method' => $data['method'],
                    'reference' => $data['reference'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'attachment_path' => $attachmentPath,
                    'status' => PaymentSubmission::STATUS_PENDING,
                    'rejection_reason' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'payment_id' => null,
                ];

                if ($submission) {
                    $previousAttachmentPath = $submission->attachment_path;
                    $submission->fill($attributes)->save();
                } else {
                    PaymentSubmission::query()->create($attributes);
                }
            });
        } catch (Throwable $exception) {
            if ($attachmentPath) {
                Storage::disk('local')->delete($attachmentPath);
            }

            throw $exception;
        }

        if ($previousAttachmentPath && $previousAttachmentPath !== $attachmentPath) {
            Storage::disk('local')->delete($previousAttachmentPath);
        }

        return redirect()
            ->route('public.player', ['token' => $token])
            ->with('success', 'Tu aviso de pago fue enviado. Quedará pendiente hasta que tesorería revise los datos.');
    }

    public function downloadSubmissionAttachment(PaymentSubmission $submission): StreamedResponse
    {
        $user = auth()->user();

        abort_unless($user && $user->is_admin && $user->is_active, 403);
        abort_unless(filled($submission->attachment_path), 404);
        abort_unless(Storage::disk('local')->exists($submission->attachment_path), 404);

        return Storage::disk('local')->download(
            $submission->attachment_path,
            'comprobante-' . $submission->player_id . '-' . $submission->period,
        );
    }
}
