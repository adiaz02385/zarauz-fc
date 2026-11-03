<?php

namespace App\Filament\Resources\SpecialEvents\Pages;

use App\Filament\Resources\SpecialEvents\SpecialEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpecialEvents extends ListRecords
{
    protected static string $resource = SpecialEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Crear evento'),
        ];
    }
}
