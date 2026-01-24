<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('veiculos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable();

            // Relacionamento
            $table->foreignId('cliente_id')->constrained('clients');

            // Identificação
            $table->string('placa', 10)->unique();
            $table->string('renavam', 20)->nullable();
            $table->string('chassi', 30)->nullable();

            // Dados principais
            $table->string('marca', 50);
            $table->string('modelo', 80);
            $table->string('versao', 80)->nullable();
            $table->integer('ano_fabricacao')->nullable();
            $table->integer('ano_modelo')->nullable();
            $table->string('cor', 30)->nullable();
            $table->string('combustivel', 30)->nullable();
            $table->string('cambio', 30)->nullable();
            $table->string('motor', 30)->nullable();

            // Controle
            $table->integer('km_atual')->nullable();
            $table->date('ultima_revisao')->nullable();
            $table->date('proxima_revisao')->nullable();

            // Sistema
            $table->boolean('ativo')->default(true);
            $table->string('origem', 30)->default('sistema');

            // Auditoria
            $table->foreignId('criado_por')->nullable()->constrained('users');
            $table->foreignId('atualizado_por')->nullable()->constrained('users');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('veiculos');
    }
};
