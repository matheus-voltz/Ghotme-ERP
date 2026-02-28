@extends('layouts/layoutMaster')

@section('title', 'Master Control Panel - Ghotme')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js'])
@endsection

@section('content')
<div class="row g-6">
  <!-- Cabeçalho com IA -->
  <div class="col-12">
    <div class="card bg-label-primary border-0 shadow-none">
      <div class="card-body d-flex align-items-center justify-content-between py-4">
        <div>
            <h4 class="mb-1 fw-bold text-primary">Bem-vindo ao Comando Central, Matheus! 🚀</h4>
            <p class="mb-0">Aqui você tem o controle total sobre o ecossistema Ghotme.</p>
        </div>
        <button class="btn btn-primary" id="btn-master-ai">
            <i class="ti tabler-robot me-1"></i> IA Master Analyst
        </button>
      </div>
    </div>
  </div>

  <!-- Tráfego da Landing Page (Visitas) -->
  <div class="col-12">
    <div class="card shadow-sm border-0">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="card-title mb-0">Tráfego da Landing Page (Últimos 30 dias)</h5>
          <small class="text-muted">Total de {{ $stats['total_visits_30d'] }} visitas neste período</small>
        </div>
        <div class="avatar avatar-md">
          <span class="avatar-initial rounded bg-label-info"><i class="ti tabler-chart-line fs-4"></i></span>
        </div>
      </div>
      <div class="card-body">
        <div id="visitHistoryChart"></div>
      </div>
    </div>
  </div>

  <!-- Estatísticas Globais -->
  <div class="col-lg-3 col-sm-6">
    <div class="card h-100 shadow-sm border-start border-primary" style="border-left-width: 5px !important;">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar avatar-md me-2">
            <span class="avatar-initial rounded bg-label-primary"><i class="ti tabler-building-skyscraper fs-4"></i></span>
          </div>
          <h4 class="mb-0">{{ $stats['total_companies'] }}</h4>
        </div>
        <p class="mb-0">Empresas Cadastradas</p>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-sm-6">
    <div class="card h-100 shadow-sm border-start border-success" style="border-left-width: 5px !important;">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar avatar-md me-2">
            <span class="avatar-initial rounded bg-label-success"><i class="ti tabler-users fs-4"></i></span>
          </div>
          <h4 class="mb-0">{{ $stats['total_users'] }}</h4>
        </div>
        <p class="mb-0">Usuários Totais</p>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-sm-6">
    <div class="card h-100 shadow-sm border-start border-info" style="border-left-width: 5px !important;">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar avatar-md me-2">
            <span class="avatar-initial rounded bg-label-info"><i class="ti tabler-mail-fast fs-4"></i></span>
          </div>
          <h4 class="mb-0">{{ $stats['total_subscribers'] }}</h4>
        </div>
        <p class="mb-0">Leads Newsletter</p>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-sm-6">
    <div class="card h-100 shadow-sm border-start border-warning" style="border-left-width: 5px !important;">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar avatar-md me-2">
            <span class="avatar-initial rounded bg-label-warning"><i class="ti tabler-user-heart fs-4"></i></span>
          </div>
          <h4 class="mb-0">{{ $stats['total_clients'] }}</h4>
        </div>
        <p class="mb-0">Clientes dos Inquilinos</p>
      </div>
    </div>
  </div>

  <!-- Faturamento Ghotme Master -->
  <div class="col-lg-6 col-md-12">
    <div class="card h-100 shadow-sm border-0 bg-primary text-white">
      <div class="card-body d-flex align-items-center justify-content-between p-6">
        <div>
            <h5 class="text-white opacity-75 mb-1">Faturamento Total em Assinaturas</h5>
            <h2 class="text-white fw-extrabold mb-0">R$ {{ number_format($stats['global_revenue'], 2, ',', '.') }}</h2>
        </div>
        <div class="text-end">
            <span class="badge bg-white text-primary mb-1">Pendentes: R$ {{ number_format($stats['pending_revenue'], 2, ',', '.') }}</span>
            <p class="small mb-0 opacity-75">Baseado em faturas processadas</p>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6 col-md-12">
    <div class="card h-100 shadow-sm border-0 bg-label-danger">
      <div class="card-body d-flex align-items-center justify-content-between p-6">
        <div class="d-flex align-items-center">
            <div class="avatar avatar-lg me-4">
                <span class="avatar-initial rounded bg-danger"><i class="ti tabler-alert-triangle fs-2"></i></span>
            </div>
            <div>
                <h5 class="text-danger fw-bold mb-1">Radar de Saúde Técnica</h5>
                <p class="text-muted mb-0">Foram detectados <span class="fw-bold">{{ $stats['total_errors'] }} erros</span> no sistema.</p>
            </div>
        </div>
        <a href="{{ route('master.errors') }}" class="btn btn-danger">Inspecionar Falhas</a>
      </div>
    </div>
  </div>

  <!-- Campanhas e Novidades -->
  <div class="col-md-8">
    <div class="card h-100 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center border-bottom">
        <h5 class="card-title mb-0">Gestão Global de Newsletter</h5>
        <a href="{{ route('master.newsletter.create') }}" class="btn btn-primary">Nova Campanha Global</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Assunto</th>
              <th>Data</th>
              <th>Envios</th>
            </tr>
          </thead>
          <tbody>
            @foreach($campaigns as $camp)
            <tr>
              <td><span class="fw-bold">{{ $camp->subject }}</span></td>
              <td>{{ $camp->created_at->format('d/m/Y') }}</td>
              <td><span class="badge bg-label-primary">{{ $camp->sent_count }} pessoas</span></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Changelog / System Updates -->
  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Atualizar Ghotme (Changelog)</h5>
      </div>
      <div class="card-body pt-4">
        <form action="{{ route('master.system-update.store') }}" method="POST">
          @csrf
          <div class="mb-4">
            <label class="form-label">Título da Novidade</label>
            <input type="text" name="title" class="form-control" placeholder="Ex: Novo Módulo de IA" required>
          </div>
          <div class="mb-4">
            <label class="form-label">Descrição Curta</label>
            <textarea name="description" class="form-control" rows="3" placeholder="O que mudou?" required></textarea>
          </div>
          <div class="mb-4">
            <label class="form-label">Tipo</label>
            <select name="type" class="form-select">
              <option value="feature">Nova Funcionalidade</option>
              <option value="improvement">Melhoria</option>
              <option value="fix">Correção de Bug</option>
            </select>
          </div>
          <button type="submit" class="btn btn-success w-100">Salvar e Notificar IA</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Inscritos Recentes -->
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Últimos Assinantes (Landing Page)</h5>
      </div>
      <ul class="list-group list-group-flush">
        @foreach($stats['recent_subscribers'] as $sub)
        <li class="list-group-item d-flex justify-content-between align-items-center">
          {{ $sub->email }}
          <small class="text-muted">{{ $sub->created_at->diffForHumans() }}</small>
        </li>
        @endforeach
      </ul>
    </div>
  </div>

  <!-- Empresas Recentes -->
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Últimas Empresas Cadastradas</h5>
      </div>
      <ul class="list-group list-group-flush">
        @foreach($stats['recent_companies'] as $comp)
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <div>
            <span class="fw-bold">{{ $comp->name }}</span><br>
            <small class="text-muted">{{ $comp->document_number }}</small>
          </div>
          <span class="badge bg-label-info">{{ $comp->niche }}</span>
        </li>
        @endforeach
      </ul>
    </div>
  </div>
