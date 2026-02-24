@extends('layouts/contentNavbarLayout')

@section('title', 'Execução OS #' . $order->id)

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        setInterval(updateTimers, 1000);

        function updateTimers() {
            document.querySelectorAll('.timer-display').forEach(timer => {
                if (timer.dataset.status === 'in_progress') {
                    let seconds = parseInt(timer.dataset.seconds) + 1;
                    timer.dataset.seconds = seconds;
                    timer.textContent = formatTime(seconds);
                }
            });
        }

        function formatTime(seconds) {
            seconds = Math.floor(seconds);
            const h = Math.floor(seconds / 3600).toString().padStart(2, '0');
            const m = Math.floor((seconds % 3600) / 60).toString().padStart(2, '0');
            const s = (seconds % 60).toString().padStart(2, '0');
            return `${h}:${m}:${s}`;
        }

        window.toggleTimer = async function(itemId, btn) {
            try {
                btn.disabled = true;
                const response = await fetch(`/employee/timer/${itemId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) throw new Error('Falha na comunicação');
                const data = await response.json();

                const timerDisplay = document.getElementById(`timer-${itemId}`);
                const statusBadge = document.getElementById(`status-${itemId}`);
                const card = btn.closest('.service-card');

                if (data.status === 'in_progress') {
                    btn.innerHTML = '<i class="icon-base ti tabler-player-pause fs-3"></i>';
                    btn.className = 'btn btn-warning btn-icon rounded-circle shadow-warning p-4';
                    timerDisplay.dataset.status = 'in_progress';
                    statusBadge.className = 'badge bg-label-info rounded-pill';
                    statusBadge.textContent = 'Em Andamento';
                    card.classList.add('border-primary');
                } else {
                    btn.innerHTML = '<i class="icon-base ti tabler-player-play fs-3"></i>';
                    btn.className = 'btn btn-success btn-icon rounded-circle shadow-success p-4';
                    timerDisplay.dataset.status = 'paused';
                    timerDisplay.dataset.seconds = data.elapsed_time;
                    timerDisplay.textContent = formatTime(data.elapsed_time);
                    statusBadge.className = 'badge bg-label-warning rounded-pill';
                    statusBadge.textContent = 'Pausado';
                    card.classList.remove('border-primary');
                }
            } catch (error) {
                console.error(error);
                alert('Erro ao processar. Tente novamente.');
            } finally {
                btn.disabled = false;
            }
        };

        window.completeItem = async function(itemId, btn) {
            if (!confirm('Deseja finalizar este serviço?')) return;
            try {
                btn.disabled = true;
                const response = await fetch(`/employee/complete/${itemId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                if (!response.ok) throw new Error('Falha na comunicação');
                location.reload();
            } catch (error) {
                alert('Erro ao finalizar.');
                btn.disabled = false;
            }
        };
    });
