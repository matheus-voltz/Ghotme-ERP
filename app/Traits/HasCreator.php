<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait HasCreator
{
    /**
     * Boot the HasCreator trait.
     */
    protected static function bootHasCreator(): void
    {
        // Regra: Ao criar um registro, preencher automaticamente o created_by
        static::creating(function ($model) {
            if (Auth::check() && ! $model->created_by) {
                $model->created_by = Auth::id();
            }
        });
    }

    /**
     * Get the user who created the model.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
