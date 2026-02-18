<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

use Illuminate\Support\Str;

class Clients extends Model implements Auditable
{
    use BelongsToCompany, AuditableTrait;

    protected $table = 'clients';

    protected $fillable = [
        'company_id',
        'uuid',
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

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function fieldValues()
    {
        return $this->hasMany(ClientFieldValue::class, 'client_id');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicles::class, 'cliente_id');
    }
}
