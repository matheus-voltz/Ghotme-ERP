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

  @endif
  /* Estilos para impressão */
  @media print {

    .layout-menu,
    .layout-navbar,
    .content-footer,
    .no-print,
    .template-customizer-open-btn,
    .btn {
      display: none !important;
    }

    .layout-page,
    .content-wrapper,
    .container-xxl {
      padding: 0 !important;
      margin: 0 !important;
      max-width: 100% !important;
    }

    .card {
      border: none !important;
      box-shadow: none !important;
    }

    body {
      background: white !important;
    }

    .card-header .d-flex {
      display: none !important;
    }
  }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  @if(!$isPublic && auth()->check())
  <div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h4 class="mb-0">{{ __('Detalhes do Checklist') }}</h4>
    <a href="{{ route('ordens-servico.checklist') }}" class="btn btn-label-secondary">
      <i class="ti tabler-arrow-left me-1"></i> {{ __('Voltar') }}
    </a>
  </div>
  @else
  <div class="text-center mb-6">
    <h3 class="mb-1 fw-bold">{{ __('Checklist de Entrada') }}</h3>
    <p class="text-muted">{{ __('Documento gerado pela oficina para sua segurança.') }}</p>
  </div>
  @endif

  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between flex-wrap gap-3">
      <h5 class="mb-0">{{ __('Checklist') }} #{{ $inspection->id }}</h5>
      @if(!$isPublic && auth()->check())
      <div class="d-flex gap-2 no-print">
        <button onclick="window.print()" class="btn btn-label-secondary">
          <i class="ti tabler-printer me-1"></i> {{ __('Imprimir') }}
        </button>

        @php
        $url = route('public.checklist.show', $inspection->id) . ($inspection->token ? "?token=" . $inspection->token : "");
        $whatsappMsg = "Olá! Segue o checklist de entrada do seu veículo " . $inspection->veiculo->modelo . " (" . $inspection->veiculo->placa . ").\nVocê pode visualizar os detalhes e fotos aqui: " . $url;
        $whatsappUrl = "https://api.whatsapp.com/send?phone=55" . preg_replace('/\D/', '', $inspection->veiculo->client->whatsapp ?? '') . "&text=" . urlencode($whatsappMsg);

        $emailSubject = "Checklist de Entrada - Veículo " . $inspection->veiculo->placa;
        $emailBody = "Olá, segue o link do seu checklist: " . $url;
        $emailUrl = "mailto:" . ($inspection->veiculo->client->email ?? '') . "?subject=" . urlencode($emailSubject) . "&body=" . urlencode($emailBody);
        @endphp

        <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-success">
          <i class="ti tabler-brand-whatsapp me-1"></i> WhatsApp
        </a>
        <button type="button" class="btn btn-primary btn-send-email" data-id="{{ $inspection->id }}">
          <i class="ti tabler-mail me-1"></i> {{ __('E-mail') }}
        </button>
      </div>
      @endif
    </div>
    <div class="card-body">
      <div class="row mb-4">
        <div class="col-md-4">
          <h6>{{ __('Veículo') }}</h6>
          <p><strong>{{ __('Placa') }}:</strong> {{ $inspection->veiculo->placa }}<br>
            <strong>{{ __('Modelo') }}:</strong> {{ $inspection->veiculo->modelo }}<br>
            <strong>{{ __('KM') }}:</strong> {{ $inspection->km_current }}
          </p>
        </div>
        <div class="col-md-4">
          <h6>{{ __('Informações') }}</h6>
          <p><strong>{{ __('Data') }}:</strong> {{ $inspection->created_at->format('d/m/Y H:i') }}<br>
            <strong>{{ __('Responsável') }}:</strong> {{ $inspection->user->name }}<br>
            <strong>{{ __('Combustível') }}:</strong> {{ $inspection->fuel_level }}
          </p>
        </div>
        @if($inspection->notes)
        <div class="col-md-4">
          <h6>{{ __('Observações Gerais') }}</h6>
          <p>{{ $inspection->notes }}</p>
        </div>
        @endif
      </div>

      <!-- Visual Inspection Display -->
      @if($inspection->damagePoints->isNotEmpty())
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Vistoria Visual (Tira-Teima)</h5>
        </div>
        <div class="card-body text-center">
          <div id="vehicle-visual-inspection" style="position: relative; display: inline-block;">
            <!-- Car Blueprint SVG -->
            <!-- Detailed Car Blueprint SVG -->
            <svg id="car-blueprint" width="900" height="300" viewBox="0 0 900 300" xmlns="http://www.w3.org/2000/svg" style="max-width: 100%; height: auto; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
              <defs>
                <style>
                  .car-body {
                    fill: #f1f3f5;
                    stroke: #adb5bd;
                    stroke-width: 2;
                    stroke-linejoin: round;
                  }

                  .car-window {
                    fill: #e9ecef;
                    stroke: #ced4da;
                    stroke-width: 1;
                  }

                  .car-wheel {
                    fill: #343a40;
                  }

                  .car-light {
                    fill: #fff;
                    stroke: #ced4da;
                  }

                  .view-label {
                    font-family: sans-serif;
                    font-size: 13px;
                    fill: #6c757d;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                  }
                </style>
              </defs>

              <!-- Left Side View -->
              <g transform="translate(40, 60)">
                <!-- Body -->
                <path class="car-body" d="M10,70 L25,40 L70,25 L160,25 L215,40 L230,70 L230,90 L205,90 A22,22 0 0,1 161,90 L79,90 A22,22 0 0,1 35,90 L10,90 Z" />
                <!-- Windows -->
                <path class="car-window" d="M35,42 L68,28 L115,28 L115,42 Z" />
                <path class="car-window" d="M120,28 L155,28 L195,42 L120,42 Z" />
                <!-- Wheels -->
                <circle class="car-wheel" cx="57" cy="90" r="18" />
                <circle class="car-wheel" cx="183" cy="90" r="18" />
                <text x="120" y="140" text-anchor="middle" class="view-label">Lateral Esquerda</text>
              </g>

              <!-- Front View -->
              <g transform="translate(310, 60)">
                <path class="car-body" d="M10,90 L10,55 Q10,25 60,25 Q110,25 110,55 L110,90 Z" />
                <!-- Windshield -->
                <path class="car-window" d="M20,55 L30,30 L90,30 L100,55 Z" />
                <!-- Lights -->
                <rect class="car-light" x="15" y="65" width="20" height="10" rx="2" />
                <rect class="car-light" x="85" y="65" width="20" height="10" rx="2" />
                <!-- Grille -->
                <rect x="40" y="65" width="40" height="15" rx="2" fill="#e9ecef" stroke="#ced4da" />
                <text x="60" y="140" text-anchor="middle" class="view-label">Frente</text>
              </g>

              <!-- Rear View -->
              <g transform="translate(460, 60)">
                <path class="car-body" d="M10,90 L10,55 Q10,25 60,25 Q110,25 110,55 L110,90 Z" />
                <!-- Rear Window -->
                <path class="car-window" d="M25,50 L35,30 L85,30 L95,50 Z" />
                <!-- Lights -->
                <rect x="15" y="60" width="25" height="12" rx="2" fill="#e03131" opacity="0.8" />
                <rect x="80" y="60" width="25" height="12" rx="2" fill="#e03131" opacity="0.8" />
                <!-- Plate Area -->
                <rect x="45" y="65" width="30" height="15" fill="#fff" stroke="#dee2e6" />
                <text x="60" y="140" text-anchor="middle" class="view-label">Traseira</text>
              </g>

              <!-- Right Side View -->
              <g transform="translate(610, 60) scale(-1, 1) translate(-240, 0)">
                <path class="car-body" d="M10,70 L25,40 L70,25 L160,25 L215,40 L230,70 L230,90 L205,90 A22,22 0 0,1 161,90 L79,90 A22,22 0 0,1 35,90 L10,90 Z" />
                <!-- Windows -->
                <path class="car-window" d="M35,42 L68,28 L115,28 L115,42 Z" />
                <path class="car-window" d="M120,28 L155,28 L195,42 L120,42 Z" />
                <!-- Wheels -->
                <circle class="car-wheel" cx="57" cy="90" r="18" />
                <circle class="car-wheel" cx="183" cy="90" r="18" />
                <!-- Label needs un-mirroring -->
                <text x="120" y="140" text-anchor="middle" class="view-label" transform="scale(-1, 1) translate(-240, 0)">Lateral Direita</text>
              </g>
            </svg>

            <!-- Markers -->
            @foreach($inspection->damagePoints as $point)
            <div class="damage-marker" style="left: {{ $point->x_coordinate }}%; top: {{ $point->y_coordinate }}%;">
              <div class="marker-dot"></div>
              <div class="marker-tooltip">
                <span>{{ $point->notes }}</span>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
      @endif
      <style>
        .damage-marker {
          position: absolute;
          transform: translate(-50%, -50%);
          z-index: 10;
          width: 30px;
          height: 30px;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
        }

        .marker-dot {
          width: 16px;
          height: 16px;
          background-color: rgba(234, 84, 85, 0.9);
          border: 2px solid #fff;
          border-radius: 50%;
          box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
        }

        .marker-tooltip {
          display: none;
          position: absolute;
          bottom: 100%;
          left: 50%;
          transform: translateX(-50%);
          background: #333;
          color: #fff;
          padding: 5px 10px;
          border-radius: 4px;
          white-space: nowrap;
          font-size: 12px;
          margin-bottom: 5px;
          z-index: 20;
        }

        .damage-marker:hover .marker-tooltip {
          display: block;
        }
      </style>

      <div class="table-responsive">
        <table class="table table-striped border-top">
          <thead>
            <tr>
              <th>{{ __('Item') }}</th>
              <th class="text-center">{{ __('Status') }}</th>
              <th>{{ __('Observações') }}</th>
              <th class="text-center">{{ __('Foto') }}</th>
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
                <span class="badge bg-label-danger">{{ __('RUIM') }}</span>
                @else
                <span class="badge bg-label-secondary">N/A</span>
                @endif
              </td>
              <td>{{ $item->observations ?? '-' }}</td>
              <td class="text-center">
                @if($item->photo_path)
                <img src="{{ asset('storage/' . $item->photo_path) }}"
                  alt="{{ __('Evidência') }}"
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
</script>
@endsection