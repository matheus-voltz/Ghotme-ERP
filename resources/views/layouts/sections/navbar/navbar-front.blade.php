@php
use Illuminate\Support\Facades\Route;
$currentRouteName = Route::currentRouteName();
$activeRoutes = ['front-pages-pricing', 'front-pages-payment', 'front-pages-checkout', 'front-pages-help-center'];
$activeClass = in_array($currentRouteName, $activeRoutes) ? 'active' : '';
@endphp
<!-- Navbar: Start -->
<nav class="layout-navbar shadow-none py-0">
  <div class="container">
    <div class="navbar navbar-expand-lg landing-navbar px-3 px-md-8">
      <!-- Menu logo wrapper: Start -->
      <div class="navbar-brand app-brand demo d-flex py-0 me-4 me-xl-8 ms-0">
        <!-- Mobile menu toggle: Start-->
        <button class="navbar-toggler border-0 px-0 me-4" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <i class="icon-base ti tabler-menu-2 icon-lg align-middle text-heading fw-medium"></i>
        </button>
        <!-- Mobile menu toggle: End-->
        <a href="javascript:;" class="app-brand-link">
          <span class="app-brand-logo demo">@include('_partials.macros')</span>
          <span class="app-brand-text demo menu-text fw-bold ms-2 ps-1">{{ config('variables.templateName') }}</span>
        </a>
      </div>
      <!-- Menu logo wrapper: End -->
      <!-- Menu wrapper: Start -->
      <div class="collapse navbar-collapse landing-nav-menu" id="navbarSupportedContent">
        <button class="navbar-toggler border-0 text-heading position-absolute end-0 top-0 scaleX-n1-rtl p-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <i class="icon-base ti tabler-x icon-lg"></i>
        </button>
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link fw-medium" href="#landingFeatures">{{ __('Features') }}</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="#landingPricing">{{ __('Pricing') }}</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="#landingFAQ">FAQ</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="#landingContact">{{ __('Contact') }}</a>
          </li>
        </ul>
      </div>
      <div class="landing-menu-overlay d-lg-none"></div>
      <!-- Menu wrapper: End -->
      <!-- Toolbar: Start -->
      <ul class="navbar-nav flex-row align-items-center ms-auto">
        
        <!-- Language -->
        <li class="nav-item dropdown me-2 me-xl-1">
          <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
            <i class="icon-base ti tabler-language icon-lg"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item {{ app()->getLocale() === 'pt_BR' ? 'active' : '' }}" href="{{ url('lang/pt_BR') }}">
                <span class="align-middle">Português (Brasil)</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}" href="{{ url('lang/en') }}">
                <span class="align-middle">English</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item {{ app()->getLocale() === 'es' ? 'active' : '' }}" href="{{ url('lang/es') }}">
                <span class="align-middle">Español</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item {{ app()->getLocale() === 'fr' ? 'active' : '' }}" href="{{ url('lang/fr') }}">
                <span class="align-middle">Français</span>
              </a>
            </li>
          </ul>
        </li>
        <!--/ Language -->

        @if ($configData['hasCustomizer'] == true)
        <!-- Style Switcher -->
        <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-1">
          <a class="nav-link dropdown-toggle hide-arrow" id="nav-theme" href="javascript:void(0);"
data-bs-toggle="dropdown">
            <i class="icon-base ti tabler-sun icon-lg theme-icon-active"></i>
            <span class="d-none ms-2" id="nav-theme-text">Toggle theme</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
            <li>
              <button type="button" class="dropdown-item align-items-center active" data-bs-theme-value="light"
aria-pressed="false">
                <span><i class="icon-base ti tabler-sun icon-md me-3" data-icon="sun"></i>Claro</span>
              </button>
            </li>
            <li>
              <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark"
aria-pressed="true">
                <span><i class="icon-base ti tabler-moon-stars icon-md me-3" data-icon="moon-stars"></i>Escuro</span>
              </button>
            </li>
            <li>
              <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system"
aria-pressed="false">
                <span><i class="icon-base ti tabler-device-desktop-analytics icon-md me-3"
data-icon="device-desktop-analytics"></i>Sistema</span>
              </button>
            </li>
          </ul>
        </li>
        <!-- / Style Switcher-->
        @endif

        <!-- navbar button: Start -->
        <li>
          @if (Route::has('login'))
            @auth
              <a href="{{ route('dashboard') }}" class="btn btn-primary"><span
                  class="icon-base ti tabler-layout-dashboard me-md-1"></span><span
                  class="d-none d-md-block">{{ __('Access Panel') }}</span></a>
            @else
              <a href="{{ route('login') }}" class="btn btn-label-primary me-2"><span
                  class="icon-base ti tabler-login me-md-1"></span><span
                  class="d-none d-md-block">{{ __('Login') }}</span></a>
              @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn btn-primary"><span
                    class="icon-base ti tabler-user-plus me-md-1"></span><span
                    class="d-none d-md-block">{{ __('Register') }}</span></a>
              @endif
            @endauth
          @endif
        </li>
        <!-- navbar button: End -->
      </ul>
      <!-- Toolbar: End -->
    </div>
  </div>
</nav>
<!-- Navbar: End -->
