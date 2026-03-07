<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinancialTransaction;
use App\Models\PaymentMethod;
use App\Models\Clients;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FinanceController extends Controller
{
    public function receivables()
    {
        $clients = Clients::all();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        
        $stats = [
            'pending' => FinancialTransaction::where('type', 'in')->where('status', 'pending')->sum('amount'),
            'overdue' => FinancialTransaction::where('type', 'in')->where('status', 'pending')->where('due_date', '<', Carbon::today())->sum('amount'),
            'paid_today' => FinancialTransaction::where('type', 'in')->where('status', 'paid')->whereDate('paid_at', Carbon::today())->sum('amount'),
            'monthly_total' => FinancialTransaction::where('type', 'in')->whereMonth('due_date', Carbon::now()->month)->whereYear('due_date', Carbon::now()->year)->sum('amount'),
        ];
        
        return view('content.finance.receivables.index', compact('clients', 'paymentMethods', 'stats'));
    }

    public function payables()
    {
        $suppliers = Supplier::all();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        
        $stats = [
            'pending' => FinancialTransaction::where('type', 'out')->where('status', 'pending')->sum('amount'),
            'overdue' => FinancialTransaction::where('type', 'out')->where('status', 'pending')->where('due_date', '<', Carbon::today())->sum('amount'),
            'paid_today' => FinancialTransaction::where('type', 'out')->where('status', 'paid')->whereDate('paid_at', Carbon::today())->sum('amount'),
            'monthly_total' => FinancialTransaction::where('type', 'out')->whereMonth('due_date', Carbon::now()->month)->whereYear('due_date', Carbon::now()->year)->sum('amount'),
        ];
        
        return view('content.finance.payables.index', compact('suppliers', 'paymentMethods', 'stats'));
    }

    public function dataBase(Request $request)
    {
        $type = $request->input('type'); // in or out
        $query = FinancialTransaction::where('type', $type)->with(['client', 'supplier', 'paymentMethod']);

        $totalData = $query->count();
        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        
        $items = $query->offset($start)
            ->limit($limit)
            ->orderBy('due_date', 'asc')
            ->get();

        $data = [];
        $ids = $start;

        foreach ($items as $item) {
            $nestedData['fake_id'] = ++$ids;
            $nestedData['id'] = $item->id;
            $nestedData['description'] = $item->description;
            $nestedData['amount'] = $item->amount;
            $nestedData['due_date'] = $item->due_date->format('Y-m-d');
            $nestedData['status'] = $item->status;
            $nestedData['entity_name'] = $item->type === 'in' 
                ? ($item->client ? ($item->client->name ?? $item->client->company_name) : '-') 
                : ($item->supplier ? $item->supplier->name : '-');
            
            $data[] = $nestedData;
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => intval($totalData),
            'recordsFiltered' => intval($totalFiltered),
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:in,out',
            'due_date' => 'required|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'client_id' => 'nullable|exists:clients,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'category' => 'nullable|string',
        ]);

        $validated['status'] = 'pending';
        
        FinancialTransaction::create($validated);

        return response()->json(['success' => true]);
    }

    public function markAsPaid($id)
    {
        $transaction = FinancialTransaction::findOrFail($id);
        $transaction->update([
            'status' => 'paid',
            'paid_at' => now()
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $transaction = FinancialTransaction::findOrFail($id);
        $transaction->delete();

        return response()->json(['success' => true]);
    }

    public function cashFlow()
    {
        $incomes = FinancialTransaction::where('type', 'in')->where('status', 'paid')->sum('amount');
        $expenses = FinancialTransaction::where('type', 'out')->where('status', 'paid')->sum('amount');
        $balance = $incomes - $expenses;
        
        $recentTransactions = FinancialTransaction::where('status', 'paid')
            ->with(['client', 'supplier'])
            ->orderBy('paid_at', 'desc')
            ->limit(10)
            ->get();

        return view('content.finance.cash-flow.index', compact('incomes', 'expenses', 'balance', 'recentTransactions'));
    }
}
