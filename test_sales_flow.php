<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use App\Models\User;
use App\Models\InventoryItem;
use App\Models\OrdemServico;
use App\Models\FinancialTransaction;
use App\Models\StockMovement;
use App\Models\ProductRecipe;
use App\Services\NicheInitializerService;
use App\Http\Controllers\Api\ApiOrdemServicoController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

echo "🚀 Iniciando teste do fluxo de vendas (Food Service)...\n";

// 1. Setup Data with Unique Keys to avoid FK issues
$unique = Str::random(4);
$email = "test.sales.{$unique}@ghotme.com";

$company = Company::create([
    'name' => "Restaurante Teste {$unique}",
    'niche' => 'food_service',
    'document_number' => rand(10000000, 99999999) . '000100',
]);

app(NicheInitializerService::class)->initialize($company->id, 'food_service');

$user = User::create([
    'company_id' => $company->id,
    'name' => 'Vendedor Teste',
    'email' => $email,
    'password' => Hash::make('123456'),
    'role' => 'admin',
    'plan' => 'pro',
    'trial_ends_at' => now()->addYear(),
]);

Auth::login($user);

// 2. Criar Ingredientes
$pao = InventoryItem::create([
    'company_id' => $company->id,
    'name' => 'Pão de Brioche',
    'quantity' => 100,
    'is_ingredient' => true,
]);

$carne = InventoryItem::create([
    'company_id' => $company->id,
    'name' => 'Carne 180g',
    'quantity' => 50,
    'is_ingredient' => true,
]);

// 3. Criar Produto Final
$product = InventoryItem::create([
    'company_id' => $company->id,
    'name' => 'X-Burguer Especial',
    'selling_price' => 25.50,
    'cost_price' => 10.00,
    'quantity' => 0,
    'is_active' => true,
]);

// 4. Vincular Ficha Técnica
ProductRecipe::create([
    'product_id' => $product->id,
    'ingredient_id' => $pao->id,
    'quantity' => 1
]);

ProductRecipe::create([
    'product_id' => $product->id,
    'ingredient_id' => $carne->id,
    'quantity' => 1
]);

echo "✅ Produto e Ficha Técnica criados: {$product->name}\n";

// 5. Criar Venda via Controller
$controller = new ApiOrdemServicoController();
$requestData = [
    'customer_name' => 'Cliente Ficha Técnica',
    'status' => 'pending',
    'parts' => [
        $product->id => [
            'selected' => true,
            'price' => 25.50,
            'quantity' => 2 // Vender 2 lanches
        ]
    ]
];

$osService = app(\App\Services\OrdemServicoService::class);
$request = new Request($requestData);
$request->setUserResolver(fn() => $user);

echo "\n📦 Criando pedido de 2 X-Burguers...\n";
$response = $controller->store($request, $osService);
$os = $response->getData();
$osId = $os->id;

// 6. Mover para 'running' (Início de preparo)
echo "👨‍🍳 Movendo pedido #{$osId} para 'Em Preparo' (running)...\n";
$requestUpdate = new Request(['status' => 'running']);
$requestUpdate->setUserResolver(fn() => $user);
$controller->updateStatus($requestUpdate, $osId);

// 7. Verificações
$pao->refresh();
$carne->refresh();

echo "\n📊 Verificação de Estoque:\n";
echo "🍞 Pão: {$pao->quantity} (Esperado: 98)\n";
echo "🥩 Carne: {$carne->quantity} (Esperado: 48)\n";

$paoOk = ($pao->quantity == 98);
$carneOk = ($carne->quantity == 48);

if ($paoOk && $carneOk) {
    echo "✨ SUCESSO: A baixa de ingredientes funcionou perfeitamente! ✨\n";
} else {
    echo "❌ FALHA: Erro na baixa dos ingredientes.\n";
}

// 8. Finalizar e Pagamento
echo "\n💰 Finalizando e Gerando Financeiro...\n";
$requestFinal = new Request([
    'status' => 'finalized',
    'payment_method' => 'pix'
]);
$requestFinal->setUserResolver(fn() => $user);
$controller->updateStatus($requestFinal, $osId);

$transaction = FinancialTransaction::where('related_id', $osId)
    ->where('related_type', OrdemServico::class)
    ->first();

if ($transaction && $transaction->amount == 51.00) {
    echo "✅ Transação financeira de R$ 51.00 criada com sucesso.\n";
} else {
    echo "❌ FALHA: Erro na geração do financeiro.\n";
}

if ($paoOk && $carneOk && $transaction) {
    echo "\n🏆 FLUXO DE VENDAS 100% VALIDADO! 🏆\n";
} else {
    echo "\n⚠️ FLUXO COM ERROS. Revise os logs.\n";
}
