<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'document_number',
        'ie',
        'im',
        'tax_regime',
        'iss_rate',
        'is_tax_iss_withheld',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'logo_path',
        'slug',
        'niche',
        'is_active',
        'accountant_token',
        'configuracoes_net', // Adicionado
    ];

    /**
     * Casts de atributos
     */
    protected $casts = [
        'configuracoes_net' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Helper para verificar se uma configuração está ativa
     */
    public function hasConfig($key, $default = false)
    {
        if (!$this->configuracoes_net) return $default;
        return data_get($this->configuracoes_net, $key, $default);
    }

    /**
     * Relacionamento com usuários
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
