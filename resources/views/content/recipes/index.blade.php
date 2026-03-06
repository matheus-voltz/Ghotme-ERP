@extends('layouts/layoutMaster')

@section('title', 'Fichas de Produção')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('content')

<div class="card">
  <div class="card-header border-bottom d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Fichas de Produção</h5>
  </div>
  <div class="card-body pt-4">
    @if($products->isEmpty())
      <div class="text-center py-5">
        <i class="ti tabler-clipboard-list ti-lg mb-3 d-block text-muted"></i>
        <p class="text-muted">Nenhum produto cadastrado. Cadastre itens no inventário primeiro.</p>
        <a href="{{ route('inventory.items') }}" class="btn btn-primary">Ir para Inventário</a>
      </div>
    @else
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Produto</th>
              <th>Preço de Venda</th>
              <th>Ingredientes</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            @foreach($products as $product)
            <tr>
              <td>
                <div class="d-flex align-items-center">
                  <div>
                    <strong>{{ $product->name }}</strong>
                    @if($product->sku)
                      <br><small class="text-muted">SKU: {{ $product->sku }}</small>
                    @endif
                  </div>
                </div>
              </td>
              <td>R$ {{ number_format($product->selling_price, 2, ',', '.') }}</td>
              <td>
                @if($product->recipe_count > 0)
                  <span class="badge bg-label-success">{{ $product->recipe_count }} ingrediente(s)</span>
                @else
                  <span class="badge bg-label-warning">Sem ficha</span>
                @endif
              </td>
              <td>
                <a href="{{ route('recipes.show', $product->id) }}" class="btn btn-sm btn-primary">
                  <i class="ti tabler-edit me-1"></i>
                  {{ $product->recipe_count > 0 ? 'Editar Ficha' : 'Criar Ficha' }}
                </a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>

@endsection
