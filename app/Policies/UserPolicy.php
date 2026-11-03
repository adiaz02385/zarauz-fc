<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin && $user->is_active;
    }

    public function view(User $user, User $record): bool
    {
        return $user->is_admin && $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_admin && $user->is_active;
    }

    public function update(User $user, User $record): bool
    {
        return $user->is_admin && $user->is_active;
    }

    public function delete(User $user, User $record): bool
    {
        if (! $user->is_admin || ! $user->is_active || $user->is($record)) {
            return false;
        }

        if (
            $record->is_admin
            && $record->is_active
            && User::query()->where('is_admin', true)->where('is_active', true)->count() <= 1
        ) {
            return false;
        }

        return true;
    }

    public function deleteAny(User $user): bool
    {
        // Users must be reviewed and deleted individually.
        return false;
    }
}
