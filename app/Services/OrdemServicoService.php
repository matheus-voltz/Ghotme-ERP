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
                'client_id'      => $data['client_id'] ?? null,
                'customer_name'  => $data['customer_name'] ?? null,
                'veiculo_id'     => $data['veiculo_id'] ?? null,
                'status'         => $data['status'],
                'description'    => $data['description'] ?? null,
                'km_entry'       => $data['km_entry'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'user_id'        => Auth::id(),
                'company_id'     => Auth::user()->company_id,
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
                'client_id'      => $data['client_id'] ?? null,
                'veiculo_id'     => $data['veiculo_id'] ?? null,
                'status'         => $data['status'],
                'description'    => $data['description'] ?? null,
                'km_entry'       => $data['km_entry'] ?? null,
                'payment_method' => $data['payment_method'] ?? $os->payment_method,
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

    // Ações de Finalização
    public function logHistory(OrdemServico $os, string $type, string $title, string $description)
    {
        $currentNiche = get_current_niche();

        // Tradução de termos para Food Service
        if ($currentNiche === 'food_service') {
            if ($type === 'entrada_oficina') {
                $title = 'Pedido Recebido';
                $description = 'O pedido foi registrado e enviado para a fila de preparo.';
            }
            if ($type === 'aguardando_orcamento') {
                $title = 'Na Fila de Espera';
                $description = 'O pedido está aguardando o início do preparo na cozinha.';
            }
        }

        if ($os->veiculo_id) {
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
        }

        // Ações de Finalização ou Início de Preparo (Food Service)
        $isFood = $currentNiche === 'food_service';
        $shouldDeduct = $type === 'os_finalizada' || $os->status === 'paid' || $os->status === 'finalized';

        // No Food Service, baixamos no running também (início de preparo/venda)
        if ($isFood && $os->status === 'running') {
            $shouldDeduct = true;
        }

        if ($shouldDeduct) {
            $this->generateFinancialTransaction($os);
            $this->generateCommission($os);
            $this->deductStock($os);
        }
    }

    protected function generateFinancialTransaction(OrdemServico $os)
    {
        // Evita duplicidade usando os campos polimórficos corretos
        $exists = \App\Models\FinancialTransaction::where('related_type', get_class($os))
            ->where('related_id', $os->id)
            ->exists();

        if ($exists) return;

        \App\Models\FinancialTransaction::create([
            'company_id' => $os->company_id ?? Auth::user()->company_id,
            'user_id' => $os->user_id ?? Auth::id(),
            'client_id' => $os->client_id,
            'related_type' => get_class($os),
            'related_id' => $os->id,
            'type' => 'in',
            'amount' => $os->total,
            'description' => "Venda de Pedido #" . $os->id . " (" . (get_current_niche() === 'food_service' ? 'Restaurante' : 'Serviços') . ")",
            'status' => 'paid',
            'paid_at' => now(),
            'due_date' => now(),
            'payment_method' => $os->payment_method ?? 'cash',
            'category' => get_current_niche() === 'food_service' ? 'Vendas' : 'Serviços'
        ]);
    }

    public function deductStock(OrdemServico $os)
    {
        // Percorre as Peças/Itens lançados na OS
        foreach ($os->parts as $osPart) {
            $item = $osPart->inventoryItem;
            if (!$item) continue;

            // Prevenir baixa duplicada para ESTE item específico desta OS
            // Usamos o ID do os_part na reason para garantir unicidade
            $exists = \App\Models\StockMovement::where('company_id', $os->company_id)
                ->where('reason', 'like', "%na OS #{$os->id}%")
                ->where('reason', 'like', "%(Ref:{$osPart->id})%")
                ->exists();

            if ($exists) continue;

            // Se o item tem uma receita (Ficha Técnica), baixa os ingredientes
            if ($item->ingredients->count() > 0) {
                foreach ($item->ingredients as $recipe) {
                    $ingredient = $recipe->ingredient;
                    if ($ingredient) {
                        $qtyToDeduct = $recipe->quantity * $osPart->quantity;
                        $ingredient->decrement('quantity', $qtyToDeduct);

                        // Log de movimentação (opcional, mas recomendado)
                        \App\Models\StockMovement::create([
                            'inventory_item_id' => $ingredient->id,
                            'type' => 'out',
                            'quantity' => $qtyToDeduct,
                            'reason' => "Consumo na OS #{$os->id} (Ingrediente de {$item->name}) (Ref:{$osPart->id})"
                        ]);
                    }
                }
            } else {
                // Se não tem receita, baixa o item principal normalmente
                $item->decrement('quantity', $osPart->quantity);

                \App\Models\StockMovement::create([
                    'inventory_item_id' => $item->id,
                    'type' => 'out',
                    'quantity' => $osPart->quantity,
                    'reason' => "Venda na OS #{$os->id} (Ref:{$osPart->id})"
                ]);
            }
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
