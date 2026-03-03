@extends('layouts/layoutMaster')

@section('title', 'Detalhes do Caixa #' . $register->id)

@section('content')

<div class="row">
  <div class="col-md-8">
    <div class="card mb-4">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Caixa #{{ $register->id }} - {{ $register->opened_at->format('d/m/Y') }}</h5>
        <a href="{{ route('cash-register.index') }}" class="btn btn-label-secondary btn-sm">
          <i class="ti tabler-arrow-left me-1"></i> Voltar
        </a>
      </div>
      <div class="card-body pt-4">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr><th>Hora</th><th>Tipo</th><th>Valor</th><th>Pagamento</th><th>Descrição</th><th>Usuário</th></tr>
            </thead>
            <tbody>
              @foreach($register->movements as $mov)
              <tr>
                <td>{{ $mov->created_at->format('H:i') }}</td>
                <td>
                  <span class="badge bg-label-{{ $mov->type === 'sale' ? 'success' : ($mov->type === 'sangria' ? 'danger' : 'info') }}">
                    {{ ucfirst($mov->type) }}
                  </span>
                </td>
                <td>R$ {{ number_format($mov->amount, 2, ',', '.') }}</td>
                <td>{{ $mov->payment_method ?? '-' }}</td>
                <td>{{ $mov->description ?? '-' }}</td>
                <td>{{ $mov->user->name ?? '-' }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Resumo</h5>
      </div>
      <div class="card-body pt-4">
        <dl class="row mb-0">
          <dt class="col-sm-6">Operador:</dt>
          <dd class="col-sm-6">{{ $register->user->name ?? '-' }}</dd>

          <dt class="col-sm-6">Abertura:</dt>
          <dd class="col-sm-6">{{ $register->opened_at->format('d/m H:i') }}</dd>

          <dt class="col-sm-6">Fechamento:</dt>
          <dd class="col-sm-6">{{ $register->closed_at?->format('d/m H:i') ?? 'Aberto' }}</dd>

          <dt class="col-sm-6">Saldo Inicial:</dt>
          <dd class="col-sm-6">R$ {{ number_format($register->opening_balance, 2, ',', '.') }}</dd>

          <dt class="col-sm-6">Esperado:</dt>
          <dd class="col-sm-6">R$ {{ number_format($register->expected_balance, 2, ',', '.') }}</dd>

          <dt class="col-sm-6">Contado:</dt>
          <dd class="col-sm-6">R$ {{ number_format($register->actual_balance, 2, ',', '.') }}</dd>

          <dt class="col-sm-6">Diferença:</dt>
          <dd class="col-sm-6">
            @php $diff = $register->difference ?? 0; @endphp
            <span class="fw-bold text-{{ $diff >= 0 ? 'success' : 'danger' }}">
              R$ {{ number_format($diff, 2, ',', '.') }}
            </span>
          </dd>
        </dl>

        @if($register->notes)
          <hr>
          <p class="mb-0"><strong>Observações:</strong><br>{{ $register->notes }}</p>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection
