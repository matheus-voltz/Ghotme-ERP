@extends('layouts/layoutMaster')

@section('title', 'Controle de Validade')

@section('content')

<div class="card">
  <div class="card-header border-bottom d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0"><i class="ti tabler-calendar-exclamation me-2"></i> Controle de Validade</h5>
  </div>
  <div class="card-body pt-4">
    @php
      $expired = \App\Models\InventoryItem::expired()->where('is_active', true)->get();
      $expiringSoon = \App\Models\InventoryItem::expiringSoon(7)->where('is_active', true)->get();
      $expiringLater = \App\Models\InventoryItem::whereNotNull('expiry_date')
          ->where('expiry_date', '>', now()->addDays(7))
          ->where('is_active', true)
          ->orderBy('expiry_date')
          ->get();
    @endphp

    @if($expired->count() > 0)
    <div class="alert alert-danger d-flex align-items-center mb-4">
      <i class="ti tabler-alert-triangle me-2 ti-md"></i>
      <strong>{{ $expired->count() }} item(ns) VENCIDO(S)!</strong>
    </div>
    <div class="table-responsive mb-4">
      <table class="table table-hover">
        <thead class="table-danger">
          <tr><th>Item</th><th>Lote</th><th>Vencimento</th><th>Estoque</th><th>Dias Vencido</th></tr>
        </thead>
        <tbody>
          @foreach($expired as $item)
          <tr>
            <td><strong>{{ $item->name }}</strong></td>
            <td>{{ $item->batch_number ?? '-' }}</td>
            <td>{{ $item->expiry_date->format('d/m/Y') }}</td>
            <td>{{ $item->quantity }} {{ $item->unit }}</td>
            <td><span class="badge bg-danger">{{ now()->diffInDays($item->expiry_date) }} dias</span></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif

    @if($expiringSoon->count() > 0)
    <div class="alert alert-warning d-flex align-items-center mb-4">
      <i class="ti tabler-clock-exclamation me-2 ti-md"></i>
      <strong>{{ $expiringSoon->count() }} item(ns) vencem nos próximos 7 dias</strong>
    </div>
    <div class="table-responsive mb-4">
      <table class="table table-hover">
        <thead class="table-warning">
          <tr><th>Item</th><th>Lote</th><th>Vencimento</th><th>Estoque</th><th>Dias Restantes</th></tr>
        </thead>
        <tbody>
          @foreach($expiringSoon as $item)
          <tr>
            <td><strong>{{ $item->name }}</strong></td>
            <td>{{ $item->batch_number ?? '-' }}</td>
            <td>{{ $item->expiry_date->format('d/m/Y') }}</td>
            <td>{{ $item->quantity }} {{ $item->unit }}</td>
            <td><span class="badge bg-warning">{{ now()->diffInDays($item->expiry_date) }} dias</span></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif

    @if($expiringLater->count() > 0)
    <h6 class="mb-3">Próximos vencimentos</h6>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr><th>Item</th><th>Lote</th><th>Vencimento</th><th>Estoque</th><th>Dias Restantes</th></tr>
        </thead>
        <tbody>
          @foreach($expiringLater as $item)
          <tr>
            <td>{{ $item->name }}</td>
            <td>{{ $item->batch_number ?? '-' }}</td>
            <td>{{ $item->expiry_date->format('d/m/Y') }}</td>
            <td>{{ $item->quantity }} {{ $item->unit }}</td>
            <td><span class="badge bg-label-success">{{ now()->diffInDays($item->expiry_date) }} dias</span></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif

    @if($expired->count() === 0 && $expiringSoon->count() === 0 && $expiringLater->count() === 0)
    <div class="text-center py-5">
      <i class="ti tabler-check ti-lg mb-3 d-block text-success"></i>
      <p class="text-muted">Nenhum item com data de validade cadastrada.</p>
    </div>
    @endif
  </div>
</div>

@endsection
