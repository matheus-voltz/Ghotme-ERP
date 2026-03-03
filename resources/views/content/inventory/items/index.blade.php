@extends('layouts/layoutMaster')

@section('title', __('Inventory Items'))

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
<script>
  window.inventoryTranslations = {
    'Select': '{{ __("Select") }}',
    'Active': '{{ __("Active") }}',
    'Inactive': '{{ __("Inactive") }}',
    'Actions': '{{ __("Actions") }}',
    'Edit': '{{ __("Edit") }}',
    'Publish on Mercado Livre': '{{ __("Publish on Mercado Livre") }}',
    'Delete': '{{ __("Delete") }}',
    'Search Item': '{{ __("Search Item") }}',
    'Add Item': '{{ __("Add Item") }}',
    'Showing _START_ to _END_ of _TOTAL_ entries': '{{ __("Showing _START_ to _END_ of _TOTAL_ entries") }}',
    'Details of': '{{ __("Details of") }}',
    'Are you sure?': '{{ __("Are you sure?") }}',
    'You won\'t be able to revert this!': '{{ __("You won\'t be able to revert this!") }}',
    'Yes, delete it!': '{{ __("Yes, delete it!") }}',
    'Deleted!': '{{ __("Deleted!") }}',
    'The item has been deleted!': '{{ __("The item has been deleted!") }}',
    'Edit Item': '{{ __("Edit Item") }}',
    'Item Created!': '{{ __("Item Created!") }}',
    'Do you want to generate the QR Code label for this item now?': '{{ __("Do you want to generate the QR Code label for this item now?") }}',
    'Yes, Generate Label': '{{ __("Yes, Generate Label") }}',
    'Not Now': '{{ __("Not Now") }}',
    'Updated!': '{{ __("Updated!") }}',
    'Success!': '{{ __("Success!") }}',
    'View Ad': '{{ __("View Ad") }}',
    'Close': '{{ __("Close") }}',
    'Error!': '{{ __("Error!") }}',
    'Understood': '{{ __("Understood") }}',
    'Publishing...': '{{ __("Publishing...") }}',
    'Publish Now': '{{ __("Publish Now") }}',
    'Please fill in the item name': '{{ __("Please fill in the item name") }}',
    'Please fill in the cost': '{{ __("Please fill in the cost") }}',
    'Please fill in the selling price': '{{ __("Please fill in the selling price") }}',
    'Please fill in the quantity': '{{ __("Please fill in the quantity") }}',
    'Please fill in the minimum stock': '{{ __("Please fill in the minimum stock") }}',
    'Please fill in the unit': '{{ __("Please fill in the unit") }}'
  };
</script>
@endsection

@section('content')

