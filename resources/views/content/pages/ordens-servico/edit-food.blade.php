@extends('layouts/layoutMaster')

@section('title', 'Pedido #' . $order->id)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-none border-primary mb-4">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                <h4 class="mb-0 text-white">PEDIDO #{{ $order->id }}</h4>
                <span class="badge bg-white text-primary fs-5 px-3">
                    {{ $order->veiculo ? 'MESA / SENHA: ' . $order->veiculo->placa : 'BALCÃO / RETIRADA' }}
                </span>
            </div>
            <div class="card-body pt-4">
                <!-- Informações do Cliente -->
                <div class="d-flex flex-column align-items-center text-center mb-4 pb-3 border-bottom">
                    <div class="avatar avatar-xl bg-label-primary rounded-circle mb-3">
                        <i class="ti tabler-user fs-1"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 fw-bold">{{ $order->client->name ?? $order->customer_name ?? 'Consumidor Final' }}</h4>
                        <span class="badge bg-label-secondary">Aberto em: {{ $order->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                <!-- Itens do Pedido -->
                <h6 class="text-uppercase fw-bold mb-3 text-primary"><i class="ti tabler-list-check me-2"></i>Itens para Preparo</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-lg">
                        <thead class="table-light">
                            <tr>
                                <th class="fs-6">QTD</th>
                                <th class="fs-6">PRODUTO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->parts as $item)
                            <tr>
                                <td class="fw-bold fs-4 text-primary" width="100">{{ number_format($item->quantity, 0) }}x</td>
                                <td class="fs-4 fw-medium">{{ $item->inventoryItem->name }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Observações -->
                @if($order->description)
                <div class="alert alert-warning border-warning mb-4">
                    <h6 class="alert-heading fw-bold mb-1"><i class="ti tabler-alert-circle me-1"></i> OBSERVAÇÕES DE PREPARO:</h6>
                    <p class="mb-0 fs-5 fw-bold text-dark">{{ $order->description }}</p>
                </div>
                @endif

                <!-- Ações de Status -->
                <div class="border-top pt-4">
                    <h6 class="text-uppercase fw-bold mb-3">Mudar Status do Pedido:</h6>
                    <form action="{{ route('ordens-servico.status', $order->id) }}" method="POST" id="statusForm">
                        @csrf
                        <input type="hidden" name="status" id="statusInput">
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <button type="button" onclick="updateStatus('pending')" class="btn btn-label-warning btn-xl w-100 {{ $order->status == 'pending' ? 'active border-2 border-warning' : '' }}">
                                    <i class="ti tabler-clock me-2"></i> NA FILA
                                </button>
                            </div>
                            <div class="col-sm-4">
                                <button type="button" onclick="updateStatus('in_progress')" class="btn btn-label-info btn-xl w-100 {{ $order->status == 'in_progress' ? 'active border-2 border-info' : '' }}">
                                    <i class="ti tabler-tools-kitchen-2 me-2"></i> PREPARANDO
                                </button>
                            </div>
                            <div class="col-sm-4">
                                <button type="button" onclick="updateStatus('completed')" class="btn btn-label-success btn-xl w-100 {{ $order->status == 'completed' ? 'active border-2 border-success' : '' }}">
                                    <i class="ti tabler-check me-2"></i> PRONTO
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-footer bg-light d-flex justify-content-between mt-3">
                <a href="{{ route('ordens-servico') }}" class="btn btn-label-secondary">
                    <i class="ti tabler-arrow-left me-1"></i> Voltar ao Monitor
                </a>
                <a href="{{ route('ordens-servico.print-order', $order->id) }}" target="_blank" class="btn btn-info">
                    <i class="ti tabler-printer me-1"></i> Imprimir Comanda
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function updateStatus(val) {
    document.getElementById('statusInput').value = val;
    
    Swal.fire({
        title: 'Atualizar Status?',
        text: "O status do pedido será alterado imediatamente.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim, atualizar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-primary me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('statusForm');
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => {
                if(response.ok) {
                    window.location.reload();
                }
            });
        }
    });
}
</script>

<style>
.btn-xl {
    padding: 1.5rem 1rem;
    font-size: 1.1rem;
    font-weight: bold;
}
.btn-label-warning.active { background-color: #fff3e0 !important; }
.btn-label-info.active { background-color: #e1f5fe !important; }
.btn-label-success.active { background-color: #e8f5e9 !important; }
</style>
@endsection
