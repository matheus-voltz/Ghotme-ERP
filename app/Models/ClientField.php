<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientField extends Model
{
    protected $casts = [
        'options' => 'array',
    ];
}
    