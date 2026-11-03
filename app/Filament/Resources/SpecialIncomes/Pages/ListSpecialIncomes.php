<?php

namespace App\Filament\Resources\SpecialIncomes\Pages;

use App\Filament\Resources\SpecialIncomes\SpecialIncomeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpecialIncomes extends ListRecords
{
    protected static string $resource = SpecialIncomeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Registrar ingreso'),
        ];
    }
}
