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
          <h1 class="text-primary hero-title display-6 fw-extrabold">A gestão completa para sua empresa em um só lugar</h1>
          <h2 class="hero-sub-title h6 mb-6">
            Controle vendas, serviços, estoque, financeiro e clientes com facilidade.<br class="d-none d-lg-block" />
            Otimize sua produtividade e escale seu negócio hoje mesmo.
          </h2>
          <div class="landing-hero-btn d-inline-block position-relative">
            <span class="hero-btn-item position-absolute d-none d-md-flex fw-medium">Conheça nosso sistema <img
                src="{{ asset('assets/img/front-pages/icons/Join-community-arrow.png') }}" alt="Seta junte-se à comunidade"
                class="scaleX-n1-rtl" /></span>
            <a href="#landingPricing" class="btn btn-primary btn-lg">Comece seu teste grátis</a>
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
        <span class="badge bg-label-primary">Funcionalidades Principais</span>
      </div>
      <h4 class="text-center mb-1">
        <span class="position-relative fw-extrabold z-1">Tudo que você precisa
          <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}" alt="icone de destaque da secao"
            class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
        </span>
        para gerir seu negócio
      </h4>
      <p class="text-center mb-12">Ferramentas poderosas para empresas de serviços, comércio e prestadores autônomos.</p>
      <div class="features-icon-wrapper row gx-0 gy-6 g-sm-12">
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="mb-4 text-primary text-center">
            <i class="ti tabler-clipboard-text icon-xl mb-3"></i>
          </div>
          <h5 class="mb-2">Gestão de Vendas e Serviços</h5>
          <p class="features-icon-description">Emita pedidos de venda, orçamentos e ordens de serviço personalizadas em poucos cliques.</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="mb-4 text-primary text-center">
            <i class="ti tabler-box icon-xl mb-3"></i>
          </div>
          <h5 class="mb-2">Controle de Estoque</h5>
          <p class="features-icon-description">Acompanhe a entrada e saída de produtos, receba alertas de reposição e gerencie fornecedores.</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="mb-4 text-primary text-center">
            <i class="ti tabler-users icon-xl mb-3"></i>
          </div>
          <h5 class="mb-2">Gestão de Clientes (CRM)</h5>
          <p class="features-icon-description">Mantenha o histórico completo de seus clientes, preferências e aumente suas vendas recorrentes.</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="mb-4 text-primary text-center">
            <i class="ti tabler-cash icon-xl mb-3"></i>
          </div>
          <h5 class="mb-2">Financeiro Completo</h5>
          <p class="features-icon-description">Fluxo de caixa, contas a pagar e receber, boletos e conciliação bancária simplificada.</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="mb-4 text-primary text-center">
            <i class="ti tabler-calendar-stats icon-xl mb-3"></i>
          </div>
          <h5 class="mb-2">Agenda e Produtividade</h5>
          <p class="features-icon-description">Organize a agenda da sua equipe, evite conflitos de horário e garanta a entrega no prazo.</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="mb-4 text-primary text-center">
            <i class="ti tabler-chart-pie icon-xl mb-3"></i>
          </div>
          <h5 class="mb-2">Relatórios Inteligentes</h5>
          <p class="features-icon-description">Tome decisões baseadas em dados com dashboards de faturamento, lucro e desempenho.</p>
        </div>
      </div>
    </div>
  </section>
  <!-- Useful features: End -->

  <!-- Business Highlights Carousel: Start -->
  <section id="landingBusinessHighlights" class="section-py bg-body landing-reviews pb-0">
    <div class="swiper-business-highlights overflow-hidden position-relative">
      <div class="swiper" id="swiper-business-highlights">
        <div class="swiper-wrapper">

          <!-- Slide 1: Transformation (Why Choose) -->
          <div class="swiper-slide">
            <div class="container h-100">
              <div class="mb-4 text-center">
                <span class="badge bg-label-primary">A Transformação</span>
              </div>
              <h4 class="text-center mb-1">
                <span class="position-relative fw-extrabold z-1">Por que escolher
                  <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}"
                    alt="icone de destaque da secao"
                    class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
                </span>
                o Ghotme?
              </h4>
              <p class="text-center mb-5 text-heading">
                Veja a diferença que um sistema de gestão integrado faz no seu dia a dia.
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
                      <h4 class="mb-3 text-danger">Sem o Ghotme</h4>
                      <ul class="list-unstyled text-start d-inline-block mx-auto">
                        <li class="mb-3 d-flex align-items-center">
                          <i class="ti tabler-file-spreadsheet text-danger me-2"></i> Planilhas espalhadas e confusas
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                          <i class="ti tabler-clock-off text-danger me-2"></i> Perda de tempo com tarefas manuais
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                          <i class="ti tabler-chart-arrows-vertical text-danger me-2"></i> Falta de controle financeiro
                        </li>
                        <li class="d-flex align-items-center">
                          <i class="ti tabler-mood-sad text-danger me-2"></i> Estresse e insegurança nas decisões
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
                      <h4 class="mb-3 text-primary">Com o Ghotme</h4>
                      <ul class="list-unstyled text-start d-inline-block mx-auto">
                        <li class="mb-3 d-flex align-items-center">
                          <i class="ti tabler-layout-dashboard text-success me-2"></i> Tudo organizado em um só lugar
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                          <i class="ti tabler-rocket text-success me-2"></i> Automação que poupa horas do seu dia
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                          <i class="ti tabler-chart-line text-success me-2"></i> Visão clara de lucros e despesas
                        </li>
                        <li class="d-flex align-items-center">
                          <i class="ti tabler-mood-smile text-success me-2"></i> Tranquilidade para focar em crescer
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
                <span class="badge bg-label-primary">Público Alvo</span>
              </div>
              <h4 class="text-center mb-1">
                <span class="position-relative fw-extrabold z-1">Feito para
                  <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}"
                    alt="icone de destaque da secao"
                    class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
                </span>
                o seu Negócio
              </h4>
              <p class="text-center mb-5">
                O sistema flexível que se adapta à sua realidade.
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
                      <h5 class="mb-2">Prestadores de Serviço</h5>
                      <p class="mb-0">Oficinas, assistências técnicas, clínicas e consultorias. Organize sua agenda e ordens de
                        serviço.</p>
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
                      <h5 class="mb-2">Comércio e Varejo</h5>
                      <p class="mb-0">Lojas de roupas, mercados e autopeças. Controle seu estoque e venda com rapidez no balcão.
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
                      <h5 class="mb-2">Profissionais Liberais</h5>
                      <p class="mb-0">Advogados, arquitetos e freelancers. Simplifique seu financeiro e emita notas fiscais em
                        segundos.</p>
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
                <span class="badge bg-label-primary">Conectividade</span>
              </div>
              <h4 class="text-center mb-1">
                <span class="position-relative fw-extrabold z-1">Integrações que
                  <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}"
                    alt="icone de destaque da secao"
                    class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
                </span>
                facilitam sua vida
              </h4>
              <p class="text-center mb-5">
                Centralize sua operação conectando as ferramentas que você já usa.
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
                      <h5 class="mb-2">WhatsApp Integrado</h5>
                      <p class="mb-0">Envie orçamentos, lembretes de agenda e status de serviços automaticamente para o
                        WhatsApp dos seus clientes.</p>
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
                      <h5 class="mb-2">Notas Fiscais (NFe/NFCe)</h5>
                      <p class="mb-0">Emita notas fiscais de produto e serviço com poucos cliques, integrado diretamente com a
                        Sefaz e prefeituras.</p>
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
                      <h5 class="mb-2">Meios de Pagamento</h5>
                      <p class="mb-0">Gere boletos registrados, cobranças via PIX e conciliação bancária automática com as
                        principais plataformas.</p>
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
                <span class="badge bg-label-primary">Passo a Passo</span>
              </div>
              <h4 class="text-center mb-1">
                <span class="position-relative fw-extrabold z-1">Sua Jornada
                  <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}"
                    alt="icone de destaque da secao"
                    class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
                </span>
                de Sucesso
              </h4>
              <p class="text-center mb-5">
                Começar a usar o Ghotme é simples, rápido e sem burocracia.
              </p>
              <div class="row gy-4 justify-content-center mb-5">
                <div class="col-lg-3 col-md-6">
                  <div class="text-center position-relative">
                    <div class="avatar avatar-xl border border-primary border-2 rounded-circle mx-auto mb-3">
                      <span class="avatar-initial rounded-circle bg-primary text-white">1</span>
                    </div>
                    <h5 class="mb-2">Crie sua Conta</h5>
                    <p class="text-muted">Escolha o plano ideal e faça seu cadastro em menos de 2 minutos.</p>
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
                    <h5 class="mb-2">Configure Fácil</h5>
                    <p class="text-muted">Cadastre seus dados ou importe clientes e produtos de planilhas.</p>
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
                    <h5 class="mb-2">Gerencie Tudo</h5>
                    <p class="text-muted">Emita notas, controle estoque e financeiro em uma única tela.</p>
                    <div class="d-none d-lg-block position-absolute start-100 top-0 translate-middle"
                      style="margin-top: 1.5rem; margin-left: -1rem;">
                      <i class="ti tabler-arrow-right text-muted icon-lg"></i>
                    </div>
                  </div>
                </div>
                <div class="col-lg-3 col-md-6">
                  <div class="text-center position-relative">
                    <div class="avatar avatar-xl border border-primary border-2 rounded-circle mx-auto mb-3">
                      <span class="avatar-initial rounded-circle bg-primary text-white">
                        <i class="ti tabler-trophy icon-md h-auto"></i>
                      </span>
                    </div>
                    <h5 class="mb-2">Veja os Resultados</h5>
                    <p class="text-muted">Acompanhe relatórios de crescimento e tome decisões inteligentes.</p>
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
    <p class="text-center mb-md-11 pb-0 pb-xl-12">O site integra com Pagar.me, Asaas, PagSeguro e Stripe.</p>
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
      </div>
    </div>
  </div>
