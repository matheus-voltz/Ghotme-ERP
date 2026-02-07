<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToCompany;

class Supplier extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
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