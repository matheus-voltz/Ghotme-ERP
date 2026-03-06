<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\BelongsToCompany;

class CashRegisterMovement extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'cash_register_id',
        'type',
        'amount',
        'payment_method',
        'description',
        'related_type',
        'related_id',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
