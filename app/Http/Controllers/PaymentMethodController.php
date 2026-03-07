<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Auth;

class PaymentMethodController extends Controller
{
    public function index()
    {
        return view('content.finance.payment-methods.index');
    }

    public function dataBase()
    {
        $companyId = Auth::user()->company_id;
        $methods = PaymentMethod::where(function($q) use ($companyId) {
            $q->where('company_id', $companyId)
              ->orWhereNull('company_id'); // Formas globais (Dinheiro, PIX...)
        })->get();
        
        return response()->json(['data' => $methods]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
        ]);

        $validated['company_id'] = Auth::user()->company_id;

        PaymentMethod::create($validated);
        return response()->json(['success' => true, 'message' => 'Forma de pagamento criada!']);
    }

    public function update(Request $request, $id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->update($request->all());
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->delete();
        return response()->json(['success' => true]);
    }
}