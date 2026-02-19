<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\OrdemServico;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AppointmentController extends Controller
{
    /**
     * Exibe o formulário público de agendamento para uma empresa específica
     */
    public function showBookingForm($slug)
    {
        $company = Company::where('slug', $slug)->firstOrFail();
        return view('content.public.booking.form', compact('company'));
    }

    /**
     * Processa o envio do formulário de agendamento
     */
    public function submitBooking(Request $request, $slug)
    {
        $company = Company::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'vehicle_plate' => 'nullable|string|max:20',
            'scheduled_at' => 'required|date|after:now',
            'notes' => 'nullable|string|max:1000',
        ]);

        $appointment = Appointment::create([
            'company_id' => $company->id,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'vehicle_plate' => $validated['vehicle_plate'],
            'scheduled_at' => $validated['scheduled_at'],
            'notes' => $validated['notes'],
            'token' => Str::random(10),
            'status' => 'pending'
        ]);

        return redirect()->back()->with('booking_success', 'Seu agendamento foi enviado com sucesso! Aguarde nossa confirmação.');
    }

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

        // Criar evento no calendário automaticamente
        \App\Models\Event::create([
            'company_id' => $appointment->company_id,
            'user_id' => Auth::id(),
            'title' => "Agendamento: " . $appointment->customer_name,
            'start' => $appointment->scheduled_at,
            'end' => \Carbon\Carbon::parse($appointment->scheduled_at)->addHour(),
            'calendar' => 'Business',
            'description' => "Serviço: " . ($appointment->service_type ?? 'Não especificado') . "\nObs: " . $appointment->notes,
            'location' => 'Sede da Empresa',
            'all_day' => false
        ]);

        return back()->with('success', 'Agendamento confirmado e adicionado ao calendário!');
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
