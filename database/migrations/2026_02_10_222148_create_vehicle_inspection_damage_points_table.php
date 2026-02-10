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
        Schema::create('vehicle_inspection_damage_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_inspection_id')->constrained('vehicle_inspections')->onDelete('cascade');
            $table->decimal('x_coordinate', 5, 2); // % X
            $table->decimal('y_coordinate', 5, 2); // % Y
            $table->string('type')->default('risk'); // risk, dent, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_inspection_damage_points');
    }
};
