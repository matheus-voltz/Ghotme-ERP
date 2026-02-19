<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ChecklistItem;
use Illuminate\Support\Facades\DB;

class NicheInitializerService
{
    /**
     * Inicializa os dados padrão para o nicho da empresa.
     */
    public function initialize($companyId, $niche)
    {
        $this->seedServices($companyId, $niche);
        $this->seedChecklist($companyId, $niche);
    }

    private function seedServices($companyId, $niche)
    {
        $defaults = [
            'automotive' => [
                ['name' => 'Troca de Óleo', 'price' => 50.00],
                ['name' => 'Alinhamento e Balanceamento', 'price' => 120.00],
                ['name' => 'Revisão Geral', 'price' => 250.00],
            ],
            'pet' => [
                ['name' => 'Banho e Tosa (Médio)', 'price' => 85.00],
                ['name' => 'Corte de Unhas', 'price' => 15.00],
                ['name' => 'Aplicação de Antipulgas', 'price' => 45.00],
            ],
            'electronics' => [
                ['name' => 'Troca de Tela (iPhone)', 'price' => 450.00],
                ['name' => 'Limpeza Interna e Pasta Térmica', 'price' => 120.00],
                ['name' => 'Recuperação de Dados', 'price' => 200.00],
            ],
            'beauty_clinic' => [
                ['name' => 'Limpeza de Pele Profunda', 'price' => 150.00],
                ['name' => 'Aplicação de Botox (Zona)', 'price' => 600.00],
                ['name' => 'Drenagem Linfática', 'price' => 120.00],
            ]
        ];

        $services = $defaults[$niche] ?? [];

        foreach ($services as $svc) {
            Service::updateOrCreate(
                ['company_id' => $companyId, 'name' => $svc['name']],
                ['price' => $svc['price'], 'is_active' => true]
            );
        }
    }

    private function seedChecklist($companyId, $niche)
    {
        $defaults = [
            'automotive' => ['Nível do Óleo', 'Luzes do Painel', 'Estado dos Pneus', 'Estepe e Macaco'],
            'pet' => ['Estado da Pelagem', 'Presença de Parasitas', 'Corte de Unhas', 'Limpeza de Ouvidos'],
            'electronics' => ['Riscos na Tela', 'Saúde da Bateria', 'Câmeras Testadas', 'Wi-Fi e Bluetooth'],
            'beauty_clinic' => ['Alergias', 'Uso de Ácidos', 'Gravidez', 'Cirurgias Prévias'],
        ];

        $items = $defaults[$niche] ?? [];

        foreach ($items as $name) {
            ChecklistItem::updateOrCreate(
                ['company_id' => $companyId, 'name' => $name],
                ['category' => 'Entrada', 'is_active' => true]
            );
        }
    }
}
