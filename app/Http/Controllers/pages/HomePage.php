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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class HomePage extends Controller
{
  public function index()
  {
    $user = Auth::user();
    $companyId = $user->company_id ?? 0;
    
    // Cache de 15 minutos para performance, chave varia por empresa e papel
    $cacheKey = "dashboard_stats_{$companyId}_" . ($user->role !== 'admin' ? "user_{$user->id}" : "admin");

    $data = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($user, $companyId) {
      if ($user && $user->role !== 'admin') {
        return $this->getEmployeeData($user, $companyId);
      }
      return $this->getAdminData($companyId);
    });

    $view = ($user && $user->role !== 'admin') ? 'content.pages.dashboard.dashboards-employee' : 'content.pages.dashboard.dashboards-analytics';
    
    return view($view, array_merge($data, ['user' => $user]));
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

    // 1. OS Stats (Simples)
    $osStats = [
      'pending' => OrdemServico::where('status', 'pending')->count(),
      'running' => OrdemServico::where('status', 'running')->count(),
      'finalized_today' => OrdemServico::where('status', 'finalized')->whereDate('updated_at', $today)->count(),
      'total_month' => OrdemServico::whereMonth('created_at', $now->month)->count(),
    ];

    // 2. Cálculo de Receita OTIMIZADO (Soma no Banco)
    $calculateRevenue = function($month, $year) {
      // Receita de Transações Diretas
      $finRevenue = FinancialTransaction::where('type', 'in')
        ->where('status', 'paid')
        ->whereMonth('paid_at', $month)
        ->whereYear('paid_at', $year)
        ->sum('amount');

      // Receita de Ordens de Serviço (Itens + Peças)
      // Usamos subconsultas para evitar N+1
      $osIds = OrdemServico::whereIn('status', ['paid', 'finalized', 'completed', 'completed'])
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

    // 4. Gráficos e Tendências (Últimos 6 Meses)
    $months = [];
    $revenueTrends = [];
    $expenseTrends = [];
    $budgetTrends = [];

    for ($i = 5; $i >= 0; $i--) {
      $mDate = $now->copy()->subMonths($i);
      $months[] = $mDate->translatedFormat('M');

      $revenueTrends[] = $calculateRevenue($mDate->month, $mDate->year);
      
      $expenseTrends[] = FinancialTransaction::where('type', 'out')
        ->where('status', 'paid')
        ->whereMonth('paid_at', $mDate->month)
        ->whereYear('paid_at', $mDate->year)
        ->sum('amount');

      $budgetTrends[] = Budget::whereMonth('created_at', $mDate->month)
        ->whereYear('created_at', $mDate->year)
        ->count();
    }

    // 5. Melhores Serviços (Ranking)
    $topServices = OrdemServicoItem::join('services', 'ordem_servico_items.service_id', '=', 'services.id')
      ->select('services.name', DB::raw('SUM(ordem_servico_items.price * ordem_servico_items.quantity) as total'))
      ->groupBy('services.name')
      ->orderBy('total', 'desc')
      ->limit(5)
      ->get();

    $monthlyExpenses = FinancialTransaction::where('type', 'out')
      ->where('status', 'paid')
      ->whereMonth('paid_at', $now->month)
      ->whereYear('paid_at', $now->year)
      ->sum('amount');

    // 6. Novas Métricas de Inteligência
    $totalClients = Clients::count();
    $avgTicket = $osStats['total_month'] > 0 ? $revenueMonth / $osStats['total_month'] : 0;
    
    // Retenção: Clientes com mais de 1 OS nos últimos 6 meses
    $retentionCount = OrdemServico::select('client_id')
        ->where('created_at', '>=', $now->copy()->subMonths(6))
        ->groupBy('client_id')
        ->having(DB::raw('count(*)'), '>', 1)
        ->get()
        ->count();
    $retentionRate = $totalClients > 0 ? ($retentionCount / $totalClients) * 100 : 0;

    return [
      'osStats' => $osStats,
      'revenueMonth' => $revenueMonth,
      'revenueGrowth' => $revenueGrowth,
      'receivablesPending' => $receivablesPending,
      'payablesPending' => $payablesPending,
      'totalClients' => $totalClients,
      'avgTicket' => $avgTicket,
      'retentionRate' => $retentionRate,
      'lowStockItems' => InventoryItem::whereRaw('quantity <= min_quantity')->count(),
      'pendingBudgets' => Budget::where('status', 'pending')->count(),
      'recentOS' => OrdemServico::with(['client', 'veiculo'])->orderBy('created_at', 'desc')->limit(5)->get(),
      'months' => $months,
      'revenueTrends' => $revenueTrends,
      'expenseTrends' => $expenseTrends,
      'budgetTrends' => $budgetTrends,
      'osDistribution' => [
        'pending' => OrdemServico::where('status', 'pending')->count(),
        'running' => OrdemServico::where('status', 'running')->count(),
        'finalized' => OrdemServico::where('status', 'finalized')->count(),
      ],
      'conversionRate' => Budget::whereMonth('created_at', $now->month)->count() > 0 
        ? (Budget::where('status', 'approved')->whereMonth('updated_at', $now->month)->count() / Budget::whereMonth('created_at', $now->month)->count()) * 100 
        : 0,
      'topServiceLabels' => $topServices->pluck('name')->map(fn($item) => str($item)->limit(15))->toArray(),
      'topServiceData' => $topServices->pluck('total')->toArray(),
      'monthlyProfitability' => $revenueMonth > 0 ? (($revenueMonth - $monthlyExpenses) / $revenueMonth) * 100 : 0,
    ];
  }
}
