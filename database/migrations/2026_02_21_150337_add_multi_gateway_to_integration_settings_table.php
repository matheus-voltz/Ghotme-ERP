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
            // Suporte a multi-tenant
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->onDelete('cascade');

            // Gateway Ativo
            $table->string('active_payment_gateway')->default('asaas')->after('company_id');

            // Pagar.me
            $table->string('pagar_me_api_key')->nullable();
            $table->string('pagar_me_encryption_key')->nullable();
            $table->enum('pagar_me_environment', ['sandbox', 'production'])->default('sandbox');

            // PagBank
            $table->string('pagbank_token')->nullable();
            $table->enum('pagbank_environment', ['sandbox', 'production'])->default('sandbox');

            // Stripe
            $table->string('stripe_public_key')->nullable();
            $table->string('stripe_secret_key')->nullable();
            $table->string('stripe_webhook_secret')->nullable();
            $table->enum('stripe_environment', ['sandbox', 'production'])->default('sandbox');
        });
    }

    public function down(): void
    {
        Schema::table('integration_settings', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn([
                'company_id',
                'active_payment_gateway',
                'pagar_me_api_key',
                'pagar_me_encryption_key',
                'pagar_me_environment',
                'pagbank_token',
                'pagbank_environment',
                'stripe_public_key',
                'stripe_secret_key',
                'stripe_webhook_secret',
                'stripe_environment'
            ]);
        });
    }
};
