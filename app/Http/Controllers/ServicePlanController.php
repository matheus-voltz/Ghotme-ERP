<?php

namespace App\Http\Controllers;

use App\Models\ServicePlan;
use App\Models\Service;
use App\Models\ServicePlanItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServicePlanController extends Controller
{
    public function index()
    {
        $services = Service::where('is_active', true)->get();
        return view('content.service-plans.index', compact('services'));
    }

    public function dataBase(Request $request)
    {
        $query = ServicePlan::withCount('items');

        $totalData = $query->count();
        
        $items = $query->latest()->get();

        $data = [];
        foreach ($items as $item) {
            $nestedData['id'] = $item->id;
            $nestedData['name'] = $item->name;
            $nestedData['price'] = 'R$ ' . number_format($item->price, 2, ',', '.');
            $nestedData['interval'] = $this->translateInterval($item->interval, $item->interval_count);
            $nestedData['items_count'] = $item->items_count;
            $nestedData['is_active'] = $item->is_active;
            $nestedData['action'] = '';
            $data[] = $nestedData;
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => intval($totalData),
            'recordsFiltered' => intval($totalData),
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'interval' => 'required|in:week,month,year',
            'services' => 'required|array',
            'services.*.id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $plan = ServicePlan::create([
                'company_id' => Auth::user()->company_id,
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'interval' => $request->interval,
                'interval_count' => 1,
                'niche' => Auth::user()->company->niche ?? 'automotive'
            ]);

            foreach ($request->services as $serviceData) {
                ServicePlanItem::create([
                    'service_plan_id' => $plan->id,
                    'service_id' => $serviceData['id'],
                    'quantity' => $serviceData['quantity']
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Plano de Assinatura criado com sucesso!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erro ao criar plano: ' . $e->getMessage()], 500);
        }
    }

    protected function translateInterval($interval, $count)
    {
        $intervals = [
            'week' => 'Semanal',
            'month' => 'Mensal',
            'year' => 'Anual'
        ];
        return $intervals[$interval] ?? $interval;
    }

    public function destroy($id)
    {
        $plan = ServicePlan::findOrFail($id);
        $plan->delete();
        return response()->json(['success' => true, 'message' => 'Plano removido com sucesso!']);
    }
}
