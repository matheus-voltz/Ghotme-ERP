<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\BelongsToCompany;

class ServicePackage extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'total_price',
        'is_active',
    ];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_package_items');
    }

    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(InventoryItem::class, 'service_package_parts')
                    ->withPivot('quantity');
    }
}