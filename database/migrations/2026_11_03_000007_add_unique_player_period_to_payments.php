<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const INDEX_NAME = 'payments_player_period_unique';

    public function up(): void
    {
        // Preserve existing records. If historical duplicates exist, keep them
        // and rely on the form + server-side validation for new registrations.
        $duplicates = DB::table('payments')
            ->select('player_id', 'period')
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('player_id', 'period')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isNotEmpty()) {
            Log::warning(
                'Skipped payments unique index because historical duplicate player/month payments exist.',
                ['duplicates' => $duplicates->toArray()],
            );

            return;
        }

        Schema::table('payments', function (Blueprint $table): void {
            $table->unique(['player_id', 'period'], self::INDEX_NAME);
        });
    }

    public function down(): void
    {
        $indexExists = collect(Schema::getIndexes('payments'))
            ->contains(fn (array $index): bool => ($index['name'] ?? null) === self::INDEX_NAME);

        if ($indexExists) {
            Schema::table('payments', function (Blueprint $table): void {
                $table->dropUnique(self::INDEX_NAME);
            });
        }
    }
};
