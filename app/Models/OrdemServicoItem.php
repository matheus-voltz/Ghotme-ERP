<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdemServicoItem extends Model
{
    protected $fillable = [
        'ordem_servico_id',
        'service_id',
        'price',
        'quantity',
        'status',
        'duration_seconds',
        'started_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class);
    }

    // Helper para obter o tempo total decorrido (incluindo sessão atual se estiver rodando)
    public function getElapsedTimeAttribute()
    {
        $total = $this->duration_seconds;

        if ($this->status === 'in_progress' && $this->started_at) {
            $total += $this->started_at->diffInSeconds(now());
        }

        return (int) $total;
    }

    public function startTimer()
    {
        if ($this->status !== 'in_progress') {
            $this->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }
    }

    public function stopTimer()
    {
        if ($this->status === 'in_progress') {
            $this->update([
                'status' => 'paused',
                'duration_seconds' => $this->elapsed_time, // Usa o accessor para calcular o novo total
                'started_at' => null,
            ]);
        }
    }

    public function complete()
    {
        $this->stopTimer(); // Garante que o tempo final seja computado
        $this->update(['status' => 'completed']);
    }
}
