@extends('layouts/layoutMaster')

@section('title', 'Portal de Atendimento (Sales Hub)')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('content')
<div class="row g-6">
  <!-- Coluna 1: Novos Clientes -->
  <div class="col-lg-6 col-md-6">
    <div class="card h-100 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Novos Clientes <span class="badge bg-label-primary rounded-pill ms-1">{{ $newClients->count() }}</span></h5>
        <small class="text-muted">Últimos 7 dias</small>
      </div>
      <div class="card-body p-0">
        <div class="list-group list-group-flush overflow-auto" style="max-height: 500px;">
          @forelse($newClients as $client)
          <div class="list-group-item d-flex align-items-center p-4 border-bottom">
            <div class="avatar avatar-md me-3">
              <span class="avatar-initial rounded-circle bg-label-{{ ['primary', 'success', 'info', 'warning'][rand(0,3)] }}">{{ substr($client->name ?? $client->company_name, 0, 1) }}</span>
            </div>
            <div class="flex-grow-1">
              <h6 class="mb-0">{{ $client->name ?? $client->company_name }}</h6>
              <small class="text-muted">{{ $client->created_at->diffForHumans() }}</small>
            </div>
            <a href="https://api.whatsapp.com/send?phone=55{{ preg_replace('/\D/', '', $client->whatsapp) }}" target="_blank" class="btn btn-sm btn-label-success btn-icon">
              <i class="ti tabler-brand-whatsapp"></i>
            </a>
          </div>
          @empty
          <div class="text-center p-5">
            <i class="ti tabler-user-plus fs-1 text-muted mb-2"></i>
            <p class="text-muted">Sem novos clientes esta semana.</p>
          </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  <!-- Coluna 2: Radar de Oportunidades (IA) -->
  <div class="col-lg-6 col-md-12">
    <div class="card h-100 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="ti tabler-antenna text-danger me-1"></i> Radar de Reativação</h5>
        <span class="badge bg-label-danger">IA Insights</span>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-6">Clientes que não realizam serviços há mais de 60 dias. Use a IA para criar uma abordagem personalizada.</p>

        <div id="radar-list">
          @foreach($staleClients as $client)
          <div class="card bg-label-secondary mb-4 border-0">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="mb-0">{{ $client->name ?? $client->company_name }}</h6>
                <button class="btn btn-sm btn-primary btn-ai-insight" data-id="{{ $client->id }}">
                  <i class="ti tabler-robot"></i> Analisar
                </button>
              </div>
              <div class="insight-container d-none mt-3 p-3 bg-white rounded border border-primary">
                <p class="small text-dark mb-2 insight-text"></p>
                <button class="btn btn-xs btn-success copy-insight">Copiar para WhatsApp</button>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-6 mt-6">
  <!-- Coluna 3: Follow-up Pós-Procedimento -->
  <div class="col-12">
    <div class="card shadow-sm border-start border-success" style="border-left-width: 5px !important;">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="ti tabler-heart-rate-monitor text-success me-1"></i> Acompanhamento Pós-Procedimento (Follow-up)</h5>
        <span class="badge bg-label-success">{{ $followUpAlerts->count() }} alertas hoje</span>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Paciente</th>
                <th>Procedimento</th>
                <th>Finalizado em</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              @forelse($followUpAlerts as $item)
              <tr>
                <td>
                  <div class="d-flex flex-column">
                    <span class="fw-bold">{{ $item->ordemServico->client->name }}</span>
                    <small class="text-muted">{{ $item->ordemServico->client->whatsapp }}</small>
                  </div>
                </td>
                <td><span class="badge bg-label-info">{{ $item->service->name }}</span></td>
                <td>{{ $item->ordemServico->updated_at->format('d/m/Y') }}</td>
                <td>
                  <button class="btn btn-sm btn-primary btn-follow-up-insight" data-id="{{ $item->id }}">
                    <i class="ti tabler-message-chatbot me-1"></i> Gerar Cuidado IA
                  </button>
                  <a href="https://api.whatsapp.com/send?phone=55{{ preg_replace('/\D/', '', $item->ordemServico->client->whatsapp) }}" target="_blank" class="btn btn-sm btn-icon btn-label-success">
                    <i class="ti tabler-brand-whatsapp"></i>
                  </a>
                </td>
              </tr>
              <tr class="follow-up-insight-row d-none" id="follow-up-{{ $item->id }}">
                <td colspan="4" class="bg-light p-4">
                  <div class="d-flex align-items-start gap-3">
                    <div class="flex-grow-1">
                        <p class="mb-2 fw-bold text-primary small">Sugestão de mensagem para pós-procedimento:</p>
                        <div class="p-3 bg-white border border-primary rounded mb-2 insight-text" style="white-space: pre-wrap;"></div>
                        <button class="btn btn-xs btn-success copy-insight">Copiar e fechar</button>
                    </div>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center py-5 text-muted">Nenhum paciente necessitando de acompanhamento hoje.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const aiButtons = document.querySelectorAll('.btn-ai-insight');

    aiButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        const clientId = this.dataset.id;
        const container = this.closest('.card-body').querySelector('.insight-container');
        const textEl = container.querySelector('.insight-text');
        const originalBtnHtml = this.innerHTML;

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch('{{ route("sales-hub.ai-insight") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
              client_id: clientId
            })
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              textEl.innerText = data.insight;
              container.classList.remove('d-none');
              container.classList.add('animate__animated', 'animate__fadeIn');
            }
          })
          .finally(() => {
            this.disabled = false;
            this.innerHTML = originalBtnHtml;
          });
      });
    });

    // Follow-up Insight
    document.querySelectorAll('.btn-follow-up-insight').forEach(btn => {
      btn.addEventListener('click', function() {
        const itemId = this.dataset.id;
        const row = document.getElementById(`follow-up-${itemId}`);
        const textEl = row.querySelector('.insight-text');
        const originalBtnHtml = this.innerHTML;

        if (!row.classList.contains('d-none')) {
            row.classList.add('d-none');
            return;
        }

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch('{{ route("sales-hub.follow-up-insight") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
              item_id: itemId
            })
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              textEl.innerText = data.insight;
              row.classList.remove('d-none');
            }
          })
          .finally(() => {
            this.disabled = false;
            this.innerHTML = originalBtnHtml;
          });
      });
    });

    document.querySelectorAll('.copy-insight').forEach(btn => {
      btn.addEventListener('click', function() {
        const textContainer = this.previousElementSibling;
        const text = textContainer.innerText;
        navigator.clipboard.writeText(text);
        
        const originalText = this.innerText;
        this.innerText = 'Copiado!';
        setTimeout(() => {
            this.innerText = originalText;
            // Se for do follow-up, fecha a linha
            const row = this.closest('.follow-up-insight-row');
            if (row) row.classList.add('d-none');
        }, 1500);
      });
    });
  });
</script>

<style>
  .animate-pulse {
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0% {
      transform: scale(1);
    }

    50% {
      transform: scale(1.1);
    }

    100% {
      transform: scale(1);
    }
  }
</style>
@endsection