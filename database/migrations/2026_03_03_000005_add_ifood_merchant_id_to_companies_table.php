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
            $table->string('ifood_merchant_id')->nullable()->after('niche')->index();
            $table->string('ifood_client_id')->nullable()->after('ifood_merchant_id');
            $table->string('ifood_client_secret')->nullable()->after('ifood_client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['ifood_merchant_id', 'ifood_client_id', 'ifood_client_secret']);
        });
    }
};
