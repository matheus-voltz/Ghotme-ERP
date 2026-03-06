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
  <div class="col-md-8">
    <div class="card mb-4">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
          <i class="ti tabler-clipboard-list me-2"></i>
          Ficha de Produção: {{ $product->name }}
        </h5>
        <a href="{{ route('recipes.index') }}" class="btn btn-label-secondary btn-sm">
          <i class="ti tabler-arrow-left me-1"></i> Voltar
        </a>
      </div>
      <div class="card-body pt-4">
        <form action="{{ $product->recipe->count() > 0 ? route('recipes.update', $product->id) : route('recipes.store') }}" method="POST" id="recipeForm">
          @csrf
          @if($product->recipe->count() > 0)
            @method('PUT')
          @endif
          <input type="hidden" name="inventory_item_id" value="{{ $product->id }}">

          <div id="ingredients-container">
            @forelse($product->recipe as $index => $item)
            <div class="row mb-3 ingredient-row align-items-end">
              <div class="col-md-5">
                <label class="form-label">Ingrediente</label>
                <select name="ingredients[{{ $index }}][ingredient_id]" class="form-select select2" required>
                  <option value="">Selecione</option>
                  @foreach($ingredients as $ingredient)
                    <option value="{{ $ingredient->id }}" {{ $item->ingredient_id == $ingredient->id ? 'selected' : '' }}>
                      {{ $ingredient->name }} (Estoque: {{ $ingredient->quantity }} {{ $ingredient->unit }})
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Quantidade</label>
                <input type="number" name="ingredients[{{ $index }}][quantity]" class="form-control"
                       step="0.0001" min="0.0001" value="{{ $item->quantity }}" required>
              </div>
              <div class="col-md-2">
                <label class="form-label">Unidade</label>
                <select name="ingredients[{{ $index }}][unit]" class="form-select" required>
                  <option value="un" {{ $item->unit == 'un' ? 'selected' : '' }}>un</option>
                  <option value="g" {{ $item->unit == 'g' ? 'selected' : '' }}>g</option>
                  <option value="kg" {{ $item->unit == 'kg' ? 'selected' : '' }}>kg</option>
                  <option value="ml" {{ $item->unit == 'ml' ? 'selected' : '' }}>ml</option>
                  <option value="l" {{ $item->unit == 'l' ? 'selected' : '' }}>l</option>
                </select>
              </div>
              <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm remove-ingredient" title="Remover">
                  <i class="ti tabler-trash"></i>
                </button>
              </div>
            </div>
            @empty
            <div class="row mb-3 ingredient-row align-items-end">
              <div class="col-md-5">
                <label class="form-label">Ingrediente</label>
                <select name="ingredients[0][ingredient_id]" class="form-select" required>
                  <option value="">Selecione</option>
                  @foreach($ingredients as $ingredient)
                    <option value="{{ $ingredient->id }}">{{ $ingredient->name }} (Estoque: {{ $ingredient->quantity }} {{ $ingredient->unit }})</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Quantidade</label>
                <input type="number" name="ingredients[0][quantity]" class="form-control" step="0.0001" min="0.0001" required>
              </div>
              <div class="col-md-2">
                <label class="form-label">Unidade</label>
                <select name="ingredients[0][unit]" class="form-select" required>
                  <option value="un">un</option>
                  <option value="g">g</option>
                  <option value="kg">kg</option>
                  <option value="ml">ml</option>
                  <option value="l">l</option>
                </select>
              </div>
              <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm remove-ingredient" title="Remover">
                  <i class="ti tabler-trash"></i>
                </button>
              </div>
            </div>
            @endforelse
          </div>

          <div class="mb-4">
            <button type="button" class="btn btn-label-primary" id="addIngredient">
              <i class="ti tabler-plus me-1"></i> Adicionar Ingrediente
            </button>
          </div>

          <button type="submit" class="btn btn-primary">
            <i class="ti tabler-device-floppy me-1"></i> Salvar Ficha de Produção
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Resumo do Produto</h5>
      </div>
      <div class="card-body pt-4">
        <dl class="row mb-0">
          <dt class="col-sm-5">Produto:</dt>
          <dd class="col-sm-7">{{ $product->name }}</dd>

          <dt class="col-sm-5">Preço de Venda:</dt>
          <dd class="col-sm-7">R$ {{ number_format($product->selling_price, 2, ',', '.') }}</dd>

          <dt class="col-sm-5">Custo Unitário:</dt>
          <dd class="col-sm-7">R$ {{ number_format($product->cost_price, 2, ',', '.') }}</dd>

          <dt class="col-sm-5">Ingredientes:</dt>
          <dd class="col-sm-7">{{ $product->recipe->count() }}</dd>
        </dl>
      </div>
    </div>

    @if($product->recipe->count() > 0)
    <div class="card">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Custo da Receita</h5>
      </div>
      <div class="card-body pt-4">
        @php
          $totalCost = 0;
          foreach ($product->recipe as $item) {
              $ingredientCost = $item->ingredient->cost_price ?? 0;
              $totalCost += $ingredientCost * $item->quantity;
          }
          $margin = $product->selling_price > 0 ? (($product->selling_price - $totalCost) / $product->selling_price) * 100 : 0;
        @endphp
        <dl class="row mb-0">
          <dt class="col-sm-6">Custo Total:</dt>
          <dd class="col-sm-6">R$ {{ number_format($totalCost, 2, ',', '.') }}</dd>

          <dt class="col-sm-6">Preço de Venda:</dt>
          <dd class="col-sm-6">R$ {{ number_format($product->selling_price, 2, ',', '.') }}</dd>

          <dt class="col-sm-6">Margem:</dt>
          <dd class="col-sm-6">
            <span class="badge bg-label-{{ $margin >= 50 ? 'success' : ($margin >= 30 ? 'warning' : 'danger') }}">
              {{ number_format($margin, 1) }}%
            </span>
          </dd>
        </dl>
      </div>
    </div>
    @endif
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let index = {{ max($product->recipe->count(), 1) }};
    const ingredientsData = @json($ingredients->map(fn($i) => ['id' => $i->id, 'name' => $i->name, 'quantity' => $i->quantity, 'unit' => $i->unit]));

    document.getElementById('addIngredient').addEventListener('click', function() {
        let options = '<option value="">Selecione</option>';
        ingredientsData.forEach(i => {
            options += `<option value="${i.id}">${i.name} (Estoque: ${i.quantity} ${i.unit})</option>`;
        });

        const html = `
        <div class="row mb-3 ingredient-row align-items-end">
            <div class="col-md-5">
                <label class="form-label">Ingrediente</label>
                <select name="ingredients[${index}][ingredient_id]" class="form-select" required>${options}</select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Quantidade</label>
                <input type="number" name="ingredients[${index}][quantity]" class="form-control" step="0.0001" min="0.0001" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Unidade</label>
                <select name="ingredients[${index}][unit]" class="form-select" required>
                    <option value="un">un</option>
                    <option value="g">g</option>
                    <option value="kg">kg</option>
                    <option value="ml">ml</option>
                    <option value="l">l</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm remove-ingredient" title="Remover">
                    <i class="ti tabler-trash"></i>
                </button>
            </div>
        </div>`;

        document.getElementById('ingredients-container').insertAdjacentHTML('beforeend', html);
        index++;
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-ingredient')) {
            const rows = document.querySelectorAll('.ingredient-row');
            if (rows.length > 1) {
                e.target.closest('.ingredient-row').remove();
            }
        }
    });
});
</script>

@endsection
