<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    protected $fillable = ['name', 'type', 'is_active'];

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }
}