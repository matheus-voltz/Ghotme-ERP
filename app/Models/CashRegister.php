<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToCompany;

class CashRegister extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'user_id',
        'opened_at',
        'closed_at',
        'opening_balance',
        'expected_balance',
        'actual_balance',
        'difference',
        'status',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'expected_balance' => 'decimal:2',
        'actual_balance' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CashRegisterMovement::class);
    }

    public function getCalculatedBalanceAttribute(): float
    {
        $sales = $this->movements()->where('type', 'sale')->sum('amount');
        $suprimentos = $this->movements()->where('type', 'suprimento')->sum('amount');
        $sangrias = $this->movements()->where('type', 'sangria')->sum('amount');

        return $this->opening_balance + $sales + $suprimentos - $sangrias;
    }
}
