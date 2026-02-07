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
use Illuminate\Support\Facades\Mail;
use App\Mail\ChecklistSharedMail;

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
            'items.*.id' => 'required|exists:checklist_items,id',
            'items.*.status' => 'required|in:ok,not_ok,na',
            'items.*.observations' => 'nullable|string',
            'items.*.photo' => 'nullable|image|max:5120',
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
                $photoPath = null;
                
                if (isset($data['photo']) && $request->hasFile("items.$itemId.photo")) {
                    $photoPath = $request->file("items.$itemId.photo")->store('checklists', 'public');
                }

                VehicleInspectionItem::create([
                    'vehicle_inspection_id' => $inspection->id,
                    'checklist_item_id' => $data['id'],
                    'status' => $data['status'],
                    'observations' => $data['observations'] ?? null,
                    'photo_path' => $photoPath,
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
        $inspection = VehicleInspection::withoutGlobalScope('company')
            ->with(['veiculo.client', 'user', 'items.checklistItem', 'company'])
            ->findOrFail($id);

        $layout = auth()->check() ? 'layouts/layoutMaster' : 'layouts/layoutPublic';
        
        return view('content.pages.ordens-servico.checklist-show', compact('inspection', 'layout'));
    }

    public function sendEmail($id)
    {
        $inspection = VehicleInspection::withoutGlobalScope('company')
            ->with(['veiculo.client', 'company'])
            ->findOrFail($id);
            
        $email = $inspection->veiculo->client->email;

        if (!$email) {
            return response()->json(['success' => false, 'message' => 'Cliente sem e-mail cadastrado.']);
        }

        try {
            Mail::to($email)->send(new ChecklistSharedMail($inspection));
            return response()->json(['success' => true, 'message' => 'E-mail enviado com sucesso para ' . $email]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao enviar e-mail: ' . $e->getMessage()]);
        }
    }
}