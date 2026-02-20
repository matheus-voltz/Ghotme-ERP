<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaintenanceContract;
use App\Models\Clients;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MaintenanceContractController extends Controller
{
    public function index()
    {
        $contracts = MaintenanceContract::with('client')->orderBy('created_at', 'desc')->get();
        $clients = Clients::orderBy('name')->get();
        
        return view('content.pages.maintenance-contracts.index', compact('contracts', 'clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'billing_day' => 'required|integer|min:1|max:31',
            'start_date' => 'required|date'
        ]);

        $billingDay = (int) $request->billing_day;
        $nextDate = Carbon::parse($request->start_date);
        
        if ($nextDate->day > $billingDay) {
            $nextDate->addMonth()->day($billingDay);
        } else {
            $nextDate->day($billingDay);
        }

        MaintenanceContract::create([
            'company_id' => Auth::user()->company_id,
            'client_id' => $request->client_id,
            'title' => $request->title,
            'amount' => $request->amount,
            'billing_day' => $request->billing_day,
            'start_date' => $request->start_date,
            'next_billing_date' => $nextDate,
            'frequency' => $request->frequency ?? 'monthly',
            'auto_generate_os' => $request->has('auto_generate_os'),
            'status' => 'active'
        ]);

        return redirect()->back()->with('success', 'Contrato de manutenção criado com sucesso!');
    }

    public function destroy($id)
    {
        MaintenanceContract::where('id', $id)->where('company_id', Auth::user()->company_id)->delete();
        return redirect()->back()->with('success', 'Contrato removido.');
    }
}
