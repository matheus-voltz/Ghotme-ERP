<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinancialTransaction;
use App\Models\PaymentMethod;
use App\Models\Clients;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;

class FinanceController extends Controller
{
    public function receivables()
    {
        $clients = Clients::all();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        return view('content.finance.receivables.index', compact('clients', 'paymentMethods'));
    }

    public function payables()
    {
        $suppliers = Supplier::all();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        return view('content.finance.payables.index', compact('suppliers', 'paymentMethods'));
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
            'client_id' => 'nullable|exists:clients,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'category' => 'nullable|string',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
        ]);

        $validated['user_id'] = Auth::id();
        $transaction = FinancialTransaction::create($validated);

        return response()->json(['success' => true, 'message' => 'Lançamento realizado!', 'data' => $transaction]);
    }

    public function markAsPaid($id)
    {
        $transaction = FinancialTransaction::findOrFail($id);
        $transaction->update([
            'status' => 'paid',
            'paid_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Pagamento confirmado!']);
    }

    public function destroy($id)
    {
        $transaction = FinancialTransaction::findOrFail($id);
        $transaction->delete();
        return response()->json(['success' => true, 'message' => 'Lançamento removido!']);
    }

    public function cashFlow()
    {
        $incomes = FinancialTransaction::where('type', 'in')->where('status', 'paid')->sum('amount');
        $expenses = FinancialTransaction::where('type', 'out')->where('status', 'paid')->sum('amount');
        $balance = $incomes - $expenses;

        $recentTransactions = FinancialTransaction::with(['client', 'supplier'])
            ->where('status', 'paid')
            ->orderBy('paid_at', 'desc')
            ->limit(10)
            ->get();

        return view('content.finance.cash-flow.index', compact('incomes', 'expenses', 'balance', 'recentTransactions'));
    }
}