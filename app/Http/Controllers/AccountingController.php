<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\TaxInvoice;
use App\Models\Company;
use App\Models\FinancialTransaction;
use App\Services\OfxParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountingController extends Controller
{
    protected $ofxService;

    public function __construct(OfxParserService $ofxService)
    {
        $this->ofxService = $ofxService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $company = Company::find($companyId) ?? Company::first();

        if (!$company) {
            return redirect()->route('dashboard')->with('error', 'Nenhuma empresa cadastrada.');
        }

        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));
        
        // Receitas e Despesas do Mês
        $revenue = OrdemServico::where('company_id', $company->id)
            ->whereIn('status', ['completed', 'finalized'])
            ->whereMonth('updated_at', $month)
            ->whereYear('updated_at', $year)
            ->with(['client'])
            ->get();

        $expenses = FinancialTransaction::where('company_id', $company->id)
            ->where('type', 'expense')
            ->whereMonth('due_date', $month)
            ->whereYear('due_date', $year)
            ->get();

        $invoices = TaxInvoice::where('company_id', $company->id)
            ->whereMonth('issued_at', $month)
            ->whereYear('issued_at', $year)
            ->get();

        $totals = [
            'revenue' => $revenue->sum('total'),
            'expenses' => $expenses->sum('amount'),
            'net_profit' => $revenue->sum('total') - $expenses->sum('amount'),
            'audited_count' => $expenses->where('audit_status', 'audited')->count(),
            'pending_audit' => $expenses->where('audit_status', 'pending')->count(),
        ];

        return view('content.accounting.index', compact('revenue', 'expenses', 'invoices', 'totals', 'month', 'year', 'company'));
    }

    public function importOfx(Request $request)
    {
        $request->validate(['ofx_file' => 'required|file']);

        $path = $request->file('ofx_file')->getRealPath();
        $ofxTransactions = $this->ofxService->parse($path);

        if (empty($ofxTransactions)) {
            return back()->with('error', 'Erro ao ler o arquivo OFX. Verifique se o formato é válido.');
        }

        // Passar os dados para uma tela específica de conciliação
        return view('content.accounting.reconcile', compact('ofxTransactions'));
    }

    public function conciliate(Request $request)
    {
        $request->validate([
            'bank_id' => 'required',
            'action' => 'required|in:match,create',
            'transaction_id' => 'required_if:action,match',
            'amount' => 'required|numeric',
            'type' => 'required',
            'date' => 'required|date',
            'description' => 'required|string',
        ]);

        $companyId = Auth::user()->company_id;

        if ($request->action === 'match') {
            $transaction = FinancialTransaction::where('id', $request->transaction_id)
                ->where('company_id', $companyId)
                ->firstOrFail();

            $transaction->update([
                'bank_transaction_id' => $request->bank_id,
                'status' => 'paid',
                'paid_at' => $request->date,
            ]);
        } else {
            // Criar novo lançamento direto do banco
            FinancialTransaction::create([
                'company_id' => $companyId,
                'description' => $request->description,
                'amount' => $request->amount,
                'type' => $request->type,
                'status' => 'paid',
                'due_date' => $request->date,
                'paid_at' => $request->date,
                'bank_transaction_id' => $request->bank_id,
                'user_id' => Auth::id(),
                'category' => 'Conciliação Bancária'
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function auditTransaction(Request $request, $id)
    {
        $transaction = FinancialTransaction::findOrFail($id);
        
        if ($transaction->company_id !== Auth::user()->company_id && !Auth::user()->is_admin) {
             return response()->json(['error' => 'Unauthorized'], 403);
        }

        $transaction->update([
            'audit_status' => $request->status,
            'accountant_notes' => $request->notes,
            'audited_at' => now(),
        ]);

        return back()->with('success', 'Auditoria realizada com sucesso!');
    }

    public function exportXml(Request $request)
    {
        return back()->with('info', 'Lógica de exportação de XML em lote em desenvolvimento.');
    }
}
