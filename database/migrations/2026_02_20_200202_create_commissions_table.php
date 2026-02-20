<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('user_id')->index(); // Funcionário que ganhou
            $table->unsignedBigInteger('ordem_servico_id')->nullable()->index();
            $table->string('description');
            $table->decimal('base_amount', 15, 2); // Valor do serviço/venda
            $table->decimal('percentage', 5, 2); // % aplicada
            $table->decimal('commission_amount', 15, 2); // R$ final da comissão
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('ordem_servico_id')->references('id')->on('ordem_servicos')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
