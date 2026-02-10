<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KanbanBoard;
use App\Models\KanbanItem;
use Illuminate\Support\Facades\Auth;

class KanbanController extends Controller
{
    public function index()
    {
        return view('content.apps.app-kanban');
    }

    // API: Retorna JSON no formato do jKanban
    public function fetch()
    {
        $boards = KanbanBoard::where('company_id', Auth::user()->company_id)
            ->orderBy('order')
            ->with('items')
            ->get();

        $kanbanData = $boards->map(function ($board) {
            return [
                'id' => 'board-' . $board->id,
                'title' => $board->title,
                'item' => $board->items->map(function ($item) {
                    return [
                        'id' => (string) $item->id, // jKanban precisa de string
                        'title' => $item->title,
                        'due-date' => $item->due_date ? $item->due_date->format('Y-m-d') : null,
                        'badge' => $item->badge_text,
                        'badge-color' => $item->badge_color,
                        'assigned' => $item->assigned_to,
                        // 'members' => $item->assigned_to // jKanban usa 'members' ou 'assigned'?
                    ];
                })
            ];
        });

        return response()->json($kanbanData);
    }

    public function addBoard(Request $request)
    {
        $request->validate(['title' => 'required']);
        
        $board = KanbanBoard::create([
            'company_id' => Auth::user()->company_id,
            'title' => $request->title,
            'slug' => \Str::slug($request->title),
            'order' => KanbanBoard::where('company_id', Auth::user()->company_id)->count()
        ]);

        return response()->json(['id' => 'board-' . $board->id, 'title' => $board->title]);
    }

    public function addItem(Request $request)
    {
        // boardId vem como "board-1", precisamos limpar
        $boardId = str_replace('board-', '', $request->boardId);
        
        $item = KanbanItem::create([
            'kanban_board_id' => $boardId,
            'title' => $request->title,
            'badge_text' => 'Novo',
            'badge_color' => 'success'
        ]);

        return response()->json($item);
    }

    public function moveItem(Request $request)
    {
        $itemId = $request->itemId;
        $targetBoardId = str_replace('board-', '', $request->targetBoardId);

        $item = KanbanItem::findOrFail($itemId);
        $item->update([
            'kanban_board_id' => $targetBoardId
        ]);

        return response()->json(['success' => true]);
    }
}
