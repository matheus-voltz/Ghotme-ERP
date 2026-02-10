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
    // API: Retorna JSON no formato do jKanban
    public function fetch()
    {
        $boards = KanbanBoard::where('company_id', Auth::user()->company_id)
            ->orderBy('order')
            ->with('items')
            ->get();

        if ($boards->isEmpty()) {
            return response()->json([
                [
                    "id" => "board-in-progress",
                    "title" => "Em Progresso",
                    "item" => [
                        [
                            "id" => "in-progress-1",
                            "title" => "Pesquisar UX da página FAQ",
                            "comments" => "12",
                            "badge-text" => "UX",
                            "badge" => "success",
                            "due-date" => "5 de Abril",
                            "attachments" => "4",
                            "assigned" => ["12.png", "5.png"],
                            "members" => ["Bruce", "Clark"]
                        ],
                        [
                            "id" => "in-progress-2",
                            "title" => "Revisar código Javascript",
                            "comments" => "8",
                            "badge-text" => "Code Review",
                            "badge" => "danger",
                            "attachments" => "2",
                            "due-date" => "10 de Abril",
                            "assigned" => ["3.png", "8.png"],
                            "members" => ["Helena", "Iris"]
                        ]
                    ]
                ],
                [
                    "id" => "board-in-review",
                    "title" => "Em Revisão",
                    "item" => [
                        [
                            "id" => "in-review-1",
                            "title" => "Revisar Apps completados",
                            "comments" => "17",
                            "badge-text" => "Info",
                            "badge" => "info",
                            "due-date" => "8 de Abril",
                            "attachments" => "8",
                            "assigned" => ["11.png", "6.png"],
                            "members" => ["Laurel", "Harley"]
                        ],
                        [
                            "id" => "in-review-2",
                            "title" => "Encontrar novas imagens",
                            "comments" => "18",
                            "badge-text" => "Imagens",
                            "badge" => "warning",
                            "due-date" => "2 de Abril",
                            "attachments" => "10",
                            "assigned" => ["9.png", "2.png", "3.png", "12.png"],
                            "members" => ["Dianna", "Jordan", "Vinnie", "Lasa"]
                        ]
                    ]
                ],
                [
                    "id" => "board-done",
                    "title" => "Feito",
                    "item" => [
                        [
                            "id" => "done-1",
                            "title" => "Seção Formulários & Tabelas",
                            "comments" => "4",
                            "badge-text" => "App",
                            "badge" => "secondary",
                            "due-date" => "7 de Abril",
                            "attachments" => "1",
                            "assigned" => ["2.png", "9.png", "10.png"],
                            "members" => ["Kara", "Nyssa", "Darcey"]
                        ],
                        [
                            "id" => "done-2",
                            "title" => "Gráficos & Mapas Completos",
                            "comments" => "21",
                            "badge-text" => "Gráficos & Mapas",
                            "badge" => "primary",
                            "due-date" => "7 de Abril",
                            "attachments" => "6",
                            "assigned" => ["1.png"],
                            "members" => ["Sarah"]
                        ]
                    ]
                ]
            ]);
        }

        $kanbanData = $boards->map(function ($board) {
            return [
                'id' => 'board-' . $board->id,
                'title' => $board->title,
                'item' => $board->items->map(function ($item) {
                    $assigned = $item->assigned_to;
                    if (is_string($assigned)) {
                        $assigned = explode(',', $assigned);
                    }

                    $members = $item->members;
                    if (is_string($members)) {
                        $members = explode(',', $members);
                    }

                    return [
                        'id' => (string) $item->id,
                        'title' => $item->title,
                        'due-date' => $item->due_date ? $item->due_date->format('Y-m-d') : null,
                        'badge-text' => $item->badge_text,
                        'badge' => $item->badge_color,
                        'assigned' => $assigned ?? [],
                        'members' => $members ?? [],
                        'comments' => (string) ($item->comments_count ?? "0"),
                        'attachments' => (string) ($item->attachments_count ?? "0")
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

    public function updateItem(Request $request, $id)
    {
        $item = KanbanItem::findOrFail($id);

        $item->update([
            'title' => $request->title,
            'due_date' => $request->dueDate,
            'badge_text' => $request->badgeText,
            'badge_color' => $request->badgeColor,
        ]);

        return response()->json($item);
    }

    public function deleteItem($id)
    {
        $item = KanbanItem::findOrFail($id);
        $item->delete();

        return response()->json(['success' => true]);
    }
}
