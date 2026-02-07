<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Clients;
use App\Models\Vehicles;
use App\Models\VehicleHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Rules\Cpf;
use App\Rules\Cnpj;

class ClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('content.pages.clients.clients-index');
    }

    /**
     * Return data for DataTables.
     */
    public function dataBase(Request $request)
    {
        $totalData = Clients::count();
        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');

        $columns = [
            0 => 'id',
            1 => 'id',
            2 => 'type',
            3 => 'name',
            4 => 'cpf',
            5 => 'email',
            6 => 'id',
            7 => 'id'
        ];
        $orderColumnIndex = $request->input('order.0.column');
        $order = $columns[$orderColumnIndex] ?? 'id';
        $dir = $request->input('order.0.dir') ?? 'desc';

        $query = Clients::withCount('vehicles');

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('company_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('cpf', 'LIKE', "%{$search}%")
                    ->orWhere('cnpj', 'LIKE', "%{$search}%");
            });
            $totalFiltered = $query->count();
        }

        $clients = $query->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();

        $data = [];
        $ids = $start;

        foreach ($clients as $client) {
            $nestedData['fake_id'] = ++$ids;
            $nestedData['id'] = $client->id;
            $nestedData['type'] = $client->type;
            $nestedData['name'] = $client->type === 'PF' ? $client->name : $client->company_name;
            $nestedData['email'] = $client->email;
            $nestedData['company_name'] = $client->company_name;
            $nestedData['whatsapp'] = $client->whatsapp;
            $nestedData['document'] = $client->type === 'PF' ? $client->cpf : $client->cnpj;
            $nestedData['vehicles_count'] = $client->vehicles_count;
            $nestedData['uuid'] = $client->uuid;
            $nestedData['is_active'] = true;
            $nestedData['action'] = '';

            $data[] = $nestedData;
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => intval($totalData),
            'recordsFiltered' => intval($totalFiltered),
            'data' => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Limpar CPF/CNPJ para validação e persistência (remover pontos e traços)
        if ($request->filled('cpf')) {
            $request->merge(['cpf' => preg_replace('/\D/', '', $request->cpf)]);
        }
        if ($request->filled('cnpj')) {
            $request->merge(['cnpj' => preg_replace('/\D/', '', $request->cnpj)]);
        }

        $validated = $request->validate([
            'type' => 'required|in:PF,PJ',
            'name' => 'required_if:type,PF|nullable|string|max:255',
            'cpf' => [
                'required_if:type,PF',
                'nullable',
                'digits:11',
                new Cpf,
                Rule::unique('clients', 'cpf')->where('company_id', Auth::user()->company_id)
            ],
            'company_name' => 'required_if:type,PJ|nullable|string|max:255',
            'cnpj' => [
                'required_if:type,PJ',
                'nullable',
                'digits:14',
                new Cnpj,
                Rule::unique('clients', 'cnpj')->where('company_id', Auth::user()->company_id)
            ],
            'email' => 'nullable|email|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:10',
            'rua' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:2',
            // Veículo
            'veiculo_placa' => 'nullable|string|max:10|unique:veiculos,placa',
            'veiculo_marca' => 'required_with:veiculo_placa|nullable|string|max:50',
            'veiculo_modelo' => 'required_with:veiculo_placa|nullable|string|max:80',
        ], [
            'name.required_if' => 'O nome é obrigatório para pessoa física.',
            'cpf.required_if' => 'O CPF é obrigatório para pessoa física.',
            'cpf.digits' => 'O CPF deve ter exatamente 11 números.',
            'cpf.unique' => 'Este CPF já está cadastrado para outro cliente nesta empresa.',
            'company_name.required_if' => 'A razão social é obrigatória para pessoa jurídica.',
            'cnpj.required_if' => 'O CNPJ é obrigatório para pessoa jurídica.',
            'cnpj.digits' => 'O CNPJ deve ter exatamente 14 números.',
            'cnpj.unique' => 'Este CNPJ já está cadastrado para outro cliente nesta empresa.',
            'email.email' => 'Informe um e-mail válido.',
            'veiculo_marca.required_with' => 'A marca é obrigatória ao informar uma placa.',
            'veiculo_modelo.required_with' => 'O modelo é obrigatório ao informar uma placa.',
        ], [
            'name' => 'Nome',
            'cpf' => 'CPF',
            'company_name' => 'Razão Social',
            'cnpj' => 'CNPJ',
            'veiculo_placa' => 'Placa do Veículo',
            'veiculo_marca' => 'Marca do Veículo',
            'veiculo_modelo' => 'Modelo do Veículo',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            // Cria o cliente (o company_id é injetado automaticamente pela Trait se o user estiver logado)
            $client = Clients::create($validated);

            if ($request->filled('veiculo_placa')) {
                $vehicle = Vehicles::create([
                    'company_id' => Auth::user()->company_id,
                    'cliente_id' => $client->id,
                    'placa' => strtoupper($request->veiculo_placa),
                    'marca' => $request->veiculo_marca,
                    'modelo' => $request->veiculo_modelo,
                ]);

                // Adicionar evento na linha do tempo
                VehicleHistory::create([
                    'veiculo_id' => $vehicle->id,
                    'date' => now(),
                    'km' => 0,
                    'event_type' => 'entrada_oficina',
                    'title' => 'Entrada na Oficina',
                    'description' => 'Veículo cadastrado junto com o cliente e disponível para ordens de serviço.',
                    'performer' => Auth::user()->name,
                    'created_by' => Auth::id()
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Cliente e Veículo cadastrados com sucesso!']);
        });
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $client = Clients::with('vehicles')->findOrFail($id);
        return response()->json($client);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $client = Clients::findOrFail($id);

        // Limpar CPF/CNPJ para validação e persistência
        if ($request->filled('cpf')) {
            $request->merge(['cpf' => preg_replace('/\D/', '', $request->cpf)]);
        }
        if ($request->filled('cnpj')) {
            $request->merge(['cnpj' => preg_replace('/\D/', '', $request->cnpj)]);
        }

        $validated = $request->validate([
            'type' => 'required|in:PF,PJ',
            'name' => 'required_if:type,PF|nullable|string|max:255',
            'cpf' => [
                'required_if:type,PF',
                'nullable',
                'digits:11',
                new Cpf,
                Rule::unique('clients', 'cpf')->ignore($id)->where('company_id', Auth::user()->company_id)
            ],
            'company_name' => 'required_if:type,PJ|nullable|string|max:255',
            'cnpj' => [
                'required_if:type,PJ',
                'nullable',
                'digits:14',
                new Cnpj,
                Rule::unique('clients', 'cnpj')->ignore($id)->where('company_id', Auth::user()->company_id)
            ],
            'email' => 'nullable|email|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:10',
            'rua' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:2',
        ], [
            'name.required_if' => 'O nome é obrigatório para pessoa física.',
            'cpf.required_if' => 'O CPF é obrigatório para pessoa física.',
            'cpf.digits' => 'O CPF deve ter exatamente 11 números.',
            'cpf.unique' => 'Este CPF já está cadastrado para outro cliente.',
            'company_name.required_if' => 'A razão social é obrigatória para pessoa jurídica.',
            'cnpj.required_if' => 'O CNPJ é obrigatório para pessoa jurídica.',
            'cnpj.digits' => 'O CNPJ deve ter exatamente 14 números.',
            'cnpj.unique' => 'Este CNPJ já está cadastrado para outro cliente.',
            'email.email' => 'Informe um e-mail válido.',
        ]);

        $client->update($validated);

        return response()->json(['success' => true, 'message' => 'Cliente atualizado com sucesso!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $client = Clients::findOrFail($id);
        $client->delete();
        return response()->json(['success' => true, 'message' => 'Cliente removido!']);
    }

    public function quickView($id)
    {
        $client = Clients::with('vehicles')->findOrFail($id);

        $html = '<div class="list-group list-group-flush mb-4">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>Tipo:</strong></span>
                        <span>' . ($client->type == 'PF' ? 'Pessoa Física' : 'Pessoa Jurídica') . '</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>Documento:</strong></span>
                        <span>' . ($client->type == 'PF' ? $client->cpf : $client->cnpj) . '</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>E-mail:</strong></span>
                        <span>' . $client->email . '</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>WhatsApp:</strong></span>
                        <span class="text-success"><i class="ti tabler-brand-whatsapp"></i> ' . $client->whatsapp . '</span>
                    </div>';

        if ($client->rua) {
            $html .= '<div class="list-group-item">
                        <strong>Endereço:</strong><br>
                        ' . $client->rua . ', ' . $client->numero . ' - ' . $client->bairro . '<br>
                        ' . $client->cidade . '/' . $client->estado . '
                    </div>';
        }

        $html .= '</div>';

        // Seção de Veículos
        $html .= '<h6 class="px-3 mb-2 mt-4"><i class="ti tabler-car me-1"></i> Veículos Cadastrados</h6>';
        if ($client->vehicles->count() > 0) {
            $html .= '<div class="table-responsive px-3">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light"><tr><th>Placa</th><th>Marca/Modelo</th></tr></thead>
                            <tbody>';
            foreach ($client->vehicles as $v) {
                $html .= '<tr><td><strong>' . $v->placa . '</strong></td><td>' . $v->marca . ' ' . $v->modelo . '</td></tr>';
            }
            $html .= '</tbody></table></div>';
        } else {
            $html .= '<p class="px-3 text-muted small">Nenhum veículo vinculado.</p>';
        }

        return response($html);
    }
}
