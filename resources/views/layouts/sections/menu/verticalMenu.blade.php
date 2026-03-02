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

  <ul class="menu-inner py-1">
    {{-- DEBUG TEMP --}}
    <li class="menu-item active bg-label-danger mb-2" style="display: block !important;">
        <div class="menu-link">
            <div class="text-white small">
                DEBUG: Co: {{ auth()->user()->company_id }} | 
                Niche: {{ auth()->user()->company->niche ?? 'NULO' }}
            </div>
        </div>
    </li>

    @foreach ($menuData[0]->menu as $menu)
    {{-- adding active and open class if child is active --}}

    {{-- plan and trial check --}}
    @php
    $user = auth()->user();

    // Se não houver usuário logado, não fazemos as checagens de trial/feature
    if (!$user) {
    $isExpired = false;
    } else {
    $isExpired = $user->isTrialExpired();

    // Se expirou e não for Dashboard ou Configurações, pula a renderização
    if ($isExpired && !in_array($menu->slug, ['dashboard', 'settings'])) {
    continue;
    }

    // Feature check normal
    if (isset($menu->feature) && !$user->hasFeature($menu->feature)) {
    continue;
    }

    // Niche check (Inclusão)
    if (isset($menu->niche) && ($user->company->niche ?? null) !== $menu->niche) {
    continue;
    }

    // Niche check (Exclusão)
    if (isset($menu->niche_exclude) && ($user->company->niche ?? null) === $menu->niche_exclude) {
    continue;
    }

    // Master check (REGRAS DE SEPARAÇÃO TOTAL)
    if ($user->is_master) {
        // Se eu sou MASTER, eu SÓ vejo o que for master_only
        if (!isset($menu->master_only) || !$menu->master_only) {
            continue;
        }
    } else {
        // Se eu NÃO SOU master, eu NUNCA vejo o que for master_only
        if (isset($menu->master_only) && $menu->master_only) {
            continue;
        }

        // REGRAS PARA FUNCIONÁRIOS
        if ($user->role === 'funcionario') {
            $restrictedSlugs = ['financial', 'reports', 'settings'];
            if (in_array($menu->slug, $restrictedSlugs)) {
                continue;
            }
        }

    // REGRAS ESPECÍFICAS POR NICHO (FOOD SERVICE)
    if (($user->company->niche ?? null) === 'food_service') {
        // Debug: descomente se precisar ver no HTML
        // echo "<!-- DEBUG: NICHE=food_service | SLUG=" . ($menu->slug ?? 'NONE') . " -->";

        // Esconde Orçamentos (slug: budgets)
        if (($menu->slug ?? '') === 'budgets' || ($menu->name ?? '') === 'Budgets') {
            continue;
        }
    }
    }
    }
    @endphp

    {{-- menu headers --}}
    @if (isset($menu->menuHeader))
    @php
        $headerText = __($menu->menuHeader);
        if ($user && ($user->company->niche ?? null) === 'food_service') {
            if ($headerText === 'Oficina' || $headerText === 'Operacional') {
                $headerText = 'Restaurante / Vendas';
            }
        }
    @endphp
    <li class="menu-header small">
      <span class="menu-header-text">{{ $headerText }}</span>
    </li>
    @else
    {{-- active menu method --}}
    @php
    $activeClass = null;
    $currentRouteName = Route::currentRouteName();

    if ($currentRouteName === $menu->slug) {
    $activeClass = 'active';
    } elseif (isset($menu->submenu)) {
    if (gettype($menu->slug) === 'array') {
    foreach ($menu->slug as $slug) {
    $slug = niche_translate($slug);
    if (str_contains($currentRouteName, $slug) and strpos($currentRouteName, $slug) === 0) {
    $activeClass = 'active open';
    }
    }
    } else {
    if (
    str_contains($currentRouteName, $menu->slug) and
    strpos($currentRouteName, $menu->slug) === 0
    ) {
    $activeClass = 'active open';
    }
    }
    }
    @endphp

    <li class="menu-item {{ $activeClass }}">
      @php
      $rawUrl = isset($menu->url) ? $menu->url : 'javascript:void(0);';
      $translatedUrl = niche_translate($rawUrl);
      $menuUrl = ($rawUrl !== 'javascript:void(0);') ? url($translatedUrl) : $translatedUrl;
      @endphp
      <a href="{{ $menuUrl }}"
        class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($menu->target) and
        !empty($menu->target)) target="_blank" @endif>
        @isset($menu->icon)
        <i class="{{ $menu->icon }}"></i>
        @endisset
        @php
        $menuName = isset($menu->name) ? __($menu->name) : '';
        
        // REGRA DE OURO: Renomear para Food Service
        if ($user && ($user->company->niche ?? null) === 'food_service') {
            if ($menuName === 'Service Orders' || $menuName === 'Ordens de Serviço' || ($menu->slug ?? '') === 'ordens') {
                $menuName = 'Pedidos';
            }
            if ($menuName === 'Entry Checklist' || str_contains($menuName, 'Checklist') || ($menu->slug ?? '') === 'ordens-servico.checklist') {
                $menuName = 'Ingredientes/Preparo';
            }
            if ($menuName === 'Budgets' || $menuName === 'Orçamentos' || ($menu->slug ?? '') === 'budgets') {
                $menuName = niche('budget_entities', 'Pré-pedidos');
            }
        }

        $menuName = niche_translate($menuName);
        @endphp
        <div>{{ $menuName }}</div>
        @isset($menu->badge)
        <div class="badge bg-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}</div>
        @endisset
      </a>

      {{-- submenu --}}
      @isset($menu->submenu)
      @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
      @endisset
    </li>
    @endif
    @endforeach
  </ul>

</aside>