@extends('layouts/layoutMaster')

@section('title', 'Planos de Assinatura (Recorrência)')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Serviços /</span> Planos de Assinatura
</h4>

<div class="card">
  <div class="card-header border-bottom d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Planos de Recorrência</h5>
    <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddPlan">
      <i class="ti tabler-plus me-1"></i> Criar Novo Plano
    </button>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-plans table border-top">
      <thead>
        <tr>
          <th>Nome do Plano</th>
          <th>Preço</th>
          <th>Frequência</th>
          <th>Serviços Inclusos</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Offcanvas to add new plan -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddPlan" style="width: 500px !important;">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title">Configurar Plano de Assinatura</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
    <form id="formAddPlan">
      @csrf
      <div class="mb-4">
        <label class="form-label">Nome do Plano</label>
        <input type="text" class="form-control" name="name" placeholder="Ex: Plano Bronze Mensal" required />
      </div>
      
      <div class="row mb-4">
        <div class="col-6">
          <label class="form-label">Preço da Assinatura (R$)</label>
          <input type="number" step="0.01" class="form-control" name="price" placeholder="0.00" required />
        </div>
        <div class="col-6">
          <label class="form-label">Frequência</label>
          <select name="interval" class="form-select">
            <option value="month">Mensal</option>
            <option value="week">Semanal</option>
            <option value="year">Anual</option>
          </select>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label">Descrição (Opcional)</label>
        <textarea class="form-control" name="description" rows="2"></textarea>
      </div>

      <hr class="my-4">
      <h6 class="mb-3">O que está incluso neste plano?</h6>
      
      <div id="services-list">
        <div class="service-row row g-2 mb-2 align-items-end">
          <div class="col-8">
            <label class="form-label small">Serviço</label>
            <select class="form-select select2-services" name="services[0][id]" required>
              <option value="">Selecione...</option>
              @foreach($services as $service)
                <option value="{{ $service->id }}">{{ $service->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-3">
            <label class="form-label small">Qtd/Mês</label>
            <input type="number" class="form-control" name="services[0][quantity]" value="1" min="1" required />
          </div>
          <div class="col-1">
            <button type="button" class="btn btn-label-danger btn-icon btn-sm mb-1 remove-service"><i class="ti tabler-trash"></i></button>
          </div>
        </div>
      </div>

      <button type="button" id="add-service-btn" class="btn btn-sm btn-outline-primary mt-2">
        <i class="ti tabler-plus me-1"></i> Adicionar mais um serviço
      </button>

      <div class="mt-6">
        <button type="submit" class="btn btn-primary me-sm-3 me-1">Salvar Plano</button>
        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let serviceIndex = 1;

    // Initialize Select2 for the first row
    $('.select2-services').select2({
        dropdownParent: $('#offcanvasAddPlan')
    });

    // Add Service Row
    document.getElementById('add-service-btn').addEventListener('click', function() {
        const row = `
            <div class="service-row row g-2 mb-2 align-items-end animate__animated animate__fadeIn">
                <div class="col-8">
                    <select class="form-select select2-services" name="services[${serviceIndex}][id]" required>
                        <option value="">Selecione...</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-3">
                    <input type="number" class="form-control" name="services[${serviceIndex}][quantity]" value="1" min="1" required />
                </div>
                <div class="col-1">
                    <button type="button" class="btn btn-label-danger btn-icon btn-sm mb-1 remove-service"><i class="ti tabler-trash"></i></button>
                </div>
            </div>
        `;
        document.getElementById('services-list').insertAdjacentHTML('beforeend', row);
        
        // Initialize Select2 for the new row
        $(`select[name="services[${serviceIndex}][id]"]`).select2({
            dropdownParent: $('#offcanvasAddPlan')
        });
        
        serviceIndex++;
    });

    // Remove Service Row
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-service')) {
            const rows = document.querySelectorAll('.service-row');
            if (rows.length > 1) {
                e.target.closest('.service-row').remove();
            } else {
                Swal.fire({ icon: 'warning', title: 'Atenção', text: 'O plano deve ter pelo menos um serviço incluso.' });
            }
        }
    });

    // Datatable
    const dt_plans = $('.datatables-plans').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("services.plans.list") }}',
        columns: [
            { data: 'name' },
            { data: 'price' },
            { data: 'interval' },
            { data: 'items_count' },
            { data: 'is_active' },
            { data: 'action' }
        ],
        columnDefs: [
            {
                targets: 3,
                render: function(data) {
                    return `<span class="badge bg-label-info">${data} itens inclusos</span>`;
                }
            },
            {
                targets: 4,
                render: function(data) {
                    return data ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>';
                }
            },
            {
                targets: -1,
                render: function(data, type, full) {
                    return `
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm btn-icon delete-plan" data-id="${full.id}"><i class="ti tabler-trash"></i></button>
                        </div>
                    `;
                }
            }
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
        }
    });

    // Submit Form
    const form = document.getElementById('formAddPlan');
    form.onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        
        fetch('{{ route("services.plans.store") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Sucesso!', text: data.message });
                dt_plans.ajax.reload();
                bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasAddPlan')).hide();
                form.reset();
            } else {
                Swal.fire({ icon: 'error', title: 'Erro', text: data.message });
            }
        });
    };

    // Delete Plan
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-plan')) {
            const id = e.target.closest('.delete-plan').dataset.id;
            Swal.fire({
                title: 'Tem certeza?',
                text: "Isso removerá o plano permanentemente!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, remover!',
                customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
                buttonsStyling: false
            }).then(result => {
                if (result.isConfirmed) {
                    fetch(`${baseUrl}services/plans/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        dt_plans.ajax.reload();
                        Swal.fire({ icon: 'success', title: 'Removido!', text: data.message });
                    });
                }
            });
        }
    });
});
</script>
@endsection
