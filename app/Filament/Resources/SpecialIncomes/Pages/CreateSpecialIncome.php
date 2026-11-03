<?php

namespace App\Filament\Resources\SpecialIncomes\Pages;

use App\Filament\Resources\SpecialIncomes\SpecialIncomeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSpecialIncome extends CreateRecord
{
    protected static string $resource = SpecialIncomeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['registered_by'] = auth()->id();

        return $data;
    }
}
