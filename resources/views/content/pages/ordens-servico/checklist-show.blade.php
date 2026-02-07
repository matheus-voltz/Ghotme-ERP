@extends($layout)

@section('title', 'Detalhes do Checklist')

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

@section('content')
<style>
  @if($isPublic) .layout-menu, .template-customizer-open-btn {
    display: none !important;
  }

  .layout-page {
    padding-left: 0 !important;
  }

  @endif
</style>

<div class="container-xxl flex-grow-1 container-p-y">

  @if(!$isPublic && Auth::check())
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Detalhes do Checklist</h4>
    <a href="{{ route('ordens-servico.checklist') }}" class="btn btn-label-secondary">
      <i class="ti tabler-arrow-left me-1"></i> Voltar
    </a>
  </div>
  @else
  <div class="text-center mb-6">
    <h3 class="mb-1 fw-bold">Checklist de Entrada</h3>
    <p class="text-muted">Documento gerado pela oficina para sua segurança.</p>
  </div>
  @endif

  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between flex-wrap gap-3">
      <h5 class="mb-0">Checklist #{{ $inspection->id }}</h5>
      @if(!$isPublic && auth()->check())
      <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-label-secondary">
          <i class="ti tabler-printer me-1"></i> Imprimir
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
          <i class="ti tabler-mail me-1"></i> E-mail
        </button>
      </div>
      @endif
    </div>
    <div class="card-body">
      <div class="row mb-4">
        <div class="col-md-4">
          <h6>Veículo</h6>
          <p><strong>Placa:</strong> {{ $inspection->veiculo->placa }}<br>
            <strong>Modelo:</strong> {{ $inspection->veiculo->modelo }}<br>
            <strong>KM:</strong> {{ $inspection->km_current }}
          </p>
        </div>
        <div class="col-md-4">
          <h6>Informações</h6>
          <p><strong>Data:</strong> {{ $inspection->created_at->format('d/m/Y H:i') }}<br>
            <strong>Responsável:</strong> {{ $inspection->user->name }}<br>
            <strong>Combustível:</strong> {{ $inspection->fuel_level }}
          </p>
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
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enviando...';

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
            title: res.success ? 'Sucesso!' : 'Ops!',
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
            title: 'Erro!',
            text: 'Falha na comunicação com o servidor.'
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