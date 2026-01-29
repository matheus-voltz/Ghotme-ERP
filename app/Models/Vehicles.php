<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicles extends Model
{
    protected $table = 'veiculos';
    
    protected $fillable = [
        'client_id',
        'placa',
        'ano',
        'renavan',
        'chassi',
        'marca',
        'modelo',
        'versao',
        'ano_fabricacao',
        'ano_modelo',
        'cor',
        'combustivel',
        'cambio',
        'motor',
        'km_atual',
        'proxima_revisao',
        'ultima_revisao',
        'ativo',
        'status',
    ];
}
