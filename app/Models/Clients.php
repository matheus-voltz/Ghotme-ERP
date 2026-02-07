<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class Clients extends Model
{
    use BelongsToCompany;

    protected $table = 'clients';

    protected $fillable = [
        'company_id',
        'type',
        'name',
        'cpf',
        'rg',
        'birth_date',
        'company_name',
        'trade_name',
        'cnpj',
        'state_registration',
        'municipal_registration',
        'email',
        'phone',
        'whatsapp',
        'cep',
        'rua',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
    ];

    public function fieldValues()
    {
        return $this->hasMany(ClientFieldValue::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicles::class, 'cliente_id');
    }
}
