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
            if (!Schema::hasColumn('vehicle_inspection_damage_points', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('notes');
            }
            // Tornar coordenadas opcionais
            $table->decimal('x_coordinate', 5, 2)->nullable()->change();
            $table->decimal('y_coordinate', 5, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_inspection_damage_points', function (Blueprint $table) {
            $table->dropColumn(['photo_path']);
        });
    }
};
