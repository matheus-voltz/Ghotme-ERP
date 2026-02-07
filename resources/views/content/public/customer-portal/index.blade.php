@php
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/layoutPublic')

@section('title', 'Portal do Cliente')

@section('page-style')
<!-- Google Fonts: Inter -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --portal-primary: #7367f0;
        --portal-bg: #f8f7fa;
        --portal-card-shadow: 0 10px 30px rgba(115, 103, 240, 0.08);
    }

    [data-bs-theme="dark"] {
        --portal-bg: #2f3349;
    }

    body {
        background-color: var(--portal-bg) !important;
        font-family: 'Inter', sans-serif !important;
    }

    .portal-header {
        background: linear-gradient(135deg, #7367f0 0%, #4831d4 100%), url('https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?auto=format&fit=crop&q=80&w=2000');
        background-blend-mode: overlay;
        background-size: cover;
        background-position: center;
        padding: 5rem 0 7rem;
        border-radius: 0 0 3.5rem 3.5rem;
        box-shadow: 0 15px 50px rgba(115, 103, 240, 0.25);
    }

    .portal-header::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 100%;
        background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.1) 100%);
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 1.5rem;
        box-shadow: var(--portal-card-shadow);
    }

    .stat-card {
        padding: 1.5rem;
        text-align: center;
        margin-top: -4rem;
        transition: transform 0.3s ease;
        border: none;
        background: var(--bs-card-bg, white);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
        border-radius: 1.25rem;
    }

    .stat-card:hover {
        transform: translateY(-8px);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        line-height: 50px;
        border-radius: 50%;
        margin: 0 auto 1rem;
        font-size: 1.5rem;
    }

    .vehicle-card {
        border: 1px solid rgba(115, 103, 240, 0.1);
        background: var(--bs-card-bg, white);
        border-radius: 1.5rem;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .vehicle-card:hover {
        box-shadow: 0 20px 40px rgba(115, 103, 240, 0.12);
        border-color: var(--portal-primary);
    }

    .status-timeline-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        position: relative;
        z-index: 2;
    }

    .status-timeline-line {
        height: 4px;
        background: #eee;
        flex-grow: 1;
        margin: 0 -2px;
        position: relative;
        top: 4px;
    }

    .whatsapp-fab {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 65px;
        height: 65px;
        background: #25d366;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        box-shadow: 0 10px 30px rgba(37, 211, 102, 0.3);
        z-index: 1000;
        transition: all 0.3s ease;
    }

    .whatsapp-fab:hover {
        transform: scale(1.1) rotate(10deg);
        color: white;
    }

    .theme-toggle-btn {
        position: absolute;
        top: 2rem;
        right: 2rem;
        padding: 0.75rem;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        cursor: pointer;
        transition: all 0.2s;
        z-index: 100;
    }

    .theme-toggle-btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .quick-action-link {
        padding: 1.25rem;
        border-radius: 1.25rem;
        background: var(--bs-card-bg, white);
        border: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        transition: all 0.2s ease;
        text-decoration: none;
        color: #444;
    }

    .quick-action-link:hover {
        background: #fdfdff;
        border-color: var(--portal-primary);
        color: var(--portal-primary);
    }

    .animate-up {
        animation: fadeInUp 0.6s both;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection

@section('content')
<!-- Header Estilizado -->
<div class="portal-header d-flex align-items-center">
    <div class="container text-center py-4">
        <h1 class="text-white fw-bold mb-3 animate-up">Olá, {{ explode(' ', $client->name)[0] }}! 👋</h1>
        <p class="text-white fs-5 opacity-75 animate-up" style="animation-delay: 0.1s">
            Acompanhe o cuidado com o seu veículo de qualquer lugar.
        </p>
    </div>
</div>

<div class="container portal-wrapper">
    <!-- Cards de Resumo -->
    <div class="row g-4 mb-5 justify-content-center">
        <div class="col-md-3 col-6 animate-up" style="animation-delay: 0.2s">
            <div class="stat-card shadow-sm">
                <div class="stat-icon bg-label-primary">
                    <i class="ti tabler-car"></i>
                </div>
                <h4 class="mb-0 fw-bold">{{ $orders->whereIn('status', ['pending', 'in_progress', 'testing', 'cleaning'])->count() }}</h4>
                <small class="text-muted">Em Serviço</small>
            </div>
        </div>
        <div class="col-md-3 col-6 animate-up" style="animation-delay: 0.3s">
            <div class="stat-card shadow-sm">
                <div class="stat-icon bg-label-warning">
                    <i class="ti tabler-file-invoice"></i>
                </div>
                <h4 class="mb-0 fw-bold">{{ $budgets->where('status', 'pending')->count() }}</h4>
                <small class="text-muted">Orçamentos</small>
            </div>
        </div>
        <div class="col-md-3 d-none d-md-block animate-up" style="animation-delay: 0.4s">
            <div class="stat-card shadow-sm">
                <div class="stat-icon bg-label-success">
                    <i class="ti tabler-history"></i>
                </div>
                <h4 class="mb-0 fw-bold">{{ $orders->where('status', 'completed')->count() }}</h4>
                <small class="text-muted">Finalizados</small>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Coluna Principal (8) -->
        <div class="col-lg-8 animate-up" style="animation-delay: 0.5s">
            <!-- Seção de Veículos -->
            <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
                <h4 class="mb-0 fw-bold"><i class="ti tabler-car me-2 text-primary"></i>Meus Veículos</h4>
            </div>

            <div class="row g-4 mb-5">
                @forelse($client->vehicles as $vehicle)
                <div class="col-md-6">
                    <div class="vehicle-card p-3 shadow-none border h-100">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-label-secondary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                <i class="ti tabler-car-suv fs-3"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="mb-0 fw-bold">{{ $vehicle->modelo }}</h6>
                                    @php
                                    // Verificar se há OS em andamento para este veículo
                                    $currentOS = $orders->where('veiculo_id', $vehicle->id)
                                    ->whereIn('status', ['pending', 'in_progress', 'testing', 'cleaning', 'awaiting_approval'])
                                    ->first();

                                    $statusClass = 'bg-label-success';
                                    $statusText = 'Disponível';

                                    if ($currentOS) {
                                    $statusClass = 'bg-label-warning';
                                    $statusText = 'Na Oficina';
                                    if ($currentOS->status == 'completed') {
                                    $statusClass = 'bg-label-info';
                                    $statusText = 'Pronto';
                                    }
                                    }
                                    @endphp
                                    <span class="badge {{ $statusClass }} rounded-pill">{{ $statusText }}</span>
                                </div>
                                <div class="d-flex gap-2 mt-1">
                                    <small class="text-uppercase fw-medium badge bg-dark text-white p-1 px-2" style="font-size: 0.7rem;">{{ $vehicle->placa }}</small>
                                    <small class="text-muted">{{ $vehicle->marca }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-4 bg-white rounded-4 border">
                    <p class="text-muted mb-0 small">Nenhum veículo cadastrado.</p>
                </div>
                @endforelse
            </div>

            <div class="d-flex align-items-center justify-content-between mb-4">
                <h4 class="mb-0 fw-bold"><i class="ti tabler-tool me-2 text-primary"></i>Status na Oficina</h4>
            </div>

            @forelse($orders->whereIn('status', ['pending', 'in_progress', 'testing', 'cleaning', 'completed']) as $order)
            <div class="vehicle-card p-4 mb-4 shadow-sm border-0">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-3 bg-label-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                <i class="ti tabler-car-suv fs-2"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-bold">{{ $order->veiculo->marca }} {{ $order->veiculo->modelo }}</h5>
                                <div class="d-flex gap-2 align-items-center">
                                    <span class="badge bg-label-secondary text-uppercase">{{ $order->veiculo->placa }}</span>
                                    <small class="text-muted"><i class="ti tabler-calendar-event me-1"></i>Início: {{ $order->created_at->format('d/m/Y') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 text-md-end">
                        @php
                        $statusClass = 'bg-label-primary';
                        $statusText = $order->status;
                        switch($order->status) {
                        case 'pending': $statusClass = 'bg-label-warning'; $statusText = 'Aguardando Início'; break;
                        case 'awaiting_approval': $statusClass = 'bg-label-danger'; $statusText = 'Aguardando Aprovação'; break;
                        case 'in_progress': $statusClass = 'bg-label-info'; $statusText = 'Em Manutenção'; break;
                        case 'testing': $statusClass = 'bg-label-primary'; $statusText = 'Testes Finais'; break;
                        case 'cleaning': $statusClass = 'bg-label-info'; $statusText = 'Higienização'; break;
                        case 'completed': $statusClass = 'bg-label-success'; $statusText = 'Pronto para Retirada'; break;
                        case 'paid': $statusClass = 'bg-label-success'; $statusText = 'Pago / Finalizado'; break;
                        }
                        @endphp
                        <span class="badge {{ $statusClass }} p-2 px-3 fs-6">
                            {{ $statusText }}
                        </span>
                    </div>
                </div>

                <!-- Barra de Progresso Visual -->
                <div class="mt-4 px-2">
                    @php
                    $perc = 0;
                    $statusMap = [
                    'pending' => 10,
                    'awaiting_approval' => 25,
                    'in_progress' => 50,
                    'testing' => 75,
                    'cleaning' => 90,
                    'completed' => 100,
                    'paid' => 100
                    ];
                    $perc = $statusMap[$order->status] ?? 0;
                    @endphp
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-muted fw-bold">Evolução do Serviço</small>
                        <small class="text-primary fw-bold">{{ $perc }}%</small>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 10px; background: #f0f0f5;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                            role="progressbar" style="width: {{ $perc }}%"></div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                    <div class="avatar-group d-none d-sm-flex">
                        <small class="text-muted"><i class="ti tabler-user-check me-1"></i>Consultor: {{ explode(' ', $order->user->name ?? 'Equipe Ghotme')[0] }}</small>
                    </div>
                    <a href="{{ route('customer.portal.order', $order->uuid) }}" class="btn btn-primary px-4 rounded-pill">
                        Detalhes <i class="ti tabler-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            @empty
            <div class="text-center py-5 glass-card mb-4 mt-2">
                <i class="ti tabler-ghost display-3 text-muted opacity-25"></i>
                <h5 class="mt-3 text-muted">A oficina está tranquila hoje.</h5>
                <p class="text-muted small">Nenhum serviço em andamento para seus veículos.</p>
            </div>
            @endforelse
        </div>

        <!-- Sidebar (4) -->
        <div class="col-lg-4 animate-up" style="animation-delay: 0.7s">
            <!-- Orçamentos Pendentes -->
            <div class="mb-5">
                <h5 class="fw-bold mb-4">Orçamentos Pendentes</h5>
                @forelse($budgets->where('status', 'pending') as $budget)
                <a href="{{ route('public.budget.show', $budget->uuid) }}" class="quick-action-link shadow-sm">
                    <div class="p-2 bg-label-warning rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="ti tabler-currency-real"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="mb-0 fw-bold">Orçamento #{{ $budget->id }}</p>
                        <small class="text-muted">Total: R$ {{ number_format($budget->total, 2, ',', '.') }}</small>
                    </div>
                    <i class="ti tabler-chevron-right opacity-25"></i>
                </a>
                @empty
                <div class="p-4 rounded-4 bg-white text-center border">
                    <i class="ti tabler-circle-check text-success fs-1"></i>
                    <p class="mb-0 mt-2 small text-muted">Nenhum orçamento pendente.</p>
                </div>
                @endforelse
            </div>

            <!-- Histórico -->
            <div class="mb-4">
                <h5 class="fw-bold mb-4">Finalizados Recente</h5>
                <div class="glass-card p-2">
                    @forelse($orders->where('status', 'completed')->take(3) as $history)
                    <div class="p-3 @if(!$loop->last) border-bottom @endif">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">{{ $history->veiculo->modelo }}</span>
                            <small class="text-muted">{{ $history->created_at->format('d/m/y') }}</small>
                        </div>
                        <small class="badge bg-label-success mt-1">Concluído</small>
                    </div>
                    @empty
                    <div class="p-4 text-center opacity-50">
                        <small>Sem histórico disponível.</small>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Card de Apoio -->
            <div class="card bg-dark border-0 rounded-4 overflow-hidden mt-5">
                <div class="card-body p-4 position-relative">
                    <i class="ti tabler-help fs-1 text-white opacity-10" style="position: absolute; right: 10px; top: 10px;"></i>
                    <h5 class="text-white mb-3">Precisa de Ajuda?</h5>
                    <p class="text-white opacity-75 small mb-4">Sua oficina de confiança está sempre à disposição para esclarecer dúvidas.</p>
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $client->company->phone ?? '') }}" target="_blank" class="btn btn-primary w-100 rounded-pill">
                        Falar com Atendente
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- WhatsApp Floating Button -->
<a href="https://wa.me/{{ preg_replace('/\D/', '', $client->company->phone ?? '') }}" target="_blank" class="whatsapp-fab animate-up">
    <i class="ti tabler-brand-whatsapp"></i>
</a>
@endsection