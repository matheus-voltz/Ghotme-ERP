@extends('layouts/layoutMaster')

@section('title', 'Detalhes do Checklist')

@section('content')
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between">
    <h5 class="mb-0">Checklist #{{ $inspection->id }}</h5>
    <button onclick="window.print()" class="btn btn-label-secondary"><i class="ti ti-printer me-1"></i> Imprimir</button>
  </div>
  <div class="card-body">
    <div class="row mb-4">
      <div class="col-md-4">
        <h6>Veículo</h6>
        <p><strong>Placa:</strong> {{ $inspection->veiculo->placa }}<br>
        <strong>Modelo:</strong> {{ $inspection->veiculo->modelo }}<br>
        <strong>KM:</strong> {{ $inspection->km_current }}</p>
      </div>
      <div class="col-md-4">
        <h6>Informações</h6>
        <p><strong>Data:</strong> {{ $inspection->created_at->format('d/m/Y H:i') }}<br>
        <strong>Responsável:</strong> {{ $inspection->user->name }}<br>
        <strong>Combustível:</strong> {{ $inspection->fuel_level }}</p>
      </div>
      @if($inspection->notes)
      <div class="col-md-4">
        <h6>Observações Gerais</h6>
        <p>{{ $inspection->notes }}</p>
      </div>
      @endif
    </div>

    <div class="table-responsive">
      <table class="table table-striped border-top">
        <thead>
          <tr>
            <th>Item</th>
            <th class="text-center">Status</th>
            <th>Observações</th>
          </tr>
        </thead>
        <tbody>
          @foreach($inspection->items as $item)
          <tr>
            <td>{{ $item->checklistItem->name }}</td>
            <td class="text-center">
              @if($item->status === 'ok')
                <span class="badge bg-label-success">OK</span>
              @elseif($item->status === 'not_ok')
                <span class="badge bg-label-danger">RUIM</span>
              @else
                <span class="badge bg-label-secondary">N/A</span>
              @endif
            </td>
            <td>{{ $item->observations ?? '-' }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
