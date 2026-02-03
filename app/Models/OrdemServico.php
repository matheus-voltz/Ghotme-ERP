<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdemServico extends Model
{
    protected $fillable = [
        'client_id',
        'veiculo_id',
        'user_id',
        'status',
        'description',
        'scheduled_at',
        'km_entry',
    ];

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculos::class, 'veiculo_id');
    }

    public function history()
    {
        return $this->hasOne(VehicleHistory::class, 'ordem_servico_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
