<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicBudgetController extends Controller
{
    public function show($uuid)
    {
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

        DB::transaction(function () use ($budget, $request) {
            // Atualiza o orçamento
            $budget->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approval_ip' => $request->ip()
            ]);

            // Cria o registro histórico
            BudgetApproval::create([
                'company_id' => $budget->company_id,
                'budget_id' => $budget->id,
                'status' => 'approved',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return back()->with('success', 'Orçamento aprovado com sucesso!');
    }

    public function reject(Request $request, $uuid)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:5'
        ]);

        $budget = Budget::withoutGlobalScope('company')->where('uuid', $uuid)->firstOrFail();

        DB::transaction(function () use ($budget, $request) {
            // Atualiza o orçamento
            $budget->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejection_reason' => $request->rejection_reason,
                'approval_ip' => $request->ip()
            ]);

            // Cria o registro histórico
            BudgetApproval::create([
                'company_id' => $budget->company_id,
                'budget_id' => $budget->id,
                'status' => 'rejected',
                'reason' => $request->rejection_reason,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return back()->with('success', 'Orçamento rejeitado.');
    }
}
