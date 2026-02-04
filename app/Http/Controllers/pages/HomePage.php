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
    $revenueMonth = FinancialTransaction::where('type', 'in')
      ->where('status', 'paid')
      ->whereMonth('paid_at', Carbon::now()->month)
      ->sum('amount');

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

    return view('content.pages.dashboard.dashboards-analytics', compact(
      'osStats', 
      'revenueMonth', 
      'receivablesPending', 
      'payablesPending', 
      'totalClients', 
      'lowStockItems', 
      'pendingBudgets',
      'recentOS'
    ));
  }
}
