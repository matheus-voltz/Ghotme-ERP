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
            'damages' => 'required|array',
            'damages.*.part' => 'required|string',
            'damages.*.photo' => 'required|string', // Base64 or will be handled via files
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

            foreach ($request->damages as $index => $damage) {
                $photoPath = null;

                // If it's a file in the multipart request
                if ($request->hasFile("damages.$index.photo_file")) {
                    $photoPath = $request->file("damages.$index.photo_file")->store('checklists', 'public');
                }

                VehicleInspectionDamagePoint::create([
                    'vehicle_inspection_id' => $inspection->id,
                    'part_name' => $damage['part'],
                    'type' => 'risk',
                    'photo_path' => $photoPath,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vistoria salva com sucesso!',
                'inspection_id' => $inspection->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar vistoria: ' . $e->getMessage()
            ], 500);
        }
    }
}
