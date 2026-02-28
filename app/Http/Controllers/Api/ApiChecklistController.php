<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VehicleInspection;
use App\Models\VehicleInspectionDamagePoint;
use App\Models\OrdemServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApiChecklistController extends Controller
{
    public function storeVisual(Request $request)
    {
        $request->validate([
            'ordem_servico_id' => 'required|exists:ordem_servicos,id',
            'parts' => 'required|array',
            'photos' => 'required|array',
        ]);

        $os = OrdemServico::findOrFail($request->ordem_servico_id);

        try {
            DB::beginTransaction();

            $inspection = VehicleInspection::create([
                'company_id' => $os->company_id,
                'veiculo_id' => $os->veiculo_id,
                'ordem_servico_id' => $os->id,
                'user_id' => Auth::id(),
                'fuel_level' => 'N/A',
                'km_current' => $os->km_entry ?? 0,
                'token' => Str::random(32),
            ]);

            foreach ($request->parts as $index => $partName) {
                $photoPath = null;

                if ($request->hasFile("photos.$index")) {
                    $photoPath = $request->file("photos.$index")->store('checklists', 'public');
                }

                VehicleInspectionDamagePoint::create([
                    'vehicle_inspection_id' => $inspection->id,
                    'part_name' => $partName,
                    'type' => 'risk',
                    'notes' => $request->input("notes.{$index}", 'Registrado via Mobile'),
                    'photo_path' => $photoPath,
                    'x_coordinate' => $request->input("coordinates.{$index}.x", 0),
                    'y_coordinate' => $request->input("coordinates.{$index}.y", 0)
                ]);
            }

            DB::commit();

            // Dados para compartilhamento
            $portalUrl = url("/portal/{$os->client->uuid}");
            $shareMessage = "Olá {$os->client->name}, a vistoria do seu dispositivo foi concluída na " . ($os->company->name ?? 'Ghotme') . ". Acompanhe o status e as fotos por aqui: " . $portalUrl;

            return response()->json([
                'success' => true,
                'message' => 'Vistoria salva com sucesso!',
                'inspection_id' => $inspection->id,
                'share_message' => $shareMessage,
                'client_phone' => $os->client->phone ?? $os->client->contact_number ?? ''
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Erro Checklist: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar vistoria: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getVisual($osId)
    {
        $inspection = VehicleInspection::where('ordem_servico_id', $osId)
            ->with('damagePoints')
            ->latest()
            ->first();

        if (!$inspection || $inspection->damagePoints->isEmpty()) {
            return response()->json([]);
        }

        $damages = $inspection->damagePoints->map(function ($point) {
            return [
                'id' => $point->id,
                'part' => $point->part_name,
                'photo' => $point->photo_path,
                'date' => $point->created_at->format('d/m/Y H:i')
            ];
        });

        return response()->json($damages);
    }
}
