<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('integration_settings', function (Blueprint $table) {
            // Mercado Livre (Meli API)
            $table->string('meli_client_id')->nullable()->after('fiscal_environment');
            $table->string('meli_client_secret')->nullable()->after('meli_client_id');
            $table->text('meli_access_token')->nullable()->after('meli_client_secret');
            $table->text('meli_refresh_token')->nullable()->after('meli_access_token');
            $table->string('meli_user_id')->nullable()->after('meli_refresh_token');
            $table->timestamp('meli_token_expires_at')->nullable()->after('meli_user_id');
            $table->boolean('meli_active')->default(false)->after('meli_token_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('integration_settings', function (Blueprint $table) {
            $table->dropColumn([
                'meli_client_id',
                'meli_client_secret',
                'meli_access_token',
                'meli_refresh_token',
                'meli_user_id',
                'meli_token_expires_at',
                'meli_active'
            ]);
        });
    }
};
