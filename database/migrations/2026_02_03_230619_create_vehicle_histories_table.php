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
        Schema::create('vehicle_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos')->onDelete('cascade');
            // Se for registro interno, liga à OS. Se for externo, fica null.
            $table->foreignId('ordem_servico_id')->nullable()->constrained('ordem_servicos')->onDelete('set null');
            
            $table->date('date'); // Data do evento/serviço
            $table->integer('km'); // Quilometragem no momento
            $table->string('event_type'); // 'os_finalizada', 'manutencao_externa', 'observacao', 'troca_proprietario'
            $table->string('title'); // Ex: "Troca de Óleo", "Revisão 50k"
            $table->text('description')->nullable(); // Detalhes técnicos
            $table->string('performer')->nullable(); // Nome da oficina ou mecânico (se externo)
            $table->decimal('cost', 10, 2)->nullable(); // Custo (opcional para histórico)
            
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_histories');
    }
};