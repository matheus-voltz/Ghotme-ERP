@extends('layouts/layoutMaster')

@section('title', 'Pacotes de Serviço')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('page-script')
@vite(['resources/js/service-packages.js'])
@endsection

@section('content')
<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-0">Pacotes de Serviço</h5>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-packages table border-top">
      <thead>
        <tr>
          <th>#</th>
          <th>Nome do Pacote</th>
          <th>Itens</th>
          <th>Preço Total (R$)</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
    </table>
  </div>

  <!-- Offcanvas Adicionar/Editar -->
  <div class="offcanvas offcanvas-end" style="width: 500px !important;" tabindex="-1" id="offcanvasPackage" aria-labelledby="offcanvasPackageLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasPackageLabel" class="offcanvas-title">Novo Pacote</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
      <form id="formPackage">
        @csrf
        <input type="hidden" name="id" id="package_id">
        
        <div class="mb-6">
          <label class="form-label">Nome do Pacote</label>
          <input type="text" class="form-control" name="name" id="package-name" placeholder="Ex: Revisão Básica" required />
        </div>

        <div class="mb-6">
          <label class="form-label">Serviços Inclusos (Mão de obra)</label>
          <select class="select2 form-select" name="services[]" id="package-services" multiple>
            @foreach($services as $service)
              <option value="{{ $service->id }}">{{ $service->name }} (R$ {{ $service->price }})</option>
            @endforeach
          </select>
        </div>

        <div class="mb-6">
          <label class="form-label">Peças Inclusas (Opcional)</label>
          <div id="parts-container">
            <select class="select2 form-select mb-2" id="add-part-select">
              <option value="">Adicionar Peça...</option>
              @foreach($parts as $part)
                <option value="{{ $part->id }}" data-name="{{ $part->name }}">
                  {{ $part->name }} (Estoque: {{ $part->quantity }})
                </option>
              @endforeach
            </select>
            <div id="selected-parts-list" class="mt-3">
                <!-- Dinâmico -->
            </div>
          </div>
        </div>

        <div class="mb-6">
          <label class="form-label">Preço Fixo do Pacote (R$)</label>
          <input type="number" step="0.01" class="form-control" name="total_price" id="package-total-price" placeholder="Deixe vazio para somar itens" />
          <small class="text-muted">Se vazio, o sistema somará o preço individual de cada item.</small>
        </div>

        <div class="mb-6">
          <label class="form-label">Descrição</label>
          <textarea class="form-control" name="description" id="package-description" rows="2"></textarea>
        </div>

        <button type="submit" class="btn btn-primary me-3">Salvar Pacote</button>
        <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>
      </form>
    </div>
  </div>
</div>
@endsection
