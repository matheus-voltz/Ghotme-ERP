<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['in', 'out']); // in = Receber, out = Pagar
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            
            $table->date('due_date'); // Data de vencimento
            $table->dateTime('paid_at')->nullable(); // Data do pagamento real
            
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            
            $table->string('category')->nullable(); // Ex: Aluguel, Peças, Mão de Obra
            
            // Relacionamentos opcionais
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            
            // Link genérico para OS ou outras tabelas
            $table->string('related_type')->nullable(); 
            $table->unsignedBigInteger('related_id')->nullable();
            
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};