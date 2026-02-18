@extends('layouts/contentNavbarLayout')

@section('title', 'Relatório de Lucratividade')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Relatórios /</span> Lucratividade (30 dias)
        </h4>
        <div class="badge bg-label-success">Período de Referência: {{ now()->subDays(30)->format('d/m') }} - {{ now()->format('d/m') }}</div>
    </div>

    <!-- KPI Cards -->
    <div class="row">
        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card card-border-shadow-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-primary"><i class="ti tabler-currency-dollar ti-md"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">R$ {{ number_format($totalRevenue, 2, ',', '.') }}</h4>
                    </div>
                    <p class="mb-1">Receita Bruta</p>
                    <p class="mb-0">
                        <small class="text-muted">Total faturado no período</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card card-border-shadow-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-danger"><i class="ti tabler-packages ti-md"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">R$ {{ number_format($totalCost, 2, ',', '.') }}</h4>
                    </div>
                    <p class="mb-1">Custo de Peças</p>
                    <p class="mb-0">
                        <small class="text-muted">Custo total dos insumos</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card card-border-shadow-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-success"><i class="ti tabler-trending-up ti-md"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">R$ {{ number_format($totalProfit, 2, ',', '.') }}</h4>
                    </div>
                    <p class="mb-1">Lucro Bruto (Margem)</p>
                    <p class="mb-0">
                        <span class="badge bg-label-success">+ {{ number_format($avgMargin, 1) }}% de margem</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card card-border-shadow-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-warning"><i class="ti tabler-clock ti-md"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">{{ number_format($servicesPerformance->sum('total_time') / 3600, 1) }}h</h4>
                    </div>
                    <p class="mb-1">Tempo Produtivo</p>
                    <p class="mb-0">
                        <small class="text-muted">Horas faturadas no período</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Serviços mais Lucrativos -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Performance por Serviço</h5>
                </div>
                <div class="card-body">
                    <ul class="p-0 m-0">
                        @foreach($servicesPerformance as $service)
                        <li class="d-flex mb-4 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-secondary"><i class="ti tabler-tools"></i></span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">{{ $service->name }}</h6>
                                    <small class="text-muted">{{ number_format($service->total_time / 3600, 1) }}h executadas</small>
                                </div>
                                <div class="user-progress">
                                    <small class="fw-semibold">R$ {{ number_format($service->revenue, 2, ',', '.') }}</small>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Lista de Ordens Recentes -->
        <div class="col-md-6 col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Ordens Recentes e Lucratividade</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>OS #</th>
                                <th>Receita</th>
                                <th>Custo</th>
                                <th>Lucro</th>
                                <th>Margem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                            <tr>
                                <td><strong>#{{ $order->id }}</strong></td>
                                <td>R$ {{ number_format($order->total, 2, ',', '.') }}</td>
                                <td class="text-danger">R$ {{ number_format($order->parts_cost_total, 2, ',', '.') }}</td>
                                <td class="text-success fw-bold">R$ {{ number_format($order->gross_profit, 2, ',', '.') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress w-100 me-2" style="height: 6px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $order->profit_margin }}%" aria-valuenow="{{ $order->profit_margin }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <small>{{ number_format($order->profit_margin, 1) }}%</small>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Nenhuma ordem finalizada no período.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
