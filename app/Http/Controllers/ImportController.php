<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\Clients;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    public function index()
    {
        return view('content.pages.settings.import.index');
    }

    /**
     * Gera um CSV de exemplo para o usuário
     */
    public function downloadTemplate($type)
    {
        // Pega os labels dinâmicos do nicho
        $entityLabel = strtolower(niche('entity'));
        $identifierLabel = strtolower(niche('identifier'));
        $brandLabel = strtolower(niche('brand'));
        $modelLabel = strtolower(niche('model'));
        $yearLabel = strtolower(niche('year'));
        $inventoryLabel = strtolower(niche('inventory_items'));

        $headers = [
            'inventory' => ['nome', 'preco_custo', 'preco_venda', 'quantidade', 'estoque_minimo'],
            'clients' => ['nome_ou_razao_social', 'cpf_ou_cnpj', 'email', 'telefone', 'cidade', 'estado'],
            'services' => ['nome', 'descricao', 'preco', 'tempo_estimado_minutos'],
            'vehicles' => [
                $identifierLabel, 
                $brandLabel, 
                $modelLabel, 
                $yearLabel, 
                'cor', 
                'documento_proprietario_cpf_cnpj'
            ],
        ];

        if (!isset($headers[$type])) abort(404);

        $callback = function() use ($headers, $type, $identifierLabel, $brandLabel, $modelLabel) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers[$type]);
            
            if ($type === 'inventory') {
                fputcsv($file, ['Exemplo Item', '50.00', '120.00', '10', '5']);
            } elseif ($type === 'clients') {
                fputcsv($file, ['João Silva Ltda', '00.000.000/0001-00', 'cliente@teste.com', '11999999999', 'São Paulo', 'SP']);
            } elseif ($type === 'services') {
                fputcsv($file, ['Serviço Teste', 'Descrição do serviço aqui', '80.00', '30']);
            } elseif ($type === 'vehicles') {
                // Exemplo dinâmico baseado no nicho
                $exampleIdentifier = ($identifierLabel == 'placa') ? 'ABC1D23' : 'Nome do Exemplo';
                $exampleBrand = ($brandLabel == 'marca' || $brandLabel == 'espécie') ? 'Toyota/Cão' : 'Geral';
                fputcsv($file, [$exampleIdentifier, $exampleBrand, 'Modelo Exemplo', '2022', 'Preto', '000.000.000-00']);
            }
            
            fclose($file);
        };

        return response()->streamDownload($callback, "modelo_{$type}.csv", [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Processa a importação de serviços
     */
    public function importServices(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);
        $data = array_map(function($v) { return str_getcsv($v, ","); }, file($request->file('file')->getRealPath()));
        array_shift($data);
        $companyId = Auth::user()->company_id;
        $count = 0;

        try {
            DB::beginTransaction();
            foreach ($data as $row) {
                if (empty($row[0])) continue;
                \App\Models\Service::create([
                    'company_id' => $companyId,
                    'name' => $row[0],
                    'description' => $row[1] ?? '',
                    'price' => (float) ($row[2] ?? 0),
                    'estimated_time' => (int) ($row[3] ?? 0),
                    'is_active' => true
                ]);
                $count++;
            }
            DB::commit();
            return back()->with('success', "{$count} serviços importados com sucesso.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Erro nos serviços: " . $e->getMessage());
        }
    }

    /**
     * Processa a importação de veículos (vinculando ao cliente pelo documento)
     */
    public function importVehicles(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);
        $data = array_map(function($v) { return str_getcsv($v, ","); }, file($request->file('file')->getRealPath()));
        array_shift($data);
        $companyId = Auth::user()->company_id;
        $count = 0;

        try {
            DB::beginTransaction();
            foreach ($data as $row) {
                if (empty($row[0])) continue;

                // Busca o cliente pelo documento fornecido na planilha
                $doc = preg_replace('/\D/', '', ($row[5] ?? ''));
                $client = Clients::where('company_id', $companyId)
                    ->where(function($q) use ($doc) {
                        $q->where('cpf', $doc)->orWhere('cnpj', $doc);
                    })->first();

                if (!$client) continue; // Pula se não achar o cliente (precisa cadastrar cliente antes)

                \App\Models\Vehicles::create([
                    'company_id' => $companyId,
                    'cliente_id' => $client->id,
                    'placa' => strtoupper($row[0]),
                    'marca' => $row[1] ?? '',
                    'modelo' => $row[2] ?? '',
                    'ano_fabricacao' => $row[3] ?? null,
                    'cor' => $row[4] ?? '',
                    'ativo' => true
                ]);
                $count++;
            }
            DB::commit();
            return back()->with('success', "{$count} veículos vinculados e importados.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Erro nos veículos: " . $e->getMessage());
        }
    }

    /**
     * Processa a importação do estoque
     */
    public function importInventory(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);

        $path = $request->file('file')->getRealPath();
        $data = array_map(function($v) { return str_getcsv($v, ","); }, file($path));
        
        $header = array_shift($data); // Remove o cabeçalho
        $companyId = Auth::user()->company_id;
        $count = 0;

        try {
            DB::beginTransaction();
            foreach ($data as $row) {
                if (count($row) < 4) continue; // Pula linhas incompletas

                InventoryItem::create([
                    'company_id' => $companyId,
                    'name' => $row[0],
                    'cost_price' => (float) str_replace(',', '.', $row[1]),
                    'selling_price' => (float) str_replace(',', '.', $row[2]),
                    'quantity' => (int) $row[3],
                    'min_quantity' => isset($row[4]) ? (int) $row[4] : 0,
                    'is_active' => true
                ]);
                $count++;
            }
            DB::commit();
            return back()->with('success', "Sucesso! {$count} itens foram importados para o seu estoque.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Erro na importação: " . $e->getMessage());
        }
    }

    /**
     * Processa a importação de clientes
     */
    public function importClients(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);

        $path = $request->file('file')->getRealPath();
        $data = array_map(function($v) { return str_getcsv($v, ","); }, file($path));
        
        $header = array_shift($data);
        $companyId = Auth::user()->company_id;
        $count = 0;

        try {
            DB::beginTransaction();
            foreach ($data as $row) {
                if (count($row) < 2) continue;

                $doc = preg_replace('/\D/', '', $row[1]);
                $type = strlen($doc) > 11 ? 'PJ' : 'PF';

                Clients::create([
                    'company_id' => $companyId,
                    'type' => $type,
                    'name' => $type === 'PF' ? $row[0] : null,
                    'company_name' => $type === 'PJ' ? $row[0] : null,
                    'cpf' => $type === 'PF' ? $doc : null,
                    'cnpj' => $type === 'PJ' ? $doc : null,
                    'email' => $row[2] ?? null,
                    'phone' => $row[3] ?? null,
                    'cidade' => $row[4] ?? null,
                    'estado' => $row[5] ?? null,
                ]);
                $count++;
            }
            DB::commit();
            return back()->with('success', "Sucesso! {$count} clientes foram importados.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Erro na importação: " . $e->getMessage());
        }
    }
}
