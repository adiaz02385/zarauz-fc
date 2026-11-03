<?php

namespace App\Filament\Resources\SpecialEventPayments\Pages;

use App\Filament\Resources\SpecialEventPayments\SpecialEventPaymentResource;
use App\Models\SpecialEvent;
use App\Models\SpecialEventPayment;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditSpecialEventPayment extends EditRecord
{
    protected static string $resource = SpecialEventPaymentResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $event = SpecialEvent::query()->findOrFail($data['special_event_id']);

        if (
            $event->status !== SpecialEvent::STATUS_ACTIVE
            && $event->id !== $this->record->special_event_id
        ) {
            throw ValidationException::withMessages([
                'data.special_event_id' => 'No puedes cambiar un pago a un evento finalizado.',
            ]);
        }

        $alreadyRegistered = SpecialEventPayment::query()
            ->where('special_event_id', $event->id)
            ->where('player_id', $data['player_id'])
            ->where('id', '!=', $this->record->getKey())
            ->exists();

        if ($alreadyRegistered) {
            throw ValidationException::withMessages([
                'data.player_id' => 'Este jugador ya tiene otra cuota registrada para el evento seleccionado.',
            ]);
        }

        $data['amount'] = $event->fee_amount;

        return $data;
    }
}
