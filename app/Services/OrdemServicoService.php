<?php

namespace App\Services;

use App\Models\OrdemServico;
use App\Models\OrdemServicoItem;
use App\Models\OrdemServicoPart;
use App\Models\VehicleHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrdemServicoService
{
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $os = OrdemServico::create([
                'client_id' => $data['client_id'],
                'veiculo_id' => $data['veiculo_id'],
                'status' => $data['status'],
                'description' => $data['description'] ?? null,
                'km_entry' => $data['km_entry'] ?? null,
                'user_id' => Auth::id()
            ]);

            $this->syncItems($os, $data['services'] ?? []);
            $this->syncParts($os, $data['parts'] ?? []);

            // Salva campos personalizados
            if (isset($data['custom_fields'])) {
                $os->syncCustomFields($data['custom_fields']);
            }

            $this->logHistory($os, 'entrada_oficina', 'Entrada na Oficina', 'O veículo deu entrada para avaliação técnica.');
            $this->logHistory($os, 'aguardando_orcamento', 'Aguardando Orçamento', 'A equipe técnica está avaliando o veículo para elaboração do orçamento.');

            return $os;
        });
    }

    public function update(OrdemServico $os, array $data)
    {
        return DB::transaction(function () use ($os, $data) {
            $os->update([
                'client_id' => $data['client_id'],
                'veiculo_id' => $data['veiculo_id'],
                'status' => $data['status'],
                'description' => $data['description'] ?? null,
                'km_entry' => $data['km_entry'] ?? null,
            ]);

            $this->syncItems($os, $data['services'] ?? [], true);
            $this->syncParts($os, $data['parts'] ?? [], true);

            // Salva campos personalizados
            if (isset($data['custom_fields'])) {
                $os->syncCustomFields($data['custom_fields']);
            }

            return $os;
        });
    }

    protected function syncItems(OrdemServico $os, array $services, bool $isUpdate = false)
    {
        $selectedServiceIds = [];
        foreach ($services as $id => $data) {
            if (isset($data['selected'])) {
                $selectedServiceIds[] = $id;
            }
        }

        if ($isUpdate) {
            $os->items()->whereNotIn('service_id', $selectedServiceIds)->delete();
        }

        foreach ($services as $serviceId => $data) {
            if (!isset($data['selected'])) continue;

            $os->items()->updateOrCreate(
                ['service_id' => $serviceId],
                [
                    'price' => $data['price'],
                    'quantity' => $data['quantity'] ?? 1,
                    'status' => $isUpdate ? null : 'pending'
                ]
            );
        }
    }

    protected function syncParts(OrdemServico $os, array $parts, bool $isUpdate = false)
    {
        $selectedPartIds = [];
        foreach ($parts as $id => $data) {
            if (isset($data['selected'])) {
                $selectedPartIds[] = $id;
            }
        }

        if ($isUpdate) {
            $os->parts()->whereNotIn('inventory_item_id', $selectedPartIds)->delete();
        }

        foreach ($parts as $partId => $data) {
            if (!isset($data['selected'])) continue;

            $os->parts()->updateOrCreate(
                ['inventory_item_id' => $partId],
                [
                    'price' => $data['price'],
                    'quantity' => $data['quantity'] ?? 1
                ]
            );
        }
    }

    protected function logHistory(OrdemServico $os, string $type, string $title, string $description)
    {
        VehicleHistory::create([
            'company_id' => Auth::user()->company_id,
            'veiculo_id' => $os->veiculo_id,
            'ordem_servico_id' => $os->id,
            'date' => now(),
            'km' => $os->km_entry ?? 0,
            'event_type' => $type,
            'title' => $title,
            'description' => $description,
            'performer' => Auth::user()->name,
            'created_by' => Auth::id()
        ]);

        // Gerar Comissão se a OS for finalizada
        if ($type === 'os_finalizada') {
            $this->generateCommission($os);
        }
    }

    protected function generateCommission(OrdemServico $os)
    {
        $user = $os->user; // Técnico responsável
        
        if ($user && $user->commission_percentage > 0) {
            $baseAmount = $os->total; // Ou apenas mão de obra se preferir
            $commissionAmount = ($baseAmount * $user->commission_percentage) / 100;

            \App\Models\Commission::updateOrCreate(
                ['ordem_servico_id' => $os->id],
                [
                    'company_id' => $os->company_id,
                    'user_id' => $user->id,
                    'description' => "Comissão OS #{$os->id}",
                    'base_amount' => $baseAmount,
                    'percentage' => $user->commission_percentage,
                    'commission_amount' => $commissionAmount,
                    'status' => 'pending'
                ]
            );
        }
    }
}
