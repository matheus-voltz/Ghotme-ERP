<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use App\Models\MenuCategory;
use App\Models\InventoryItem;
use App\Models\ProductRecipe;
use App\Models\Clients;
use App\Models\OrdemServico;
use App\Models\OrdemServicoPart;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class FoodServiceDemoSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            // 1. Criar Empresa de Teste
            $company = Company::create([
                'name' => 'Hamburgueria Ghotme Demo',
                'document_number' => '99.888.777/0001-00',
                'email' => 'teste@ghotme.com.br',
                'phone' => '(11) 98888-7777',
                'niche' => 'food_service',
                'is_active' => true,
            ]);

            // 2. Criar Usuário de Teste
            $user = User::create([
                'company_id' => $company->id,
                'name' => 'Usuário Teste Food',
                'email' => 'teste@ghotme.com.br',
                'password' => Hash::make('ghotme123'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'status' => 'active',
                'niche' => 'food_service'
            ]);

            // 3. Formas de Pagamento
            PaymentMethod::create(['company_id' => $company->id, 'name' => 'Dinheiro', 'type' => 'cash', 'is_active' => true]);
            PaymentMethod::create(['company_id' => $company->id, 'name' => 'PIX', 'type' => 'pix', 'is_active' => true]);
            $card = PaymentMethod::create(['company_id' => $company->id, 'name' => 'Cartão Débito/Crédito', 'type' => 'credit_card', 'is_active' => true]);

            // 4. Categorias
            $catInsumos = MenuCategory::create(['company_id' => $company->id, 'name' => 'Ingredientes', 'icon' => 'tabler-tools-kitchen-2', 'order' => 1]);
            $catBurgers = MenuCategory::create(['company_id' => $company->id, 'name' => 'Burgers', 'icon' => 'tabler-burger', 'order' => 2]);
            $catBebidas = MenuCategory::create(['company_id' => $company->id, 'name' => 'Bebidas', 'icon' => 'tabler-glass-full', 'order' => 3]);
            $catPorcoes = MenuCategory::create(['company_id' => $company->id, 'name' => 'Porções', 'icon' => 'tabler-fry', 'order' => 4]);

            // 5. Insumos (Estoque que não vende direto)
            $pao = InventoryItem::create(['company_id' => $company->id, 'menu_category_id' => $catInsumos->id, 'name' => 'Pão Brioche', 'cost_price' => 1.50, 'quantity' => 100, 'unit' => 'un', 'is_ingredient' => true, 'is_for_sale' => false]);
            $carne = InventoryItem::create(['company_id' => $company->id, 'menu_category_id' => $catInsumos->id, 'name' => 'Carne 180g', 'cost_price' => 5.50, 'quantity' => 50, 'unit' => 'un', 'is_ingredient' => true, 'is_for_sale' => false]);
            $queijo = InventoryItem::create(['company_id' => $company->id, 'menu_category_id' => $catInsumos->id, 'name' => 'Queijo Cheddar', 'cost_price' => 0.80, 'quantity' => 200, 'unit' => 'un', 'is_ingredient' => true, 'is_for_sale' => false]);
            $bacon = InventoryItem::create(['company_id' => $company->id, 'menu_category_id' => $catInsumos->id, 'name' => 'Bacon Fatiado', 'cost_price' => 1.20, 'quantity' => 150, 'unit' => 'un', 'is_ingredient' => true, 'is_for_sale' => false]);

            // 6. Produtos Finais (O que aparece no PDV)
            $xbacon = InventoryItem::create(['company_id' => $company->id, 'menu_category_id' => $catBurgers->id, 'name' => 'X-Bacon Especial', 'cost_price' => 9.00, 'selling_price' => 32.00, 'quantity' => 0, 'unit' => 'un', 'is_ingredient' => false, 'is_for_sale' => true]);
            $coca = InventoryItem::create(['company_id' => $company->id, 'menu_category_id' => $catBebidas->id, 'name' => 'Coca-Cola Lata', 'cost_price' => 2.50, 'selling_price' => 6.00, 'quantity' => 48, 'unit' => 'un', 'is_ingredient' => false, 'is_for_sale' => true]);

            // 7. Fichas Técnicas
            ProductRecipe::create(['product_id' => $xbacon->id, 'ingredient_id' => $pao->id, 'quantity' => 1]);
            ProductRecipe::create(['product_id' => $xbacon->id, 'ingredient_id' => $carne->id, 'quantity' => 1]);
            ProductRecipe::create(['product_id' => $xbacon->id, 'ingredient_id' => $queijo->id, 'quantity' => 2]);
            ProductRecipe::create(['product_id' => $xbacon->id, 'ingredient_id' => $bacon->id, 'quantity' => 3]);

            // 8. Clientes
            $cliente1 = Clients::create(['company_id' => $company->id, 'name' => 'Matheus Ghotme', 'email' => 'matheus@exemplo.com']);
            
            // 9. Vendas Fictícias (Para o Dashboard não ficar vazio)
            for ($i = 1; $i <= 5; $i++) {
                $os = OrdemServico::create([
                    'company_id' => $company->id,
                    'client_id' => $cliente1->id,
                    'user_id' => $user->id,
                    'status' => 'paid',
                    'description' => 'Venda de Teste #' . $i,
                    'payment_method_id' => $card->id,
                    'created_at' => now()->subHours($i * 2),
                ]);

                OrdemServicoPart::create([
                    'ordem_servico_id' => $os->id,
                    'inventory_item_id' => $xbacon->id,
                    'quantity' => 1,
                    'price' => 32.00
                ]);
                
                OrdemServicoPart::create([
                    'ordem_servico_id' => $os->id,
                    'inventory_item_id' => $coca->id,
                    'quantity' => 1,
                    'price' => 6.00
                ]);
            }
        });
    }
}
