<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetItem extends Model
{
    protected $fillable = ['budget_id', 'service_id', 'price', 'quantity'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}