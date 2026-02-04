<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    protected $fillable = [
        'client_id',
        'veiculo_id',
        'user_id',
        'status',
        'valid_until',
        'description',
        'notes'
    ];

    protected $casts = [
        'valid_until' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Vehicles::class, 'veiculo_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(BudgetPart::class);
    }

    public function getTotalAttribute()
    {
        $servicesTotal = $this->items->sum(fn($i) => $i->price * $i->quantity);
        $partsTotal = $this->parts->sum(fn($p) => $p->price * $p->quantity);
        return $servicesTotal + $partsTotal;
    }
}