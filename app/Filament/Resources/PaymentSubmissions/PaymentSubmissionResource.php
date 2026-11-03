<?php

namespace App\Filament\Resources\PaymentSubmissions;

use App\Filament\Resources\PaymentSubmissions\Pages\ListPaymentSubmissions;
use App\Models\Payment;
use App\Models\PaymentSubmission;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class PaymentSubmissionResource extends Resource
{
    protected static ?string $model = PaymentSubmission::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static string | UnitEnum | null $navigationGroup = 'Tesorería';

    protected static ?string $navigationLabel = 'Avisos de pago';

    protected static ?string $modelLabel = 'aviso de pago';

    protected static ?string $pluralModelLabel = 'avisos de pago';

    public static function getNavigationBadge(): ?string
    {
        $pending = PaymentSubmission::query()
            ->where('status', PaymentSubmission::STATUS_PENDING)
            ->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    private static function periodLabel(string $period): string
    {
        return ucfirst(
            Carbon::parse($period . '-01')
                ->locale('es')
                ->translatedFormat('F Y')
        );
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('player.name')
                    ->label('Jugador')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('period')
                    ->label('Período')
                    ->formatStateUsing(fn (string $state): string => self::periodLabel($state))
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Monto informado')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((int) $state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label('Fecha de pago')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('method')
                    ->label('Medio')
                    ->badge(),
                TextColumn::make('reference')
                    ->label('Referencia')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('attachment_path')
                    ->label('Comprobante')
                    ->formatStateUsing(fn ($state): string => filled($state) ? 'Adjunto' : 'Sin adjunto')
                    ->badge()
                    ->color(fn ($state): string => filled($state) ? 'success' : 'gray'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        PaymentSubmission::STATUS_PENDING => 'Pendiente',
                        PaymentSubmission::STATUS_APPROVED => 'Aprobado',
                        PaymentSubmission::STATUS_REJECTED => 'Rechazado',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        PaymentSubmission::STATUS_PENDING => 'warning',
                        PaymentSubmission::STATUS_APPROVED => 'success',
                        PaymentSubmission::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Enviado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        PaymentSubmission::STATUS_PENDING => 'Pendientes',
                        PaymentSubmission::STATUS_APPROVED => 'Aprobados',
                        PaymentSubmission::STATUS_REJECTED => 'Rechazados',
                    ])
                    ->default(PaymentSubmission::STATUS_PENDING),
                SelectFilter::make('period')
                    ->label('Período')
                    ->options(fn (): array => PaymentSubmission::query()
                        ->select('period')
                        ->distinct()
                        ->orderByDesc('period')
                        ->pluck('period')
                        ->mapWithKeys(fn (string $period): array => [
                            $period => self::periodLabel($period),
                        ])
                        ->all()),
            ])
            ->recordActions([
                Action::make('view_receipt')
                    ->label('Ver comprobante')
                    ->icon('heroicon-o-eye')
                    ->url(fn (PaymentSubmission $record): string => route('admin.payment-submissions.attachment', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (PaymentSubmission $record): bool => filled($record->attachment_path)),
                Action::make('approve')
                    ->label('Aprobar y registrar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar pago')
                    ->modalDescription('Se creará el pago oficial de este jugador y período. Esta acción no se puede repetir.')
                    ->visible(fn (PaymentSubmission $record): bool => $record->status === PaymentSubmission::STATUS_PENDING)
                    ->action(function (PaymentSubmission $record): void {
                        $result = DB::transaction(function () use ($record): string {
                            $submission = PaymentSubmission::query()
                                ->whereKey($record->getKey())
                                ->lockForUpdate()
                                ->firstOrFail();

                            if ($submission->status !== PaymentSubmission::STATUS_PENDING) {
                                return 'already-reviewed';
                            }

                            $existingPayment = Payment::query()
                                ->where('player_id', $submission->player_id)
                                ->where('period', $submission->period)
                                ->first();

                            if ($existingPayment) {
                                $submission->update([
                                    'status' => PaymentSubmission::STATUS_REJECTED,
                                    'rejection_reason' => 'La cuota ya cuenta con un pago registrado por tesorería.',
                                    'reviewed_by' => auth()->id(),
                                    'reviewed_at' => now(),
                                ]);

                                return 'already-paid';
                            }

                            $payment = Payment::query()->create([
                                'player_id' => $submission->player_id,
                                'period' => $submission->period,
                                'amount' => $submission->amount,
                                'paid_at' => $submission->paid_at,
                                'method' => $submission->method,
                                'reference' => $submission->reference,
                                'notes' => $submission->notes,
                                'attachment_path' => $submission->attachment_path,
                                'registered_by' => auth()->id(),
                            ]);

                            $submission->update([
                                'status' => PaymentSubmission::STATUS_APPROVED,
                                'rejection_reason' => null,
                                'reviewed_by' => auth()->id(),
                                'reviewed_at' => now(),
                                'payment_id' => $payment->id,
                            ]);

                            return 'approved';
                        });

                        Notification::make()
                            ->title($result === 'approved' ? 'Pago confirmado' : 'El aviso no se aprobó')
                            ->body(match ($result) {
                                'approved' => 'El pago ya aparece en el historial oficial del jugador.',
                                'already-paid' => 'Ya existe un pago oficial para ese jugador y período.',
                                default => 'Este aviso ya fue revisado.',
                            })
                            ->color($result === 'approved' ? 'success' : 'warning')
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Motivo del rechazo')
                            ->required()
                            ->maxLength(1000),
                    ])
                    ->visible(fn (PaymentSubmission $record): bool => $record->status === PaymentSubmission::STATUS_PENDING)
                    ->action(function (PaymentSubmission $record, array $data): void {
                        DB::transaction(function () use ($record, $data): void {
                            $submission = PaymentSubmission::query()
                                ->whereKey($record->getKey())
                                ->lockForUpdate()
                                ->firstOrFail();

                            if ($submission->status !== PaymentSubmission::STATUS_PENDING) {
                                return;
                            }

                            $submission->update([
                                'status' => PaymentSubmission::STATUS_REJECTED,
                                'rejection_reason' => $data['rejection_reason'],
                                'reviewed_by' => auth()->id(),
                                'reviewed_at' => now(),
                            ]);
                        });

                        Notification::make()
                            ->success()
                            ->title('Aviso rechazado')
                            ->body('El jugador podrá corregir y reenviar el aviso.')
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentSubmissions::route('/'),
        ];
    }
}
