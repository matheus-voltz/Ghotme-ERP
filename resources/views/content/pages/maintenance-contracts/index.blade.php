@extends('layouts/layoutMaster')

@section('title', 'Contratos de Manutenção')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Clientes /</span> Contratos de Manutenção
</h4>

<div class="row">
  <!-- Formulário de Criação -->
  <div class="col-md-4">
    <div class="card mb-4">
      <h5 class="card-header">Novo Contrato de Recorrência</h5>
      <div class="card-body">
        <form action="{{ route('maintenance-contracts.store') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label">Cliente</label>
            <select name="client_id" class="select2 form-select" required>
              <option value="">Selecione...</option>
              @foreach($clients as $client)
              <option value="{{ $client->id }}">{{ $client->name ?? $client->company_name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Título do Contrato</label>
            <input type="text" name="title" class="form-control" placeholder="Ex: Manutenção Mensal Ar Condicionado" required />
          </div>
          <div class="mb-3">
            <label class="form-label">Valor Mensal (R$)</label>
            <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required />
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Dia do Vencimento</label>
              <input type="number" name="billing_day" class="form-control" min="1" max="31" value="1" required />
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Frequência</label>
              <select name="frequency" class="form-select">
                <option value="monthly">Mensal</option>
                <option value="quarterly">Trimestral</option>
                <option value="yearly">Anual</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Data de Início</label>
            <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required />
          </div>
          <div class="mb-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="auto_generate_os" id="auto_generate_os" value="1" checked>
              <label class="form-check-label" for="auto_generate_os">Gerar OS automática a cada mês</label>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100">Ativar Contrato</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Lista de Contratos -->
  <div class="col-md-8">
    <div class="card">
      <h5 class="card-header">Contratos Ativos</h5>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>Título / Valor</th>
              <th>Próximo Venc.</th>
              <th>Status</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse($contracts as $contract)
            <tr>
              <td>
                <span class="fw-bold">{{ $contract->client->name ?? $contract->client->company_name }}</span>
              </td>
              <td>
                <span class="d-block text-truncate" style="max-width: 200px">{{ $contract->title }}</span>
                <small class="text-success fw-bold">R$ {{ number_format($contract->amount, 2, ',', '.') }}</small>
              </td>
              <td>{{ $contract->next_billing_date->format('d/m/Y') }}</td>
              <td>
                <span class="badge bg-label-{{ $contract->status == 'active' ? 'success' : 'warning' }}">
                  {{ $contract->status == 'active' ? 'Ativo' : 'Pausado' }}
                </span>
              </td>
              <td>
                <form action="{{ route('maintenance-contracts.destroy', $contract->id) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-icon text-danger"><i class="ti tabler-trash"></i></button>
                </form>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center p-4 text-muted">Nenhum contrato recorrente cadastrado.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('.select2').select2();
});
</script>
@endsection
