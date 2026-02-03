<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    public function index()
    {
        return view('content.finance.payment-methods.index');
    }

    public function dataBase()
    {
        $methods = PaymentMethod::all();
        return response()->json(['data' => $methods]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
        ]);

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