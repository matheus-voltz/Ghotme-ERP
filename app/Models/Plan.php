<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $connection = 'landlord';

    protected $fillable = [
        'nome',
        'max_funcionarios',
        'max_users',
        'max_products',
        'valor_mensal',
        'status',
    ];

    protected $casts = [
        'max_funcionarios' => 'integer',
        'max_users' => 'integer',
        'max_products' => 'integer',
        'valor_mensal' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'plano_id');
    }
}
