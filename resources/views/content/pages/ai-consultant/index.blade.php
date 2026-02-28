@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Consultor IA - Ghotme')

@section('content')
<div class="row h-100 justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-md-6 text-center">
        <div class="mb-4">
            <div class="avatar avatar-xl mx-auto mb-3" style="width: 100px; height: 100px;">
                <span class="avatar-initial rounded-circle bg-label-primary">
                    <i class="ti tabler-robot fs-1"></i>
                </span>
            </div>
            <h3 class="mb-2">Olá! Eu sou seu Consultor Estratégico.</h3>
            <p class="text-muted">Estou aqui para analisar seus dados financeiros, ordens de serviço e estoque para te ajudar a tomar as melhores decisões para o seu negócio.</p>
        </div>

        @if(count($chats) > 0)
        <div class="card mb-4">
            <div class="card-header">Suas consultas recentes</div>
            <div class="list-group list-group-flush">
                @foreach($chats->take(3) as $c)
                <a href="{{ route('ai-consultant.show', $c->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between">
                    <span>{{ $c->title }}</span>
                    <small>{{ $c->updated_at->diffForHumans() }}</small>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <a href="{{ route('ai-consultant.create') }}" class="btn btn-primary btn-lg">
            <i class="ti tabler-plus me-2"></i> Iniciar Nova Consulta
        </a>
    </div>
</div>
@endsection