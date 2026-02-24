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

        $nicheCategories = niche('checklist_categories') ?? [];
        $order = 0;

        if (!empty($nicheCategories)) {
            foreach ($nicheCategories as $category => $items) {
                foreach ($items as $itemName) {
                    ChecklistItem::create([
                        'company_id' => $company->id,
                        'name' => $itemName,
                        'category' => $category,
                        'order' => $order++,
                        'is_active' => true,
                    ]);
                }
            }
        } else {
            // Fallback for niches without predefined categories
            $items = [
                ['category' => 'Geral', 'name' => 'Estado Geral'],
                ['category' => 'Geral', 'name' => 'Limpeza'],
            ];

            foreach ($items as $index => $item) {
                ChecklistItem::create([
                    'company_id' => $company->id,
                    'name' => $item['name'],
                    'category' => $item['category'],
                    'order' => $order++,
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('Itens de checklist padrão criados para a empresa: ' . $company->name);
    }
}
