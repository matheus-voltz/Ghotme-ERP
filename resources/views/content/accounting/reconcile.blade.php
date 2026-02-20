@extends('layouts/layoutMaster')

@section('title', 'Conciliação Bancária')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Contabilidade /</span> Conciliação Bancária
</h4>

<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-0">Processar Extrato OFX</h5>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Data</th>
          <th>Descrição (Banco)</th>
          <th>Valor</th>
          <th>Tipo</th>
          <th>Correspondência Sugerida / Ação</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach($ofxTransactions as $index => $trx)
        <tr id="row-{{ $index }}" class="{{ $trx['status'] == 'conciliated' ? 'table-light opacity-50' : '' }}">
          <td>{{ \Carbon\Carbon::parse($trx['date'])->format('d/m/Y') }}</td>
          <td>
            <span class="d-block fw-bold">{{ $trx['memo'] }}</span>
            <small class="text-muted">ID: {{ $trx['bank_id'] }}</small>
          </td>
          <td class="fw-bold {{ $trx['type'] == 'in' ? 'text-success' : 'text-danger' }}">
            R$ {{ number_format($trx['amount'], 2, ',', '.') }}
          </td>
          <td>
            <span class="badge bg-label-{{ $trx['type'] == 'in' ? 'success' : 'danger' }}">
              {{ $trx['type'] == 'in' ? 'Recebimento' : 'Pagamento' }}
            </span>
          </td>
          <td>
            @if($trx['status'] == 'conciliated')
              <span class="badge bg-success"><i class="ti tabler-check me-1"></i> Já Conciliado</span>
            @elseif($trx['status'] == 'match_found')
              <div class="d-flex align-items-center gap-2">
                <div class="flex-grow-1">
                  <span class="badge bg-label-primary d-block mb-1">Encontrado no Sistema</span>
                  <small class="d-block">{{ $trx['match']['description'] }} ({{ $trx['match']['due_date'] }})</small>
                </div>
                <button class="btn btn-primary btn-sm btn-conciliate" 
                  data-index="{{ $index }}"
                  data-bank-id="{{ $trx['bank_id'] }}"
                  data-action="match"
                  data-id="{{ $trx['match']['id'] }}"
                  data-amount="{{ $trx['amount'] }}"
                  data-type="{{ $trx['type'] }}"
                  data-date="{{ $trx['date'] }}"
                  data-desc="{{ $trx['memo'] }}">
                  Confirmar Match
                </button>
              </div>
            @else
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-label-warning">Novo Lançamento</span>
                <button class="btn btn-outline-primary btn-sm btn-conciliate"
                  data-index="{{ $index }}"
                  data-bank-id="{{ $trx['bank_id'] }}"
                  data-action="create"
                  data-amount="{{ $trx['amount'] }}"
                  data-type="{{ $trx['type'] }}"
                  data-date="{{ $trx['date'] }}"
                  data-desc="{{ $trx['memo'] }}">
                  Lançar e Pagar
                </button>
              </div>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    <a href="{{ route('accounting.index') }}" class="btn btn-label-secondary">Voltar</a>
  </div>
</div>

@endsection

@section('page-script')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.btn-conciliate');
    
    buttons.forEach(btn => {
        btn.addEventListener('click', function() {
            const data = this.dataset;
            const row = document.getElementById(`row-${data.index}`);

            fetch('{{ route("accounting.conciliate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    bank_id: data.bankId,
                    action: data.action,
                    transaction_id: data.id,
                    amount: data.amount,
                    type: data.type,
                    date: data.date,
                    description: data.desc
                })
            })
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    row.classList.add('table-light', 'opacity-50');
                    btn.disabled = true;
                    btn.innerHTML = '<i class="ti tabler-check"></i> Conciliado';
                    btn.classList.remove('btn-primary', 'btn-outline-primary');
                    btn.classList.add('btn-success');
                }
            });
        });
    });
});
</script>
@endsection
