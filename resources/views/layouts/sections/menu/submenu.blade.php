@php
use Illuminate\Support\Facades\Route;
@endphp

<ul class="menu-sub">
  @if (isset($menu))
  @foreach ($menu as $submenu)

  @php
  $user = auth()->user();
  @endphp

  {{-- niche check --}}
  @php
  $currentNiche = get_current_niche();
  if (isset($submenu->niche) && $currentNiche !== $submenu->niche) continue;
  if (isset($submenu->niche_exclude) && $currentNiche === $submenu->niche_exclude) continue;
  @endphp

  {{-- feature plan check --}}
  @if (isset($submenu->feature))
  @if (!$user || !$user->hasFeature($submenu->feature))
  @continue
  @endif
  @endif

  @php
  $activeClass = null;
  $currentRouteName = Route::currentRouteName();

  $isSubmenuSelected = function($item) use ($currentRouteName, &$isSubmenuSelected) {
  if (isset($item->slug)) {
  $slugs = is_array($item->slug) ? $item->slug : [$item->slug];
  foreach ($slugs as $s) {
  $translatedS = niche_translate($s);
  if ($currentRouteName === $translatedS || $currentRouteName === $translatedS . '.index' || (str_contains($currentRouteName, $translatedS) && strpos($currentRouteName, $translatedS) === 0)) {
  return true;
  }
  }
  }
  if (isset($item->submenu)) {
  foreach ($item->submenu as $sub) {
  if ($isSubmenuSelected($sub)) return true;
  }
  }
  return false;
  };

  if ($isSubmenuSelected($submenu)) {
  $activeClass = isset($submenu->submenu) ? ($configData["layout"] === 'vertical' ? 'active open' : 'active') : 'active';
  }
  @endphp

  <li class="menu-item {{$activeClass}}">
    @php
    $rawSubUrl = isset($submenu->url) ? $submenu->url : 'javascript:void(0)';
    $translatedSubUrl = niche_translate($rawSubUrl);
    $submenuUrl = ($rawSubUrl !== 'javascript:void(0)') ? url($translatedSubUrl) : $translatedSubUrl;
    @endphp
    <a href="{{ $submenuUrl }}" class="{{ isset($submenu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($submenu->target) and !empty($submenu->target)) target="_blank" @endif>
      @if (isset($submenu->icon))
      <i class="{{ $submenu->icon }}"></i>
      @endif
      @php
      $submenuName = isset($submenu->name) ? __($submenu->name) : '';
      $submenuName = niche_translate($submenuName);
      @endphp
      <div>{{ $submenuName }}</div>
      @isset($submenu->badge)
      <div class="badge bg-{{ $submenu->badge[0] }} rounded-pill ms-auto">{{ $submenu->badge[1] }}</div>
      @endisset
    </a>

    {{-- submenu --}}
    @if (isset($submenu->submenu))
    @include('layouts.sections.menu.submenu',['menu' => $submenu->submenu])
    @endif
  </li>
  @endforeach
  @endif
</ul>