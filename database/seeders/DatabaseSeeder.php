<?php

namespace Database\Seeders;

use App\Models\MonthlyFee;
use App\Models\Payment;
use App\Models\Player;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        // Optional: no default administrator credentials are ever created.
        if (filled($email) && filled($password)) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => env('ADMIN_NAME', 'Tesorero Zaraúz FC'),
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                    'is_admin' => true,
                ],
            );
        }

        // Opening roster supplied by the treasurer on 03-11-2026.
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

        foreach (['2026-10', '2026-11'] as $period) {
            MonthlyFee::updateOrCreate(
                ['period' => $period],
                ['amount' => (int) env('ZARAUZ_MONTHLY_FEE', 10000)],
            );
        }

        $octoberPaid = array_values(array_filter(
            $roster,
            fn (string $name): bool => $name !== 'Luis Salgado',
        ));

        // Green checks on the supplied list identify November payments received to date.
        $novemberPaid = [
            'Gonzalo Álvarez',
            'Jonathan Riveros',
            'Víctor Zamorano',
            'Rodrigo Sanzana',
            'Alexander Díaz',
            'Felipe Zamorano',
            'Gerardo Pereira',
            'Daniel Riveros',
            'Luis Salgado',
        ];

        $openingPayments = [
            '2026-10' => $octoberPaid,
            '2026-11' => $novemberPaid,
        ];

        foreach ($openingPayments as $period => $paidPlayers) {
            foreach ($paidPlayers as $name) {
                $reference = 'initial-roster-' . $period;

                Payment::firstOrCreate(
                    [
                        'player_id' => $players[$name]->id,
                        'period' => $period,
                        'reference' => $reference,
                    ],
                    [
                        'amount' => (int) env('ZARAUZ_MONTHLY_FEE', 10000),
                        // The image confirms payment status, not the exact day paid.
                        'paid_at' => null,
                        'method' => 'otro',
                        'notes' => 'Carga inicial informada por tesorería el 03-11-2026. Fecha exacta de pago no informada.',
                    ],
                );
            }
        }
    }
}
