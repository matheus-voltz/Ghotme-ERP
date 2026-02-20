<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class Commission extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'user_id',
        'ordem_servico_id',
        'description',
        'base_amount',
        'percentage',
        'commission_amount',
        'status',
        'paid_at'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'base_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class);
    }
}
