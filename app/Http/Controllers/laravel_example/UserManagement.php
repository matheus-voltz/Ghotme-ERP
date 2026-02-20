<?php

namespace App\Http\Controllers\laravel_example;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class UserManagement extends Controller
{
  /**
   * Redirect to user-management view.
   *
   */
  public function index(): View
  {
    $currentUser = Auth::user();
    $companyId = $currentUser->company_id;

    // Filtra apenas usuários da mesma empresa
    $query = User::where('company_id', $companyId);
    
    $userCount = $query->count();
    $verified = (clone $query)->whereNotNull('email_verified_at')->count();
    $notVerified = (clone $query)->whereNull('email_verified_at')->count();
    
    return view('content.laravel-example.user-management', [
      'totalUser' => $userCount,
      'verified' => $verified,
      'notVerified' => $notVerified,
      'userDuplicates' => 0, // Removido por não fazer sentido no contexto de empresa única
    ]);
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function dataBase(Request $request): JsonResponse
  {
    $currentUser = Auth::user();
    $companyId = $currentUser->company_id;

    $columns = [
      1 => 'id',
      2 => 'name',
      3 => 'email',
      4 => 'email_verified_at',
      5 => 'is_active',
    ];

    // Segurança: Sempre filtra pela empresa do usuário logado
    $query = User::where('company_id', $companyId);

    $totalData = $query->count();
    $totalFiltered = $totalData;

    $limit = $request->input('length');
    $start = $request->input('start');
    $order = $columns[$request->input('order.0.column')] ?? 'id';
    $dir = $request->input('order.0.dir') ?? 'desc';

    // Search handling
    if (!empty($request->input('search.value'))) {
      $search = $request->input('search.value');

      $query->where(function ($q) use ($search) {
        $q->where('id', 'LIKE', "%{$search}%")
          ->orWhere('name', 'LIKE', "%{$search}%")
          ->orWhere('email', 'LIKE', "%{$search}%");
      });

      $totalFiltered = $query->count();
    }

    $query->orderBy($order, $dir);

    if ($limit !== null && $limit != -1) {
      $query->offset($start)->limit($limit);
    }

    $users = $query->get();

    $data = [];
    $ids = $start;

    foreach ($users as $user) {
      $data[] = [
        'id' => $user->id,
        'fake_id' => ++$ids,
        'name' => $user->name,
        'email' => $user->email,
        'email_verified_at' => $user->email_verified_at,
        'is_active' => $user->is_active,
      ];
    }

    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => intval($totalData),
      'recordsFiltered' => intval($totalFiltered),
      'data' => $data,
    ]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $userID = $request->id;
    $currentUser = auth()->user();

    if ($userID) {
      // Segurança: Verifica se o usuário a ser editado pertence à mesma empresa
      $userToUpdate = User::where('id', $userID)->where('company_id', $currentUser->company_id)->firstOrFail();

      $userToUpdate->update([
        'name' => $request->name,
        'email' => $request->email,
        'contact_number' => $request->userContact,
        'role' => $request->role,
      ]);

      return response()->json('atualizado');
    } else {
      // Check limits based on plan
      $limit = ($currentUser->plan === 'enterprise') ? 10 : 3;
      $createdCount = User::where('company_id', $currentUser->company_id)->count();

      if ($createdCount >= $limit) {
        return response()->json(['message' => "Seu plano permite apenas {$limit} usuários. Faça upgrade para Enterprise para ter mais."], 403);
      }

      // create new one if email is unique
      $userEmail = User::where('email', $request->email)->first();

      if (empty($userEmail)) {
        User::create([
          'name' => $request->name,
          'email' => $request->email,
          'password' => bcrypt(Str::random(10)),
          'company_id' => $currentUser->company_id,
          'parent_id' => $currentUser->id,
          'role' => $request->role ?? 'funcionario',
          'status' => 'active',
          'is_active' => 1
        ]);

        return response()->json('Criado');
      } else {
        return response()->json(['message' => "E-mail já cadastrado no sistema."], 422);
      }
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy($id)
  {
    $currentUser = Auth::user();
    // Segurança: Só deleta se for da mesma empresa
    User::where('id', $id)->where('company_id', $currentUser->company_id)->delete();
    return response()->json(['status' => 'success']);
  }

  /**
   * Suspend the specified user account.
   */
  public function suspendUser(Request $request)
  {
    $currentUser = Auth::user();
    $userId = $request->id;
    $user = User::where('id', $userId)->where('company_id', $currentUser->company_id)->first();

    if ($user) {
      $user->is_active = 0;
      $user->save();
      return response()->json(['status' => 'success', 'message' => 'User suspended successfully']);
    }

    return response()->json(['status' => 'error', 'message' => 'User not found or access denied'], 404);
  }
}
