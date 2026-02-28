<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiConsultantMessage extends Model
{
    use HasFactory;

    protected $fillable = ['chat_id', 'role', 'content', 'metadata'];

    protected $casts = [
        'metadata' => 'json'
    ];

    public function chat()
    {
        return $this->belongsTo(AiConsultantChat::class, 'chat_id');
    }
}
