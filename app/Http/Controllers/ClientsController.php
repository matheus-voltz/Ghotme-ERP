<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Clients;

class ClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function index()
    {
        $clients = Clients::all();
        return view('content.pages.clients.clients-index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function dataBase(Request $request)
    {
        $totalData = Clients::count();
        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')] ?? 'id';
        $dir = $request->input('order.0.dir') ?? 'desc';

        if (empty($request->input('search.value'))) {
            $clients = Clients::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');

            $clients = Clients::where('id', 'LIKE', "%{$search}%")
                ->orWhere('name', 'LIKE', "%{$search}%")
                ->orWhere('company_name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            $totalFiltered = Clients::where('id', 'LIKE', "%{$search}%")
                ->orWhere('name', 'LIKE', "%{$search}%")
                ->orWhere('company_name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->count();
        }

        $data = [];
        $ids = $start;

        foreach ($clients as $client) {
            $nestedData['fake_id'] = ++$ids;
            $nestedData['id'] = $client->id;
            // Para o JS exibir corretamente, passamos o nome completo (ou razão social) no campo 'name'
            $nestedData['name'] = $client->type === 'PJ' ? $client->company_name : $client->name;
            $nestedData['email'] = $client->email;
            $nestedData['email_verified_at'] = 1; // Simulado pois Client não tem verification
            $nestedData['is_active'] = $client->status === 'Ativo';
            $nestedData['type'] = $client->type;

            // Campos extras que podem ser úteis no template JS
            $nestedData['company_name'] = $client->company_name;
            $nestedData['trade_name'] = $client->trade_name ?? '';
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

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
