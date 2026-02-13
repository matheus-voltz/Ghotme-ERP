<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToCompany;

class StockMovement extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'inventory_item_id',
        'type',
        'quantity',
        'unit_price',
        'reason',
        'user_id'
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}