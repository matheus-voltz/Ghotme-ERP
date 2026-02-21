<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\TaxInvoice;
use App\Services\FiscalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaxInvoiceController extends Controller
{
    protected $fiscalService;

    public function __construct(FiscalService $fiscalService)
    {
        $this->fiscalService = $fiscalService;
    }

    /**
     * Prepara e emite a nota fiscal via API
     */
    public function createFromOS(Request $request)
    {
        $osId = $request->get('os');
        $os = OrdemServico::with(['client', 'items.service', 'parts.part'])->where('id', $osId)
            ->where('company_id', Auth::user()->company_id)
            ->firstOrFail();

        // 1. Verificar se já existe nota autorizada
        $existing = TaxInvoice::where('ordem_servico_id', $os->id)
            ->where('status', 'issued')
            ->first();

        if ($existing) {
            return back()->with('error', 'Já existe uma Nota Fiscal para esta OS #' . $os->id);
        }

        try {
            // 2. Transmitir via Serviço Fiscal
            $invoice = $this->fiscalService->transmitFromOS($os);

            return back()->with('success', "Nota Fiscal #{$invoice->invoice_number} emitida com sucesso!");
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao emitir nota: ' . $e->getMessage());
        }
    }
}
