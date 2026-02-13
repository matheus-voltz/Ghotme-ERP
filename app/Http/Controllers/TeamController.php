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

            // Query users belonging strictly to the current user's company
            $currentUser = Auth::user();

            \Illuminate\Support\Facades\Log::info('TeamList Debug:', [
                'User ID' => $currentUser->id,
                'User Company' => $currentUser->company_id,
                'User Email' => $currentUser->email
            ]);

            // Start query
            $query = User::query();

            // 1. Must be in the same company
            if ($currentUser->company_id) {
                $query->where('company_id', $currentUser->company_id);
            } else {
                // If user has no company, they should only see their direct children (freelancers)
                $query->where('parent_id', $currentUser->id);
            }

            // 2. Hide the user themselves
            $query->where('id', '!=', $currentUser->id);

            // Search functionality
            if (!empty($request->input('search.value'))) {
                $search = $request->input('search.value');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }

            $totalFiltered = $query->count();

            // Total data count (without search filter)
            // Re-use logic for consistency
            $totalDataQuery = User::where('id', '!=', $currentUser->id)
                ->where(function ($q) use ($currentUser) {
                    $q->where('parent_id', $currentUser->id);
                    if ($currentUser->company_id) {
                        $q->orWhere('company_id', $currentUser->company_id);
                    }
                });
            $totalData = $totalDataQuery->count();

            $query->orderBy($order, $dir);

            if ($limit !== null && $limit != -1) {
                $query->offset($start)->limit($limit);
            }

            $users = $query->get();

            $data = [];

            foreach ($users as $user) {
                $createdAt = '-';
                if ($user->created_at instanceof \Carbon\Carbon) {
                    $createdAt = $user->created_at->format('d/m/Y');
                } elseif (is_string($user->created_at)) {
                    $createdAt = date('d/m/Y', strtotime($user->created_at));
                }

                $data[] = [
                    'id' => $user->id,
                    'fake_id' => $user->id, // Frontend uses this
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at ? 'Verificado' : 'Pendente',
                    'role' => $user->role,
                    'plan' => $user->plan,
                    'contact_number' => $user->contact_number, // Added contact number
                    'created_at' => $createdAt,
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
            return response()->json(['error' => 'Erro ao carregar dados: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
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
                // Create Logic with Plan Limits
                $plan = $currentUser->plan ?? 'free';
                $limit = $this->getPlanLimit($plan);

                // Count existing employees (excluding the owner/current user if they are part of the count, 
                // but usually the limit is on the number of additional users or total users).
                // Let's assume the limit is excluding the owner (so it's "employees" limit).
                $currentEmployees = User::where('parent_id', $currentUser->id)->count();

                if ($currentEmployees >= $limit) {
                    return response()->json([
                        'message' => "Seu plano atual ({$plan}) permite apenas {$limit} funcionários. Faça um upgrade para adicionar mais."
                    ], 403);
                }

                $email = trim($request->email);
                if (User::where('email', $email)->exists()) {
                    return response()->json(['message' => "Este e-mail já está cadastrado no sistema."], 422);
                }

                $rawPassword = Str::random(8); // Generate random password

                $newUser = User::create([
                    'name' => trim($request->name),
                    'email' => $email,
                    'password' => bcrypt($rawPassword),
                    'parent_id' => $currentUser->id,
                    'company_id' => $currentUser->company_id,
                    'company' => $currentUser->company,
                    'role' => $request->role ?? 'funcionario',
                    'plan' => 'free',
                    'contact_number' => $request->userContact,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);

                // Send Welcome Email
                try {
                    $company = $currentUser->company; // This might be the name string or relationship depending on usage, but let's fetch the model to be safe
                    $companyModel = \App\Models\Company::find($currentUser->company_id);
                    \Illuminate\Support\Facades\Mail::to($newUser->email)->send(new \App\Mail\NewEmployeeMail($newUser, $rawPassword, $companyModel));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error sending welcome email: ' . $e->getMessage());
                }

                \Illuminate\Support\Facades\Log::info('User created successfully', ['id' => $newUser->id, 'parent' => $currentUser->id]);

                return response()->json([
                    'message' => 'Funcionário criado com sucesso!',
                    'user' => $newUser,
                    'raw_password' => $rawPassword // Return for WhatsApp sharing
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error creating user in TeamController: ' . $e->getMessage());
            return response()->json(['message' => 'Erro interno ao salvar usuário: ' . $e->getMessage()], 500);
        }
    }

    private function getPlanLimit($plan)
    {
        return match (strtolower($plan)) {
            'free' => 10,
            'starter' => 20,
            'pro' => 50,
            'enterprise' => 999,
            default => 10,
        };
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
    public function destroy(Request $request, $id)
    {
        $currentUser = Auth::user();

        // Find user belonging to current user or company
        $user = User::where('id', $id)
            ->where(function ($q) use ($currentUser) {
                $q->where('parent_id', $currentUser->id);
                if ($currentUser->company_id) {
                    $q->orWhere('company_id', $currentUser->company_id);
                }
            })
            ->first();

        if ($user) {
            // Cannot delete yourself
            if ($user->id === $currentUser->id) {
                return response()->json(['message' => 'Nao pode deletar a si mesmo.'], 403);
            }

            // Save reason if provided
            $reason = $request->input('reason');
            if ($reason) {
                $user->deleted_reason = $reason;
                $user->save();
            }

            $user->delete(); // Soft delete

            \Illuminate\Support\Facades\Log::info('User soft deleted', ['id' => $id, 'reason' => $reason]);

            return response()->json(['message' => 'Usuário removido com sucesso.']);
        }
        return response()->json(['message' => 'User not found or permission denied'], 404);
    }
}
