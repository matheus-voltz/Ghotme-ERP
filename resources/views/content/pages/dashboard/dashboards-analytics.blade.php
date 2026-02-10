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
</style>
@endsection

@section('content')
<div class="row g-6">
  <!-- Welcome & Highlights -->
  <div class="col-xl-8 col-lg-7 col-md-12">
    <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(105deg, #7367f0 0%, #4844a3 100%); color: white; position: relative; overflow: hidden;">
      <div class="card-body p-4">
        <div class="row align-items-center position-relative" style="z-index: 2;">
          <div class="col-md-9 col-12">
            <h3 class="text-white fw-bold mb-1">Olá, {{ explode(' ', auth()->user()->name)[0] }}! 👋</h3>
            <p class="text-white mb-3 opacity-75 small">Você tem <strong>{{ $osStats['running'] }}</strong> ordens em execução.</p>

            <div class="row g-2">
              <div class="col-3">
                <div style="background: rgba(255,255,255,0.1); padding: 8px; border-radius: 8px; text-align: center;">
                  <h5 class="text-white mb-0 fw-bold">{{ $osStats['pending'] }}</h5>
                  <small class="text-white opacity-75" style="font-size: 0.7rem;">Pendentes</small>
                </div>
              </div>
              <div class="col-3">
                <div style="background: rgba(255,255,255,0.1); padding: 8px; border-radius: 8px; text-align: center;">
                  <h5 class="text-white mb-0 fw-bold">{{ $osStats['running'] }}</h5>
                  <small class="text-white opacity-75" style="font-size: 0.7rem;">Ativas</small>
                </div>
              </div>
              <div class="col-3">
                <div style="background: rgba(255,255,255,0.1); padding: 8px; border-radius: 8px; text-align: center;">
                  <h5 class="text-white mb-0 fw-bold">{{ $osStats['finalized_today'] }}</h5>
                  <small class="text-white opacity-75" style="font-size: 0.7rem;">Hoje</small>
                </div>
              </div>
              <div class="col-3">
                <div style="background: rgba(255,255,255,0.1); padding: 8px; border-radius: 8px; text-align: center;">
                  <h5 class="text-white mb-0 fw-bold">{{ $osStats['total_month'] }}</h5>
                  <small class="text-white opacity-75" style="font-size: 0.7rem;">Mês</small>
                </div>
              </div>
            </div>
          </div>
        </div>
        <img src="{{ asset('assets/img/illustrations/boy-with-laptop-light.png') }}"
          class="position-absolute bottom-0 end-0 d-none d-lg-block"
          style="height: 120px; margin-right: -10px; margin-bottom: -10px; opacity: 0.8; z-index: 1;">
      </div>
    </div>
  </div>

  <!-- Quick Stats -->
  <div class="col-xl-4 col-lg-5 col-md-12">
    <div class="row g-4">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-body py-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <small class="text-muted d-block mb-1">Receita Mensal</small>
                <h4 class="mb-0 fw-bold">R$ {{ number_format($revenueMonth, 2, ',', '.') }}</h4>
              </div>
              <div class="stat-icon bg-label-success mb-0">
                <i class="ti tabler-currency-dollar fs-3"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-body py-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <small class="text-muted d-block mb-1">Conversão (Mês)</small>
                <h4 class="mb-0 fw-bold">{{ round($conversionRate) }}%</h4>
              </div>
              <div class="stat-icon bg-label-info mb-0">
                <i class="ti tabler-chart-pie fs-3"></i>
              </div>
            </div>
            <div class="progress mt-3" style="height: 6px;">
              <div class="progress-bar bg-info" style="width: {{ $conversionRate }}%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Performance Chart -->
  <div class="col-xl-8 col-lg-7">
    <div class="card h-100 shadow-sm">
      <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Atividade Comercial</h5>
        <div class="dropdown">
          <button class="btn p-0" type="button" data-bs-toggle="dropdown"><i class="ti tabler-dots-vertical fs-4 text-muted"></i></button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="javascript:void(0);">Relatórios</a></li>
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
      <div class="card-header py-3">
        <h5 class="mb-0">Status das OS</h5>
      </div>
      <div class="card-body d-flex flex-column justify-content-center">
        <div id="osDistributionChart"></div>
        <div class="mt-4">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <small class="text-muted fw-medium"><i class="ti tabler-circle-filled text-warning me-2"></i>Pendentes</small>
            <small class="fw-bold">{{ round(($osDistribution['pending'] / max(1, array_sum($osDistribution))) * 100) }}%</small>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-1">
            <small class="text-muted fw-medium"><i class="ti tabler-circle-filled text-info me-2"></i>Execução</small>
            <small class="fw-bold">{{ round(($osDistribution['running'] / max(1, array_sum($osDistribution))) * 100) }}%</small>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted fw-medium"><i class="ti tabler-circle-filled text-success me-2"></i>Finalizadas</small>
            <small class="fw-bold">{{ round(($osDistribution['finalized'] / max(1, array_sum($osDistribution))) * 100) }}%</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Activities -->
  <div class="col-xl-6">
    <div class="card h-100 shadow-sm">
      <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Últimas OS</h5>
        <a href="{{ route('ordens-servico') }}" class="btn btn-sm btn-label-primary">Ver Tudo</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-borderless align-middle table-sm">
          <thead class="bg-lighter">
            <tr>
              <th>ID</th>
              <th>CLIENTE</th>
              <th>STATUS</th>
            </tr>
          </thead>
          <tbody>
            @foreach($recentOS as $os)
            <tr>
              <td class="fw-bold">#{{ $os->id }}</td>
              <td><span class="text-heading fw-medium small">{{ str($os->client->name ?? $os->client->company_name)->limit(15) }}</span></td>
              <td>
                @php
                $statusConfig = [
                'pending' => ['color' => 'warning', 'label' => 'Pendente'],
                'running' => ['color' => 'info', 'label' => 'Execução'],
                'finalized' => ['color' => 'success', 'label' => 'Finalizada']
                ];
                $conf = $statusConfig[$os->status] ?? ['color' => 'secondary', 'label' => $os->status];
                @endphp
                <span class="badge badge-sm bg-label-{{ $conf['color'] }}">{{ $conf['label'] }}</span>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Top Services Chart -->
  <div class="col-xl-6">
    <div class="card h-100 shadow-sm">
      <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Top 5 Serviços (Receita)</h5>
        <i class="ti tabler-trending-up text-success fs-3"></i>
      </div>
      <div class="card-body">
        <div id="topServicesChart"></div>
      </div>
    </div>
  </div>

  <!-- Critical Alerts -->
  <div class="col-xl-6">
    <div class="card h-100 shadow-sm">
      <div class="card-header border-0 py-3 pb-0">
        <h5 class="mb-0 text-danger fw-bold"><i class="ti tabler-urgent me-2"></i>Atenção</h5>
      </div>
      <div class="card-body">
        <div class="d-flex flex-column gap-3">
          <div class="alert alert-danger d-flex align-items-center border-0 shadow-none mb-0 p-2">
            <div class="stat-icon bg-danger-subtle text-danger me-3 mb-0" style="width: 32px; height: 32px;">
              <i class="ti tabler-box fs-5"></i>
            </div>
            <div class="d-flex w-100 justify-content-between align-items-center">
              <div>
                <h7 class="mb-0 d-block fw-bold">Estoque Crítico</h7>
                <small>{{ $lowStockItems }} produtos baixo.</small>
              </div>
              <a href="{{ route('inventory.items') }}" class="btn btn-danger btn-xs">Repor</a>
            </div>
          </div>

          <div class="alert alert-warning d-flex align-items-center border-0 shadow-none mb-0 p-2">
            <div class="stat-icon bg-warning-subtle text-warning me-3 mb-0" style="width: 32px; height: 32px;">
              <i class="ti tabler-file-invoice fs-5"></i>
            </div>
            <div class="d-flex w-100 justify-content-between align-items-center">
              <div>
                <h7 class="mb-0 d-block fw-bold">Orçamentos</h7>
                <small>{{ $pendingBudgets }} pendentes.</small>
              </div>
              <a href="{{ route('budgets.pending') }}" class="btn btn-warning btn-xs text-white">Ver</a>
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
          height: 300,
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
            columnWidth: '15%',
            borderRadius: 3
          }
        },
        colors: ['#28c76f', '#ea5455', '#7367f0'],
        fill: {
          opacity: [0.3, 0.3, 1]
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
            labels: {
              formatter: (val) => 'R$ ' + val.toLocaleString('pt-BR')
            }
          },
          {
            opposite: true,
            labels: {
              show: false
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
          height: 200
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
                  fontSize: '1.2rem',
                  fontWeight: '600',
                  formatter: (val) => val
                },
                total: {
                  show: true,
                  label: 'Total',
                  formatter: () => @json(array_sum($osDistribution))
                }
              }
            }
          }
        }
      };
    if (osChartEl) new ApexCharts(osChartEl, osChartConfig).render();

    // Top Services Chart
    const topServicesEl = document.querySelector('#topServicesChart'),
      topServicesConfig = {
        series: [{
          name: 'Receita Total',
          data: @json($topServiceData)
        }],
        chart: {
          type: 'bar',
          height: 250,
          toolbar: {
            show: false
          }
        },
        plotOptions: {
          bar: {
            borderRadius: 4,
            horizontal: true,
            distributed: true,
            barHeight: '60%',
          }
        },
        colors: ['#7367f0', '#28c76f', '#00cfe8', '#ff9f43', '#ea5455'],
        dataLabels: {
          enabled: true,
          formatter: (val) => 'R$ ' + val.toLocaleString('pt-BR'),
          style: {
            fontSize: '10px'
          }
        },
        xaxis: {
          categories: @json($topServiceLabels),
          labels: {
            show: false
          },
          axisBorder: {
            show: false
          }
        },
        grid: {
          show: false
        },
        legend: {
          show: false
        },
        tooltip: {
          y: {
            formatter: (val) => 'R$ ' + val.toLocaleString('pt-BR')
          }
        }
      };
    if (topServicesEl) new ApexCharts(topServicesEl, topServicesConfig).render();
  });
</script>
@endpush
@endsection