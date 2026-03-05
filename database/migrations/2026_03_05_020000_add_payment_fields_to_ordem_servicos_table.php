<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordem_servicos', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('status')->comment('cash, debit, credit, pix');
            $table->string('gateway_payment_id')->nullable()->after('payment_method')->comment('ID da cobrança no gateway (ex: Asaas payment ID)');
            $table->timestamp('paid_at')->nullable()->after('gateway_payment_id')->comment('Data/hora que o pagamento foi confirmado');
        });
    }

    public function down(): void
    {
        Schema::table('ordem_servicos', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'gateway_payment_id', 'paid_at']);
        });
    }
};
