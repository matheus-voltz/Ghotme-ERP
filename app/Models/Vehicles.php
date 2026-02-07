<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class Vehicles extends Model
{
    use BelongsToCompany;

    protected $table = 'veiculos';

    protected $fillable = [
        'company_id',
        'cliente_id',
        'placa',
        'ano',
        'renavam',
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

    public function client()
    {
        return $this->belongsTo(Clients::class, 'cliente_id');
    }

    public function history()
    {
        return $this->hasMany(VehicleHistory::class, 'veiculo_id');
    }
}
