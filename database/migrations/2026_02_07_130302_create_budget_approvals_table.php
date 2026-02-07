<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('budget_id')->constrained('budgets')->onDelete('cascade');
            $table->string('status'); // approved, rejected
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable(); // Navegador/Dispositivo
            $table->text('reason')->nullable(); // Motivo se for rejeitado
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_approvals');
    }
};