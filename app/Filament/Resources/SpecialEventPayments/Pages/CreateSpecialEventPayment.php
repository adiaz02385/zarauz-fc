<?php

namespace App\Filament\Resources\SpecialEventPayments\Pages;

use App\Filament\Resources\SpecialEventPayments\SpecialEventPaymentResource;
use App\Models\SpecialEvent;
use App\Models\SpecialEventPayment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateSpecialEventPayment extends CreateRecord
{
    protected static string $resource = SpecialEventPaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $event = SpecialEvent::query()->findOrFail($data['special_event_id']);

        if ($event->status !== SpecialEvent::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'data.special_event_id' => 'No se pueden registrar pagos en un evento finalizado.',
            ]);
        }

        $alreadyRegistered = SpecialEventPayment::query()
            ->where('special_event_id', $event->id)
            ->where('player_id', $data['player_id'])
            ->exists();

        if ($alreadyRegistered) {
            throw ValidationException::withMessages([
                'data.player_id' => 'Este jugador ya tiene una cuota registrada para el evento seleccionado.',
            ]);
        }

        $data['amount'] = $event->fee_amount;
        $data['registered_by'] = auth()->id();

        return $data;
    }
}
