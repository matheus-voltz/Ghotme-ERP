<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialReportController extends Controller
{
    public function index()
    {
        // Estatísticas Básicas (Mês Atual)
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $incomeMonth = FinancialTransaction::where('type', 'in')
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $expenseMonth = FinancialTransaction::where('type', 'out')
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $pendingReceivables = FinancialTransaction::where('type', 'in')
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->sum('amount');

        return view('content.finance.reports.index', compact('incomeMonth', 'expenseMonth', 'pendingReceivables'));
    }

    public function getChartData()
    {
        // Dados para gráfico de Barras (Últimos 6 meses)
        $months = [];
        $incomes = [];
        $expenses = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->translatedFormat('M');
            
            $incomes[] = FinancialTransaction::where('type', 'in')
                ->where('status', 'paid')
                ->whereMonth('paid_at', $date->month)
                ->whereYear('paid_at', $date->year)
                ->sum('amount');

            $expenses[] = FinancialTransaction::where('type', 'out')
                ->where('status', 'paid')
                ->whereMonth('paid_at', $date->month)
                ->whereYear('paid_at', $date->year)
                ->sum('amount');
        }

        // Dados para gráfico de Pizza (Categorias)
        $categories = FinancialTransaction::select('category', DB::raw('SUM(amount) as total'))
            ->where('status', 'paid')
            ->where('type', 'in')
            ->groupBy('category')
            ->get();

        return response()->json([
            'months' => $months,
            'incomes' => $incomes,
            'expenses' => $expenses,
            'categories' => $categories
        ]);
    }
}