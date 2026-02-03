@extends('layouts/layoutMaster')

@section('title', 'Contas a Receber')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js'
])
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dt_table = document.querySelector('.datatables-receivables');
    if (dt_table) {
        const dt = new DataTable(dt_table, {
            processing: true,
            serverSide: true,
            ajax: {
                url: baseUrl + 'finance/data',
                data: { type: 'in' }
            },
            columns: [
                { data: 'fake_id' },
                { data: 'description' },
                { data: 'entity_name' },
                { data: 'amount' },
                { data: 'due_date' },
                { data: 'status' },
                { data: 'id' }
            ],
            columnDefs: [
                {
                    targets: 3,
                    render: (data) => `R$ ${parseFloat(data).toFixed(2)}`
                },
                {
                    targets: 5,
                    render: (data) => {
                        const colors = { pending: 'warning', paid: 'success', cancelled: 'danger' };
                        const labels = { pending: 'Pendente', paid: 'Recebido', cancelled: 'Cancelado' };
                        return `<span class="badge bg-label-${colors[data]}">${labels[data]}</span>`;
                    }
                },
                {
                    targets: 6,
                    title: 'Ações',
                    render: (data, type, full) => {
                        let html = '<div class="d-flex gap-2">';
                        if (full.status === 'pending') {
                            html += `<button class="btn btn-sm btn-success mark-paid" data-id="${data}"><i class="ti tabler-check"></i></button>`;
                        }
                        html += `<button class="btn btn-sm btn-danger delete-record" data-id="${data}"><i class="ti tabler-trash"></i></button></div>`;
                        return html;
                    }
                }
            ]
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.mark-paid')) {
                const id = e.target.closest('.mark-paid').dataset.id;
                fetch(`${baseUrl}finance/transactions/${id}/pay`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                }).then(() => dt.draw());
            }
            if (e.target.closest('.delete-record')) {
                const id = e.target.closest('.delete-record').dataset.id;
                fetch(`${baseUrl}finance/transactions/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                }).then(() => dt.draw());
            }
        });
    }

    $('.flatpickr').flatpickr();
    $('.select2').select2({ dropdownParent: $('#offcanvasTransaction').parent() });

    document.getElementById('formTransaction').onsubmit = function(e) {
        e.preventDefault();
        const formData = new URLSearchParams(new FormData(this));
        fetch("{{ route('finance.transactions.store') }}", {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        }).then(res => res.json()).then(data => {
            if (data.success) {
                bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasTransaction')).hide();
                dt_table && new DataTable(dt_table).draw();
            }
        });
    };
});
</script>
@endsection

@section('content')
<div class="card">
  <div class="card-header border-bottom d-flex justify-content-between">
    <h5 class="card-title mb-0">Contas a Receber</h5>
    <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasTransaction">
        <i class="ti tabler-plus me-1"></i> Novo Recebimento
    </button>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-receivables table border-top">
      <thead>
        <tr>
          <th>#</th>
          <th>Descrição</th>
          <th>Cliente</th>
          <th>Valor</th>
          <th>Vencimento</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
    </table>
  </div>

  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasTransaction">
    <div class="offcanvas-header border-bottom">
      <h5>Novo Lançamento de Receita</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body mx-0 p-6 h-100">
      <form id="formTransaction">
        @csrf
        <input type="hidden" name="type" value="in">
        <div class="mb-4">
          <label class="form-label">Descrição</label>
          <input type="text" name="description" class="form-control" required />
        </div>
        <div class="mb-4">
          <label class="form-label">Valor (R$)</label>
          <input type="number" step="0.01" name="amount" class="form-control" required />
        </div>
        <div class="mb-4">
          <label class="form-label">Data de Vencimento</label>
          <input type="text" name="due_date" class="form-control flatpickr" required />
        </div>
        <div class="mb-4">
          <label class="form-label">Cliente</label>
          <select name="client_id" class="select2 form-select">
            <option value="">Selecione...</option>
            @foreach($clients as $client)
              <option value="{{ $client->id }}">{{ $client->name ?? $client->company_name }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-4">
          <label class="form-label">Forma de Pagamento</label>
          <select name="payment_method_id" class="select2 form-select">
            <option value="">Selecione...</option>
            @foreach($paymentMethods as $method)
              <option value="{{ $method->id }}">{{ $method->name }}</option>
            @endforeach
          </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">Salvar</button>
      </form>
    </div>
  </div>
</div>
@endsection
