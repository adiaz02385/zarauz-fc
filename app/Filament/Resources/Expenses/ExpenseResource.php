<?php

namespace App\Filament\Resources\Expenses;

use App\Filament\Resources\Expenses\Pages\CreateExpense;
use App\Filament\Resources\Expenses\Pages\EditExpense;
use App\Filament\Resources\Expenses\Pages\ListExpenses;
use App\Models\Expense;
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

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static string | UnitEnum | null $navigationGroup = 'Tesorería';

    protected static ?string $navigationLabel = 'Gastos';

    protected static ?string $modelLabel = 'gasto';

    protected static ?string $pluralModelLabel = 'gastos';

    private static function categories(): array
    {
        return [
            'alimentacion' => 'Alimentación',
            'cancha' => 'Cancha / arriendo',
            'arbitraje' => 'Arbitraje',
            'equipamiento' => 'Equipamiento',
            'transporte' => 'Transporte',
            'otros' => 'Otros',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Descripción del gasto')
                ->required()
                ->maxLength(255),
            Select::make('category')
                ->label('Categoría')
                ->options(self::categories())
                ->default('otros')
                ->required(),
            TextInput::make('amount')
                ->label('Monto')
                ->numeric()
                ->minValue(1)
                ->prefix('$')
                ->required(),
            DatePicker::make('expense_date')
                ->label('Fecha del gasto')
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
                ->label('Comprobante')
                ->image()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(2048)
                ->disk('local')
                ->directory('expense-proofs')
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
                TextColumn::make('expense_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('method')
                    ->label('Medio de pago')
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
                    ->label('Medio de pago')
                    ->options([
                        'transferencia' => 'Transferencia',
                        'efectivo' => 'Efectivo',
                        'otro' => 'Otro',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('expense_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExpenses::route('/'),
            'create' => CreateExpense::route('/create'),
            'edit' => EditExpense::route('/{record}/edit'),
        ];
    }
}
