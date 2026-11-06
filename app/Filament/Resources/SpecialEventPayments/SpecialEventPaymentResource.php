<?php

namespace App\Filament\Resources\SpecialEventPayments;

use App\Filament\Resources\SpecialEventPayments\Pages\CreateSpecialEventPayment;
use App\Filament\Resources\SpecialEventPayments\Pages\EditSpecialEventPayment;
use App\Filament\Resources\SpecialEventPayments\Pages\ListSpecialEventPayments;
use App\Models\Player;
use App\Models\SpecialEvent;
use App\Models\SpecialEventPayment;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;
use UnitEnum;

class SpecialEventPaymentResource extends Resource
{
    protected static ?string $model = SpecialEventPayment::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-ticket';

    protected static string | UnitEnum | null $navigationGroup = 'Tesorería';

    protected static ?string $navigationLabel = 'Pagos de eventos';

    protected static ?string $modelLabel = 'pago de evento';

    protected static ?string $pluralModelLabel = 'pagos de eventos';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('special_event_id')
                ->label('Evento')
                ->options(function (?SpecialEventPayment $record): array {
                    return SpecialEvent::query()
                        ->where(function (Builder $query) use ($record): void {
                            $query->where('status', SpecialEvent::STATUS_ACTIVE);

                            // Keep the current event available when editing a payment
                            // that was registered before the event was finalized.
                            if ($record) {
                                $query->orWhereKey($record->special_event_id);
                            }
                        })
                        ->orderByDesc('event_date')
                        ->orderByDesc('id')
                        ->get()
                        ->mapWithKeys(fn (SpecialEvent $event): array => [
                            $event->id => $event->name
                                . ' · ' . $event->event_date->format('d/m/Y')
                                . ' · $' . number_format($event->fee_amount, 0, ',', '.'),
                        ])
                        ->all();
                })
                ->default(fn (): ?int => SpecialEvent::query()
                    ->where('status', SpecialEvent::STATUS_ACTIVE)
                    ->orderByDesc('event_date')
                    ->orderByDesc('id')
                    ->value('id'))
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(function ($state, Set $set): void {
                    $feeAmount = $state
                        ? SpecialEvent::query()->whereKey($state)->value('fee_amount')
                        : null;

                    $set('amount', $feeAmount !== null ? (int) $feeAmount : null);
                })
                ->required(),
            Select::make('player_id')
                ->label('Jugador')
                ->relationship(
                    'player',
                    'name',
                    modifyQueryUsing: fn ($query) => $query->orderBy('name'),
                )
                ->searchable()
                ->preload()
                ->unique(
                    modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule->where('special_event_id', $get('special_event_id')),
                    ignoreRecord: true,
                )
                ->validationMessages([
                    'unique' => 'Este jugador ya tiene una cuota registrada para el evento seleccionado.',
                ])
                ->required(),
            TextInput::make('amount')
                ->label('Cuota registrada')
                ->numeric()
                ->prefix('$')
                ->disabled()
                ->dehydrated(false)
                ->afterStateHydrated(function (TextInput $component, Get $get): void {
                    $eventId = $get('special_event_id');

                    $event = $eventId
                        ? SpecialEvent::query()->find($eventId)
                        : SpecialEvent::query()
                            ->where('status', SpecialEvent::STATUS_ACTIVE)
                            ->orderByDesc('event_date')
                            ->orderByDesc('id')
                            ->first();

                    $component->state($event ? (int) $event->fee_amount : null);
                })
                ->helperText('El monto se obtiene automáticamente desde la cuota definida en el evento.'),
            DatePicker::make('paid_at')
                ->label('Fecha de pago')
                ->default(now())
                ->native(false)
                ->required(),
            Select::make('method')
                ->label('Medio de pago')
                ->options([
                    'transferencia' => 'Transferencia',
                    'efectivo' => 'Efectivo',
                    'otro' => 'Otro',
                ]),
            TextInput::make('reference')
                ->label('N.º de operación / referencia')
                ->maxLength(255),
            FileUpload::make('attachment_path')
                ->label('Comprobante fotográfico')
                ->image()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(2048)
                ->disk('local')
                ->directory('special-event-payment-proofs')
                ->visibility('private')
                ->openable()
                ->downloadable()
                ->helperText('Formatos permitidos: JPG, PNG o WebP. Máximo 2 MB.'),
            Textarea::make('notes')
                ->label('Observaciones')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('specialEvent.name')
                    ->label('Evento')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('specialEvent.event_date')
                    ->label('Fecha del evento')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('player.name')
                    ->label('Jugador')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Monto')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((int) $state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label('Fecha de pago')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('method')
                    ->label('Medio')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('registrar.name')
                    ->label('Registrado por')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('special_event_id')
                    ->label('Evento')
                    ->options(fn (): array => SpecialEvent::query()
                        ->orderByDesc('event_date')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable(),
                SelectFilter::make('player_id')
                    ->label('Jugador')
                    ->options(fn (): array => Player::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('paid_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpecialEventPayments::route('/'),
            'create' => CreateSpecialEventPayment::route('/create'),
            'edit' => EditSpecialEventPayment::route('/{record}/edit'),
        ];
    }
}

