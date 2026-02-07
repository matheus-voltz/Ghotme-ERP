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
        Schema::table('clients', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id')->unique();
        });

        Schema::table('ordem_servicos', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('ordem_servicos', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
