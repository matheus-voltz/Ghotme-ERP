@extends('layouts/layoutMaster')

@section('title', 'Tempo Médio por Serviço')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card mb-6 bg-primary text-white">
            <div class="card-body text-center">
                <h5 class="card-title text-white">Média de Atendimento</h5>
                <h2 class="mb-2 text-white">{{ number_format($avgTime, 1) }} <small>horas</small></h2>
                <p class="mb-0 opacity-75">Tempo total entre a abertura e a finalização da OS</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card mb-6">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Eficiência nas Últimas OS</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>OS #</th>
                            <th>Veículo</th>
                            <th>Abertura</th>
                            <th>Conclusão</th>
                            <th>Duração</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($osList as $os)
                        @php
                            $duration = $os->created_at->diffInHours($os->updated_at);
                            $color = $duration > 48 ? 'danger' : ($duration > 24 ? 'warning' : 'success');
                        @endphp
                        <tr>
                            <td>#{{ $os->id }}</td>
                            <td>{{ $os->veiculo->placa ?? '-' }}</td>
                            <td><small>{{ $os->created_at->format('d/m H:i') }}</small></td>
                            <td><small>{{ $os->updated_at->format('d/m H:i') }}</small></td>
                            <td>
                                <span class="badge bg-label-{{ $color }}">
                                    {{ $duration }}h
                                </span>
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
