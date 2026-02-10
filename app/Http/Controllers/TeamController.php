<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    /**
     * Redirect to team-management view.
     */
    public function index()
    {
        $userId = Auth::id();

        // Stats
        $totalUser = User::where('parent_id', $userId)->count();
        $verified = User::where('parent_id', $userId)->whereNotNull('email_verified_at')->count();
        $notVerified = User::where('parent_id', $userId)->whereNull('email_verified_at')->count();
        $userDuplicates = 0;

        return view('content.pages.settings.team-management', [
            'totalUser' => $totalUser,
            'verified' => $verified,
            'notVerified' => $notVerified,
            'userDuplicates' => $userDuplicates,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dataBase(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('TeamController dataBase called', [
            'user_id' => Auth::id(),
            'request' => $request->all()
        ]);

        try {
            $columns = [
                0 => 'id',
                1 => 'name',
                2 => 'email',
                3 => 'role',
                4 => 'email_verified_at',
            ];

            $limit = $request->input('length');
            $start = $request->input('start');
            $orderIndex = $request->input('order.0.column');
            $order = $columns[$orderIndex] ?? 'id';
            $dir = $request->input('order.0.dir') ?? 'desc';

            // Users created by current user
            $query = User::where('parent_id', Auth::id());

            // Search
            if (!empty($request->input('search.value'))) {
                $search = $request->input('search.value');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }

            $totalFiltered = $query->count();
            // Re-query for total count (unfiltered)
            $totalData = User::where('parent_id', Auth::id())->count();

            $query->orderBy($order, $dir);

            if ($limit !== null && $limit != -1) {
                $query->offset($start)->limit($limit);
            }

            $users = $query->get();

            \Illuminate\Support\Facades\Log::info('TeamController users fetched', ['count' => $users->count(), 'total' => $totalData]);

            $data = [];

            foreach ($users as $user) {
                $data[] = [
                    'id' => $user->id,
                    'fake_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at ? 'Verificado' : 'Pendente',
                    'role' => $user->role,
                    'plan' => $user->plan,
                    'created_at' => $user->created_at ? $user->created_at->format('d/m/Y') : '-',
                    // The frontend JS uses 'fake_id', 'name', 'email', 'role', 'email_verified_at' keys.
                    // Actions are rendered by JS using 'id'.
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => intval($totalData),
                'recordsFiltered' => intval($totalFiltered),
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('TeamController dataBase error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('TeamController store called', $request->all());

        try {
            $userID = $request->id;
            $currentUser = Auth::user();

            if (!$currentUser) {
                return response()->json(['message' => 'Usuário não autenticado.'], 401);
            }

            if ($userID) {
                // Update
                $userToUpdate = User::where('id', $userID)->where('parent_id', $currentUser->id)->first();

                if (!$userToUpdate) {
                    return response()->json(['message' => "Usuário não encontrado ou sem permissão."], 403);
                }

                $userToUpdate->update([
                    'name' => trim($request->name),
                    'email' => trim($request->email),
                    'contact_number' => $request->userContact,
                    'role' => $request->role,
                ]);

                return response()->json(['message' => 'Usuário atualizado com sucesso.']);
            } else {
                // Create
                $limit = ($currentUser->plan === 'enterprise') ? 10 : 3;
                $createdCount = User::where('parent_id', $currentUser->id)->count();

                if ($createdCount >= $limit) {
                    return response()->json(['message' => "Seu plano permite apenas {$limit} funcionários. Faça upgrade para adicionar mais."], 403);
                }

                $email = trim($request->email);
                if (User::where('email', $email)->exists()) {
                    return response()->json(['message' => "Este e-mail já está cadastrado no sistema."], 422);
                }

                $newUser = User::create([
                    'name' => trim($request->name),
                    'email' => $email,
                    'password' => bcrypt('password'), // Default
                    'parent_id' => $currentUser->id,
                    'company' => $currentUser->company,
                    'company_id' => $currentUser->company_id,
                    'role' => $request->role ?? 'subscriber',
                    'plan' => 'free',
                    'contact_number' => $request->userContact,
                    'country' => 'Brasil', // Default
                ]);

                \Illuminate\Support\Facades\Log::info('User created successfully', ['id' => $newUser->id, 'parent' => $currentUser->id]);

                return response()->json(['message' => 'Funcionário criado com sucesso!', 'user' => $newUser]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error creating user in TeamController: ' . $e->getMessage());
            return response()->json(['message' => 'Erro interno ao salvar usuário: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::where('id', $id)->where('parent_id', Auth::id())->firstOrFail();
        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::where('id', $id)->where('parent_id', Auth::id())->first();
        if ($user) {
            $user->delete();
            return response()->json(['message' => 'User deleted']);
        }
        return response()->json(['message' => 'User not found'], 404);
    }
}
