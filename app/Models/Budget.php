<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToCompany;

use Illuminate\Support\Str;

class Budget extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'uuid',
        'client_id',
        'veiculo_id',
        'user_id',
        'status',
        'early_payment',
        'valid_until',
        'description',
        'notes',
        'approved_at',
        'rejected_at',
        'approval_ip',
        'rejection_reason'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    protected $casts = [
        'valid_until' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
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
