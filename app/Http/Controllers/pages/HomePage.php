<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrdemServico;
use App\Models\FinancialTransaction;
use App\Models\Clients;
use App\Models\InventoryItem;
use App\Models\Budget;
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
    $cacheKey = "dashboard_stats_{$companyId}_" . ($user->role !== 'admin' ? "user_{$user->id}" : "admin");

    $data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
      if ($user && $user->role !== 'admin') {
        return $this->getEmployeeData($user);
      }
      return $this->getAdminData();
    });

    $view = ($user && $user->role !== 'admin') ? 'content.pages.dashboard.dashboards-employee' : 'content.pages.dashboard.dashboards-analytics';
    
    return view($view, array_merge($data, ['user' => $user]));
  }

  protected function getEmployeeData($user)
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

  protected function getAdminData()
  {
    $today = Carbon::today();
    $startOfMonth = Carbon::now()->startOfMonth();
    $lastMonth = Carbon::now()->subMonth();

    // OS Stats
    $osStats = [
      'pending' => OrdemServico::where('status', 'pending')->count(),
      'running' => OrdemServico::where('status', 'running')->count(),
      'finalized_today' => OrdemServico::where('status', 'finalized')->whereDate('updated_at', $today)->count(),
      'total_month' => OrdemServico::whereMonth('created_at', Carbon::now()->month)->count(),
    ];

    // Finance Stats
    $financialRevenue = FinancialTransaction::where('type', 'in')
      ->where('status', 'paid')
      ->whereMonth('paid_at', Carbon::now()->month)
      ->whereYear('paid_at', Carbon::now()->year)
      ->sum('amount');

    $osRevenue = OrdemServico::whereIn('status', ['paid', 'finalized', 'completed'])
      ->whereMonth('updated_at', Carbon::now()->month)
      ->whereYear('updated_at', Carbon::now()->year)
      ->with(['items', 'parts'])
      ->get()
      ->sum(fn($os) => $os->total);

    $revenueMonth = $financialRevenue + $osRevenue;

    $financialRevenueLast = FinancialTransaction::where('type', 'in')
      ->where('status', 'paid')
      ->whereMonth('paid_at', $lastMonth->month)
      ->whereYear('paid_at', $lastMonth->year)
      ->sum('amount');

    $osRevenueLast = OrdemServico::whereIn('status', ['paid', 'finalized', 'completed'])
      ->whereMonth('updated_at', $lastMonth->month)
      ->whereYear('updated_at', $lastMonth->year)
      ->with(['items', 'parts'])
      ->get()
      ->sum(fn($os) => $os->total);

    $revenueLastMonth = $financialRevenueLast + $osRevenueLast;
    $revenueGrowth = $revenueLastMonth > 0 ? (($revenueMonth - $revenueLastMonth) / $revenueLastMonth) * 100 : ($revenueMonth > 0 ? 100 : 0);

    $receivablesPending = FinancialTransaction::where('type', 'in')
      ->where('status', 'pending')
      ->whereDate('due_date', '<=', Carbon::now()->addDays(7))
      ->sum('amount');

    $payablesPending = FinancialTransaction::where('type', 'out')
      ->where('status', 'pending')
      ->whereDate('due_date', '<=', Carbon::now()->addDays(7))
      ->sum('amount');

    // Chart Data & Trends
    $months = [];
    $revenueTrends = [];
    $expenseTrends = [];
    $budgetTrends = [];

    for ($i = 5; $i >= 0; $i--) {
      $monthDate = Carbon::now()->subMonths($i);
      $months[] = $monthDate->translatedFormat('M');

      $finRev = FinancialTransaction::where('type', 'in')->where('status', 'paid')->whereMonth('paid_at', $monthDate->month)->whereYear('paid_at', $monthDate->year)->sum('amount');
      $osRev = OrdemServico::whereIn('status', ['paid', 'finalized', 'completed'])->whereMonth('updated_at', $monthDate->month)->whereYear('updated_at', $monthDate->year)->with(['items', 'parts'])->get()->sum(fn($os) => $os->total);
      
      $revenueTrends[] = $finRev + $osRev;
      $expenseTrends[] = FinancialTransaction::where('type', 'out')->where('status', 'paid')->whereMonth('paid_at', $monthDate->month)->whereYear('paid_at', $monthDate->year)->sum('amount');
      $budgetTrends[] = Budget::whereMonth('created_at', $monthDate->month)->whereYear('created_at', $monthDate->year)->count();
    }

    $topServices = \App\Models\OrdemServicoItem::join('services', 'ordem_servico_items.service_id', '=', 'services.id')
      ->select('services.name as description', DB::raw('SUM(ordem_servico_items.price * ordem_servico_items.quantity) as total_revenue'))
      ->groupBy('services.name')->orderBy('total_revenue', 'desc')->limit(5)->get();

    $monthlyExpenses = FinancialTransaction::where('type', 'out')->where('status', 'paid')->whereMonth('paid_at', Carbon::now()->month)->whereYear('paid_at', Carbon::now()->year)->sum('amount');

    return [
      'osStats' => $osStats,
      'revenueMonth' => $revenueMonth,
      'receivablesPending' => $receivablesPending,
      'payablesPending' => $payablesPending,
      'totalClients' => Clients::count(),
      'lowStockItems' => InventoryItem::whereRaw('quantity <= min_quantity')->count(),
      'pendingBudgets' => Budget::where('status', 'pending')->count(),
      'recentOS' => OrdemServico::with(['client', 'veiculo'])->orderBy('created_at', 'desc')->limit(5)->get(),
      'months' => $months,
      'revenueTrends' => $revenueTrends,
      'expenseTrends' => $expenseTrends,
      'osDistribution' => [
        'pending' => OrdemServico::where('status', 'pending')->count(),
        'running' => OrdemServico::where('status', 'running')->count(),
        'finalized' => OrdemServico::where('status', 'finalized')->count(),
      ],
      'conversionRate' => Budget::whereMonth('created_at', Carbon::now()->month)->count() > 0 ? (Budget::where('status', 'approved')->whereMonth('updated_at', Carbon::now()->month)->count() / Budget::whereMonth('created_at', Carbon::now()->month)->count()) * 100 : 0,
      'budgetTrends' => $budgetTrends,
      'totalBudgetsMonth' => Budget::whereMonth('created_at', Carbon::now()->month)->count(),
      'approvedBudgetsMonth' => Budget::where('status', 'approved')->whereMonth('updated_at', Carbon::now()->month)->count(),
      'topServiceLabels' => $topServices->pluck('description')->map(fn($item) => str($item)->limit(20))->toArray(),
      'topServiceData' => $topServices->pluck('total_revenue')->toArray(),
      'revenueGrowth' => $revenueGrowth,
      'monthlyProfitability' => $revenueMonth > 0 ? (($revenueMonth - $monthlyExpenses) / $revenueMonth) * 100 : 0,
    ];
  }
}
