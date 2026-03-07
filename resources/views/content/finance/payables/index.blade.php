@extends('layouts/layoutMaster')

@section('title', __('Accounts payable'))

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/@form-validation/form-validation.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dt_table = document.querySelector('.datatables-payables');
    
    if (dt_table) {
        const dt = $(dt_table).DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('finance.data') }}", 
                data: function(d) {
                    d.type = 'out';
                }
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
                    targets: 0,
                    render: function(data) {
                        return `<span class="fw-medium">#${data}</span>`;
                    }
                },
                {
                    targets: 1,
                    render: function(data) {
                        return `<span class="text-nowrap">${data}</span>`;
                    }
                },
                {
                    targets: 3,
                    render: function(data) {
                        return `<span class="fw-bold text-danger">R$ ${parseFloat(data).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>`;
                    }
                },
                {
                    targets: 4,
                    render: function(data) {
                        var date = new Date(data);
                        return `<span class="text-nowrap"><i class="ti tabler-calendar me-1"></i>${date.toLocaleDateString('pt-BR')}</span>`;
                    }
                },
                {
                    targets: 5,
                    render: function(data) {
                        const statusObj = {
                            pending: { title: "{{ __('Pending') }}", class: 'bg-label-warning' },
                            paid: { title: "{{ __('Paid') }}", class: 'bg-label-success' },
                            overdue: { title: "{{ __('Overdue') }}", class: 'bg-label-danger' }
                        };
                        return `<span class="badge ${statusObj[data]?.class || 'bg-label-secondary'}">${statusObj[data]?.title || data}</span>`;
                    }
                },
                {
                    targets: -1,
                    title: "{{ __('Actions') }}",
                    orderable: false,
                    searchable: false,
                    render: function(data, type, full) {
                        let actions = `<div class="d-flex align-items-center">`;
                        if (full.status !== 'paid') {
                            actions += `<a href="javascript:;" class="text-body mark-as-paid" data-id="${data}" data-bs-toggle="tooltip" title="{{ __('Mark as Paid') }}"><i class="ti tabler-check-circle ti-sm me-2 text-success"></i></a>`;
                        }
                        actions += `<a href="javascript:;" class="text-body delete-record" data-id="${data}" data-bs-toggle="tooltip" title="{{ __('Delete') }}"><i class="ti tabler-trash ti-sm text-danger"></i></a>`;
                        actions += `</div>`;
                        return actions;
                    }
                }
            ],
            order: [[4, 'asc']],
            dom: '<"card-header d-flex flex-wrap pb-2"<"me-5 ms-n2"f><"dt-action-buttons v-stack align-items-start align-items-md-center justify-content-end flex-md-row flex-column gap-3 mb-3 mb-md-0"B>>t<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            language: {
                sLengthMenu: '_MENU_',
                search: '',
                searchPlaceholder: "{{ __('Search Bill') }}",
                paginate: {
                    next: '<i class="ti tabler-chevron-right ti-sm"></i>',
                    previous: '<i class="ti tabler-chevron-left ti-sm"></i>'
                },
                info: "{{ __('Showing') }} _START_ {{ __('to') }} _END_ {{ __('of') }} _TOTAL_ {{ __('entries') }}",
                infoEmpty: "{{ __('No entries found') }}",
                emptyTable: "{{ __('No data available in table') }}",
                zeroRecords: "{{ __('No matching records found') }}"
            },
            buttons: [
                {
                    text: '<i class="ti tabler-plus me-md-1"></i><span class="d-md-inline-block d-none">{{ __("Add Bill") }}</span>',
                    className: 'add-new btn btn-danger',
                    attr: {
                        'data-bs-toggle': 'offcanvas',
                        'data-bs-target': '#offcanvasAddBill'
                    }
                }
            ]
        });

        $(document).on('click', '.delete-record', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: "{{ __('Are you sure?') }}",
                text: "{{ __('This action cannot be undone!') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "{{ __('Yes, delete it!') }}",
                customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `${baseUrl}finance/transactions/${id}`,
                        type: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function() {
                            dt.draw();
                            Swal.fire({ icon: 'success', title: "{{ __('Deleted!') }}", customClass: { confirmButton: 'btn btn-success' } });
                        }
                    });
                }
            });
        });

        $(document).on('click', '.mark-as-paid', function() {
            var id = $(this).data('id');
            $.ajax({
                url: `${baseUrl}finance/transactions/${id}/pay`,
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function() {
                    dt.draw();
                    Swal.fire({ icon: 'success', title: "{{ __('Paid!') }}", customClass: { confirmButton: 'btn btn-success' } });
                }
            });
        });
    }

    $('.flatpickr').flatpickr({
        dateFormat: 'Y-m-d'
    });

    $('.select2').each(function() {
        var $this = $(this);
        $this.wrap('<div class="position-relative"></div>').select2({
            placeholder: "{{ __('Select') }}",
            dropdownParent: $this.parent()
        });
    });
});
</script>
@endsection

