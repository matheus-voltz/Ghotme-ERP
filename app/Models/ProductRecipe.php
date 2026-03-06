<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRecipe extends Model
{
    protected $fillable = ['product_id', 'ingredient_id', 'quantity'];

    public function product()
    {
        return $this->belongsTo(InventoryItem::class, 'product_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(InventoryItem::class, 'ingredient_id');
    }
}
