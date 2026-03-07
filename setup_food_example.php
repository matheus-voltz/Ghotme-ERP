<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use App\Models\User;
use App\Models\InventoryItem;
use App\Models\ProductRecipe;
use App\Services\NicheInitializerService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

echo "🔨 Iniciando montagem do Produto Final (Ghotme Dog) via API...\n";

// 1. Localizar ou Criar Empresa/Usuário
$email = 'admin.food@ghotme.com.br';
$user = User::where('email', $email)->first();

if (!$user) {
    $company = Company::create([
        'name' => 'Ghotme Food Experience',
        'niche' => 'food_service',
        'document_number' => '44333222000188',
        'is_active' => true,
    ]);
    app(NicheInitializerService::class)->initialize($company->id, 'food_service');

    $user = User::create([
        'company_id' => $company->id,
        'name' => 'Matheus Admin Food',
        'email' => $email,
        'password' => Hash::make('ghotme123'),
        'role' => 'admin',
        'plan' => 'pro',
        'trial_ends_at' => now()->addYear(),
    ]);
}

$companyId = $user->company_id;

// 2. Cadastro de Insumos
$pao = InventoryItem::updateOrCreate(
    ['company_id' => $companyId, 'name' => 'Pão de Brioche Artesanal'],
    [
        'sku' => 'INS-PAO-01',
        'cost_price' => 1.80,
        'quantity' => 150,
        'is_ingredient' => true,
        'unit' => 'UN',
        'is_active' => true
    ]
);

$salsicha = InventoryItem::updateOrCreate(
    ['company_id' => $companyId, 'name' => 'Salsicha Premium Frankfurt'],
    [
        'sku' => 'INS-SAL-01',
        'cost_price' => 2.50,
        'quantity' => 80,
        'is_ingredient' => true,
        'unit' => 'UN',
        'is_active' => true
    ]
);

echo "✅ Insumos Criados: {$pao->name} e {$salsicha->name}\n";

// 3. Cadastro do Produto Final
$produto = InventoryItem::updateOrCreate(
    ['company_id' => $companyId, 'name' => 'Ghotme Hot Dog Signature'],
    [
        'sku' => 'PROD-DOG-01',
        'selling_price' => 28.90,
        'cost_price' => 4.30, // Soma dos insumos
        'quantity' => 0,
        'is_ingredient' => false,
        'unit' => 'UN',
        'is_active' => true
    ]
);

// 4. Montagem da Ficha Técnica (Receita)
ProductRecipe::where('product_id', $produto->id)->delete();

ProductRecipe::create([
    'product_id' => $produto->id,
    'ingredient_id' => $pao->id,
    'quantity' => 1
]);

ProductRecipe::create([
    'product_id' => $produto->id,
    'ingredient_id' => $salsicha->id,
    'quantity' => 1
]);

echo "🍔 Produto Final Criado: {$produto->name}\n";
echo "📋 Ficha Técnica vinculada com sucesso!\n";

echo "\n🚀 Login para acessar via browser:\n";
echo "E-mail: {$email}\n";
echo "Senha: ghotme123\n";
