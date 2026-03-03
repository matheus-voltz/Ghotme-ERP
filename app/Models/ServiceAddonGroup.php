<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToCompany;

class ServiceAddonGroup extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'service_id',
        'name',
        'selection_type',
        'min_options',
        'max_options',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function addons(): HasMany
    {
        return $this->hasMany(ServiceAddon::class);
    }
}
