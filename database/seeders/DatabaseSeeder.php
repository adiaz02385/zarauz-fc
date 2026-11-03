<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        // No default account is created. Configure both values explicitly.
        if (blank($email) || blank($password)) {
            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Tesorero Zaraúz FC'),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ],
        );
    }
}
