<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('integration_settings', function (Blueprint $table) {
            // Mercado Pago
            $table->string('mercado_pago_public_key')->nullable()->after('active_payment_gateway');
            $table->string('mercado_pago_access_token')->nullable()->after('mercado_pago_public_key');
            
            // WhatsApp API (Generic/Evolution/Z-API)
            $table->string('whatsapp_api_url')->nullable()->after('whatsapp_phone_number_id');
            $table->string('whatsapp_instance_id')->nullable()->after('whatsapp_api_url');
            $table->string('whatsapp_api_key')->nullable()->after('whatsapp_instance_id');
        });
    }

    public function down(): void
    {
        Schema::table('integration_settings', function (Blueprint $table) {
            $table->dropColumn([
                'mercado_pago_public_key', 
                'mercado_pago_access_token',
                'whatsapp_api_url',
                'whatsapp_instance_id',
                'whatsapp_api_key'
            ]);
        });
    }
};
