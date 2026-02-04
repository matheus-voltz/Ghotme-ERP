<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['os_prefix', 'os_next_number', 'budget_validity_days', 'os_terms'];
}