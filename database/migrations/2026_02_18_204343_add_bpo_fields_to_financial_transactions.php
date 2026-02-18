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
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->string('audit_status')->default('pending')->after('status'); // pending, audited, discrepancy
            $table->text('accountant_notes')->nullable()->after('audit_status'); // Espaço para o BPO anotar erros
            $table->timestamp('audited_at')->nullable()->after('accountant_notes'); // Data da auditoria
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropColumn(['audit_status', 'accountant_notes', 'audited_at']);
        });
    }
};
