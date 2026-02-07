@extends('layouts/layoutMaster')

@section('title', 'Receita por Serviço')

@section('content')
<h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Relatórios /</span> Receita por Serviço
</h4>

<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Desempenho dos Serviços</h5>
        <small class="text-muted">Baseado nas Ordens de Serviço finalizadas</small>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Serviço</th>
                    <th class="text-center">Qtd. Execuções</th>
                    <th class="text-end">Receita Total</th>
                    <th class="text-end">Ticket Médio</th>
                </tr>
            </thead>
            <tbody>
                @forelse($servicesReport as $row)
                <tr>
                    <td>
                        <strong>{{ $row->service->name }}</strong>
                        <small class="d-block text-muted">{{ \Illuminate\Support\Str::limit($row->service->description, 50) }}</small>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-label-primary">{{ $row->total_qty }}</span>
                    </td>
                    <td class="text-end fw-bold text-success">
                        R$ {{ number_format($row->total_revenue, 2, ',', '.') }}
                    </td>
                    <td class="text-end">
                        R$ {{ $row->total_qty > 0 ? number_format($row->total_revenue / $row->total_qty, 2, ',', '.') : '0,00' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5">
                        <div class="text-muted">Nenhum serviço finalizado encontrado.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection