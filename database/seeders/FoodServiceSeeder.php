<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use App\Models\MenuCategory;
use App\Models\InventoryItem;
use App\Models\Service;
use App\Models\ServicePackage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FoodServiceSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Company
        $company = Company::create([
            'name' => 'Food Service Demo',
            'document_number' => '12.345.678/0001-99',
            'email' => 'contato@foodservice.com.br',
            'phone' => '(11) 99999-9999',
            'niche' => 'food_service',
            'is_active' => true,
        ]);

        // 2. Create User (Email already verified as requested)
        $user = User::create([
            'company_id' => $company->id,
            'name' => 'Gestor Food Service',
            'email' => 'vendas@foodservice.com.br',
            'password' => Hash::make('venda 123'),
            'role' => 'admin',
            'is_master' => false,
            'email_verified_at' => now(), // Rule: Always verified
            'trial_ends_at' => now()->addDays(30),
            'niche' => 'food_service',
        ]);

        // 3. Create Categories
        $catIngredientes = MenuCategory::create([
            'company_id' => $company->id,
            'name' => 'Ingredientes',
            'icon' => 'tabler-tools-kitchen-2',
            'order' => 1,
            'is_active' => true,
        ]);

        $catLanches = MenuCategory::create([
            'company_id' => $company->id,
            'name' => 'Lanches',
            'icon' => 'tabler-burger',
            'order' => 2,
            'is_active' => true,
        ]);

        $catBebidas = MenuCategory::create([
            'company_id' => $company->id,
            'name' => 'Bebidas',
            'icon' => 'tabler-glass-full',
            'order' => 3,
            'is_active' => true,
        ]);

        // 4. Create Insumos (Ingredients)
        $insumos = [
            ['name' => 'Pão de Brioche', 'cost' => 1.50],
            ['name' => 'Hambúrguer Bovino 180g', 'cost' => 4.50],
            ['name' => 'Queijo Cheddar', 'cost' => 0.80],
            ['name' => 'Bacon Fatiado', 'cost' => 1.20],
            ['name' => 'Salsicha Premium', 'cost' => 0.60],
            ['name' => 'Pão de Hot Dog', 'cost' => 0.50],
        ];

        foreach ($insumos as $i) {
            InventoryItem::create([
                'company_id' => $company->id,
                'menu_category_id' => $catIngredientes->id,
                'name' => $i['name'],
                'cost_price' => $i['cost'],
                'selling_price' => 0,
                'quantity' => 100,
                'unit' => 'un',
                'is_ingredient' => true,
                'is_active' => true,
            ]);
        }

        // 5. Create Services (Pratos/Pronto)
        $pratos = [
            ['name' => 'X-Burger Especial', 'price' => 28.90, 'time' => 15],
            ['name' => 'Hot Dog Completo', 'price' => 18.50, 'time' => 10],
            ['name' => 'X-Bacon Supremo', 'price' => 32.00, 'time' => 15],
        ];

        $serviceModels = [];
        foreach ($pratos as $p) {
            $serviceModels[] = Service::create([
                'company_id' => $company->id,
                'name' => $p['name'],
                'price' => $p['price'],
                'estimated_time' => $p['time'],
                'is_active' => true,
            ]);
        }

        // 6. Create Ready Products (Drinks/Sides) in Inventory
        $bebidas = [
            ['name' => 'Coca-Cola 350ml', 'price' => 6.00, 'cost' => 2.50],
            ['name' => 'Água Mineral 500ml', 'price' => 4.00, 'cost' => 1.00],
            ['name' => 'Cerveja Lata', 'price' => 8.00, 'cost' => 3.50],
        ];

        $drinkModels = [];
        foreach ($bebidas as $b) {
            $drinkModels[] = InventoryItem::create([
                'company_id' => $company->id,
                'menu_category_id' => $catBebidas->id,
                'name' => $b['name'],
                'cost_price' => $b['cost'],
                'selling_price' => $b['price'],
                'quantity' => 50,
                'unit' => 'un',
                'is_ingredient' => false,
                'is_active' => true,
            ]);
        }

        // 7. Create Combos (Service Packages)
        $combo = ServicePackage::create([
            'company_id' => $company->id,
            'name' => 'Combo X-Burger + Refri',
            'description' => 'Acompanha X-Burger Especial e Coca-Cola 350ml',
            'total_price' => 32.00,
            'is_active' => true,
        ]);

        // Attach items to combo
        // Note: Relation names and pivot table names from ServicePackage model:
        // services() -> service_package_items
        // parts() -> service_package_parts

        $combo->services()->attach($serviceModels[0]->id); // X-Burger
        $combo->parts()->attach($drinkModels[0]->id, ['quantity' => 1]); // Coca

        $comboDog = ServicePackage::create([
            'company_id' => $company->id,
            'name' => 'Combo Hot Dog + Água',
            'description' => 'Hot Dog Completo e Água Mineral',
            'total_price' => 20.00,
            'is_active' => true,
        ]);

        $comboDog->services()->attach($serviceModels[1]->id); // Hot Dog
        $comboDog->parts()->attach($drinkModels[1]->id, ['quantity' => 1]); // Água

        return "Conta criada com sucesso!";
    }
}
