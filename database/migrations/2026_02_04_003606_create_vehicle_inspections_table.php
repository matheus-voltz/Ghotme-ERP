<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos')->onDelete('cascade');
            $table->foreignId('ordem_servico_id')->nullable()->constrained('ordem_servicos')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users');
            
            $table->string('fuel_level')->nullable(); // Reseva, 1/4, 1/2, 3/4, Cheio
            $table->integer('km_current')->nullable();
            $table->text('notes')->nullable(); // Observações gerais (amassados, riscos)
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_inspections');
    }
};