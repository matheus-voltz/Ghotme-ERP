<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\TaxInvoice;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        
        // Busca a empresa do usuário ou a primeira disponível se for admin sem empresa
        $company = Company::find($companyId) ?? Company::first();

        if (!$company) {
            return redirect()->route('dashboard')->with('error', 'Nenhuma empresa cadastrada no sistema. Cadastre uma empresa primeiro.');
        }
        
        // Garante que o ID da empresa usada seja consistente para os filtros
        $companyId = $company->id;
        
        // Filtro de Mês e Ano (Default mês atual)
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        // Vendas Finalizadas (Receitas)
        $orders = OrdemServico::where('company_id', $companyId)
            ->whereIn('status', ['completed', 'finalized'])
            ->whereMonth('updated_at', $month)
            ->whereYear('updated_at', $year)
            ->with(['client', 'items', 'parts'])
            ->get();

        // Notas Fiscais Emitidas
        $invoices = TaxInvoice::where('company_id', $companyId)
            ->whereMonth('issued_at', $month)
            ->whereYear('issued_at', $year)
            ->get();

        // Projeção de impostos (Baseado no faturamento total vs alíquota real da empresa)
        $projectedTax = $orders->sum('total') * ($company->iss_rate / 100);

        $totals = [
            'revenue' => $orders->sum('total'),
            'invoiced' => $invoices->where('status', 'authorized')->sum('total_amount'),
            'taxes' => $invoices->where('status', 'authorized')->sum('tax_amount'),
            'projected_tax' => $projectedTax,
        ];

        return view('content.accounting.index', compact('orders', 'invoices', 'totals', 'month', 'year', 'company'));
    }

    public function exportXml(Request $request)
    {
        // Aqui implementaremos a lógica de ZIP com todos os XMLs do mês
        return back()->with('info', 'Funcionalidade de exportação em lote sendo preparada (Requer integração com API de NFe).');
    }
}
