<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;

class PublicBudgetController extends Controller
{
    public function show($uuid)
    {
        // Buscamos o orçamento pelo UUID, ignorando o escopo de empresa pois o cliente é externo
        $budget = Budget::withoutGlobalScope('company')
            ->with(['client', 'veiculo', 'items.service', 'parts.part', 'company'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('content.public.budget-approval', compact('budget'));
    }

    public function approve(Request $request, $uuid)
    {
        $budget = Budget::withoutGlobalScope('company')->where('uuid', $uuid)->firstOrFail();

        if ($budget->status !== 'pending') {
            return back()->with('error', 'Este orçamento já foi processado.');
        }

        $budget->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approval_ip' => $request->ip()
        ]);

        return back()->with('success', 'Orçamento aprovado com sucesso!');
    }

    public function reject(Request $request, $uuid)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:5'
        ]);

        $budget = Budget::withoutGlobalScope('company')->where('uuid', $uuid)->firstOrFail();

        $budget->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason,
            'approval_ip' => $request->ip()
        ]);

        return back()->with('success', 'Orçamento rejeitado.');
    }
}