<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-0">{{ __('Inventory Items') }}</h5>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-items table border-top">
      <thead>
        <tr>
          <th></th>
          <th>Id</th>
          <th>{{ __('Item Name') }}</th>
          <th>{{ __('SKU') }}</th>
          <th>{{ __('Quantity') }}</th>
          <th>{{ __('Selling Price') }}</th>
          <th>{{ __('Location') }}</th>
          <th>{{ __('Status') }}</th>
          <th>{{ __('Actions') }}</th>
        </tr>
      </thead>
    </table>
  </div>

  <!-- Offcanvas to add new item -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddItems" aria-labelledby="offcanvasAddItemsLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddItemsLabel" class="offcanvas-title">{{ __('Add Item') }}</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
      <form class="add-new-items pt-0" id="addNewItemsForm">
        @csrf
        <input type="hidden" name="id" id="item_id">

        <div class="mb-6 form-control-validation">
          <label class="form-label" for="add-item-name">{{ __('Item Name') }}</label>
          <input type="text" class="form-control" id="add-item-name" placeholder="Ex: Óleo Motor 5W30" name="name" aria-label="{{ __('Item Name') }}" />
        </div>

        <div class="mb-6 form-control-validation">
          <label class="form-label" for="add-item-sku">{{ __('SKU') }}</label>
          <input type="text" id="add-item-sku" class="form-control" placeholder="Ex: OLEO-5W30" aria-label="SKU" name="sku" />
        </div>

        <div class="row mb-6">
          <div class="col-6 form-control-validation">
            <label class="form-label" for="add-item-cost">{{ __('Cost') }} (R$)</label>
            <input type="number" step="0.01" id="add-item-cost" class="form-control" placeholder="0.00" name="cost_price" />
          </div>
          <div class="col-6 form-control-validation">
            <label class="form-label" for="add-item-price">{{ __('Selling Price') }} (R$)</label>
            <input type="number" step="0.01" id="add-item-price" class="form-control" placeholder="0.00" name="selling_price" />
          </div>
        </div>

        <div class="row mb-6">
          <div class="col-6 form-control-validation">
            <label class="form-label" for="add-item-quantity">{{ __('Quantity') }}</label>
            <input type="number" id="add-item-quantity" class="form-control" placeholder="0" name="quantity" />
          </div>
          <div class="col-6 form-control-validation">
            <label class="form-label" for="add-item-min-quantity">{{ __('Min Stock') }}</label>
            <input type="number" id="add-item-min-quantity" class="form-control" placeholder="0" name="min_quantity" />
          </div>
        </div>

        <div class="mb-6 form-control-validation">
          <label class="form-label" for="add-item-unit">{{ __('Unit') }}</label>
          <select id="add-item-unit" class="form-select" name="unit">
            <option value="un">{{ __('Unit (un)') }}</option>
            <option value="kg">{{ __('Kilo (kg)') }}</option>
            <option value="l">{{ __('Liter (l)') }}</option>
            <option value="m">{{ __('Meter (m)') }}</option>
            <option value="cx">{{ __('Box (cx)') }}</option>
          </select>
        </div>

        <div class="mb-6">
          <label class="form-label" for="add-item-supplier">{{ __('Supplier') }}</label>
          <select id="add-item-supplier" class="select2 form-select" name="supplier_id">
            <option value="">{{ __('Select') }}</option>
            @foreach($suppliers as $supplier)
            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="mb-6">
          <label class="form-label" for="add-item-location">{{ __('Location') }}</label>
          <input type="text" id="add-item-location" class="form-control" placeholder="Ex: Prateleira A1" name="location" />
        </div>

        <div class="mb-6">
          <label class="form-label" for="add-item-description">{{ __('Description') }}</label>
          <textarea id="add-item-description" class="form-control" name="description" rows="3"></textarea>
        </div>

        @if(get_current_niche() === 'food_service')
        <div id="recipe-section" class="d-none mt-4 border-top pt-4">
          <h6 class="mb-3 text-primary"><i class="ti tabler-recipe me-1"></i> {{ __('Recipe Card (Technique Sheet)') }}</h6>
          <div id="livewire-recipe-container">
            <!-- O componente será injetado aqui via JS ao editar -->
            <p class="text-muted small">{{ __('Save the item first to enable ingredient configuration.') }}</p>
          </div>
        </div>
        @endif

        <button type="submit" class="btn btn-primary me-3 data-submit">{{ __('Save') }}</button>
        <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">{{ __('Cancel') }}</button>
      </form>
    </div>
  </div>

  <!-- Modal to publish on Mercado Livre -->
  <div class="modal fade" id="modalPublishMeli" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('Publish on Mercado Livre') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
        </div>
        <form id="formPublishMeli">
          @csrf
          <input type="hidden" name="id" id="publish_item_id">
          <input type="hidden" name="type" value="product">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">{{ __('Item Name') }}</label>
              <input type="text" id="publish_item_name" class="form-control" readonly>
            </div>
            <div class="mb-3">
              <label class="form-label">{{ __('Price on Mercado Livre') }} (R$)</label>
              <input type="number" step="0.01" name="price" id="publish_item_price" class="form-control" required>
              <small class="text-muted">{{ __('Suggestion: Current selling price.') }}</small>
            </div>
            <div class="mb-3">
              <label class="form-label">{{ __('Category ID (ML)') }}</label>
              <input type="text" name="category_id" class="form-control" placeholder="Ex: MLB1234" required>
              <small><a href="https://developers.mercadolibre.com.br/pt_br/categorias-e-atributos" target="_blank">{{ __('How to find the category?') }}</a></small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="btn btn-warning">{{ __('Publish Now') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection