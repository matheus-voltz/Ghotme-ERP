<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'company_id',
        'customer_name',
        'customer_phone',
        'vehicle_plate',
        'service_type',
        'scheduled_at',
        'status',
        'notes',
        'token'
    ];
}
