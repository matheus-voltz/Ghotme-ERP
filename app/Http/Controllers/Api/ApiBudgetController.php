<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Http\Request;

class ApiBudgetController extends Controller
{
    public function index(Request $request)
    {
        // O isolamento por company_id é feito automaticamente pela trait no model Budget
        $query = Budget::with(['client', 'veiculo'])
            ->latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(20));
    }

    public function show($id)
    {
        $budget = Budget::with(['client', 'veiculo', 'items.service', 'parts.part'])
            ->findOrFail($id);

        return response()->json($budget);
    }
}