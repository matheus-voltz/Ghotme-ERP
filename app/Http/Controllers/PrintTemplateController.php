<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrintTemplate;

use Illuminate\Support\Facades\Auth;

class PrintTemplateController extends Controller
{
    public function index()
    {
        $companyId = Auth::user()->company_id;
        $templates = PrintTemplate::where('company_id', $companyId)->get();
        
        // Se não houver templates para esta empresa, cria o padrão
        if ($templates->isEmpty()) {
            $this->seedDefaults($companyId);
            $templates = PrintTemplate::where('company_id', $companyId)->get();
        }

        return view('content.settings.print-templates.index', compact('templates'));
    }

    public function edit($id)
    {
        $template = PrintTemplate::where('company_id', Auth::user()->company_id)->findOrFail($id);
        return view('content.settings.print-templates.edit', compact('template'));
    }

    public function update(Request $request, $id)
    {
        $template = PrintTemplate::where('company_id', Auth::user()->company_id)->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'css' => 'nullable|string',
        ]);

        $template->update($validated);

        return response()->json(['success' => true, 'message' => 'Modelo de impressão atualizado!']);
    }

    private function seedDefaults($companyId)
    {
        $entityLabel = niche('entity');
        $workshopLabel = (get_current_niche() === 'construction') ? 'Canteiro' : 'Empresa';

        PrintTemplate::create([
            'company_id' => $companyId,
            'name' => 'Ordem de Serviço Padrão',
            'slug' => 'os',
            'content' => '
<div class="print-container">
    <header class="header">
        <div class="company-logo">
            <img src="{{logo_url}}" alt="Logo">
        </div>
        <div class="company-info">
            <h1>{{company_name}}</h1>
            <p>{{company_cnpj}} | {{company_address}}, {{company_number}}</p>
            <p>{{company_city}} - {{company_state}} | {{company_phone}}</p>
        </div>
        <div class="os-info">
            <h2>OS #{{os_number}}</h2>
            <p>Data: {{os_date}}</p>
        </div>
    </header>

    <section class="section">
        <h3>Dados do Cliente</h3>
        <div class="grid">
            <div><strong>Nome:</strong> {{client_name}}</div>
            <div><strong>Documento:</strong> {{client_document}}</div>
            <div><strong>Fone:</strong> {{client_phone}}</div>
        </div>
    </section>

    <section class="section">
        <h3>' . $entityLabel . '</h3>
        <div class="grid">
            <div><strong>Modelo:</strong> {{vehicle_model}} ({{vehicle_brand}})</div>
            <div><strong>Identificador:</strong> {{vehicle_plate}}</div>
            <div><strong>Métrica:</strong> {{vehicle_km}}</div>
        </div>
    </section>

    <section class="section">
        <h3>Serviços e Itens</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Qtd</th>
                    <th>V. Unit</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                {{items_loop}}
                <tr>
                    <td>{{item_name}}</td>
                    <td>{{item_qty}}</td>
                    <td>R$ {{item_price}}</td>
                    <td>R$ {{item_total}}</td>
                </tr>
                {{/items_loop}}
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" align="right"><strong>TOTAL GERAL:</strong></td>
                    <td><strong>R$ {{os_total}}</strong></td>
                </tr>
            </tfoot>
        </table>
    </section>

    <footer class="footer">
        <div class="terms">
            <h4>Termos e Garantia</h4>
            <p>{{os_terms}}</p>
        </div>
        <div class="signatures">
            <div class="signature">___________________________<br>Assinatura ' . $workshopLabel . '</div>
            <div class="signature">___________________________<br>Assinatura Cliente</div>
        </div>
    </footer>
</div>',
            'css' => '
.print-container { font-family: sans-serif; color: #333; }
.header { display: flex; justify-content: space-between; border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
.company-info h1 { margin: 0; font-size: 24px; }
.section { margin-bottom: 30px; }
.section h3 { background: #f8f9fa; padding: 8px; border-radius: 4px; font-size: 16px; margin-bottom: 10px; }
.grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; }
.table { width: 100%; border-collapse: collapse; }
.table th, .table td { border: 1px solid #eee; padding: 10px; text-align: left; }
.table th { background: #f8f9fa; }
.footer { margin-top: 50px; }
.signatures { display: flex; justify-content: space-between; margin-top: 60px; text-align: center; }
.signature { width: 45%; }'
        ]);
    }
}