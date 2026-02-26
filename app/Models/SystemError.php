<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemError extends Model
{
    protected $fillable = [
        'user_id',
        'url',
        'method',
        'error_type',
        'message',
        'stack_trace',
        'request_data'
    ];

    protected $casts = [
        'request_data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}