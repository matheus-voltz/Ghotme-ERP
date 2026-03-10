<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class AiInsight extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'agent_name',
        'title',
        'observation',
        'recommendation',
        'status',
        'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime'
    ];
}
