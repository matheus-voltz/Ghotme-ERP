<?php

namespace App\Http\Controllers;

use App\Models\ClientField;
use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;


class ClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, User $user)
    {
    
        $segment = 'cliente'; // depois você pode puxar da empresa logada

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
        $segment = $request->get('segment', 'cliente');

        $fields = ClientField::where('segment', $segment)
            ->where('active', true)
            ->orderBy('order')
            ->get();

        return view('content.pages.clients.newClients', compact('fields', 'segment'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        dd($request->all());
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'cep' => 'nullable|string',
            'rua' => 'nullable|string',
            'numero' => 'nullable|string',
            'complemento' => 'nullable|string',
            'bairro' => 'nullable|string',
            'cidade' => 'nullable|string',
            'estado' => 'nullable|string',

        ]);

        $client = Client::create($data);

        return redirect()->back()->with('success', 'Cliente salvo com sucesso!');
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
