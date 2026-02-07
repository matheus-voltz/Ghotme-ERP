<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class BudgetApproval extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'budget_id',
        'status',
        'ip_address',
        'user_agent',
        'reason'
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
}