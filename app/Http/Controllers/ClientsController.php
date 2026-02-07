<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Clients;
use Illuminate\Support\Facades\DB;

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
            4 => 'cpf', // document mapping
            5 => 'email', 
            6 => 'id', // vehicles_count (sorting on count is complex, using id for now)
            7 => 'id'
        ];
        $orderColumnIndex = $request->input('order.0.column');
        $order = $columns[$orderColumnIndex] ?? 'id';
        $dir = $request->input('order.0.dir') ?? 'desc';

        $query = Clients::withCount('vehicles');

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function($q) use ($search) {
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
        $validated = $request->validate([
            'type' => 'required|in:PF,PJ',
            'name' => 'required_if:type,PF|nullable|string|max:255',
            'cpf' => 'required_if:type,PF|nullable|string|max:20',
            'company_name' => 'required_if:type,PJ|nullable|string|max:255',
            'cnpj' => 'required_if:type,PJ|nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:10',
            'rua' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:2',
        ]);

        $client = Clients::create($validated);

        return response()->json(['success' => true, 'message' => 'Cliente cadastrado com sucesso!']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $client = Clients::findOrFail($id);
        return response()->json($client);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $client = Clients::findOrFail($id);
        
        $validated = $request->validate([
            'type' => 'required|in:PF,PJ',
            'name' => 'required_if:type,PF|nullable|string|max:255',
            'cpf' => 'required_if:type,PF|nullable|string|max:20',
            'company_name' => 'required_if:type,PJ|nullable|string|max:255',
            'cnpj' => 'required_if:type,PJ|nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:10',
            'rua' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:2',
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
            $client = Clients::findOrFail($id);
            
            $html = '<div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong>Tipo:</strong></span>
                            <span>'.($client->type == 'PF' ? 'Pessoa Física' : 'Pessoa Jurídica').'</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong>Documento:</strong></span>
                            <span>'.($client->type == 'PF' ? $client->cpf : $client->cnpj).'</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong>E-mail:</strong></span>
                            <span>'.$client->email.'</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong>WhatsApp:</strong></span>
                            <span class="text-success"><i class="ti tabler-brand-whatsapp"></i> '.$client->whatsapp.'</span>
                        </div>';
    
            if($client->rua) {
                $html .= '<div class="list-group-item">
                            <strong>Endereço:</strong><br>
                            '.$client->rua.', '.$client->numero.' - '.$client->bairro.'<br>
                            '.$client->cidade.'/'.$client->estado.'
                        </div>';
            }
    
            $html .= '</div>';
    
            return response($html);
        }
    }
    