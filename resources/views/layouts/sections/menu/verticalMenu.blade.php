@php
use Illuminate\Support\Facades\Route;
$configData = Helper::appClasses();
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu" @foreach ($configData['menuAttributes'] as $attribute=>
    $value)
    {{ $attribute }}="{{ $value }}" @endforeach>

    <!-- ! Hide app brand if navbar-full -->
    @if (!isset($navbarFull))
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">@include('_partials.macros')</span>
            <span class="app-brand-text demo menu-text fw-bold ms-3">{{ config('variables.templateName') }}</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
            <i class="icon-base ti tabler-x d-block d-xl-none"></i>
        </a>
    </div>
    @endif

    <div class="menu-inner-shadow"></div>

    <style>
        /* Estilo Premium Roxo para Item Ativo */
        .menu-vertical .menu-item.active>.menu-link {
            background: linear-gradient(72.47deg, #7367f0 22.16%, rgba(115, 103, 240, 0.7) 76.47%) !important;
            color: #fff !important;
            box-shadow: 0px 2px 6px 0px rgba(115, 103, 240, 0.48) !important;
        }

        .menu-vertical .menu-item.active>.menu-link i,
        .menu-vertical .menu-item.active>.menu-link div {
            color: #fff !important;
        }

        /* Garantir que o dropdown pai fique com a seta correta quando aberto */
        .menu-vertical .menu-item.open>.menu-link.menu-toggle::after {
            transform: translateY(-50%) rotate(90deg);
        }
    </style>

    <ul class="menu-inner py-1">
        @foreach ($menuData[0]->menu ?? [] as $menu)
        {{-- plan and trial check --}}
        @php
        $user = auth()->user();
        $currentNiche = get_current_niche();

        if ($user) {
        $isExpired = $user->isTrialExpired();

        // Trial check
        if ($isExpired && !in_array($menu->slug, ['dashboard'])) {
        continue;
        }

        // Feature check
        if (isset($menu->feature) && !$user->hasFeature($menu->feature)) {
        continue;
        }

        // Niche Inclusion Check (From JSON)
        if (isset($menu->niche) && $currentNiche !== $menu->niche) {
        continue;
        }

        // Niche Exclusion Check (From JSON)
        if (isset($menu->niche_exclude) && $currentNiche === $menu->niche_exclude) {
        continue;
        }

        // Master check rules
        if ($user->is_master) {
        if (!isset($menu->master_only) || !$menu->master_only) continue;
        } else {
        if (isset($menu->master_only) && $menu->master_only) continue;

        // Employee view restrictions
        if ($user->role === 'funcionario') {
        if (in_array($menu->slug, ['financial', 'reports', 'settings'])) continue;
        }
        }
        }
        @endphp

        {{-- menu headers --}}
        @if (isset($menu->menuHeader))
        @php
        $headerText = __($menu->menuHeader);
        $headerClass = '';
        if ($currentNiche === 'food_service') {
        if ($headerText === 'Oficina' || $headerText === 'Operacional') {
        $headerText = '👨‍🍳 Cozinha e Vendas';
        $headerClass = 'text-warning fw-bold';
        }
        if ($headerText === 'Serviços') {
        $headerText = '📋 Cardápio';
        $headerClass = 'text-info fw-bold';
        }
        }
        @endphp
        <li class="menu-header small">
            <span class="menu-header-text {{ $headerClass }}">{{ $headerText }}</span>
        </li>
        @else
        @php
        $activeClass = null;
        $currentRouteName = Route::currentRouteName();

        // Função recursiva para verificar se o menu ou qualquer submenu está ativo
        $isMenuSelected = function($menuItem) use ($currentRouteName, &$isMenuSelected) {
        if (isset($menuItem->slug)) {
        if (is_array($menuItem->slug)) {
        foreach ($menuItem->slug as $s) {
        if ($currentRouteName === $s || (str_contains($currentRouteName, $s) && strpos($currentRouteName, $s) === 0)) return true;
        }
        } else {
        if ($currentRouteName === $menuItem->slug || (str_contains($currentRouteName, $menuItem->slug) && strpos($currentRouteName, $menuItem->slug) === 0)) return true;
        }
        }
        if (isset($menuItem->submenu)) {
        foreach ($menuItem->submenu as $sub) {
        if ($isMenuSelected($sub)) return true;
        }
        }
        return false;
        };

        if ($isMenuSelected($menu)) {
        $activeClass = isset($menu->submenu) ? 'active open' : 'active';
        }
        @endphp

        <li class="menu-item {{ $activeClass }}">
            @php
            $rawUrl = isset($menu->url) ? $menu->url : 'javascript:void(0);';
            $translatedUrl = niche_translate($rawUrl);
            $menuUrl = ($rawUrl !== 'javascript:void(0);') ? url($translatedUrl) : $translatedUrl;
            @endphp
            <a href="{{ $menuUrl }}" class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($menu->target) && !empty($menu->target)) target="_blank" @endif>
                @isset($menu->icon)
                <i class="{{ $menu->icon }}"></i>
                @endisset
                @php
                $menuName = isset($menu->name) ? __($menu->name) : '';

                // Melhoria Visual Premium por Nicho
                if ($currentNiche === 'food_service') {
                if ($menuName === 'Service Orders' || $menuName === 'Ordens de Serviço' || ($menu->slug ?? '') === 'ordens') {
                $menuName = 'Painel de Pedidos';
                }
                if ($menuName === 'Entry Checklist' || str_contains($menuName, 'Checklist') || ($menu->slug ?? '') === 'ordens-servico.checklist') {
                $menuName = 'Personalizar Lanches';
                }
                }

                $menuName = niche_translate($menuName);
                @endphp
                <div>{{ $menuName }}</div>
                @isset($menu->badge)
                <div class="badge bg-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}</div>
                @endisset
            </a>

            @isset($menu->submenu)
            @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
            @endisset
        </li>
        @endif
        @endforeach
    </ul>

</aside>