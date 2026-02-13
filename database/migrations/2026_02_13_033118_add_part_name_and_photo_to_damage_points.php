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
        Schema::table('vehicle_inspection_damage_points', function (Blueprint $table) {
            $table->string('part_name')->nullable()->after('vehicle_inspection_id');
            $table->string('photo_path')->nullable()->after('notes');
            // Make coordinates nullable as mobile uses part_name instead of precise X/Y for now
            $table->decimal('x_coordinate', 5, 2)->nullable()->change();
            $table->decimal('y_coordinate', 5, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_inspection_damage_points', function (Blueprint $table) {
            $table->dropColumn(['part_name', 'photo_path']);
            $table->decimal('x_coordinate', 5, 2)->nullable(false)->change();
            $table->decimal('y_coordinate', 5, 2)->nullable(false)->change();
        });
    }
};
