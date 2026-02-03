@extends('layouts/layoutMaster')

@section('title', 'Relatórios Financeiros')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js'])
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('/finance/reports/chart-data')
        .then(res => res.json())
        .then(data => {
            // Gráfico de Receitas vs Despesas
            const incomeExpenseEl = document.querySelector('#incomeExpenseChart');
            const incomeExpenseOptions = {
                chart: {
                    height: 350,
                    type: 'bar',
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    },
                },
                dataLabels: { enabled: false },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                series: [{
                    name: 'Receitas',
                    data: data.incomes
                }, {
                    name: 'Despesas',
                    data: data.expenses
                }],
                xaxis: {
                    categories: data.months,
                },
                fill: { opacity: 1 },
                colors: ['#28c76f', '#ea5455'],
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return "R$ " + val.toLocaleString('pt-BR', {minimumFractionDigits: 2})
                        }
                    }
                }
            };
            new ApexCharts(incomeExpenseEl, incomeExpenseOptions).render();

            // Gráfico de Categorias (Pizza)
            const categoryEl = document.querySelector('#categoryChart');
            const categoryOptions = {
                chart: {
                    height: 350,
                    type: 'donut',
                },
                labels: data.categories.map(c => c.category || 'Geral'),
                series: data.categories.map(c => parseFloat(c.total)),
                colors: ['#7367f0', '#00bad1', '#ff9f43', '#28c76f'],
                legend: {
                    position: 'bottom'
                },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: function (w) {
                                        return 'R$ ' + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString('pt-BR', {minimumFractionDigits: 2})
                                    }
                                }
                            }
                        }
                    }
                }
            };
            new ApexCharts(categoryEl, categoryOptions).render();
        });
});
</script>
@endsection

@section('content')
<div class="row">
    <!-- Cards de Resumo -->
    <div class="col-md-4">
        <div class="card mb-6">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-label-success p-2 me-3">
                        <i class="ti tabler-trending-up ti-md"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Receita (Mês)</h5>
                        <h4 class="mb-0">R$ {{ number_format($incomeMonth, 2, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-6">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-label-danger p-2 me-3">
                        <i class="ti tabler-trending-down ti-md"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Despesa (Mês)</h5>
                        <h4 class="mb-0">R$ {{ number_format($expenseMonth, 2, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-6 border-danger border">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-label-warning p-2 me-3">
                        <i class="ti tabler-alert-circle ti-md"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 text-danger">Inadimplência</h5>
                        <h4 class="mb-0">R$ {{ number_format($pendingReceivables, 2, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="col-md-8">
        <div class="card mb-6">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Receitas vs Despesas (6 Meses)</h5>
            </div>
            <div class="card-body pt-4">
                <div id="incomeExpenseChart"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-6">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Origem do Faturamento</h5>
            </div>
            <div class="card-body pt-4">
                <div id="categoryChart"></div>
            </div>
        </div>
    </div>
</div>
@endsection
