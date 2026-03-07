<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrdemServico;
use App\Models\FinancialTransaction;
use App\Models\Clients;
use App\Models\InventoryItem;
use App\Models\Budget;
use App\Models\OrdemServicoItem;
use App\Models\OrdemServicoPart;
use App\Models\SystemUpdate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class HomePage extends Controller
{
  public function aiAnalysis()
  {
    $user = Auth::user();

    // Verificação de Plano
    if (!$user->hasFeature('ai_analysis')) {
      return response()->json([
        'success' => false,
        'need_upgrade' => true,
        'message' => 'A Análise de Negócio com IA é exclusiva para o plano Enterprise. Deseja fazer o upgrade agora?'
      ], 403);
    }

    $company = $user->company;
    $companyId = $user->company_id;
    $apiKey = config('services.ai.gemini_key');

    if (!$apiKey) return response()->json(['success' => false, 'message' => 'IA não configurada']);

    // Limites de Plano (usa a mesma chave do badge no dashboard)
    if (!$user->hasFeature('ai_unlimited')) {
      $monthKey = now()->format('Y-m');
      $usageKey = "ai_usage_{$companyId}_{$monthKey}";
      $usageCount = Cache::get($usageKey, 0);

      if ($usageCount >= 10) {
        return response()->json([
          'success' => false,
          'message' => 'Limite de 10 consultas de IA mensais atingido. Faça o upgrade para o Enterprise para análises ilimitadas!',
          'limit_reached' => true
        ], 403);
      }
      Cache::put($usageKey, $usageCount + 1, now()->addMonth());
    }

    // Tradução manual de slugs para nomes amigáveis para a IA
    $nicheKey = get_current_niche();
    $nichesNames = [
      'workshop' => 'Oficina Mecânica',
      'automotive' => 'Centro Automotivo',
      'electronics' => 'Assistência Técnica de Eletrônicos',
      'pet' => 'Pet Shop e Clínica Veterinária',
      'food_service' => 'Food Truck e Gastronomia (Cachorro-quente)',
      'beauty_clinic' => 'Clínica de Estética',
      'construction' => 'Construtora e Empreiteira'
    ];
    $nicheName = $nichesNames[$nicheKey] ?? $nicheKey;
    $companyName = $company->name ?? 'sua empresa';

    $revenue = FinancialTransaction::where('company_id', $companyId)->where('type', 'in')->where('status', 'paid')->whereMonth('paid_at', now()->month)->sum('amount');
    $pendingOS = OrdemServico::where('company_id', $companyId)->where('status', 'pending')->count();
    $completedOS = OrdemServico::where('company_id', $companyId)->where('status', 'finalized')->whereMonth('created_at', now()->month)->count();

    $prompt = "Aja como um consultor de negócios sênior especialista em {$nicheName}.
    A empresa que você está analisando se chama {$companyName}. 

    DADOS DO MÊS:
    - Receita: R$ " . number_format($revenue, 2, ',', '.') . "
    - Serviços em Aberto: {$pendingOS}
    - Serviços Concluídos: {$completedOS}

    Gere um insight estratégico para o dono da {$companyName}.
    
    REGRAS OBRIGATÓRIAS:
    1. JAMAIS use placeholders como '[Nicho]' ou '[Exemplo]'. Use termos REAIS de {$nicheName}.
    2. Se for Pet Shop, fale de banho, tosa, ração ou vacinas.
    3. Se for Estética, fale de procedimentos, Botox, drenagem ou pacotes.
    4. Seja direto. Comece falando da {$companyName} e como ela pode lucrar mais com os {$pendingOS} serviços parados.
    5. Retorne APENAS o texto do insight, sem saudações como 'Entendi a situação'.
    6. JAMAIS use hashtags (# ou ##) no texto. Use negrito (**Texto**) para destacar títulos.";

    try {
      $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;
      $response = Http::post($url, [
        'contents' => [['parts' => [['text' => $prompt]]]]
      ]);

      if ($response->successful()) {
        $insight = $response->json('candidates.0.content.parts.0.text');
        $insight = preg_replace('/^```html|```$/m', '', $insight);
        return response()->json(['success' => true, 'insight' => trim($insight)]);
      }
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }

    return response()->json(['success' => false, 'message' => 'Erro ao gerar análise']);
  }

  public function index()
  {
    $user = Auth::user();

    // Redireciona para o Master Dashboard se for o proprietário do sistema
    if ($user && $user->is_master) {
      return redirect()->route('master.dashboard');
    }

    $companyId = $user->company_id ?? 0;

    // Cache de 15 minutos para performance, chave varia por empresa e papel
    $cacheKey = "dashboard_stats_{$companyId}_" . ($user->role !== 'admin' ? "user_{$user->id}" : "admin");

    $data = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($user, $companyId) {
      if ($user && $user->role !== 'admin') {
        return $this->getEmployeeData($user, $companyId);
      }
      return $this->getAdminData($companyId);
    });

    $currentNiche = get_current_niche();
    $view = ($user && $user->role !== 'admin') ? 'content.pages.dashboard.dashboards-employee' : ($currentNiche === 'food_service' ? 'content.pages.dashboard.dashboards-foodservice' : 'content.pages.dashboard.dashboards-analytics');
    $aiUsageCount = Cache::get("ai_usage_{$companyId}_" . now()->format('Y-m'), 0);

    return view($view, array_merge($data, [
      'user' => $user,
      'aiUsageCount' => $aiUsageCount
    ]));
  }

  /**
   * Dados simplificados para funcionários/técnicos
   */
  protected function getEmployeeData($user, $companyId)
  {
    return [
      'pendingBudgetsCount' => Budget::where('user_id', $user->id)->where('status', 'pending')->count(),
      'runningOSCount' => OrdemServico::where('user_id', $user->id)->where('status', 'running')->count(),
      'completedOSToday' => OrdemServico::where('user_id', $user->id)
        ->where('status', 'finalized')
        ->whereDate('updated_at', Carbon::today())
        ->count(),
      'recentBudgets' => Budget::with('client')
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get(),
      'recentOS' => OrdemServico::with(['client', 'veiculo'])
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get(),
    ];
  }

  /**
   * Dados completos para administradores (Otimizado)
   */
  protected function getAdminData($companyId)
  {
    $today = Carbon::today();
    $now = Carbon::now();
    $startOfMonth = $now->copy()->startOfMonth();
    $lastMonth = $now->copy()->subMonth();

    // 1. OS Stats (Filtrado por Empresa)
    $osStats = [
      'pending' => OrdemServico::where('company_id', $companyId)->where('status', 'pending')->count(),
      'running' => OrdemServico::where('company_id', $companyId)->where('status', 'running')->count(),
      'finalized_today' => OrdemServico::where('company_id', $companyId)->where('status', 'finalized')->whereDate('updated_at', $today)->count(),
      'total_month' => OrdemServico::where('company_id', $companyId)->whereMonth('created_at', $now->month)->count(),
    ];

    $calculateRevenue = function ($month, $year) use ($companyId) {
      // Receita de Transações Diretas
      $finRevenue = FinancialTransaction::where('company_id', $companyId)
        ->where('type', 'in')
        ->where('status', 'paid')
        ->whereMonth('paid_at', $month)
        ->whereYear('paid_at', $year)
        ->sum('amount');

      // Receita de Ordens de Serviço (Itens + Peças)
      $osIds = OrdemServico::where('company_id', $companyId)
        ->whereIn('status', ['paid', 'finalized', 'completed'])
        ->whereMonth('updated_at', $month)
        ->whereYear('updated_at', $year)
        ->pluck('id');

      $itemsSum = OrdemServicoItem::whereIn('ordem_servico_id', $osIds)->sum(DB::raw('price * quantity'));
      $partsSum = OrdemServicoPart::whereIn('ordem_servico_id', $osIds)->sum(DB::raw('price * quantity'));

      return $finRevenue + $itemsSum + $partsSum;
    };

    $revenueMonth = $calculateRevenue($now->month, $now->year);
    $revenueLastMonth = $calculateRevenue($lastMonth->month, $lastMonth->year);

    $revenueGrowth = $revenueLastMonth > 0
      ? (($revenueMonth - $revenueLastMonth) / $revenueLastMonth) * 100
      : ($revenueMonth > 0 ? 100 : 0);

    // 3. Financeiro Pendente (Contas a Pagar/Receber próximas)
    $receivablesPending = FinancialTransaction::where('type', 'in')
      ->where('status', 'pending')
      ->whereDate('due_date', '<=', $now->copy()->addDays(7))
      ->sum('amount');

    $payablesPending = FinancialTransaction::where('type', 'out')
      ->where('status', 'pending')
      ->whereDate('due_date', '<=', $now->copy()->addDays(7))
      ->sum('amount');

    // 4. Gráficos e Tendências (Últimos 6 Meses) OTIMIZADO (1 Query por Métrica)
    $months = [];
    $revenueTrends = [];
    $expenseTrends = [];
    $budgetTrends = [];

    $sixMonthsAgo = $now->copy()->subMonths(5)->startOfMonth();

    // Sumarização direta no BD de Transações (Receita/Despesa Direta)
    $financialRaw = FinancialTransaction::select(
      DB::raw('MONTH(paid_at) as m'),
      DB::raw('YEAR(paid_at) as y'),
      'type',
      DB::raw('SUM(amount) as total')
    )->where('company_id', $companyId)
      ->where('status', 'paid')
      ->where('paid_at', '>=', $sixMonthsAgo)
      ->groupBy('y', 'm', 'type')
      ->get();

    // Sumarização OS Itens/Peças (Apenas para Receita)
    $osIdsForSixMonths = OrdemServico::where('company_id', $companyId)
      ->whereIn('status', ['paid', 'finalized', 'completed'])
      ->where('updated_at', '>=', $sixMonthsAgo)
      ->pluck('id', 'updated_at');

    // Mapeia OS Itens pela Data da OS
    $osItemsRaw = DB::table('ordem_servico_items')
      ->join('ordem_servicos', 'ordem_servico_items.ordem_servico_id', '=', 'ordem_servicos.id')
      ->whereIn('ordem_servicos.id', $osIdsForSixMonths->keys())
      ->select(DB::raw('MONTH(ordem_servicos.updated_at) as m'), DB::raw('YEAR(ordem_servicos.updated_at) as y'), DB::raw('SUM(price * quantity) as total'))
      ->groupBy('y', 'm')
      ->get();

    $osPartsRaw = DB::table('ordem_servico_parts')
      ->join('ordem_servicos', 'ordem_servico_parts.ordem_servico_id', '=', 'ordem_servicos.id')
      ->whereIn('ordem_servicos.id', $osIdsForSixMonths->keys())
      ->select(DB::raw('MONTH(ordem_servicos.updated_at) as m'), DB::raw('YEAR(ordem_servicos.updated_at) as y'), DB::raw('SUM(price * quantity) as total'))
      ->groupBy('y', 'm')
      ->get();

    // Budgets em Lote
    $budgetsRaw = Budget::select(DB::raw('MONTH(created_at) as m'), DB::raw('YEAR(created_at) as y'), DB::raw('COUNT(*) as total'))
      ->where('company_id', $companyId)
      ->where('created_at', '>=', $sixMonthsAgo)
      ->groupBy('y', 'm')
      ->get();

    for ($i = 5; $i >= 0; $i--) {
      $mDate = $now->copy()->subMonths($i);
      $m = $mDate->month;
      $y = $mDate->year;
      $months[] = $mDate->translatedFormat('M');

      // Busca na coleção puxada do array
      $finIn = $financialRaw->where('m', $m)->where('y', $y)->where('type', 'in')->sum('total');
      $itemsInc = $osItemsRaw->where('m', $m)->where('y', $y)->sum('total');
      $partsInc = $osPartsRaw->where('m', $m)->where('y', $y)->sum('total');

      $revenueTrends[] = $finIn + $itemsInc + $partsInc;

      $expenseTrends[] = $financialRaw->where('m', $m)->where('y', $y)->where('type', 'out')->sum('total');
      $budgetTrends[] = $budgetsRaw->where('m', $m)->where('y', $y)->sum('total');
    }

    // 5. Melhores Serviços (Ranking) - Filtrado por Empresa
    $topServices = OrdemServicoItem::join('services', 'ordem_servico_items.service_id', '=', 'services.id')
      ->join('ordem_servicos', 'ordem_servico_items.ordem_servico_id', '=', 'ordem_servicos.id')
      ->where('ordem_servicos.company_id', $companyId)
      ->select('services.name', DB::raw('SUM(ordem_servico_items.price * ordem_servico_items.quantity) as total'))
      ->groupBy('services.name')
      ->orderBy('total', 'desc')
      ->limit(5)
      ->get();

    $monthlyExpenses = FinancialTransaction::where('company_id', $companyId)
      ->where('type', 'out')
      ->where('status', 'paid')
      ->whereMonth('paid_at', $now->month)
      ->whereYear('paid_at', $now->year)
      ->sum('amount');

    // 6. Novas Métricas de Inteligência
    $totalClients = Clients::count();
    $avgTicket = $osStats['total_month'] > 0 ? $revenueMonth / $osStats['total_month'] : 0;

    // Retenção: Clientes com mais de 1 OS nos últimos 6 meses (Otimizado)
    $retentionCount = DB::table('ordem_servicos')
      ->select('client_id')
      ->where('company_id', $companyId)
      ->where('created_at', '>=', $now->copy()->subMonths(6))
      ->groupBy('client_id')
      ->having(DB::raw('count(*)'), '>', 1)
      ->get()
      ->count();

    $retentionRate = $totalClients > 0 ? ($retentionCount / $totalClients) * 100 : 0;

    // Academy Highlights
    $academyHighlights = [
      ['title' => 'Abrindo sua primeira OS', 'duration' => '1:30', 'icon' => 'tabler-file-plus'],
      ['title' => 'Configurações Iniciais', 'duration' => '2:15', 'icon' => 'tabler-settings'],
      ['title' => 'Gestão Financeira', 'duration' => '3:00', 'icon' => 'tabler-wallet'],
    ];

    return [
      'osStats' => $osStats,
      'revenueMonth' => $revenueMonth,
      'revenueGrowth' => $revenueGrowth,
      'receivablesPending' => $receivablesPending,
      'payablesPending' => $payablesPending,
      'totalClients' => $totalClients,
      'avgTicket' => $avgTicket,
      'retentionRate' => $retentionRate,
      'academyHighlights' => $academyHighlights,
      'lowStockItems' => InventoryItem::where('company_id', $companyId)
        ->when(get_current_niche() === 'food_service', function($q) {
            return $q->where('is_ingredient', true);
        })
        ->whereRaw('quantity <= min_quantity')->count(),
      'pendingBudgets' => Budget::where('company_id', $companyId)->where('status', 'pending')->count(),
      'recentOS' => OrdemServico::where('company_id', $companyId)->with(['client', 'veiculo'])->orderBy('created_at', 'desc')->limit(5)->get(),
      'months' => $months,
      'revenueTrends' => $revenueTrends,
      'expenseTrends' => $expenseTrends,
      'budgetTrends' => $budgetTrends,
      'osDistribution' => [
        'pending' => OrdemServico::where('company_id', $companyId)->where('status', 'pending')->count(),
        'running' => OrdemServico::where('company_id', $companyId)->where('status', 'running')->count(),
        'finalized' => OrdemServico::where('company_id', $companyId)->where('status', 'finalized')->count(),
      ],
      'conversionRate' => Budget::where('company_id', $companyId)->whereMonth('created_at', $now->month)->count() > 0
        ? (Budget::where('company_id', $companyId)->where('status', 'approved')->whereMonth('updated_at', $now->month)->count() / Budget::where('company_id', $companyId)->whereMonth('created_at', $now->month)->count()) * 100
        : 0,
      'topServiceLabels' => $topServices->pluck('name')->map(fn($item) => str($item)->limit(15))->toArray(),
      'topServiceData' => $topServices->pluck('total')->toArray(),
      'monthlyProfitability' => $revenueMonth > 0 ? (($revenueMonth - $monthlyExpenses) / $revenueMonth) * 100 : 0,
      'lastUpdate' => SystemUpdate::latest()->first(),
    ];
  }
}
