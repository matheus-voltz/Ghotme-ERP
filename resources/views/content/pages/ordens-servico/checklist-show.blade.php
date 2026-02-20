@extends($layout)

@section('title', __('Detalhes do Checklist'))

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/animate-css/animate.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('page-style')
<style>
  /* Esconde absolutamente tudo que não seja o conteúdo se for público */
  @if($isPublic) .layout-menu,
  .template-customizer-open-btn,
  .layout-navbar,
  .content-footer,
  #primary-color-style,
  .layout-page::before,
  .content-wrapper::before,
  .layout-wrapper::before,
  [class*="layout-page"]::before {
    display: none !important;
    visibility: hidden !important;
    content: none !important;
    height: 0 !important;
  }

  /* Reset total para um "Esqueleto" branco puro */
  html,
  body,
  .layout-wrapper,
  .layout-container,
  .layout-page,
  .content-wrapper,
  .container-xxl {
    background: #ffffff !important;
    background-color: #ffffff !important;
    background-image: none !important;
    color: #000000 !important;
    padding: 0 !important;
    margin: 0 !important;
    border: none !important;
    box-shadow: none !important;
  }

  .card {
    box-shadow: none !important;
    border: 1px solid #e0e0e0 !important;
    background: #fff !important;
  }

  .container-p-y {
    padding-top: 2rem !important;
  }

  /* Forçar Full Width sempre */
  .container-fluid,
  .container-xxl {
    max-width: 100% !important;
    width: 100% !important;
    padding-left: 2rem !important;
    padding-right: 2rem !important;
  }

  @endif

  /* Fix para ícones que aparecem como interrogação */
  .ti {
    display: inline-block !important;
    width: 1em;
    height: 1em;
    mask-size: 100% 100% !important;
    -webkit-mask-size: 100% 100% !important;
  }

  /* Estilos para impressão */
  @media print {
    @page {
      margin: 0.5cm;
      size: A4 portrait;
    }

    body {
      background: white !important;
      font-size: 12px;
      /* Fonte levemente maior que antes */
      -webkit-print-color-adjust: exact;
    }

    .layout-menu,
    .layout-navbar,
    .content-footer,
    .no-print,
    .template-customizer-open-btn,
    .btn,
    .layout-menu-toggle {
      display: none !important;
    }

    .layout-page,
    .content-wrapper,
    .container-xxl {
      padding: 0 !important;
      margin: 0 !important;
      width: 100% !important;
      max-width: 100% !important;
    }

    .card {
      border: 1px solid #ddd !important;
      box-shadow: none !important;
      break-inside: avoid;
      margin-bottom: 15px !important;
    }

    /* Forçar largura total nos cards */
    .col-md-4,
    .col-xl-3,
    .col-sm-6 {
      width: 33% !important;
      /* Distribui melhor as informações no topo */
      float: left;
    }

    /* Grid de Fotos na Impressão */
    .row.g-4 .col-xl-3 {
      width: 50% !important;
      /* 2 fotos por linha na impressão para ficarem grandes */
      page-break-inside: avoid;
    }

    .card-img-top {
      height: 200px !important;
      /* Fotos grandes na impressão */
      object-fit: contain !important;
    }

    /* Tabela */
    .table th,
    .table td {
      padding: 6px 10px !important;
      font-size: 11px !important;
    }

    .badge {
      border: 1px solid #000;
      color: #000 !important;
      background: transparent !important;
    }
  }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y px-lg-5">

  @if(!$isPublic && auth()->check())
  <div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h4 class="mb-0">{{ __('Detalhes do Checklist') }}</h4>
    <a href="{{ route('ordens-servico.checklist') }}" class="btn btn-label-secondary">
      <i class="icon-base ti tabler-arrow-left ti-sm me-1"></i> {{ __('Voltar') }}
    </a>
  </div>
  @endif

  <!-- Cabeçalho com Logo da Empresa -->
  <div class="row mb-5 align-items-center header-print-area">
    <div class="col-sm-7">
      <div class="d-flex align-items-center gap-3 company-branding">
        @php
          $logoUrl = ($inspection->company && $inspection->company->logo_path) 
                     ? asset('storage/' . $inspection->company->logo_path) 
                     : null;
        @endphp

        @if($logoUrl)
          <img src="{{ $logoUrl }}" alt="Logo" class="company-logo-img" style="max-height: 80px; max-width: 200px; object-fit: contain;">
        @else
          <div class="bg-label-primary p-2 rounded">
            <i class="ti tabler-building-store fs-1"></i>
          </div>
        @endif

        <div class="company-text-details">
          <h2 class="mb-0 fw-bold text-primary company-name-display">{{ $inspection->company->name ?? config('app.name') }}</h2>
          <p class="mb-0 text-muted small">{{ $inspection->company->email ?? '' }} {{ $inspection->company->phone ? '| ' . $inspection->company->phone : '' }}</p>
        </div>
      </div>
    </div>
    <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
      <h3 class="mb-1 fw-bold text-uppercase title-print">{{ __('Checklist de Entrada') }}</h3>
      <p class="text-muted mb-0 small">{{ __('Protocolo de segurança e transparência.') }}</p>
    </div>
  </div>

  <div class="card mb-4 shadow-sm">
    <div class="card-header d-flex justify-content-between flex-wrap gap-3 border-bottom bg-light">
      <h5 class="mb-0 py-2">{{ __('Protocolo') }} #{{ $inspection->id }}</h5>
      @if(!$isPublic && auth()->check())
      <div class="d-flex gap-2 no-print">
        <button onclick="window.print()" class="btn btn-label-secondary">
          <i class="icon-base ti tabler-printer ti-sm me-1"></i> {{ __('Imprimir') }}
        </button>

        @php
        $url = route('public.checklist.show', $inspection->id) . ($inspection->token ? "?token=" . $inspection->token : "");
        $whatsappMsg = "Olá! Segue o checklist de entrada do seu veículo " . $inspection->veiculo->modelo . " (" . $inspection->veiculo->placa . ").\nVocê pode visualizar os detalhes e fotos aqui: " . $url;
        $whatsappUrl = "https://api.whatsapp.com/send?phone=55" . preg_replace('/\D/', '', $inspection->veiculo->client->whatsapp ?? '') . "&text=" . urlencode($whatsappMsg);
        @endphp

        <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-success">
          <i class="icon-base ti tabler-brand-whatsapp ti-sm me-1"></i> WhatsApp
        </a>
        <button type="button" class="btn btn-primary btn-send-email" data-id="{{ $inspection->id }}">
          <i class="icon-base ti tabler-mail ti-sm me-1"></i> {{ __('E-mail') }}
        </button>
      </div>
      @endif
    </div>
    <div class="card-body pt-4">
      <div class="row g-4 mb-5">
        <div class="col-md-4 border-end">
          <h6 class="text-muted text-uppercase small fw-bold mb-3">{{ niche('entity') }}</h6>
          <p class="mb-1"><span class="fw-bold">{{ niche('identifier') }}:</span> {{ $inspection->veiculo->placa }}</p>
          <p class="mb-1"><span class="fw-bold">{{ niche('model') }}:</span> {{ $inspection->veiculo->modelo }}</p>
          <p class="mb-1"><span class="fw-bold">{{ niche('metric') }}:</span> {{ number_format($inspection->km_current, 0, ',', '.') }} km</p>
        </div>
        <div class="col-md-4 border-end">
          <h6 class="text-muted text-uppercase small fw-bold mb-3">{{ __('Informações Gerais') }}</h6>
          <p class="mb-1"><span class="fw-bold">{{ __('Data') }}:</span> {{ $inspection->created_at->format('d/m/Y H:i') }}</p>
          <p class="mb-1"><span class="fw-bold">{{ __('Responsável') }}:</span> {{ $inspection->user->name }}</p>
          <p class="mb-1"><span class="fw-bold">{{ niche('fuel') }}:</span> {{ $inspection->fuel_level }}</p>
        </div>
        <div class="col-md-4">
          <h6 class="text-muted text-uppercase small fw-bold mb-3">{{ __('Observações do Atendente') }}</h6>
          <p class="text-muted italic">{{ $inspection->notes ?: __('Nenhuma observação registrada.') }}</p>
        </div>
      </div>

      <!-- Visual Inspection Display -->
      @if($inspection->damagePoints->isNotEmpty())
      <div class="card mb-5 border-dashed bg-lighter">
        <div class="card-header bg-transparent text-center border-bottom mb-4">
          <h5 class="mb-0 fw-bold"><i class="icon-base ti tabler-camera ti-md me-2"></i>{{ niche('visual_inspection_title') }}</h5>
          <small class="text-muted">Mapa de avarias externas identificadas na entrada</small>
        </div>
        <div class="card-body">
          <!-- Mapa Visual com Marcadores -->
          @php $inspectionComponent = niche_config('components.visual_inspection'); @endphp
          
          @if($inspectionComponent)
          <div class="text-center mb-5">
            <div id="vehicle-visual-display" style="position: relative; display: inline-block; width: 100%; max-width: 900px;">
              <!-- Car Blueprint Component -->
              <div style="width: 100%;">
                @include($inspectionComponent)
              </div>

              <!-- Markers Layer (Overlay) -->
              <div id="display-markers-container" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;">
                @foreach($inspection->damagePoints as $point)
                <div class="damage-marker-fixed" 
                     style="position: absolute; left: {{ $point->x_coordinate }}%; top: {{ $point->y_coordinate }}%; pointer-events: auto;"
                     data-bs-toggle="tooltip" 
                     title="{{ $point->notes }}">
                  <div class="marker-dot"></div>
                </div>
                @endforeach
              </div>
            </div>
          </div>
          @endif

          <div class="row g-4">
            @foreach($inspection->damagePoints as $point)
            <div class="col-xl-3 col-md-4 col-sm-6">
              <div class="card h-100 shadow-none border">
                @if($point->photo_path)
                <img src="{{ asset('storage/' . $point->photo_path) }}"
                  class="card-img-top cursor-pointer"
                  style="height: 180px; object-fit: cover;"
                  data-bs-toggle="modal"
                  data-bs-target="#damageModal{{ $point->id }}">
                @else
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                  <i class="icon-base ti tabler-camera-off text-muted fs-1"></i>
                </div>
                @endif
                <div class="card-body p-3 text-center bg-white">
                  <span class="badge bg-label-danger text-uppercase mb-2">{{ $point->part_name ?: __('Avaria') }}</span>
                  @if($point->notes && $point->notes != 'Registrado via Mobile')
                  <p class="mb-0 small text-muted text-truncate">{{ $point->notes }}</p>
                  @endif
                </div>

                @if($point->photo_path)
                <!-- Modal for Large Damage Photo -->
                <div class="modal fade" id="damageModal{{ $point->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-uppercase">{{ $point->part_name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body text-center p-4">
                        <img src="{{ asset('storage/' . $point->photo_path) }}" class="img-fluid rounded shadow-lg" style="max-height: 80vh;">
                        @if($point->notes)
                        <div class="mt-4 p-3 bg-light rounded text-start border-start border-danger border-3">
                          <strong class="text-danger small text-uppercase">{{ __('Notas do Mecânico') }}:</strong><br>
                          <span class="text-dark">{{ $point->notes }}</span>
                        </div>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
                @endif
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
      @endif

      <div class="divider divider-dashed my-5">
        <div class="divider-text text-uppercase small fw-bold">{{ __('Itens Verificados') }}</div>
      </div>

      <div class="table-responsive border rounded mb-4">
        <table class="table table-hover table-striped mb-0">
          <thead class="table-light">
            <tr>
              <th class="py-3 px-4">{{ __('Item de Inspeção') }}</th>
              <th class="text-center py-3">{{ __('Status') }}</th>
              <th class="py-3">{{ __('Observações / Notas') }}</th>
              <th class="text-center py-3 px-4">{{ __('Evidência') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($inspection->items as $item)
            <tr>
              <td class="px-4 fw-bold text-heading">{{ $item->checklistItem->name }}</td>
              <td class="text-center">
                @if($item->status === 'ok')
                <span class="badge bg-label-success px-3"><i class="icon-base ti tabler-check me-1"></i> OK</span>
                @elseif($item->status === 'not_ok')
                <span class="badge bg-label-danger px-3"><i class="icon-base ti tabler-x me-1"></i> {{ __('RUIM') }}</span>
                @else
                <span class="badge bg-label-secondary px-3">N/A</span>
                @endif
              </td>
              <td class="text-muted italic">{{ $item->observations ?? '-' }}</td>
              <td class="text-center px-4">
                @if($item->photo_path)
                <img src="{{ asset('storage/' . $item->photo_path) }}"
                  alt="{{ __('Evidência') }}"
                  class="rounded cursor-pointer shadow-sm border"
                  style="width: 50px; height: 50px; object-fit: cover;"
                  data-bs-toggle="modal"
                  data-bs-target="#photoModal{{ $item->id }}">

                <!-- Modal para Foto Grande -->
                <div class="modal fade" id="photoModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">{{ $item->checklistItem->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body p-4 text-center">
                        <img src="{{ asset('storage/' . $item->photo_path) }}" class="img-fluid rounded shadow-lg" style="max-height: 80vh;">
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

    <div class="card-footer bg-lighter border-top text-center py-4">
      <p class="mb-0 small text-muted">
        {{ __('Este documento é parte integrante do histórico do veículo no sistema') }} <strong>{{ config('app.name') }}</strong>.<br>
        {{ __('Gerado em') }} {{ now()->format('d/m/Y \à\s H:i') }}
      </p>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-send-email');
    if (btn) {
      e.preventDefault();
      const id = btn.getAttribute('data-id');
      const originalHtml = btn.innerHTML;
      const baseUrl = window.location.origin;

      btn.classList.add('disabled');
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> {{ __("Enviando...") }}';

      fetch(`${baseUrl}/ordens-servico/checklist/${id}/send-email`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          }
        })
        .then(response => response.json())
        .then(res => {
          const swalConfig = {
            icon: res.success ? 'success' : 'error',
            title: res.success ? "{{ __('Sucesso!') }}" : "{{ __('Ops!') }}",
            text: res.message
          };
          if (typeof Swal !== 'undefined') {
            Swal.fire(swalConfig);
          } else {
            alert(res.message);
          }
        })
        .catch(err => {
          console.error('Error:', err);
          Swal.fire({
            icon: 'error',
            title: "{{ __('Erro!') }}",
            text: "{{ __('Falha na comunicação com o servidor.') }}"
          });
        })
        .finally(() => {
          btn.classList.remove('disabled');
          btn.innerHTML = originalHtml;
        });
    }
  });

  // Init Tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  })
</script>

<style>
  /* Estilos para tela */
  .damage-marker-fixed {
    position: absolute;
    transform: translate(-50%, -50%);
    z-index: 10;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  #display-markers-container .marker-dot {
    width: 14px;
    height: 14px;
    background-color: #ea5455 !important;
    border: 2px solid #fff !important;
    border-radius: 50%;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  /* Ajustes específicos para Impressão */
  @media print {
    .no-print { display: none !important; }
    .card { border: none !important; shadow: none !important; }
    .bg-lighter { background-color: transparent !important; }
    
    /* Forçar cores na impressão */
    * {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    #vehicle-visual-display {
      width: 100% !important;
      max-width: 800px !important;
      margin: 0 auto !important;
    }

    .damage-marker-fixed .marker-dot {
      background-color: red !important;
      border: 2px solid white !important;
    }

    /* Garantir layout lado a lado no cabeçalho */
    .row.align-items-center {
      display: table !important;
      width: 100% !important;
    }
    .col-sm-7 { display: table-cell !important; width: 60% !important; vertical-align: middle !important; }
    .col-sm-5 { display: table-cell !important; width: 40% !important; vertical-align: middle !important; text-align: right !important; }
  }
</style>
@endsection