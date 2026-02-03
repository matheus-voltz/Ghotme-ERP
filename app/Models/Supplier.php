<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'trade_name',
        'contact_name',
        'email',
        'phone',
        'mobile',
        'document',
        'address',
        'city',
        'state',
        'zip_code',
        'is_active',
    ];

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }
}