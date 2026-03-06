@extends('layouts/layoutMaster')

@section('title', get_current_niche() === 'food_service' ? 'Lançar Novo Pedido' : 'Nova ' . niche('entity'))

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/animate-css/animate.scss'
])
<style>
    /* POS Design Rules */
    .pos-product-card {
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid #dbdade;
    }
    .pos-product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        border-color: #7367f0;
    }
    .pos-cart-container {
        position: sticky;
        top: 20px;
        max-height: calc(100vh - 100px);
        display: flex;
        flex-direction: column;
    }
    .cart-items-list {
        flex: 1;
        overflow-y: auto;
    }
    .category-nav {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding-bottom: 10px;
        scrollbar-width: none;
    }
    .category-nav::-webkit-scrollbar { display: none; }
    
    .category-btn {
        white-space: nowrap;
        border-radius: 50px;
    }
</style>
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('content')

@if(get_current_niche() === 'food_service')
{{-- INTERFACE PDV FOOD SERVICE --}}
<div class="row g-4 animate__animated animate__fadeIn">
    <!-- Coluna de Produtos (Cardápio) -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-body">
                <div class="input-group input-group-merge shadow-none border-bottom mb-4">
                    <span class="input-group-text"><i class="ti tabler-search"></i></span>
                    <input type="text" id="posSearch" class="form-control form-control-lg border-0" placeholder="Pesquisar lanche, bebida ou adicional...">
                </div>

                <div class="category-nav mb-4">
                    <button class="btn btn-primary category-btn" data-category="all">Todos</button>
                    @foreach($categories as $cat)
                        <button class="btn btn-label-secondary category-btn" data-category="{{ $cat->id }}">
                            <i class="ti {{ $cat->icon ?? 'tabler-category' }} me-1"></i> {{ $cat->name }}
                        </button>
                    @endforeach
                </div>

                <div class="row g-3" id="posGrid">
                    @foreach($categories as $cat)
                        @foreach($cat->items as $item)
                        <div class="col-md-4 col-6 pos-item" data-category="{{ $cat->id }}" data-search="{{ strtolower($item->name) }}">
                            <div class="card pos-product-card h-100 shadow-none" onclick="addToCart('{{ $item->id }}', '{{ $item->name }}', {{ $item->selling_price }})">
                                <div class="card-body p-3 text-center">
                                    <img src="{{ $item->mainImage ? asset('storage/'.$item->mainImage->path) : asset('assets/img/elements/food-placeholder.png') }}" class="rounded mb-3" style="width: 100%; height: 100px; object-fit: cover;">
                                    <h6 class="mb-1 text-truncate">{{ $item->name }}</h6>
                                    <span class="badge bg-label-primary">R$ {{ number_format($item->selling_price, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Carrinho Lateral -->
    <div class="col-md-4">
        <form action="{{ route('ordens-servico.store') }}" method="POST" id="posForm">
            @csrf
            <div class="card pos-cart-container shadow-sm border-0">
                <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center bg-label-primary">
                    <h5 class="mb-0 fw-bold"><i class="ti tabler-shopping-cart me-2"></i> Carrinho</h5>
                    <button type="button" class="btn btn-sm btn-label-danger" onclick="clearCart()">Limpar</button>
                </div>
                
                <div class="card-body pt-4">
                    <!-- Cliente e Mesa -->
                    <div class="mb-3">
                        <label class="form-label">Cliente (Opcional)</label>
                        <select name="client_id" id="client_id" class="select2 form-select">
                            <option value="">Consumidor Final</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Mesa / Senha / Nome do Pedido</label>
                        <input type="text" name="description" class="form-control form-control-lg fw-bold" placeholder="Ex: Mesa 05">
                    </div>

                    <hr class="my-4">

                    <div class="cart-items-list mb-4" id="cartItems">
                        <!-- Itens via JS -->
                        <div class="text-center py-5 text-muted opacity-50" id="emptyCartMsg">
                            <i class="ti tabler-shopping-cart-x fs-1 mb-2"></i>
                            <p>Carrinho Vazio</p>
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal:</span>
                            <span id="subtotalVal">R$ 0,00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0 fw-bold text-primary">Total:</h4>
                            <h4 class="mb-0 fw-bold text-primary" id="totalVal">R$ 0,00</h4>
                        </div>
                    </div>

                    <input type="hidden" name="status" value="pending">
                    <button type="submit" class="btn btn-primary btn-xl w-100 shadow-sm" id="btnSubmit" disabled>
                        <i class="ti tabler-tools-kitchen-2 me-2"></i> ENVIAR PARA COZINHA
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    let cart = {};

    function addToCart(id, name, price) {
        if (cart[id]) {
            cart[id].qty++;
        } else {
            cart[id] = { name, price, qty: 1 };
        }
        renderCart();
    }

    function updateQty(id, delta) {
        if (cart[id]) {
            cart[id].qty += delta;
            if (cart[id].qty <= 0) delete cart[id];
            renderCart();
        }
    }

    function clearCart() {
        cart = {};
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cartItems');
        const emptyMsg = document.getElementById('emptyCartMsg');
        const btnSubmit = document.getElementById('btnSubmit');
        let html = '';
        let total = 0;

        const ids = Object.keys(cart);
        if (ids.length === 0) {
            container.innerHTML = '';
            container.appendChild(emptyMsg);
            btnSubmit.disabled = true;
            document.getElementById('totalVal').innerText = 'R$ 0,00';
            document.getElementById('subtotalVal').innerText = 'R$ 0,00';
            return;
        }

        btnSubmit.disabled = false;
        ids.forEach(id => {
            const item = cart[id];
            const itemTotal = item.price * item.qty;
            total += itemTotal;
            html += `
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <div style="flex: 1">
                        <h6 class="mb-0 fw-bold">${item.name}</h6>
                        <small class="text-primary">R$ ${item.price.toFixed(2)}</small>
                        <input type="hidden" name="parts[${id}][selected]" value="1">
                        <input type="hidden" name="parts[${id}][price]" value="${item.price}">
                        <input type="hidden" name="parts[${id}][quantity]" value="${item.qty}">
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-xs btn-label-secondary" onclick="updateQty('${id}', -1)">-</button>
                        <span class="fw-bold">${item.qty}</span>
                        <button type="button" class="btn btn-xs btn-label-secondary" onclick="updateQty('${id}', 1)">+</button>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        const formattedTotal = 'R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
        document.getElementById('totalVal').innerText = formattedTotal;
        document.getElementById('subtotalVal').innerText = formattedTotal;
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Busca e Filtros
        document.getElementById('posSearch').addEventListener('input', function(e) {
            const val = e.target.value.toLowerCase();
            document.querySelectorAll('.pos-item').forEach(item => {
                item.style.display = item.dataset.search.includes(val) ? 'block' : 'none';
            });
        });

        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const cat = this.dataset.category;
                document.querySelectorAll('.category-btn').forEach(b => b.classList.replace('btn-primary', 'btn-label-secondary'));
                this.classList.replace('btn-label-secondary', 'btn-primary');
                
                document.querySelectorAll('.pos-item').forEach(item => {
                    item.style.display = (cat === 'all' || item.dataset.category === cat) ? 'block' : 'none';
                });
            });
        });

        $('#client_id').select2({
            placeholder: "Consumidor Final",
            ajax: {
                url: '/api/clients/search',
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term }),
                processResults: data => ({ results: data.results }),
                cache: true
            }
        });
    });
</script>

@else
{{-- INTERFACE PADRÃO PARA OUTROS NICHOS --}}
<form action="{{ route('ordens-servico.store') }}" method="POST">
    @csrf
    <div class="row">
        <!-- Coluna Esquerda: Dados Gerais -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Informações Básicas</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Cliente</label>
                            <select name="client_id" id="client_id_std" class="select2 form-select" required>
                                <option value="">Selecione o Cliente</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ niche('entity') }} (Obrigatório)</label>
                            <select name="veiculo_id" id="veiculo_id" class="select2 form-select" required disabled>
                                <option value="">Selecione o Cliente Primeiro</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Status Inicial</label>
                            <select name="status" class="form-select">
                                <option value="pending">Aguardando Início</option>
                                <option value="running">Em Execução</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Descrição Geral do Problema / Relato do Cliente</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <!-- Serviços -->
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Serviços (Mão de Obra)</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th width="50">Add</th>
                                    <th>Serviço</th>
                                    <th>Preço Un.</th>
                                    <th>Qtd</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($services as $service)
                                <tr>
                                    <td><input type="checkbox" name="services[{{ $service->id }}][selected]" class="form-check-input"></td>
                                    <td>{{ $service->name }}</td>
                                    <td><input type="number" step="0.01" name="services[{{ $service->id }}][price]" value="{{ $service->price }}" class="form-control form-control-sm"></td>
                                    <td><input type="number" name="services[{{ $service->id }}][quantity]" value="1" class="form-control form-control-sm" style="width: 70px"></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Peças -->
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">{{ niche('inventory_items') }}</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th width="50">Add</th>
                                    <th>Item</th>
                                    <th>Venda Un.</th>
                                    <th>Qtd</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($parts as $part)
                                <tr>
                                    <td><input type="checkbox" name="parts[{{ $part->id }}][selected]" class="form-check-input"></td>
                                    <td>{{ $part->name }} <small>(Estoque: {{ $part->quantity }})</small></td>
                                    <td><input type="number" step="0.01" name="parts[{{ $part->id }}][price]" value="{{ $part->selling_price }}" class="form-control form-control-sm"></td>
                                    <td><input type="number" name="parts[{{ $part->id }}][quantity]" value="1" class="form-control form-control-sm" style="width: 70px"></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Ações -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Ações</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="redirect_to_checklist" id="redirect_to_checklist" value="1" checked>
                            <label class="form-check-input-label" for="redirect_to_checklist">Realizar vistoria de entrada após salvar</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-3">Abrir {{ niche('entity') }}</button>
                    <a href="{{ route('ordens-servico') }}" class="btn btn-label-secondary w-100">Cancelar</a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#client_id_std').select2({
            placeholder: "Buscar cliente...",
            ajax: {
                url: '/api/clients/search',
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term }),
                processResults: data => ({ results: data.results }),
                cache: true
            }
        });

        $('#client_id_std').on('change', function() {
            const clientId = $(this).val();
            const vehicleSelect = $('#veiculo_id');
            if (clientId) {
                fetch(`/api/get-vehicles/${clientId}`)
                    .then(res => res.json())
                    .then(data => {
                        let html = '<option value="">Selecione o {{ niche("entity") }}</option>';
                        data.forEach(v => { html += `<option value="${v.id}">${v.placa} - ${v.modelo}</option>`; });
                        vehicleSelect.html(html).prop('disabled', false);
                    });
            }
        });
    });
</script>
@endif

@endsection