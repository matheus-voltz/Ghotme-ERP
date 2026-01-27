@extends('layouts/layoutMaster')

@section('title', 'Clients')
@section('content')


@section('vendor-style')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss',
'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite(['resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleave-zen/cleave-zen.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
@vite(['resources/js/laravel-clients.js'])
@endsection
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddVehicles" aria-labelledby="offcanvasAddVehiclesLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasAddVehiclesLabel" class="offcanvas-title">Add Vehicle</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0">
    <form class="add-new-clients pt-0" id="addNewClientsForm">
      ...
      ...
    </form>
  </div>
</div>
<div class="card">
  <div class="card-header border-bottom">
  </div>
  <div class="card-datatable">
    <table class="datatables-clients table border-top">
      <thead>
        <tr>
          <th></th>
          <th>Id</th>
          <th>Placa</th>
          <th>Renavam</th>
          <th>Marca</th>
          <th>Modelo</th>
          <th>Ano Fabricacao</th>
          <th>Ativo</th>
          <th>Ações</th>
        </tr>
      </thead>
    </table>
  </div>
  <!-- Offcanvas to add new client -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddVehicles" aria-labelledby="offcanvasAddVehiclesLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddVehiclesLabel" class="offcanvas-title">Adicionar Veículo</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
      <form class="add-new-vehicles pt-0" id="addNewVehiclesForm">
        @csrf
        <input type="hidden" name="id" id="vehicle_id">
        <div class="mb-6 form-control-validation">
          <label class="form-label" for="add-vehicle-fullname">Placa</label>
          <input type="text" class="form-control" id="add-vehicle-fullname" placeholder="Luke Skywalker" name="name"
            aria-label="Luke Skywalker" />
        </div>
        <div class="mb-6 form-control-validation">
          <label class="form-label" for="add-vehicle-email">Renavam</label>
          <input type="text" id="add-vehicle-email" class="form-control" placeholder="john.doe@example.com"
            aria-label="john.doe@example.com" name="email" />
        </div>
        <div class="mb-6">
          <label class="form-label" for="add-vehicle-company">Marca</label>
          <input type="text" id="add-vehicle-company" class="form-control" placeholder="Web Developer" aria-label="jdoe1"
            name="company" value />
        </div>
        <div class="mb-6">
          <label class="form-label" for="add-vehicle-company">Modelo</label>
          <input type="text" id="add-vehicle-company" class="form-control" placeholder="Web Developer" aria-label="jdoe1"
            name="company" value />
        </div>
        <div class="mb-6">
          <label class="form-label" for="add-vehicle-company">Ano Fabricacao</label>
          <input type="text" id="add-vehicle-company" class="form-control" placeholder="Web Developer" aria-label="jdoe1"
            name="company" value />
        </div>
        <div class="mb-6">
          <label class="form-label" for="vehicle-role">Ativo</label>
          <select id="vehicle-role" class="form-select" name="role">
            <option value="subscriber">Inscrito</option>
            <option value="editor">Editor</option>
            <option value="maintainer">Mantenedor</option>
            <option value="author">Autor</option>
            <option value="admin">Administrador</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary me-3 data-submit">Enviar</button>
        <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>
      </form>
    </div>
  </div>
</div>

@endsection