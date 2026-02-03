<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServicePackage;
use App\Models\Service;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;

class ServicePackageController extends Controller
{
    public function index()
    {
        $services = Service::where('is_active', true)->get();
        $parts = InventoryItem::where('is_active', true)->get();
        return view('content.services.packages.index', compact('services', 'parts'));
    }

    public function dataBase(Request $request)
    {
        $totalData = ServicePackage::count();
        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        
        $columns = ['id', 'name', 'total_price', 'is_active'];
        $orderColumnIndex = $request->input('order.0.column');
        $order = $columns[$orderColumnIndex] ?? 'id';
        $dir = $request->input('order.0.dir') ?? 'asc';

        $query = ServicePackage::with(['services', 'parts']);

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where('name', 'LIKE', "%{$search}%");
            $totalFiltered = $query->count();
        }

        $packages = $query->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();

        $data = [];
        $ids = $start;

        foreach ($packages as $package) {
            $nestedData['fake_id'] = ++$ids;
            $nestedData['id'] = $package->id;
            $nestedData['name'] = $package->name;
            $nestedData['total_price'] = $package->total_price;
            $nestedData['services_count'] = $package->services->count();
            $nestedData['parts_count'] = $package->parts->count();
            $nestedData['is_active'] = $package->is_active;
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
            'total_price' => 'nullable|numeric|min:0',
            'services' => 'nullable|array',
            'parts' => 'nullable|array',
            'parts_qty' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $package = ServicePackage::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'total_price' => $validated['total_price'],
            ]);

            if (!empty($validated['services'])) {
                $package->services()->sync($validated['services']);
            }

            if (!empty($validated['parts'])) {
                foreach ($validated['parts'] as $partId) {
                    $qty = $validated['parts_qty'][$partId] ?? 1;
                    $package->parts()->attach($partId, ['quantity' => $qty]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pacote criado com sucesso!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $package = ServicePackage::with(['services', 'parts'])->find($id);
        return response()->json($package);
    }

    public function update(Request $request, $id)
    {
        $package = ServicePackage::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_price' => 'nullable|numeric|min:0',
            'services' => 'nullable|array',
            'parts' => 'nullable|array',
            'parts_qty' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $package->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'total_price' => $validated['total_price'],
            ]);

            $package->services()->sync($validated['services'] ?? []);

            $package->parts()->detach();
            if (!empty($validated['parts'])) {
                foreach ($validated['parts'] as $partId) {
                    $qty = $validated['parts_qty'][$partId] ?? 1;
                    $package->parts()->attach($partId, ['quantity' => $qty]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pacote atualizado com sucesso!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $package = ServicePackage::findOrFail($id);
        $package->delete();
        return response()->json(['success' => true, 'message' => 'Pacote removido com sucesso!']);
    }
}
