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
        $table->string('zip_code')->nullable();
        $table->string('street')->nullable();
        $table->string('number')->nullable();
        $table->string('complement')->nullable();
        $table->string('district')->nullable();
        $table->string('city')->nullable();
        $table->string('state')->nullable();

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
