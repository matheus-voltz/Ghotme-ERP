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
        Schema::table('integration_settings', function (Blueprint $table) {
            $table->string('bitcoin_api_key')->nullable();
            $table->string('bitcoin_webhook_secret')->nullable();
            $table->enum('bitcoin_environment', ['sandbox', 'production'])->default('sandbox');
        });
    }

    public function down(): void
    {
        Schema::table('integration_settings', function (Blueprint $table) {
            $table->dropColumn(['bitcoin_api_key', 'bitcoin_webhook_secret', 'bitcoin_environment']);
        });
    }
};
