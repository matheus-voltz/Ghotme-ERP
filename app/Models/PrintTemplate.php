<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class PrintTemplate extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'slug', 'content', 'css', 'is_active'];
}