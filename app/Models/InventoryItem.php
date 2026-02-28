<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToCompany;

class InventoryItem extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
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

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable')->orderBy('order');
    }

    public function mainImage()
    {
        return $this->morphOne(Image::class, 'imageable')->where('is_main', true);
    }

    public function marketplacePublications()
    {
        return $this->morphMany(MarketplacePublication::class, 'publishable');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}