<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'description',
        'cost_price',
        'selling_price',
        'quantity',
        'min_quantity',
        'supplier_id',
        'unit',
        'location',
        'is_active',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}