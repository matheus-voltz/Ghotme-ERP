<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrdemServicoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client' => $this->client ? ($this->client->name ?? $this->client->company_name) : '-',
            'vehicle' => $this->veiculo ? "{$this->veiculo->placa} - {$this->veiculo->modelo}" : '-',
            'status' => $this->status,
            'status_label' => $this->getStatusLabel($this->status),
            'total' => (float) $this->total,
            'total_formatted' => 'R$ ' . number_format($this->total, 2, ',', '.'),
            'date' => $this->created_at->format('d/m/Y'),
            'created_at' => $this->created_at->toISOString(),
        ];
    }

    protected function getStatusLabel($status)
    {
        $statusLabels = [
            'pending' => 'Pendente',
            'in_progress' => 'Em Manutenção',
            'testing' => 'Em Teste',
            'cleaning' => 'Em Limpeza',
            'completed' => 'Pronto para Retirada',
            'paid' => 'Finalizado / Pago',
            'awaiting_approval' => 'Aguardando Aprovação'
        ];

        return $statusLabels[$status] ?? $status;
    }
}
