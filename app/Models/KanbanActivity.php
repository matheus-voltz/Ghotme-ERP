<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KanbanActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'kanban_item_id',
        'user_id',
        'type',
        'description',
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function item()
    {
        return $this->belongsTo(KanbanItem::class, 'kanban_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
