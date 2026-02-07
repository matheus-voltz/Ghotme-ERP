@extends('layouts/layoutMaster')

@section('title', 'Notificações')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Todas as Notificações</h5>
                <button class="btn btn-label-secondary mark-as-read-btn">
                    <i class="ti tabler-mail-opened me-1"></i> Marcar todas como lidas
                </button>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($notifications as $notification)
                    <li class="list-group-item list-group-item-action border-0 p-4 {{ $notification->unread() ? 'bg-label-primary' : '' }} notification-list-item" data-id="{{ $notification->id }}">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar avatar-md">
                                    <span class="avatar-initial rounded-circle bg-label-success">
                                        <i class="ti tabler-currency-dollar"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fw-bold">{{ $notification->data['title'] ?? 'Notificação' }}</h6>
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-2 text-body">{{ $notification->data['message'] ?? '' }}</p>
                                <div class="d-flex align-items-center gap-3">
                                    @if($notification->read())
                                    <span class="badge bg-label-success">
                                        <i class="ti tabler-check me-1"></i> Lido em: {{ $notification->read_at->format('d/m/Y H:i') }}
                                    </span>
                                    @else
                                    <button class="btn btn-sm btn-link p-0 text-primary individual-mark-as-read-page">
                                        Marcar como lida
                                    </button>
                                    @endif

                                    @php
                                    $url = $notification->data['url'] ?? null;
                                    if (isset($notification->data['budget_id'])) {
                                    $url = route('budgets.approved');
                                    }
                                    @endphp
                                    @if($url)
                                    <a href="{{ $url }}" class="btn btn-sm btn-label-primary">
                                        Ver Detalhes
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </li>
                    @empty
                    <li class="list-group-item p-5 text-center">
                        <i class="ti tabler-bell-off icon-lg text-muted mb-3 d-block"></i>
                        <p class="text-muted mb-0">Você não tem notificações no momento.</p>
                    </li>
                    @endforelse
                </ul>
            </div>
            @if($notifications->hasPages())
            <div class="card-footer border-top">
                {{ $notifications->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@push('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Reutilizando lógica do navbar para marcar individual na página
        document.addEventListener('click', function(e) {
            if (e.target.closest('.individual-mark-as-read-page')) {
                e.preventDefault();
                const btn = e.target.closest('.individual-mark-as-read-page');
                const item = btn.closest('.notification-list-item');
                const id = item.dataset.id;

                fetch(`/notifications/${id}/mark-as-read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            item.classList.remove('bg-label-primary');
                            const badge = `<span class="badge bg-label-success"><i class="ti tabler-check me-1"></i> Lido em: ${data.read_at}</span>`;
                            btn.outerHTML = badge;

                            // Se estiver no topo da página, também tenta atualizar o badge do navbar se existir
                            const navBadge = document.querySelector('.badge-notifications');
                            const navCount = document.querySelector('.dropdown-header .badge');
                            if (navCount) {
                                const count = Math.max(0, parseInt(navCount.textContent) - 1);
                                navCount.textContent = `${count} Novas`;
                                if (count === 0 && navBadge) navBadge.remove();
                            }
                        }
                    });
            }
        });

        // Marcar todas como lidas na página
        const markAllBtn = document.querySelector('.mark-as-read-btn');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', function() {
                fetch('{{ route("notifications.mark-as-read") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload(); // Recarrega para aplicar visual de lido em tudo
                        }
                    });
            });
        }
    });
</script>
@endpush
@endsection