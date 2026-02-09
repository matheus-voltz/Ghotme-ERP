@extends('layouts/contentNavbarLayout')

@section('title', 'OS #' . $order->id . ' - Detalhes')

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Setup timer interval
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
                // Disable button
                btn.disabled = true;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

                const response = await fetch(`/mechanic/timer/${itemId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) throw new Error('Network response was not ok');

                const data = await response.json();

                // Update UI based on new status
                const timerDisplay = document.getElementById(`timer-${itemId}`);
                const statusBadge = document.getElementById(`status-${itemId}`);

                if (data.status === 'in_progress') {
                    // Started
                    btn.innerHTML = '<i class="ti tabler-player-pause"></i>';
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-warning');
                    timerDisplay.dataset.status = 'in_progress';
                    statusBadge.className = 'badge bg-label-info ms-2';
                    statusBadge.textContent = 'Em Andamento';
                } else {
                    // Paused
                    btn.innerHTML = '<i class="ti tabler-player-play"></i>';
                    btn.classList.remove('btn-warning');
                    btn.classList.add('btn-success');
                    timerDisplay.dataset.status = 'paused';
                    timerDisplay.dataset.seconds = data.elapsed_time; // Sync with server time
                    timerDisplay.textContent = formatTime(data.elapsed_time);
                    statusBadge.className = 'badge bg-label-warning ms-2';
                    statusBadge.textContent = 'Pausado';
                }

            } catch (error) {
                console.error('Error toggling timer:', error);
                alert('Erro ao alterar cronômetro. Tente novamente.');
            } finally {
                btn.disabled = false;
            }
        };

        window.completeItem = async function(itemId, btn) {
            if (!confirm('Você tem certeza que deseja marcar este serviço como concluído?')) return;

            try {
                btn.disabled = true;
                const response = await fetch(`/mechanic/complete/${itemId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) throw new Error('Network response was not ok');

                // Reload or update UI to show completed state
                location.reload();

            } catch (error) {
                console.error('Error completing item:', error);
                alert('Erro ao finalizar serviço.');
                btn.disabled = false;
            }
        };
    });
</script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <a href="{{ route('mechanic.dashboard') }}" class="text-muted fw-light me-2"><i class="ti tabler-arrow-left"></i> Voltar</a>
            OS #{{ $order->id }}
        </h4>
        <span class="badge bg-label-primary">{{ $order->status }}</span>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="avatar avatar-lg me-3">
                    <span class="avatar-initial rounded-circle bg-label-primary"><i class="ti tabler-car fs-3"></i></span>
                </div>
                <div>
                    <h5 class="mb-1">{{ $order->veiculo->modelo }} <span class="badge bg-label-secondary ms-2">{{ $order->veiculo->placa }}</span></h5>
                    <p class="mb-0 text-muted">{{ $order->client->name }}</p>
                </div>
            </div>
        </div>
    </div>

    <h5 class="mb-3">Serviços a Realizar</h5>

    <div class="row">
        @forelse($order->items as $item)
        <div class="col-12 mb-3">
            <div class="card border {{ $item->status == 'completed' ? 'bg-label-success border-success' : '' }}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-bold">{{ $item->service->name ?? 'Serviço #' . $item->id }}</h6>
                        <span id="status-{{ $item->id }}" class="badge ms-2 
                            {{ $item->status == 'in_progress' ? 'bg-label-info' : 
                              ($item->status == 'completed' ? 'bg-success' : 
                              ($item->status == 'paused' ? 'bg-label-warning' : 'bg-label-secondary')) }}">
                            {{ $item->status == 'in_progress' ? 'Em Andamento' : 
                              ($item->status == 'completed' ? 'Concluído' : 
                              ($item->status == 'paused' ? 'Pausado' : 'Pendente')) }}
                        </span>
                    </div>

                    @if($item->status != 'completed')
                    <div class="d-flex justify-content-between align-items-center mt-3 p-3 bg-lighter rounded">
                        <div class="d-flex align-items-center">
                            <i class="ti tabler-clock me-2 text-muted"></i>
                            <h3 class="mb-0 fw-bold timer-display"
                                id="timer-{{ $item->id }}"
                                data-status="{{ $item->status }}"
                                data-seconds="{{ $item->elapsed_time }}">
                                {{ gmdate("H:i:s", $item->elapsed_time) }}
                            </h3>
                        </div>

                        <div class="action-buttons d-flex gap-2">
                            @if($item->status == 'in_progress')
                            <button class="btn btn-warning btn-icon rounded-pill btn-lg" onclick="toggleTimer({{ $item->id }}, this)">
                                <i class="ti tabler-player-pause"></i>
                            </button>
                            @else
                            <button class="btn btn-success btn-icon rounded-pill btn-lg" onclick="toggleTimer({{ $item->id }}, this)">
                                <i class="ti tabler-player-play"></i>
                            </button>
                            @endif

                            <button class="btn btn-primary btn-icon rounded-pill btn-lg" onclick="completeItem({{ $item->id }}, this)" title="Finalizar">
                                <i class="ti tabler-check"></i>
                            </button>
                        </div>
                    </div>
                    @else
                    <div class="mt-2 text-success">
                        <small><i class="ti tabler-clock-check me-1"></i> Tempo final: {{ gmdate("H:i:s", $item->elapsed_time) }}</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @empty
        <div class="col-12 text-center py-5">
            <span class="avatar avatar-xl rounded-circle bg-label-secondary mb-3">
                <i class="ti tabler-clipboard-off fs-1"></i>
            </span>
            <h5 class="text-muted">Nenhum serviço listado</h5>
            <p class="text-muted small">Esta Ordem de Serviço não possui itens cadastrados ainda.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection