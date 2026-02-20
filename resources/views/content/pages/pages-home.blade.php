@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Home')

@section('content')
@if(auth()->user()->isTrialExpired())
<div class="row justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-md-8 text-center">
        <div class="card shadow-lg border-0">
            <div class="card-body p-12">
                <div class="mb-8">
                    <div class="avatar avatar-xl mx-auto mb-4" style="width: 100px; height: 100px;">
                        <span class="avatar-initial rounded-circle bg-label-primary">
                            <i class="ti tabler-rocket icon-50px"></i>
                        </span>
                    </div>
                </div>
                <h2 class="mb-4 fw-extrabold">Seu período de teste chegou ao fim 🚀</h2>
                <p class="fs-5 text-muted mb-8">
                    Esperamos que o <strong>{{ config('variables.templateName') }}</strong> tenha ajudado a organizar sua gestão nestes últimos 30 dias. <br>
                    Para continuar acessando suas <strong>obras, relatórios (RDO) e financeiro</strong>, escolha um dos nossos planos.
                </p>
                
                <div class="row g-4 mb-8 text-start justify-content-center">
                    <div class="col-md-5">
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti tabler-check text-success me-2"></i>
                            <span class="fw-medium">Todos os seus dados estão salvos</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti tabler-check text-success me-2"></i>
                            <span class="fw-medium">Suporte prioritário via WhatsApp</span>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti tabler-check text-success me-2"></i>
                            <span class="fw-medium">Liberação imediata pós-pagamento</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti tabler-check text-success me-2"></i>
                            <span class="fw-medium">Acesso total ao aplicativo mobile</span>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                    <a href="{{ route('settings') }}" class="btn btn-primary btn-lg px-8 shadow-primary">
                        Ver Planos e Assinar
                        <i class="ti tabler-arrow-right ms-2"></i>
                    </a>
                </div>
                
                <p class="mt-8 mb-0 text-muted small">
                    Dúvidas? <a href="{{ route('support.whatsapp') }}" class="text-primary fw-bold">Fale com nosso suporte técnico</a>
                </p>
            </div>
        </div>
    </div>
</div>
@else
<h4>Home Page</h4>
<p>For more layout options refer <a href="{{ config('variables.documentation') ? config('variables.documentation').'/laravel-introduction.html' : '#' }}" target="_blank" rel="noopener noreferrer">documentation</a>.</p>
@endif
@endsection
