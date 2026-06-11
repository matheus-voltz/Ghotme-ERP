<!DOCTYPE html>
@php
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use App\Helpers\Helpers;

$menuFixed =
$configData['layout'] === 'vertical'
? $menuFixed ?? ''
: ($configData['layout'] === 'front'
? ''
: $configData['headerType']);
$navbarType =
$configData['layout'] === 'vertical'
? $configData['navbarType']
: ($configData['layout'] === 'front'
? 'layout-navbar-fixed'
: '');
$isFront = ($isFront ?? '') == true ? 'Front' : '';
$contentLayout = isset($container) ? ($container === 'container-xxl' ? 'layout-compact' : 'layout-wide') : '';

// Get skin name from configData - only applies to admin layouts
$isAdminLayout = !Str::contains($configData['layout'] ?? '', 'front');
$skinName = $isAdminLayout ? $configData['skinName'] ?? 'default' : 'default';

// Get semiDark value from configData - only applies to admin layouts
$semiDarkEnabled = $isAdminLayout && filter_var($configData['semiDark'] ?? false, FILTER_VALIDATE_BOOLEAN);

// Generate primary color CSS if color is set
$primaryColorCSS = '';
if (isset($configData['color']) && $configData['color']) {
$primaryColorCSS = Helpers::generatePrimaryColorCSS($configData['color']);
}

@endphp

@php
$currentNiche = get_current_niche();
@endphp
<html lang="{{ str_replace('_', '-', session()->get('locale') ?? app()->getLocale()) }}"
  class="{{ $navbarType ?? '' }} {{ $contentLayout ?? '' }} {{ $menuFixed ?? '' }} {{ $menuCollapsed ?? '' }} {{ $footerFixed ?? '' }} {{ $customizerHidden ?? '' }} niche-{{ $currentNiche }}"
  dir="{{ $configData['textDirection'] }}" data-skin="{{ $skinName }}" data-assets-path="{{ asset('/assets') . '/' }}"
  data-base-url="{{ url('/') }}" data-framework="laravel" data-template="{{ $configData['layout'] }}-menu-template"
  data-bs-theme="{{ $configData['theme'] }}" @if ($isAdminLayout && $semiDarkEnabled) data-semidark-menu="true" @endif>

<head>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-T38GGFME59"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-T38GGFME59');
  </script>

  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=5.0" />

  <title>
    @yield('title') | {{ config('variables.templateName') ? config('variables.templateName') : 'TemplateName' }}
    - {{ config('variables.templateSuffix') ? config('variables.templateSuffix') : 'TemplateSuffix' }}
  </title>
  <meta name="description"
    content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
  <meta name="keywords"
    content="{{ config('variables.templateKeyword') ? config('variables.templateKeyword') : '' }}" />
  <meta property="og:title" content="{{ config('variables.ogTitle') ? config('variables.ogTitle') : '' }}" />
  <meta property="og:type" content="{{ config('variables.ogType') ? config('variables.ogType') : '' }}" />
  <meta property="og:url" content="{{ config('variables.productPage') ? config('variables.productPage') : '' }}" />
  <meta property="og:image" content="{{ config('variables.ogImage') ? config('variables.ogImage') : '' }}" />
  <meta property="og:description"
    content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
  <meta property="og:site_name"
    content="{{ config('variables.creatorName') ? config('variables.creatorName') : '' }}" />
  <meta name="robots" content="index, follow" />
  <!-- laravel CRUD token -->
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  @auth
  <meta name="user-id" content="{{ auth()->id() }}" />
  @endauth
  <!-- Canonical SEO -->
  <link rel="canonical" href="{{ config('variables.productPage') ? config('variables.productPage') : '' }}" />
  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

  <!-- Include Styles -->
  <!-- $isFront is used to append the front layout styles only on the front layout otherwise the variable will be blank -->
  @include('layouts/sections/styles' . $isFront)

  @if (
  $primaryColorCSS &&
  (config('custom.custom.primaryColor') ||
  isset($_COOKIE['admin-primaryColor']) ||
  isset($_COOKIE['front-primaryColor'])))
  <!-- Primary Color Style -->
  <style id="primary-color-style">
    {
      ! ! $primaryColorCSS ! !
    }
  </style>
  @endif

  <!-- Include Scripts for customizer, helper, analytics, config -->
  <!-- $isFront is used to append the front layout scriptsIncludes only on the front layout otherwise the variable will be blank -->
  @include('layouts/sections/scriptsIncludes' . $isFront)

  <style>
    /* Global Premium UI/UX Adjustments */
    .shadow-premium {
      box-shadow: 0 4px 24px 0 rgba(34, 41, 47, 0.08) !important;
    }

    .card {
      border-radius: 16px !important;
      transition: all 0.25s ease;
      border: none !important;
    }

    .card-hover:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 30px 0 rgba(34, 41, 47, 0.12) !important;
    }

    .btn {
      border-radius: 12px !important;
    }

    .table-hover tbody tr {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      border-radius: 8px;
    }

    .table-hover tbody tr:hover {
      transform: translateY(-2px);
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
      background-color: rgba(115, 103, 240, 0.04) !important;
      z-index: 10;
      position: relative;
    }

    .badge-soft {
      border-radius: 8px;
      padding: 0.5em 0.8em;
      font-weight: 600;
    }

    /* =============================================
       WCAG AA Contrast Fixes
       ============================================= */

    /* bg-label-primary badges: aumenta contraste do texto */
    .bg-label-primary {
      color: #4a3aad !important; /* era ~#7367f0 (razão ~2.8:1) → agora ~4.8:1 */
    }

    /* text-info: azul claro → azul acessível */
    .text-info {
      color: #0a7abf !important; /* razão 4.6:1 sobre branco */
    }

    /* btn-outline-secondary: texto/borda mais escuro */
    .btn-outline-secondary {
      color: #5a5f6e !important;
      border-color: #5a5f6e !important;
    }
    .btn-outline-secondary:hover {
      color: #fff !important;
      background-color: #5a5f6e !important;
    }

    /* text-body-secondary (/mês, subtítulos): garante contraste mínimo */
    .text-body-secondary {
      color: #6c757d !important; /* Bootstrap padrão, mas garante aplicação */
    }

    /* opacity-75 em texto: remove opacidade que reduz contraste */
    .landing-app h5.opacity-75,
    section h5.opacity-75 {
      opacity: 1 !important;
    }

    /* Bloco de código API: cores dos spans dentro do card-body dark */
    .card-body.font-monospace span[style*="color: #808080"] {
      color: #a0a0a0 !important; /* cinza claro suficiente sobre #1e1f22 */
    }
    .card-body.font-monospace span[style*="color: #6a8759"] {
      color: #89c07a !important; /* verde mais claro */
    }
    .card-body.font-monospace span[style*="color: #9876aa"] {
      color: #c49edb !important; /* roxo mais claro */
    }

    /* bg-label-secondary (cards "Sem Ghotme"): texto mais escuro */
    .bg-label-secondary {
      color: #444 !important;
    }
  </style>
</head>

<body>
  <!-- Layout Content -->
  <main id="main-content">
    @yield('layoutContent')
  </main>
  <!--/ Layout Content -->

  {{-- remove while creating package --}}
  {{-- remove while creating package end --}}

  <!-- Include Scripts -->
  <!-- $isFront is used to append the front layout scripts only on the front layout otherwise the variable will be blank -->
  @include('layouts/sections/scripts' . $isFront)

  @auth
  @if(!Route::is('customer.portal.*') && !Route::is('public.*'))
  @livewire('ai-support-chat')
  @endif
  @endauth
</body>

</html>