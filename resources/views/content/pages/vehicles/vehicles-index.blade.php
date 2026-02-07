@extends('layouts/layoutMaster')

@section('title', 'Veículos')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/@form-validation/form-validation.scss',
  'resources/assets/vendor/libs/animate-css/animate.scss', 
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/moment/moment.js',
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/select2/select2.js', 
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js', 
  'resources/assets/vendor/libs/cleave-zen/cleave-zen.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('page-script')
@vite(['resources/js/laravel-vehicles.js'])
@endsection

@section('content')
<div class="card">
  <div class="card-header border-bottom d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Gestão de Veículos</h5>
    <button class="btn btn-primary add-new" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddVehicles">
        <i class="ti tabler-plus me-1"></i> Adicionar Veículo
    </button>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-vehicles table border-top">
      <thead>
        <tr>
          <th></th>
          <th>ID</th>
          <th>Placa</th>
          <th>Marca</th>
          <th>Modelo</th>
          <th>Ano</th>
          <th>Ativo</th>
          <th>Ações</th>
        </tr>
      </thead>
    </table>
  </div>

  <!-- Offcanvas to add new vehicle -->
  <div class="offcanvas offcanvas-end" style="width: 500px !important;" tabindex="-1" id="offcanvasAddVehicles" aria-labelledby="offcanvasAddVehiclesLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddVehiclesLabel" class="offcanvas-title">Cadastrar Veículo</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
      <form class="add-new-vehicles pt-0" id="addNewVehicleForm">
        @csrf
        <input type="hidden" name="id" id="vehicle_id">
        
        <div class="mb-6">
          <label class="form-label">Cliente</label>
          <select id="vehicle-cliente" name="cliente_id" class="select2 form-select" required>
            <option value="">Selecione o Cliente</option>
            @foreach(\App\Models\Clients::orderBy('name')->get() as $client)
                <option value="{{ $client->id }}">{{ $client->name ?? $client->company_name }}</option>
            @endforeach
          </select>
        </div>

        <div class="mb-6">
          <label class="form-label">Placa</label>
          <input type="text" class="form-control" id="add-vehicle-placa" placeholder="ABC-1234" name="placa" required />
        </div>

        <div class="row">
            <div class="col-md-6 mb-6">
              <label class="form-label">Marca</label>
              <input type="text" name="marca" id="add-vehicle-marca" class="form-control" placeholder="Toyota" required />
            </div>
            <div class="col-md-6 mb-6">
              <label class="form-label">Modelo</label>
              <input type="text" name="modelo" id="add-vehicle-modelo" class="form-control" placeholder="Corolla" required />
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-6">
              <label class="form-label">Ano Fabricação</label>
              <input type="number" name="ano_fabricacao" id="add-vehicle-ano-fabricacao" class="form-control" placeholder="2022" />
            </div>
        </div>

        <div class="mb-6">
          <label class="form-label">Renavam</label>
          <input type="text" name="renavan" id="add-vehicle-renavam" class="form-control" placeholder="Opcional" />
        </div>

        <div class="mb-6">
          <label class="form-label">Ativo</label>
          <select id="vehicle-status" class="form-select" name="ativo">
            <option value="1">Sim</option>
            <option value="0">Não</option>
          </select>
        </div>

                <div class="mt-4">

                    <button type="submit" class="btn btn-primary me-3 data-submit">Salvar Veículo</button>

                    <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>

                </div>

              </form>

            </div>

          </div>

        </div>

        

        <!-- Modal Dossiê do Veículo -->

        <div class="modal fade" id="viewDossierModal" tabindex="-1" aria-hidden="true">

          <div class="modal-dialog modal-dialog-centered modal-lg">

            <div class="modal-content">

              <div class="modal-header">

                <h5 class="modal-title">Dossiê do Veículo</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

              </div>

              <div class="modal-body" id="dossierModalContent">

                <div class="text-center p-5"><div class="spinner-border text-primary"></div></div>

              </div>

            </div>

          </div>

        </div>

        @endsection

        