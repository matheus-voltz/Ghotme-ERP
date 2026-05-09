<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Índices para multi-tenancy
        Schema::table('ordem_servicos', function (Blueprint $table) {
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'created_at']);
            $table->index(['company_id', 'updated_at']);
            $table->index(['company_id', 'client_id']);
        });

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->index(['company_id', 'type', 'status']);
            $table->index(['company_id', 'paid_at']);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->index(['company_id', 'name']);
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'created_at']);
        });

        Schema::table('ordem_servico_items', function (Blueprint $table) {
            $table->index(['ordem_servico_id', 'service_id']);
        });

        Schema::table('ordem_servico_parts', function (Blueprint $table) {
            $table->index(['ordem_servico_id']);
        });
    }

    public function down(): void
    {
        Schema::table('ordem_servicos', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'status']);
            $table->dropIndex(['company_id', 'created_at']);
            $table->dropIndex(['company_id', 'updated_at']);
            $table->dropIndex(['company_id', 'client_id']);
        });

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'type', 'status']);
            $table->dropIndex(['company_id', 'paid_at']);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'name']);
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'status']);
            $table->dropIndex(['company_id', 'created_at']);
        });

        Schema::table('ordem_servico_items', function (Blueprint $table) {
            $table->dropIndex(['ordem_servico_id', 'service_id']);
        });

        Schema::table('ordem_servico_parts', function (Blueprint $table) {
            $table->dropIndex(['ordem_servico_id']);
        });
    }
};
