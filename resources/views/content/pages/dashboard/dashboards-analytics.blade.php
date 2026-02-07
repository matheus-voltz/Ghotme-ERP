@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Dashboard Profissional')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
'resources/assets/vendor/libs/swiper/swiper.scss',
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js'])
@endsection

@section('page-style')
<style>
  .card-gradient {
    background: linear-gradient(135deg, var(--bs-primary) 0%, #4844a3 100%);
    color: white;
  }

  .stat-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    margin-bottom: 1rem;
  }

  .bg-glass {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
  }

  .trend-up {
    color: #28c76f;
  }

  .trend-down {
    color: #ea5455;
  }
</style>
@endsection

@section('content')
<div class="row g-6">
  <!-- Welcome & Highlights -->
  <div class="col-xl-8 col-lg-7 col-md-12">
    <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(105deg, #7367f0 0%, #4844a3 100%); color: white; position: relative; overflow: hidden;">
      <div class="card-body p-4 p-md-5">
        <div class="row align-items-center position-relative" style="z-index: 2;">
          <div class="col-md-8 col-12">
            <h2 class="text-white fw-bold mb-2">Bem-vindo, {{ auth()->user()->name }}! 🚀</h2>
            <p class="text-white mb-4 opacity-75 fs-5">Sua oficina está ativa! Você tem <strong>{{ $osStats['running'] }}</strong> ordens em execução agora.</p>

            <div class="row g-3">
              <div class="col-6 col-md-3">
                <div style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); padding: 12px; border-radius: 12px; text-align: center;">
                  <h4 class="text-white mb-0 fw-bold">{{ $osStats['pending'] }}</h4>
                  <small class="text-white opacity-75">Pendentes</small>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); padding: 12px; border-radius: 12px; text-align: center;">
                  <h4 class="text-white mb-0 fw-bold">{{ $osStats['running'] }}</h4>
                  <small class="text-white opacity-75">Em Curso</small>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); padding: 12px; border-radius: 12px; text-align: center;">
                  <h4 class="text-white mb-0 fw-bold">{{ $osStats['finalized_today'] }}</h4>
                  <small class="text-white opacity-75">Concluídas</small>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); padding: 12px; border-radius: 12px; text-align: center;">
                  <h4 class="text-white mb-0 fw-bold">{{ $osStats['total_month'] }}</h4>
                  <small class="text-white opacity-75">Este Mês</small>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Illustration -->
        <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}"
          alt="Welcome"
          class="position-absolute bottom-0 end-0 d-none d-lg-block"
          style="height: 180px; object-fit: contain; z-index: 1; opacity: 0.9;">
      </div>
    </div>
  </div>

  <!-- Quick Stats -->
  <div class="col-xl-4 col-lg-5 col-md-12">
    <div class="row g-4">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div class="stat-icon bg-label-success">
                <i class="ti tabler-currency-dollar fs-3"></i>
              </div>
              <span class="badge bg-label-success">+12.5%</span>
            </div>
            <h5 class="mb-1 text-muted">Receita Mensal</h5>
            <h3 class="mb-0 fw-bold">R$ {{ number_format($revenueMonth, 2, ',', '.') }}</h3>
          </div>
        </div>
      </div>

      <!-- Conversion & Budget Stats -->
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-body d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="stat-icon bg-label-info mb-0">
                  <i class="ti tabler-chart-pie fs-3"></i>
                </div>
                <span class="badge bg-label-info">Funil de Vendas</span>
              </div>
              <h5 class="mb-2 text-muted">Conversão (Mês)</h5>
              <div class="d-flex align-items-baseline gap-2 mb-1">
                <h2 class="mb-0 fw-bold">{{ round($conversionRate) }}%</h2>
                <small class="text-success fw-medium"><i class="ti tabler-arrow-up-right"></i> {{ $approvedBudgetsMonth }} aprovados</small>
              </div>
              <p class="small text-muted mb-4">{{ $totalBudgetsMonth }} orçamentos totais</p>
            </div>

            <div class="progress mb-1" style="height: 8px;">
              <div class="progress-bar bg-info" role="progressbar" style="width: {{ $conversionRate }}%" aria-valuenow="{{ $conversionRate }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="d-flex justify-content-between">
              <small class="text-muted">Meta: 70%</small>
              <small class="fw-medium">{{ round($conversionRate) }}%</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Performance Chart -->
  <div class="col-xl-8 col-lg-7">
    <div class="card h-100 shadow-sm">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <div>
          <h5 class="card-title mb-0">Atividade Comercial</h5>
          <small class="text-muted">Vendas, Receitas e Orçamentos</small>
        </div>
        <div class="dropdown">
          <button class="btn p-0" type="button" data-bs-toggle="dropdown"><i class="ti tabler-dots-vertical fs-4 text-muted"></i></button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="javascript:void(0);">Relatórios Completos</a></li>
          </ul>
        </div>
      </div>
      <div class="card-body">
        <div id="performanceCombinedChart"></div>
      </div>
    </div>
  </div>

  <!-- OS Distribution -->
  <div class="col-xl-4 col-lg-5">
    <div class="card h-100 shadow-sm border-0">
      <div class="card-header">
        <h5 class="mb-0">Distribuição por Status</h5>
        <small class="text-muted">Volume de Ordens de Serviço</small>
      </div>
      <div class="card-body d-flex flex-column justify-content-center">
        <div id="osDistributionChart"></div>
        <div class="mt-4">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted fw-medium"><i class="ti tabler-circle-filled text-warning me-2"></i>Pendentes</span>
            <span class="fw-bold">{{ round(($osDistribution['pending'] / max(1, array_sum($osDistribution))) * 100) }}%</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted fw-medium"><i class="ti tabler-circle-filled text-info me-2"></i>Em Execução</span>
            <span class="fw-bold">{{ round(($osDistribution['running'] / max(1, array_sum($osDistribution))) * 100) }}%</span>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted fw-medium"><i class="ti tabler-circle-filled text-success me-2"></i>Finalizadas</span>
            <span class="fw-bold">{{ round(($osDistribution['finalized'] / max(1, array_sum($osDistribution))) * 100) }}%</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Activities -->
  <div class="col-xl-6">
    <div class="card h-100 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Atividades Recentes (OS)</h5>
        <a href="{{ route('ordens-servico') }}" class="btn btn-sm btn-label-primary">Ver Tudo</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-borderless align-middle">
          <thead class="bg-lighter">
            <tr>
              <th>ID</th>
              <th>CLIENTE</th>
              <th>VEÍCULO</th>
              <th>STATUS</th>
            </tr>
          </thead>
          <tbody>
            @foreach($recentOS as $os)
            <tr>
              <td class="fw-bold">#{{ $os->id }}</td>
              <td>
                <div class="d-flex flex-column">
                  <span class="text-heading fw-medium">{{ str($os->client->name ?? $os->client->company_name)->limit(20) }}</span>
                </div>
              </td>
              <td><span class="badge bg-label-secondary font-monospace">{{ $os->veiculo->placa }}</span></td>
              <td>
                @php
                $statusConfig = [
                'pending' => ['color' => 'warning', 'label' => 'Pendente'],
                'running' => ['color' => 'info', 'label' => 'Execução'],
                'finalized' => ['color' => 'success', 'label' => 'Finalizada']
                ];
                $conf = $statusConfig[$os->status] ?? ['color' => 'secondary', 'label' => $os->status];
                @endphp
                <span class="badge bg-label-{{ $conf['color'] }}">{{ $conf['label'] }}</span>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Critical Alerts -->
  <div class="col-xl-6">
    <div class="card h-100 shadow-sm">
      <div class="card-header border-0 pb-0">
        <h5 class="mb-0 text-danger fw-bold"><i class="ti tabler-urgent me-2"></i>Alertas de Atenção</h5>
      </div>
      <div class="card-body">
        <div class="d-flex flex-column gap-4 mt-3">
          <div class="alert alert-danger d-flex align-items-center border-0 shadow-none mb-0">
            <div class="stat-icon bg-danger-subtle text-danger me-4 mb-0">
              <i class="ti tabler-box"></i>
            </div>
            <div class="d-flex w-100 justify-content-between align-items-center">
              <div>
                <h6 class="mb-1">Reposição de Estoque</h6>
                <p class="mb-0 small">{{ $lowStockItems }} produtos críticos.</p>
              </div>
              <a href="{{ route('inventory.items') }}" class="btn btn-danger btn-sm">Repor</a>
            </div>
          </div>

          <div class="alert alert-warning d-flex align-items-center border-0 shadow-none mb-0">
            <div class="stat-icon bg-warning-subtle text-warning me-4 mb-0">
              <i class="ti tabler-file-invoice"></i>
            </div>
            <div class="d-flex w-100 justify-content-between align-items-center">
              <div>
                <h6 class="mb-1">Orçamentos Pendentes</h6>
                <p class="mb-0 small">{{ $pendingBudgets }} clientes aguardam retorno.</p>
              </div>
              <a href="{{ route('budgets.pending') }}" class="btn btn-warning btn-sm text-white">Ver</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Combined Performance Chart
    const performanceChartEl = document.querySelector('#performanceCombinedChart'),
      performanceChartConfig = {
        series: [{
            name: 'Receita',
            type: 'area',
            data: @json($revenueTrends)
          },
          {
            name: 'Despesa',
            type: 'area',
            data: @json($expenseTrends)
          },
          {
            name: 'Orçamentos',
            type: 'column',
            data: @json($budgetTrends)
          }
        ],
        chart: {
          height: 350,
          type: 'line',
          stacked: false,
          toolbar: {
            show: false
          }
        },
        stroke: {
          width: [2, 2, 0],
          curve: 'smooth'
        },
        plotOptions: {
          bar: {
            columnWidth: '20%',
            borderRadius: 4
          }
        },
        colors: ['#28c76f', '#ea5455', '#7367f0'],
        fill: {
          opacity: [0.35, 0.35, 1],
          gradient: {
            inverseColors: false,
            shade: 'light',
            type: "vertical",
            opacityFrom: 0.85,
            opacityTo: 0.55,
            stops: [0, 100, 100, 100]
          }
        },
        xaxis: {
          categories: @json($months),
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false
          }
        },
        yaxis: [{
            title: {
              text: 'Financeiro'
            },
            labels: {
              formatter: (val) => 'R$ ' + val.toLocaleString('pt-BR')
            }
          },
          {
            opposite: true,
            title: {
              text: 'Qtd. Orçamentos'
            }
          }
        ],
        grid: {
          borderColor: '#f1f1f1',
          padding: {
            top: 0,
            bottom: 0
          }
        },
        tooltip: {
          shared: true,
          intersect: false,
          y: {
            formatter: (val) => val.toLocaleString('pt-BR')
          }
        }
      };
    if (performanceChartEl) new ApexCharts(performanceChartEl, performanceChartConfig).render();

    // OS Distribution Chart
    const osChartEl = document.querySelector('#osDistributionChart'),
      osChartConfig = {
        series: [@json($osDistribution['pending']), @json($osDistribution['running']), @json($osDistribution['finalized'])],
        chart: {
          type: 'donut',
          height: 250
        },
        labels: ['Pendentes', 'Execução', 'Finalizadas'],
        colors: ['#ff9f43', '#00cfe8', '#28c76f'],
        stroke: {
          width: 0
        },
        dataLabels: {
          enabled: false
        },
        legend: {
          show: false
        },
        plotOptions: {
          pie: {
            donut: {
              size: '75%',
              labels: {
                show: true,
                value: {
                  fontSize: '1.5rem',
                  fontWeight: '600',
                  formatter: (val) => val
                },
                total: {
                  show: true,
                  label: 'Total OS',
                  formatter: () => @json(array_sum($osDistribution))
                }
              }
            }
          }
        }
      };
    if (osChartEl) new ApexCharts(osChartEl, osChartConfig).render();
  });
</script>
@endpush
@endsection