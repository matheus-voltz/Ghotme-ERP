@extends('layouts/contentNavbarLayout')

@section('title', 'Portal do Funcionário')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header Otimizado -->
    <div class="row align-items-center mb-4">
        <div class="col-12 col-md-6">
            <h4 class="fw-bold mb-1">Olá, {{ explode(' ', auth()->user()->name)[0] }}! 👋</h4>
            <p class="text-muted mb-0">Você tem <span class="text-primary fw-semibold">{{ count($orders) }} ordens de serviço</span> ativas hoje.</p>
        </div>
        <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
            <div class="dropdown">
                <button class="btn btn-label-secondary dropdown-toggle" type="button" id="employeeFilter" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="icon-base ti tabler-filter me-1"></i> Filtrar Status
                </button>
                <div class="dropdown-menu" aria-labelledby="employeeFilter">
                    <a class="dropdown-item" href="javascript:void(0);">Todos</a>
                    <a class="dropdown-item" href="javascript:void(0);">Em Andamento</a>
                    <a class="dropdown-item" href="javascript:void(0);">Aguardando Teste</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Resumo Rápidos -->
    <div class="row mb-4 g-3">
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar bg-label-primary rounded p-2 me-3 d-flex align-items-center justify-content-center">
                        <i class="icon-base ti tabler-clipboard-list fs-2 text-primary"></i>
                    </div>
                    <div class="card-info">
                        <h4 class="mb-0 fw-bold">{{ count($orders) }}</h4>
                        <small class="text-muted">Ativas</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar bg-label-success rounded p-2 me-3 d-flex align-items-center justify-content-center">
                        <i class="icon-base ti tabler-circle-check fs-2 text-success"></i>
                    </div>
                    <div class="card-info">
                        <h4 class="mb-0 fw-bold">{{ $finalizedCount }}</h4>
                        <small class="text-muted">Finalizadas</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar bg-label-info rounded p-2 me-3 d-flex align-items-center justify-content-center">
                        <i class="icon-base ti tabler-player-play fs-2 text-info"></i>
                    </div>
                    <div class="card-info">
                        <h4 class="mb-0 fw-bold">{{ $productionTime }}</h4>
                        <small class="text-muted">Produção</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar bg-label-warning rounded p-2 me-3 d-flex align-items-center justify-content-center">
                        <i class="icon-base ti tabler-clock-pause fs-2 text-warning"></i>
                    </div>
                    <div class="card-info">
                        <h4 class="mb-0 fw-bold">{{ $pausedCount }}</h4>
                        <small class="text-muted">Pausadas</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h5 class="mb-3 fw-bold"><i class="icon-base ti tabler-list me-2 text-primary"></i>Sua Lista de Trabalho</h5>

    @forelse($orders as $order)
    <div class="card mb-4 border-0 shadow-sm overflow-hidden" style="border-radius: 1.25rem;">
        <div class="card-body p-0">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-label-secondary me-2">
                            <span class="avatar-initial rounded-circle text-heading fw-bold">#{{ $order->id }}</span>
                        </div>
                        <span class="text-muted small"><i class="icon-base ti tabler-calendar-event me-1"></i>{{ $order->created_at->format('d/m') }}</span>
                    </div>
                    @php
                    $statusClass = 'bg-label-primary';
                    $statusText = $order->status;
                    switch($order->status) {
                        case 'pending': $statusClass = 'bg-label-secondary'; $statusText = 'Pendente'; break;
                        case 'approved': $statusClass = 'bg-label-success'; $statusText = 'Aprovado'; break;
                        case 'in_progress': $statusClass = 'bg-label-primary'; $statusText = 'Em Execução'; break;
                        case 'testing': $statusClass = 'bg-label-info'; $statusText = 'Em Teste'; break;
                        case 'cleaning': $statusClass = 'bg-label-warning'; $statusText = (niche('current') == 'pet' ? 'Banho/Tosa' : 'Limpeza'); break;
                        case 'canceled': $statusClass = 'bg-label-danger'; $statusText = 'Cancelada'; break;
                    }
                    @endphp
                    <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill">{{ $statusText }}</span>
                </div>

                <div class="row align-items-center">
                    <div class="col-8">
                        <h5 class="mb-1 fw-bold text-dark">{{ $order->veiculo->modelo ?? niche('entity') . ' não informado' }}</h5>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border-0"><i class="icon-base ti {{ niche_icon('entity') }} me-1 icon-xs"></i> {{ $order->veiculo->placa ?? 'S/P' }}</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary border-0"><i class="icon-base ti tabler-user me-1 icon-xs"></i> {{ explode(' ', $order->client->name ?? 'Cliente')[0] }}</span>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <a href="{{ route('employee.os.show', $order->uuid) }}" class="btn btn-icon btn-primary rounded-circle shadow-primary p-4" style="width: 50px; height: 50px;">
                            <i class="icon-base ti tabler-player-play fs-3"></i>
                        </a>
                    </div>
                </div>

                @if($order->items->count() > 0)
                <div class="mt-2 border-top pt-3">
                    <p class="mb-2 small text-muted fw-medium">SERVIÇOS NESTA OS:</p>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($order->items->take(3) as $item)
                            <span class="badge bg-label-secondary small border-0">{{ $item->service->name ?? 'Item' }}</span>
                        @endforeach
                        @if($order->items->count() > 3)
                            <span class="badge bg-label-secondary small border-0">+{{ $order->items->count() - 3 }}</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Barra de Progresso Real (Calculada) -->
            @php
                $totalItems = $order->items->count();
                $completedItems = $order->items->where('status', 'completed')->count();
                $percent = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
            @endphp
            <div class="progress" style="height: 6px; background-color: #f1f1f1;">
                <div class="progress-bar bg-primary shadow-none" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>
    @empty
    <div class="card border-0 shadow-none bg-transparent">
        <div class="card-body text-center py-5">
            <img src="{{ asset('assets/img/illustrations/page-pricing-standard.png') }}" alt="No tasks" class="img-fluid mb-4" width="150">
            <h5 class="fw-bold">Tudo em ordem por aqui!</h5>
            <p class="text-muted small">Você não tem nenhuma ordem de serviço pendente no momento.</p>
            <button class="btn btn-primary rounded-pill px-4 mt-2">Atualizar Lista</button>
        </div>
    </div>
    @endforelse

</div>

<style>
    .shadow-primary {
        box-shadow: 0 0.5rem 1rem rgba(115, 103, 240, 0.3) !important;
    }
    .bg-label-secondary {
        background-color: #f1f1f2 !important;
        color: #444050 !important;
    }
    .text-heading {
        color: #444050;
    }
    .card:hover {
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
</style>
@endsection
