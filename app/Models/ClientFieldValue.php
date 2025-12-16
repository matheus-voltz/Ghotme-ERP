<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientFieldValue extends Model
{
    protected $fillable = ['client_id', 'client_field_id', 'value'];
}
