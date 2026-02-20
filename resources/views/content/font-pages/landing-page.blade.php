@php
$configData = Helper::appClasses();
$isFront = true;
$pageConfigs = [
'myLayout' => 'front',
'myTheme' => 'light',
'customizerHidden' => true,
'displayCustomizer' => false
];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Ghotme - Gestão Empresarial Completa')

<!-- Vendor Styles -->
@section('vendor-style')
@vite(['resources/assets/vendor/libs/nouislider/nouislider.scss', 'resources/assets/vendor/libs/swiper/swiper.scss'])
@endsection

<!-- Page Styles -->
@section('page-style')
@vite(['resources/assets/vendor/scss/pages/front-page-landing.scss'])
<style>
  :root {
    --ghotme-primary: #7367f0;
    --ghotme-gradient: linear-gradient(135deg, #7367f0 0%, #4831d4 100%);
  }

  .hero-title {
    background: var(--ghotme-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    display: inline-block;
  }

  .niche-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(115, 103, 240, 0.1) !important;
  }

  .niche-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(115, 103, 240, 0.15);
    border-color: var(--ghotme-primary) !important;
  }

  .pricing-card-popular {
    position: relative;
    border: 2px solid var(--ghotme-primary) !important;
    transform: scale(1.05);
    z-index: 10;
  }

  .pricing-card-popular::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: var(--ghotme-gradient);
    z-index: -1;
    border-radius: 0.5rem;
    opacity: 0.1;
  }

  .btn-primary {
    background: var(--ghotme-gradient) !important;
    border: none !important;
    transition: all 0.3s ease;
  }

  .btn-primary:hover {
    transform: scale(1.05);
    box-shadow: 0 10px 20px rgba(115, 103, 240, 0.3);
  }

  .badge-niche {
    font-size: 0.75rem;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    margin-bottom: 1rem;
  }

  /* Animações dos Ícones dos Planos */
  @keyframes float {
    0% {
      transform: translateY(0px) rotate(0deg);
    }

    50% {
      transform: translateY(-10px) rotate(5deg);
    }

    100% {
      transform: translateY(0px) rotate(0deg);
    }
  }

  @keyframes propulsion {
    0% {
      transform: translateY(0px) rotate(0deg);
    }

    25% {
      transform: translateY(-5px) rotate(-2deg);
    }

    50% {
      transform: translateY(0px) rotate(0deg);
    }

    75% {
      transform: translateY(-5px) rotate(2deg);
    }

    100% {
      transform: translateY(0px) rotate(0deg);
    }
  }

  @keyframes rocketLaunch {
    0% {
      transform: translateY(0) scale(1);
      filter: drop-shadow(0 0 0px rgba(115, 103, 240, 0));
    }

    50% {
      transform: translateY(-15px) scale(1.1);
      filter: drop-shadow(0 15px 20px rgba(115, 103, 240, 0.4));
    }

    100% {
      transform: translateY(0) scale(1);
      filter: drop-shadow(0 0 0px rgba(115, 103, 240, 0));
    }
  }

  .animate-float {
    animation: float 4s ease-in-out infinite;
  }

  .animate-propulsion {
    animation: propulsion 2s ease-in-out infinite;
  }

  .animate-rocket {
    animation: rocketLaunch 3s ease-in-out infinite;
  }
