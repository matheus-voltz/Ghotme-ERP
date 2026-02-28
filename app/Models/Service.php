<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\BelongsToCompany;

class Service extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'price',
        'estimated_time',
        'follow_up_days',
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

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(ServicePackage::class, 'service_package_items');
    }
}