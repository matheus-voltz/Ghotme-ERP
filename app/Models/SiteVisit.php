<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    public $timestamps = false; // Como usamos created_at padrão, não precisamos do updated_at

    protected $fillable = [
        'ip_address',
        'user_agent',
        'path',
        'referer',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];
}
