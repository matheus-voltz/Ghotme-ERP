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
        // DEBUGGING
        \Illuminate\Support\Facades\Log::info('--- Checklist Create Start ---');

        $vehicles = Vehicles::all();

        // Filter checklist items by current niche categories
        $nicheCategories = array_keys(niche('checklist_categories') ?? []);
        $nicheCategories[] = 'Geral'; // Always include General

        $checklistItems = ChecklistItem::whereIn('category', $nicheCategories)
            ->orderBy('category')
            ->orderBy('order')
            ->get();

        // If no items found for this niche, fallback to all (to not break everything)
        if ($checklistItems->isEmpty()) {
            $checklistItems = ChecklistItem::all();
        }

        $osId = $request->query('os_id');
        $selectedOs = null;
        $petType = 'dog';

        if ($osId) {
            $selectedOs = OrdemServico::with('veiculo')->find($osId);
            
            if ($selectedOs && $selectedOs->veiculo) {
                $breed = strtolower($selectedOs->veiculo->modelo . ' ' . $selectedOs->veiculo->marca);
                $catKeywords = ['gato', 'felino', 'siames', 'persa', 'maine', 'angora', 'bengal', 'sphynx'];
                foreach ($catKeywords as $keyword) {
                    if (str_contains($breed, $keyword)) {
                        $petType = 'cat';
                        break;
                    }
                }
            }
        }
        
        $inspectionComponent = niche_config('components.visual_inspection');
        \Illuminate\Support\Facades\Log::info('Checklist Niche: ' . niche('current'));
        \Illuminate\Support\Facades\Log::info('Pet Type Determined: ' . $petType);
        \Illuminate\Support\Facades\Log::info('Inspection Component Path: ' . ($inspectionComponent ?? 'NULL'));

        return view('content.pages.ordens-servico.checklist-create', compact('vehicles', 'checklistItems', 'selectedOs', 'petType'));
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
                'token' => \Illuminate\Support\Str::random(32),
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

            // Save Visual Inspection Points
            if ($request->filled('damage_points_json')) {
                $damagePoints = json_decode($request->input('damage_points_json'), true);
                if (is_array($damagePoints)) {
                    foreach ($damagePoints as $point) {
                        \App\Models\VehicleInspectionDamagePoint::create([
                            'vehicle_inspection_id' => $inspection->id,
                            'part_name' => \Illuminate\Support\Str::limit($point['note'], 50),
                            'x_coordinate' => $point['x'],
                            'y_coordinate' => $point['y'],
                            'notes' => $point['note'],
                            'type' => 'risk'
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('ordens-servico.checklist')->with('success', __('Checklist de entrada realizado com sucesso!'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', __('Erro ao salvar checklist: ') . $e->getMessage());
        }
    }

    public function show(Request $request, $id)
    {
        $token = $request->query('token');

        $query = VehicleInspection::withoutGlobalScopes() // Remove global scope to allow public viewing
            ->with(['veiculo.client', 'user', 'items.checklistItem', 'company', 'damagePoints']);

        if ($token) {
            $inspection = $query->where('token', $token)->firstOrFail();
            return view('content.pages.ordens-servico.checklist-show', [
                'inspection' => $inspection,
                'isPublic' => true,
                'layout' => 'layouts/blankLayout'
            ]);
        }

        // Se não houver token, exige login e usa layout padrão
        if (!Auth::check()) {
            abort(403, __('Acesso negado. Token de segurança necessário.'));
        }

        $inspection = $query->findOrFail($id);

        $petType = 'dog';
        if ($inspection->veiculo) {
            $breed = strtolower($inspection->veiculo->modelo . ' ' . $inspection->veiculo->marca);
            $catKeywords = ['gato', 'felino', 'siames', 'persa', 'maine', 'angora', 'bengal', 'sphynx'];
            foreach ($catKeywords as $keyword) {
                if (str_contains($breed, $keyword)) {
                    $petType = 'cat';
                    break;
                }
            }
        }

        return view('content.pages.ordens-servico.checklist-show', [
            'inspection' => $inspection,
            'isPublic' => false,
            'layout' => 'layouts/layoutMaster',
            'petType' => $petType
        ]);
    }

    public function sendEmail($id)
    {
        $inspection = VehicleInspection::withoutGlobalScope('company')
            ->with(['veiculo.client', 'company'])
            ->findOrFail($id);

        $email = $inspection->veiculo->client->email;

        if (!$email) {
            return response()->json(['success' => false, 'message' => __('Cliente sem e-mail cadastrado.')]);
        }

        try {
            Mail::to($email)->send(new ChecklistSharedMail($inspection));
            return response()->json(['success' => true, 'message' => __('E-mail enviado com sucesso para ') . $email]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => __('Erro ao enviar e-mail: ') . $e->getMessage()]);
        }
    }
}
