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
        Schema::connection('landlord')->table('tenants', function (Blueprint $table) {
            $table->foreignId('plano_id')->after('nome_empresa')->nullable()->constrained('plans');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('landlord')->table('tenants', function (Blueprint $table) {
            $table->dropForeign(['plano_id']);
            $table->dropColumn('plano_id');
        });
    }
};
