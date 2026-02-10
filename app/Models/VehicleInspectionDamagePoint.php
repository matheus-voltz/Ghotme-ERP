<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleInspectionDamagePoint extends Model
{
    protected $fillable = [
        'vehicle_inspection_id',
        'x_coordinate',
        'y_coordinate',
        'type',
        'notes'
    ];

    public function inspection()
    {
        return $this->belongsTo(VehicleInspection::class, 'vehicle_inspection_id');
    }
}
