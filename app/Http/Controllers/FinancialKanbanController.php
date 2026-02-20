<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinancialTransaction;
use App\Models\Clients;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FinancialKanbanController extends Controller
{
    public function index()
    {
        return view('content.finance.kanban.index');
    }

    /**
     * API: Retorna as transações no formato do Kanban
     */
    public function fetch()
    {
        $companyId = Auth::user()->company_id;
        $today = Carbon::today();

        // Buscar todas as transações de entrada pendentes ou pagas recentemente (últimos 30 dias)
        $transactions = FinancialTransaction::where('company_id', $companyId)
            ->where('type', 'in')
            ->where(function($q) use ($today) {
                $q->where('status', 'pending')
                  ->orWhere(function($sq) use ($today) {
                      $sq->where('status', 'paid')->where('paid_at', '>=', $today->copy()->subDays(30));
                  });
            })
            ->with('client')
            ->get();

        // Estrutura base dos boards
        $boards = [
            'overdue' => ['id' => 'overdue', 'title' => 'Atrasado', 'class' => 'danger', 'items' => []],
            'due_today' => ['id' => 'due_today', 'title' => 'Vencendo Hoje', 'class' => 'warning', 'items' => []],
            'upcoming' => ['id' => 'upcoming', 'title' => 'A Vencer', 'class' => 'info', 'items' => []],
            'notified' => ['id' => 'notified', 'title' => 'Cobrança Enviada', 'class' => 'primary', 'items' => []],
            'received' => ['id' => 'received', 'title' => 'Recebido', 'class' => 'success', 'items' => []],
        ];

        foreach ($transactions as $tr) {
            $boardKey = 'upcoming';
            $dueDate = Carbon::parse($tr->due_date);

            if ($tr->status === 'paid') {
                $boardKey = 'received';
            } elseif ($tr->bank_transaction_id) { // Usando esse campo como flag de notificação/processamento se desejar
                $boardKey = 'notified';
            } elseif ($dueDate->isPast() && !$dueDate->isToday()) {
                $boardKey = 'overdue';
            } elseif ($dueDate->isToday()) {
                $boardKey = 'due_today';
            }

            $boards[$boardKey]['items'][] = [
                'id' => (string) $tr->id,
                'title' => ($tr->client->name ?? 'Cliente Avulso') . ' - R$ ' . number_format($tr->amount, 2, ',', '.'),
                'description' => $tr->description,
                'due-date' => $dueDate->format('d/m/Y'),
                'badge-text' => $tr->category ?? 'Serviço',
                'badge' => $boards[$boardKey]['class'],
                'client_whatsapp' => $tr->client->whatsapp ?? null
            ];
        }

        return response()->json(array_values($boards));
    }

    /**
     * Atualiza o status da transação ao mover no Kanban
     */
    public function updateStatus(Request $request)
    {
        $id = $request->id;
        $targetBoard = $request->targetBoard;
        $transaction = FinancialTransaction::where('id', $id)
            ->where('company_id', Auth::user()->company_id)
            ->firstOrFail();

        if ($targetBoard === 'received') {
            $transaction->update([
                'status' => 'paid',
                'paid_at' => now()
            ]);
        } elseif ($targetBoard === 'overdue' || $targetBoard === 'upcoming' || $targetBoard === 'due_today') {
            $transaction->update([
                'status' => 'pending',
                'paid_at' => null
            ]);
        }

        return response()->json(['success' => true]);
    }
}