</style>
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite(['resources/assets/vendor/libs/nouislider/nouislider.js', 'resources/assets/vendor/libs/swiper/swiper.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
@vite(['resources/assets/js/front-page-landing.js'])
<script>
  // Script de emergência injetado diretamente
  (function() {
    function init() {
      const toggler = document.querySelector('.price-duration-toggler');
      const menuBtn = document.querySelector('.navbar-toggler');
      const menuCollapse = document.getElementById('navbarSupportedContent');

      // Troca de Preços
      if (toggler) {
        toggler.addEventListener('change', function() {
          const isYearly = this.checked;
          document.querySelectorAll('.price-monthly').forEach(el => el.classList.toggle('d-none', isYearly));
          document.querySelectorAll('.price-yearly').forEach(el => el.classList.toggle('d-none', !isYearly));
          document.querySelectorAll('.plan-action-btn').forEach(btn => {
            const link = isYearly ? btn.getAttribute('data-yearly-link') : btn.getAttribute('data-monthly-link');
            if (link) btn.setAttribute('href', link);
          });
        });
      }

      // Menu Mobile (Correção para o erro de travamento)
      if (menuBtn && menuCollapse) {
        menuBtn.addEventListener('click', function() {
          menuCollapse.classList.toggle('show');
        });
      }
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', init);
    } else {
      init();
    }
  })();
</script>
@endsection


@section('content')
<div data-bs-spy="scroll" class="scrollspy-example">
  <!-- Hero: Start -->
  <section id="hero-animation">
    <div id="landingHero" class="section-py landing-hero position-relative">
      <img src="{{ asset('assets/img/front-pages/backgrounds/hero-bg.png') }}" alt="hero background"
        class="position-absolute top-0 start-50 translate-middle-x object-fit-cover w-100 h-100" data-speed="1" />
      <div class="container">
        <div class="hero-text-box text-center position-relative">
          <h1 class="text-primary hero-title display-6 fw-extrabold text-uppercase">{{ __('The ERP that shapes itself to your business') }}</h1>
          <h2 class="hero-sub-title h6 mb-6">
            {{ __('The only intelligent system that') }} <strong>{{ __('speaks your language') }}</strong>. <br class="d-none d-lg-block" />
            {{ __('Our technology adapts terms, icons, and processes instantly to the reality of your segment, whatever it may be.') }}
          </h2>
          <div class="landing-hero-btn d-inline-block position-relative">
            <span class="hero-btn-item position-absolute d-none d-md-flex fw-medium">{{ __('Discover our system') }} <img
                src="{{ asset('assets/img/front-pages/icons/Join-community-arrow.png') }}" alt="Join community arrow"
                class="scaleX-n1-rtl" /></span>
            <a href="#landingPricing" class="btn btn-primary btn-lg">{{ __('Start your free trial') }}</a>
          </div>
        </div>
        <div id="heroDashboardAnimation" class="hero-animation-img mt-12">
          <a href="{{ url('/dashboard') }}" target="_blank">
            <div id="heroAnimationImg" class="position-relative hero-dashboard-img">
              <img
                src="{{ asset('assets/img/front-pages/landing-page/meu-sistema-' . $configData['theme'] . '.png') }}"
                alt="Ghotme Dashboard" class="animation-img w-75 mx-auto d-block"
                data-app-light-img="front-pages/landing-page/meu-sistema-light.png"
                data-app-dark-img="front-pages/landing-page/meu-sistema-dark.png" />
            </div>
          </a>
        </div>
      </div>
    </div>
    <div class="landing-hero-blank"></div>
  </section>
  <!-- Hero: End -->

  <!-- Useful features: Start -->
  <section id="landingFeatures" class="section-py landing-features">
    <div class="container">
      <div class="text-center mb-4">
        <span class="badge bg-label-primary">{{ __('Key Features') }}</span>
      </div>
      <h4 class="text-center mb-1">
        <span class="position-relative fw-extrabold z-1">{{ __('Everything you need') }}
          <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}" alt="section title icon"
            class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
        </span>
        {{ __('to manage your business') }}
      </h4>
      <p class="text-center mb-12">{{ __('Powerful tools for service companies, retail and self-employed providers.') }}</p>
      <div class="features-icon-wrapper row gx-0 gy-6 g-sm-12">
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="mb-4 text-primary text-center">
            <i class="ti tabler-rocket icon-xl mb-3"></i>
          </div>
          <h5 class="mb-2">{{ __('Operational Agility') }}</h5>
          <p class="features-icon-description">{{ __('Open Service Orders in seconds and track status in real-time by mobile.') }}</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="mb-4 text-primary text-center">
            <i class="ti tabler-shield-check icon-xl mb-3"></i>
          </div>
          <h5 class="mb-2">{{ __('Intelligent Inventory') }}</h5>
          <p class="features-icon-description">{{ __('Avoid losses with minimum stock alerts and automatic purchase orders by supplier.') }}</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="mb-4 text-primary text-center">
            <i class="ti tabler-chart-arrows icon-xl mb-3"></i>
          </div>
          <h5 class="mb-2">{{ __('Real Profitability') }}</h5>
          <p class="features-icon-description">{{ __('Know exactly how much you earned on each service, deducting the real cost of parts and supplies.') }}</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="mb-4 text-primary text-center">
            <i class="ti tabler-receipt-tax icon-xl mb-3"></i>
          </div>
          <h5 class="mb-2">{{ __('Fiscal and Accounting without Fear') }}</h5>
          <p class="features-icon-description">{{ __('Integrated Tax Invoice emission (NFe/NFSe) and exclusive portal for your accountant.') }}</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="mb-4 text-primary text-center">
            <i class="ti tabler-device-mobile-message icon-xl mb-3"></i>
          </div>
          <h5 class="mb-2">{{ __('WhatsApp as an Ally') }}</h5>
          <p class="features-icon-description">{{ __('Send budgets for approval and "service ready" notices automatically via WhatsApp.') }}</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="mb-4 text-primary text-center">
            <i class="ti tabler-building-bank icon-xl mb-3"></i>
          </div>
          <h5 class="mb-2">{{ __('Bank Reconciliation') }}</h5>
          <p class="features-icon-description">{{ __('Import your OFX file and let Ghotme match bank balances with your financials.') }}</p>
        </div>
      </div>
    </div>
  </section>
  <!-- Useful features: End -->

  <!-- Multi-Niche: Start -->
  <section id="landingNiches" class="section-py bg-body">
    <div class="container">
      <div class="text-center mb-4">
        <span class="badge bg-label-primary">{{ __('Made for you') }}</span>
      </div>
      <h4 class="text-center mb-1">
        {{ __('One system') }}, <span class="fw-extrabold">{{ __('infinite possibilities') }}</span>
      </h4>
      <p class="text-center mb-12">{{ __('See how Ghotme transforms to meet the specific needs of each market:') }}</p>
      <div class="row g-6">
        <div class="col-md-3">
          <div class="card niche-card shadow-sm text-center p-5 h-100">
            <div class="avatar avatar-xl mx-auto mb-4 bg-label-info rounded-circle">
              <i class="ti tabler-settings-automation fs-1"></i>
            </div>
            <h5 class="fw-extrabold mb-3">{{ __('Automotive Segment') }}</h5>
            <p class="text-muted small mb-0">{{ __('Management of plates, chassis and history by vehicles for Workshops and Aesthetics.') }}</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card niche-card shadow-sm text-center p-5 h-100">
            <div class="avatar avatar-xl mx-auto mb-4 bg-label-success rounded-circle">
              <i class="ti tabler-heart-handshake fs-1"></i>
            </div>
            <h5 class="fw-extrabold mb-3">{{ __('Health and Well-being') }}</h5>
            <p class="text-muted small mb-0">{{ __('Dynamic labels for Pets, Patients and session control for Clinics and Pet Shops.') }}</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card niche-card shadow-sm text-center p-5 h-100">
            <div class="avatar avatar-xl mx-auto mb-4 bg-label-warning rounded-circle">
              <i class="ti tabler-device-laptop fs-1"></i>
            </div>
            <h5 class="fw-extrabold mb-3">{{ __('Technology and Retail') }}</h5>
            <p class="text-muted small mb-0">{{ __('Focus on Serial, Model and warranty for Technical Assistance and Stores in general.') }}</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card niche-card shadow-sm text-center p-5 h-100">
            <div class="avatar avatar-xl mx-auto mb-4 bg-label-primary rounded-circle">
              <i class="ti tabler-building-skyscraper fs-1"></i>
            </div>
            <h5 class="fw-extrabold mb-3">{{ __('Construção e Engenharia') }}</h5>
            <p class="text-muted small mb-0">{{ __('Controle de obras, medições e emissão de RDO (Relatório Diário de Obra) em tempo real.') }}</p>
          </div>
        </div>
      </div>
      <div class="text-center mt-8">
        <p class="fw-medium text-primary">{{ __('And much more... Ghotme is configurable for any service provider!') }}</p>
      </div>
    </div>
  </section>
  <!-- Multi-Niche: End -->

  <!-- Business Highlights Carousel: Start -->
  <section id="landingBusinessHighlights" class="section-py bg-body landing-reviews pb-0">
    <div class="swiper-business-highlights overflow-hidden position-relative">
      <div class="swiper" id="swiper-business-highlights">
        <div class="swiper-wrapper">

          <!-- Slide 1: Transformation (Why Choose) -->
          <div class="swiper-slide">
            <div class="container h-100">
              <div class="mb-4 text-center">
                <span class="badge bg-label-primary">{{ __('The Transformation') }}</span>
              </div>
              <h4 class="text-center mb-1">
                <span class="position-relative fw-extrabold z-1">{{ __('Why choose') }}
                  <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}"
                    alt="section title icon"
                    class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
                </span>
                {{ __('Ghotme?') }}
              </h4>
              <p class="text-center mb-5 text-body">
                {{ __('See the difference an integrated management system makes in your daily life.') }}
              </p>
              <div class="row g-4 justify-content-center align-items-center mb-5">
                <div class="col-lg-5">
                  <div class="card border-0 shadow-none bg-label-secondary h-100 opacity-75 grayscale-content">
                    <div class="card-body text-center p-5">
                      <div class="mb-4">
                        <div class="avatar avatar-xl mx-auto">
                          <span class="avatar-initial rounded bg-label-danger">
                            <i class="ti tabler-x icon-xl"></i>
                          </span>
                        </div>
                      </div>
                      <h4 class="mb-3 text-danger">{{ __('Without Ghotme') }}</h4>
                      <ul class="list-unstyled text-start d-inline-block mx-auto">
                        <li class="mb-3 d-flex align-items-center">
                          <i class="ti tabler-file-spreadsheet text-danger me-2"></i> {{ __('Scattered and confusing spreadsheets') }}
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                          <i class="ti tabler-clock-off text-danger me-2"></i> {{ __('Wasted time with manual tasks') }}
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                          <i class="ti tabler-chart-arrows-vertical text-danger me-2"></i> {{ __('Lack of financial control') }}
                        </li>
                        <li class="d-flex align-items-center">
                          <i class="ti tabler-mood-sad text-danger me-2"></i> {{ __('Stress and insecurity in decisions') }}
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="col-lg-1 d-none d-lg-flex justify-content-center">
                  <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-lg"
                    style="width: 60px; height: 60px; z-index: 1;">
                    <span class="fw-bold">VS</span>
                  </div>
                </div>
                <div class="col-lg-5">
                  <div class="card border-primary shadow-lg h-100 hover-shadow-xl transition-all scale-up-center">
                    <div class="card-body text-center p-5">
                      <div class="mb-4">
                        <div class="avatar avatar-xl mx-auto">
                          <span class="avatar-initial rounded bg-label-primary">
                            <i class="ti tabler-check icon-xl"></i>
                          </span>
                        </div>
                      </div>
                      <h4 class="mb-3 text-primary">{{ __('With Ghotme') }}</h4>
                      <ul class="list-unstyled text-start d-inline-block mx-auto">
                        <li class="mb-3 d-flex align-items-center">
                          <i class="ti tabler-layout-dashboard text-success me-2"></i> {{ __('Everything organized in one place') }}
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                          <i class="ti tabler-rocket text-success me-2"></i> {{ __('Automation that saves hours of your day') }}
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                          <i class="ti tabler-chart-line text-success me-2"></i> {{ __('Clear view of profits and expenses') }}
                        </li>
                        <li class="d-flex align-items-center">
                          <i class="ti tabler-mood-smile text-success me-2"></i> {{ __('Peace of mind to focus on growing') }}
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Slide 2: Ideal For -->
          <div class="swiper-slide">
            <div class="container h-100">
              <div class="mb-4 text-center">
                <span class="badge bg-label-primary">{{ __('Target Audience') }}</span>
              </div>
              <h4 class="text-center mb-1">
                <span class="position-relative fw-extrabold z-1">{{ __('Made for') }}
                  <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}"
                    alt="section title icon"
                    class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
                </span>
                {{ __('your Business') }}
              </h4>
              <p class="text-center mb-5 text-body">
                {{ __('The flexible system that adapts to your reality.') }}
              </p>
              <div class="row gy-6 mb-5">
                <div class="col-lg-4 col-sm-6">
                  <div class="card h-100 border-0 shadow-sm hover-shadow-lg transition-all">
                    <div class="card-body text-center">
                      <div class="mb-3">
                        <div class="avatar avatar-xl mx-auto">
                          <span class="avatar-initial rounded bg-label-info">
                            <i class="ti tabler-tools icon-xl"></i>
                          </span>
                        </div>
                      </div>
                      <h5 class="mb-2">{{ __('Service Providers') }}</h5>
                      <p class="mb-0">{{ __('Workshops, technical assistance, clinics and consultancies. Organize your schedule and service orders.') }}</p>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4 col-sm-6">
                  <div class="card h-100 border-0 shadow-sm hover-shadow-lg transition-all">
                    <div class="card-body text-center">
                      <div class="mb-3">
                        <div class="avatar avatar-xl mx-auto">
                          <span class="avatar-initial rounded bg-label-warning">
                            <i class="ti tabler-building-store icon-xl"></i>
                          </span>
                        </div>
                      </div>
                      <h5 class="mb-2">{{ __('Commerce and Retail') }}</h5>
                      <p class="mb-0">{{ __('Clothing stores, markets and auto parts. Control your stock and sell quickly at the counter.') }}
                      </p>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4 col-sm-6">
                  <div class="card h-100 border-0 shadow-sm hover-shadow-lg transition-all">
                    <div class="card-body text-center">
                      <div class="mb-3">
                        <div class="avatar avatar-xl mx-auto">
                          <span class="avatar-initial rounded bg-label-primary">
                            <i class="ti tabler-briefcase icon-xl"></i>
                          </span>
                        </div>
                      </div>
                      <h5 class="mb-2">{{ __('Independent Professionals') }}</h5>
                      <p class="mb-0">{{ __('Lawyers, architects and freelancers. Simplify your financials and issue tax invoices in seconds.') }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Slide 3: Integrations -->
          <div class="swiper-slide">
            <div class="container h-100">
              <div class="mb-4 text-center">
                <span class="badge bg-label-primary">{{ __('Connectivity') }}</span>
              </div>
              <h4 class="text-center mb-1">
                <span class="position-relative fw-extrabold z-1">{{ __('Integrations that') }}
                  <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}"
                    alt="section title icon"
                    class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
                </span>
                {{ __('make your life easier') }}
              </h4>
              <p class="text-center mb-5 text-body">
                {{ __('Centralize your operation by connecting the tools you already use.') }}
              </p>
              <div class="row gy-6 mb-5">
                <div class="col-lg-4 col-sm-6">
                  <div class="card h-100 border-0 shadow-sm hover-shadow-lg transition-all">
                    <div class="card-body text-center">
                      <div class="mb-3">
                        <div class="avatar avatar-xl mx-auto">
                          <span class="avatar-initial rounded bg-label-success">
                            <i class="ti tabler-brand-whatsapp icon-xl"></i>
                          </span>
                        </div>
                      </div>
                      <h5 class="mb-2">{{ __('Integrated WhatsApp') }}</h5>
                      <p class="mb-0">{{ __('Send budgets, schedule reminders and service status automatically to your customers WhatsApp.') }}</p>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4 col-sm-6">
                  <div class="card h-100 border-0 shadow-sm hover-shadow-lg transition-all">
                    <div class="card-body text-center">
                      <div class="mb-3">
                        <div class="avatar avatar-xl mx-auto">
                          <span class="avatar-initial rounded bg-label-secondary">
                            <i class="ti tabler-file-certificate icon-xl"></i>
                          </span>
                        </div>
                      </div>
                      <h5 class="mb-2">{{ __('Tax Invoices (NFe/NFCe)') }}</h5>
                      <p class="mb-0">{{ __('Issue product and service tax invoices with a few clicks, integrated directly with Sefaz and city halls.') }}</p>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4 col-sm-6">
                  <div class="card h-100 border-0 shadow-sm hover-shadow-lg transition-all">
                    <div class="card-body text-center">
                      <div class="mb-3">
                        <div class="avatar avatar-xl mx-auto">
                          <span class="avatar-initial rounded bg-label-primary">
                            <i class="ti tabler-credit-card icon-xl"></i>
                          </span>
                        </div>
                      </div>
                      <h5 class="mb-2">{{ __('Payment Methods') }}</h5>
                      <p class="mb-0">{{ __('Generate registered bank slips, charges via PIX and automatic bank reconciliation with the main platforms.') }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Slide 4: Journey -->
          <div class="swiper-slide">
            <div class="container h-100">
              <div class="mb-4 text-center">
                <span class="badge bg-label-primary">{{ __('Step by Step') }}</span>
              </div>
              <h4 class="text-center mb-1">
                <span class="position-relative fw-extrabold z-1">{{ __('Your Journey') }}
                  <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}"
                    alt="section title icon"
                    class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
                </span>
                {{ __('to Success') }}
              </h4>
              <p class="text-center mb-5 text-body">
                {{ __('Starting to use Ghotme is simple, fast and without bureaucracy.') }}
              </p>
              <div class="row gy-4 justify-content-center mb-5">
                <div class="col-lg-3 col-md-6">
                  <div class="text-center position-relative">
                    <div class="avatar avatar-xl border border-primary border-2 rounded-circle mx-auto mb-3">
                      <span class="avatar-initial rounded-circle bg-primary text-white">1</span>
                    </div>
                    <h5 class="mb-2 text-heading">{{ __('Create your Account') }}</h5>
                    <p class="text-body">{{ __('Choose the ideal plan and register in less than 2 minutes.') }}</p>
                    <div class="d-none d-lg-block position-absolute start-100 top-0 translate-middle"
                      style="margin-top: 1.5rem; margin-left: -1rem;">
                      <i class="ti tabler-arrow-right text-muted icon-lg"></i>
                    </div>
                  </div>
                </div>
                <div class="col-lg-3 col-md-6">
                  <div class="text-center position-relative">
                    <div class="avatar avatar-xl border border-primary border-2 rounded-circle mx-auto mb-3">
                      <span class="avatar-initial rounded-circle bg-white text-primary">2</span>
                    </div>
                    <h5 class="mb-2 text-heading">{{ __('Easy Config') }}</h5>
                    <p class="text-body">{{ __('Register your data or import customers and products from spreadsheets.') }}</p>
                    <div class="d-none d-lg-block position-absolute start-100 top-0 translate-middle"
                      style="margin-top: 1.5rem; margin-left: -1rem;">
                      <i class="ti tabler-arrow-right text-muted icon-lg"></i>
                    </div>
                  </div>
                </div>
                <div class="col-lg-3 col-md-6">
                  <div class="text-center position-relative">
                    <div class="avatar avatar-xl border border-primary border-2 rounded-circle mx-auto mb-3">
                      <span class="avatar-initial rounded-circle bg-white text-primary">3</span>
                    </div>
                    <h5 class="mb-2 text-heading">{{ __('Manage Everything') }}</h5>
                    <p class="text-body">{{ __('Issue invoices, control inventory and finances in a single screen.') }}</p>
                    <div class="d-none d-lg-block position-absolute start-100 top-0 translate-middle"
                      style="margin-top: 1.5rem; margin-left: -1rem;">
                      <i class="ti tabler-arrow-right text-muted icon-lg"></i>
                    </div>
                  </div>
                </div>
                <div class="col-lg-3 col-md-6">
                  <div class="text-center position-relative">
                    <div class="avatar avatar-xl border border-primary border-2 rounded-circle mx-auto mb-3">
                      <span class="avatar-initial rounded-circle bg-primary text-white">4</span>
                    </div>
                    <h5 class="mb-2 text-heading">{{ __('See Results') }}</h5>
                    <p class="text-body">{{ __('Track growth reports and make intelligent decisions.') }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
      <div class="swiper-pagination"></div>
    </div>
</div>
</section>
<!-- Business Highlights Carousel: End -->

<!-- Payment Platforms: Start -->
<section id="landingPaymentPlatforms" class="section-py landing-payment-platforms">
  <div class="container">
    <div class="text-center mb-4">
      <span class="badge bg-label-primary">Pagamentos Integrados</span>
    </div>
    <h4 class="text-center mb-1">
      <span class="position-relative fw-extrabold z-1">Plataformas
        <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}" alt="section title icon"
          class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
      </span>
      de pagamento
    </h4>
    <p class="text-center mb-md-11 pb-0 pb-xl-12">O site integra com Pagar.me, Asaas, PagSeguro, Stripe e Bitcoin.</p>
    <div class="row gy-12 mt-2 justify-content-center align-items-center">
      <style>
        @keyframes floating {
          0% {
            transform: translateY(0px);
          }

          50% {
            transform: translateY(-10px);
          }

          100% {
            transform: translateY(0px);
          }
        }

        .partner-logo-container {
          transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
          filter: grayscale(100%);
          opacity: 0.6;
        }

        .partner-logo-container:hover {
          filter: grayscale(0%);
          opacity: 1;
          transform: scale(1.1);
          filter: drop-shadow(0 10px 15px rgba(0, 0, 0, 0.1));
        }

        .floating-animation {
          animation: floating 3s ease-in-out infinite;
        }

        .delay-1 {
          animation-delay: 0s;
        }

        .delay-2 {
          animation-delay: 0.5s;
        }

        .delay-3 {
          animation-delay: 1s;
        }

        .delay-4 {
          animation-delay: 1.5s;
        }
      </style>
      <div class="row gy-12 mt-2 justify-content-center align-items-center">
        <div class="col-lg-3 col-sm-6 text-center">
          <div class="p-4 rounded-3 shadow-none border border-transparent partner-logo-container floating-animation delay-1">
            <img src="{{ asset('assets/img/front-pages/partners/pagarme_hq.png') }}" alt="Pagar.me Logo" class="img-fluid" style="max-height: 40px;" />
          </div>
        </div>
        <div class="col-lg-3 col-sm-6 text-center">
          <div class="p-4 rounded-3 shadow-none border border-transparent partner-logo-container floating-animation delay-2">
            <img src="{{ asset('assets/img/front-pages/partners/asaas.png') }}" alt="Asaas Logo" class="img-fluid" style="max-height: 45px;" />
          </div>
        </div>
        <div class="col-lg-3 col-sm-6 text-center">
          <div class="p-4 rounded-3 shadow-none border border-transparent partner-logo-container floating-animation delay-3">
            <img src="{{ asset('assets/img/front-pages/partners/pagseguro_hq.png') }}" alt="PagSeguro Logo" class="img-fluid" style="max-height: 40px;" />
          </div>
        </div>
        <div class="col-lg-3 col-sm-6 text-center">
          <div class="p-4 rounded-3 shadow-none border border-transparent partner-logo-container floating-animation delay-4">
            <img src="{{ asset('assets/img/front-pages/partners/stripe_hq.png') }}" alt="Stripe Logo" class="img-fluid" style="max-height: 45px;" />
          </div>
        </div>
        <div class="col-lg-3 col-sm-6 text-center">
          <div class="p-4 rounded-3 shadow-none border border-transparent partner-logo-container floating-animation delay-1">
            <img src="{{ asset('assets/img/front-pages/partners/bitcoin_logo_full.svg') }}" alt="Bitcoin Logo" class="img-fluid" style="max-height: 70px;" />
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- Payment Platforms: End -->

