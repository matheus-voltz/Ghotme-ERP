@extends('layouts/layoutMaster')

@section('title', 'Acesso Restrito - Logs de Erro')

@section('page-style')
<style>
    .auth-dev-card {
        max-width: 450px;
        margin: 4rem auto;
        border: none;
        border-radius: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .auth-dev-header {
        background: linear-gradient(135deg, #7367f0 0%, #a89af9 100%);
        padding: 3rem 2rem;
        text-align: center;
        color: white;
    }

    .auth-dev-header i {
        font-size: 4rem;
        margin-bottom: 1rem;
        display: block;
        filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.2));
    }

    .auth-dev-body {
        padding: 2.5rem;
        background: white;
    }

    .password-input-group {
        position: relative;
    }

    .password-input-group i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #aab3c3;
    }

    .password-input-group input {
        padding-left: 3rem;
        height: 3.5rem;
        border-radius: 0.75rem;
        border: 2px solid #f0f2f4;
        transition: all 0.3s ease;
    }

    .password-input-group input:focus {
        border-color: #7367f0;
        box-shadow: 0 4px 12px rgba(115, 103, 240, 0.1);
    }

    .btn-dev-auth {
        height: 3.5rem;
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 1rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        background: #7367f0;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-dev-auth:hover {
        background: #5e50ee;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(115, 103, 240, 0.3);
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="auth-dev-card">
        <div class="auth-dev-header">
            <i class="ti tabler-shield-lock"></i>
            <h3 class="text-white mb-1 fw-bold">Modo Desenvolvedor</h3>
            <p class="mb-0 opacity-75">Área restrita para diagnóstico do sistema</p>
        </div>
        <div class="auth-dev-body">
            @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                <i class="ti tabler-alert-circle me-2"></i>
                <div>{{ session('error') }}</div>
            </div>
            @endif

            <form action="{{ route('settings.system-errors.auth') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark mb-2">Senha de Acesso</label>
                    <div class="password-input-group">
                        <i class="ti tabler-key"></i>
                        <input type="password" name="password" class="form-control" placeholder="Digite a chave mestre..." required autofocus>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-dev-auth">
                    Desbloquear Logs
                </button>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('dashboard') }}" class="text-muted small">
                    <i class="ti tabler-arrow-left me-1"></i> Voltar ao Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection