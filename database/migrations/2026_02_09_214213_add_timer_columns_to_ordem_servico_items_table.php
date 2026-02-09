<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ordem_servico_items', function (Blueprint $table) {
            $table->enum('status', ['pending', 'in_progress', 'paused', 'completed'])->default('pending')->after('quantity');
            $table->integer('duration_seconds')->default(0)->after('status');
            $table->timestamp('started_at')->nullable()->after('duration_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordem_servico_items', function (Blueprint $table) {
            $table->dropColumn(['status', 'duration_seconds', 'started_at']);
        });
    }
};
