@isset($pageConfigs)
  {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
  $configData = Helper::appClasses();
@endphp
@extends('layouts/commonMaster')

@php
  /* Display elements */
  $contentNavbar = $contentNavbar ?? true;
  $containerNav = $containerNav ?? 'container-xxl';
  $isNavbar = $isNavbar ?? true;
  $isMenu = $isMenu ?? true;
  $isFlex = $isFlex ?? false;
  $isFooter = $isFooter ?? true;
  $customizerHidden = $customizerHidden ?? '';

  /* HTML Classes */
  $navbarDetached = 'navbar-detached';
  $menuFixed = isset($configData['menuFixed']) ? $configData['menuFixed'] : '';
  if (isset($navbarType)) {
      $configData['navbarType'] = $navbarType;
  }
  $navbarType = isset($configData['navbarType']) ? $configData['navbarType'] : '';
  $footerFixed = isset($configData['footerFixed']) ? $configData['footerFixed'] : '';
  $menuCollapsed = isset($configData['menuCollapsed']) ? $configData['menuCollapsed'] : '';

  /* Content classes */
  $container =
      isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact'
          ? 'container-xxl'
          : 'container-fluid';

@endphp

@section('layoutContent')
  <div class="layout-wrapper layout-content-navbar {{ $isMenu ? '' : 'layout-without-menu' }}">
    <div class="layout-container">

      @if ($isMenu)
        @include('layouts/sections/menu/verticalMenu')
      @endif

      <!-- Layout page -->
      <div class="layout-page">

        {{-- Below commented code read by artisan command while installing jetstream. !! Do not remove if you want to use jetstream. --}}
        <x-banner />

        <!-- BEGIN: Navbar-->
        @if ($isNavbar)
          @include('layouts/sections/navbar/navbar')
        @endif
        <!-- END: Navbar-->

        <!-- Content wrapper -->
        <div class="content-wrapper">

          <!-- Content -->
          @if ($isFlex)
            <div class="{{ $container }} d-flex align-items-stretch flex-grow-1 p-0">
            @else
              <div class="{{ $container }} flex-grow-1 container-p-y">
          @endif

          @auth
            @php
              $user = auth()->user();
              $showTrialBanner = false;
              $trialDaysLeft = 0;
              $bannerClass = 'alert-warning';
              $bannerIcon = 'tabler-alert-triangle';
              
              if ($user && ($user->plan === 'free' || empty($user->plan)) && $user->trial_ends_at) {
                  $trialDaysLeft = (int)now()->diffInDays($user->trial_ends_at, false);
                  if ($trialDaysLeft <= 7) {
                      $showTrialBanner = true;
                      if ($trialDaysLeft <= 0) {
                          $bannerClass = 'alert-danger';
                          $bannerIcon = 'tabler-circle-x';
                      }
                  }
              }
            @endphp

            @if($showTrialBanner)
              <div class="alert {{ $bannerClass }} alert-dismissible d-flex align-items-center mb-6" role="alert">
                <span class="alert-icon rounded-circle p-1 me-3">
                  <i class="icon-base ti {{ $bannerIcon }}"></i>
                </span>
                <div class="d-flex flex-column">
                  @if($trialDaysLeft > 0)
                    <h6 class="alert-heading mb-1">Atenção: Período de teste chegando ao fim!</h6>
                    <span>Faltam apenas <strong>{{ $trialDaysLeft }} {{ \Illuminate\Support\Str::plural('dia', $trialDaysLeft) }}</strong> para o bloqueio da sua conta. <a href="{{ route('settings') }}" class="fw-bold text-decoration-underline">Assine um plano agora</a> para manter seus dados ativos.</span>
                  @else
                    <h6 class="alert-heading mb-1">Seu período de teste expirou!</h6>
                    <span>Acesse as <a href="{{ route('settings') }}" class="fw-bold text-decoration-underline text-danger">Configurações de Faturamento</a> para regularizar sua conta e continuar usando o sistema.</span>
                  @endif
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
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

  @if ($isMenu)
    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
  @endif
  <!-- Drag Target Area To SlideIn Menu On Small Screens -->
  <div class="drag-target"></div>
  </div>
  <!-- / Layout wrapper -->
@endsection
