<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class ChecklistItem extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'category', 'order', 'is_active'];
}