<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceAddonGroup;
use App\Models\ServiceAddon;
use App\Models\Service;

class ServiceAddonController extends Controller
{
    public function index($serviceId)
    {
        $groups = ServiceAddonGroup::with('addons')->where('service_id', $serviceId)->get();
        return response()->json($groups);
    }

    public function storeGroup(Request $request, $serviceId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'selection_type' => 'required|in:single,multiple',
            'min_options' => 'required|integer|min:0',
            'max_options' => 'nullable|integer|min:1',
        ]);

        $group = ServiceAddonGroup::create([
            'company_id' => auth()->user()->company_id,
            'service_id' => $serviceId,
            'name' => $validated['name'],
            'selection_type' => $validated['selection_type'],
            'min_options' => $validated['min_options'],
            'max_options' => $validated['max_options'],
        ]);

        return response()->json(['success' => true, 'message' => 'Grupo criado com sucesso.', 'data' => $group]);
    }

    public function updateGroup(Request $request, $groupId)
    {
        $group = ServiceAddonGroup::findOrFail($groupId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'selection_type' => 'required|in:single,multiple',
            'min_options' => 'required|integer|min:0',
            'max_options' => 'nullable|integer|min:1',
        ]);

        $group->update($validated);

        return response()->json(['success' => true, 'message' => 'Grupo atualizado.']);
    }

    public function destroyGroup($groupId)
    {
        $group = ServiceAddonGroup::findOrFail($groupId);
        $group->delete();
        return response()->json(['success' => true, 'message' => 'Grupo removido.']);
    }

    public function storeAddon(Request $request, $groupId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $addon = ServiceAddon::create([
            'company_id' => auth()->user()->company_id,
            'service_addon_group_id' => $groupId,
            'name' => $validated['name'],
            'price' => $validated['price'],
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Adicional criado com sucesso.', 'data' => $addon]);
    }

    public function destroyAddon($addonId)
    {
        $addon = ServiceAddon::findOrFail($addonId);
        $addon->delete();
        return response()->json(['success' => true, 'message' => 'Adicional removido.']);
    }
}
