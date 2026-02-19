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
    top: -2px; left: -2px; right: -2px; bottom: -2px;
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
    0% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-10px) rotate(5deg); }
    100% { transform: translateY(0px) rotate(0deg); }
  }

  @keyframes propulsion {
    0% { transform: translateY(0px) rotate(0deg); }
    25% { transform: translateY(-5px) rotate(-2deg); }
    50% { transform: translateY(0px) rotate(0deg); }
    75% { transform: translateY(-5px) rotate(2deg); }
    100% { transform: translateY(0px) rotate(0deg); }
  }

  @keyframes rocketLaunch {
    0% { transform: translateY(0) scale(1); filter: drop-shadow(0 0 0px rgba(115, 103, 240, 0)); }
    50% { transform: translateY(-15px) scale(1.1); filter: drop-shadow(0 15px 20px rgba(115, 103, 240, 0.4)); }
    100% { transform: translateY(0) scale(1); filter: drop-shadow(0 0 0px rgba(115, 103, 240, 0)); }
  }

  .animate-float { animation: float 4s ease-in-out infinite; }
  .animate-propulsion { animation: propulsion 2s ease-in-out infinite; }
  .animate-rocket { animation: rocketLaunch 3s ease-in-out infinite; }
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
          <h1 class="text-primary hero-title display-6 fw-extrabold text-uppercase">O ERP que se molda ao seu negócio</h1>
          <h2 class="hero-sub-title h6 mb-6">
            O único sistema inteligente que <strong>fala a sua língua</strong>. <br class="d-none d-lg-block" />
            Nossa tecnologia adapta termos, ícones e processos instantaneamente para a realidade do seu segmento, seja ele qual for.
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
            <i class="ti tabler-rocket icon-xl mb-3"></i>
          </div>
          <h5 class="mb-2">Agilidade na Operação</h5>
          <p class="features-icon-description">Abra Ordens de Serviço em segundos e acompanhe o status em tempo real pelo celular.</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="mb-4 text-primary text-center">
            <i class="ti tabler-shield-check icon-xl mb-3"></i>
          </div>
          <h5 class="mb-2">Estoque Inteligente</h5>
          <p class="features-icon-description">Evite prejuízos com alertas de estoque mínimo e pedidos de compra automáticos por fornecedor.</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="mb-4 text-primary text-center">
            <i class="ti tabler-chart-arrows icon-xl mb-3"></i>
          </div>
          <h5 class="mb-2">Lucratividade Real</h5>
          <p class="features-icon-description">Saiba exatamente quanto ganhou em cada serviço, descontando o custo real das peças e insumos.</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="mb-4 text-primary text-center">
            <i class="ti tabler-receipt-tax icon-xl mb-3"></i>
          </div>
          <h5 class="mb-2">Fiscal e Contábil sem Medo</h5>
          <p class="features-icon-description">Emissão de Notas Fiscais (NFe/NFSe) integrada e portal exclusivo para o seu contador.</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="mb-4 text-primary text-center">
            <i class="ti tabler-device-mobile-message icon-xl mb-3"></i>
          </div>
          <h5 class="mb-2">WhatsApp como Aliado</h5>
          <p class="features-icon-description">Envie orçamentos para aprovação e avisos de "serviço pronto" automaticamente via WhatsApp.</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="mb-4 text-primary text-center">
            <i class="ti tabler-building-bank icon-xl mb-3"></i>
          </div>
          <h5 class="mb-2">Conciliação Bancária</h5>
          <p class="features-icon-description">Importe seu arquivo OFX e deixe o Ghotme bater os saldos bancários com o seu financeiro.</p>
        </div>
      </div>
    </div>
  </section>
  <!-- Useful features: End -->

  <!-- Multi-Niche: Start -->
  <section id="landingNiches" class="section-py bg-body">
    <div class="container">
      <div class="text-center mb-4">
        <span class="badge bg-label-primary">Feito para você</span>
      </div>
      <h4 class="text-center mb-1">
        Um sistema, <span class="fw-extrabold">infinitas possibilidades</span>
      </h4>
      <p class="text-center mb-12">Veja como o Ghotme se transforma para atender as necessidades específicas de cada mercado:</p>
      <div class="row g-6">
        <div class="col-md-4">
          <div class="card niche-card shadow-sm text-center p-5 h-100">
            <div class="avatar avatar-xl mx-auto mb-4 bg-label-info rounded-circle">
                <i class="ti tabler-settings-automation fs-1"></i>
            </div>
            <h5 class="fw-extrabold mb-3">Segmento Automotivo</h5>
            <p class="text-muted small mb-0">Gestão de placas, chassi e histórico por veículos para Oficinas e Estéticas.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card niche-card shadow-sm text-center p-5 h-100">
            <div class="avatar avatar-xl mx-auto mb-4 bg-label-success rounded-circle">
                <i class="ti tabler-heart-handshake fs-1"></i>
            </div>
            <h5 class="fw-extrabold mb-3">Saúde e Bem-estar</h5>
            <p class="text-muted small mb-0">Rótulos dinâmicos para Pets, Pacientes e controle de sessões para Clínicas e Pet Shops.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card niche-card shadow-sm text-center p-5 h-100">
            <div class="avatar avatar-xl mx-auto mb-4 bg-label-warning rounded-circle">
                <i class="ti tabler-device-laptop fs-1"></i>
            </div>
            <h5 class="fw-extrabold mb-3">Tecnologia e Varejo</h5>
            <p class="text-muted small mb-0">Foco em Serial, Modelo e garantia para Assistências Técnicas e Lojas em geral.</p>
          </div>
        </div>
      </div>
      <div class="text-center mt-8">
          <p class="fw-medium text-primary">E muito mais... O Ghotme é configurável para qualquer prestador de serviço!</p>
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
              <p class="text-center mb-5 text-body">
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
              <p class="text-center mb-5 text-body">
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
              <p class="text-center mb-5 text-body">
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
              <p class="text-center mb-5 text-body">
                Começar a usar o Ghotme é simples, rápido e sem burocracia.
              </p>
              <div class="row gy-4 justify-content-center mb-5">
                <div class="col-lg-3 col-md-6">
                  <div class="text-center position-relative">
                    <div class="avatar avatar-xl border border-primary border-2 rounded-circle mx-auto mb-3">
                      <span class="avatar-initial rounded-circle bg-primary text-white">1</span>
                    </div>
                    <h5 class="mb-2 text-heading">Crie sua Conta</h5>
                    <p class="text-body">Escolha o plano ideal e faça seu cadastro em menos de 2 minutos.</p>
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
                    <h5 class="mb-2 text-heading">Configure Fácil</h5>
                    <p class="text-body">Cadastre seus dados ou importe clientes e produtos de planilhas.</p>
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
                    <h5 class="mb-2 text-heading">Gerencie Tudo</h5>
                    <p class="text-body">Emita notas, controle estoque e financeiro em uma única tela.</p>
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
                    <h5 class="mb-2 text-heading">Veja os Resultados</h5>
                    <p class="text-body">Acompanhe relatórios de crescimento e tome decisões inteligentes.</p>
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
                class="mb-8 pb-2 animate-float" />
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
        <div class="card pricing-card-popular shadow-xl">
          <div class="card-header">
            <div class="text-center position-relative">
              <div class="position-absolute end-0 top-0 mt-n2">
                <span class="badge bg-label-primary rounded-1">Popular</span>
              </div>
              <img src="{{ asset('assets/img/front-pages/icons/plane.png') }}" alt="plane icon" class="mb-8 pb-2 animate-propulsion" />
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
                  <strong>Até 3 usuários</strong> e suporte completo
                </h6>
              </li>
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  <strong>Multi-Nicho:</strong> O sistema se adapta ao seu negócio
                </h6>
              </li>
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  <strong>Agendamento Online via Site:</strong> Venda mais!
                </h6>
              </li>
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  <strong>PIX Dinâmico</strong> com baixa automática (Asaas)
                </h6>
              </li>
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  <strong>Conciliação Bancária (OFX):</strong> Economize horas
                </h6>
              </li>
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  Integração via WhatsApp e Relatórios de Lucro
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
                class="mb-8 pb-2 animate-rocket" />
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
                  <strong>Tudo do Plano Padrão +</strong>
                </h6>
              </li>
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  <strong>Até 10 usuários</strong> e suporte prioritário
                </h6>
              </li>
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  <strong>Suprimento Automático:</strong> Robô de reposição
                </h6>
              </li>
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  <strong>BPO Financeiro</strong> e Portal do Contador
                </h6>
              </li>
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  <strong>Emissão de Notas Fiscais</strong> ilimitada (NFe/NFSe)
                </h6>
              </li>
              <li>
                <h6 class="d-flex align-items-center mb-3">
                  <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                      class="ti tabler-check icon-12px"></i></span>
                  Gestão de Frotas e B2B avançado
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
            <h5 class="mb-2">Suporte Humanizado</h5>
            <p class="text-muted mb-0">Atendimento rápido e direto com especialistas. Nada de robôs.</p>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card border border-success shadow-none h-100 diferencial-card hover-effect-success">
          <div class="card-body text-center">
            <div class="mb-4 text-success icon-box">
              <i class="ti tabler-lock-open icon-xl"></i>
            </div>
            <h5 class="mb-2">Sem Fidelidade</h5>
            <p class="text-muted mb-0">Total liberdade para cancelar quando quiser, sem multas ou burocracia.</p>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card border border-info shadow-none h-100 diferencial-card hover-effect-info">
          <div class="card-body text-center">
            <div class="mb-4 text-info icon-box">
              <i class="ti tabler-refresh icon-xl"></i>
            </div>
            <h5 class="mb-2">Sempre Atualizado</h5>
            <p class="text-muted mb-0">Novas funcionalidades e melhorias constantes sem custo adicional.</p>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card border border-warning shadow-none h-100 diferencial-card hover-effect-warning">
          <div class="card-body text-center">
            <div class="mb-4 text-warning icon-box">
              <i class="ti tabler-shield-lock icon-xl"></i>
            </div>
            <h5 class="mb-2">Dados Seguros</h5>
            <p class="text-muted mb-0">Segurança de nível bancário e backups diários automáticos.</p>
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
          <!-- Pergunta 1: Multi-nicho -->
          <div class="card accordion-item active">
            <h2 class="accordion-header" id="headingOne">
              <button type="button" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#accordionOne"
                aria-expanded="true" aria-controls="accordionOne">
                <i class="ti tabler-adjustments-alt me-2 text-primary"></i> O Ghotme realmente se adapta ao meu nicho?
              </button>
            </h2>
            <div id="accordionOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                Com certeza! Ao se cadastrar, você escolhe seu segmento (Oficina, Pet Shop, Assistência, etc) e o sistema muda automaticamente todos os termos, ícones e processos para falar a sua língua. É um sistema feito sob medida para você.
              </div>
            </div>
          </div>

          <!-- Pergunta 2: Pagamentos e Bitcoin -->
          <div class="card accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                data-bs-target="#accordionTwo" aria-expanded="false" aria-controls="accordionTwo">
                <i class="ti tabler-currency-bitcoin me-2 text-warning"></i> Quais formas de pagamento são aceitas?
              </button>
            </h2>
            <div id="accordionTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
              data-bs-parent="#accordionExample">
              <div class="accordion-body">
                Aceitamos cartões de crédito, boleto bancário e o inovador <strong>PIX Dinâmico</strong> com baixa automática. Além disso, o Ghotme já está preparado para o futuro e permite o recebimento através de <strong>Bitcoin</strong>.
              </div>
            </div>
          </div>

          <!-- Pergunta 3: Contabilidade/BPO -->
          <div class="card accordion-item">
            <h2 class="accordion-header" id="headingThree">
              <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                data-bs-target="#accordionThree" aria-expanded="false" aria-controls="accordionThree">
                <i class="ti tabler-user-search me-2 text-info"></i> Meu contador terá acesso ao sistema?
              </button>
            </h2>
            <div id="accordionThree" class="accordion-collapse collapse" aria-labelledby="headingThree"
              data-bs-parent="#accordionExample">
              <div class="accordion-body">
                Sim! Criamos um <strong>Portal do Contador</strong> exclusivo. Ele poderá baixar seus XMLs, conferir seu faturamento e realizar a auditoria financeira (BPO) sem que você precise enviar pilhas de papéis todo mês.
              </div>
            </div>
          </div>

          <!-- Pergunta 4: Conciliação Bancária -->
          <div class="card accordion-item">
            <h2 class="accordion-header" id="headingFour">
              <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                data-bs-target="#accordionFour" aria-expanded="false" aria-controls="accordionFour">
                <i class="ti tabler-refresh me-2 text-success"></i> Como funciona a conciliação bancária?
              </button>
            </h2>
            <div id="accordionFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
              data-bs-parent="#accordionExample">
              <div class="accordion-body">
                Basta importar o arquivo OFX do seu banco e o Ghotme faz o "match" automático com suas vendas e despesas. Você economiza horas de trabalho manual e evita erros de digitação no seu financeiro.
              </div>
            </div>
          </div>

          <!-- Pergunta 5: Suporte -->
          <div class="card accordion-item">
            <h2 class="accordion-header" id="headingFive">
              <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                data-bs-target="#accordionFive" aria-expanded="false" aria-controls="accordionFive">
                <i class="ti tabler-headset me-2 text-danger"></i> Terei suporte para configurar o sistema?
              </button>
            </h2>
            <div id="accordionFive" class="accordion-collapse collapse" aria-labelledby="headingFive"
              data-bs-parent="#accordionExample">
              <div class="accordion-body">
                Sim! Oferecemos suporte humanizado via WhatsApp e chat interno. Nossa equipe ajuda você na importação de dados e na configuração inicial para que você comece a faturar o quanto antes.
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
        <h3 class="cta-title text-primary fw-extrabold mb-2 display-5">Faça sua empresa <span class="text-dark">decolar hoje!</span></h3>
        <h5 class="text-body mb-8 opacity-75">Junte-se a centenas de empreendedores que automatizaram sua gestão com o Ghotme. <strong>Teste grátis por 30 dias</strong>, sem compromisso.</h5>
        <div class="d-flex flex-column flex-sm-row gap-4 justify-content-sm-center justify-content-lg-start">
            <a href="{{ url('/register') }}" class="btn btn-lg btn-primary shadow-lg px-5 py-3">
                Começar Agora Gratuitamente <i class="ti tabler-rocket ms-2"></i>
            </a>
            <a href="#landingFeatures" class="btn btn-lg btn-outline-secondary px-5 py-3">
                Ver Funcionalidades
            </a>
        </div>
      </div>
      <div class="col-lg-6 pt-lg-12 text-center text-lg-end">
        <img src="{{ asset('assets/img/front-pages/landing-page/meu-sistema-dark.png') }}" alt="cta dashboard"
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