@extends('layouts/layoutMaster')

@section('title', 'Clientes')

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
@vite(['resources/js/laravel-clients.js'])
@endsection

@section('content')
<div class="card">
  <div class="card-header border-bottom d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Gestão de Clientes</h5>
    <button class="btn btn-primary add-new" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddClients">
        <i class="ti tabler-plus me-1"></i> Adicionar Cliente
    </button>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-clients table border-top">
      <thead>
        <tr>
          <th></th>
          <th>ID</th>
          <th>Tipo</th>
          <th>Nome</th>
          <th>Documento</th>
          <th>Email</th>
          <th>Veículos</th>
          <th>Ações</th>
        </tr>
      </thead>
    </table>
  </div>

  <!-- Offcanvas to add new client -->
  <div class="offcanvas offcanvas-end" style="width: 550px !important;" tabindex="-1" id="offcanvasAddClients" aria-labelledby="offcanvasAddClientsLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddClientsLabel" class="offcanvas-title">Cadastrar Cliente</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
      <form class="add-new-clients pt-0" id="addNewClientsForm">
        @csrf
        <input type="hidden" name="id" id="client_id">
        
        <div class="mb-6">
          <label class="form-label">Tipo de Pessoa</label>
          <div class="d-flex gap-4 mt-2">
            <div class="form-check">
              <input name="type" class="form-check-input client-type-toggle" type="radio" value="PF" id="typePF" checked>
              <label class="form-check-label" for="typePF">Pessoa Física</label>
            </div>
            <div class="form-check">
              <input name="type" class="form-check-input client-type-toggle" type="radio" value="PJ" id="typePJ">
              <label class="form-check-label" for="typePJ">Pessoa Jurídica</label>
            </div>
          </div>
        </div>

        <!-- Campos Pessoa Física -->
        <div id="sectionPF">
            <div class="mb-6">
              <label class="form-label">Nome Completo</label>
              <input type="text" class="form-control" name="name" placeholder="Ex: João Silva" />
            </div>
            <div class="mb-6">
              <label class="form-label">CPF</label>
              <input type="text" class="form-control cpf-mask" name="cpf" placeholder="000.000.000-00" />
            </div>
        </div>

        <!-- Campos Pessoa Jurídica -->
        <div id="sectionPJ" class="d-none">
            <div class="mb-6">
              <label class="form-label">Razão Social</label>
              <input type="text" class="form-control" name="company_name" placeholder="Ex: Oficina Mecânica LTDA" />
            </div>
            <div class="mb-6">
              <label class="form-label">CNPJ</label>
              <input type="text" class="form-control cnpj-mask" name="cnpj" placeholder="00.000.000/0000-00" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-6">
              <label class="form-label text-primary">WhatsApp / Celular</label>
              <input type="text" class="form-control mobile-mask" name="whatsapp" placeholder="(00) 00000-0000" />
            </div>
            <div class="col-md-6 mb-6">
              <label class="form-label">E-mail</label>
              <input type="email" class="form-control" name="email" placeholder="cliente@email.com" />
            </div>
        </div>

        <hr class="my-4">
        <h6 class="mb-4">Endereço (Opcional)</h6>
        <div class="row">
            <div class="col-md-4 mb-4">
                <label class="form-label">CEP</label>
                <input type="text" name="cep" class="form-control cep-mask" placeholder="00000-000" />
            </div>
            <div class="col-md-8 mb-4">
                <label class="form-label">Rua</label>
                <input type="text" name="rua" class="form-control" />
            </div>
            <div class="col-md-3 mb-4">
                <label class="form-label">Nº</label>
                <input type="text" name="numero" class="form-control" />
            </div>
            <div class="col-md-9 mb-4">
                <label class="form-label">Bairro</label>
                <input type="text" name="bairro" class="form-control" />
            </div>
            <div class="col-md-8 mb-4">
                <label class="form-label">Cidade</label>
                <input type="text" name="cidade" class="form-control" />
            </div>
            <div class="col-md-4 mb-4">
                <label class="form-label">Estado</label>
                <input type="text" name="estado" class="form-control" maxlength="2" />
            </div>
        </div>

        <hr class="my-4">
        <h6 class="mb-4"><i class="ti tabler-car me-1"></i> Dados do Veículo (Opcional)</h6>
        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="form-label text-primary">Placa</label>
                <input type="text" name="veiculo_placa" class="form-control" placeholder="ABC-1234" />
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label">Marca</label>
                <input type="text" name="veiculo_marca" class="form-control" placeholder="Ex: Toyota" />
            </div>
            <div class="col-md-12 mb-4">
                <label class="form-label">Modelo</label>
                <input type="text" name="veiculo_modelo" class="form-control" placeholder="Ex: Corolla" />
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary me-3 data-submit">Salvar Cliente</button>
            <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
