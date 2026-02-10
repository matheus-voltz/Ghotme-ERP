<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'nome',
        'email',
        'cargo',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
