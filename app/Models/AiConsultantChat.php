<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiConsultantChat extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'company_id', 'title'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function messages()
    {
        return $this->hasMany(AiConsultantMessage::class, 'chat_id')->orderBy('created_at', 'asc');
    }
}
