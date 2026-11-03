<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, User $record): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, User $record): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, User $record): bool
    {
        if (! $user->is_admin || $user->is($record)) {
            return false;
        }

        if ($record->is_admin && User::query()->where('is_admin', true)->count() <= 1) {
            return false;
        }

        return true;
    }

    public function deleteAny(User $user): bool
    {
        // User accounts must be reviewed and deleted individually.
        return false;
    }
}
