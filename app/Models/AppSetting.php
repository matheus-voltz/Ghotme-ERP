<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class AppSetting extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'os_prefix', 'os_next_number', 'budget_validity_days', 'os_terms'];
}