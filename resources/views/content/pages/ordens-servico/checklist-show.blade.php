@extends('layouts/layoutMaster')

@section('title', 'Detalhes do Checklist')

@section('content')
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between">
    <h5 class="mb-0">Checklist #{{ $inspection->id }}</h5>
    <button onclick="window.print()" class="btn btn-label-secondary"><i class="ti tabler-printer me-1"></i> Imprimir</button>
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
            <th class="text-center">Foto</th>
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
            <td class="text-center">
                @if($item->photo_path)
                    <img src="{{ asset('storage/' . $item->photo_path) }}" 
                         alt="Evidência" 
                         class="rounded cursor-pointer shadow-sm" 
                         style="width: 40px; height: 40px; object-fit: cover;"
                         data-bs-toggle="modal" 
                         data-bs-target="#photoModal{{ $item->id }}">

                    <!-- Modal para Foto Grande -->
                    <div class="modal fade" id="photoModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title">{{ $item->checklistItem->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <img src="{{ asset('storage/' . $item->photo_path) }}" class="img-fluid rounded w-100 shadow">
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <span class="text-muted small">-</span>
                @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
