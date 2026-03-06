@extends('layouts/layoutMaster')

@section('title', 'Gestão de Cardápio - Food Service')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/animate-css/animate.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
<style>
    .menu-item-card {
        cursor: grab;
        transition: all 0.2s ease;
    }
    .menu-item-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .category-container {
        min-height: 150px;
        border: 2px dashed #dbdade;
        border-radius: 12px;
        padding: 15px;
        background: #f8f7fa;
    }
    .category-container.drag-over {
        border-color: #7367f0;
        background: #f0eeff;
    }
    .item-image {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
    }
</style>
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-3 mb-0">
        <span class="text-muted fw-light">Cardápio /</span> Montar e Desmontar
    </h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddCategory">
        <i class="ti tabler-plus me-1"></i> Nova Categoria
    </button>
</div>

<div class="row">
    <!-- Coluna de Itens Disponíveis (Estoque) -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">📦 Itens no Estoque</h5>
                <small class="text-muted">Arraste para as categorias</small>
            </div>
            <div class="card-body p-3" id="unassigned-items">
                @forelse($unassignedItems as $item)
                <div class="card mb-3 menu-item-card border shadow-none" draggable="true" data-id="{{ $item->id }}">
                    <div class="card-body p-3">
                        <div class="d-flex gap-3">
                            <img src="{{ $item->mainImage ? asset('storage/'.$item->mainImage->path) : asset('assets/img/elements/food-placeholder.png') }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                            <div>
                                <h6 class="mb-0">{{ $item->name }}</h6>
                                <small class="text-primary">R$ {{ number_format($item->selling_price, 2, ',', '.') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-center text-muted py-4">Nenhum item disponível sem categoria.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Coluna do Cardápio Estruturado -->
    <div class="col-md-8">
        <div class="row g-4">
            @foreach($categories as $cat)
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center bg-label-{{ $cat->type === 'ingredient' ? 'info' : ($cat->type === 'beverage' ? 'warning' : 'primary') }}">
                        <h5 class="card-title mb-0">
                            <i class="ti {{ $cat->icon ?? 'tabler-category' }} me-1"></i> {{ $cat->name }}
                        </h5>
                        <span class="badge bg-white text-dark small">{{ strtoupper($cat->type) }}</span>
                    </div>
                    <div class="card-body pt-4 category-container" data-category-id="{{ $cat->id }}" data-type="{{ $cat->type }}">
                        @foreach($cat->items as $item)
                        <div class="card mb-3 menu-item-card border" draggable="true" data-id="{{ $item->id }}">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex gap-3">
                                        <img src="{{ $item->mainImage ? asset('storage/'.$item->mainImage->path) : asset('assets/img/elements/food-placeholder.png') }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                        <div>
                                            <h6 class="mb-0">{{ $item->name }}</h6>
                                            <small class="text-muted">Venda: R$ {{ number_format($item->selling_price, 2, ',', '.') }}</small>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-label-danger unassign-btn" data-id="{{ $item->id }}">
                                        <i class="ti tabler-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal Add Category -->
<div class="modal fade" id="modalAddCategory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Categoria de Cardápio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('menu.categories.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome da Categoria</label>
                        <input type="text" name="name" class="form-control" placeholder="Ex: Hot Dogs Especiais" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de Itens</label>
                        <select name="type" class="form-select" required>
                            <option value="product">Lanches Prontos (Venda Unitária)</option>
                            <option value="ingredient">Ingredientes / Adicionais (Monte o seu)</option>
                            <option value="beverage">Bebidas</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ícone (Tabler Icon)</label>
                        <input type="text" name="icon" class="form-control" placeholder="ti-tools-kitchen-2">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Criar Categoria</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let draggedItem = null;

    // Drag and Drop Logic
    document.querySelectorAll('.menu-item-card').forEach(card => {
        card.addEventListener('dragstart', function() {
            draggedItem = this;
            setTimeout(() => this.style.display = 'none', 0);
        });

        card.addEventListener('dragend', function() {
            setTimeout(() => this.style.display = 'block', 0);
            draggedItem = null;
        });
    });

    document.querySelectorAll('.category-container, #unassigned-items').forEach(container => {
        container.addEventListener('dragover', e => e.preventDefault());
        
        container.addEventListener('dragenter', function() {
            this.classList.add('drag-over');
        });

        container.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });

        container.addEventListener('drop', function() {
            this.classList.remove('drag-over');
            const itemId = draggedItem.dataset.id;
            const categoryId = this.dataset.categoryId || null;
            const isIngredient = this.dataset.type === 'ingredient';

            // Ajax to update database
            fetch('{{ route("menu.items.assign") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    item_id: itemId,
                    category_id: categoryId,
                    is_ingredient: isIngredient
                })
            }).then(() => {
                location.reload(); // Simplificado para a demo, o ideal seria mover o DOM
            });
        });
    });

    // Unassign button logic
    document.querySelectorAll('.unassign-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.id;
            fetch('{{ route("menu.items.assign") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ item_id: itemId, category_id: null })
            }).then(() => location.reload());
        });
    });
});
</script>
@endsection
