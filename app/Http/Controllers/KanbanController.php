<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KanbanBoard;
use App\Models\KanbanItem;
use App\Models\KanbanActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
                    $assignedToIds = $item->assigned_to ?? [];

                    // Se não for array (legado), tenta converter
                    if (is_string($assignedToIds)) {
                        $assignedToIds = explode(',', $assignedToIds);
                    }

                    // Busca os usuários reais para pegar as fotos e nomes
                    $users = \App\Models\User::whereIn('id', $assignedToIds)->get();

                    $assignedPhotos = $users->map(fn($u) => $u->profile_photo_url)->toArray();
                    $membersNames = $users->pluck('name')->toArray();

                    return [
                        'id' => (string) $item->id,
                        'title' => $item->title,
                        'due-date' => $item->due_date ? $item->due_date->format('Y-m-d') : null,
                        'badge-text' => $item->badge_text,
                        'badge' => $item->badge_color,
                        'assigned' => $assignedPhotos,
                        'members' => $membersNames,
                        'assigned_ids' => $assignedToIds,
                        'comments' => (string) ($item->comments_count ?? "0"),
                        'attachments' => (string) ($item->attachments_count ?? "0")
                    ];
                })
            ];
        });

        return response()->json($kanbanData);
    }

    public function fetchActivities($id)
    {
        \Illuminate\Support\Facades\Log::info('fetchActivities called with ID: ' . $id);

        // Se o ID não for numérico, provavelmente é um card de demonstração (ex: in-progress-1)
        if (!is_numeric($id)) {
            return response()->json([]);
        }

        try {
            $item = KanbanItem::findOrFail($id);
            $activities = $item->activities()->with('user')->get();

            $formatted = $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'user_name' => $activity->user->name ?? 'Sistema',
                    'user_avatar' => $activity->user ? $activity->user->profile_photo_url : asset('assets/img/avatars/1.png'),
                    'type' => $activity->type,
                    'description' => $activity->description,
                    'created_at' => $activity->created_at ? $activity->created_at->format('d/m/Y H:i') : '',
                    'time_ago' => $activity->created_at ? $activity->created_at->diffForHumans() : ''
                ];
            });

            return response()->json($formatted);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in fetchActivities: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function logActivity($itemId, $type, $description, $data = null)
    {
        return KanbanActivity::create([
            'kanban_item_id' => $itemId,
            'user_id' => Auth::id(),
            'type' => $type,
            'description' => $description,
            'data' => $data
        ]);
    }

    public function getUsers()
    {
        $users = \App\Models\User::where('company_id', Auth::user()->company_id)
            ->where('status', 'active')
            ->get(['id', 'name', 'profile_photo_path']);

        $users = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->profile_photo_url
            ];
        });

        return response()->json($users);
    }

    public function addBoard(Request $request)
    {
        $request->validate(['title' => 'required']);

        $board = KanbanBoard::create([
            'company_id' => Auth::user()->company_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title),
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

        $this->logActivity($item->id, 'creation', 'Criou a tarefa: ' . $item->title);

        return response()->json($item);
    }

    public function moveItem(Request $request)
    {
        $itemId = $request->itemId;
        $targetBoardId = str_replace('board-', '', $request->targetBoardId);

        $item = KanbanItem::findOrFail($itemId);
        $oldBoard = $item->board->title;
        $item->update([
            'kanban_board_id' => $targetBoardId
        ]);
        $newBoard = $item->fresh()->board->title;

        $this->logActivity($item->id, 'move', "Moveu a tarefa de '{$oldBoard}' para '{$newBoard}'");

        return response()->json(['success' => true]);
    }

    public function updateItem(Request $request, $id)
    {
        $item = KanbanItem::findOrFail($id);
        $oldTitle = $item->title;

        $item->update([
            'title' => $request->title,
            'due_date' => $request->dueDate,
            'badge_text' => $request->badgeText,
            'badge_color' => $request->badgeColor,
            'assigned_to' => $request->assignedTo, // Espera array de IDs
        ]);

        if ($oldTitle !== $request->title) {
            $this->logActivity($item->id, 'update', "Renomeou a tarefa de '{$oldTitle}' para '{$request->title}'");
        }

        if ($request->comment) {
            $this->logActivity($item->id, 'comment', "Adicionou um comentário: " . strip_tags($request->comment));
        }

        return response()->json($item);
    }

    public function deleteItem($id)
    {
        $item = KanbanItem::findOrFail($id);
        $item->delete();

        return response()->json(['success' => true]);
    }
}
