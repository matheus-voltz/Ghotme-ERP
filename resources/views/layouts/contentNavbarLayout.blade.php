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

    <!-- Ghotme Academy Floating Button -->
    <button class="btn btn-primary btn-icon rounded-pill shadow-lg position-fixed"
      style="bottom: 25px; right: 25px; width: 50px; height: 50px; z-index: 9999;"
      data-bs-toggle="modal" data-bs-target="#academyModal" title="Ghotme Academy">
      <i class="ti tabler-help fs-2"></i>
    </button>

    <!-- Academy Modal -->
    <div class="modal fade" id="academyModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-label-primary p-4">
            <h5 class="modal-title d-flex align-items-center"><i class="ti tabler-school me-2 fs-2"></i> Ghotme Academy</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-0">
            <div class="p-4 border-bottom">
              <div class="input-group input-group-merge shadow-none">
                <span class="input-group-text border-0 ps-0" id="academy-search-addon"><i class="ti tabler-search fs-4"></i></span>
                <input type="text" id="academySearch" class="form-control border-0 ps-2" placeholder="O que você deseja aprender hoje?" aria-label="Search" aria-describedby="academy-search-addon">
              </div>
            </div>
            <div id="academyContent" class="p-4" style="max-height: 450px; overflow-y: auto;">
              <!-- Conteúdo carregado via JS -->
              <div class="text-center py-5">
                <div class="spinner-border text-primary"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const academyModal = document.getElementById('academyModal');
        const academySearch = document.getElementById('academySearch');
        const academyContent = document.getElementById('academyContent');

        function loadTutorials(query = '') {
          fetch(`/api/academy?search=${query}`)
            .then(res => res.json())
            .then(data => {
              if (data.length === 0) {
                academyContent.innerHTML = '<div class="text-center py-5"><p class="text-muted">Nenhum tutorial encontrado para sua busca.</p></div>';
                return;
              }
              academyContent.innerHTML = '<div class="row g-4">' + data.map(item => `
              <div class="col-md-6">
                <div class="card shadow-none border h-100">
                  <div class="ratio ratio-16x9 card-img-top">
                    <iframe src="${item.video_url}" title="YouTube video" allowfullscreen></iframe>
                  </div>
                  <div class="card-body p-3">
                    <span class="badge bg-label-primary mb-2">${item.category}</span>
                    <h6 class="card-title mb-1">${item.title}</h6>
                    <p class="card-text small text-muted mb-0">${item.description}</p>
                  </div>
                </div>
              </div>
            `).join('') + '</div>';
            });
        }

        academyModal.addEventListener('shown.bs.modal', () => loadTutorials());

        let timeout = null;
        academySearch.addEventListener('input', (e) => {
          clearTimeout(timeout);
          timeout = setTimeout(() => loadTutorials(e.target.value), 500);
        });
      });
    </script>
  </div>
  <!-- / Layout wrapper -->
  @endsection