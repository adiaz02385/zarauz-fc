<?php

namespace App\Filament\Resources\SpecialEventPayments\Pages;

use App\Filament\Resources\SpecialEventPayments\SpecialEventPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpecialEventPayments extends ListRecords
{
    protected static string $resource = SpecialEventPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Registrar pago de evento'),
        ];
    }
}
