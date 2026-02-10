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

class HomePage extends Controller
{
  public function index()
  {
    $today = Carbon::today();
    $startOfMonth = Carbon::now()->startOfMonth();

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

    $receivablesPending = FinancialTransaction::where('type', 'in')
      ->where('status', 'pending')
      ->whereDate('due_date', '<=', Carbon::now()->addDays(7))
      ->sum('amount');

    $payablesPending = FinancialTransaction::where('type', 'out')
      ->where('status', 'pending')
      ->whereDate('due_date', '<=', Carbon::now()->addDays(7))
      ->sum('amount');

    // Other Stats
    $totalClients = Clients::count();
    $lowStockItems = InventoryItem::whereRaw('quantity <= min_quantity')->count();
    $pendingBudgets = Budget::where('status', 'pending')->count();

    // Recent OS
    $recentOS = OrdemServico::with(['client', 'veiculo'])
      ->orderBy('created_at', 'desc')
      ->limit(5)
      ->get();

    // Chart Data: Revenue vs Expenses (Last 6 Months)
    $months = [];
    $revenueTrends = [];
    $expenseTrends = [];

    for ($i = 5; $i >= 0; $i--) {
      $monthDate = Carbon::now()->subMonths($i);
      $months[] = $monthDate->translatedFormat('M');

      $finRev = FinancialTransaction::where('type', 'in')
        ->where('status', 'paid')
        ->whereMonth('paid_at', $monthDate->month)
        ->whereYear('paid_at', $monthDate->year)
        ->sum('amount');

      $osRev = OrdemServico::whereIn('status', ['paid', 'finalized', 'completed'])
        ->whereMonth('updated_at', $monthDate->month)
        ->whereYear('updated_at', $monthDate->year)
        ->with(['items', 'parts'])
        ->get()
        ->sum(fn($os) => $os->total);

      $revenueTrends[] = $finRev + $osRev;

      $expenseTrends[] = FinancialTransaction::where('type', 'out')
        ->where('status', 'paid')
        ->whereMonth('paid_at', $monthDate->month)
        ->whereYear('paid_at', $monthDate->year)
        ->sum('amount');
    }

    // OS Distribution Chart
    $osDistribution = [
      'pending' => OrdemServico::where('status', 'pending')->count(),
      'running' => OrdemServico::where('status', 'running')->count(),
      'finalized' => OrdemServico::where('status', 'finalized')->count(),
    ];

    // Budget Conversion Metrics
    $totalBudgetsMonth = Budget::whereMonth('created_at', Carbon::now()->month)->count();
    $approvedBudgetsMonth = Budget::where('status', 'approved')
      ->whereMonth('updated_at', Carbon::now()->month)
      ->count();

    $conversionRate = $totalBudgetsMonth > 0 ? ($approvedBudgetsMonth / $totalBudgetsMonth) * 100 : 0;

    // Budget Trends (Last 6 Months)
    $budgetTrends = [];
    for ($i = 5; $i >= 0; $i--) {
      $monthDate = Carbon::now()->subMonths($i);
      $budgetTrends[] = Budget::whereMonth('created_at', $monthDate->month)
        ->whereYear('created_at', $monthDate->year)
        ->count();
    }

    return view('content.pages.dashboard.dashboards-analytics', compact(
      'osStats',
      'revenueMonth',
      'receivablesPending',
      'payablesPending',
      'totalClients',
      'lowStockItems',
      'pendingBudgets',
      'recentOS',
      'months',
      'revenueTrends',
      'expenseTrends',
      'osDistribution',
      'conversionRate',
      'budgetTrends',
      'totalBudgetsMonth',
      'approvedBudgetsMonth'
    ));
  }
}
