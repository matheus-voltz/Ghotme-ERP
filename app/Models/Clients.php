<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clients extends Model
{
    protected $fillable = [
        'type',
        'name',
        'cpf',
        'rg',
        'birth_date',
        'company_name',
        'cnpj',
        'state_registration',
        'municipal_registration',
        'cep',
        'address',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'country',
        'email',
        'phone',
        'whatsapp',
        'status'
    ];
    public function fieldValues()
    {
        return $this->hasMany(ClientFieldValue::class);
    }
}

