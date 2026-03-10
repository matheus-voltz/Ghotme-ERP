@extends('layouts/layoutMaster')

@section('title', $company->name . ' - Cardápio Digital')

@section('vendor-style')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Outfit', sans-serif !important;
        background-color: #f8f7fa;
    }

    .public-menu-wrapper {
        max-width: 800px;
        margin: 0 auto;
        padding-bottom: 100px;
    }

    .hero-section {
        background: linear-gradient(135deg, #7367f0 0%, #a098f5 100%);
        padding: 60px 20px;
        text-align: center;
        color: white;
        border-radius: 0 0 30px 30px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(115, 103, 240, 0.2);
    }

    .company-logo {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: white;
        padding: 5px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        display: inline-block;
    }

    .company-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .category-nav {
        position: sticky;
        top: 0;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        z-index: 1000;
        padding: 15px 0;
        margin-bottom: 20px;
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .category-nav::-webkit-scrollbar {
        display: none;
    }

    .nav-pill {
        display: inline-block;
        padding: 8px 20px;
        margin: 0 5px;
        border-radius: 25px;
        background: #f1f0f2;
        color: #5d596c;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .nav-pill.active {
        background: #7367f0;
        color: white;
        box-shadow: 0 4px 10px rgba(115, 103, 240, 0.3);
    }

    .product-card {
        background: white;
        border-radius: 15px;
        padding: 15px;
        margin-bottom: 15px;
        display: flex;
        gap: 15px;
        border: 1px solid #dbdade;
        transition: transform 0.2s ease;
        cursor: pointer;
    }

    .product-card:hover {
        transform: scale(1.02);
    }

    .product-img {
        width: 90px;
        height: 90px;
        border-radius: 12px;
        object-fit: cover;
    }

    .product-info {
        flex: 1;
    }

    .product-name {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 5px;
        color: #2f2b3d;
    }

    .product-desc {
        font-size: 0.9rem;
        color: #6f6b7d;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 8px;
    }

    .product-price {
        font-weight: 800;
        color: #7367f0;
        font-size: 1.2rem;
    }

    .category-title {
        font-weight: 800;
        font-size: 1.5rem;
        margin: 40px 0 20px;
        padding-left: 5px;
        color: #2f2b3d;
        border-left: 5px solid #7367f0;
        line-height: 1;
    }

    #cart-fab {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: #7367f0;
        color: white;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 20px rgba(115, 103, 240, 0.4);
        z-index: 1001;
        cursor: pointer;
        transition: transform 0.2s;
    }

    #cart-fab:active {
        transform: scale(0.9);
    }

    .badge-cart {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ea5455;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        border: 2px solid white;
    }
</style>
@endsection

@section('content')
<div class="public-menu-wrapper">
    <div class="hero-section">
        <div class="company-logo">
            <img src="{{ $company->logo_path ? asset('storage/' . $company->logo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($company->name) . '&background=7367f0&color=fff' }}" alt="{{ $company->name }}">
        </div>
        <h1 class="h2 fw-bold mb-1">{{ $company->name }}</h1>
        <p class="mb-0 opacity-75">O melhor sabor para o seu dia!</p>
    </div>

    <div class="category-nav">
        <div class="container d-flex">
            <a href="#" class="nav-pill active">Todos</a>
            @foreach($categories as $cat)
            <a href="#cat-{{ $cat->id }}" class="nav-pill">{{ $cat->name }}</a>
            @endforeach
        </div>
    </div>

    <div class="container">
        @foreach($categories as $cat)
        <div id="cat-{{ $cat->id }}">
            <h2 class="category-title">{{ $cat->name }}</h2>

            @foreach($cat->items as $item)
            <div class="product-card shadow-sm" data-bs-toggle="modal" data-bs-target="#modalProductDetail{{ $item->id }}">
                <img src="{{ $item->mainImage ? asset('storage/' . $item->mainImage->path) : asset('assets/img/front-pages/misc/product-image.png') }}" class="product-img" onerror="this.src='{{ asset('assets/img/front-pages/misc/product-image.png') }}';">
                <div class="product-info">
                    <div class="product-name">{{ $item->name }}</div>
                    <div class="product-desc">{{ $item->description }}</div>
                    <div class="product-price">R$ {{ number_format($item->selling_price, 2, ',', '.') }}</div>
                </div>
            </div>

            <!-- Modal Detalhe Produto -->
            <div class="modal fade" id="modalProductDetail{{ $item->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 overflow-hidden" style="border-radius: 20px;">
                        <img src="{{ $item->mainImage ? asset('storage/' . $item->mainImage->path) : asset('assets/img/front-pages/misc/product-image.png') }}"
                            class="w-100" style="height: 250px; object-fit: cover;"
                            onerror="this.src='{{ asset('assets/img/front-pages/misc/product-image.png') }}';">
                        <div class="modal-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h3 class="fw-bold mb-0">{{ $item->name }}</h3>
                                <span class="text-primary h4 fw-black mb-0">R$ {{ number_format($item->selling_price, 2, ',', '.') }}</span>
                            </div>
                            <p class="text-muted mb-4">{{ $item->description }}</p>

                            @if($item->is_ingredient)
                            <div class="alert alert-info py-2 small">Este item é um adicional.</div>
                            @endif

                            <button class="btn btn-primary w-100 py-3 rounded-pill fw-bold" onclick="addToCart({{ $item->id }}, '{{ $item->name }}', {{ $item->selling_price }})">
                                Adicionar ao Pedido
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
</div>

