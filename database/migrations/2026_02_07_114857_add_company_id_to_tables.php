<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Desativar verificação de chaves estrangeiras temporariamente
        Schema::disableForeignKeyConstraints();

        $tables = [
            'users', 'clients', 'vehicles', 'ordem_servicos', 'budgets', 
            'inventory_items', 'suppliers', 'services', 'service_packages', 
            'financial_transactions', 'payment_methods', 'vehicle_histories', 
            'stock_movements', 'company_settings'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    // Se a coluna não existe, cria como um simples inteiro (sem FK por enquanto)
                    if (!Schema::hasColumn($tableName, 'company_id')) {
                        $table->unsignedBigInteger('company_id')->nullable()->after('id');
                    }
                });
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        // ... (código de remoção se necessário)
        Schema::enableForeignKeyConstraints();
    }
};
