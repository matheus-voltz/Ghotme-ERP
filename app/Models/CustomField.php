<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToCompany;

class CustomField extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'entity_type',
        'name',
        'type',
        'options',
        'required',
        'order',
        'is_active'
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }
}
