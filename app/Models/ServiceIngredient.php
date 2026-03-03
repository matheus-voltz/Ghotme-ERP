<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToCompany;

class ServiceIngredient extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'service_id',
        'inventory_item_id',
        'quantity',
        'unit_of_measure',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
