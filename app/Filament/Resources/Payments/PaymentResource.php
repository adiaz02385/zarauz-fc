<?php

namespace App\\Filament\\Resources\\Payments;

use App\\Filament\\Resources\\Payments\\Pages\\CreatePayment;
use App\\Filament\\Resources\\Payments\\Pages\\EditPayment;
use App\\Filament\\Resources\\Payments\\Pages\\ListPayments;
use App\\Models\\Payment;
use BackedEnum;
use Filament\\Forms\\Components\\DatePicker;
use Filament\\Forms\\Components\\FileUpload;
use Filament\\Forms\\Components\\Select;
use Filament\\Forms\\Components\\Textarea;
use Filament\\Forms\\Components\\TextInput;
use Filament\\Resources\\Resource;
use Filament\\Schemas\\Schema;
use Filament\\Tables\\Columns\\TextColumn;
use Filament\\Tables\\Table;
use UnitEnum;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static string | UnitEnum | null $navigationGroup = 'Tesorería';

    protected static ?string $navigationLabel = 'Pagos';

    protected static ?string $modelLabel = 'pago';

    protected static ?string $pluralModelLabel = 'pagos';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('player_id')
                ->label('Jugador')
                ->relationship('player', 'name')
                ->searchable()
                ->preload()
                ->required(),
            TextInput::make('period')
                ->label('Mes de la cuota')
                ->default(now()->format('Y-m'))
                ->maxLength(7)
                ->regex('/^\\d{4}-(0[1-9]|1[0-2])$/')
                ->helperText('Formato: AAAA-MM, por ejemplo 2026-11.')
                ->required(),
            TextInput::make('amount')
                ->label('Monto pagado')
                ->numeric()
                ->minValue(1)
                ->prefix('$')
                ->default(10000)
                ->required(),
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
                ->directory('payment-proofs')
                ->visibility('private')
                ->openable()
                ->downloadable()
                ->helperText('Formatos permitidos: JPG, PNG o WebP. Tamaño máximo: 2 MB.'),
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
                TextColumn::make('player.name')
                    ->label('Jugador')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('period')
                    ->label('Período')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Monto')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((int) $state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('method')
                    ->label('Medio')
                    ->badge(),
                TextColumn::make('attachment_path')
                    ->label('Comprobante')
                    ->formatStateUsing(fn ($state): string => filled($state) ? 'Adjunto' : 'Sin adjunto')
                    ->badge()
                    ->color(fn ($state): string => filled($state) ? 'success' : 'gray'),
                TextColumn::make('registrar.name')
                    ->label('Registrado por'),
            ])
            ->recordActions([
                \\Filament\\Actions\\EditAction::make(),
            ])
            ->defaultSort('paid_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            'create' => CreatePayment::route('/create'),
            'edit' => EditPayment::route('/{record}/edit'),
        ];
    }
}
