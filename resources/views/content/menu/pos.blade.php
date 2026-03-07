@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', niche_translate('Balcão de Vendas - PDV'))

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/animate-css/animate.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/select2/select2.scss'
])
<style>
    :root {
        --pos-primary: var(--bs-primary, #7367f0);
        --pos-secondary: #f8f7fa;
        --pos-success: #28c76f;
        --pos-border: #dbdade;
        --pos-bg: #f4f4f7;
    }

    .pos-wrapper {
        display: flex;
        height: calc(100vh - 120px);
        /* Ajustando altura para subtrair navbar/footer */
        width: 100%;
        overflow: hidden;
        background: var(--pos-bg);
        border-radius: 10px;
    }

    /* Left Side: Product Selection */
    .pos-products-section {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 1.5rem;
        overflow-y: auto;
    }

    .pos-header-sticky {
        position: sticky;
        top: 0;
        z-index: 10;
        background: var(--pos-bg);
        margin-bottom: 1.5rem;
    }

    .category-pills {
        display: flex;
        gap: 0.6rem;
        overflow-x: auto;
        padding: 0.5rem 0 1rem;
        scrollbar-width: none;
    }

    .category-pills::-webkit-scrollbar {
        display: none;
    }

    .category-pill {
        white-space: nowrap;
        padding: 0.7rem 1.4rem;
        border-radius: 50px;
        background: white;
        border: 1px solid var(--pos-border);
        cursor: pointer;
        transition: all 0.2s;
        font-weight: 600;
        color: #5d596c;
    }

    .category-pill.active {
        background: var(--pos-primary);
        color: white;
        border-color: var(--pos-primary);
        box-shadow: 0 4px 12px rgba(115, 103, 240, 0.3);
    }

    /* Grid layout for products */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
        gap: 1.25rem;
    }

    .product-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--pos-border);
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        flex-direction: column;
        animation: fadeIn 0.3s ease;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
        border-color: var(--pos-primary);
    }

    .product-card .img-container {
        width: 100%;
        height: 120px;
        overflow: hidden;
        background: var(--pos-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: 1px solid var(--pos-border);
    }

    .product-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-card-body {
        padding: 0.9rem;
        text-align: center;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .product-name {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.4rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        color: #5d596c;
    }

    .product-price {
        color: var(--pos-primary);
        font-weight: 800;
        font-size: 1.1rem;
    }

    /* Right Side: Cart and Actions */
    .pos-cart-section {
        width: 420px;
        background: white;
        border-left: 1px solid var(--pos-border);
        display: flex;
        flex-direction: column;
        box-shadow: -5px 0 25px rgba(0, 0, 0, 0.03);
    }

    .cart-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--pos-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cart-items-list {
        flex: 1;
        overflow-y: auto;
        padding: 1.25rem;
    }

    .cart-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 0.75rem;
        background: #f8f7fa;
        border: 1px solid #f1f0f2;
        animation: slideInRight 0.2s ease;
    }

    .cart-item-info {
        flex: 1;
    }

    .cart-item-name {
        font-weight: 700;
        font-size: 0.95rem;
        color: #5d596c;
    }

    .cart-item-price {
        font-size: 0.85rem;
        color: #a5a3ae;
        font-weight: 600;
    }

    .cart-qty-ctrl {
        display: flex;
        align-items: center;
        background: white;
        border: 1px solid var(--pos-border);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .cart-qty-btn {
        padding: 0.3rem 0.8rem;
        background: none;
        border: none;
        cursor: pointer;
        font-weight: 800;
        color: var(--pos-primary);
        font-size: 1.2rem;
    }

    .cart-qty-btn:hover {
        background: #f4f4f7;
    }

    .cart-qty-val {
        padding: 0 0.8rem;
        min-width: 35px;
        text-align: center;
        font-weight: 700;
        font-size: 1rem;
        border-left: 1px solid var(--pos-border);
        border-right: 1px solid var(--pos-border);
    }

    .cart-footer {
        padding: 1.5rem;
        background: #fcfcfd;
        border-top: 1px solid var(--pos-border);
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.6rem;
        font-size: 1rem;
        font-weight: 600;
        color: #5d596c;
    }

    .summary-total {
        font-size: 1.6rem;
        font-weight: 900;
        color: #2f2b3d;
        margin-top: 0.8rem;
        border-top: 2px dashed var(--pos-border);
        padding-top: 1rem;
    }

    /* Payment icons */
    .payment-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.75rem;
        margin: 1.25rem 0;
    }

    .pay-opt {
        border: 2px solid var(--pos-border);
        border-radius: 12px;
        padding: 0.8rem 0.5rem;
        text-align: center;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 700;
        transition: all 0.2s;
        color: #a5a3ae;
    }

    .pay-opt.selected {
        border-color: var(--pos-primary);
        background: rgba(115, 103, 240, 0.08);
        color: var(--pos-primary);
    }

    .pay-opt i {
        display: block;
        font-size: 1.8rem;
        margin-bottom: 5px;
    }

    .btn-checkout-pos {
        width: 100%;
        padding: 1.1rem;
        font-size: 1.2rem;
        font-weight: 800;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(115, 103, 240, 0.3);
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes slideInRight {
        from {
            transform: translateX(20px);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @media (max-width: 1200px) {
        .pos-cart-section {
            width: 360px;
        }
    }

    /* Otimizações Mobile PDV */
    @media (max-width: 767.98px) {
        .content-wrapper { padding: 0 !important; }
        .container-xxl { padding: 0 !important; }
        .pos-wrapper { flex-direction: column; height: calc(100vh - 100px); border-radius: 0; }
        
        .product-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 10px !important;
        }
        
        .product-card .img-container { height: 100px !important; }
        .product-name { font-size: 0.85rem !important; }
        
        .pos-cart-section {
            position: fixed !important;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100% !important;
            z-index: 1050;
            height: 60px;
            transition: all 0.3s ease;
            border-left: 0 !important;
            border-top: 1px solid var(--pos-border);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .pos-cart-section.expanded { height: 100vh !important; top: 0; }
        
        #mobile-cart-toggle {
            display: flex !important;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: var(--pos-primary);
            color: white;
            font-weight: bold;
            cursor: pointer;
            min-height: 60px;
        }
        
        .cart-header, .px-4.pt-4, .cart-items-list, .cart-footer { display: none; }
        .expanded .cart-header, .expanded .px-4.pt-4, .expanded .cart-items-list, .expanded .cart-footer { display: flex !important; }
        .expanded .px-4.pt-4 { display: block !important; }
        .expanded .cart-items-list { display: block !important; flex: 1; overflow-y: auto; }
        .expanded #mobile-cart-toggle { border-radius: 0; }
        .expanded #mobile-cart-toggle i.chevron { transform: rotate(180deg); }
    }
    
    @media (min-width: 768px) {
        #mobile-cart-toggle { display: none !important; }
    }
</style>
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('content')
<div class="pos-wrapper shadow">
    <!-- Items Selection area -->
    <div class="pos-products-section">
        <div class="pos-header-sticky">
            <div class="row align-items-center g-3">
                <div class="col">
                    <div class="input-group input-group-merge shadow-sm">
                        <span class="input-group-text"><i class="ti tabler-search fs-4"></i></span>
                        <input type="text" id="productSearch" class="form-control form-control-lg" placeholder="Buscar pedidos, combos ou adicionais...">
                    </div>
                </div>
                <div class="col-auto">
                    <a href="{{ route('dashboard') }}" class="btn btn-label-secondary btn-icon btn-xl rounded-circle">
                        <i class="ti tabler-home fs-3"></i>
                    </a>
                </div>
            </div>

            <div class="category-pills mt-3">
                <div class="category-pill active" data-category="all">
                    Todos
                </div>
                @foreach($categories as $cat)
                <div class="category-pill" data-category="{{ $cat->id }}">
                    <i class="ti {{ $cat->icon ?? 'tabler-category' }} me-1"></i> {{ $cat->name }}
                </div>
                @endforeach
            </div>
        </div>

        <div class="product-grid" id="productGrid">
            @php $totalItems = 0; @endphp
            @foreach($categories as $cat)
            @foreach($cat->items as $item)
            @php $totalItems++; @endphp
            <div class="product-card"
                data-id="{{ $item->id }}"
                data-name="{{ $item->name }}"
                data-price="{{ $item->selling_price }}"
                data-category="{{ $cat->id }}">
                <div class="img-container">
                    @if($item->mainImage)
                    <img src="{{ asset('storage/'.$item->mainImage->path) }}" alt="{{ $item->name }}" onerror="this.src='{{ asset('assets/img/front-pages/misc/product-image.png') }}';">
                    @else
                    <img src="{{ asset('assets/img/front-pages/misc/product-image.png') }}" alt="{{ $item->name }}" style="opacity: 0.5; object-fit: contain; padding: 10px;">
                    @endif
                </div>
                <div class="product-card-body">
                    <div class="product-name" title="{{ $item->name }}">{{ $item->name }}</div>
                    <div class="product-price">R$ {{ number_format($item->selling_price, 2, ',', '.') }}</div>
                </div>
            </div>
            @endforeach
            @endforeach

            @if($totalItems == 0)
            <div class="col-12 text-center py-5 w-100" id="emptyState">
                <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3">
                    <span class="avatar-initial rounded-circle"><i class="ti tabler-package-off fs-1"></i></span>
                </div>
                <h4 class="text-muted">Nenhum produto cadastrado.</h4>
                <p class="text-muted">Adicione lanches e bebidas ao cardápio para começar as vendas.</p>
                <div class="d-flex justify-content-center gap-2 mt-2">
                    <a href="{{ route('inventory.items') }}" class="btn btn-primary">Ir para Estoque</a>
                    <a href="{{ route('menu.categories') }}" class="btn btn-label-secondary">Categorias</a>
                </div>
            </div>
            @else
            <div class="col-12 text-center py-5 w-100" id="emptyState" style="display: none;">
                <div class="avatar avatar-lg bg-label-secondary mx-auto mb-3">
                    <span class="avatar-initial rounded-circle"><i class="ti tabler-search fs-3"></i></span>
                </div>
                <h5 class="text-muted">Nenhum produto encontrado na busca.</h5>
            </div>
            @endif
        </div>
    </div>

    <!-- Sidebar with items and payment -->
    <div class="pos-cart-section" id="cartSection">
        <!-- Header Mobile do Carrinho -->
        <div id="mobile-cart-toggle">
            <span><i class="ti tabler-shopping-cart me-2"></i> VER PEDIDO</span>
            <span id="mobile-total-display">R$ 0,00</span>
            <i class="ti tabler-chevron-up chevron"></i>
        </div>

        <div class="cart-header">
            <h5 class="mb-0 fw-bold"><i class="ti tabler-shopping-cart-check me-2 text-primary"></i>Pedido Atual</h5>
            <button class="btn btn-sm btn-label-danger" id="btn-clear-cart">Limpar</button>
        </div>

        <div class="px-4 pt-4">
            <select id="client_select" class="select2 form-select">
                <option value="">Consumidor Final (Sem Cadastro)</option>
                @foreach($clients as $client)
                <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="cart-items-list" id="cart-container">
            <!-- Populated via JS -->
            <div class="text-center py-5 text-muted opacity-50" id="cart-empty-msg">
                <i class="ti tabler-shopping-cart-x fs-1 mb-3"></i>
                <h6 class="mb-0">Seu carrinho está vazio</h6>
            </div>
        </div>

        <div class="cart-footer">
            <div class="summary-row">
                <span>Subtotal</span>
                <span id="subtotal-val">R$ 0,00</span>
            </div>
            <div class="summary-row summary-total">
                <span>Total</span>
                <span id="total-val">R$ 0,00</span>
            </div>

            <div class="payment-grid">
                @forelse($paymentMethods as $index => $method)
                <div class="pay-opt {{ $index === 0 ? 'selected' : '' }}" data-method="{{ $method->id }}" data-name="{{ $method->name }}">
                    <i class="ti {{ $method->icon ?? 'tabler-credit-card' }}"></i> {{ $method->name }}
                </div>
                @empty
                <div class="col-12 text-center">
                    <small class="text-danger">Nenhuma forma de pagamento ativa. <a href="{{ route('finance.payment-methods') }}">Cadastrar agora</a></small>
                </div>
                @endforelse
            </div>

            <button class="btn btn-primary btn-checkout-pos" id="btn-checkout" disabled>
                Finalizar Pedido
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cartSection = document.getElementById('cartSection');
        const mobileCartToggle = document.getElementById('mobile-cart-toggle');
        const mobileTotalDisplay = document.getElementById('mobile-total-display');

        if (mobileCartToggle) {
            mobileCartToggle.addEventListener('click', function() {
                cartSection.classList.toggle('expanded');
            });
        }

        let cart = [];
        const productCards = document.querySelectorAll('.product-card');
        const cartContainer = document.getElementById('cart-container');
        const cartEmptyMsg = document.getElementById('cart-empty-msg');
        const subtotalLabel = document.getElementById('subtotal-val');
        const totalLabel = document.getElementById('total-val');
        const checkoutBtn = document.getElementById('btn-checkout');
        const clearBtn = document.getElementById('btn-clear-cart');
        const searchInput = document.getElementById('productSearch');
        const categoryPills = document.querySelectorAll('.category-pill');

        // Select2 registration
        $('#client_select').select2({
            placeholder: "Selecione o Cliente (Opcional)"
        });

        // Add product to cart logic
        productCards.forEach(card => {
            card.addEventListener('click', () => {
                const id = card.dataset.id;
                const name = card.dataset.name;
                const price = parseFloat(card.dataset.price);

                const existing = cart.find(item => item.id === id);
                if (existing) {
                    existing.qty++;
                } else {
                    cart.push({
                        id,
                        name,
                        price,
                        qty: 1
                    });
                }
                updateCartUI();
            });
        });

        // Handle cart quantity changes
        cartContainer.addEventListener('click', (e) => {
            const id = e.target.dataset.id;
            if (!id) return;

            const item = cart.find(i => i.id === id);
            if (e.target.classList.contains('qty-plus')) {
                item.qty++;
                updateCartUI();
            } else if (e.target.classList.contains('qty-minus')) {
                if (item.qty > 1) {
                    item.qty--;
                } else {
                    cart = cart.filter(i => i.id !== id);
                }
                updateCartUI();
            }
        });

        function updateCartUI() {
            if (cart.length === 0) {
                cartContainer.innerHTML = '';
                cartContainer.appendChild(cartEmptyMsg);
                subtotalLabel.innerText = 'R$ 0,00';
                totalLabel.innerText = 'R$ 0,00';
                if(mobileTotalDisplay) mobileTotalDisplay.innerText = 'R$ 0,00';
                checkoutBtn.disabled = true;
                return;
            }

            checkoutBtn.disabled = false;
            let html = '';
            let total = 0;
            cart.forEach(item => {
                const sub = item.price * item.qty;
                total += sub;
                html += `
                <div class="cart-item">
                    <div class="cart-item-info">
                        <div class="cart-item-name text-truncate" style="max-width: 180px;">${item.name}</div>
                        <div class="cart-item-price">R$ ${item.price.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</div>
                    </div>
                    <div class="cart-qty-ctrl">
                        <button class="cart-qty-btn qty-minus" data-id="${item.id}">-</button>
                        <div class="cart-qty-val">${item.qty}</div>
                        <button class="cart-qty-btn qty-plus" data-id="${item.id}">+</button>
                    </div>
                </div>
            `;
            });
            cartContainer.innerHTML = html;
            const formattedTotal = 'R$ ' + total.toLocaleString('pt-BR', {
                minimumFractionDigits: 2
            });
            subtotalLabel.innerText = formattedTotal;
            totalLabel.innerText = formattedTotal;
            if(mobileTotalDisplay) mobileTotalDisplay.innerText = formattedTotal;
        }

        // Payment Selection indicator
        document.querySelectorAll('.pay-opt').forEach(opt => {
            opt.addEventListener('click', () => {
                document.querySelectorAll('.pay-opt').forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');
            });
        });

        // Final checkout submission
        checkoutBtn.addEventListener('click', () => {
            const clientId = $('#client_select').val();
            const selectedOpt = document.querySelector('.pay-opt.selected');
            
            if (!selectedOpt) {
                Swal.fire("Atenção", "Selecione uma forma de pagamento", "warning");
                return;
            }

            const paymentMethodId = selectedOpt.dataset.method;
            const methodLabel = selectedOpt.dataset.name;

            Swal.fire({
                title: "Confirmar Pedido",
                html: `Método: <b>${methodLabel}</b><br><br><span class="h4 text-success">Total: ${totalLabel.innerText}</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#7367f0',
                confirmButtonText: "Sim, Finalizar!",
                cancelButtonText: "Voltar",
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    const parts = {};
                    cart.forEach(item => {
                        parts[item.id] = {
                            selected: true,
                            price: item.price,
                            quantity: item.qty
                        };
                    });

                    return fetch('{{ route("ordens-servico.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                client_id: clientId || 1,
                                status: 'finalized',
                                payment_method_id: paymentMethodId,
                                description: 'VENDA PDV - ' + methodLabel,
                                parts: parts
                            })
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Error processing sale');
                            return response.json();
                        })
                        .catch(error => {
                            Swal.showValidationMessage(`Fail: ${error}`);
                        });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    cart = [];
                    updateCartUI();
                    Swal.fire({
                        title: "Pedido Concluído!",
                        text: "O estoque foi atualizado.",
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        position: 'top-end',
                        toast: true
                    });
                }
            });
        });

        // Clear whole cart
        clearBtn.addEventListener('click', () => {
            if (cart.length > 0) {
                cart = [];
                updateCartUI();
            }
        });

        // UI filtering for search and categories
        searchInput.addEventListener('input', filterContent);
        categoryPills.forEach(pill => {
            pill.addEventListener('click', () => {
                categoryPills.forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
                filterContent();
            });
        });

        function filterContent() {
            const query = searchInput.value.toLowerCase();
            const activeCat = document.querySelector('.category-pill.active').dataset.category;

            let visibleCount = 0;
            productCards.forEach(card => {
                const name = card.dataset.name.toLowerCase();
                const cat = card.dataset.category;

                const matchesQuery = name.includes(query);
                const matchesCat = (activeCat === 'all' || cat === activeCat);

                if (matchesQuery && matchesCat) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            const emptyMsg = document.getElementById('emptyState');
            if (emptyMsg) {
                emptyMsg.style.display = (visibleCount === 0) ? 'block' : 'none';
            }
        }

        // Initializing state
        updateCartUI();
    });
</script>
@endsection