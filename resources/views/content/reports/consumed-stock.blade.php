@extends('layouts/layoutMaster')

@section('title', 'Estoque Consumido')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Peças e Itens Mais Utilizados (Top 15)</h5>
                <small class="text-muted">Baseado em todas as Ordens de Serviço</small>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item / Peça</th>
                            <th>Quantidade Total Usada</th>
                            <th>Estoque Atual</th>
                            <th>Status de Reposição</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mostUsedParts as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium text-heading">{{ $item->part->name ?? 'Item removido' }}</span>
                                    <small class="text-muted">SKU: {{ $item->part->sku ?? '-' }}</small>
                                </div>
                            </td>
                            <td class="fw-bold text-primary">{{ $item->total_qty }}</td>
                            <td>{{ $item->part->quantity ?? 0 }}</td>
                            <td>
                                @if(isset($item->part))
                                    @if($item->part->quantity <= $item->part->min_quantity)
                                        <span class="badge bg-label-danger">Repor Urgente</span>
                                    @elseif($item->part->quantity <= ($item->part->min_quantity * 1.5))
                                        <span class="badge bg-label-warning">Atenção</span>
                                    @else
                                        <span class="badge bg-label-success">Normal</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">Nenhuma peça foi utilizada em OS ainda.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
