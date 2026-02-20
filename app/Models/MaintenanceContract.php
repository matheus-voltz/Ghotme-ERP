<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class MaintenanceContract extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'client_id',
        'title',
        'amount',
        'billing_day',
        'frequency',
        'start_date',
        'next_billing_date',
        'auto_generate_os',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'next_billing_date' => 'date',
        'amount' => 'decimal:2',
        'auto_generate_os' => 'boolean'
    ];

    public function client()
    {
        return $this->belongsTo(Clients::class);
    }
}
