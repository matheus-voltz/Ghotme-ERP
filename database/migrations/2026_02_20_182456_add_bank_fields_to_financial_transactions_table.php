<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->string('bank_transaction_id')->nullable()->index(); // FITID do OFX
            $table->string('bank_name')->nullable(); // Nome do banco
            $table->json('bank_metadata')->nullable(); // Dados extras do extrato
        });
    }

    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropColumn(['bank_transaction_id', 'bank_name', 'bank_metadata']);
        });
    }
};
