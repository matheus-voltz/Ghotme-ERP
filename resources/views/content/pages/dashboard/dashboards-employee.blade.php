@extends('layouts/layoutMaster')

@section('title', 'Painel do Funcionário')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
])
@endsection

@section('content')
<div class="row">
    <!-- Welcome Card -->
    <div class="col-12 mb-4">
        <div class="card h-100 bg-linear-primary text-white" style="background: linear-gradient(135deg, #7367f0 0%, #ce9ffc 100%); border: none;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1 text-white">Olá, {{ $user->name }}! 👋</h4>
                        <p class="mb-0 opacity-75">Bem-vindo ao seu painel. Aqui está o resumo das suas atividades hoje.</p>
                    </div>
                    <div class="d-none d-sm-block">
                        <i class="ti tabler-sparkles text-white" style="font-size: 3rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">
                    <h5 class="mb-0 me-2">{{ $pendingBudgetsCount }}</h5>
                    <small class="text-muted">Orçamentos Pendentes</small>
                </div>
                <div class="card-icon">
                    <span class="badge bg-label-warning rounded p-2">
                        <i class="ti tabler-file-dollar ti-sm"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">
                    <h5 class="mb-0 me-2">{{ $runningOSCount }}</h5>
                    <small class="text-muted">OS em Andamento</small>
                </div>
                <div class="card-icon">
                    <span class="badge bg-label-info rounded p-2">
                        <i class="ti tabler-tools ti-sm"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">
                    <h5 class="mb-0 me-2">{{ $completedOSToday }}</h5>
                    <small class="text-muted">Finalizadas Hoje</small>
                </div>
                <div class="card-icon">
                    <span class="badge bg-label-success rounded p-2">
                        <i class="ti tabler-check ti-sm"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Budgets Table -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Orçamentos Recentes</h5>
                <a href="{{ route('budgets.pending') }}" class="btn btn-sm btn-label-primary">Ver todos</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Valor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBudgets as $budget)
                        <tr>
                            <td>#{{ $budget->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs me-2">
                                        <span class="avatar-initial rounded-circle bg-label-secondary">{{ strtoupper(substr($budget->client->name ?? 'C', 0, 1)) }}</span>
                                    </div>
                                    <span class="fw-medium">{{ $budget->client->name ?? 'Cliente Removido' }}</span>
                                </div>
                            </td>
                            <td>{{ $budget->created_at->format('d/m/Y') }}</td>
                            <td>R$ {{ number_format($budget->total ?? 0, 2, ',', '.') }}</td>
                            <td>
                                <span class="badge bg-label-{{ $budget->status === 'approved' ? 'success' : ($budget->status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ __($budget->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">Nenhum orçamento recente.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Shortcuts -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Acesso Rápido</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="{{ route('budgets.create') }}" class="btn btn-outline-primary d-flex align-items-center justify-content-center p-3">
                        <i class="ti tabler-file-plus me-2"></i> Criar Novo Orçamento
                    </a>
                    <a href="{{ route('ordens-servico.create') }}" class="btn btn-outline-info d-flex align-items-center justify-content-center p-3">
                        <i class="ti tabler-tool me-2"></i> Nova Ordem de Serviço
                    </a>
                    <a href="{{ route('clients-list') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center p-3">
                        <i class="ti tabler-user-plus me-2"></i> Cadastrar Cliente
                    </a>
                    <a href="{{ route('support.open-ticket') }}" class="btn btn-label-warning d-flex align-items-center justify-content-center p-3">
                        <i class="ti tabler-headset me-2"></i> Suporte Técnico
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Recent OS Table -->
        <div class="col-lg-12 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Ordens de Serviço Recentes</h5>
                    <a href="{{ route('ordens-servico') }}" class="btn btn-sm btn-label-info">Ver todas</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Veículo</th>
                                <th>Status</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOS as $os)
                            <tr>
                                <td>#{{ $os->id }}</td>
                                <td>{{ $os->client->name ?? 'Cliente Removido' }}</td>
                                <td>{{ $os->veiculo->placa ?? '-' }} - {{ $os->veiculo->modelo ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $os->status === 'finalized' || $os->status === 'paid' ? 'success' : ($os->status === 'running' || $os->status === 'in_progress' ? 'info' : 'warning') }}">
                                        {{ __($os->status) }}
                                    </span>
                                </td>
                                <td>{{ $os->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">Nenhuma ordem de serviço recente.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endsection