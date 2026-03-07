<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\User;
use App\Models\MenuCategory;
use App\Models\InventoryItem;
use App\Models\ProductRecipe;
use Illuminate\Support\Facades\Hash;

class SetupFoodService extends Command
{
    protected $signature = 'app:setup-food-service {email=vendas@foodservice.com.br} {password=venda123}';
    protected $description = 'Configura um ambiente completo de Food Service (Empresa, Usuário e Dados Iniciais)';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $this->info("Iniciando setup do Food Service para: {$email}...");

        // 1. Criar Empresa
        $company = Company::updateOrCreate(
            ['email' => 'contato@foodservice.com.br'],
            [
                'name' => 'Restaurante Ghotme Gourmet',
                'document_number' => '12.345.678/0001-99',
                'niche' => 'food_service',
                'is_active' => true
            ]
        );

        // 2. Criar Usuário Gestor
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'company_id' => $company->id,
                'name' => 'Gestor Food Service',
                'password' => Hash::make($password),
                'role' => 'admin',
                'email_verified_at' => now(),
                'status' => 'active',
                'niche' => 'food_service'
            ]
        );

        // 3. Criar Categorias
        $catInsumos = MenuCategory::updateOrCreate(['company_id' => $company->id, 'name' => 'Ingredientes'], ['icon' => 'tabler-tools-kitchen-2', 'order' => 1]);
        $catLanches = MenuCategory::updateOrCreate(['company_id' => $company->id, 'name' => 'Lanches'], ['icon' => 'tabler-burger', 'order' => 2]);
        $catBebidas = MenuCategory::updateOrCreate(['company_id' => $company->id, 'name' => 'Bebidas'], ['icon' => 'tabler-glass-full', 'order' => 3]);

        // 4. Criar Insumos (O que não vende)
        $carne = InventoryItem::updateOrCreate(
            ['company_id' => $company->id, 'name' => 'Hambúrguer Bovino 180g'],
            ['menu_category_id' => $catInsumos->id, 'cost_price' => 5.50, 'selling_price' => 0, 'quantity' => 100, 'unit' => 'un', 'is_ingredient' => true, 'is_for_sale' => false]
        );

        $pao = InventoryItem::updateOrCreate(
            ['company_id' => $company->id, 'name' => 'Pão de Brioche'],
            ['menu_category_id' => $catInsumos->id, 'cost_price' => 1.20, 'selling_price' => 0, 'quantity' => 50, 'unit' => 'un', 'is_ingredient' => true, 'is_for_sale' => false]
        );

        $queijo = InventoryItem::updateOrCreate(
            ['company_id' => $company->id, 'name' => 'Queijo Cheddar'],
            ['menu_category_id' => $catInsumos->id, 'cost_price' => 0.80, 'selling_price' => 0, 'quantity' => 200, 'unit' => 'un', 'is_ingredient' => true, 'is_for_sale' => false]
        );

        // 5. Criar Produto Final (O que vende)
        $burger = InventoryItem::updateOrCreate(
            ['company_id' => $company->id, 'name' => 'Cheeseburger Clássico'],
            ['menu_category_id' => $catLanches->id, 'cost_price' => 0, 'selling_price' => 28.90, 'quantity' => 0, 'unit' => 'un', 'is_ingredient' => false, 'is_for_sale' => true]
        );

        // 6. Montar Ficha Técnica (Mágica)
        ProductRecipe::updateOrCreate(['product_id' => $burger->id, 'ingredient_id' => $carne->id], ['quantity' => 1]);
        ProductRecipe::updateOrCreate(['product_id' => $burger->id, 'ingredient_id' => $pao->id], ['quantity' => 1]);
        ProductRecipe::updateOrCreate(['product_id' => $burger->id, 'ingredient_id' => $queijo->id], ['quantity' => 2]); // 2 fatias

        // Atualiza o preço de custo do burger baseado na ficha
        $totalCost = (5.50 * 1) + (1.20 * 1) + (0.80 * 2);
        $burger->update(['cost_price' => $totalCost]);

        $this->info("------------------------------------------------");
        $this->info("Setup concluído com sucesso!");
        $this->info("Empresa: {$company->name}");
        $this->info("Login: {$email}");
        $this->info("Senha: {$password}");
        $this->info("------------------------------------------------");
    }
}
