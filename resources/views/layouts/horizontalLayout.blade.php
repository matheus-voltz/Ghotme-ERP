@isset($pageConfigs)
  {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/commonMaster')
@php
  $menuHorizontal = true;
  $navbarFull = true;

  /* Display elements */
  $isNavbar = $isNavbar ?? true;
  $isMenu = $isMenu ?? true;
  $isFlex = $isFlex ?? false;
  $isFooter = $isFooter ?? true;
  $customizerHidden = $customizerHidden ?? '';

  /* HTML Classes */
  $menuFixed = isset($configData['menuFixed']) ? $configData['menuFixed'] : '';
  $navbarType = isset($configData['navbarType']) ? $configData['navbarType'] : '';
  $footerFixed = isset($configData['footerFixed']) ? $configData['footerFixed'] : '';
  $menuCollapsed = isset($configData['menuCollapsed']) ? $configData['menuCollapsed'] : '';

  /* Content classes */
  $container = $configData['contentLayout'] === 'compact' ? 'container-xxl' : 'container-fluid';
  $containerNav = $configData['contentLayout'] === 'compact' ? 'container-xxl' : 'container-fluid';

@endphp

@section('layoutContent')
  <div class="layout-wrapper layout-navbar-full layout-horizontal layout-without-menu">
    <div class="layout-container">

      <!-- BEGIN: Navbar-->
      @if ($isNavbar)
        @include('layouts/sections/navbar/navbar')
      @endif
      <!-- END: Navbar-->

      <!-- Layout page -->
      <div class="layout-page">
        {{-- Below commented code read by artisan command while installing jetstream. !! Do not remove if you want to use jetstream. --}}
        <x-banner />
        <!-- Content wrapper -->
        <div class="content-wrapper">
          @if ($isMenu)
            @include('layouts/sections/menu/horizontalMenu')
          @endif

          <!-- Content -->
          @if ($isFlex)
            <div class="{{ $container }} d-flex align-items-stretch flex-grow-1 p-0">
            @else
              <div class="{{ $container }} flex-grow-1 container-p-y">
          @endif

          @auth
            @if(auth()->user()->plan === 'free')
              @php
                $creationDate = auth()->user()->created_at;
                $trialEnds = auth()->user()->trial_ends_at ?? $creationDate->addDays(30);
                $isExpired = now()->greaterThan($trialEnds);
              @endphp

              @if($isExpired)
                <div class="alert alert-danger d-flex align-items-center mb-6" role="alert">
                  <span class="alert-icon rounded-circle bg-danger p-1 me-3">
                    <i class="icon-base ti tabler-alert-triangle text-white"></i>
                  </span>
                  <div class="d-flex flex-column">
                    <h6 class="alert-heading mb-1 text-danger">Seu período de teste grátis expirou!</h6>
                    <span>Acesse as <a href="{{ route('settings') }}" class="fw-bold text-danger text-decoration-underline">Configurações de Faturamento</a> para escolher um plano e continuar usando o sistema sem interrupções.</span>
                  </div>
                </div>
              @endif
            @endif
          @endauth

          @yield('content')

        </div>
        <!-- / Content -->

        <!-- Footer -->
        @if ($isFooter)
          @include('layouts/sections/footer/footer')
        @endif
        <!-- / Footer -->
        <div class="content-backdrop fade"></div>
      </div>
      <!--/ Content wrapper -->
    </div>
    <!-- / Layout page -->
  </div>
  <!-- / Layout Container -->

  @if ($isMenu)
    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
  @endif
  <!-- Drag Target Area To SlideIn Menu On Small Screens -->
  <div class="drag-target"></div>
  </div>
  <!-- / Layout wrapper -->
@endsection
