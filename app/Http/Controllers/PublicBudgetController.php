<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetApproval;
use App\Models\User;
use App\Notifications\BudgetApprovedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

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

    public function checkout($uuid)
    {
        $budget = Budget::withoutGlobalScope('company')
            ->with(['company'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Check if already approved/rejected
        if ($budget->status !== 'pending') {
            return redirect()->route('public.budget.show', $uuid)->with('info', 'Este orçamento já foi processado.');
        }

        $paymentMethods = \App\Models\PaymentMethod::where('company_id', $budget->company_id)
            ->where('is_active', true)
            ->get();

        return view('content.public.budget-checkout', compact('budget', 'paymentMethods'));
    }

    public function approve(Request $request, $uuid)
    {
        $budget = Budget::withoutGlobalScope('company')
            ->with(['veiculo' => function ($q) {
                $q->withoutGlobalScope('company');
            }])
            ->where('uuid', $uuid)
            ->firstOrFail();

        if ($budget->status !== 'pending') {
            return back()->with('error', 'Este orçamento já foi processado.');
        }

        DB::transaction(function () use ($budget, $request) {
            // Atualiza o orçamento
            $budget->update([
                'status' => 'approved',
                'early_payment' => $request->early_payment,
                'approved_at' => now(),
                'approval_ip' => $request->ip()
            ]);

            // Cria o registro histórico de aprovação
            BudgetApproval::create([
                'company_id' => $budget->company_id,
                'budget_id' => $budget->id,
                'status' => 'approved',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Adicionar à Linha do Tempo do Veículo
            \App\Models\VehicleHistory::create([
                'company_id' => $budget->company_id,
                'veiculo_id' => $budget->veiculo_id,
                'date' => now(),
                'km' => $budget->veiculo->km_atual ?? 0,
                'event_type' => 'orcamento_aprovado',
                'title' => 'Orçamento Aprovado #' . $budget->id,
                'description' => 'Serviços e peças autorizados pelo cliente via Portal Digital.' . ($request->early_payment ? ' Optou por pagamento antecipado.' : ''),
                'performer' => 'Cliente (Portal Digital)',
            ]);

            // Notifica os usuários da oficina
            $usersToNotify = User::where('company_id', $budget->company_id)->get();
            Notification::send($usersToNotify, new BudgetApprovedNotification($budget));
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
