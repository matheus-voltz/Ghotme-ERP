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
        Schema::create('cfgconfiguracoes_net', function (Blueprint $table) {
            $table->id();

            $table->string('identificacao_validacao', 100)->unique();
            $table->string('descricao_validacao', 255);
            $table->string('valor_configuracao', 255);
            $table->text('valor_complemento')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cfgconfiguracoes_net');
    }
};