<!-- Mobile App Section: Start -->
<section id="landingApp" class="section-py bg-body landing-app position-relative">
  <img src="{{ asset('assets/img/front-pages/backgrounds/hero-bg.png') }}" alt="hero background"
    class="position-absolute top-0 start-50 translate-middle-x object-fit-cover w-100 h-100 z-n1" style="opacity: 0.2;" />
  <div class="container">
    <div class="row align-items-center gy-6">
      <!-- Image Side -->
      <div class="col-lg-6 text-center text-lg-start position-relative">
        <style>
          .mobile-hero-img {
            max-width: 320px;
            animation: floatingMobileReal 4s ease-in-out infinite;
            filter: drop-shadow(-20px 20px 40px rgba(var(--bs-primary-rgb), 0.3));
          }

          @keyframes floatingMobileReal {
            0% {
              transform: translateY(0px) rotate(-2deg);
            }

            50% {
              transform: translateY(-15px) rotate(-1deg);
            }

            100% {
              transform: translateY(0px) rotate(-2deg);
            }
          }
        </style>
        <div class="d-inline-block position-relative pt-5">
          <!-- Blob background behind phone -->
          <div class="position-absolute top-50 start-50 translate-middle rounded-circle bg-label-primary" style="width: 350px; height: 350px; opacity: 0.5; filter: blur(40px); z-index: 0;"></div>
          <img src="{{ asset('assets/img/front-pages/landing-page/meu-mobile.png') }}" alt="Ghotme Mobile App Dashboard" class="img-fluid mobile-hero-img position-relative z-1" />
        </div>
      </div>

      <!-- Content Side -->
      <div class="col-lg-6">
        <div class="mb-4 text-start">
          <span class="badge bg-label-primary">{{ __('Gestão na Palma da Mão') }}</span>
        </div>
        <h3 class="mb-4 display-6 fw-extrabold">
          {{ __('Seu ERP completo em') }}
          <span class="text-primary position-relative z-1">{{ __('Qualquer Lugar') }}
            <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}" alt="section title icon"
              class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" style="width: 100%;" />
          </span>
        </h3>
        <p class="mb-5 fs-5">
          {{ __('O aplicativo do Ghotme permite que você e sua equipe controlem o negócio de onde estiverem. Abra ordens de serviço, consulte estoques e analise métricas em tempo real.') }}
        </p>

        <div class="row gy-4">
          <div class="col-sm-6">
            <div class="d-flex align-items-center border-bottom pb-4">
              <div class="badge bg-label-primary rounded p-2 me-4">
                <i class="ti tabler-camera icon-lg"></i>
              </div>
              <div>
                <h6 class="mb-1">{{ __('Vistoria Foto Integrada') }}</h6>
                <p class="mb-0 text-muted fs-6">{{ __('Tire fotos pelo celular e anexe na OS na hora.') }}</p>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="d-flex align-items-center border-bottom pb-4">
              <div class="badge bg-label-success rounded p-2 me-4">
                <i class="ti tabler-bell-ringing icon-lg"></i>
              </div>
              <div>
                <h6 class="mb-1">{{ __('Notificações Push') }}</h6>
                <p class="mb-0 text-muted fs-6">{{ __('Seja avisado sobre orçamentos aprovados.') }}</p>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="d-flex align-items-center">
              <div class="badge bg-label-info rounded p-2 me-4">
                <i class="ti tabler-checkup-list icon-lg"></i>
              </div>
              <div>
                <h6 class="mb-1">{{ __('RDO e Checklist Técnico') }}</h6>
                <p class="mb-0 text-muted fs-6">{{ __('Emita Relatórios Diários de Obra direto do canteiro.') }}</p>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="d-flex align-items-center">
              <div class="badge bg-label-warning rounded p-2 me-4">
                <i class="ti tabler-chart-pie icon-lg"></i>
              </div>
              <div>
                <h6 class="mb-1">{{ __('Dashboard Financeiro') }}</h6>
                <p class="mb-0 text-muted fs-6">{{ __('Veja seu faturamento de forma rápida.') }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-8 d-flex gap-3">
          <a href="#" class="btn btn-dark btn-lg border border-secondary shadow-sm hover-shadow-lg transition-all px-4">
            <i class="ti tabler-brand-apple icon-md me-2"></i> {{ __('App Store') }}
          </a>
          <a href="#" class="btn btn-dark btn-lg border border-secondary shadow-sm hover-shadow-lg transition-all px-4">
            <i class="ti tabler-brand-google-play icon-md me-2"></i> {{ __('Google Play') }}
          </a>
        </div>
      </div>
    </div>
  </div>
  <!-- Mobile App Section: End -->

  <!-- API & Integrations Section: Start -->
  <section id="landingAPI" class="section-py landing-api position-relative bg-body">
    <div class="container">
      <div class="row align-items-center gy-6 flex-column-reverse flex-lg-row">
        <!-- Content Side -->
        <div class="col-lg-6">
          <div class="mb-4 text-start">
            <span class="badge bg-label-info">{{ __('Ecossistema Aberto') }}</span>
          </div>
          <h3 class="mb-4 display-6 fw-extrabold">
            {{ __('Ghotme API - 100% integrável') }}
            <span class="text-info position-relative z-1">{{ __('com tudo o que você já usa!') }}
              <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}" alt="section title icon"
                class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" style="width: 100%; filter: hue-rotate(180deg);" />
            </span>
          </h3>
          <p class="mb-5 fs-5">
            {{ __('Seu negócio não deveria ficar preso a um software fechado. Com nossos Tokens de API, os seus clientes de planos avançados podem criar integrações incríveis sem limites.') }}
          </p>

          <div class="row gy-4">
            <div class="col-sm-6">
              <div class="d-flex flex-column align-items-start border rounded p-4 h-100 shadow-sm hover-shadow-xl transition-all">
                <div class="avatar avatar-md bg-label-danger rounded-circle mb-3">
                  <i class="ti tabler-brand-zapier fs-4"></i>
                </div>
                <h6 class="mb-2">{{ __('Integrações No-Code') }}</h6>
                <p class="mb-0 text-muted fs-6 small">{{ __('Conecte o Ghotme ao Zapier ou n8n e dispare e-mails automáticos no Mailchimp ao aprovar orçamentos.') }}</p>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="d-flex flex-column align-items-start border rounded p-4 h-100 shadow-sm hover-shadow-xl transition-all">
                <div class="avatar avatar-md bg-label-success rounded-circle mb-3">
                  <i class="ti tabler-message-chatbot fs-4"></i>
                </div>
                <h6 class="mb-2">{{ __('Chatbots no WhatsApp') }}</h6>
                <p class="mb-0 text-muted fs-6 small">{{ __('Permita que seus clientes consultem o status das Ordens de Serviço sozinhos através de um bot.') }}</p>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="d-flex flex-column align-items-start border rounded p-4 h-100 shadow-sm hover-shadow-xl transition-all">
                <div class="avatar avatar-md bg-label-warning rounded-circle mb-3">
                  <i class="ti tabler-shopping-cart fs-4"></i>
                </div>
                <h6 class="mb-2">{{ __('Sincronização de E-commerce') }}</h6>
                <p class="mb-0 text-muted fs-6 small">{{ __('Unifique seu estoque do ERP físico com o Shopify, Mercado Livre ou WooCommerce via API.') }}</p>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="d-flex flex-column align-items-start border rounded p-4 h-100 shadow-sm hover-shadow-xl transition-all">
                <div class="avatar avatar-md bg-label-primary rounded-circle mb-3">
                  <i class="ti tabler-chart-bar fs-4"></i>
                </div>
                <h6 class="mb-2">{{ __('Dashboards Inteligentes') }}</h6>
                <p class="mb-0 text-muted fs-6 small">{{ __('Conecte o Ghotme direto ao Power BI ou Looker Studio para painéis em telões na empresa.') }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Image Side -->
        <div class="col-lg-6 text-center text-lg-end position-relative">
          <div class="d-inline-block position-relative">
            <div class="bg-label-info rounded-circle position-absolute top-50 start-50 translate-middle" style="width: 300px; height: 300px; filter: blur(40px); opacity: 0.5;"></div>
            <!-- A placeholder to represent API / Code, you can change this image later to something like an API code snippet or dashboard -->
            <div class="card shadow-lg border-0 bg-dark text-white rounded-3 overflow-hidden position-relative z-1 text-start" style="max-width: 450px; transform: perspective(1000px) rotateY(-5deg) rotateX(5deg);">
              <div class="card-header bg-darker border-bottom border-secondary p-3 d-flex align-items-center">
                <div class="d-flex gap-2">
                  <span class="rounded-circle bg-danger" style="width: 12px; height: 12px;"></span>
                  <span class="rounded-circle bg-warning" style="width: 12px; height: 12px;"></span>
                  <span class="rounded-circle bg-success" style="width: 12px; height: 12px;"></span>
                </div>
                <span class="ms-3 font-monospace small text-muted">terminal - zsh</span>
              </div>
              <div class="card-body p-4 font-monospace small" style="color: #a9b7c6; background-color: #1e1f22;">
                <span style="color: #cc7832;">curl</span> -X GET \ <br>
                &nbsp;&nbsp;https://api.ghotme.com.br/v1/orders/1239 \ <br>
                &nbsp;&nbsp;-H <span style="color: #6a8759;">'Authorization: Bearer <span class="text-info">ghotme_sec_9xk2...</span>'</span> \ <br>
                &nbsp;&nbsp;-H <span style="color: #6a8759;">'Accept: application/json'</span><br><br>
                <span style="color: #808080;">// Response</span><br>
                {<br>
                &nbsp;&nbsp;<span style="color: #9876aa;">"status"</span>: <span style="color: #6a8759;">"Em Andamento"</span>,<br>
                &nbsp;&nbsp;<span style="color: #9876aa;">"total"</span>: <span style="color: #cc7832;">450.00</span>,<br>
                &nbsp;&nbsp;<span style="color: #9876aa;">"items"</span>: [<br>
                &nbsp;&nbsp;&nbsp;&nbsp;{ <span style="color: #9876aa;">"name"</span>: <span style="color: #6a8759;">"Serviço Especializado"</span> }<br>
                &nbsp;&nbsp;]<br>
                }
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- API & Integrations Section: End -->

  <!-- Pricing plans: Start -->
  <section id="landingPricing" class="section-py bg-body landing-pricing">
    <div class="container">
      <div class="text-center mb-4">
        <span class="badge bg-label-primary">{{ __('Pricing Plans') }}</span>
      </div>
      <h4 class="text-center mb-1">
        <span class="position-relative fw-extrabold z-1">{{ __('Tailored pricing plans') }}
          <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}" alt="section title icon"
            class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
        </span>
        {{ __('made for you') }}
      </h4>
      <p class="text-center pb-2 mb-7">{{ __('All plans include essential features to boost your business.') }}<br />{{ __('Choose the best plan for your needs.') }}</p>

      <div class="text-center mb-12">
        <div class="position-relative d-inline-block pt-3 pt-md-0">
          <div class="d-flex align-items-center justify-content-center">
            <span class="fs-6 text-body me-3">{{ __('Monthly') }}</span>
            <label class="switch switch-primary me-3">
              <input type="checkbox" class="switch-input price-duration-toggler" />
              <span class="switch-toggle-slider">
                <span class="switch-on"></span>
                <span class="switch-off"></span>
              </span>
            </label>
            <span class="fs-6 text-body">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __('Yearly') }}</span>
          </div>
          <div class="d-none d-sm-flex align-items-center gap-1 position-absolute" style="top: -30px; left: 65%; width: max-content;">
            <i class="ti tabler-corner-left-down icon-lg text-body-secondary scaleX-n1-rtl"></i>
            <span class="badge badge-sm bg-label-primary rounded-1">{{ __('Get 2 months free') }}</span>
          </div>
        </div>
      </div>

      <div class="row g-6 pt-lg-5">
        <!-- Basic -->
        <div class="col-xl-4 col-lg-6">
          <div class="card">
            <div class="card-header">
              <div class="text-center">
                <img src="{{ asset('assets/img/front-pages/icons/paper-airplane.png') }}" alt="paper airplane icon"
                  class="mb-8 pb-2 animate-float" />
                <h4 class="mb-0">{{ __('Basic') }}</h4>
                <div class="d-flex align-items-center justify-content-center">
                  <sup class="h6 text-body mt-2 mb-0 me-1">R$</sup>
                  <h1 class="price-toggle price-monthly text-primary mb-0">0</h1>
                  <h1 class="price-toggle price-yearly text-primary mb-0 d-none">0</h1>
                  <sub class="h6 text-body-secondary mb-n1 ms-1 price-monthly">/{{ __('month') }}</sub>
                  <sub class="h6 text-body-secondary mb-n1 ms-1 price-yearly d-none">/{{ __('year') }}</sub>
                </div>
              </div>
            </div>
            <div class="card-body">
              <ul class="list-unstyled pricing-list">
                <li>
                  <h6 class="d-flex align-items-center mb-3">
                    <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                        class="ti tabler-check icon-12px"></i></span>
                    {{ __('A simple start for everyone') }}
                  </h6>
                </li>
                <li>
                  <h6 class="d-flex align-items-center mb-3">
                    <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                        class="ti tabler-check icon-12px"></i></span>
                    {{ __('30 days Standard plan') }}
                  </h6>
                </li>
              </ul>
              <div class="d-grid mt-8">
                <a href="{{ url('/register') }}" class="btn btn-label-primary plan-action-btn">{{ __('Start Free') }}</a>
              </div>
            </div>
          </div>
        </div>

        <!-- Padrão -->
        <div class="col-xl-4 col-lg-6">
          <div class="card pricing-card-popular shadow-xl">
            <div class="card-header">
              <div class="text-center position-relative">
                <div class="position-absolute end-0 top-0 mt-n2">
                  <span class="badge bg-label-primary rounded-1">{{ __('Popular') }}</span>
                </div>
                <img src="{{ asset('assets/img/front-pages/icons/plane.png') }}" alt="plane icon" class="mb-8 pb-2 animate-propulsion" />
                <h4 class="mb-0">{{ __('Standard') }}</h4>
                <div class="d-flex align-items-center justify-content-center">
                  <sup class="h6 text-body mt-2 mb-0 me-1">R$</sup>
                  <h1 class="price-toggle price-monthly text-primary mb-0">149</h1>
                  <h1 class="price-toggle price-yearly text-primary mb-0 d-none">1.490</h1>
                  <sub class="h6 text-body-secondary mb-n1 ms-1 price-monthly">/{{ __('month') }}</sub>
                  <sub class="h6 text-body-secondary mb-n1 ms-1 price-yearly d-none">/{{ __('year') }}</sub>
                </div>
              </div>
            </div>
            <div class="card-body">
              <ul class="list-unstyled pricing-list">
                <li>
                  <h6 class="d-flex align-items-center mb-3">
                    <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                        class="ti tabler-check icon-12px"></i></span>
                    <strong>{{ __('Up to 3 users') }}</strong> {{ __('and full support') }}
                  </h6>
                </li>
                <li>
                  <h6 class="d-flex align-items-center mb-3">
                    <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                        class="ti tabler-check icon-12px"></i></span>
                    <strong>{{ __('Multi-Niche') }}:</strong> {{ __('The system adapts to your business') }}
                  </h6>
                </li>
                <li>
                  <h6 class="d-flex align-items-center mb-3">
                    <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                        class="ti tabler-check icon-12px"></i></span>
                    <strong>{{ __('Online Booking via Site') }}:</strong> {{ __('Sell more!') }}
                  </h6>
                </li>
                <li>
                  <h6 class="d-flex align-items-center mb-3">
                    <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                        class="ti tabler-check icon-12px"></i></span>
                    <strong>{{ __('Dynamic PIX') }}</strong> {{ __('with automatic clearing (Asaas)') }}
                  </h6>
                </li>
                <li>
                  <h6 class="d-flex align-items-center mb-3">
                    <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                        class="ti tabler-check icon-12px"></i></span>
                    <strong>{{ __('Bank Reconciliation (OFX)') }}:</strong> {{ __('Save hours') }}
                  </h6>
                </li>
                <li>
                  <h6 class="d-flex align-items-center mb-3">
                    <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                        class="ti tabler-check icon-12px"></i></span>
                    {{ __('WhatsApp Integration and Profit Reports') }}
                  </h6>
                </li>
              </ul>
              <div class="d-grid mt-8">
                <a href="https://www.asaas.com/c/plano-padrao-mensal"
                  data-monthly-link="https://www.asaas.com/c/plano-padrao-mensal"
                  data-yearly-link="https://www.asaas.com/c/plano-padrao-anual"
                  target="_blank" class="btn btn-primary plan-action-btn">{{ __('Subscribe Now') }}</a>
              </div>
            </div>
          </div>
        </div>

        <!-- Enterprise -->
        <div class="col-xl-4 col-lg-6">
          <div class="card">
            <div class="card-header">
              <div class="text-center">
                <img src="{{ asset('assets/img/front-pages/icons/shuttle-rocket.png') }}" alt="shuttle rocket icon"
                  class="mb-8 pb-2 animate-rocket" />
                <h4 class="mb-0">{{ __('Enterprise') }}</h4>
                <div class="d-flex align-items-center justify-content-center">
                  <sup class="h6 text-body mt-2 mb-0 me-1">R$</sup>
                  <h1 class="price-toggle price-monthly text-primary mb-0">279</h1>
                  <h1 class="price-toggle price-yearly text-primary mb-0 d-none">2.790</h1>
                  <sub class="h6 text-body-secondary mb-n1 ms-1 price-monthly">/{{ __('month') }}</sub>
                  <sub class="h6 text-body-secondary mb-n1 ms-1 price-yearly d-none">/{{ __('year') }}</sub>
                </div>
              </div>
            </div>
            <div class="card-body">
              <ul class="list-unstyled pricing-list">
                <li>
                  <h6 class="d-flex align-items-center mb-3">
                    <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                        class="ti tabler-check icon-12px"></i></span>
                    <strong>{{ __('Everything in Standard Plan +') }}</strong>
                  </h6>
                </li>
                <li>
                  <h6 class="d-flex align-items-center mb-3">
                    <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                        class="ti tabler-check icon-12px"></i></span>
                    <strong>{{ __('Up to 10 users') }}</strong> {{ __('and priority support') }}
                  </h6>
                </li>
                <li>
                  <h6 class="d-flex align-items-center mb-3">
                    <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                        class="ti tabler-check icon-12px"></i></span>
                    <strong>{{ __('Automatic Supply') }}:</strong> {{ __('Replenishment robot') }}
                  </h6>
                </li>
                <li>
                  <h6 class="d-flex align-items-center mb-3">
                    <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                        class="ti tabler-check icon-12px"></i></span>
                    <strong>{{ __('Financial BPO') }}</strong> {{ __('and Accountant Portal') }}
                  </h6>
                </li>
                <li>
                  <h6 class="d-flex align-items-center mb-3">
                    <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                        class="ti tabler-check icon-12px"></i></span>
                    <strong>{{ __('Tax Invoice Issuance') }}</strong> {{ __('unlimited (NFe/NFSe)') }}
                  </h6>
                </li>
                <li>
                  <h6 class="d-flex align-items-center mb-3">
                    <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                        class="ti tabler-check icon-12px"></i></span>
                    {{ __('Fleet Management and advanced B2B') }}
                  </h6>
                </li>
              </ul>
              <div class="d-grid mt-8">
                <a href="https://www.asaas.com/c/plano-enterprise-mensal"
                  data-monthly-link="https://www.asaas.com/c/plano-enterprise-mensal"
                  data-yearly-link="https://www.asaas.com/c/plano-enterprise-anual"
                  target="_blank" class="btn btn-label-primary plan-action-btn">{{ __('Subscribe Now') }}</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- Pricing plans: End -->

  <!-- Fun facts: Start -->
  <section id="landingDiferenciais" class="section-py landing-diferenciais">
    <div class="container">
      <style>
        .diferencial-card {
          transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
          position: relative;
          z-index: 1;
          background: var(--bs-body-bg);
          /* Ensure opacity */
        }

        .diferencial-card:hover {
          transform: translateY(-8px);
          border-color: transparent !important;
        }

        .diferencial-card .icon-box {
          transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
          /* Bounce effect */
          display: inline-block;
        }

        .diferencial-card:hover .icon-box {
          transform: scale(1.15) rotate(3deg);
        }

        /* Individual Colors */
        .hover-effect-primary:hover {
          box-shadow: 0 15px 30px rgba(var(--bs-primary-rgb), 0.25);
        }

        .hover-effect-success:hover {
          box-shadow: 0 15px 30px rgba(var(--bs-success-rgb), 0.25);
        }

        .hover-effect-info:hover {
          box-shadow: 0 15px 30px rgba(var(--bs-info-rgb), 0.25);
        }

        .hover-effect-warning:hover {
          box-shadow: 0 15px 30px rgba(var(--bs-warning-rgb), 0.25);
        }
      </style>
      <div class="row gy-6">
        <div class="col-sm-6 col-lg-3">
          <div class="card border border-primary shadow-none h-100 diferencial-card hover-effect-primary">
            <div class="card-body text-center">
              <div class="mb-4 text-primary icon-box">
                <i class="ti tabler-headset icon-xl"></i>
              </div>
              <h5 class="mb-2">{{ __('Humanized Support') }}</h5>
              <p class="text-muted mb-0">{{ __('Fast and direct service with specialists. No robots.') }}</p>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card border border-success shadow-none h-100 diferencial-card hover-effect-success">
            <div class="card-body text-center">
              <div class="mb-4 text-success icon-box">
                <i class="ti tabler-lock-open icon-xl"></i>
              </div>
              <h5 class="mb-2">{{ __('No Loyalty Contract') }}</h5>
              <p class="text-muted mb-0">{{ __('Total freedom to cancel whenever you want, without fines or bureaucracy.') }}</p>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card border border-info shadow-none h-100 diferencial-card hover-effect-info">
            <div class="card-body text-center">
              <div class="mb-4 text-info icon-box">
                <i class="ti tabler-refresh icon-xl"></i>
              </div>
              <h5 class="mb-2">{{ __('Always Updated') }}</h5>
              <p class="text-muted mb-0">{{ __('New features and constant improvements at no additional cost.') }}</p>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card border border-warning shadow-none h-100 diferencial-card hover-effect-warning">
            <div class="card-body text-center">
              <div class="mb-4 text-warning icon-box">
                <i class="ti tabler-shield-lock icon-xl"></i>
              </div>
              <h5 class="mb-2">{{ __('Secure Data') }}</h5>
              <p class="text-muted mb-0">{{ __('Bank-level security and automatic daily backups.') }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- Fun facts: End -->

  <!-- FAQ: Start -->
  <section id="landingFAQ" class="section-py bg-body landing-faq">
    <div class="container">
      <div class="text-center mb-4">
        <span class="badge bg-label-primary">{{ __('Frequently Asked Questions') }}</span>
      </div>
      <h4 class="text-center mb-1">
        {{ __('Common') }}
        <span class="position-relative fw-extrabold z-1">{{ __('Questions') }}
          <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}" alt="section title icon"
            class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
        </span>
      </h4>
      <p class="text-center mb-12 pb-md-4">{{ __('Find answers to the main questions about Ghotme.') }}</p>
      <div class="row gy-12 align-items-center">
        <div class="col-lg-5">
          <div class="text-center">
            <img src="{{ asset('assets/img/front-pages/landing-page/faq-boy-with-logos.png') }}"
              alt="faq boy with logos" class="faq-image" />
          </div>
        </div>
        <div class="col-lg-7">
          <div class="accordion" id="accordionExample">
            <!-- Pergunta 1: Multi-nicho -->
            <div class="card accordion-item active">
              <h2 class="accordion-header" id="headingOne">
                <button type="button" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#accordionOne"
                  aria-expanded="true" aria-controls="accordionOne">
                  <i class="ti tabler-adjustments-alt me-2 text-primary"></i> {{ __('Does Ghotme really adapt to my niche?') }}
                </button>
              </h2>
              <div id="accordionOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                  {{ __('Absolutely! When registering, you choose your segment (Workshop, Pet Shop, Construction, Assistance, etc.) and the system automatically changes all terms, icons and processes to speak your language. It is a system tailored for you.') }}
                </div>
              </div>
            </div>

            <!-- Pergunta 2: Pagamentos e Bitcoin -->
            <div class="card accordion-item">
              <h2 class="accordion-header" id="headingTwo">
                <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                  data-bs-target="#accordionTwo" aria-expanded="false" aria-controls="accordionTwo">
                  <i class="ti tabler-currency-bitcoin me-2 text-warning"></i> {{ __('What payment methods are accepted?') }}
                </button>
              </h2>
              <div id="accordionTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                data-bs-parent="#accordionExample">
                <div class="accordion-body">
                  {{ __('We accept credit cards, bank slips and the innovative Dynamic PIX with automatic clearing. Additionally, Ghotme is already prepared for the future and allows receiving through Bitcoin.') }}
                </div>
              </div>
            </div>

            <!-- Pergunta 3: Contabilidade/BPO -->
            <div class="card accordion-item">
              <h2 class="accordion-header" id="headingThree">
                <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                  data-bs-target="#accordionThree" aria-expanded="false" aria-controls="accordionThree">
                  <i class="ti tabler-user-search me-2 text-info"></i> {{ __('Will my accountant have access to the system?') }}
                </button>
              </h2>
              <div id="accordionThree" class="accordion-collapse collapse" aria-labelledby="headingThree"
                data-bs-parent="#accordionExample">
                <div class="accordion-body">
                  {{ __('Yes! We created an exclusive Accountant Portal. They will be able to download your XMLs, check your revenue and perform financial auditing (BPO) without you having to send stacks of papers every month.') }}
                </div>
              </div>
            </div>

            <!-- Pergunta 4: Conciliação Bancária -->
            <div class="card accordion-item">
              <h2 class="accordion-header" id="headingFour">
                <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                  data-bs-target="#accordionFour" aria-expanded="false" aria-controls="accordionFour">
                  <i class="ti tabler-refresh me-2 text-success"></i> {{ __('How does bank reconciliation work?') }}
                </button>
              </h2>
              <div id="accordionFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
                data-bs-parent="#accordionExample">
                <div class="accordion-body">
                  {{ __('Just import your bank\'s OFX file and Ghotme automatically matches it with your sales and expenses. You save hours of manual work and avoid typing errors in your financials.') }}
                </div>
              </div>
            </div>

            <!-- Pergunta 5: Suporte -->
            <div class="card accordion-item">
              <h2 class="accordion-header" id="headingFive">
                <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                  data-bs-target="#accordionFive" aria-expanded="false" aria-controls="accordionFive">
                  <i class="ti tabler-headset me-2 text-danger"></i> {{ __('Will I have support to configure the system?') }}
                </button>
              </h2>
              <div id="accordionFive" class="accordion-collapse collapse" aria-labelledby="headingFive"
                data-bs-parent="#accordionExample">
                <div class="accordion-body">
                  {{ __('Yes! We offer humanized support via WhatsApp and internal chat. Our team helps you with data import and initial configuration so you can start billing as soon as possible.') }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- FAQ: End -->

  <!-- CTA: Start -->
  <section id="landingCTA" class="section-py landing-cta position-relative p-lg-0 pb-0 overflow-hidden">
    <div class="container position-relative z-1">
      <div class="row align-items-center gy-12">
        <div class="col-lg-6 text-start text-sm-center text-lg-start">
          <h3 class="cta-title text-primary fw-extrabold mb-2 display-5">{{ __('Make your company') }} <span class="text-dark">{{ __('take off today!') }}</span></h3>
          <h5 class="text-body mb-8 opacity-75">{{ __('Join hundreds of entrepreneurs who automated their management with Ghotme.') }} <strong>{{ __('Free trial for 30 days') }}</strong>, {{ __('no commitment.') }}</h5>
          <div class="d-flex flex-column flex-sm-row gap-4 justify-content-sm-center justify-content-lg-start">
            <a href="{{ url('/register') }}" class="btn btn-lg btn-primary shadow-lg px-5 py-3">
              {{ __('Start Now for Free') }} <i class="ti tabler-rocket ms-2"></i>
            </a>
            <a href="#landingFeatures" class="btn btn-lg btn-outline-secondary px-5 py-3">
              {{ __('See Features') }}
            </a>
          </div>
        </div>
        <div class="col-lg-6 pt-lg-12 text-center text-lg-end">
          <img src="{{ asset('assets/img/front-pages/landing-page/meu-sistema-' . $configData['theme'] . '.png') }}" alt="cta dashboard"
            data-app-light-img="front-pages/landing-page/meu-sistema-light.png"
            data-app-dark-img="front-pages/landing-page/meu-sistema-dark.png"
            class="img-fluid mt-lg-4 hover-shadow-xl transition-all" style="border-radius: 1rem; transform: perspective(1000px) rotateY(-10deg) rotateX(5deg);" />
        </div>
      </div>
    </div>
  </section>
  <!-- CTA: End -->

  <!-- Contact Us: Start -->
  <section id="landingContact" class="section-py bg-body landing-contact">
    <div class="container">
      <div class="text-center mb-4">
        <span class="badge bg-label-primary">{{ __('Contact Us') }}</span>
      </div>
      <h4 class="text-center mb-1">
        <span class="position-relative fw-extrabold z-1">{{ __('Let\'s Grow') }}
          <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}" alt="section title icon"
            class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
        </span>
        {{ __('Together') }}
      </h4>
      <p class="text-center mb-12 pb-md-4">{{ __('Questions about the system? Our team of specialists is ready to help.') }}</p>
      <div class="row g-6">
        <div class="col-lg-5">
          <div class="contact-img-box position-relative border p-2 h-100">
            <img src="{{ asset('assets/img/front-pages/icons/contact-border.png') }}" alt="contact border"
              class="contact-border-img position-absolute d-none d-lg-block scaleX-n1-rtl" />
            <img src="{{ asset('assets/img/front-pages/landing-page/contact-customer-service.png') }}"
              alt="contact customer service" class="contact-img w-100 scaleX-n1-rtl" />
            <div class="p-4 pb-2">
              <div class="row g-4">
                <div class="col-md-6 col-lg-12 col-xl-6">
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-primary rounded p-1_5 me-3"><i
                        class="ti tabler-mail icon-lg"></i></div>
                    <div>
                      <p class="mb-0">{{ __('Email') }}</p>
                      <h6 class="mb-0"><a href="mailto:suporte@ghotme.com.br" class="text-heading">suporte@ghotme.com.br</a>
                      </h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-7">
          <div class="card h-100">
            <div class="card-body">
              <h4 class="mb-2">{{ __('Send a message') }}</h4>
              <p class="mb-6">
                {{ __('Still have questions about plans, features or want a personalized demonstration? Fill in the fields below.') }}
              </p>
              <form>
                <div class="row g-4">
                  <div class="col-md-6">
                    <label class="form-label" for="contact-form-fullname">{{ __('Full Name') }}</label>
                    <input type="text" class="form-control" id="contact-form-fullname" placeholder="{{ __('Your name') }}" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="contact-form-email">{{ __('Email') }}</label>
                    <input type="text" id="contact-form-email" class="form-control" placeholder="seu@email.com" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="contact-form-phone">{{ __('WhatsApp / Phone') }}</label>
                    <input type="text" id="contact-form-phone" class="form-control" placeholder="(00) 00000-0000" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="contact-form-subject">{{ __('Subject') }}</label>
                    <select id="contact-form-subject" class="form-select">
                      <option selected disabled value="">{{ __('Select...') }}</option>
                      <option value="sales">{{ __('Commercial / Sales') }}</option>
                      <option value="support">{{ __('Technical Support') }}</option>
                      <option value="partnership">{{ __('Partnership') }}</option>
                      <option value="other">{{ __('Others') }}</option>
                    </select>
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="contact-form-message">{{ __('Message') }}</label>
                    <textarea id="contact-form-message" class="form-control" rows="5"
                      placeholder="{{ __('Hello, I would like to know more about the Enterprise plan...') }}"></textarea>
                  </div>
                  <div class="col-12">
                    <button type="submit" class="btn btn-primary">{{ __('Send Message') }}</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- Contact Us: End -->
  </div>
  @endsection