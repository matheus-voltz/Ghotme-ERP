@extends('layouts/layoutMaster')

@section('title', 'Checklist Personalizado')

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
    const dt_table = document.querySelector('.datatables-checklist');
    if (dt_table) {
        const dt = new DataTable(dt_table, {
            ajax: baseUrl + 'settings/custom-checklist-list',
            columns: [
                { data: 'category' },
                { data: 'name' },
                { data: 'order' },
                { data: 'id' }
            ],
            columnDefs: [
                {
                    targets: 3,
                    render: (data) => `<button class="btn btn-sm btn-icon delete-record" data-id="${data}"><i class="ti tabler-trash"></i></button>`
                }
            ],
            order: [[0, 'asc'], [2, 'asc']]
        });

        document.getElementById('formChecklist').onsubmit = function(e) {
            e.preventDefault();
            fetch("{{ route('settings.custom-checklist.store') }}", {
                method: 'POST',
                body: new URLSearchParams(new FormData(this)),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            }).then(() => {
                this.reset();
                dt.ajax.reload();
            });
        };

        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-record')) {
                const id = e.target.closest('.delete-record').dataset.id;
                fetch(`${baseUrl}settings/custom-checklist/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                }).then(() => dt.ajax.reload());
            }
        });
    }
});
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card mb-6">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Adicionar Item de Inspeção</h5>
            </div>
            <div class="card-body pt-6">
                <form id="formChecklist">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Categoria</label>
                        <select name="category" class="form-select" required>
                            <option value="Motor">Motor</option>
                            <option value="Suspensão">Suspensão</option>
                            <option value="Freios">Freios</option>
                            <option value="Elétrica">Elétrica</option>
                            <option value="Estética">Estética / Exterior</option>
                            <option value="Geral">Geral</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Nome do Item</label>
                        <input type="text" name="name" class="form-control" placeholder="Ex: Nível do óleo" required />
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Ordem de exibição</label>
                        <input type="number" name="order" class="form-control" value="0" />
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Adicionar ao Checklist</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Itens Atuais do Checklist</h5>
            </div>
            <div class="card-datatable table-responsive">
                <table class="datatables-checklist table border-top">
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th>Item de Verificação</th>
                            <th>Ordem</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
