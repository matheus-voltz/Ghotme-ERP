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
        Schema::table('ordem_servicos', function (Blueprint $table) {
            $table->string('device_password')->nullable()->after('description');
            $table->string('device_pattern_lock')->nullable()->after('device_password')->comment('Ex: 1-2-3-6-9 for a 3x3 grid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordem_servicos', function (Blueprint $table) {
            $table->dropColumn(['device_password', 'device_pattern_lock']);
        });
    }
};
