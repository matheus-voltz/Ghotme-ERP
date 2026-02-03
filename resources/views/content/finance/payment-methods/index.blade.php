@extends('layouts/layoutMaster')

@section('title', 'Formas de Pagamento')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dt_table = document.querySelector('.datatables-methods');
    if (dt_table) {
        const dt = new DataTable(dt_table, {
            ajax: baseUrl + 'finance/payment-methods-list',
            columns: [
                { data: 'id' },
                { data: 'name' },
                { data: 'type' },
                { data: 'is_active' },
                { data: 'id' }
            ],
            columnDefs: [
                {
                    targets: 3,
                    render: (data) => data ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>'
                },
                {
                    targets: 4,
                    render: (data) => `<button class="btn btn-sm btn-icon delete-record" data-id="${data}"><i class="ti tabler-trash"></i></button>`
                }
            ]
        });

        document.addEventListener('submit', function(e) {
            if (e.target.id === 'formMethod') {
                e.preventDefault();
                fetch("{{ route('finance.payment-methods.store') }}", {
                    method: 'POST',
                    body: new URLSearchParams(new FormData(e.target)),
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                }).then(() => {
                    bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasMethod')).hide();
                    dt.ajax.reload();
                });
            }
        });
    }
});
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header border-bottom d-flex justify-content-between">
                <h5 class="card-title mb-0">Formas de Pagamento</h5>
                <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMethod">Adicionar</button>
            </div>
            <div class="card-datatable table-responsive">
                <table class="datatables-methods table border-top">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasMethod">
    <div class="offcanvas-header border-bottom">
        <h5>Nova Forma de Pagamento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body mx-0 p-6 h-100">
        <form id="formMethod">
            @csrf
            <div class="mb-4">
                <label class="form-label">Nome (Ex: PIX, Visa Crédito)</label>
                <input type="text" name="name" class="form-control" required />
            </div>
            <div class="mb-4">
                <label class="form-label">Tipo</label>
                <select name="type" class="form-select">
                    <option value="cash">Dinheiro</option>
                    <option value="pix">PIX</option>
                    <option value="credit_card">Cartão de Crédito</option>
                    <option value="debit_card">Cartão de Débito</option>
                    <option value="transfer">Transferência</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Salvar</button>
        </form>
    </div>
</div>
@endsection