</section>
<!-- Payment Platforms: End -->

<!-- Pricing plans: Start -->
<section id="landingPricing" class="section-py bg-body landing-pricing">
  <div class="container">
    <div class="text-center mb-4">
      <span class="badge bg-label-primary">Planos de Preços</span>
    </div>
    <h4 class="text-center mb-1">
      <span class="position-relative fw-extrabold z-1">Planos de preços sob medida
        <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}" alt="icone de destaque da secao"
          class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
      </span>
      feitos para você
    </h4>
    <p class="text-center pb-2 mb-7">Todos os planos incluem recursos essenciais para impulsionar sua empresa.<br />Escolha o melhor plano para suas necessidades.</p>

    <div class="text-center mb-12">
      <div class="position-relative d-inline-block pt-3 pt-md-0">
        <div class="d-flex align-items-center justify-content-center">
          <span class="fs-6 text-body me-3">Mensal</span>
          <label class="switch switch-primary me-3">
            <input type="checkbox" class="switch-input price-duration-toggler" />
            <span class="switch-toggle-slider">
              <span class="switch-on"></span>
              <span class="switch-off"></span>
            </span>
          </label>
          <span class="fs-6 text-body">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Anual</span>
        </div>
        <div class="d-none d-sm-flex align-items-center gap-1 position-absolute" style="top: -30px; left: 65%; width: max-content;">
          <i class="ti tabler-corner-left-down icon-lg text-body-secondary scaleX-n1-rtl"></i>
          <span class="badge badge-sm bg-label-primary rounded-1">Ganhe 2 meses grátis</span>
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
                class="mb-8 pb-2" />
              <h4 class="mb-0">Básico</h4>
              <div class="d-flex align-items-center justify-content-center">
                <sup class="h6 text-body mt-2 mb-0 me-1">R$</sup>
                <h1 class="price-toggle price-monthly text-primary mb-0">0</h1>
                <h1 class="price-toggle price-yearly text-primary mb-0 d-none">0</h1>
                <sub class="h6 text-body-secondary mb-n1 ms-1 price-monthly">/mês</sub>
                <sub class="h6 text-body-secondary mb-n1 ms-1 price-yearly d-none">/ano</sub>
              </div>
            </div>
          </div>
          <div class="card-body">
            <ul class="list-unstyled pricing-list">
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  Um começo simples para todos
                </h6>
              </li>
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  30 dias plano Padrão
                </h6>
              </li>
            </ul>
            <div class="d-grid mt-8">
              <a href="{{ url('/register') }}" class="btn btn-label-primary plan-action-btn">Começar Grátis</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Padrão -->
      <div class="col-xl-4 col-lg-6">
        <div class="card border border-primary shadow-xl">
          <div class="card-header">
            <div class="text-center position-relative">
              <div class="position-absolute end-0 top-0 mt-n2">
                <span class="badge bg-label-primary rounded-1">Popular</span>
              </div>
              <img src="{{ asset('assets/img/front-pages/icons/plane.png') }}" alt="plane icon" class="mb-8 pb-2" />
              <h4 class="mb-0">Padrão</h4>
              <div class="d-flex align-items-center justify-content-center">
                <sup class="h6 text-body mt-2 mb-0 me-1">R$</sup>
                <h1 class="price-toggle price-monthly text-primary mb-0">149</h1>
                <h1 class="price-toggle price-yearly text-primary mb-0 d-none">1.490</h1>
                <sub class="h6 text-body-secondary mb-n1 ms-1 price-monthly">/mês</sub>
                <sub class="h6 text-body-secondary mb-n1 ms-1 price-yearly d-none">/ano</sub>
              </div>
            </div>
          </div>
          <div class="card-body">
            <ul class="list-unstyled pricing-list">
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  MEI e Pequenos negócios
                </h6>
              </li>
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  Integração via WhatsApp
                </h6>
              </li>
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  Até 3 usuários
                </h6>
              </li>
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  Funcionalidades essenciais ERP
                </h6>
              </li>
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  Suporte em horário comercial
                </h6>
              </li>
            </ul>
            <div class="d-grid mt-8">
              <a href="https://www.asaas.com/c/plano-padrao-mensal"
                data-monthly-link="https://www.asaas.com/c/plano-padrao-mensal"
                data-yearly-link="https://www.asaas.com/c/plano-padrao-anual"
                target="_blank" class="btn btn-primary plan-action-btn">Assinar Agora</a>
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
                class="mb-8 pb-2" />
              <h4 class="mb-0">Enterprise</h4>
              <div class="d-flex align-items-center justify-content-center">
                <sup class="h6 text-body mt-2 mb-0 me-1">R$</sup>
                <h1 class="price-toggle price-monthly text-primary mb-0">279</h1>
                <h1 class="price-toggle price-yearly text-primary mb-0 d-none">2.790</h1>
                <sub class="h6 text-body-secondary mb-n1 ms-1 price-monthly">/mês</sub>
                <sub class="h6 text-body-secondary mb-n1 ms-1 price-yearly d-none">/ano</sub>
              </div>
            </div>
          </div>
          <div class="card-body">
            <ul class="list-unstyled pricing-list">
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  Empresas estruturadas (PJ)
                </h6>
              </li>
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  Até 10 usuários
                </h6>
              </li>
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  Todos os módulos do sistema
                </h6>
              </li>
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  Prioridade no suporte
                </h6>
              </li>
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  Recursos avançados e automações
                </h6>
              </li>
            </ul>
            <div class="d-grid mt-8">
              <a href="https://www.asaas.com/c/plano-enterprise-mensal"
                data-monthly-link="https://www.asaas.com/c/plano-enterprise-mensal"
                data-yearly-link="https://www.asaas.com/c/plano-enterprise-anual"
                target="_blank" class="btn btn-label-primary plan-action-btn">Assinar Agora</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- Pricing plans: End -->

