<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('special_event_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('special_event_id')->constrained()->restrictOnDelete();
            $table->foreignId('player_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('amount');
            $table->date('paid_at');
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Un jugador puede pagar una sola cuota por cada evento.
            $table->unique(
                ['special_event_id', 'player_id'],
                'special_event_player_unique'
            );
            $table->index(['special_event_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_event_payments');
    }
};
