<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomField;
use Illuminate\Support\Facades\Auth;

class CustomFieldController extends Controller
{
    /**
     * Display a listing of custom fields.
     */
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $query = CustomField::where('company_id', $companyId);

        if ($request->has('entity')) {
            return response()->json($query->where('entity_type', $request->entity)->where('is_active', true)->orderBy('order')->get());
        }

        $fields = $query->orderBy('entity_type')->orderBy('order')->get();
        
        return view('content.pages.settings.custom-fields.index', compact('fields'));
    }

    /**
     * Store a newly created custom field.
     */
    public function store(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|string',
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'required' => 'boolean',
            'order' => 'integer'
        ]);

        CustomField::create([
            'company_id' => Auth::user()->company_id,
            'entity_type' => $request->entity_type,
            'name' => $request->name,
            'type' => $request->type,
            'options' => $request->options ? explode(',', $request->options) : null,
            'required' => $request->has('required'),
            'order' => $request->order ?? 0,
            'is_active' => true
        ]);

        return redirect()->back()->with('success', 'Campo personalizado criado com sucesso!');
    }

    /**
     * Update the specified custom field.
     */
    public function update(Request $request, $id)
    {
        $field = CustomField::where('id', $id)->where('company_id', Auth::user()->company_id)->firstOrFail();
        
        $field->update([
            'name' => $request->name,
            'type' => $request->type,
            'options' => $request->options ? (is_array($request->options) ? $request->options : explode(',', $request->options)) : null,
            'required' => $request->has('required'),
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->back()->with('success', 'Campo atualizado com sucesso!');
    }

    /**
     * Remove the specified custom field.
     */
    public function destroy($id)
    {
        CustomField::where('id', $id)->where('company_id', Auth::user()->company_id)->delete();
        return redirect()->back()->with('success', 'Campo removido com sucesso!');
    }
}
