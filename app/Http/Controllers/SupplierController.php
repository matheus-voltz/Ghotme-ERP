<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('content.inventory.suppliers.index');
    }

    /**
     * Return data for DataTables.
     */
    public function dataBase(Request $request)
    {
        $totalData = Supplier::count();
        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        
        $columns = [
            0 => 'id',
            1 => 'id',
            2 => 'name',
            3 => 'contact_name',
            4 => 'email',
            5 => 'phone',
            6 => 'city',
            7 => 'is_active',
            8 => 'id'
        ];
        $orderColumnIndex = $request->input('order.0.column');
        $order = $columns[$orderColumnIndex] ?? 'id';
        $dir = $request->input('order.0.dir') ?? 'desc';

        if (empty($request->input('search.value'))) {
            $suppliers = Supplier::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');

            $query = Supplier::where('name', 'LIKE', "%{$search}%")
                ->orWhere('contact_name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->orWhere('trade_name', 'LIKE', "%{$search}%");

            $totalFiltered = $query->count();
            
            $suppliers = $query->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        }

        $data = [];
        $ids = $start;

        foreach ($suppliers as $supplier) {
            $nestedData['fake_id'] = ++$ids;
            $nestedData['id'] = $supplier->id;
            $nestedData['name'] = $supplier->name;
            $nestedData['trade_name'] = $supplier->trade_name;
            $nestedData['contact_name'] = $supplier->contact_name;
            $nestedData['email'] = $supplier->email;
            $nestedData['phone'] = $supplier->phone;
            $nestedData['city'] = $supplier->city;
            $nestedData['is_active'] = $supplier->is_active;
            $nestedData['action'] = ''; // Will be handled by JS

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
            'name' => 'required|string|max:255',
            'trade_name' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'document' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'zip_code' => 'nullable|string|max:20',
        ]);

        $supplier = Supplier::create($validated);

        return response()->json(['success' => true, 'message' => 'Fornecedor criado com sucesso!', 'data' => $supplier]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return response()->json(['success' => false, 'message' => 'Fornecedor não encontrado.'], 404);
        }
        return response()->json($supplier);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return response()->json(['success' => false, 'message' => 'Fornecedor não encontrado.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'trade_name' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'document' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'zip_code' => 'nullable|string|max:20',
        ]);

        $supplier->update($validated);

        return response()->json(['success' => true, 'message' => 'Fornecedor atualizado com sucesso!', 'data' => $supplier]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return response()->json(['success' => false, 'message' => 'Fornecedor não encontrado.'], 404);
        }
        
        $supplier->delete();
        
        return response()->json(['success' => true, 'message' => 'Fornecedor removido com sucesso!']);
    }
}
