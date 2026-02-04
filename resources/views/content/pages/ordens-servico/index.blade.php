@extends('layouts/layoutMaster')

@section('title', 'Ordens de Serviço')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('content')
<div class="card">
  <div class="card-header border-bottom d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Ordens de Serviço</h5>
    <a href="{{ route('ordens-servico.create') }}" class="btn btn-primary">
        <i class="ti tabler-plus me-1"></i> Nova OS
    </a>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-os table border-top">
      <thead>
        <tr>
          <th>ID</th>
          <th>Data</th>
          <th>Cliente</th>
          <th>Veículo</th>
          <th>Status</th>
          <th>Total (R$)</th>
          <th>Ações</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dt_table = document.querySelector('.datatables-os');
    if (dt_table) {
        const dt = new DataTable(dt_table, {
            ajax: "{{ route('ordens-servico.data') }}",
            columns: [
                { data: 'id' },
                { data: 'date' },
                { data: 'client' },
                { data: 'vehicle' },
                { data: 'status' },
                { data: 'total' },
                { data: 'id' }
            ],
            columnDefs: [
                {
                    targets: 4,
                    render: (data) => {
                        const colors = { pending: 'warning', finalized: 'success', running: 'info' };
                        return `<span class="badge bg-label-${colors[data] || 'secondary'}">${data}</span>`;
                    }
                },
                {
                    targets: 5,
                    render: (data) => `R$ ${parseFloat(data).toFixed(2)}`
                },
                {
                    targets: 6,
                    render: (data, type, full) => {
                        let html = `<div class="d-flex gap-2">`;
                        if (full.status !== 'finalized') {
                            html += `<button class="btn btn-sm btn-success finalize-os" data-id="${data}">Finalizar</button>`;
                        }
                        html += `<a href="${baseUrl}ordens-servico/checklist/create?os_id=${data}" class="btn btn-sm btn-info" title="Checklist"><i class="ti ti-clipboard-check"></i></a>`;
                        html += `</div>`;
                        return html;
                    }
                }
            ]
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.finalize-os')) {
                const id = e.target.closest('.finalize-os').dataset.id;
                fetch(`${baseUrl}ordens-servico/${id}/status`, {
                    method: 'POST',
                    body: JSON.stringify({ status: 'finalized' }),
                    headers: { 
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Content-Type': 'application/json'
                    }
                }).then(() => dt.ajax.reload());
            }
        });
    }
});
</script>
@endsection
