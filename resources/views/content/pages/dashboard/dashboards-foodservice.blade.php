@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Dashboard Food Service')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/animate-css/animate.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
@endsection

@section('page-style')
<style>
    .fs-dashboard-card {
        transition: all 0.2s;
        border: none;
        border-radius: 12px;
    }

    .fs-dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .bg-dark-pos {
        background: #2b2c40 !important;
        color: white;
    }

    .pos-shortcut-btn {
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        background: linear-gradient(135deg, #7367f0 0%, #a098f5 100%);
        color: white;
        text-decoration: none;
        border-radius: 12px;
    }

    .pos-shortcut-btn:hover {
        color: white;
        background: linear-gradient(135deg, #6259ca 0%, #857ced 100%);
    }
</style>
@endsection

@section('content')
<div class="row g-4 animate__animated animate__fadeIn">
    <!-- Welcome -->
    <div class="col-12">
        <div class="card bg-dark-pos shadow-none">
            <div class="card-body py-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="text-white mb-1 fw-bold">🍔 {{ __('Food Service Dashboard') }}</h4>
                    <p class="mb-0 opacity-75">{{ __('Manage your kitchen and sales in real time.') }}</p>
                </div>
                <div class="d-none d-md-block">
                    <span class="badge bg-label-warning p-2"><i class="ti tabler-flame me-1"></i> {{ __('Operation Online') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Big POS Button -->
    <div class="col-md-4">
        <a href="{{ route('menu.pos') }}" class="pos-shortcut-btn fs-dashboard-card shadow-sm">
            <i class="ti tabler-device-tablet fs-1 mb-3"></i>
            <h4 class="text-white mb-1 fw-bold">{{ __('OPEN POS') }}</h4>
            <small class="opacity-75">{{ __('Sell now at the counter') }}</small>
        </a>
    </div>

    <!-- Key Metrics -->
    <div class="col-md-8">
        <div class="row g-4">
            <div class="col-6 col-md-4">
                <div class="card h-100 fs-dashboard-card shadow-sm">
                    <div class="card-body">
                        <div class="avatar bg-label-success rounded p-2 mb-2">
                            <i class="ti tabler-currency-dollar fs-4"></i>
                        </div>
                        <h5 class="card-title mb-1 text-muted small text-uppercase fw-bold">{{ __('Sales Today') }}</h5>
                        <h3 class="fw-bold mb-0">R$ {{ number_format($revenueMonth, 2, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card h-100 fs-dashboard-card shadow-sm">
                    <div class="card-body">
                        <div class="avatar bg-label-info rounded p-2 mb-2">
                            <i class="ti tabler-tools-kitchen-2 fs-4"></i>
                        </div>
                        <h5 class="card-title mb-1 text-muted small text-uppercase fw-bold">{{ __('In Kitchen') }}</h5>
                        <h3 class="fw-bold mb-0">{{ $osStats['running'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100 fs-dashboard-card shadow-sm">
                    <div class="card-body">
                        <div class="avatar bg-label-warning rounded p-2 mb-2">
                            <i class="ti tabler-box fs-4"></i>
                        </div>
                        <h5 class="card-title mb-1 text-muted small text-uppercase fw-bold">{{ __('Low Stock') }}</h5>
                        <h3 class="fw-bold mb-0 text-danger">{{ $lowStockItems }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="col-12 col-xl-8">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">{{ __('Recent Orders') }}</h5>
                <a href="{{ route('ordens-servico') }}" class="btn btn-sm btn-label-primary">{{ __('Manage All') }}</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">{{ __('ID / Client') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-end pe-4">{{ __('Value') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOS as $os)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        <span class="avatar-initial rounded bg-label-primary fs-small">#{{ $os->id }}</span>
                                    </div>
                                    <div>
                                        <div class="fw-bold mb-0">{{ $os->client->name ?? 'Consumidor' }}</div>
                                        <small class="text-muted">{{ $os->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                $statusColor = 'secondary';
                                if($os->status == 'pending') $statusColor = 'warning';
                                if($os->status == 'running') $statusColor = 'info';
                                if($os->status == 'finalized' || $os->status == 'paid') $statusColor = 'success';
                                @endphp
                                <span class="badge bg-label-{{ $statusColor }} rounded-pill btn-sm">
                                    {{ __($os->status) }}
                                </span>
                            </td>
                            <td class="text-end pe-4 fw-bold">R$ {{ number_format($os->total, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Ingredients to Watch -->
    <div class="col-12 col-xl-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header border-bottom py-3">
                <h5 class="mb-0 fw-bold text-danger">{{ __('Low Ingredients') }}</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @if(count($recentOS) > 0) {{-- Simulated check, need to fetch actual items if real data exists --}}
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-0">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-label-danger rounded-circle p-2 me-3">
                                <i class="ti tabler-alert-triangle fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">{{ __('Watch your inventory') }}</h6>
                                <small class="text-muted">{{ $lowStockItems }} {{ __('items need restock.') }}</small>
                            </div>
                        </div>
                    </li>
                    @endif
                </ul>
                <div class="p-3">
                    <a href="{{ route('inventory.items') }}" class="btn btn-outline-danger w-100">{{ __('Go to Inventory') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection