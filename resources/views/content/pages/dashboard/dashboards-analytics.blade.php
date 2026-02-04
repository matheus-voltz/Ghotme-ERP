@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Painel de Controle')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
'resources/assets/vendor/libs/swiper/swiper.scss',
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
])
@endsection

@section('page-style')
@vite('resources/assets/vendor/scss/pages/cards-advance.scss')
@endsection

@section('content')
<div class="row g-6">
  <!-- Card de Boas-vindas e Resumo de OS -->
  <div class="col-xl-6 col-md-12">
    <div class="card h-100 bg-primary text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="text-white mb-1">Olá, {{ auth()->user()->name }}! 👋</h4>
            <p class="mb-0 text-white-50">Aqui está o que está acontecendo na oficina hoje.</p>
          </div>
          <div class="avatar bg-white p-2 rounded">
            <i class="icon-base ti tabler-settings text-primary fs-2"></i>
          </div>
        </div>
        <div class="row g-4">
          <div class="col-6 col-sm-3">
            <div class="text-center border-end border-white-10">
              <h3 class="text-white mb-0">{{ $osStats['pending'] }}</h3>
              <small class="text-white-50">OS Pendentes</small>
            </div>
          </div>
          <div class="col-6 col-sm-3">
            <div class="text-center border-end border-white-10">
              <h3 class="text-white mb-0">{{ $osStats['running'] }}</h3>
              <small class="text-white-50">Em Execução</small>
            </div>
          </div>
          <div class="col-6 col-sm-3">
            <div class="text-center border-end border-white-10">
              <h3 class="text-white mb-0">{{ $osStats['finalized_today'] }}</h3>
              <small class="text-white-50">Finalizadas Hoje</small>
            </div>
          </div>
          <div class="col-6 col-sm-3">
            <div class="text-center">
              <h3 class="text-white mb-0">{{ $osStats['total_month'] }}</h3>
              <small class="text-white-50">OS no Mês</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Faturamento do Mês -->
  <div class="col-xl-3 col-md-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="badge p-2 bg-label-success mb-3"><i class="icon-base ti tabler-currency-dollar fs-3"></i></div>
        <h5 class="card-title mb-1">Faturamento (Mês)</h5>
        <p class="text-muted mb-3 small">Receita confirmada</p>
        <div class="d-flex align-items-center gap-2">
          <h3 class="mb-0">R$ {{ number_format($revenueMonth, 2, ',', '.') }}</h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Alertas Rápidos -->
  <div class="col-xl-3 col-md-6">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="card-title mb-4">Atenção</h5>
        <ul class="list-unstyled mb-0">
          <li class="d-flex mb-3 align-items-center">
            <div class="badge bg-label-danger p-1 rounded me-3"><i class="icon-base ti tabler-alert-triangle fs-5"></i></div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Estoque Baixo</h6>
                <small class="text-muted">{{ $lowStockItems }} itens abaixo do mín.</small>
              </div>
              <a href="{{ route('inventory.items') }}" class="btn btn-xs btn-outline-danger">Ver</a>
            </div>
          </li>
          <li class="d-flex align-items-center">
            <div class="badge bg-label-warning p-1 rounded me-3"><i class="icon-base ti tabler-file-dollar fs-5"></i></div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Orçamentos</h6>
                <small class="text-muted">{{ $pendingBudgets }} aguardando aprovação</small>
              </div>
              <a href="{{ route('budgets.pending') }}" class="btn btn-xs btn-outline-warning">Ver</a>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Financeiro Próximos 7 Dias -->
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Fluxo para os Próximos 7 Dias</h5>
        <i class="icon-base ti tabler-calendar-event text-muted"></i>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-around align-items-center py-4 border rounded bg-lighter mb-4">
          <div class="text-center">
            <small class="text-muted d-block mb-1">A Receber</small>
            <h4 class="text-success mb-0">R$ {{ number_format($receivablesPending, 2, ',', '.') }}</h4>
          </div>
          <div class="divider divider-vertical"></div>
          <div class="text-center">
            <small class="text-muted d-block mb-1">A Pagar</small>
            <h4 class="text-danger mb-0">R$ {{ number_format($payablesPending, 2, ',', '.') }}</h4>
          </div>
        </div>
        <p class="small text-muted mb-0">* Valores baseados em contas pendentes com vencimento na próxima semana.</p>
      </div>
    </div>
  </div>

  <!-- Ordens de Serviço Recentes -->
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Últimas Ordens de Serviço</h5>
        <a href="{{ route('ordens-servico') }}" class="btn btn-xs btn-primary">Ver Todas</a>
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead>
            <tr>
              <th>OS</th>
              <th>Cliente</th>
              <th>Veículo</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($recentOS as $os)
            <tr>
              <td>#{{ $os->id }}</td>
              <td>{{ str($os->client->name ?? $os->client->company_name)->limit(15) }}</td>
              <td><small>{{ $os->veiculo->placa }}</small></td>
              <td>
                @php
                  $statusColor = ['pending' => 'warning', 'running' => 'info', 'finalized' => 'success'][$os->status] ?? 'secondary';
                  $statusLabel = ['pending' => 'Pendente', 'running' => 'Execução', 'finalized' => 'Fim'][$os->status] ?? $os->status;
                @endphp
                <span class="badge bg-label-{{ $statusColor }} badge-xs">{{ $statusLabel }}</span>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
