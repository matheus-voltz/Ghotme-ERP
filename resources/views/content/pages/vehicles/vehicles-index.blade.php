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
@vite(['resources/js/laravel-vehicles.js'])
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
    <table class="datatables-vehicles table border-top">
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
          <label class="form-label" for="add-vehicle-placa">Placa</label>
          <input type="text" class="form-control" id="add-vehicle-placa" placeholder="ABC-1234" name="placa"
            aria-label="Placa" />
        </div>
        <div class="mb-6 form-control-validation">
          <label class="form-label" for="add-vehicle-renavam">Renavam</label>
          <input type="text" id="add-vehicle-renavam" class="form-control" placeholder="12345678901"
            aria-label="Renavam" name="renavan" />
        </div>
        <div class="mb-6">
          <label class="form-label" for="add-vehicle-marca">Marca</label>
          <input type="text" id="add-vehicle-marca" class="form-control" placeholder="Toyota" aria-label="Marca"
            name="marca" />
        </div>
        <div class="mb-6">
          <label class="form-label" for="add-vehicle-modelo">Modelo</label>
          <input type="text" id="add-vehicle-modelo" class="form-control" placeholder="Corolla" aria-label="Modelo"
            name="modelo" />
        </div>
        <div class="mb-6">
          <label class="form-label" for="add-vehicle-ano-fabricacao">Ano Fabricação</label>
          <input type="text" id="add-vehicle-ano-fabricacao" class="form-control" placeholder="2022" aria-label="Ano Fabricação"
            name="ano_fabricacao" />
        </div>
        <div class="mb-6">
          <label class="form-label" for="vehicle-status">Ativo</label>
          <select id="vehicle-status" class="form-select" name="ativo">
            <option value="1">Sim</option>
            <option value="0">Não</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary me-3 data-submit">Enviar</button>
        <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>
      </form>
    </div>
  </div>
</div>

@endsection