<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('os_prefix')->default('OS-');
            $table->integer('os_next_number')->default(1);
            $table->integer('budget_validity_days')->default(7);
            $table->text('os_terms')->nullable(); // Termos de garantia/contrato no rodapé
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};