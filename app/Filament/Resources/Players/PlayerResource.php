<?php

namespace App\Filament\Resources\Players;

use App\Filament\Resources\Players\Pages\CreatePlayer;
use App\Filament\Resources\Players\Pages\EditPlayer;
use App\Filament\Resources\Players\Pages\ListPlayers;
use App\Models\Player;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PlayerResource extends Resource
{
    protected static ?string $model = Player::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static string | UnitEnum | null $navigationGroup = 'Tesorería';

    protected static ?string $navigationLabel = 'Jugadores';

    protected static ?string $modelLabel = 'jugador';

    protected static ?string $pluralModelLabel = 'jugadores';

    protected static ?string $recordTitleAttribute = 'name';

    private static function publicPlayerUrl(Player $player): string
    {
        return url('/jugador/' . $player->public_token);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nombre completo')
                ->required()
                ->maxLength(255),
            TextInput::make('identification')
                ->label('Identificación / RUT')
                ->maxLength(30)
                ->placeholder('Ej.: 12.345.678-9')
                ->helperText('Identificación del jugador. Campo opcional.'),
            TextInput::make('nickname')
                ->label('Apodo')
                ->maxLength(255),
            TextInput::make('phone')
                ->label('Teléfono')
                ->tel()
                ->maxLength(30),
            Toggle::make('active')
                ->label('Jugador activo')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('identification')
                    ->label('Identificación / RUT')
                    ->searchable(),
                TextColumn::make('nickname')
                    ->label('Apodo')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Teléfono'),
                TextColumn::make('active')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Activo' : 'Inactivo')
                    ->color(fn ($state): string => $state ? 'success' : 'gray'),
                TextColumn::make('public_token')
                    ->label('Portal del jugador')
                    ->state(fn (Player $record): string => self::publicPlayerUrl($record))
                    ->copyable()
                    ->copyMessage('Enlace personal copiado')
                    ->wrap()
                    ->url(fn (Player $record): string => self::publicPlayerUrl($record))
                    ->openUrlInNewTab(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlayers::route('/'),
            'create' => CreatePlayer::route('/create'),
            'edit' => EditPlayer::route('/{record}/edit'),
        ];
    }
}
