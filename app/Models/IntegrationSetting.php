<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationSetting extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id',
        'active_payment_gateway',
        'mercado_pago_public_key',
        'mercado_pago_access_token',
        'asaas_api_key',
        'asaas_environment',
        'pagar_me_api_key',
        'pagar_me_encryption_key',
        'pagar_me_environment',
        'pagbank_token',
        'pagbank_environment',
        'stripe_public_key',
        'stripe_secret_key',
        'stripe_webhook_secret',
        'stripe_environment',
        'bitcoin_api_key',
        'bitcoin_webhook_secret',
        'bitcoin_environment',
        'whatsapp_token',
        'whatsapp_phone_number_id',
        'whatsapp_api_url',
        'whatsapp_instance_id',
        'whatsapp_api_key',
        'fiscal_api_token',
        'fiscal_environment'
    ];
}
