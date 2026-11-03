<?php

namespace App\Http\Controllers;

use App\Models\MonthlyFee;
use App\Models\Payment;
use App\Models\PaymentSubmission;
use App\Models\Player;
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
