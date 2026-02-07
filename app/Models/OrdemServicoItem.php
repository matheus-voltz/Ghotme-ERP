<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdemServicoItem extends Model
{
    protected $fillable = ['ordem_servico_id', 'service_id', 'price', 'quantity'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class);
    }
}