@section('content')
<!-- Accounts Payable Statistics -->
<div class="row g-6 mb-6">
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100 border-start border-warning border-3">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">{{ __('Total Pending') }}</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">R$ {{ number_format($stats['pending'], 2, ',', '.') }}</h4>
            </div>
            <p class="mb-0">{{ __('Awaiting payment') }}</p>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="ti tabler-clock ti-26px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100 border-start border-danger border-3">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">{{ __('Total Overdue') }}</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">R$ {{ number_format($stats['overdue'], 2, ',', '.') }}</h4>
            </div>
            <p class="mb-0 text-danger">{{ __('Requires attention') }}</p>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="ti tabler-alert-triangle ti-26px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100 border-start border-success border-3">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">{{ __('Paid Today') }}</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">R$ {{ number_format($stats['paid_today'], 2, ',', '.') }}</h4>
            </div>
            <p class="mb-0 text-success">{{ __('Transactions concluded') }}</p>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-success">
              <i class="ti tabler-check ti-26px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100 border-start border-primary border-3">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">{{ __('Monthly Total') }}</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">R$ {{ number_format($stats['monthly_total'], 2, ',', '.') }}</h4>
            </div>
            <p class="mb-0">{{ __('Expected for the month') }}</p>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="ti tabler-currency-dollar ti-26px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Accounts Payable Table -->
<div class="card">
  <div class="card-datatable table-responsive">
    <table class="datatables-payables table border-top">
      <thead>
        <tr>
          <th>#</th>
          <th>{{ __('Description') }}</th>
          <th>{{ __('Supplier') }}</th>
          <th>{{ __('Amount') }}</th>
          <th>{{ __('Due Date') }}</th>
          <th>{{ __('Status') }}</th>
          <th>{{ __('Actions') }}</th>
        </tr>
      </thead>
    </table>
  </div>

  <!-- Offcanvas to add new bill -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddBill" aria-labelledby="offcanvasAddBillLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddBillLabel" class="offcanvas-title">{{ __('Add New Bill') }}</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
      <form class="add-new-bill pt-0" id="formAddBill">
        @csrf
        <input type="hidden" name="type" value="out">
        <div class="mb-4">
          <label class="form-label" for="bill-description">{{ __('Description') }}</label>
          <input type="text" class="form-control" id="bill-description" placeholder="{{ __('Ex: Rent, Supplier, Water') }}" name="description" required />
        </div>
        <div class="mb-4">
          <label class="form-label" for="bill-amount">{{ __('Amount') }} (R$)</label>
          <input type="number" step="0.01" id="bill-amount" class="form-control" placeholder="0.00" name="amount" required />
        </div>
        <div class="mb-4">
          <label class="form-label" for="bill-due-date">{{ __('Due Date') }}</label>
          <input type="text" id="bill-due-date" class="form-control flatpickr" placeholder="YYYY-MM-DD" name="due_date" required />
        </div>
        <div class="mb-4">
          <label class="form-label" for="bill-supplier">{{ __('Supplier') }}</label>
          <select id="bill-supplier" name="supplier_id" class="select2 form-select">
            <option value="">{{ __('Select Supplier') }}</option>
            @foreach($suppliers as $supplier)
              <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-4">
          <label class="form-label" for="bill-payment-method">{{ __('Payment Method') }}</label>
          <select id="bill-payment-method" name="payment_method_id" class="select2 form-select" required>
            <option value="">{{ __('Select Method') }}</option>
            @foreach($paymentMethods as $method)
              <option value="{{ $method->id }}">{{ $method->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-4">
          <label class="form-label" for="bill-category">{{ __('Category') }}</label>
          <select id="bill-category" name="category" class="select2 form-select">
            <option value="fixed">{{ __('Fixed') }}</option>
            <option value="variable">{{ __('Variable') }}</option>
            <option value="employee">{{ __('Employee') }}</option>
            <option value="supplier">{{ __('Supplier') }}</option>
          </select>
        </div>
        <button type="submit" class="btn btn-danger me-sm-3 me-1 data-submit">{{ __('Save') }}</button>
        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">{{ __('Cancel') }}</button>
      </form>
    </div>
  </div>
</div>
@endsection
