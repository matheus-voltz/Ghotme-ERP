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

            $isOverdue = false;
            $overdueDays = 0;
            $isLocked = false;

            if ($user) {
            $isOverdue = $user->isPaymentOverdue();
            $overdueDays = $user->getOverdueDays();
            $isLocked = $user->isLockedDueToOverdue();

            if (!$isOverdue && ($user->plan === 'free' || empty($user->plan)) && $user->trial_ends_at) {
            $trialDaysLeft = (int)now()->diffInDays($user->trial_ends_at, false);
            if ($trialDaysLeft <= 7) {
              $showTrialBanner=true;
              if ($trialDaysLeft <=0) {
              $bannerClass='alert-danger' ;
              $bannerIcon='tabler-circle-x' ;
              }
              }
              }
              }
              @endphp

              @if($isOverdue && !$isLocked && request()->route()->getName() !== 'settings')
              <div class="alert alert-danger alert-dismissible d-flex align-items-center mb-6" role="alert">
                <span class="alert-icon rounded-circle p-1 me-3">
                  <i class="icon-base ti tabler-currency-dollar"></i>
                </span>
                <div class="d-flex flex-column">
                  <h6 class="alert-heading mb-1">Atraso de Pagamento Detectado</h6>
                  <span>Identificamos um atraso no seu pagamento há <strong>{{ $overdueDays }} {{ \Illuminate\Support\Str::plural('dia', $overdueDays) }}</strong>. Em {{ 3 - $overdueDays }} {{ \Illuminate\Support\Str::plural('dia', 3 - $overdueDays) }} o acesso ao sistema será interrompido. <a href="{{ route('settings') }}" class="fw-bold text-decoration-underline text-danger">Regularizar agora</a>.</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
              @elseif($showTrialBanner && !$isLocked)
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

              @if(isset($isLocked) && $isLocked && request()->route()->getName() !== 'settings')
              <div class="row pt-5">
                <div class="col-md-8 mx-auto text-center">
                  <div class="card p-5 border-danger border border-2 shadow-lg">
                    <div class="mb-4">
                      <i class="icon-base ti tabler-lock-square-rounded text-danger" style="font-size: 5rem;"></i>
                    </div>
                    <h3 class="mb-2 text-danger">Acesso Bloqueado</h3>
                    <p class="fs-5 mb-4">Sua assinatura está com uma pendência financeira que excedeu o prazo de 3 dias de cortesia. Para restaurar o acesso imediato de toda a sua equipe, por favor, regularize o pagamento.</p>
                    <a href="{{ route('settings') }}" class="btn btn-danger btn-lg"><i class="ti tabler-brand-stripe me-2"></i> Regularizar Pagamento e Desbloquear</a>
                  </div>
                </div>
              </div>
              @else
              @yield('content')
              @endif

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

    <!-- AI Consultant Floating Button -->
    @if(!isset($isPublic) || !$isPublic)
    <a href="{{ route('ai-consultant.index') }}" class="btn btn-primary btn-icon rounded-pill shadow-lg position-fixed d-flex align-items-center justify-content-center"
      style="bottom: 25px; right: 25px; width: 50px; height: 50px; z-index: 9999;"
      title="Consultor IA">
      <i class="ti tabler-robot fs-2"></i>
    </a>
    @endif


  </div>
  <!-- / Layout wrapper -->
  @endsection