<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\TaxInvoice;
use App\Models\Company;
use App\Models\FinancialTransaction;
use App\Services\OfxParserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountingController extends Controller
{
    protected $ofxService;

    public function __construct(OfxParserService $ofxService)
    {
        $this->ofxService = $ofxService;
    }

    public function index(Request $request, $token = null)
    {
        // Se houver token, busca a empresa pelo token (acesso direto do contador)
        if ($token) {
            $company = Company::where('accountant_token', $token)->firstOrFail();
        } else {
            // Caso contrário, exige login
            $user = Auth::user();
            if (!$user) return redirect()->route('login');
            $companyId = $user->company_id;
            $company = Company::find($companyId) ?? Company::first();
        }

        if (!$company) {
            return redirect()->route('dashboard')->with('error', 'Nenhuma empresa cadastrada.');
        }

        // Filtro por Intervalo de Datas
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        
        // Receitas (OS finalizadas no período)
        $revenue = OrdemServico::where('company_id', $company->id)
            ->whereIn('status', ['completed', 'finalized', 'paid'])
            ->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with(['client'])
            ->get();

        // Despesas (Lançamentos no período)
        $expenses = FinancialTransaction::where('company_id', $company->id)
            ->where('type', 'out')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->get();

        // Lógica para o DRE (Agrupamento por Categoria)
        $dreData = $expenses->groupBy('category')->map(function($items) {
            return $items->sum('amount');
        });

        // Notas Fiscais emitidas no período
        $invoices = TaxInvoice::where('company_id', $company->id)
            ->whereBetween('issued_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get();

        $totals = [
            'revenue' => $revenue->sum(fn($os) => $os->total),
            'expenses' => $expenses->sum('amount'),
            'net_profit' => $revenue->sum(fn($os) => $os->total) - $expenses->sum('amount'),
            'audited_count' => $expenses->where('audit_status', 'audited')->count(),
            'pending_audit' => $expenses->where('audit_status', 'pending')->count(),
        ];

        $isPublic = (bool) $token;

        return view('content.accounting.index', [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'invoices' => $invoices,
            'totals' => $totals,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'company' => $company,
            'dreData' => $dreData,
            'isPublic' => $isPublic,
            'isMenu' => !$isPublic,
            'isNavbar' => true
        ]);
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

    public function updateTaxRegime(Request $request, $token)
    {
        $company = Company::where('accountant_token', $token)->firstOrFail();
        
        $request->validate([
            'tax_regime' => 'required|string'
        ]);

        $company->update([
            'tax_regime' => $request->tax_regime
        ]);

        return response()->json(['success' => true, 'message' => 'Regime tributário atualizado com sucesso!']);
    }

    public function exportXml(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));
        $token = $request->get('token');
        
        if ($token) {
            $company = Company::where('accountant_token', $token)->firstOrFail();
        } else {
            $company = Company::find(Auth::user()->company_id);
        }

        $invoices = TaxInvoice::where('company_id', $company->id)
            ->whereMonth('issued_at', $month)
            ->whereYear('issued_at', $year)
            ->whereNotNull('xml_url')
            ->get();

        if ($invoices->isEmpty()) {
            return back()->with('error', 'Nenhum XML encontrado para este período.');
        }

        $zipFileName = "Notas_Fiscais_{$month}_{$year}.zip";
        $zipPath = storage_path("app/public/{$zipFileName}");
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($invoices as $invoice) {
                // Em um sistema real, leríamos o arquivo de $invoice->xml_url
                // Para este teste, vamos criar um XML fictício no ZIP
                $content = "<?xml version='1.0' encoding='UTF-8'?><nfe><infNFe><total><vNF>{$invoice->total_amount}</vNF></total></infNFe></nfe>";
                $zip->addFromString("Nota_{$invoice->invoice_number}.xml", $content);
            }
            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));
        $token = $request->get('token');
        
        if ($token) {
            $company = Company::where('accountant_token', $token)->firstOrFail();
        } else {
            $company = Company::find(Auth::user()->company_id);
        }

        $invoices = TaxInvoice::where('company_id', $company->id)
            ->whereMonth('issued_at', $month)
            ->whereYear('issued_at', $year)
            ->get();

        if ($invoices->isEmpty()) {
            return back()->with('error', 'Nenhum PDF encontrado para este período.');
        }

        $zipFileName = "Recibos_PDF_{$month}_{$year}.zip";
        $zipPath = storage_path("app/public/{$zipFileName}");
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($invoices as $invoice) {
                $zip->addFromString("Recibo_{$invoice->invoice_number}.txt", "Simulação de PDF para a nota {$invoice->invoice_number}");
            }
            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
