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
        'is_active',
    ];

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(ServicePackage::class, 'service_package_items');
    }
}