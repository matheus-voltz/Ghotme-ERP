<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToCompany;

class FinancialTransaction extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'description',
        'amount',
        'type',
        'status',
        'due_date',
        'paid_at',
        'payment_method_id',
        'category',
        'client_id',
        'supplier_id',
        'related_type',
        'related_id',
        'user_id',
        'bank_transaction_id',
        'bank_name',
        'bank_metadata'
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}