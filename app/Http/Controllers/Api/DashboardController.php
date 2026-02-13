<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrdemServico;
use App\Models\FinancialTransaction;
use App\Models\Clients;
use App\Models\InventoryItem;
use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function getStats()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // Base query for company scoping if applicable
        $companyId = $user->company_id;

        if ($user->role !== 'admin') {
            $myRecentOS = OrdemServico::where('user_id', $user->id)
                ->with(['client', 'veiculo'])
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($os) {
                    return [
                        'id' => $os->id,
                        'status' => $os->status,
                        'client_name' => $os->client->name ?? $os->client->company_name ?? 'N/A',
                        'vehicle' => $os->veiculo ? "{$os->veiculo->marca} {$os->veiculo->modelo}" : 'N/A',
                        'plate' => $os->veiculo->placa ?? '',
                        'total' => $os->total,
                        'created_at' => $os->created_at->format('Y-m-d H:i:s'),
                    ];
                });

            return response()->json([
                'role' => $user->role,
                'stats' => [
                    'pendingBudgets' => Budget::where('user_id', $user->id)->where('status', 'pending')->count(),
                    'runningOS' => OrdemServico::where('user_id', $user->id)->where('status', 'running')->count(),
                    'completedToday' => OrdemServico::where('user_id', $user->id)
                        ->where('status', 'finalized')
                        ->whereDate('updated_at', $today)
                        ->count(),
                ],
                'recentOS' => $myRecentOS,
            ]);
        }

        // Admin Stats logic (mirroring HomePage.php but returning JSON)

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

        // Growth calculation
        $lastMonth = Carbon::now()->subMonth();
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

        // Other counts
        $totalClients = Clients::count();
        $lowStockItems = InventoryItem::whereRaw('quantity <= min_quantity')->count();
        $pendingBudgets = Budget::where('status', 'pending')->count();

        // Trends (Last 6 months)
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

        // Distribution
        $osDistribution = [
            'pending' => OrdemServico::where('status', 'pending')->count(),
            'running' => OrdemServico::where('status', 'running')->count(),
            'finalized' => OrdemServico::where('status', 'finalized')->count(),
        ];

        // Profitability
        $monthlyExpenses = FinancialTransaction::where('type', 'out')
            ->where('status', 'paid')
            ->whereMonth('paid_at', Carbon::now()->month)
            ->whereYear('paid_at', Carbon::now()->year)
            ->sum('amount');
        $monthlyProfitability = $revenueMonth > 0 ? (($revenueMonth - $monthlyExpenses) / $revenueMonth) * 100 : 0;

        // Recent OS
        $recentOSData = OrdemServico::with(['client', 'veiculo'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($os) {
                return [
                    'id' => $os->id,
                    'status' => $os->status,
                    'client_name' => $os->client->name ?? $os->client->company_name ?? 'N/A',
                    'vehicle' => $os->veiculo ? "{$os->veiculo->marca} {$os->veiculo->modelo}" : 'N/A',
                    'plate' => $os->veiculo->placa ?? '',
                    'total' => $os->total,
                    'created_at' => $os->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'role' => 'admin',
            'osStats' => $osStats,
            'revenueMonth' => $revenueMonth,
            'revenueGrowth' => $revenueGrowth,
            'totalClients' => $totalClients,
            'lowStockItems' => $lowStockItems,
            'pendingBudgets' => $pendingBudgets,
            'osDistribution' => $osDistribution,
            'monthlyProfitability' => $monthlyProfitability,
            'revenueTrends' => $revenueTrends,
            'expenseTrends' => $expenseTrends,
            'trendMonths' => $months,
            'recentOS' => $recentOSData,
        ]);
    }
}
