<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToCompany;

class RecipeItem extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'inventory_item_id',
        'ingredient_id',
        'quantity',
        'unit',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'ingredient_id');
    }
}
