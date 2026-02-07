@extends('layouts/layoutMaster')

@section('title', 'Tempo Médio por Serviço')

@section('content')
<h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Relatórios /</span> Tempo Médio por Serviço
</h4>

<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Eficiência Operacional por Tipo de Serviço</h5>
        <small class="text-muted">Calculado com base na duração total das ordens de serviço finalizadas.</small>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Serviço</th>
                    <th class="text-center">Qtd. Execuções</th>
                    <th class="text-center">Tempo Médio (h)</th>
                    <th>Performance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($servicesTime as $row)
                @php
                $avg = $row->avg_hours;
                $color = 'success';
                if($avg > 24) $color = 'warning';
                if($avg > 48) $color = 'danger';
                @endphp
                <tr>
                    <td>
                        <strong>{{ $row->service->name }}</strong>
                        <small class="d-block text-muted">{{ \Illuminate\Support\Str::limit($row->service->description, 50) }}</small>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-label-primary">{{ $row->total_executions }}</span>
                    </td>
                    <td class="text-center fw-bold">
                        {{ number_format($avg, 1) }} horas
                    </td>
                    <td>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ min(100, ($avg / 72) * 100) }}%"></div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5">
                        <div class="text-muted">Nenhum dado de tempo disponível.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection