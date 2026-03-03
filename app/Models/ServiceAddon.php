<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToCompany;

class ServiceAddon extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'service_addon_group_id',
        'name',
        'price',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ServiceAddonGroup::class, 'service_addon_group_id');
    }
}
