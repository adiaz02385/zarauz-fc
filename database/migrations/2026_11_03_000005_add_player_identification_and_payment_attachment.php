<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table): void {
            $table->string('identification', 30)->nullable()->index();
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->string('attachment_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn('attachment_path');
        });

        Schema::table('players', function (Blueprint $table): void {
            $table->dropIndex(['identification']);
            $table->dropColumn('identification');
        });
    }
};
