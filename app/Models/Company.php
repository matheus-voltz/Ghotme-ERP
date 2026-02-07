<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'document_number',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'logo_path',
        'slug',
        'is_active',
    ];

    /**
     * Relacionamento com usuários
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}