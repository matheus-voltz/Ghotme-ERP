<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Http\Request;

class ApiBudgetController extends Controller
{
    public function getPending(Request $request)
    {
        $user = $request->user();
        $query = Budget::with(['client', 'veiculo'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc');

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        return response()->json($query->get());
    }

    public function approve(Request $request, $id)
    {
        $budget = Budget::findOrFail($id);
        if ($request->user()->role !== 'admin' && $budget->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $budget->status = 'approved';
        $budget->approved_at = now();
        $budget->save();

        return response()->json(['message' => 'Orçamento aprovado!', 'budget' => $budget]);
    }

    public function reject(Request $request, $id)
    {
        $budget = Budget::findOrFail($id);
        if ($request->user()->role !== 'admin' && $budget->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $budget->status = 'rejected';
        $budget->rejected_at = now();
        $budget->save();

        return response()->json(['message' => 'Orçamento rejeitado!', 'budget' => $budget]);
    }
}
