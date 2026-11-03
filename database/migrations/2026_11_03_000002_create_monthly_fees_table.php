<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('monthly_fees', function (Blueprint $table): void {
            $table->id();
            $table->char('period', 7);
            $table->unsignedInteger('amount')->default(10000);
            $table->date('due_date')->nullable();
            $table->timestamps();
            $table->unique('period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_fees');
    }
};
