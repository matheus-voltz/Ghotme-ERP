@php
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/commonMaster')

@section('title', 'Agendamento Online - ' . $company->name)

@section('page-style')
<style>
    body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
    .booking-card { max-width: 500px; margin: 3rem auto; border: none; border-radius: 1.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .btn-booking { background: linear-gradient(135deg, #7367f0 0%, #4831d4 100%); border: none; padding: 1rem; font-weight: 600; border-radius: 0.75rem; color: white; }
    .form-control { border-radius: 0.75rem; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; }
</style>
@endsection

@section('layoutContent')
<div class="container px-4">
    <div class="card booking-card">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-5">
                @if($company->logo_path)
                <img src="{{ asset('storage/' . $company->logo_path) }}" alt="Logo" class="mb-4" style="max-height: 60px;">
                @endif
                <h3 class="fw-bold mb-1">Agende seu serviço</h3>
                <p class="text-muted">{{ $company->name }}</p>
            </div>

            @if(session('success'))
            <div class="alert alert-success p-4 rounded-4 text-center">
                <i class="ti tabler-circle-check fs-2 d-block mb-3"></i>
                <h5 class="fw-bold">{{ session('success') }}</h5>
            </div>
            @else
            <form action="{{ route('public.booking.store', $company->slug) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nome Completo</label>
                    <input type="text" name="customer_name" class="form-control" placeholder="Seu nome aqui" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Celular (WhatsApp)</label>
                    <input type="text" name="customer_phone" class="form-control" placeholder="(00) 00000-0000" required>
                </div>
                <div class="row mb-3 g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ niche('identifier', 'Identificador', $company) }}</label>
                        <input type="text" name="vehicle_plate" class="form-control" placeholder="{{ niche('identifier', 'Placa', $company) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Data e Hora</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Qual o problema / serviço?</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Ex: Revisão de 20k km, barulho na suspensão..."></textarea>
                </div>
                <button type="submit" class="btn btn-booking w-100 mb-3 shadow">Solicitar Agendamento <i class="ti tabler-send ms-1"></i></button>
                <p class="text-center text-muted small">Nossa equipe responderá sua solicitação em poucos minutos.</p>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
