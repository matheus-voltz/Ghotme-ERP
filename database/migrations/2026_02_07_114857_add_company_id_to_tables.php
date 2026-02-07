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
        // Lista de tabelas corrigida com os nomes reais do seu banco
        $tables = [
            'users',
            'clients',
            'veiculos', // Corrigido de 'vehicles' para 'veiculos'
            'ordem_servicos',
            'budgets',
            'inventory_items',
            'suppliers',
            'services',
            'service_packages',
            'financial_transactions',
            'payment_methods',
            'vehicle_histories',
            'stock_movements',
            'company_settings',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'company_id')) {
                        $table->unsignedBigInteger('company_id')->nullable()->after('id');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'users', 'clients', 'veiculos', 'ordem_servicos', 'budgets', 
            'inventory_items', 'suppliers', 'services', 'service_packages', 
            'financial_transactions', 'payment_methods', 'vehicle_histories', 
            'stock_movements', 'company_settings'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    if (Schema::hasColumn($table->getTable(), 'company_id')) {
                        $table->dropColumn('company_id');
                    }
                });
            }
        }
    }
};