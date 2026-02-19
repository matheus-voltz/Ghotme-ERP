<div class="nav-align-top mb-4">
    <ul class="nav nav-pills mb-3" role="tablist">
        <li class="nav-item">
            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-details" aria-controls="navs-details" aria-selected="true">
                <i class="ti {{ niche_config('icons.entity') }} me-1"></i> Detalhes
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
                            <span><strong>{{ niche('year') }}:</strong></span>
                            <span>{{ $vehicle->ano_fabricacao }}/{{ $vehicle->ano_modelo }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><strong>{{ niche('color') }}:</strong></span>
                            <span>{{ $vehicle->cor ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><strong>{{ niche('secondary_identifier') }}:</strong></span>
                            <span>{{ $vehicle->renavan ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><strong>{{ niche('metric') }} Atual:</strong></span>
                            <span>{{ number_format($vehicle->km_atual ?? 0, 0, ',', '.') }} {{ niche('metric_unit') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Aba Histórico (Timeline) -->
        <div class="tab-pane fade" id="navs-history" role="tabpanel">
            @if($history->count() > 0)
            <ul class="timeline timeline-vertical">
                @foreach($history as $item)
                @php
                $badgeColor = 'info';
                $icon = 'tabler-tool';

                if($item->event_type === 'os_finalizada') {
                $badgeColor = 'success';
                $icon = 'tabler-file-check';
                } elseif($item->event_type === 'os_aberta') {
                $badgeColor = 'primary';
                $icon = 'tabler-file-plus';
                } elseif($item->event_type === 'entrada_oficina') {
                $badgeColor = 'warning';
                $icon = 'tabler-home-check';
                } elseif($item->event_type === 'aguardando_orcamento') {
                $badgeColor = 'warning';
                $icon = 'tabler-clipboard-list';
                } elseif($item->event_type === 'orcamento_aprovado') {
                $badgeColor = 'info';
                $icon = 'tabler-currency-dollar';
                }
                @endphp
                <li class="timeline-item timeline-item-transparent text-break">
                    <span class="timeline-point timeline-point-{{ $badgeColor }}"></span>
                    <div class="timeline-event">
                        <div class="timeline-header mb-1">
                            <h6 class="mb-0"><i class="ti {{ $icon }} me-1"></i> {{ $item->title }}</h6>
                            <small class="text-muted">{{ $item->date->format('d/m/Y') }}</small>
                        </div>
                        <p class="mb-2">{{ $item->description }}</p>

                        @if($item->ordemServico)
                        @if($item->ordemServico->items->count() > 0 || $item->ordemServico->parts->count() > 0)
                        <div class="bg-light p-2 rounded mb-2">
                            <small class="fw-bold d-block mb-1">Serviços & Peças:</small>
                            <ul class="list-unstyled mb-0 small">
                                @foreach($item->ordemServico->items as $serviceItem)
                                <li><i class="ti tabler-tool me-1 text-primary"></i> {{ $serviceItem->service->name }}</li>
                                @endforeach
                                @foreach($item->ordemServico->parts as $part)
                                <li><i class="ti tabler-box me-1 text-info"></i> {{ $part->inventoryItem->name ?? 'Peça' }} ({{ $part->quantity }}x)</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        @endif

                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="badge bg-label-secondary">{{ niche('metric') }}: {{ number_format($item->km ?? 0, 0, ',', '.') }}</span>
                            @if($item->cost)
                            <span class="fw-bold text-heading">Total: R$ {{ number_format($item->cost, 2, ',', '.') }}</span>
                            @elseif($item->ordemServico)
                            <span class="fw-bold text-heading">Total: R$ {{ number_format($item->ordemServico->total, 2, ',', '.') }}</span>
                            @endif
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
            @else
            <div class="text-center py-5">
                <i class="ti tabler-clipboard-list display-1 text-muted mb-3"></i>
                <p>Nenhum histórico de serviços encontrado para este {{ niche('entity') }}.</p>
            </div>
            @endif
        </div>
    </div>
</div>