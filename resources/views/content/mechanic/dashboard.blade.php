@extends('layouts/contentNavbarLayout')

@section('title', 'Painel do Mecânico')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Oficina /</span> Meus Serviços</h4>
        </div>
        <div class="col-auto">
            <span class="badge bg-label-primary rounded-pill">{{ count($orders) }} Ativos</span>
        </div>
    </div>

    @forelse($orders as $order)
    <div class="card mb-4 border-0 shadow-sm" style="border-radius: 1rem; overflow: hidden;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1 fw-bold text-dark">OS #{{ $order->id }}</h5>
                    <p class="mb-0 text-muted small"><i class="ti tabler-calendar me-1"></i> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                </div>
                @php
                $statusClass = 'bg-label-primary';
                $statusText = $order->status;
                switch($order->status) {
                case 'pending': $statusClass = 'bg-label-secondary'; $statusText = 'Pendente'; break;
                case 'approved': $statusClass = 'bg-label-success'; $statusText = 'Aprovado'; break;
                case 'in_progress': $statusClass = 'bg-label-primary'; $statusText = 'Em Andamento'; break;
                case 'testing': $statusClass = 'bg-label-info'; $statusText = 'Em Teste'; break;
                case 'cleaning': $statusClass = 'bg-label-warning'; $statusText = 'Lavagem'; break;
                }
                @endphp
                <span class="badge {{ $statusClass }} rounded-pill px-3 py-2">{{ $statusText }}</span>
            </div>

            <div class="d-flex align-items-center mb-4 p-3 bg-label-secondary rounded-3">
                <div class="avatar avatar-md me-3">
                    <span class="avatar-initial rounded-circle bg-white text-secondary"><i class="ti tabler-car fs-4"></i></span>
                </div>
                <div>
                    <h6 class="mb-0 text-dark">{{ $order->veiculo->modelo ?? 'Veículo não informado' }}</h6>
                    <small class="text-muted">{{ $order->veiculo->placa ?? '' }}</small>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                <div class="d-flex align-items-center">
                    <div class="avatar-group me-2">
                        <div class="avatar avatar-xs" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $order->client->name }}">
                            <span class="avatar-initial rounded-circle bg-primary text-white">{{ substr($order->client->name ?? 'C', 0, 1) }}</span>
                        </div>
                    </div>
                    <small class="text-muted">{{ explode(' ', $order->client->name ?? 'Cliente')[0] }}</small>
                </div>

                <a href="{{ route('mechanic.os.show', $order->uuid) }}" class="btn btn-primary rounded-pill px-4">
                    Abrir OS <i class="ti tabler-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
        <!-- Progress Bar (Fake for now or calculated) -->
        <div class="progress" style="height: 4px;">
            <div class="progress-bar bg-primary" role="progressbar" style="width: 50%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>
    @empty
    <div class="text-center py-5">
        <div class="mb-3">
            <span class="avatar avatar-xl rounded-circle bg-label-secondary">
                <i class="ti tabler-check fs-1"></i>
            </span>
        </div>
        <h5 class="text-muted">Tudo limpo por aqui!</h5>
        <p class="text-muted small">Nenhuma ordem de serviço pendente para você.</p>
    </div>
    @endforelse

</div>
@endsection