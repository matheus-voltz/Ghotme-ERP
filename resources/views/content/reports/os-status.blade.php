@extends('layouts/layoutMaster')

@section('title', get_current_niche() === 'food_service' ? 'Fluxo de Pedidos' : 'OS por Status')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js'])
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isFood = "{{ get_current_niche() === 'food_service' }}";
    
    fetch('/reports/os-status-data')
        .then(res => res.json())
        .then(data => {
            const statusLabels = {
                'pending': isFood ? 'Na Fila' : 'Pendente',
                'in_progress': isFood ? 'Em Preparo' : 'Executando',
                'completed': isFood ? 'Pronto (Entrega)' : 'Concluído',
                'paid': isFood ? 'Finalizado / Pago' : 'Pago',
                'finalized': isFood ? 'Finalizado' : 'Finalizado'
            };

            const options = {
                chart: {
                    type: 'donut',
                    height: 400
                },
                labels: data.map(d => statusLabels[d.status] || d.status),
                series: data.map(d => d.total),
                colors: ['#ff9f43', '#7367f0', '#28c76f', '#00cfe8', '#a1acb8'],
                legend: { position: 'bottom' },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: isFood ? 'Total de Pedidos' : 'Total de OS',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                    }
                                }
                            }
                        }
                    }
                }
            };
            new ApexCharts(document.querySelector('#osStatusChart'), options).render();
        });
});
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card mb-6">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">{{ get_current_niche() === 'food_service' ? 'Saúde da Cozinha / Fluxo' : 'Distribuição de Ordens de Serviço' }}</h5>
            </div>
            <div class="card-body pt-4">
                <div id="osStatusChart"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-6">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Resumo Numérico</h5>
            </div>
            <div class="card-body pt-4">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Quantidade</th>
                                <th>Percentual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                            $total = $stats->sum('total');
                            $statusLabels = [
                                'pending' => get_current_niche() === 'food_service' ? 'Na Fila' : 'Pendente',
                                'in_progress' => get_current_niche() === 'food_service' ? 'Em Preparo' : 'Executando',
                                'completed' => get_current_niche() === 'food_service' ? 'Pronto (Entrega)' : 'Concluído',
                                'paid' => get_current_niche() === 'food_service' ? 'Finalizado / Pago' : 'Pago',
                                'finalized' => 'Finalizado'
                            ];
                            @endphp
                            @foreach($stats as $stat)
                            <tr>
                                <td>
                                    <span class="badge bg-label-primary">{{ $statusLabels[$stat->status] ?? ucfirst($stat->status) }}</span>
                                </td>
                                <td>{{ $stat->total }}</td>
                                <td>{{ $total > 0 ? number_format(($stat->total / $total) * 100, 1) : 0 }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