</script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Topo Mobile Friendly -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('employee.dashboard') }}" class="btn btn-icon btn-label-secondary rounded-circle me-3">
                <i class="icon-base ti tabler-chevron-left"></i>
            </a>
            <div>
                <h4 class="fw-bold mb-0">Ordem de Serviço #{{ $order->id }}</h4>
                <small class="text-muted"><i class="icon-base ti tabler-calendar me-1"></i>{{ $order->created_at->format('d/m/Y H:i') }}</small>
            </div>
        </div>
        @php
            $statusClass = 'bg-label-primary';
            $statusTranslated = $order->status;
            switch(strtolower($order->status)) {
                case 'approved': $statusClass = 'bg-label-success'; $statusTranslated = 'Aprovado'; break;
                case 'in_progress': $statusClass = 'bg-label-primary'; $statusTranslated = 'Em Execução'; break;
                case 'testing': $statusClass = 'bg-label-info'; $statusTranslated = 'Em Teste'; break;
                case 'pending': $statusClass = 'bg-label-warning'; $statusTranslated = 'Pendente'; break;
                case 'completed': $statusClass = 'bg-label-success'; $statusTranslated = 'Concluído'; break;
                case 'cleaning': $statusClass = 'bg-label-warning'; $statusTranslated = (niche('current') == 'pet' ? 'Banho/Tosa' : 'Limpeza'); break;
                case 'canceled': $statusClass = 'bg-label-danger'; $statusTranslated = 'Cancelada'; break;
            }
        @endphp
        <span class="badge {{ $statusClass }} rounded-pill px-3 py-2 fw-bold">{{ $statusTranslated }}</span>
    </div>

    <!-- Info Card Modernizado -->
    <div class="card mb-4 border-0 shadow-sm" style="border-radius: 1rem;">
        <div class="card-body p-0">
            <div class="row g-0">
                <div class="col-12 col-md-6 p-4 border-end-md">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg bg-label-primary rounded-circle me-3 p-2 d-flex align-items-center justify-content-center">
                            <i class="icon-base ti {{ niche_icon('entity') }} fs-2"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-medium d-block mb-1">{{ niche('entity') }}</small>
                            <h5 class="mb-0 fw-bold text-dark">{{ $order->veiculo->modelo ?? 'Não informado' }}</h5>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border-0 mt-1">{{ $order->veiculo->placa ?? 'S/P' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 p-4 bg-lighter">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg bg-label-info rounded-circle me-3 p-2 d-flex align-items-center justify-content-center">
                            <i class="icon-base ti tabler-user fs-2"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-medium d-block mb-1">Responsável / Cliente</small>
                            <h5 class="mb-0 fw-bold text-dark">{{ $order->client->name }}</h5>
                            <div class="mt-1 d-flex align-items-center">
                                <i class="icon-base ti tabler-phone text-primary me-1 icon-xs"></i>
                                <small class="text-primary fw-semibold">{{ $order->client->phone ?? 'Sem telefone' }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h5 class="mb-3 fw-bold"><i class="icon-base ti tabler-tools me-2 text-primary"></i>Itens para Execução</h5>

    <div class="row">
        @forelse($order->items as $item)
        <div class="col-12 mb-3">
            <div class="card service-card border-0 shadow-sm {{ $item->status == 'completed' ? 'bg-label-success border-success' : '' }}" style="border-radius: 1.25rem; transition: all 0.3s ease;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="pe-3">
                            <h6 class="mb-1 fw-bold text-dark">{{ $item->service->name ?? 'Serviço Personalizado' }}</h6>
                            <span id="status-{{ $item->id }}" class="badge rounded-pill 
                                {{ $item->status == 'in_progress' ? 'bg-label-info' : 
                                  ($item->status == 'completed' ? 'bg-success' : 
                                  ($item->status == 'paused' ? 'bg-label-warning' : 'bg-label-secondary')) }}">
                                {{ $item->status == 'in_progress' ? 'Em Execução' : 
                                  ($item->status == 'completed' ? 'Concluído' : 
                                  ($item->status == 'paused' ? 'Pausado' : 'Pendente')) }}
                            </span>
                        </div>
                        @if($item->status == 'completed')
                            <div class="text-success text-end">
                                <i class="icon-base ti tabler-circle-check fs-2"></i>
                            </div>
                        @endif
                    </div>

                    @if($item->status != 'completed')
                    <div class="row align-items-center mt-4">
                        <div class="col">
                            <div class="d-flex align-items-center">
                                <div class="bg-label-secondary rounded p-2 me-3">
                                    <i class="icon-base ti tabler-clock fs-3"></i>
                                </div>
                                <div>
                                    <h2 class="mb-0 fw-bold timer-display lh-1"
                                        id="timer-{{ $item->id }}"
                                        data-status="{{ $item->status }}"
                                        data-seconds="{{ $item->elapsed_time }}">
                                        {{ gmdate("H:i:s", $item->elapsed_time) }}
                                    </h2>
                                    <small class="text-muted text-uppercase fw-medium" style="font-size: 0.65rem;">Tempo Decorrido</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex gap-2">
                                @if($item->status == 'in_progress')
                                <button class="btn btn-warning btn-icon rounded-circle shadow-warning p-4" onclick="toggleTimer({{ $item->id }}, this)" style="width: 56px; height: 56px;">
                                    <i class="icon-base ti tabler-player-pause fs-3"></i>
                                </button>
                                @else
                                <button class="btn btn-success btn-icon rounded-circle shadow-success p-4" onclick="toggleTimer({{ $item->id }}, this)" style="width: 56px; height: 56px;">
                                    <i class="icon-base ti tabler-player-play fs-3"></i>
                                </button>
                                @endif

                                <button class="btn btn-primary btn-icon rounded-circle shadow-primary p-4" onclick="completeItem({{ $item->id }}, this)" style="width: 56px; height: 56px;">
                                    <i class="icon-base ti tabler-check fs-3"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="mt-2 border-top pt-2 d-flex justify-content-between">
                        <small class="text-muted"><i class="icon-base ti tabler-clock-check me-1"></i> Total: {{ gmdate("H:i:s", $item->elapsed_time) }}</small>
                        <small class="text-muted">Finalizado em {{ $item->updated_at->format('d/m H:i') }}</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <h5 class="text-muted">Nenhum serviço listado.</h5>
        </div>
        @endforelse
    </div>
</div>

<style>
    .border-end-md { border-right: 1px solid #eee; }
    @media (max-width: 767.98px) {
        .border-end-md { border-right: none; border-bottom: 1px solid #eee; padding-bottom: 1rem; }
    }
    .shadow-success { box-shadow: 0 0.5rem 1rem rgba(40, 199, 111, 0.3) !important; }
    .shadow-warning { box-shadow: 0 0.5rem 1rem rgba(255, 159, 67, 0.3) !important; }
    .shadow-primary { box-shadow: 0 0.5rem 1rem rgba(115, 103, 240, 0.3) !important; }
    .service-card.border-primary { border: 1px solid #7367f0 !important; }
</style>
@endsection
