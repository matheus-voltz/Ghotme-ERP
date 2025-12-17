<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'type',
        'name',
        'document',
        'email',
        'phone',
        'whatsapp',
        'status'
    ];
    public function fieldValues()
    {
        return $this->hasMany(ClientFieldValue::class);
    }
}

