<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $currentlyActiveAdmin = $this->record->is_admin && $this->record->is_active;
        $willRemainActiveAdmin = (bool) ($data['is_admin'] ?? false)
            && (bool) ($data['is_active'] ?? false);

        if (
            $currentlyActiveAdmin
            && ! $willRemainActiveAdmin
            && User::query()->where('is_admin', true)->where('is_active', true)->count() <= 1
        ) {
            throw ValidationException::withMessages([
                'data.is_admin' => 'No puedes desactivar al último administrador activo del sistema.',
            ]);
        }

        return $data;
    }
}
