<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CfgConfiguracaoNet extends Model
{
    use HasFactory;

    protected $table = 'cfgconfiguracoes_net';

    protected $fillable = [
        'identificacao_validacao',
        'descricao_validacao',
        'valor_configuracao',
        'valor_complemento',
    ];
}
