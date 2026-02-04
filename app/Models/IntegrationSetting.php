<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationSetting extends Model
{
    protected $fillable = [
        'asaas_api_key',
        'asaas_environment',
        'whatsapp_token',
        'whatsapp_phone_number_id'
    ];
}