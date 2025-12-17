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
        Schema::create('clients', function (Blueprint $table) {
        $table->id();

        // Tipo de cliente
        $table->enum('type', ['PF', 'PJ']);

        // PF
        $table->string('name')->nullable();
        $table->string('cpf')->nullable();
        $table->string('rg')->nullable();
        $table->date('birth_date')->nullable();

        // PJ
        $table->string('company_name')->nullable();
        $table->string('trade_name')->nullable();
        $table->string('cnpj')->nullable();
        $table->string('state_registration')->nullable();
        $table->string('municipal_registration')->nullable();

        // Contato
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        $table->string('whatsapp')->nullable();

        // Endereço
        $table->string('cep')->nullable();
        $table->string('rua')->nullable();
        $table->string('numero')->nullable();
        $table->string('complemento')->nullable();
        $table->string('bairro')->nullable();
        $table->string('cidade')->nullable();
        $table->string('estado')->nullable();

        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
