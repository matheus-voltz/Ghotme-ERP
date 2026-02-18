@extends('layouts/contentNavbarLayout')

@section('title', 'Suprimento Automático de Estoque')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Estoque /</span> Sugestão de Reposição
        </h4>
        <form action="{{ route('inventory.purchase-orders.automatic') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary shadow-lg">
                <i class="ti tabler-robot me-1"></i> Gerar Pedidos de Compra Automáticos
            </button>
        </form>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <!-- Itens Abaixo do Mínimo -->
        <div class="col-md-5 mb-4">
            <div class="card h-100">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Itens em Alerta de Estoque</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Atual / Mín</th>
                                <th>Fornecedor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockItems as $item)
                            <tr>
                                <td><strong>{{ $item->name }}</strong></td>
                                <td>
                                    <span class="text-danger fw-bold">{{ $item->quantity }}</span> / {{ $item->min_quantity }}
                                </td>
                                <td>{{ $item->supplier->name ?? 'Não definido' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 text-success">Todos os itens estão com estoque saudável! 🎉</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pedidos de Compra Gerados -->
        <div class="col-md-7 mb-4">
            <div class="card h-100">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Histórico de Pedidos de Compra</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th># Pedido</th>
                                <th>Fornecedor</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr>
                                <td>#{{ $order->id }}</td>
                                <td>{{ $order->supplier->name }}</td>
                                <td>R$ {{ number_format($order->total_amount, 2, ',', '.') }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $order->status == 'received' ? 'success' : 'warning' }}">
                                        {{ $order->status == 'received' ? 'Recebido' : 'Pendente' }}
                                    </span>
                                </td>
                                <td>
                                    @if($order->status == 'draft')
                                    <form action="{{ route('inventory.purchase-orders.receive', $order->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-outline-success">Confirmar Recebimento</button>
                                    </form>
                                    @else
                                    <span class="text-muted small">{{ $order->received_at ? $order->received_at->format('d/m/Y') : '' }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-4">Nenhum pedido de compra registrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
