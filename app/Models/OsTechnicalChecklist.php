<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OsTechnicalChecklist extends Model
{
    use HasFactory;

    protected $table = 'os_technical_checklists';

    protected $fillable = [
        'ordem_servico_id',
        'category',
        'item',
        'status',
        'observation'
    ];

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class);
    }
}