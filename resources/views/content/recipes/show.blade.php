@extends('layouts/layoutMaster')

@section('title', 'Ficha de Produção - ' . $product->name)

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('content')

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
          <i class="ti tabler-clipboard-list me-2"></i>
          Ficha Técnica de Preparo: <span class="text-primary">{{ $product->name }}</span>
        </h5>
        <a href="{{ route('recipes.index') }}" class="btn btn-label-secondary btn-sm">
          <i class="ti tabler-arrow-left me-1"></i> Voltar para Lista
        </a>
      </div>
      <div class="card-body pt-4">
        <div class="row g-4">
            <div class="col-md-4 border-end">
                <h6 class="fw-bold mb-3 text-uppercase small text-muted">Resumo do Produto Final</h6>
                <div class="bg-light p-3 rounded mb-3">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Preço Venda:</dt>
                        <dd class="col-sm-6 fw-bold text-success">R$ {{ number_format($product->selling_price, 2, ',', '.') }}</dd>

                        <dt class="col-sm-6">Custo Atual:</dt>
                        <dd class="col-sm-6">R$ {{ number_format($product->cost_price, 2, ',', '.') }}</dd>

                        <dt class="col-sm-6">Unidade:</dt>
                        <dd class="col-sm-6">{{ $product->unit }}</dd>
                    </dl>
                </div>
                <div class="alert alert-info small py-2">
                    <i class="ti tabler-info-circle me-1"></i>
                    Os ingredientes adicionados aqui serão descontados do estoque automaticamente a cada venda deste produto.
                </div>
            </div>
            <div class="col-md-8">
                @livewire('product-recipe-manager', ['productId' => $product->id])
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
