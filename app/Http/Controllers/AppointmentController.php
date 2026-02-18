<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\OrdemServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index()
    {
        $companyId = Auth::user()->company_id;
        
        $pending = Appointment::where('company_id', $companyId)->where('status', 'pending')->orderBy('scheduled_at', 'asc')->get();
        $confirmed = Appointment::where('company_id', $companyId)->where('status', 'confirmed')->orderBy('scheduled_at', 'desc')->limit(10)->get();
        $cancelled = Appointment::where('company_id', $companyId)->where('status', 'cancelled')->orderBy('scheduled_at', 'desc')->limit(10)->get();

        return view('content.appointments.index', compact('pending', 'confirmed', 'cancelled'));
    }

    public function confirm($id)
    {
        $appointment = Appointment::where('id', $id)
            ->where('company_id', Auth::user()->company_id)
            ->firstOrFail();

        $appointment->update(['status' => 'confirmed']);

        return back()->with('success', 'Agendamento confirmado com sucesso!');
    }

    public function cancel($id)
    {
        $appointment = Appointment::where('id', $id)
            ->where('company_id', Auth::user()->company_id)
            ->firstOrFail();

        $appointment->update(['status' => 'cancelled']);

        return back()->with('success', 'Agendamento cancelado.');
    }
}
