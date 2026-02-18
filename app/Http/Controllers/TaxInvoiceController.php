<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\TaxInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaxInvoiceController extends Controller
{
    /**
     * Prepara e emite a nota fiscal (Simulação para integração futura)
     */
    public function createFromOS(Request $request)
    {
        $osId = $request->get('os');
        $os = OrdemServico::where('id', $osId)
            ->where('company_id', Auth::user()->company_id)
            ->firstOrFail();

        // 1. Verificar se já existe nota autorizada para essa OS
        $existing = TaxInvoice::where('ordem_servico_id', $os->id)
            ->where('status', 'authorized')
            ->first();

        if ($existing) {
            return back()->with('error', 'Já existe uma Nota Fiscal autorizada para esta OS #' . $os->id);
        }

        // 2. Criar registro de nota fiscal pendente
        $invoice = TaxInvoice::create([
            'company_id' => $os->company_id,
            'ordem_servico_id' => $os->id,
            'invoice_type' => 'nfse', // Padrão serviço p/ oficina
            'status' => 'processing',
            'total_amount' => $os->total,
            'tax_amount' => $os->total * 0.05, // Exemplo 5% ISS (deve vir de config)
            'issued_at' => now(),
        ]);

        // 3. Aqui entraria o código de integração com a API (FocusNFe, PlugNotas, etc)
        // Por agora, vamos simular sucesso para demonstrar o fluxo
        
        $invoice->update([
            'status' => 'authorized',
            'number' => rand(1000, 9999),
            'series' => '1',
        ]);

        return back()->with('success', 'Nota Fiscal emitida com sucesso para a OS #' . $os->id);
    }
}