<!-- Fun facts: Start -->
<section id="landingFunFacts" class="section-py landing-fun-facts">
  <div class="container">
    <div class="row gy-6">
      <div class="col-sm-6 col-lg-3">
        <div class="card border border-primary shadow-none">
          <div class="card-body text-center">
            <div class="mb-4 text-primary">
              <i class="ti tabler-tool icon-xl"></i>
            </div>
            <h3 class="mb-0">150k+</h3>
            <p class="fw-medium mb-0">
              Pedidos<br />
              Processados
            </p>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card border border-success shadow-none">
          <div class="card-body text-center">
            <div class="mb-4 text-success">
              <i class="ti tabler-users icon-xl"></i>
            </div>
            <h3 class="mb-0">2.5k+</h3>
            <p class="fw-medium mb-0">
              Empresas<br />
              Parceiras
            </p>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card border border-info shadow-none">
          <div class="card-body text-center">
            <div class="mb-4 text-info">
              <i class="ti tabler-thumb-up icon-xl"></i>
            </div>
            <h3 class="mb-0">99%</h3>
            <p class="fw-medium mb-0">
              Satisfação dos<br />
              Clientes
            </p>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card border border-warning shadow-none">
          <div class="card-body text-center">
            <div class="mb-4 text-warning">
              <i class="ti tabler-shield-check icon-xl"></i>
            </div>
            <h3 class="mb-0">100%</h3>
            <p class="fw-medium mb-0">
              Segurança nos<br />
              Seus Dados
            </p>
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
      <span class="badge bg-label-primary">Perguntas Frequentes</span>
    </div>
    <h4 class="text-center mb-1">
      Dúvidas
      <span class="position-relative fw-extrabold z-1">Comuns
        <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}" alt="icone de destaque da secao"
          class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
      </span>
    </h4>
    <p class="text-center mb-12 pb-md-4">Encontre respostas para as principais perguntas sobre o Ghotme.</p>
    <div class="row gy-12 align-items-center">
      <div class="col-lg-5">
        <div class="text-center">
          <img src="{{ asset('assets/img/front-pages/landing-page/faq-boy-with-logos.png') }}"
            alt="faq boy with logos" class="faq-image" />
        </div>
      </div>
      <div class="col-lg-7">
        <div class="accordion" id="accordionExample">
          <div class="card accordion-item">
            <h2 class="accordion-header" id="headingOne">
              <button type="button" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#accordionOne"
                aria-expanded="true" aria-controls="accordionOne">O Ghotme funciona em dispositivos móveis?</button>
            </h2>

            <div id="accordionOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
              <div class="accordion-body">Sim! O Ghotme é totalmente responsivo e pode ser acessado de qualquer smartphone ou tablet, permitindo que você gerencie seu negócio de qualquer lugar.</div>
            </div>
          </div>
          <div class="card accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                data-bs-target="#accordionTwo" aria-expanded="false" aria-controls="accordionTwo">Posso importar meus dados de outro sistema?</button>
            </h2>
            <div id="accordionTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
              data-bs-parent="#accordionExample">
              <div class="accordion-body">Sim, nossa equipe de suporte auxilia na importação de cadastros de clientes, produtos e fornecedores através de planilhas Excel.</div>
            </div>
          </div>
          <div class="card accordion-item active">
            <h2 class="accordion-header" id="headingThree">
              <button type="button" class="accordion-button" data-bs-toggle="collapse"
                data-bs-target="#accordionThree" aria-expanded="false" aria-controls="accordionThree">Como funciona o período de teste grátis?</button>
            </h2>
            <div id="accordionThree" class="accordion-collapse collapse show" aria-labelledby="headingThree"
              data-bs-parent="#accordionExample">
              <div class="accordion-body">
                Você pode testar todas as funcionalidades do plano Profissional por 7 dias sem compromisso. Não solicitamos cartão de crédito para o teste.
              </div>
            </div>
          </div>
          <div class="card accordion-item">
            <h2 class="accordion-header" id="headingFour">
              <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                data-bs-target="#accordionFour" aria-expanded="false" aria-controls="accordionFour">O sistema emite Nota Fiscal?</button>
            </h2>
            <div id="accordionFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
              data-bs-parent="#accordionExample">
              <div class="accordion-body">Sim, o Ghotme está preparado para emissão de Notas Fiscais de Produto (NF-e) e Serviço (NFS-e) de forma integrada.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- FAQ: End -->

