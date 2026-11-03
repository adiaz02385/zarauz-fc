<?php

namespace App\Filament\Resources\SpecialEvents\Pages;

use App\Filament\Resources\SpecialEvents\SpecialEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSpecialEvent extends CreateRecord
{
    protected static string $resource = SpecialEventResource::class;
}
