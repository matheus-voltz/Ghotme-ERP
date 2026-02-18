@extends('layouts/contentNavbarLayout')

@section('title', 'Central de Agendamentos Online')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Operacional /</span> Agendamentos do Site
        </h4>
        <div class="badge bg-label-primary">Novos agendamentos aparecem aqui</div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <!-- AGENDAMENTOS PENDENTES (OS MAIS IMPORTANTES) -->
        <div class="col-12 mb-5">
            <div class="card border-primary">
                <div class="card-header bg-label-primary d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Aguardando Confirmação ({{ $pending->count() }})</h5>
                    <i class="ti tabler-bell-ringing animate-shake"></i>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Data/Hora</th>
                                <th>Cliente / Veículo</th>
                                <th>Serviço Solicitado</th>
                                <th>Ações Rápidas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pending as $appt)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ date('d/m/Y', strtotime($appt->scheduled_at)) }}</span><br>
                                    <small class="text-primary">{{ date('H:i', strtotime($appt->scheduled_at)) }}</small>
                                </td>
                                <td>
                                    <strong>{{ $appt->customer_name }}</strong><br>
                                    <small>{{ $appt->customer_phone }} • {{ $appt->vehicle_plate }}</small>
                                </td>
                                <td>
                                    <span class="text-wrap d-inline-block" style="max-width: 300px;">{{ $appt->notes ?? 'Não informado' }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('appointments.confirm', $appt->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success px-4 rounded-pill">Confirmar</button>
                                        </form>
                                        <form action="{{ route('appointments.cancel', $appt->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger px-4 rounded-pill">Recusar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-5">Nenhum agendamento pendente. Tudo em dia! 🎉</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- HISTÓRICO RECENTE -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Confirmados Recentemente</h5>
                </div>
                <ul class="list-group list-group-flush">
                    @foreach($confirmed as $appt)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-0 fw-bold">{{ $appt->customer_name }}</p>
                            <small class="text-muted">{{ date('d/m \à\s H:i', strtotime($appt->scheduled_at)) }}</small>
                        </div>
                        <span class="badge bg-label-success rounded-pill">Confirmado</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Cancelados / Recusados</h5>
                </div>
                <ul class="list-group list-group-flush">
                    @foreach($cancelled as $appt)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-0 fw-bold">{{ $appt->customer_name }}</p>
                            <small class="text-muted">{{ date('d/m', strtotime($appt->scheduled_at)) }}</small>
                        </div>
                        <span class="badge bg-label-danger rounded-pill">Cancelado</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes shake {
  0% { transform: rotate(0deg); }
  25% { transform: rotate(10deg); }
  50% { transform: rotate(0deg); }
  75% { transform: rotate(-10deg); }
  100% { transform: rotate(0deg); }
}
.animate-shake {
  display: inline-block;
  animation: shake 0.5s infinite;
}
</style>
@endsection
