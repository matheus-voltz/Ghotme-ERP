@extends('layouts/layoutMaster')

@section('title', 'Orçamentos')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('content')
<div class="card">
  <div class="card-header border-bottom d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Orçamentos ({{ ucfirst($status ?? 'Todos') }})</h5>
    <a href="{{ route('budgets.create') }}" class="btn btn-primary">
        <i class="ti tabler-plus me-1"></i> Novo Orçamento
    </a>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-budgets table border-top">
      <thead>
        <tr>
          <th>ID</th>
          <th>Data</th>
          <th>Validade</th>
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
    const dt_table = document.querySelector('.datatables-budgets');
    if (dt_table) {
        const dt = new DataTable(dt_table, {
            ajax: {
                url: "{{ route('budgets.data') }}",
                data: { status: "{{ $status }}" }
            },
            columns: [
                { data: 'id' },
                { data: 'date' },
                { data: 'valid_until' },
                { data: 'client' },
                { data: 'vehicle' },
                { data: 'status' },
                { data: 'total' },
                { data: 'id' }
            ],
            columnDefs: [
                {
                    targets: 5,
                    render: (data) => {
                        const colors = { pending: 'warning', approved: 'success', rejected: 'danger', expired: 'secondary' };
                        const labels = { pending: 'Pendente', approved: 'Aprovado', rejected: 'Rejeitado', expired: 'Expirado' };
                        return `<span class="badge bg-label-${colors[data] || 'secondary'}">${labels[data] || data}</span>`;
                    }
                },
                {
                    targets: 6,
                    render: (data) => `R$ ${parseFloat(data).toFixed(2)}`
                },
                {
                    targets: 7,
                    render: (data, type, full) => {
                        let html = `<div class="d-flex gap-2">`;
                        if (full.status === 'pending') {
                            html += `<button class="btn btn-sm btn-success convert-os" data-id="${data}" title="Aprovar e Gerar OS"><i class="ti tabler-check"></i></button>`;
                            html += `<button class="btn btn-sm btn-danger reject-budget" data-id="${data}" title="Rejeitar"><i class="ti tabler-x"></i></button>`;
                        }
                        html += `<button class="btn btn-sm btn-info send-whatsapp" data-id="${data}" title="Enviar WhatsApp"><i class="ti tabler-brand-whatsapp"></i></button>`;
                        html += `</div>`;
                        return html;
                    }
                }
            ]
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.convert-os')) {
                const id = e.target.closest('.convert-os').dataset.id;
                fetch(`${baseUrl}budgets/${id}/convert`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Sucesso!', text: data.message });
                        dt.ajax.reload();
                    }
                });
            }

            if (e.target.closest('.send-whatsapp')) {
                const id = e.target.closest('.send-whatsapp').dataset.id;
                fetch(`${baseUrl}budgets/${id}/whatsapp`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.open(data.url, '_blank');
                        } else {
                            Swal.fire({ icon: 'error', title: 'Erro!', text: data.message });
                        }
                    });
            }
        });
    }
});
</script>
@endsection
