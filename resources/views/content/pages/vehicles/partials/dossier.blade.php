<div class="nav-align-top mb-4">
  <ul class="nav nav-pills mb-3" role="tablist">
    <li class="nav-item">
      <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-details" aria-controls="navs-details" aria-selected="true">
        <i class="ti tabler-car me-1"></i> Detalhes
      </button>
    </li>
    <li class="nav-item">
      <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-history" aria-controls="navs-history" aria-selected="false">
        <i class="ti tabler-history me-1"></i> Histórico
      </button>
    </li>
  </ul>
  <div class="tab-content shadow-none border-0 p-0">
    
    <!-- Aba Detalhes -->
    <div class="tab-pane fade show active" id="navs-details" role="tabpanel">
        <div class="row">
            <div class="col-md-5 text-center mb-3">
                <div class="avatar avatar-xl mb-3 mx-auto" style="width: 100px; height: 100px;">
                    <span class="avatar-initial rounded-circle bg-label-primary display-4">{{ substr($vehicle->marca, 0, 1) }}</span>
                </div>
                <h4 class="mb-1">{{ $vehicle->placa }}</h4>
                <p class="text-muted">{{ $vehicle->marca }} {{ $vehicle->modelo }}</p>
                @if($vehicle->ativo)
                    <span class="badge bg-success">Ativo</span>
                @else
                    <span class="badge bg-danger">Inativo</span>
                @endif
            </div>
            <div class="col-md-7">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><strong>Cliente:</strong></span>
                        <span>{{ $vehicle->client->name ?? $vehicle->client->company_name }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><strong>Ano:</strong></span>
                        <span>{{ $vehicle->ano_fabricacao }}/{{ $vehicle->ano_modelo }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><strong>Cor:</strong></span>
                        <span>{{ $vehicle->cor ?? 'N/A' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><strong>Renavam:</strong></span>
                        <span>{{ $vehicle->renavan ?? 'N/A' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><strong>KM Atual:</strong></span>
                        <span>{{ number_format($vehicle->km_atual ?? 0, 0, ',', '.') }} km</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Aba Histórico (Timeline) -->
    <div class="tab-pane fade" id="navs-history" role="tabpanel">
        @if($history->count() > 0)
            <ul class="timeline timeline-vertical">
                @foreach($history as $os)
                <li class="timeline-item timeline-item-transparent">
                    <span class="timeline-point timeline-point-{{ $os->status == 'completed' ? 'success' : 'warning' }}"></span>
                    <div class="timeline-event">
                        <div class="timeline-header mb-1">
                            <h6 class="mb-0">OS #{{ $os->id }} - {{ $os->status == 'completed' ? 'Concluída' : 'Em Andamento' }}</h6>
                            <small class="text-muted">{{ $os->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                        <p class="mb-2">{{ $os->description }}</p>
                        
                        @if($os->items->count() > 0 || $os->parts->count() > 0)
                        <div class="bg-light p-2 rounded">
                            <small class="fw-bold d-block mb-1">Serviços & Peças:</small>
                            <ul class="list-unstyled mb-0 small">
                                @foreach($os->items as $item)
                                    <li><i class="ti tabler-tool me-1 text-primary"></i> {{ $item->service->name }}</li>
                                @endforeach
                                @foreach($os->parts as $part)
                                    <li><i class="ti tabler-box me-1 text-info"></i> {{ $part->inventoryItem->name ?? 'Peça' }} ({{ $part->quantity }}x)</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="badge bg-label-secondary">KM: {{ number_format($os->km_entry ?? 0, 0, ',', '.') }}</span>
                            <span class="fw-bold text-heading">Total: R$ {{ number_format($os->total, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        @else
            <div class="text-center py-5">
                <i class="ti tabler-clipboard-list display-1 text-muted mb-3"></i>
                <p>Nenhum histórico de serviços encontrado para este veículo.</p>
            </div>
        @endif
    </div>
  </div>
</div>
