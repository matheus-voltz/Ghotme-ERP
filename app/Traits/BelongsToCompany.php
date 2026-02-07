<?php

namespace App\Traits;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToCompany
{
    /**
     * Boot the BelongsToCompany trait.
     */
    protected static function bootBelongsToCompany(): void
    {
        // Se estiver rodando via console (migrations, seeds), não aplica o filtro
        if (app()->runningInConsole()) {
            return;
        }

        // Regra 1: Ao criar um registro, preencher automaticamente o company_id
        static::creating(function ($model) {
            if (Auth::check() && ! $model->company_id) {
                $model->company_id = Auth::user()->company_id;
            }
        });

        // Regra 2: Ao buscar registros, filtrar apenas os da empresa do usuário logado
        static::addGlobalScope('company', function (Builder $builder) {
            // Verifica se o usuário está logado ANTES de tentar acessar o company_id
            if (Auth::check() && Auth::user()->company_id) {
                $builder->where($builder->getQuery()->from . '.company_id', Auth::user()->company_id);
            }
        });
    }

    /**
     * Get the company that owns the model.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}