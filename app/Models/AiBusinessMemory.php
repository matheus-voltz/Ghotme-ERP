<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class AiBusinessMemory extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'key',
        'content',
        'importance'
    ];

    /**
     * Busca os fatos mais relevantes ou recentes para o contexto.
     */
    public static function getContextForCompany($companyId, $limit = 10)
    {
        return self::where('company_id', $companyId)
            ->orderByDesc('importance')
            ->orderByDesc('updated_at')
            ->take($limit)
            ->get()
            ->map(function($memory) {
                return "- {$memory->content}";
            })
            ->implode("\n");
    }
}
