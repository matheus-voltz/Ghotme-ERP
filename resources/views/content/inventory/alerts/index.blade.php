@extends('layouts/layoutMaster')

@section('title', 'Estoque Crítico / Alerta')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/animate-css/animate.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/moment/moment.js'
])
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dt_table = document.querySelector('.datatables-alerts');
    if (dt_table) {
        new DataTable(dt_table, {
            processing: true,
            serverSide: true,
            ajax: {
                url: baseUrl + 'inventory/critical-stock-list',
            },
            columns: [
                { data: 'fake_id' },
                { data: 'name' },
                { data: 'sku' },
                { data: 'quantity' },
                { data: 'min_quantity' },
                { data: 'supplier_name' },
                { data: 'status' },
                { data: 'id' } // For Actions
            ],
            columnDefs: [
                {
                    targets: 1, // Name
                    render: function(data, type, full) {
                        return `<span class="fw-medium text-heading">${data}</span>`;
                    }
                },
                {
                    targets: 3, // Qty
                    render: function(data, type, full) {
                        const color = data <= 0 ? 'danger' : 'warning';
                        return `<span class="badge bg-label-${color}">${data}</span>`;
                    }
                },
                {
                    targets: 6, // Status
                    render: function(data, type, full) {
                        const color = full.quantity <= 0 ? 'danger' : 'warning';
                        return `<span class="badge bg-${color}">${data}</span>`;
                    }
                },
                {
                    targets: 7, // Actions
                    title: 'Ações',
                    orderable: false,
                    render: function(data, type, full) {
                        return `
                            <a href="${baseUrl}inventory/stock-in" class="btn btn-sm btn-primary">
                                <i class="ti tabler-plus me-1"></i> Comprar
                            </a>
                        `;
                    }
                }
            ],
            order: [[3, 'asc']], // Ordenar pela menor quantidade
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
            }
        });
    }
});
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Itens com Estoque Baixo ou Crítico</h5>
                <span class="badge bg-label-danger">Atenção Necessária</span>
            </div>
            <div class="card-datatable table-responsive">
                <table class="datatables-alerts table border-top">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>SKU</th>
                            <th>Qtd. Atual</th>
                            <th>Mínimo</th>
                            <th>Fornecedor</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
