@extends('layouts/layoutMaster')

@section('title', 'Desempenho por Mecânico')

@section('content')
<div class="row">
    @foreach($mechanics->take(3) as $index => $m)
    <div class="col-md-4">
        <div class="card mb-6">
            <div class="card-body text-center">
                <div class="mx-auto mb-4">
                    <span class="avatar avatar-xl bg-label-{{ ['primary', 'success', 'info'][$index] ?? 'secondary' }} rounded-circle p-4">
                        <i class="ti tabler-user ti-lg"></i>
                    </span>
                </div>
                <h5 class="mb-1">{{ $m->name }}</h5>
                <small class="d-block mb-4 text-muted">{{ $m->total_os }} OS Finalizadas</small>
                <div class="d-flex justify-content-around">
                    <div>
                        <h5 class="mb-0">R$ {{ number_format($m->revenue_generated, 2, ',', '.') }}</h5>
                        <small>Gerado</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Ranking Geral de Produtividade</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Mecânico / Colaborador</th>
                    <th>OS Concluídas</th>
                    <th>Faturamento Gerado</th>
                    <th>Média por OS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mechanics as $m)
                <tr>
                    <td>
                        <div class="d-flex justify-content-start align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($m->name, 0, 2) }}</span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-medium">{{ $m->name }}</span>
                                <small class="text-muted">{{ $m->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-label-info">{{ $m->total_os }}</span></td>
                    <td class="fw-bold">R$ {{ number_format($m->revenue_generated, 2, ',', '.') }}</td>
                    <td>R$ {{ $m->total_os > 0 ? number_format($m->revenue_generated / $m->total_os, 2, ',', '.') : 0 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
