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

    // Master check
    if (isset($menu->master_only) && $menu->master_only && !$user->is_master) {
    continue;
    }
    }
    @endphp

    {{-- menu headers --}}
    @if (isset($menu->menuHeader))
    <li class="menu-header small">
      <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
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