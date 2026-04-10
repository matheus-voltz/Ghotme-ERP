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
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
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
    <div class="d-flex gap-2">
        <button class="btn btn-label-dark" data-bs-toggle="modal" data-bs-target="#modalAppearance">
            <i class="ti tabler-palette me-1"></i> Aparência
        </button>
        <a href="{{ route('public.menu.show', auth()->user()->company?->slug ?? auth()->user()->company?->id ?? 'default') }}" target="_blank" class="btn btn-label-primary">
            <i class="ti tabler-external-link me-1"></i> Ver Cardápio Público
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddCategory">
            <i class="ti tabler-plus me-1"></i> Nova Categoria
        </button>
    </div>
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
                            <img src="{{ $item->mainImage ? asset('storage/'.$item->mainImage->path) : asset('assets/img/front-pages/misc/product-image.png') }}"
                                class="rounded"
                                style="width: 50px; height: 50px; object-fit: cover; {{ !$item->mainImage ? 'opacity: 0.5;' : '' }}"
                                onerror="this.src='{{ asset('assets/img/front-pages/misc/product-image.png') }}';">
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
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge bg-white text-dark small me-1">{{ strtoupper($cat->type) }}</span>
                            <div class="dropdown">
                                <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="ti tabler-dots-vertical text-muted"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item edit-category" href="javascript:void(0);"
                                        data-id="{{ $cat->id }}"
                                        data-name="{{ $cat->name }}"
                                        data-type="{{ $cat->type }}"
                                        data-icon="{{ $cat->icon }}">
                                        <i class="ti tabler-pencil me-1"></i> Editar
                                    </a>
                                    <form action="{{ route('menu.categories.destroy', $cat->id) }}" method="POST" class="d-inline delete-category-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="dropdown-item text-danger btn-delete-category">
                                            <i class="ti tabler-trash me-1"></i> Excluir
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-4 category-container" data-category-id="{{ $cat->id }}" data-type="{{ $cat->type }}">
                        @foreach($cat->items as $item)
                        <div class="card mb-3 menu-item-card border" draggable="true" data-id="{{ $item->id }}">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex gap-3">
                                        <img src="{{ $item->mainImage ? asset('storage/'.$item->mainImage->path) : asset('assets/img/front-pages/misc/product-image.png') }}"
                                            class="rounded"
                                            style="width: 40px; height: 40px; object-fit: cover; {{ !$item->mainImage ? 'opacity: 0.5;' : '' }}"
                                            onerror="this.src='{{ asset('assets/img/front-pages/misc/product-image.png') }}';">
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
                            <option value="beverage">Bebidas</option>
                            <option value="ingredient">Ingredientes / Adicionais (Monte o seu)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ícone (Tabler Icon)</label>
                        <input type="text" name="icon" class="form-control" placeholder="tabler-tools-kitchen-2">
                        <small class="text-muted">Use prefixo 'tabler-'. Ex: tabler-pizza, tabler-cup, tabler-tools-kitchen-2</small>
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

<!-- Modal Edit Category -->
<div class="modal fade" id="modalEditCategory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Categoria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditCategory" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome da Categoria</label>
                        <input type="text" name="name" id="edit-cat-name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de Itens</label>
                        <select name="type" id="edit-cat-type" class="form-select" required>
                            <option value="product">Lanches Prontos</option>
                            <option value="beverage">Bebidas</option>
                            <option value="ingredient">Ingredientes / Adicionais</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ícone (Tabler Icon)</label>
                        <input type="text" name="icon" id="edit-cat-icon" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Appearance -->
<div class="modal fade" id="modalAppearance" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Personalizar Cardápio Público</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('menu.categories.theme') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">A cor escolhida será o destaque principal (botões, detalhes e categorias) do seu cardápio público.</p>
                    <div class="mb-3">
                        <label class="form-label">Cor Principal do Cardápio</label>
                        <input type="color" name="primary_color" class="form-control form-control-color w-100" value="{{ optional(auth()->user()->company)->hasConfig('public_menu_theme', '#ff4757') ?? '#ff4757' }}" title="Escolha a cor principal">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Aparência</button>
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
                    body: JSON.stringify({
                        item_id: itemId,
                        category_id: null
                    })
                }).then(() => location.reload());
            });
        });
        // Category management logic
        document.querySelectorAll('.edit-category').forEach(btn => {
            btn.addEventListener('click', function() {
                const data = this.dataset;
                document.getElementById('edit-cat-name').value = data.name;
                document.getElementById('edit-cat-type').value = data.type;
                document.getElementById('edit-cat-icon').value = data.icon;

                const form = document.getElementById('formEditCategory');
                form.action = `/menu/categories/${data.id}`;

                new bootstrap.Modal(document.getElementById('modalEditCategory')).show();
            });
        });

        document.querySelectorAll('.btn-delete-category').forEach(btn => {
            btn.addEventListener('click', function() {
                const form = this.closest('form');
                Swal.fire({
                    title: 'Tem certeza?',
                    text: "Os itens desta categoria ficarão sem categoria no estoque!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#7367f0',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endsection