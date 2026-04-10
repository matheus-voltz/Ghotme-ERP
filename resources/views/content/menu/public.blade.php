@extends('layouts/layoutMaster')

@section('title', $company->name . ' - Cardápio Digital')

@section('vendor-style')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
@php
$themeColor = $company->hasConfig('public_menu_theme', '#ff4757');
if (!$themeColor || $themeColor === true) $themeColor = '#ff4757';
@endphp
<style>
    :root {
        --primary: {
                {
                $themeColor
            }
        }

        ;

        --primary-hover: {
                {
                $themeColor
            }
        }

        cc;
        --dark: #2f3542;
        --gray-light: #f1f2f6;
        --gray-text: #747d8c;
        --bg: #f8f9fa;
        --card-radius: 16px;
    }

    body {
        font-family: 'Outfit', sans-serif !important;
        background-color: var(--bg);
        color: var(--dark);
        padding-bottom: 90px;
        /* Espaço para a Bottom Bar do carrinho */
    }

    /* Container Principal Mobile-First */
    .public-menu-wrapper {
        max-width: 600px;
        margin: 0 auto;
        background: #fff;
        min-height: 100vh;
        box-shadow: 0 0 40px rgba(0, 0, 0, 0.05);
        position: relative;
    }

    /* Hero Section - Header */
    .hero-section {
        background: #fff;
        padding: 40px 20px 20px;
        text-align: center;
        border-bottom: 1px solid var(--gray-light);
    }

    .company-logo {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        margin-bottom: 15px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        display: inline-block;
        border: 4px solid #fff;
    }

    .company-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .hero-title {
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 5px;
    }

    .hero-subtitle {
        color: var(--gray-text);
        font-size: 0.95rem;
    }

    /* Sticky Nav Categorias */
    .category-nav-wrapper {
        position: sticky;
        top: 0;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        z-index: 1000;
        border-bottom: 1px solid var(--gray-light);
        padding: 12px 0;
    }

    .category-nav {
        display: flex;
        overflow-x: auto;
        padding: 0 20px;
        gap: 10px;
        scrollbar-width: none;
        scroll-behavior: smooth;
    }

    .category-nav::-webkit-scrollbar {
        display: none;
    }

    .nav-pill {
        padding: 8px 18px;
        border-radius: 20px;
        background: var(--bg);
        color: var(--dark);
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        white-space: nowrap;
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }

    .nav-pill.active {
        background: var(--primary);
        color: white;
        box-shadow: 0 4px 10px rgba(255, 71, 87, 0.3);
    }

    /* Títulos da Categoria */
    .category-title {
        font-weight: 800;
        font-size: 1.25rem;
        margin: 30px 20px 15px;
        color: var(--dark);
    }

    /* Cards de Produto Listagem */
    .product-list {
        padding: 0 20px;
    }

    .product-card {
        display: flex;
        background: #fff;
        border-radius: var(--card-radius);
        padding: 15px;
        margin-bottom: 15px;
        border: 1px solid var(--gray-light);
        transition: transform 0.2s, box-shadow 0.2s;
        gap: 15px;
        cursor: pointer;
    }

    .product-card:active {
        transform: scale(0.98);
        background-color: var(--gray-light);
    }

    .product-img {
        width: 100px;
        height: 100px;
        border-radius: 12px;
        object-fit: cover;
        background: var(--bg);
    }

    .product-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .product-name {
        font-weight: 700;
        font-size: 1.05rem;
        margin-bottom: 4px;
        color: var(--dark);
        line-height: 1.2;
    }

    .product-desc {
        font-size: 0.85rem;
        color: var(--gray-text);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 8px;
        line-height: 1.3;
    }

    .product-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .product-price {
        font-weight: 800;
        color: var(--primary);
        font-size: 1.15rem;
    }

    .btn-add-quick {
        background: var(--primary);
        color: white;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: bold;
        transition: background 0.2s;
    }

    .btn-add-quick:hover {
        background: var(--primary-hover);
    }

    /* Modal de Detalhe */
    .modal-detail-img {
        width: 100%;
        height: 250px;
        object-fit: cover;
        border-radius: 20px 20px 0 0;
    }

    .modal-content {
        border-radius: 20px;
        border: none;
    }

    /* BOTTOM BAR CARRINHO STICKY */
    .cart-bottom-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #fff;
        padding: 15px 20px;
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08);
        z-index: 1010;
        transform: translateY(120%);
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .cart-bottom-bar.show {
        transform: translateY(0);
    }

    .cart-bottom-bar-wrapper {
        max-width: 600px;
        margin: 0 auto;
    }

    .btn-checkout {
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 14px 20px;
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 700;
        font-size: 1.05rem;
        text-decoration: none;
    }

    .btn-checkout:hover {
        background: var(--primary-hover);
        color: white;
    }

    .checkout-qty {
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.9rem;
    }

    /* Controles de Quantidade no Carrinho */
    .qty-controls {
        display: flex;
        align-items: center;
        gap: 15px;
        background: var(--gray-light);
        border-radius: 8px;
        padding: 4px;
    }

    .qty-btn {
        background: white;
        border: none;
        border-radius: 6px;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: var(--dark);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    #cart-fab {
        display: none;
    }

    /* Ocultar o FAB antigo */

    /* Sistema de Busca */
    .search-wrapper {
        padding: 0 20px;
        margin-top: -25px;
        margin-bottom: 10px;
        position: relative;
        z-index: 1005;
    }

    .search-input-group {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        display: flex;
        align-items: center;
        padding: 5px 15px;
        border: 1px solid var(--gray-light);
    }

    .search-input-group i {
        color: var(--primary);
        font-size: 1.2rem;
    }

    .search-input-group input {
        border: none;
        padding: 10px;
        width: 100%;
        outline: none;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .no-results {
        display: none;
        text-align: center;
        padding: 40px 20px;
        color: var(--gray-text);
    }
</style>
@endsection

@section('content')
<div class="public-menu-wrapper">
    <!-- Header Otimizado -->
    <div class="hero-section">
        <div class="company-logo">
            <img src="{{ $company->logo_path ? asset('storage/' . $company->logo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($company->name) . '&background=ff4757&color=fff' }}" alt="{{ $company->name }}">
        </div>
        <h1 class="hero-title">{{ $company->name }}</h1>
        <p class="hero-subtitle">Cardápio Oficial Digital</p>
    </div>

    <!-- Barra de Busca -->
    <div class="search-wrapper">
        <div class="search-input-group">
            <i class="ti tabler-search"></i>
            <input type="text" id="menu-search" placeholder="O que você quer comer hoje?" onkeyup="filterMenu()">
        </div>
    </div>

    <!-- Navegação Sticky -->
    <div class="category-nav-wrapper">
        <div class="category-nav">
            @foreach($categories as $cat)
            <a href="#cat-{{ $cat->id }}" class="nav-pill {{ $loop->first ? 'active' : '' }}">{{ $cat->name }}</a>
            @endforeach
        </div>
    </div>

    <!-- Lista de Produtos -->
    <div class="product-list">
        <div id="no-results" class="no-results">
            <i class="ti tabler-search-off fs-1 mb-2 d-block"></i>
            <p class="mb-0 fw-bold">Nenhum item encontrado</p>
            <small>Tente buscar por outro termo.</small>
        </div>
        @foreach($categories as $cat)
        <div id="cat-{{ $cat->id }}" class="category-section">
            <h2 class="category-title">{{ $cat->name }}</h2>

            @foreach($cat->items as $item)
            <div class="product-card" onclick="openProductModal('modalProductDetail{{ $item->id }}', event)">
                <img src="{{ $item->mainImage ? asset('storage/' . $item->mainImage->path) : asset('assets/img/front-pages/misc/product-image.png') }}" class="product-img" onerror="this.src='{{ asset('assets/img/front-pages/misc/product-image.png') }}';">

                <div class="product-info">
                    <div>
                        <div class="product-name">{{ $item->name }}</div>
                        <div class="product-desc">{{ $item->description }}</div>
                    </div>
                    <div class="product-footer">
                        <div class="product-price">R$ {{ number_format($item->selling_price, 2, ',', '.') }}</div>

                        <!-- Botão + Direto (Previne clique no modal) -->
                        <button class="btn-add-quick" onclick="event.stopPropagation(); addToCart({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->selling_price }})">
                            <i class="ti tabler-plus fs-4"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Detalhe Produto (Mantém a estrutura base mas com classes atualizadas) -->
            <div class="modal fade" id="modalProductDetail{{ $item->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
                    <div class="modal-content">
                        <div class="position-relative">
                            <button type="button" class="btn-close position-absolute bg-white rounded-circle p-2 mt-3 ms-3 shadow-sm" data-bs-dismiss="modal" style="z-index: 10;"></button>
                            <img src="{{ $item->mainImage ? asset('storage/' . $item->mainImage->path) : asset('assets/img/front-pages/misc/product-image.png') }}" class="modal-detail-img" onerror="this.src='{{ asset('assets/img/front-pages/misc/product-image.png') }}';">
                        </div>

                        <div class="modal-body p-4">
                            <h3 class="fw-bold mb-1">{{ $item->name }}</h3>
                            <p class="text-muted mb-4">{{ $item->description }}</p>

                            <h4 class="fw-bold product-price mb-4">R$ {{ number_format($item->selling_price, 2, ',', '.') }}</h4>

                            @if($item->is_ingredient)
                            <div class="alert alert-secondary py-2 small d-flex align-items-center">
                                <i class="ti tabler-info-circle me-2"></i> Este item é um ingrediente/adicional.
                            </div>
                            @endif
                        </div>

                        <div class="modal-footer border-0 p-4 pt-0">
                            <button class="btn-checkout" onclick="addToCart({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->selling_price }}); bootstrap.Modal.getInstance(document.getElementById('modalProductDetail{{ $item->id }}')).hide();">
                                <span>Adicionar ao Pedido</span>
                                <i class="ti tabler-shopping-cart-plus"></i>
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

<!-- Bottom Bar do Carrinho (Exibida SÓ quando há itens) -->
<div class="cart-bottom-bar" id="cart-bottom-bar">
    <div class="cart-bottom-bar-wrapper">
        <a href="#" class="btn-checkout" onclick="showCart(); return false;">
            <div class="d-flex align-items-center gap-2">
                <i class="ti tabler-shopping-bag"></i>
                <span>Ver Sacola</span>
                <span class="checkout-qty" id="cart-badge-qty">0</span>
            </div>
            <span id="cart-badge-total">R$ 0,00</span>
        </a>
    </div>
</div>

<!-- Modal Principal do Carrinho/Sacola -->
<div class="modal fade" id="modalCart" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Sua Sacola</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="cart-items-list" class="p-4">
                    <!-- Javascript Injects here -->
                </div>

                <div class="bg-light p-4 mt-2">
                    <label class="form-label fw-bold small text-muted text-uppercase mb-2">Alguma observação?</label>
                    <textarea id="order-notes" class="form-control" rows="2" placeholder="Ex: Tirar cebola, troco para R$ 50..."></textarea>
                </div>
            </div>

            <div class="modal-footer flex-column border-top p-4 bg-white">
                <div class="d-flex justify-content-between w-100 mb-3">
                    <span class="h5 fw-bold mb-0 text-muted">Total a Pagar</span>
                    <span class="h5 fw-bold mb-0 product-price" id="cart-total">R$ 0,00</span>
                </div>
                <button onclick="sendOrderWhatsApp()" class="btn-checkout w-100 justify-content-center gap-2" id="btn-whatsapp-order">
                    <i class="ti tabler-brand-whatsapp fs-4"></i>
                    <span>Tudo Certo, Fazer Pedido</span>
                </button>
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none mt-2 w-100" data-bs-dismiss="modal">Continuar Olhando</button>
            </div>
        </div>
    </div>
</div>

<script>
    let cart = [];
    const companyName = @json($company->name);
    const companyPhone = @json(preg_replace('/[^0-9]/', '', $company->phone));

    // Impede que o modal abra se clicar no botão de (+) rápido
    function openProductModal(modalId, event) {
        if (event.target.closest('.btn-add-quick')) return;
        new bootstrap.Modal(document.getElementById(modalId)).show();
    }

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

        // Pequena vibração no celular (se suportado)
        if (navigator.vibrate) navigator.vibrate(50);

        Swal.fire({
            title: 'Adicionado!',
            text: 'Item colocado na sacola.',
            icon: 'success',
            timer: 1000,
            showConfirmButton: false,
            toast: true,
            position: 'top',
            customClass: {
                popup: 'rounded-4'
            }
        });
    }

    function updateQty(id, change) {
        const itemIndex = cart.findIndex(i => i.id === id);
        if (itemIndex > -1) {
            cart[itemIndex].qty += change;
            if (cart[itemIndex].qty <= 0) {
                cart.splice(itemIndex, 1);
            }
        }
        updateCartUI();
    }

    function updateCartUI() {
        const count = cart.reduce((acc, current) => acc + current.qty, 0);
        const bottomBar = document.getElementById('cart-bottom-bar');
        const badgeQty = document.getElementById('cart-badge-qty');
        const badgeTotal = document.getElementById('cart-badge-total');
        const modalTotal = document.getElementById('cart-total');
        const cartList = document.getElementById('cart-items-list');
        const btnWhatsapp = document.getElementById('btn-whatsapp-order');

        let total = 0;
        let html = '';

        cart.forEach(item => {
            const sub = item.price * item.qty;
            total += sub;
            const subStr = sub.toLocaleString('pt-BR', {
                minimumFractionDigits: 2
            });
            html += `
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div style="flex:1;">
                        <div class="fw-bold text-dark mb-1" style="font-size:1.05rem;">${item.name}</div>
                        <div class="product-price">R$ ${subStr}</div>
                    </div>
                    <div class="qty-controls">
                        <button class="qty-btn" onclick="updateQty(${item.id}, -1)">-</button>
                        <span class="fw-bold px-2">${item.qty}</span>
                        <button class="qty-btn" onclick="updateQty(${item.id}, 1)">+</button>
                    </div>
                </div>
            `;
        });

        // Visibilidade da Bottom Bar
        if (count > 0) {
            bottomBar.classList.add('show');
            badgeQty.innerText = count + (count === 1 ? ' item' : ' itens');
            btnWhatsapp.disabled = false;
        } else {
            bottomBar.classList.remove('show');
            html = `
                <div class="text-center py-5">
                    <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                        <i class="ti tabler-shopping-bag text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-bold text-muted">Sua sacola está vazia</h5>
                    <p class="text-muted small">Adicione itens para fazer seu pedido.</p>
                </div>`;
            btnWhatsapp.disabled = true;

            // Fecha o modal do carrinho se esvaziar
            const modalEl = document.getElementById('modalCart');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal && modal._isShown) {
                modal.hide();
            }
        }

        const totalStr = 'R$ ' + total.toLocaleString('pt-BR', {
            minimumFractionDigits: 2
        });
        badgeTotal.innerText = totalStr;
        modalTotal.innerText = totalStr;
        cartList.innerHTML = html;
    }

    function showCart() {
        const modal = new bootstrap.Modal(document.getElementById('modalCart'));
        modal.show();
    }

    function sendOrderWhatsApp() {
        if (cart.length === 0) return;

        let total = 0;
        let text = `*NOVO PEDIDO - ${companyName}*\n\n`;

        cart.forEach(item => {
            const sub = item.price * item.qty;
            total += sub;
            text += `▪️ ${item.qty}x *${item.name}*\n`;
            if (item.qty > 1) {
                text += `   (R$ ${item.price.toLocaleString('pt-BR', {minimumFractionDigits: 2})} un.)\n`;
            }
        });

        const notes = document.getElementById('order-notes').value.trim();
        if (notes) {
            text += `\n*Observações:*\n_${notes}_\n`;
        }

        text += `\n*TOTAL A PAGAR: R$ ${total.toLocaleString('pt-BR', {minimumFractionDigits: 2})}*`;

        const url = `https://wa.me/${companyPhone}?text=${encodeURIComponent(text)}`;
        window.open(url, '_blank');
    }

    // Scroll Suave Sticky Nav e Intersection Observer para marcação
    document.querySelectorAll('.nav-pill').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');

            if (targetId === '#') {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            } else {
                const target = document.querySelector(targetId);
                if (target) {
                    const offset = 140; // Espaço do Header
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - offset;
                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // Observer para atualizar "pill" ativa no scroll
    const sections = document.querySelectorAll('.category-section');
    const navPills = document.querySelectorAll('.nav-pill');

    const observerOptions = {
        root: null,
        rootMargin: '-150px 0px -60% 0px',
        threshold: 0
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                navPills.forEach(pill => {
                    pill.classList.remove('active');
                    if (pill.getAttribute('href') === '#' + entry.target.id) {
                        pill.classList.add('active');
                        // Garante que a pill ativa esteja visível no scroll horizontal
                        pill.scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest',
                            inline: 'center'
                        });
                    }
                });
            }
        });
    }, observerOptions);

    sections.forEach(sec => observer.observe(sec));

    // Lógica de Busca
    function filterMenu() {
        const query = document.getElementById('menu-search').value.toLowerCase().trim();
        const allSections = document.querySelectorAll('.category-section');
        const noResults = document.getElementById('no-results');
        const navWrapper = document.querySelector('.category-nav-wrapper');
        let hasAnyMatch = false;

        // Se não há busca, restaura tudo
        if (query.length === 0) {
            allSections.forEach(section => {
                section.style.display = 'block';
                section.querySelectorAll('.product-card').forEach(card => {
                    card.style.display = 'flex';
                });
            });
            if (noResults) noResults.style.display = 'none';
            if (navWrapper) navWrapper.style.display = 'block';
            return;
        }

        // Oculta nav enquanto busca
        if (navWrapper) navWrapper.style.display = 'none';

        allSections.forEach(section => {
            let sectionHasMatch = false;
            const sectionCards = section.querySelectorAll('.product-card');

            sectionCards.forEach(card => {
                const nameEl = card.querySelector('.product-name');
                const descEl = card.querySelector('.product-desc');
                const name = nameEl ? nameEl.innerText.toLowerCase() : '';
                const desc = descEl ? descEl.innerText.toLowerCase() : '';

                if (name.includes(query) || desc.includes(query)) {
                    card.style.display = 'flex';
                    sectionHasMatch = true;
                    hasAnyMatch = true;
                } else {
                    card.style.display = 'none';
                }
            });

            section.style.display = sectionHasMatch ? 'block' : 'none';
        });

        if (noResults) {
            noResults.style.display = hasAnyMatch ? 'none' : 'block';
        }
    }
</script>
@endsection