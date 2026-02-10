<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KanbanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'kanban_board_id',
        'user_id',
        'title',
        'due_date',
        'badge_text',
        'badge_color',
        'assigned_to',
        'attachments',
        'comments'
    ];

    protected $casts = [
        'assigned_to' => 'array',
        'attachments' => 'array',
        'comments' => 'array',
        'due_date' => 'date',
    ];

    public function board()
    {
        return $this->belongsTo(KanbanBoard::class, 'kanban_board_id');
    }
}