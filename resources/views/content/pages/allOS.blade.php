@extends('layouts/layoutMaster')

@section('title', 'Todas as ordens de serviço') 
@section('page-script')
@vite(['resources/assets/js/tables-datatables-basic.js'])
@endsection
@section('vendor-style')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('content')

<div class="card">
  <div class="card-datatable pt-0">
  <table class="datatables-basic table table-bordered">
    <thead>
      <tr>
        <th></th>
        <th></th>
        <th>id</th>
        <th>Name</th>
        <th>Email</th>
        <th>Date</th>
        <th>Salary</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
  </table>
</div>
</div>
@endsection