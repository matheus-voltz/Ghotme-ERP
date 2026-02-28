<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('interval')->default('month'); // month, week, year
            $table->integer('interval_count')->default(1);
            $table->string('niche')->nullable(); // Para filtrar planos por nicho (automotive, pet, etc)
            $table->boolean('is_active')->default(true);
            
            // Integração com Gateway
            $table->string('external_plan_id')->nullable(); // ID do plano no Asaas/Stripe
            $table->string('gateway')->nullable(); // asaas, stripe
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_plans');
    }
};
