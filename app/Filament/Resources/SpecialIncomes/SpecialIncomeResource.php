<?php

namespace App\Filament\Resources\SpecialIncomes;

use App\Filament\Resources\SpecialIncomes\Pages\CreateSpecialIncome;
use App\Filament\Resources\SpecialIncomes\Pages\EditSpecialIncome;
use App\Filament\Resources\SpecialIncomes\Pages\ListSpecialIncomes;
use App\Models\SpecialIncome;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class SpecialIncomeResource extends Resource
{
    protected static ?string $model = SpecialIncome::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static string | UnitEnum | null $navigationGroup = 'Tesorería';

    protected static ?string $navigationLabel = 'Ingresos especiales';

    protected static ?string $modelLabel = 'ingreso especial';

    protected static ?string $pluralModelLabel = 'ingresos especiales';

    private static function categories(): array
    {
        return [
            'aporte' => 'Aporte extraordinario',
            'donacion' => 'Donación',
            'patrocinio' => 'Patrocinio',
            'reembolso' => 'Reembolso',
            'venta' => 'Venta / actividad',
            'otros' => 'Otros',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Descripción del ingreso')
                ->placeholder('Ej.: Donación para compra de balones')
                ->required()
                ->maxLength(255),
            Select::make('category')
                ->label('Categoría')
                ->options(self::categories())
                ->default('otros')
                ->required(),
            TextInput::make('amount')
                ->label('Monto recibido')
                ->numeric()
                ->minValue(1)
                ->prefix('$')
                ->required(),
            DatePicker::make('income_date')
                ->label('Fecha de ingreso')
                ->default(now())
                ->native(false)
                ->required(),
            Select::make('method')
                ->label('Medio de ingreso')
                ->options([
                    'transferencia' => 'Transferencia',
                    'efectivo' => 'Efectivo',
                    'otro' => 'Otro',
                ]),
            TextInput::make('reference')
                ->label('N.º de operación / referencia')
                ->maxLength(255),
            FileUpload::make('attachment_path')
                ->label('Comprobante')
                ->image()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(2048)
                ->disk('local')
                ->directory('special-income-proofs')
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
                TextColumn::make('title')
                    ->label('Descripción')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Categoría')
                    ->formatStateUsing(fn (string $state): string => self::categories()[$state] ?? ucfirst($state))
                    ->badge(),
                TextColumn::make('amount')
                    ->label('Monto')
                    ->formatStateUsing(fn ($state): string => '$' . number_format((int) $state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('income_date')
                    ->label('Fecha')
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
                SelectFilter::make('category')
                    ->label('Categoría')
                    ->options(self::categories()),
                SelectFilter::make('method')
                    ->label('Medio de ingreso')
                    ->options([
                        'transferencia' => 'Transferencia',
                        'efectivo' => 'Efectivo',
                        'otro' => 'Otro',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('income_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpecialIncomes::route('/'),
            'create' => CreateSpecialIncome::route('/create'),
            'edit' => EditSpecialIncome::route('/{record}/edit'),
        ];
    }
}
