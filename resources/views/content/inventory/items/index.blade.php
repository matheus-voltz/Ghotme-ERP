@extends('layouts/layoutMaster')

@section('title', 'Itens de Estoque')

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
@vite(['resources/js/inventory-items.js'])
@endsection

@section('content')

<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-0">Itens de Estoque</h5>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-items table border-top">
      <thead>
        <tr>
          <th></th>
          <th>Id</th>
          <th>Nome</th>
          <th>SKU</th>
          <th>Qtd</th>
          <th>Preço Venda</th>
          <th>Localização</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
    </table>
  </div>
  
  <!-- Offcanvas to add new item -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddItems" aria-labelledby="offcanvasAddItemsLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddItemsLabel" class="offcanvas-title">Adicionar Item</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
      <form class="add-new-items pt-0" id="addNewItemsForm">
        @csrf
        <input type="hidden" name="id" id="item_id">
        
        <div class="mb-6 form-control-validation">
          <label class="form-label" for="add-item-name">Nome do Item</label>
          <input type="text" class="form-control" id="add-item-name" placeholder="Ex: Óleo Motor 5W30" name="name" aria-label="Nome do Item" />
        </div>
        
        <div class="mb-6 form-control-validation">
          <label class="form-label" for="add-item-sku">SKU / Código</label>
          <input type="text" id="add-item-sku" class="form-control" placeholder="Ex: OLEO-5W30" aria-label="SKU" name="sku" />
        </div>
        
        <div class="row mb-6">
            <div class="col-6 form-control-validation">
                <label class="form-label" for="add-item-cost">Custo (R$)</label>
                <input type="number" step="0.01" id="add-item-cost" class="form-control" placeholder="0.00" name="cost_price" />
            </div>
            <div class="col-6 form-control-validation">
                <label class="form-label" for="add-item-price">Venda (R$)</label>
                <input type="number" step="0.01" id="add-item-price" class="form-control" placeholder="0.00" name="selling_price" />
            </div>
        </div>

        <div class="row mb-6">
            <div class="col-6 form-control-validation">
                <label class="form-label" for="add-item-quantity">Quantidade</label>
                <input type="number" id="add-item-quantity" class="form-control" placeholder="0" name="quantity" />
            </div>
            <div class="col-6 form-control-validation">
                <label class="form-label" for="add-item-min-quantity">Estoque Mín.</label>
                <input type="number" id="add-item-min-quantity" class="form-control" placeholder="0" name="min_quantity" />
            </div>
        </div>
        
        <div class="mb-6 form-control-validation">
          <label class="form-label" for="add-item-unit">Unidade</label>
          <select id="add-item-unit" class="form-select" name="unit">
            <option value="un">Unidade (un)</option>
            <option value="kg">Quilo (kg)</option>
            <option value="l">Litro (l)</option>
            <option value="m">Metro (m)</option>
            <option value="cx">Caixa (cx)</option>
          </select>
        </div>

        <div class="mb-6">
          <label class="form-label" for="add-item-supplier">Fornecedor</label>
          <select id="add-item-supplier" class="select2 form-select" name="supplier_id">
            <option value="">Selecione</option>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
            @endforeach
          </select>
        </div>
        
        <div class="mb-6">
          <label class="form-label" for="add-item-location">Localização</label>
          <input type="text" id="add-item-location" class="form-control" placeholder="Ex: Prateleira A1" name="location" />
        </div>

        <div class="mb-6">
          <label class="form-label" for="add-item-description">Descrição</label>
          <textarea id="add-item-description" class="form-control" name="description" rows="3"></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary me-3 data-submit">Salvar</button>
        <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>
      </form>
    </div>
  </div>
</div>
@endsection
