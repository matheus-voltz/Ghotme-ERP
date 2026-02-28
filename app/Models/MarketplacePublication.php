<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MarketplacePublication extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'publishable_id',
        'publishable_type',
        'platform',
        'external_id',
        'external_url',
        'status',
        'price',
        'sync_stock',
        'last_synced_at',
        'error_message',
    ];

    protected $casts = [
        'sync_stock' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function publishable(): MorphTo
    {
        return $this->morphTo();
    }
}
