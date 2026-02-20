<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewEmployeeMail;
use App\Models\Company;

class TeamController extends Controller
{
    /**
     * Redirect to team-management view.
     */
    public function index()
    {
        $currentUser = Auth::user();
        $companyId = $currentUser->company_id;

        // Stats filtrados por empresa
        $query = User::where('company_id', $companyId)->where('id', '!=', $currentUser->id);
        
        $totalUser = $query->count();
        $verified = (clone $query)->whereNotNull('email_verified_at')->count();
        $notVerified = (clone $query)->whereNull('email_verified_at')->count();

        return view('content.pages.settings.team-management', [
            'totalUser' => $totalUser,
            'verified' => $verified,
            'notVerified' => $notVerified,
            'userDuplicates' => 0,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function dataBase(Request $request)
    {
        try {
            $currentUser = Auth::user();
            $companyId = $currentUser->company_id;

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

            // Query base: Mesma empresa e não é o próprio usuário
            $query = User::where('company_id', $companyId)->where('id', '!=', $currentUser->id);

            // Filtro de Busca
            if (!empty($request->input('search.value'))) {
                $search = $request->input('search.value');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }

            $totalData = User::where('company_id', $companyId)->where('id', '!=', $currentUser->id)->count();
            $totalFiltered = $query->count();

            $query->orderBy($order, $dir);

            if ($limit !== null && $limit != -1) {
                $query->offset($start)->limit($limit);
            }

            $users = $query->get();
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
                    'contact_number' => $user->contact_number,
                    'created_at' => $user->created_at ? $user->created_at->format('d/m/Y') : '-',
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => intval($totalData),
                'recordsFiltered' => intval($totalFiltered),
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('TeamController dataBase error: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao carregar colaboradores'], 500);
        }
    }

    /**
     * Store or Update resource.
     */
    public function store(Request $request)
    {
        try {
            $userID = $request->id;
            $currentUser = Auth::user();

            if ($userID) {
                // Update: Segurança reforçada verificando company_id
                $userToUpdate = User::where('id', $userID)->where('company_id', $currentUser->company_id)->first();

                if (!$userToUpdate) {
                    return response()->json(['message' => "Acesso negado ou colaborador não encontrado."], 403);
                }

                $userToUpdate->update([
                    'name' => trim($request->name),
                    'email' => trim($request->email),
                    'contact_number' => $request->userContact,
                    'role' => $request->role,
                ]);

                return response()->json(['message' => 'Colaborador atualizado com sucesso.']);
            } else {
                // Create Logic
                $plan = $currentUser->plan ?? 'free';
                $limit = $this->getPlanLimit($plan);
                $currentEmployees = User::where('company_id', $currentUser->company_id)->where('id', '!=', $currentUser->id)->count();

                if ($currentEmployees >= $limit) {
                    return response()->json([
                        'message' => "Seu plano permite apenas {$limit} colaboradores. Faça upgrade para adicionar mais."
                    ], 403);
                }

                $email = trim($request->email);
                if (User::where('email', $email)->exists()) {
                    return response()->json(['message' => "Este e-mail já está cadastrado."], 422);
                }

                $rawPassword = Str::random(8);

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
                    'is_active' => 1
                ]);

                // Welcome Email
                try {
                    $companyModel = Company::find($currentUser->company_id);
                    Mail::to($newUser->email)->send(new NewEmployeeMail($newUser, $rawPassword, $companyModel));
                } catch (\Exception $e) {
                    Log::error('Error sending welcome email: ' . $e->getMessage());
                }

                return response()->json([
                    'message' => 'Colaborador criado com sucesso!',
                    'user' => $newUser,
                    'raw_password' => $rawPassword
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in TeamController@store: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao salvar colaborador.'], 500);
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
     * Show the form for editing.
     */
    public function edit($id)
    {
        $user = User::where('id', $id)->where('company_id', Auth::user()->company_id)->firstOrFail();
        return response()->json($user);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(Request $request, $id)
    {
        $currentUser = Auth::user();
        $user = User::where('id', $id)->where('company_id', $currentUser->company_id)->first();

        if ($user) {
            if ($user->id === $currentUser->id) {
                return response()->json(['message' => 'Você não pode remover sua própria conta.'], 403);
            }

            if ($request->filled('reason')) {
                $user->deleted_reason = $request->reason;
                $user->save();
            }

            $user->delete();
            return response()->json(['message' => 'Colaborador removido com sucesso.']);
        }
        return response()->json(['message' => 'Acesso negado ou colaborador não encontrado.'], 404);
    }
}
