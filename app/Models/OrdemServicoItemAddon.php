<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdemServicoItemAddon extends Model
{
    protected $fillable = [
        'ordem_servico_item_id',
        'service_addon_id',
        'name',
        'price',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrdemServicoItem::class, 'ordem_servico_item_id');
    }

    public function serviceAddon(): BelongsTo
    {
        return $this->belongsTo(ServiceAddon::class);
    }
}
