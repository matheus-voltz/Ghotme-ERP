<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico;
use App\Models\Budget;
use App\Models\Clients;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\FinancialTransaction;
use App\Models\User;
use App\Models\InventoryItem;

class ApiOrdemServicoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = OrdemServico::with(['client', 'veiculo', 'user', 'items', 'parts'])->latest();
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        return response()->json($query->paginate(50));
    }

    public function show($id)
    {
        $os = OrdemServico::with(['client', 'veiculo', 'user', 'items.service', 'parts.inventoryItem'])->findOrFail($id);
        return response()->json($os);
    }

    public function store(Request $request, \App\Services\OrdemServicoService $service)
    {
        $user = auth()->user();
        $isFoodService = ($user->company->niche ?? null) === 'food_service';

        // Validação flexível: aceita client_id tradicional ou customer_name (food service)
        $validated = $request->validate([
            'client_id' => 'nullable|integer',
            'customer_name' => 'nullable|string|max:255',
            'veiculo_id' => 'nullable|integer',
            'product_id' => 'nullable|integer',
            'status' => 'required|string',
            'description' => 'nullable|string',
            'km_entry' => 'nullable|string',
            'parts' => 'nullable|array',
            'services' => 'nullable|array',
            'payment_method' => 'nullable|string',
        ]);

        try {
            // Se for Food Service, preparamos o terreno (sempre cria client)
            if ($isFoodService) {
                $customerName = $request->filled('customer_name') ? $request->customer_name : 'Balcão';

                // 1. Localiza ou Cria o Cliente (Evita duplicar se já existir um com mesmo nome)
                $client = Clients::where('name', $customerName)
                    ->where('company_id', $user->company_id)
                    ->first();

                if (!$client) {
                    $client = Clients::create([
                        'name' => $customerName,
                        'company_id' => $user->company_id,
                        'uuid' => (string) \Illuminate\Support\Str::uuid()
                    ]);
                }

                $validated['client_id'] = $client->id;
                $validated['customer_name'] = $customerName;

                // 2. Cria um Objeto 'Pedido' (Vehicle) genérico se não houver um
                $pedido = \App\Models\Vehicles::where('cliente_id', $client->id)
                    ->where('modelo', 'Pedido ' . $customerName)
                    ->where('company_id', $user->company_id)
                    ->first();

                if (!$pedido) {
                    $pedido = \App\Models\Vehicles::create([
                        'cliente_id' => $client->id,
                        'modelo' => 'Pedido ' . $customerName,
                        'company_id' => $user->company_id,
                        'marca' => 'Ghotme Food',
                        'placa' => 'FOOD-' . now()->format('His')
                    ]);
                }

                $validated['veiculo_id'] = $pedido->id;

                // 3. Se selecionou um produto isolado (legacy/outros casos), injeta no array de parts
                if ($request->filled('product_id') && !isset($validated['parts'])) {
                    $item = InventoryItem::find($request->product_id);
                    if ($item) {
                        $validated['parts'] = [
                            $item->id => [
                                'selected' => true,
                                'price' => $item->selling_price,
                                'quantity' => 1
                            ]
                        ];
                    }
                }
            }

            $os = $service->store($validated);

            // Adiciona o link do portal para o mobile usar
            $os->load('client', 'company');
            if ($os->client) {
                $os->portal_url = url("/portal/{$os->client->uuid}");
                $os->share_message = "Olá {$os->client->name}, seu pedido foi recebido com sucesso na " . ($os->company->name ?? 'Ghotme') . ". Acompanhe o preparo por aqui: " . $os->portal_url;
                $os->client_phone = $os->client->phone ?? $os->client->contact_number ?? '';
            }

            return response()->json($os, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao criar pedido: ' . $e->getMessage()], 500);
        }
    }

    public function getWatchDashboard(Request $request)
    {
        $user = $request->user();
        $orders = OrdemServico::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved', 'running'])
            ->with(['client', 'veiculo'])
            ->latest()
            ->get()
            ->map(function ($os) {
                return [
                    'id' => $os->id,
                    'client' => $os->client ? ($os->client->name ?: $os->client->company_name) : 'N/A',
                    'vehicle' => $os->veiculo ? $os->veiculo->modelo : 'N/A',
                    'status' => $os->status,
                ];
            });

        return response()->json([
            'user_name' => $user->name,
            'orders' => $orders
        ]);
    }

    public function getDashboardStats(Request $request)
    {
        $user = $request->user();
        $formatOS = function ($os) {
            $isDelivery = str_contains($os->description ?? '', 'ENTREGA') || ($os->payment_method === 'ifood');
            return [
                'id' => $os->id,
                'client_name' => $os->client ? ($os->client->name ?: $os->client->company_name) : ($os->customer_name ?: 'Balcão'),
                'customer_name' => $os->customer_name,
                'vehicle' => $os->veiculo ? $os->veiculo->modelo : null,
                'plate' => $os->veiculo ? $os->veiculo->placa : 'N/A',
                'status' => $os->status,
                'total' => $os->total,
                'payment_method' => $os->payment_method,
                'is_delivery' => $isDelivery,
                'created_at' => $os->created_at
            ];
        };

        if ($user->role === 'admin') {
            $companyId = $user->company_id;
            $today = Carbon::today();
            $month = Carbon::now()->month;
            $year = Carbon::now()->year;

            // Current Month Revenue
            $financialRevenue = FinancialTransaction::where('company_id', $companyId)->where('type', 'in')->where('status', 'paid')
                ->whereMonth('paid_at', $month)->whereYear('paid_at', $year)->sum('amount');
            $osRevenue = OrdemServico::where('company_id', $companyId)->with(['items', 'parts'])->whereIn('status', ['paid', 'finalized', 'completed'])
                ->whereMonth('updated_at', $month)->whereYear('updated_at', $year)
                ->get()->sum('total');
            $monthlyRevenue = $financialRevenue + $osRevenue;

            // Last Month Revenue for Growth
            $lastMonth = Carbon::now()->subMonth();
            $financialRevenueLast = FinancialTransaction::where('company_id', $companyId)->where('type', 'in')->where('status', 'paid')
                ->whereMonth('paid_at', $lastMonth->month)->whereYear('paid_at', $lastMonth->year)->sum('amount');
            $osRevenueLast = OrdemServico::where('company_id', $companyId)->with(['items', 'parts'])->whereIn('status', ['paid', 'finalized', 'completed'])
                ->whereMonth('updated_at', $lastMonth->month)->whereYear('updated_at', $lastMonth->year)
                ->get()->sum('total');
            $revenueLastMonth = $financialRevenueLast + $osRevenueLast;
            $revenueGrowth = round($revenueLastMonth > 0 ? (($monthlyRevenue - $revenueLastMonth) / $revenueLastMonth) * 100 : ($monthlyRevenue > 0 ? 100 : 0), 1);

            // Expenses for Profitability
            $monthlyExpenses = FinancialTransaction::where('company_id', $companyId)->where('type', 'out')->where('status', 'paid')
                ->whereMonth('paid_at', $month)->whereYear('paid_at', $year)->sum('amount');
            $monthlyProfitability = round($monthlyRevenue > 0 ? (($monthlyRevenue - $monthlyExpenses) / $monthlyRevenue) * 100 : 0, 1);

            // Last 7 days chart data
            $revenueChart = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $dailyFinancial = FinancialTransaction::where('company_id', $companyId)->where('type', 'in')->where('status', 'paid')
                    ->whereDate('paid_at', $date)->sum('amount');
                $dailyOS = OrdemServico::where('company_id', $companyId)->with(['items', 'parts'])->whereIn('status', ['paid', 'finalized', 'completed'])
                    ->whereDate('updated_at', $date)->get()->sum('total');

                $revenueChart[] = [
                    'day' => $date->format('d/m'),
                    'value' => (float)($dailyFinancial + $dailyOS)
                ];
            }

            $osStats = [
                'pending' => OrdemServico::where('company_id', $companyId)->where('status', 'pending')->count(),
                'approved' => OrdemServico::where('company_id', $companyId)->where('status', 'approved')->count(),
                'running' => OrdemServico::where('company_id', $companyId)->where('status', 'running')->count(),
                'finalized_today' => OrdemServico::where('company_id', $companyId)->where('status', 'finalized')->whereDate('updated_at', $today)->count(),
            ];
            return response()->json([
                'monthlyRevenue' => $monthlyRevenue,
                'revenueGrowth' => $revenueGrowth,
                'monthlyProfitability' => $monthlyProfitability,
                'totalClients' => Clients::where('company_id', $companyId)->count(),
                'osStats' => $osStats,
                'revenueChart' => $revenueChart,
                'lowStockCount' => InventoryItem::where('company_id', $companyId)->whereColumn('quantity', '<=', 'min_quantity')->count(),
                'pendingBudgetsCount' => Budget::where('company_id', $companyId)->where('status', 'pending')->where('created_at', '<', Carbon::now()->subDays(5))->count(),
                'recentOS' => OrdemServico::where('company_id', $companyId)->whereIn('status', ['pending', 'approved', 'running'])->with(['client', 'veiculo'])->latest()->take(10)->get()->map($formatOS),
                'unreadNotificationsCount' => $user->unreadNotifications->count()
            ]);
        }

        $stats = [
            'runningOS' => OrdemServico::where('user_id', $user->id)->where('status', 'running')->count(),
            'completedToday' => OrdemServico::where('user_id', $user->id)->where('status', 'finalized')->whereDate('updated_at', Carbon::today())->count(),
            'pendingBudgets' => Budget::where('user_id', $user->id)->where('status', 'pending')->count(),
            'criticalBudgets' => Budget::where('user_id', $user->id)->where('status', 'pending')->where('created_at', '<', Carbon::now()->subDays(5))->count(),
        ];

        return response()->json([
            'stats' => $stats,
            'recentOS' => OrdemServico::where('user_id', $user->id)->whereIn('status', ['pending', 'approved', 'running'])->with(['client', 'veiculo'])->latest()->take(10)->get()->map($formatOS),
            'unreadNotificationsCount' => $user->unreadNotifications->count()
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,running,finalized,canceled',
            'payment_method' => 'nullable|string'
        ]);

        try {
            $os = OrdemServico::findOrFail($id);
            $os->status = $request->status;

            // Salvar payment_method só se a coluna existir (migration pode não ter rodado)
            if ($request->filled('payment_method') && \Illuminate\Support\Facades\Schema::hasColumn('ordem_servicos', 'payment_method')) {
                $os->payment_method = $request->payment_method;
            }

            $os->save();

            // Aciona automações de estoque e financeiro via Service se necessário
            $osService = app(\App\Services\OrdemServicoService::class);
            $isFood = (auth()->user()->company->niche ?? null) === 'food_service';

            if ($os->status === 'finalized' || ($isFood && $os->status === 'running')) {
                $osService->deductStock($os);
            }

            if ($request->status === 'finalized' && $request->filled('payment_method')) {
                $methodMap = [
                    'cash'   => 'dinheiro',
                    'money'  => 'dinheiro',
                    'credit' => 'cartao_credito',
                    'debit'  => 'cartao_debito',
                    'pix'    => 'pix',
                ];
                $pmType = $methodMap[$request->payment_method] ?? 'dinheiro';
                $companyId = $os->company_id;

                $pm = \App\Models\PaymentMethod::firstOrCreate(
                    ['type' => $pmType, 'company_id' => $companyId],
                    ['name' => ucfirst(str_replace('_', ' ', $pmType)), 'is_active' => true]
                );

                \App\Models\FinancialTransaction::create([
                    'company_id'        => $companyId,
                    'description'       => 'Baixa PDV - Pedido #' . $os->id,
                    'amount'            => $os->total,
                    'type'              => 'in',
                    'status'            => 'paid',
                    'due_date'          => now(),
                    'paid_at'           => now(),
                    'payment_method_id' => $pm->id,
                    'client_id'         => $os->client_id,
                    'category'          => 'Vendas',
                    'related_type'      => get_class($os),
                    'related_id'        => $os->id,
                    'user_id'           => $request->user()->id,
                ]);
            }

            return response()->json(['message' => 'Status atualizado', 'os' => $os]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('updateStatus error: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao atualizar status: ' . $e->getMessage()], 500);
        }
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'device_password' => 'nullable|string',
            'device_pattern_lock' => 'nullable|string'
        ]);

        $os = OrdemServico::findOrFail($id);
        $os->device_password = $request->input('device_password');
        $os->device_pattern_lock = $request->input('device_pattern_lock');
        $os->save();

        return response()->json(['message' => 'Senha atualizada com sucesso', 'os' => $os]);
    }

    public function toggleTimer($itemId)
    {
        $item = \App\Models\OrdemServicoItem::findOrFail($itemId);
        if ($item->status === 'in_progress') {
            $item->stopTimer();
        } else {
            $item->startTimer();
        }
        return response()->json(['success' => true, 'item' => $item]);
    }

    public function completeItem($itemId)
    {
        $item = \App\Models\OrdemServicoItem::findOrFail($itemId);
        $item->complete();
        return response()->json(['success' => true, 'item' => $item]);
    }

    public function generatePix($id)
    {
        $os = OrdemServico::findOrFail($id);

        // Verificar permissão
        if ($os->company_id !== auth()->user()->company_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $paymentService = new \App\Services\PaymentService();
        $result = $paymentService->generatePixCharge($os);

        if (isset($result['error'])) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    public function checkPixStatus($id)
    {
        $os = OrdemServico::findOrFail($id);

        // Verificar permissão
        if ($os->company_id !== auth()->user()->company_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $paymentService = new \App\Services\PaymentService();
        $isPaid = $paymentService->checkPixStatus($os);

        // Se pagou, finalizar automaticamente a OS
        if ($isPaid && $os->status !== 'finalized') {
            $os->update([
                'status' => 'finalized',
                'paid_at' => now()
            ]);

            // Criar FinancialTransaction
            $pm = \App\Models\PaymentMethod::firstOrCreate(
                ['type' => 'pix'],
                ['name' => 'PIX', 'is_active' => true]
            );

            \App\Models\FinancialTransaction::create([
                'company_id' => $os->company_id,
                'description' => 'Cobrança PIX - Pedido #' . $os->id,
                'amount' => $os->total,
                'type' => 'in',
                'status' => 'paid',
                'due_date' => now(),
                'paid_at' => now(),
                'payment_method_id' => $pm->id,
                'client_id' => $os->client_id,
                'category' => 'Vendas',
                'related_type' => get_class($os),
                'related_id' => $os->id,
                'user_id' => auth()->user()->id,
            ]);
        }

        return response()->json([
            'is_paid' => $isPaid,
            'payment_id' => $os->gateway_payment_id,
            'os_status' => $os->status,
        ]);
    }

    public function destroy($id)
    {
        $os = OrdemServico::findOrFail($id);
        $os->delete();
        return response()->json(['message' => 'Pedido excluído com sucesso']);
    }
}
