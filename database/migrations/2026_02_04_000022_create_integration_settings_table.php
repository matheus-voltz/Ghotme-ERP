<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_settings', function (Blueprint $table) {
            $table->id();
            // Asaas
            $table->string('asaas_api_key')->nullable();
            $table->enum('asaas_environment', ['sandbox', 'production'])->default('sandbox');
            
            // Outras futuras integrações
            $table->string('whatsapp_token')->nullable();
            $table->string('whatsapp_phone_number_id')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_settings');
    }
};