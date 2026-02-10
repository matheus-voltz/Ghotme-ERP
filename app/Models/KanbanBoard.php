<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class KanbanBoard extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = ['company_id', 'title', 'slug', 'order'];

    public function items()
    {
        return $this->hasMany(KanbanItem::class)->orderBy('updated_at', 'desc');
    }
}