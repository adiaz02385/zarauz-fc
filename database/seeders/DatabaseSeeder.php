<?php

namespace Database\Seeders;

use App\Models\MonthlyFee;
use App\Models\Payment;
use App\Models\Player;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $email = Str::lower(trim((string) config('zarauz.admin.email', '')));
        $password = (string) config('zarauz.admin.password', '');
        $adminName = trim((string) config('zarauz.admin.name', 'Tesorero Zaraúz FC'));

        if ($email !== '' && $password !== '') {
            $admin = User::query()->where('email', $email)->first()
                ?? User::query()->where('is_admin', true)->orderBy('id')->first()
                ?? new User();

            User::query()
                ->where('is_admin', true)
                ->when($admin->exists, fn ($query) => $query->where('id', '!=', $admin->id))
                ->update(['is_admin' => false]);

            $admin->forceFill([
                'name' => $adminName !== '' ? $adminName : 'Tesorero Zaraúz FC',
                'email' => $email,
                'password' => $password,
                'email_verified_at' => now(),
                'is_admin' => true,
                'is_active' => true,
            ])->save();

            logger()->info('Zarauz FC administrator account synchronized.', [
                'user_id' => $admin->id,
            ]);
        } else {
            logger()->warning('Zarauz FC administrator was not synchronized: ADMIN_EMAIL or ADMIN_PASSWORD is empty.');
        }

        $roster = [
            'Gonzalo Álvarez',
            'Cristián Bueno',
            'Jonathan Riveros',
            'Guillermo Pavez',
            'Víctor Zamorano',
            'Rodrigo Sanzana',
            'Alexander Díaz',
            'Daniel Correa',
            'Sergio Pezo',
            'Felipe Zamorano',
            'Aarón Jiménez',
            'Gerardo Pereira',
            'Daniel Riveros',
            'Julio Siau',
            'Harold Cortés',
            'Luis Salgado',
        ];

        $players = [];
        foreach ($roster as $name) {
            $players[$name] = Player::firstOrCreate(
                ['name' => $name],
                ['active' => true],
            );
        }

        $monthlyFee = (int) config('zarauz.monthly_fee', 10000);

        foreach (['2026-10', '2026-11'] as $period) {
            MonthlyFee::updateOrCreate(
                ['period' => $period],
                ['amount' => $monthlyFee],
            );
        }

        $openingPayments = [
            '2026-10' => array_values(array_filter(
                $roster,
                fn (string $name): bool => $name !== 'Luis Salgado',
            )),
            '2026-11' => [
                'Gonzalo Álvarez',
                'Jonathan Riveros',
                'Víctor Zamorano',
                'Rodrigo Sanzana',
                'Alexander Díaz',
                'Felipe Zamorano',
                'Gerardo Pereira',
                'Daniel Riveros',
                'Luis Salgado',
            ],
        ];

        foreach ($openingPayments as $period => $paidPlayers) {
            foreach ($paidPlayers as $name) {
                Payment::firstOrCreate(
                    [
                        'player_id' => $players[$name]->id,
                        'period' => $period,
                        'reference' => 'initial-roster-' . $period,
                    ],
                    [
                        'amount' => $monthlyFee,
                        'paid_at' => null,
                        'method' => 'otro',
                        'notes' => 'Carga inicial informada por tesorería el 03-11-2026. Fecha exacta de pago no informada.',
                    ],
                );
            }
        }
    }
}
