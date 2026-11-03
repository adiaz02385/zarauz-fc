<?php

namespace App\Filament\Resources\SpecialEvents;

use App\Filament\Resources\SpecialEvents\Pages\CreateSpecialEvent;
use App\Filament\Resources\SpecialEvents\Pages\EditSpecialEvent;
use App\Filament\Resources\SpecialEvents\Pages\ListSpecialEvents;
use App\Models\SpecialEvent;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SpecialEventResource extends Resource
{
    protected static ?string $model = SpecialEvent::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string | UnitEnum | null $navigationGroup = 'Tesorería';

    protected static ?string $navigationLabel = 'Eventos especiales';

    protected static ?string $modelLabel = 'evento especial';

    protected static ?string $pluralModelLabel = 'eventos especiales';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nombre del evento')
                ->placeholder('Ej.: Asado de fin de año')
                ->required()
                ->maxLength(255),
            DatePicker::make('event_date')
                ->label('Fecha del evento')
                ->default(now())
                ->native(false)
                ->required(),
            TextInput::make('fee_amount')
                ->label('Valor de la cuota por jugador')
                ->numeric()
                ->minValue(1)
                ->prefix('$')
                ->required()
                ->helperText('Este será el monto único que se registrará para cada jugador.'),
            Select::make('status')
                ->label('Estado del evento')
                ->options([
                    SpecialEvent::STATUS_ACTIVE => 'Activo',
                    SpecialEvent::STATUS_FINISHED => 'Finalizado',
                ])
                ->default(SpecialEvent::STATUS_ACTIVE)
                ->required(),
            Textarea::make('description')
                ->label('Detalle / observaciones')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Evento')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('fee_amount')
                    ->label('Cuota por jugador')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((int) $state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('payments_count')
                    ->label('Jugadores que pagaron')
                    ->counts('payments')
                    ->sortable(),
                TextColumn::make('payments_sum_amount')
                    ->label('Total recaudado')
                    ->state(fn (SpecialEvent $record): int => (int) ($record->payments_sum_amount ?? 0))
                    ->formatStateUsing(fn ($state): string => '$' . number_format((int) $state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        SpecialEvent::STATUS_ACTIVE => 'Activo',
                        SpecialEvent::STATUS_FINISHED => 'Finalizado',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        SpecialEvent::STATUS_ACTIVE => 'success',
                        SpecialEvent::STATUS_FINISHED => 'gray',
                        default => 'warning',
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('event_date', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withSum('payments', 'amount');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpecialEvents::route('/'),
            'create' => CreateSpecialEvent::route('/create'),
            'edit' => EditSpecialEvent::route('/{record}/edit'),
        ];
    }
}