<!-- CTA: Start -->
<section id="landingCTA" class="section-py landing-cta position-relative p-lg-0 pb-0">
  <img src="{{ asset('assets/img/front-pages/backgrounds/cta-bg-' . $configData['theme'] . '.png') }}"
    class="position-absolute bottom-0 end-0 scaleX-n1-rtl h-100 w-100 z-n1" alt="cta image"
    data-app-light-img="front-pages/backgrounds/cta-bg-light.png"
    data-app-dark-img="front-pages/backgrounds/cta-bg-dark.png" />
  <div class="container">
    <div class="row align-items-center gy-12">
      <div class="col-lg-6 text-start text-sm-center text-lg-start">
        <h3 class="cta-title text-primary fw-bold mb-1">Pronto para transformar sua empresa?</h3>
        <h5 class="text-body mb-8">Comece agora seu teste grátis de 30 dias e sinta a diferença.</h5>
        <a href="{{ url('/register') }}" class="btn btn-lg btn-primary">Quero Começar</a>
      </div>
      <div class="col-lg-6 pt-lg-12 text-center text-lg-end">
        <img src="{{ asset('assets/img/front-pages/landing-page/meu-sistema-dark.png') }}" alt="cta dashboard"
          class="img-fluid mt-lg-4" />
      </div>
    </div>
  </div>
