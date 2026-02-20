<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomFieldValue extends Model
{
    protected $fillable = [
        'custom_field_id',
        'model_id',
        'model_type',
        'value'
    ];

    public function field(): BelongsTo
    {
        return $this->belongsTo(CustomField::class, 'custom_field_id');
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
