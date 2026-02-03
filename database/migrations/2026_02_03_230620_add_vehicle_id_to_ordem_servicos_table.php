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
        Schema::table('ordem_servicos', function (Blueprint $table) {
            $table->foreignId('veiculo_id')->nullable()->after('client_id')->constrained('veiculos')->onDelete('set null');
            $table->integer('km_entry')->nullable()->after('description'); // KM na entrada da OS
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordem_servicos', function (Blueprint $table) {
            $table->dropForeign(['veiculo_id']);
            $table->dropColumn(['veiculo_id', 'km_entry']);
        });
    }
};