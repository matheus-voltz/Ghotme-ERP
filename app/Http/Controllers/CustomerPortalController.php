<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use App\Models\OrdemServico;
use App\Models\Budget;
use Illuminate\Http\Request;

class CustomerPortalController extends Controller
{
    public function index($uuid)
    {
        $client = Clients::withoutGlobalScope('company')
            ->with(['vehicles.history', 'fieldValues', 'company.users'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $orders = OrdemServico::withoutGlobalScope('company')
            ->with(['veiculo', 'items', 'parts', 'user'])
            ->where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $budgets = Budget::withoutGlobalScope('company')
            ->with(['veiculo'])
            ->where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Identifica o atendente responsável (última OS ou o primeiro admin da empresa)
        $responsible = $orders->first()->user ?? $client->company->users()->where('role', 'admin')->first();

        // Lógica dinâmica para definir status de OS baseada em orçamentos pendentes
        $orders->each(function ($order) use ($budgets) {
            $hasPendingBudget = $budgets->where('veiculo_id', $order->veiculo_id)
                ->where('status', 'pending')
                ->isNotEmpty();
            if ($hasPendingBudget && in_array($order->status, ['pending', 'in_progress'])) {
                $order->status = 'awaiting_approval';
            }
        });

        // Buscar histórico unificado de todos os veículos do cliente
        $vehicleIds = $client->vehicles->pluck('id');
        $unifiedHistory = \App\Models\VehicleHistory::whereIn('veiculo_id', $vehicleIds)
            ->with(['ordemServico'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        // Buscar mensagens do chat
        $messages = \App\Models\ChatMessage::withoutGlobalScope('company')
            ->where('client_id', $client->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('content.public.customer-portal.index', compact('client', 'orders', 'budgets', 'unifiedHistory', 'responsible', 'messages'));
    }

    public function sendMessage(Request $request, $uuid)
    {
        $request->validate(['message' => 'required|string']);
        
        $client = Clients::withoutGlobalScope('company')->where('uuid', $uuid)->firstOrFail();
        
        $lastOrder = OrdemServico::withoutGlobalScope('company')
            ->where('client_id', $client->id)
            ->latest()
            ->first();
            
        $receiverId = $lastOrder->user_id ?? \App\Models\User::where('company_id', $client->company_id)->where('role', 'admin')->first()->id;

        $msg = \App\Models\ChatMessage::create([
            'company_id' => $client->company_id,
            'client_id' => $client->id,
            'sender_id' => null, // Externo
            'receiver_id' => $receiverId,
            'message' => $request->message,
            'is_read' => false
        ]);

        return response()->json(['success' => true]);
    }

    public function fetchMessages($uuid)
    {
        $client = Clients::withoutGlobalScope('company')->where('uuid', $uuid)->firstOrFail();
        
        $messages = \App\Models\ChatMessage::withoutGlobalScope('company')
            ->where('client_id', $client->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function showOrder($uuid)
    {
        $order = OrdemServico::withoutGlobalScope('company')
            ->with(['client', 'veiculo', 'items.service', 'parts.part', 'company'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Verifica se há orçamentos pendentes para este veículo
        $hasPendingBudget = Budget::withoutGlobalScope('company')
            ->where('veiculo_id', $order->veiculo_id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingBudget && in_array($order->status, ['pending', 'in_progress'])) {
            $order->status = 'awaiting_approval';
        }

        return view('content.public.customer-portal.order-details', compact('order'));
    }
}
