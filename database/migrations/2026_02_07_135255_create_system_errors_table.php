<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_errors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Quem causou o erro (se logado)
            $table->string('url')->nullable(); // Onde aconteceu
            $table->string('method')->nullable(); // GET, POST
            $table->string('error_type'); // Exception class
            $table->text('message'); // Mensagem técnica
            $table->longText('stack_trace')->nullable(); // Onde no código
            $table->json('request_data')->nullable(); // O que o usuário enviou
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_errors');
    }
};