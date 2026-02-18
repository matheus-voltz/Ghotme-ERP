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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('ie')->nullable()->after('document_number'); // Inscrição Estadual
            $table->string('im')->nullable()->after('ie'); // Inscrição Municipal
            $table->string('tax_regime')->nullable()->after('im'); // Simples Nacional, Lucro Presumido, etc.
            $table->decimal('iss_rate', 5, 2)->default(5.00)->after('tax_regime'); // Alíquota de ISS (%)
            $table->boolean('is_tax_iss_withheld')->default(false)->after('iss_rate'); // ISS Retido?
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['ie', 'im', 'tax_regime', 'iss_rate', 'is_tax_iss_withheld']);
        });
    }
};
