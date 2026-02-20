@extends('layouts/layoutMaster')

@section('title', 'Fluxo de Caixa')

@section('content')
<div class="row">
  <div class="col-md-4">
    <div class="card bg-label-success mb-6">
      <div class="card-body text-center">
        <h5 class="card-title text-success">Total Recebido</h5>
        <h2 class="mb-0">R$ {{ number_format($incomes, 2, ',', '.') }}</h2>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card bg-label-danger mb-6">
      <div class="card-body text-center">
        <h5 class="card-title text-danger">Total Pago</h5>
        <h2 class="mb-0">R$ {{ number_format($expenses, 2, ',', '.') }}</h2>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card {{ $balance >= 0 ? 'bg-label-primary' : 'bg-label-warning' }} mb-6">
      <div class="card-body text-center">
        <h5 class="card-title {{ $balance >= 0 ? 'text-primary' : 'text-warning' }}">Saldo em Caixa</h5>
        <h2 class="mb-0">R$ {{ number_format($balance, 2, ',', '.') }}</h2>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-0">Últimas Movimentações (Confirmadas)</h5>
  </div>
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Data</th>
          <th>Descrição</th>
          <th>Origem/Destino</th>
          <th>Tipo</th>
          <th>Valor</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse($recentTransactions as $t)
          <tr>
            <td>{{ $t->paid_at->format('d/m/Y H:i') }}</td>
            <td>{{ $t->description }}</td>
            <td>
                {{ $t->type === 'in' 
                    ? ($t->client ? ($t->client->name ?? $t->client->company_name) : '-') 
                    : ($t->supplier ? $t->supplier->name : '-') }}
            </td>
            <td>
                <span class="badge bg-label-{{ $t->type === 'in' ? 'success' : 'danger' }}">
                    {{ $t->type === 'in' ? 'Entrada' : 'Saída' }}
                </span>
            </td>
            <td class="fw-bold {{ $t->type === 'in' ? 'text-success' : 'text-danger' }}">
                {{ $t->type === 'in' ? '+' : '-' }} R$ {{ number_format($t->amount, 2, ',', '.') }}
            </td>
            <td>
                <a href="{{ route('finance.transaction.pdf', $t->id) }}" target="_blank" class="btn btn-sm btn-icon btn-label-secondary" title="Ver PDF">
                    <i class="ti tabler-file-text"></i>
                </a>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center py-5">Nenhuma movimentação realizada ainda.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
