<?php

namespace App\Http\Controllers;

use App\Models\ClientField;
use App\Models\User;
use Illuminate\Http\Request;


class ClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, User $user)
    {
    
        $segment = 'clientes'; // depois você pode puxar da empresa logada

        $users = User::all();

        $fields = ClientField::where('segment', $segment)
            ->where('active', true)
            ->orderBy('order')
            ->get();
        

        return view('content.pages.clients.clientes', compact('fields', 'users'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $segment = $request->get('segment', 'oficina');

        $fields = ClientField::where('segment', $segment)
            ->where('active', true)
            ->orderBy('order')
            ->get();

        return view('clients.create', compact('fields', 'segment'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $client = Client::create($request->only([
            'type', 'name', 'document', 'email', 'phone', 'whatsapp'
        ]));

        foreach ($request->dynamic ?? [] as $fieldId => $value) {
            ClientFieldValue::create([
                'client_id' => $client->id,
                'client_field_id' => $fieldId,
                'value' => $value
            ]);
        }

        return redirect()->route('clients.index');
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
