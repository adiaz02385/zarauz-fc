<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('special_events', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->date('event_date');
            $table->unsignedInteger('fee_amount');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('event_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_events');
    }
};
