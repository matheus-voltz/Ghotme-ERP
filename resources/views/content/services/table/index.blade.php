@extends('layouts/layoutMaster')

@section('title', 'Tabela de Serviços')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/@form-validation/form-validation.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('page-script')
@vite(['resources/js/services-table.js'])
@endsection

@section('content')
<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-0">Tabela de Serviços (Mão de Obra)</h5>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-services table border-top">
      <thead>
        <tr>
          <th>#</th>
          <th>Serviço</th>
          <th>Preço (R$)</th>
          <th>Tempo Est.</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
    </table>
  </div>

  <!-- Offcanvas Adicionar/Editar -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasService" aria-labelledby="offcanvasServiceLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasServiceLabel" class="offcanvas-title">Adicionar Serviço</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
      <form class="pt-0" id="formService">
        @csrf
        <input type="hidden" name="id" id="service_id">
        <div class="mb-6">
          <label class="form-label" for="service-name">Nome do Serviço</label>
          <input type="text" class="form-control" id="service-name" name="name" placeholder="Ex: Alinhamento e Balanceamento" required />
        </div>
        <div class="mb-6">
          <label class="form-label" for="service-price">Preço da Mão de Obra (R$)</label>
          <input type="number" step="0.01" class="form-control" id="service-price" name="price" placeholder="0.00" required />
        </div>
        <div class="mb-6">
          <label class="form-label" for="service-time">Tempo Estimado (minutos)</label>
          <input type="number" class="form-control" id="service-time" name="estimated_time" placeholder="60" />
        </div>
        <div class="mb-6">
          <label class="form-label" for="service-description">Descrição</label>
          <textarea class="form-control" id="service-description" name="description" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary me-3">Salvar</button>
        <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>
      </form>
    </div>
  </div>
</div>
@endsection
