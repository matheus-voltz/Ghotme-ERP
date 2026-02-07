<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToCompany;

class VehicleInspection extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'veiculo_id',
        'ordem_servico_id',
        'user_id',
        'fuel_level',
        'km_current',
        'notes',
    ];

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Vehicles::class, 'veiculo_id');
    }

    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(VehicleInspectionItem::class);
    }
}