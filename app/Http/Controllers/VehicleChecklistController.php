<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicles;
use App\Models\ChecklistItem;
use App\Models\VehicleInspection;
use App\Models\VehicleInspectionItem;
use App\Models\OrdemServico;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VehicleChecklistController extends Controller
{
    public function index()
    {
        $inspections = VehicleInspection::with(['veiculo', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('content.pages.ordens-servico.checklist-index', compact('inspections'));
    }

    public function create(Request $request)
    {
        $vehicles = Vehicles::all();
        $checklistItems = ChecklistItem::all();
        $osId = $request->query('os_id');
        $selectedOs = null;
        
        if ($osId) {
            $selectedOs = OrdemServico::with('veiculo')->find($osId);
        }

        return view('content.pages.ordens-servico.checklist-create', compact('vehicles', 'checklistItems', 'selectedOs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'veiculo_id' => 'required|exists:veiculos,id',
            'ordem_servico_id' => 'nullable|exists:ordem_servicos,id',
            'fuel_level' => 'required|string',
            'km_current' => 'required|integer',
            'notes' => 'nullable|string',
            'items' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            $inspection = VehicleInspection::create([
                'veiculo_id' => $validated['veiculo_id'],
                'ordem_servico_id' => $validated['ordem_servico_id'],
                'user_id' => Auth::id(),
                'fuel_level' => $validated['fuel_level'],
                'km_current' => $validated['km_current'],
                'notes' => $validated['notes'],
            ]);

            foreach ($validated['items'] as $itemId => $data) {
                VehicleInspectionItem::create([
                    'vehicle_inspection_id' => $inspection->id,
                    'checklist_item_id' => $itemId,
                    'status' => $data['status'],
                    'observations' => $data['observations'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('ordens-servico.checklist')->with('success', 'Checklist de entrada realizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao salvar checklist: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $inspection = VehicleInspection::with(['veiculo', 'user', 'items.checklistItem'])->findOrFail($id);
        return view('content.pages.ordens-servico.checklist-show', compact('inspection'));
    }
}