<div id="cart-fab" onclick="showCart()">
    <i class="ti tabler-shopping-cart fs-3"></i>
    <span class="badge-cart d-none" id="cart-count">0</span>
</div>

<div class="modal fade" id="modalCart" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Seu Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="cart-items-list">
                    <!-- JS -->
                </div>
                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <span class="h5 fw-bold">Total</span>
                    <span class="h5 fw-bold text-primary" id="cart-total">R$ 0,00</span>
                </div>
            </div>
            <div class="modal-footer flex-column border-0 pt-0">
                <a href="#" id="btn-whatsapp-order" target="_blank" class="btn btn-success w-100 py-3 rounded-pill fw-bold mb-2 disabled">
                    <i class="ti tabler-brand-whatsapp me-2"></i> Enviar pelo WhatsApp
                </a>
                <button type="button" class="btn btn-label-secondary w-100 rounded-pill" data-bs-dismiss="modal">Continuar Comprando</button>
            </div>
        </div>
    </div>
</div>

<script>
    let cart = [];

    function addToCart(id, name, price) {
        const existing = cart.find(i => i.id === id);
        if (existing) {
            existing.qty++;
        } else {
            cart.push({
                id: id,
                name: name,
                price: price,
                qty: 1
            });
        }
        updateCartUI();

        const modalEl = document.getElementById('modalProductDetail' + id);
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        Swal.fire({
            title: 'Adicionado!',
            text: name + ' foi para o carrinho.',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }

    function updateCartUI() {
        const count = cart.reduce((acc, current) => acc + current.qty, 0);
        const cartCountEl = document.getElementById('cart-count');

        if (count > 0) {
            cartCountEl.classList.remove('d-none');
            cartCountEl.innerText = count;
        } else {
            cartCountEl.classList.add('d-none');
        }

        let html = '';
        let total = 0;
        let whatsappText = '*NOVO PEDIDO - ' + @json($company - > name) + '*\n\n';

        cart.forEach(item => {
            const sub = item.price * item.qty;
            total += sub;
            const subStr = sub.toLocaleString('pt-BR', {
                minimumFractionDigits: 2
            });
            html += `
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="fw-bold font-heading">${item.name}</div>
                        <div class="small text-muted">${item.qty}x R$ ${item.price.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</div>
                    </div>
                    <div class="fw-bold">R$ ${subStr}</div>
                </div>
            `;
            whatsappText += '• ' + item.qty + 'x ' + item.name + ' (R$ ' + subStr + ')\n';
        });

        if (cart.length === 0) {
            html = '<div class="text-center py-4 text-muted">Seu carrinho está vazio</div>';
            document.getElementById('btn-whatsapp-order').classList.add('disabled');
        } else {
            document.getElementById('btn-whatsapp-order').classList.remove('disabled');
        }

        whatsappText += '\n*TOTAL: R$ ' + total.toLocaleString('pt-BR', {
            minimumFractionDigits: 2
        }) + '*';

        document.getElementById('cart-items-list').innerHTML = html;
        document.getElementById('cart-total').innerText = 'R$ ' + total.toLocaleString('pt-BR', {
            minimumFractionDigits: 2
        });

        const waLink = document.getElementById('btn-whatsapp-order');
        const phone = @json(preg_replace('/[^0-9]/', '', $company - > phone));
        waLink.href = 'https://wa.me/' + phone + '?text=' + encodeURIComponent(whatsappText);
    }

    function showCart() {
        new bootstrap.Modal(document.getElementById('modalCart')).show();
    }

    document.querySelectorAll('.nav-pill').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();

            document.querySelectorAll('.nav-pill').forEach(p => p.classList.remove('active'));
            this.classList.add('active');

            const targetId = this.getAttribute('href');
            if (targetId === '#') {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
                return;
            }

            const target = document.querySelector(targetId);
            if (target) {
                const offset = 80;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - offset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
</script>
@endsection