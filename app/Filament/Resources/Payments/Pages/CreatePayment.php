<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Payment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $alreadyRegistered = Payment::query()
            ->where('player_id', $data['player_id'])
            ->where('period', $data['period'])
            ->exists();

        if ($alreadyRegistered) {
            throw ValidationException::withMessages([
                'data.period' => 'Este jugador ya tiene un pago registrado para el mes seleccionado.',
            ]);
        }

        $data['registered_by'] = auth()->id();

        return $data;
    }
}
