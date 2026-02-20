<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Commission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;
        
        $query = Commission::where('company_id', $companyId)->with(['user', 'ordemServico']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $commissions = $query->orderBy('created_at', 'desc')->get();
        $employees = User::where('company_id', $companyId)->where('role', '!=', 'admin')->get();

        $totals = [
            'pending' => $commissions->where('status', 'pending')->sum('commission_amount'),
            'paid' => $commissions->where('status', 'paid')->sum('commission_amount'),
        ];

        return view('content.pages.team.commissions.index', compact('commissions', 'employees', 'totals'));
    }

    public function markAsPaid($id)
    {
        $commission = Commission::where('id', $id)
            ->where('company_id', Auth::user()->company_id)
            ->firstOrFail();

        $commission->update([
            'status' => 'paid',
            'paid_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Comissão marcada como paga!']);
    }
}
