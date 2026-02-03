@extends('layouts/layoutMaster')

@section('title', 'Fornecedores')

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
@vite(['resources/js/inventory-suppliers.js'])
@endsection

@section('content')

<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-0">Fornecedores</h5>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-suppliers table border-top">
      <thead>
        <tr>
          <th></th>
          <th>Id</th>
          <th>Nome</th>
          <th>Contato</th>
          <th>Email</th>
          <th>Telefone</th>
          <th>Cidade</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
    </table>
  </div>
  
  <!-- Offcanvas to add new supplier -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddSuppliers" aria-labelledby="offcanvasAddSuppliersLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddSuppliersLabel" class="offcanvas-title">Adicionar Fornecedor</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
      <form class="add-new-suppliers pt-0" id="addNewSuppliersForm">
        @csrf
        <input type="hidden" name="id" id="supplier_id">
        
        <div class="mb-6 form-control-validation">
          <label class="form-label" for="add-supplier-name">Razão Social / Nome</label>
          <input type="text" class="form-control" id="add-supplier-name" placeholder="Ex: Fornecedor ABC Ltda" name="name" aria-label="Razão Social" />
        </div>
        
        <div class="mb-6">
          <label class="form-label" for="add-supplier-trade-name">Nome Fantasia</label>
          <input type="text" id="add-supplier-trade-name" class="form-control" placeholder="Ex: ABC Peças" name="trade_name" />
        </div>
        
        <div class="mb-6">
          <label class="form-label" for="add-supplier-contact-name">Pessoa de Contato</label>
          <input type="text" id="add-supplier-contact-name" class="form-control" placeholder="Ex: João da Silva" name="contact_name" />
        </div>

        <div class="mb-6 form-control-validation">
          <label class="form-label" for="add-supplier-email">Email</label>
          <input type="email" id="add-supplier-email" class="form-control" placeholder="email@exemplo.com" name="email" />
        </div>

        <div class="row mb-6">
            <div class="col-6">
                <label class="form-label" for="add-supplier-phone">Telefone</label>
                <input type="text" id="add-supplier-phone" class="form-control phone-mask" placeholder="(00) 0000-0000" name="phone" />
            </div>
            <div class="col-6">
                <label class="form-label" for="add-supplier-document">CNPJ / CPF</label>
                <input type="text" id="add-supplier-document" class="form-control" placeholder="Documento" name="document" />
            </div>
        </div>
        
        <div class="mb-6">
          <label class="form-label" for="add-supplier-city">Cidade</label>
          <input type="text" id="add-supplier-city" class="form-control" placeholder="Ex: São Paulo" name="city" />
        </div>
        
        <div class="mb-6">
          <label class="form-label" for="add-supplier-address">Endereço</label>
          <textarea id="add-supplier-address" class="form-control" name="address" rows="2"></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary me-3 data-submit">Salvar</button>
        <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>
      </form>
    </div>
  </div>
</div>
@endsection
