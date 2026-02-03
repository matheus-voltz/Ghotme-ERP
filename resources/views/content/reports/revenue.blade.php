@extends('layouts/layoutMaster')

@section('title', 'Faturamento por OS')

@section('content')
<div class="card mb-6">
    <div class="card-widget-separator-wrapper">
        <div class="card-body card-widget-separator">
            <div class="row gy-4 gy-sm-1">
                <div class="col-sm-6 col-lg-4">
                    <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-4 pb-sm-0">
                        <div>
                            <h4 class="mb-0">R$ {{ number_format($totalGeral, 2, ',', '.') }}</h4>
                            <p class="mb-0">Faturamento Total (OS Finalizadas)</p>
                        </div>
                        <div class="avatar me-sm-6">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="ti tabler-currency-dollar ti-md"></i>
                            </span>
                        </div>
                    </div>
                    <hr class="d-none d-sm-block d-lg-none me-6">
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-4 pb-sm-0">
                        <div>
                            <h4 class="mb-0">{{ $osFinalizadas->count() }}</h4>
                            <p class="mb-0">Ordens de Serviço Atendidas</p>
                        </div>
                        <div class="avatar me-lg-6">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="ti tabler-file-check ti-md"></i>
                            </span>
                        </div>
                    </div>
                    <hr class="d-none d-sm-block d-lg-none">
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="mb-0">R$ {{ $osFinalizadas->count() > 0 ? number_format($totalGeral / $osFinalizadas->count(), 2, ',', '.') : 0 }}</h4>
                            <p class="mb-0">Ticket Médio por OS</p>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="ti tabler-chart-bar ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Detalhamento de Faturamento</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>OS #</th>
                    <th>Data</th>
                    <th>Cliente</th>
                    <th>Valor OS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($osFinalizadas as $os)
                <tr>
                    <td><strong>#{{ $os->id }}</strong></td>
                    <td>{{ $os->updated_at->format('d/m/Y') }}</td>
                    <td>{{ $os->client->name ?? $os->client->company_name }}</td>
                    <td class="text-success fw-bold">R$ {{ number_format($os->total, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
