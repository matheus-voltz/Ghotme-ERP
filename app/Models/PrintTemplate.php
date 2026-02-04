<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintTemplate extends Model
{
    protected $fillable = ['name', 'slug', 'content', 'css', 'is_active'];
}