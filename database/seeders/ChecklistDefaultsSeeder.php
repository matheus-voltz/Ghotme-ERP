<?php

namespace Database\Seeders;

use App\Models\ChecklistItem;
use App\Models\Company;
use Illuminate\Database\Seeder;

class ChecklistDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        
        if (!$company) {
            $this->command->error('Nenhuma empresa encontrada. Rode o ProjectCompleteSeeder primeiro.');
            return;
        }

        $items = [
            ['category' => 'Exterior', 'name' => 'Parabrisa e Vidros'],
            ['category' => 'Exterior', 'name' => 'Retrovisores'],
            ['category' => 'Exterior', 'name' => 'Rodas e Calotas'],
            ['category' => 'Exterior', 'name' => 'Antena / Acessórios'],
            ['category' => 'Exterior', 'name' => 'Limpador de Para-brisa'],
            
            ['category' => 'Iluminação', 'name' => 'Faróis (Baixo/Alto)'],
            ['category' => 'Iluminação', 'name' => 'Lanternas'],
            ['category' => 'Iluminação', 'name' => 'Setas / Pisca-alerta'],
            ['category' => 'Iluminação', 'name' => 'Luz de Freio'],
            ['category' => 'Iluminação', 'name' => 'Luz de Ré'],
            
            ['category' => 'Segurança', 'name' => 'Pneus (Estado/Desgaste)'],
            ['category' => 'Segurança', 'name' => 'Estepe (Presença/Calibragem)'],
            ['category' => 'Segurança', 'name' => 'Macaco e Chave de Roda'],
            ['category' => 'Segurança', 'name' => 'Triângulo'],
            ['category' => 'Segurança', 'name' => 'Cintos de Segurança'],
            
            ['category' => 'Níveis e Fluídos', 'name' => 'Óleo do Motor'],
            ['category' => 'Níveis e Fluídos', 'name' => 'Líquido de Arrefecimento'],
            ['category' => 'Níveis e Fluídos', 'name' => 'Fluído de Freio'],
            ['category' => 'Níveis e Fluídos', 'name' => 'Água do Limpador'],
            ['category' => 'Níveis e Fluídos', 'name' => 'Bateria (Terminais/Carga)'],
        ];

        foreach ($items as $index => $item) {
            ChecklistItem::create([
                'company_id' => $company->id,
                'name' => $item['name'],
                'category' => $item['category'],
                'order' => $index,
                'is_active' => true,
            ]);
        }

        $this->command->info('Itens de checklist padrão criados para a empresa: ' . $company->name);
    }
}