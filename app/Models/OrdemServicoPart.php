<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdemServicoPart extends Model
{
    protected $fillable = ['ordem_servico_id', 'inventory_item_id', 'price', 'quantity'];

    public function part()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