</section>
<!-- CTA: End -->

<!-- Contact Us: Start -->
<section id="landingContact" class="section-py bg-body landing-contact">
  <div class="container">
    <div class="text-center mb-4">
      <span class="badge bg-label-primary">Fale Conosco</span>
    </div>
    <h4 class="text-center mb-1">
      <span class="position-relative fw-extrabold z-1">Vamos Crescer
        <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}" alt="icone de destaque da secao"
          class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
      </span>
      Juntos
    </h4>
    <p class="text-center mb-12 pb-md-4">Dúvidas sobre o sistema? Nossa equipe de especialistas está pronta para ajudar.</p>
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
                    <p class="mb-0">E-mail</p>
                    <h6 class="mb-0"><a href="mailto:suporte@ghotme.com.br" class="text-heading">suporte@ghotme.com.br</a>
                    </h6>
                  </div>
                </div>
              </div>
              <!-- <div class="col-md-6 col-lg-12 col-xl-6">
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-success rounded p-1_5 me-3"><i
                        class="ti tabler-phone-call icon-lg"></i></div>
                    <div>
                      <p class="mb-0">Telefone</p>
                      <h6 class="mb-0"><a href="tel:+551199999999" class="text-heading">(11) 99999-9999</a></h6>
                    </div>
                  </div>
                </div> -->
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="card h-100">
          <div class="card-body">
            <h4 class="mb-2">Envie uma mensagem</h4>
            <p class="mb-6">
              Ficou com alguma dúvida sobre planos, funcionalidades ou deseja uma demonstração personalizada? Preencha os campos abaixo.
            </p>
            <form>
              <div class="row g-4">
                <div class="col-md-6">
                  <label class="form-label" for="contact-form-fullname">Nome Completo</label>
                  <input type="text" class="form-control" id="contact-form-fullname" placeholder="Seu nome" />
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="contact-form-email">E-mail</label>
                  <input type="text" id="contact-form-email" class="form-control" placeholder="seu@email.com" />
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="contact-form-phone">WhatsApp / Telefone</label>
                  <input type="text" id="contact-form-phone" class="form-control" placeholder="(00) 00000-0000" />
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="contact-form-subject">Assunto</label>
                  <select id="contact-form-subject" class="form-select">
                    <option selected disabled value="">Selecione...</option>
                    <option value="sales">Comercial / Vendas</option>
                    <option value="support">Suporte Técnico</option>
                    <option value="partnership">Parcerias</option>
                    <option value="other">Outros</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label" for="contact-form-message">Mensagem</label>
                  <textarea id="contact-form-message" class="form-control" rows="5"
                    placeholder="Olá, gostaria de saber mais sobre o plano Enterprise..."></textarea>
                </div>
                <div class="col-12">
                  <button type="submit" class="btn btn-primary">Enviar Mensagem</button>
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