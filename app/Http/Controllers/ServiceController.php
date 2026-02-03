<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;

class ServiceController extends Controller
{
    public function index()
    {
        return view('content.services.table.index');
    }

    public function dataBase(Request $request)
    {
        $totalData = Service::count();
        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        
        $columns = ['id', 'name', 'price', 'estimated_time', 'is_active'];
        $orderColumnIndex = $request->input('order.0.column');
        $order = $columns[$orderColumnIndex] ?? 'id';
        $dir = $request->input('order.0.dir') ?? 'asc';

        if (empty($request->input('search.value'))) {
            $services = Service::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $query = Service::where('name', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%");

            $totalFiltered = $query->count();
            $services = $query->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        }

        $data = [];
        $ids = $start;

        foreach ($services as $service) {
            $nestedData['fake_id'] = ++$ids;
            $nestedData['id'] = $service->id;
            $nestedData['name'] = $service->name;
            $nestedData['price'] = $service->price;
            $nestedData['estimated_time'] = $service->estimated_time;
            $nestedData['is_active'] = $service->is_active;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'estimated_time' => 'nullable|integer|min:0',
        ]);

        $service = Service::create($validated);

        return response()->json(['success' => true, 'message' => 'Serviço cadastrado com sucesso!', 'data' => $service]);
    }

    public function edit($id)
    {
        $service = Service::find($id);
        return response()->json($service);
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'estimated_time' => 'nullable|integer|min:0',
        ]);

        $service->update($validated);

        return response()->json(['success' => true, 'message' => 'Serviço atualizado com sucesso!']);
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();
        return response()->json(['success' => true, 'message' => 'Serviço removido com sucesso!']);
    }
}
