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
@if(isset($lastUpdate))
<div class="row mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-none bg-label-primary">
      <div class="card-body d-flex align-items-center justify-content-between py-3">
        <div class="d-flex align-items-center">
          <div class="avatar avatar-sm me-3">
            <span class="avatar-initial rounded bg-primary"><i class="ti tabler-rocket fs-5"></i></span>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-primary">Novidade: {{ $lastUpdate->title }}</h6>
            <small class="text-muted d-none d-md-inline">{{ str($lastUpdate->description)->limit(80) }}</small>
          </div>
        </div>
        <a href="{{ route('whats-new') }}" class="btn btn-sm btn-primary">Ver Tudo</a>
      </div>
    </div>
  </div>
</div>
@endif

<div class="row g-4">
  <!-- Welcome & Highlights -->
  <div class="col-xl-8 col-lg-7 col-md-12">
    <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(72.47deg, #7367f0 22.16%, rgba(115, 103, 240, 0.7) 76.47%); color: white; position: relative; overflow: hidden;">
      <div class="card-body p-4 position-relative z-1">
        <div class="row align-items-center">
          <div class="col-md-8 col-12">
            <h4 class="text-white mb-2 fw-bold">{{ __('Welcome back') }}, {{ explode(' ', auth()->user()->name)[0] }}! 👋</h4>
            <p class="text-white mb-4 opacity-75">{{ __('Your workshop has') }} <strong class="fs-5 text-white">{{ $osStats['running'] }}</strong> {{ __('orders running now.') }}</p>

            <div class="row g-3 mt-4">
              <div class="col-sm-6 col-auto">
                <div class="d-flex align-items-center bg-white rounded-3 p-3 shadow-sm" style="border: 1px solid rgba(255,255,255,0.5);">
                  <div class="avatar avatar-md me-3">
                    <span class="avatar-initial rounded-circle bg-label-warning shadow-sm">
                      <i class="ti tabler-clock fs-3"></i>
                    </span>
                  </div>
                  <div class="d-flex flex-column">
                    <h4 class="mb-0 fw-bolder text-dark" style="color: #343a40 !important;">{{ $osStats['pending'] }}</h4>
                    <span class="small text-uppercase fw-bold ls-1 text-secondary" style="font-size: 0.75rem; color: #6c757d !important;">{{ __('Pending') }}</span>
                  </div>
                </div>
              </div>

              <div class="col-sm-6 col-auto">
                <div class="d-flex align-items-center bg-white rounded-3 p-3 shadow-sm" style="border: 1px solid rgba(255,255,255,0.5);">
                  <div class="avatar avatar-md me-3">
                    <span class="avatar-initial rounded-circle bg-label-success shadow-sm">
                      <i class="ti tabler-circle-check fs-3"></i>
                    </span>
                  </div>
                  <div class="d-flex flex-column">
                    <h4 class="mb-0 fw-bolder text-dark" style="color: #343a40 !important;">{{ $osStats['finalized_today'] }}</h4>
                    <span class="small text-uppercase fw-bold ls-1 text-secondary" style="font-size: 0.75rem; color: #6c757d !important;">{{ __('Completed Today') }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <img src="{{ asset('assets/img/illustrations/boy-with-laptop-light.png') }}"
        class="position-absolute bottom-0 end-0 d-none d-md-block me-4 mb-2"
        style="height: 160px; object-fit: contain; z-index: 0;" alt="Welcome">
    </div>
  </div>

  <!-- Quick Stats Column -->
  <div class="col-xl-4 col-lg-5 col-md-12">
    <div class="row g-4">
      <!-- Receita Mensal -->
      <div class="col-12 col-sm-6 col-md-12">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex flex-column justify-content-center p-4">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <span class="text-muted fw-medium d-block mb-1 text-uppercase small ls-1">{{ __('Monthly Revenue') }}</span>
                <h3 class="mb-0 fw-bold text-heading">R$ {{ number_format($revenueMonth, 2, ',', '.') }}</h3>
              </div>
              <div class="avatar avatar-md bg-label-success rounded p-1">
                <i class="ti tabler-currency-dollar fs-2"></i>
              </div>
            </div>
            @isset($revenueGrowth)
            <div class="mt-2">
              <span class="badge {{ $revenueGrowth >= 0 ? 'bg-label-success' : 'bg-label-danger' }} rounded-pill">
                <i class="ti {{ $revenueGrowth >= 0 ? 'tabler-arrow-up' : 'tabler-arrow-down' }} fs-6 me-1"></i>
                {{ number_format(abs($revenueGrowth), 1) }}%
              </span>
              <small class="text-muted ms-1">vs {{ __('last month') }}</small>
            </div>
            @endisset
          </div>
        </div>
      </div>

      <!-- Conversão -->
      <div class="col-12 col-sm-6 col-md-12">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex flex-column justify-content-center p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <span class="text-muted fw-medium d-block mb-1 text-uppercase small ls-1">{{ __('Conversion Rate') }}</span>
                <h3 class="mb-0 fw-bold text-heading">{{ round($conversionRate) }}%</h3>
              </div>
              <div class="avatar avatar-md bg-label-info rounded p-1">
                <i class="ti tabler-chart-pie fs-2"></i>
              </div>
            </div>
            <div class="progress" style="height: 8px; border-radius: 4px;">
              <div class="progress-bar bg-info" role="progressbar" style="width:{{ $conversionRate }}%" aria-valuenow="{{ $conversionRate }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <small class="text-muted mt-2">{{ __('Approved budgets this month') }}</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Performance Chart Row -->
  <div class="col-xl-8 col-lg-12 mt-4">
    <div class="card h-100 shadow-sm border-0">
      <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center bg-transparent">
        <div>
          <h5 class="card-title mb-0 fw-bold">{{ __('Business Activity') }}</h5>
          <small class="text-muted">{{ __('Revenue, Expenses and Budgets') }}</small>
        </div>
        <div class="dropdown">
          <button class="btn p-0" type="button" data-bs-toggle="dropdown"><i class="ti tabler-dots-vertical fs-4 text-muted"></i></button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="javascript:void(0);">{{ __('Detailed Reports') }}</a></li>
          </ul>
        </div>
      </div>
      <div class="card-body px-2 h-100">
        <div id="performanceCombinedChart"></div>
      </div>
    </div>
  </div>

  <!-- Status & Profitability Column -->
  <div class="col-xl-4 col-lg-12 mt-4">
    <div class="row g-4">
      <!-- Ticket Médio -->
      <div class="col-xl-12 col-md-6">
        <div class="card shadow-sm border-0 bg-label-primary bg-opacity-10">
          <div class="card-body d-flex align-items-center">
            <div class="avatar avatar-md bg-label-primary rounded p-1 me-3">
              <i class="ti tabler-receipt-2 fs-2"></i>
            </div>
            <div>
              <span class="text-muted d-block small ls-1 text-uppercase fw-bold">{{ __('Average Ticket') }}</span>
              <h4 class="mb-0 fw-bold">R$ {{ number_format($avgTicket, 2, ',', '.') }}</h4>
            </div>
          </div>
        </div>
      </div>
      <!-- Taxa de Retenção -->
      <div class="col-xl-12 col-md-6">
        <div class="card shadow-sm border-0 bg-label-info bg-opacity-10">
          <div class="card-body d-flex align-items-center">
            <div class="avatar avatar-md bg-label-info rounded p-1 me-3">
              <i class="ti tabler-users-group fs-2"></i>
            </div>
            <div>
              <span class="text-muted d-block small ls-1 text-uppercase fw-bold">{{ __('Retention Rate') }}</span>
              <h4 class="mb-0 fw-bold">{{ number_format($retentionRate, 1) }}%</h4>
            </div>
          </div>
        </div>
      </div>
      <!-- Status OS -->
      <div class="col-xl-12 col-md-12">
        <div class="card shadow-sm border-0">
          <div class="card-header py-3 bg-transparent border-bottom">
            <h5 class="mb-0 fw-bold">{{ __('OS Status') }}</h5>
          </div>
          <div class="card-body d-flex align-items-center justify-content-center p-2">
            <div class="w-100">
              <div id="osDistributionChart"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Activities & Profitability Row -->
  <div class="col-xl-6 col-lg-12">
    <div class="card h-100 shadow-sm border-0">
      <div class="card-header py-3 bg-transparent border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">{{ __('Recent Service Orders') }}</h5>
        <a href="{{ route('ordens-servico') }}" class="btn btn-sm btn-label-primary">{{ __('View All') }}</a>
      </div>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th class="ps-4 py-3 text-muted text-uppercase small fw-bold">{{ __('OS') }} / {{ __('Customer') }}</th>
              <th class="py-3 text-muted text-uppercase small fw-bold">{{ __('Status') }}</th>
              <th class="text-end pe-4 py-3 text-muted text-uppercase small fw-bold">{{ __('Value') }}</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @foreach($recentOS as $os)
            @php
            $clientName = $os->client->name ?? $os->client->company_name ?? 'Consumidor';
            $initials = collect(explode(' ', $clientName))->map(function($segment) { return strtoupper(substr($segment, 0, 1)); })->take(2)->join('');
            $colors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info'];
            $randomColor = $colors[$loop->index % count($colors)];
            @endphp
            <tr>
              <td class="ps-4">
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-3">
                    <span class="avatar-initial rounded-circle bg-label-{{ $randomColor }} fw-bold">{{ $initials }}</span>
                  </div>
                  <div class="d-flex flex-column">
                    <a href="{{ route('ordens-servico.edit', $os->id) }}" class="text-heading fw-bold mb-0">#{{ $os->id }} - {{ str($clientName)->limit(15) }}</a>
                    <small class="text-muted">{{ $os->veiculo->model ?? niche('entity') . ' not inf.' }}</small>
                  </div>
                </div>
              </td>
              <td>
                @php
                $statusConfig = [
                'pending' => ['color' => 'warning', 'label' => __('Pending'), 'icon' => 'tabler-clock'],
                'running' => ['color' => 'info', 'label' => __('Running'), 'icon' => 'tabler-tool'],
                'in_progress' => ['color' => 'info', 'label' => __('In Progress'), 'icon' => 'tabler-tool'],
                'finalized' => ['color' => 'success', 'label' => __('Finalized'), 'icon' => 'tabler-circle-check'],
                'paid' => ['color' => 'success', 'label' => __('Paid'), 'icon' => 'tabler-currency-dollar']
                ];
                $conf = $statusConfig[$os->status] ?? ['color' => 'secondary', 'label' => __($os->status), 'icon' => 'tabler-help'];
                @endphp
                <span class="badge bg-label-{{ $conf['color'] }} rounded-pill px-3">
                  {{ $conf['label'] }}
                </span>
              </td>
              <td class="text-end pe-4 text-heading fw-bold">
                R$ {{ number_format($os->total, 2, ',', '.') }}
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Top Services & Profitability -->
  <div class="col-xl-6 col-lg-12">
    <div class="row g-4 h-100">
      <div class="col-md-6 col-12">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-header py-3 bg-transparent border-bottom d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-bold">{{ __('Profitability') }}</h5>
          </div>
          <div class="card-body d-flex flex-column align-items-center justify-content-center py-2">
            <div id="profitabilityRadialChart"></div>
            <div class="text-center mt-n2">
              <h2 class="mb-0 {{ $monthlyProfitability >= 0 ? 'text-success' : 'text-danger' }} fw-bolder">
                {{ $monthlyProfitability >= 0 ? '+' : '' }}{{ number_format($monthlyProfitability, 1, ',', '.') }}%
              </h2>
              <span class="text-muted fw-medium">{{ __('Net Margin') }}</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-12">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-header py-3 bg-transparent border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold small">{{ __('Top 5 Services') }}</h5>
          </div>
          <div class="card-body p-2">
            <div id="topServicesChart"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Critical Alerts -->
  <div class="col-12">
    <div class="card h-100 shadow-sm border-0 bg-label-secondary bg-opacity-25">
      <div class="card-header bg-transparent border-0 py-3 pb-0">
        <h5 class="mb-0 text-danger fw-bold d-flex align-items-center">
          <i class="ti tabler-alert-triangle me-2"></i>{{ __('Attention Required') }}
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="alert alert-danger d-flex align-items-center border-0 shadow-sm mb-0 p-3 bg-white">
              <div class="avatar avatar-md bg-label-danger me-3 rounded">
                <span class="avatar-initial"><i class="ti tabler-box fs-3"></i></span>
              </div>
              <div class="d-flex w-100 justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1 fw-bold text-danger">{{ __('Low Stock') }}</h6>
                  <p class="mb-0 small text-muted">{{ $lowStockItems }} {{ __('products below minimum.') }}</p>
                </div>
                <a href="{{ route('inventory.items') }}" class="btn btn-danger btn-sm px-3">{{ __('Replenish') }}</a>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="alert alert-warning d-flex align-items-center border-0 shadow-sm mb-0 p-3 bg-white">
              <div class="avatar avatar-md bg-label-warning me-3 rounded">
                <span class="avatar-initial"><i class="ti tabler-file-invoice fs-3"></i></span>
              </div>
              <div class="d-flex w-100 justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1 fw-bold text-warning">{{ __('Pending Budgets') }}</h6>
                  <p class="mb-0 small text-muted">{{ $pendingBudgets }} {{ __('awaiting approval.') }}</p>
                </div>
                <a href="{{ route('budgets.pending') }}" class="btn btn-warning btn-sm px-3 text-white">{{ __('Verify') }}</a>
              </div>
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
          height: 320,
          type: 'line',
          stacked: false,
          toolbar: {
            show: false
          },
          parentHeightOffset: 0,
          fontFamily: 'inherit'
        },
        stroke: {
          width: [2, 2, 0],
          curve: 'smooth'
        },
        plotOptions: {
          bar: {
            columnWidth: '20%',
            borderRadius: 6,
            colors: {
              backgroundBarOpacity: 1,
            }
          }
        },
        colors: ['#28c76f', '#ea5455', '#7367f0'],
        fill: {
          opacity: [0.1, 0.1, 1],
          type: ['gradient', 'gradient', 'solid'],
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.4,
            opacityTo: 0.1,
            stops: [0, 90, 100]
          }
        },
        dataLabels: {
          enabled: false
        },
        xaxis: {
          categories: @json($months),
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false
          },
          labels: {
            style: {
              colors: '#aab3c3',
              fontSize: '12px'
            }
          }
        },
        yaxis: [{
            labels: {
              style: {
                colors: '#aab3c3',
                fontSize: '12px'
              },
              formatter: (val) => 'R$ ' + val.toLocaleString('pt-BR', {
                notation: "compact",
                compactDisplay: "short"
              })
            }
          },
          {
            opposite: true,
            show: false
          }
        ],
        grid: {
          borderColor: '#e7e7e7',
          strokeDashArray: 5,
          padding: {
            top: -20,
            bottom: 0,
            left: 10,
            right: 10
          }
        },
        tooltip: {
          shared: true,
          intersect: false,
          theme: 'light',
          y: {
            formatter: (val) => 'R$ ' + val.toLocaleString('pt-BR')
          }
        },
        legend: {
          position: 'top',
          horizontalAlign: 'left',
          fontFamily: 'inherit',
          labels: {
            colors: '#5d596c'
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
          height: 220,
          fontFamily: 'inherit'
        },
        labels: ['Pendentes', 'Execução', 'Finalizadas'],
        colors: ['#ff9f43', '#00cfe8', '#28c76f'],
        stroke: {
          width: 4,
          colors: ['#fff']
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
              size: '72%',
              labels: {
                show: true,
                value: {
                  fontSize: '1.5rem',
                  fontWeight: '600',
                  color: '#5d596c',
                  offsetY: -15,
                  formatter: (val) => val
                },
                name: {
                  offsetY: 20,
                  fontFamily: 'inherit'
                },
                total: {
                  show: true,
                  label: 'Total',
                  fontSize: '0.8rem',
                  color: '#aab3c3',
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
          height: 300,
          toolbar: {
            show: false
          },
          fontFamily: 'inherit'
        },
        plotOptions: {
          bar: {
            borderRadius: 6,
            horizontal: true,
            distributed: true,
            barHeight: '55%',
            dataLabels: {
              position: 'bottom'
            }
          }
        },
        colors: ['#7367f0', '#28c76f', '#00cfe8', '#ff9f43', '#ea5455'],
        dataLabels: {
          enabled: true,
          textAnchor: 'start',
          style: {
            colors: ['#5d596c'],
            fontSize: '11px',
            fontWeight: '600'
          },
          formatter: (val, opt) => opt.w.globals.labels[opt.dataPointIndex] + ": R$ " + val.toLocaleString('pt-BR'),
          offsetX: 0,
          dropShadow: {
            enabled: false
          }
        },
        xaxis: {
          categories: @json($topServiceLabels),
          labels: {
            show: false
          },
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false
          }
        },
        yaxis: {
          labels: {
            show: false
          }
        },
        grid: {
          show: false,
          padding: {
            top: 0,
            right: 0,
            bottom: 0,
            left: 0
          }
        },
        legend: {
          show: false
        },
        tooltip: {
          theme: 'light',
          y: {
            formatter: (val) => 'R$ ' + val.toLocaleString('pt-BR')
          }
        },
        states: {
          hover: {
            filter: {
              type: 'none'
            }
          },
          active: {
            filter: {
              type: 'none'
            }
          }
        }
      };
    if (topServicesEl) new ApexCharts(topServicesEl, topServicesConfig).render();

    // Profitability Radial Chart
    const profitabilityChartEl = document.querySelector('#profitabilityRadialChart'),
      profitabilityChartConfig = {
        series: [@json(abs(round($monthlyProfitability, 1)))],
        chart: {
          height: 240,
          type: 'radialBar',
          fontFamily: 'inherit'
        },
        plotOptions: {
          radialBar: {
            startAngle: -135,
            endAngle: 135,
            hollow: {
              margin: 10,
              size: '65%',
              image: undefined,
              imageOffsetX: 0,
              imageOffsetY: 0,
              position: 'front',
              dropShadow: {
                enabled: true,
                top: 3,
                left: 0,
                blur: 4,
                opacity: 0.1
              }
            },
            track: {
              background: '#f0f2f4',
              strokeWidth: '100%',
              margin: 0,
            },
            dataLabels: {
              show: true,
              name: {
                offsetY: 20,
                show: true,
                color: '#888ea8',
                fontSize: '13px'
              },
              value: {
                offsetY: -10,
                color: '#111',
                fontSize: '24px',
                fontWeight: 700,
                show: true,
                formatter: function(val) {
                  return val + "%";
                }
              }
            }
          },
        },
        fill: {
          type: 'gradient',
          gradient: {
            shade: 'dark',
            type: 'horizontal',
            shadeIntensity: 0.5,
            gradientToColors: ['{{ $monthlyProfitability >= 0 ? "#40CD88" : "#FF6B6B" }}'],
            inverseColors: true,
            opacityFrom: 1,
            opacityTo: 1,
            stops: [0, 100]
          }
        },
        colors: ['{{ $monthlyProfitability >= 0 ? "#28c76f" : "#ea5455" }}'],
        stroke: {
          lineCap: 'round',
          dashArray: 4
        },
        labels: ['Lucratividade'],
      };
    if (profitabilityChartEl) new ApexCharts(profitabilityChartEl, profitabilityChartConfig).render();
  });
</script>
@endpush
@endsection