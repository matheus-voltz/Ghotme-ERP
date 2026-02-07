<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleHistory extends Model
{
    protected $fillable = [
        'veiculo_id',
        'ordem_servico_id',
        'date',
        'km',
        'event_type',
        'title',
        'description',
        'performer',
        'cost',
        'created_by'
    ];

    protected $casts = [
        'date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Vehicles::class, 'veiculo_id')->withoutGlobalScope('company');
    }

    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id')->withoutGlobalScope('company');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
