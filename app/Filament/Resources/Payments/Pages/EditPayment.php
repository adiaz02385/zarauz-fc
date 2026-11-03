<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Payment;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $alreadyRegistered = Payment::query()
            ->where('player_id', $data['player_id'])
            ->where('period', $data['period'])
            ->where('id', '!=', $this->record->getKey())
            ->exists();

        if ($alreadyRegistered) {
            throw ValidationException::withMessages([
                'data.period' => 'Este jugador ya tiene otro pago registrado para el mes seleccionado.',
            ]);
        }

        return $data;
    }
}
