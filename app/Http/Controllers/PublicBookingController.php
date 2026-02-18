<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicBookingController extends Controller
{
    public function show(Request $request, $slug)
    {
        $company = Company::where('slug', $slug)->firstOrFail();
        return view('content.public.booking', compact('company'));
    }

    public function store(Request $request, $slug)
    {
        $company = Company::where('slug', $slug)->firstOrFail();

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string',
            'scheduled_at' => 'required|date|after:now',
            'service_type' => 'nullable|string',
        ]);

        $appointment = Appointment::create([
            'company_id' => $company->id,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'vehicle_plate' => $request->vehicle_plate,
            'service_type' => $request->service_type,
            'scheduled_at' => $request->scheduled_at,
            'notes' => $request->notes,
            'token' => (string) Str::uuid(),
        ]);

        return back()->with('success', 'Agendamento solicitado com sucesso! Nossa equipe entrará em contato para confirmar.');
    }
}