</div>
<!-- Modal IA Analysis -->
<div class="modal fade" id="aiMasterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title">Estratégia Ghotme (IA Insight)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="ai-insight-content" class="p-2" style="white-space: pre-wrap;">
            <div class="text-center p-5">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2 text-muted">Processando dados do ecossistema...</p>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnAi = document.getElementById('btn-master-ai');
    const modal = new bootstrap.Modal(document.getElementById('aiMasterModal'));
    const content = document.getElementById('ai-insight-content');

    btnAi.addEventListener('click', function() {
        modal.show();
        content.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Analisando o crescimento global...</p></div>';

        fetch('{{ route("master.ai-analysis") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = `<div class="animate__animated animate__fadeIn">${data.insight}</div>`;
            } else {
                content.innerHTML = '<p class="text-danger">Erro ao obter análise.</p>';
            }
        });
    });

    // Gráfico de Visitas
    const visitChartEl = document.querySelector('#visitHistoryChart');
    if (visitChartEl) {
        const visitChartConfig = {
            chart: {
                height: 250,
                type: 'area',
                parentHeightOffset: 0,
                toolbar: { show: false }
            },
            dataLabels: { enabled: false },
            stroke: { show: true, curve: 'smooth' },
            legend: { show: false },
            grid: {
                show: true,
                borderColor: '#e5e5e5',
                padding: { top: 0, bottom: 0, left: 0, right: 10 }
            },
            colors: ['#7367f0'],
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    shadeIntensity: 0.5,
                    gradientToColors: ['#7367f0'],
                    inverseColors: true,
                    opacityFrom: 0.6,
                    opacityTo: 0.1,
                    stops: [0, 100]
                }
            },
            series: [{
                name: 'Visitas',
                data: @json($stats['visit_chart_data'])
            }],
            xaxis: {
                categories: @json($stats['visit_chart_labels']),
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: '#a1acb8', fontSize: '13px' } }
            },
            yaxis: {
                labels: { show: false }
            },
            tooltip: { x: { show: true } }
        };
        const visitChart = new ApexCharts(visitChartEl, visitChartConfig);
        visitChart.render();
    }
});
</script>
@endsection
