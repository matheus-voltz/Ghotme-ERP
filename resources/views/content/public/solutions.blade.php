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

@section('title', $marketingData['hero_title'])

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/front-page-landing.scss'])
<style>
    .solution-hero {
        background: linear-gradient(72.47deg, #7367f0 22.16%, #4831d4 76.47%);
        padding: 140px 0 100px 0;
        color: white;
        border-radius: 0 0 5rem 5rem;
        position: relative;
        overflow: hidden;
    }
    .solution-hero::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url("{{ asset('assets/img/front-pages/backgrounds/hero-bg-light.png') }}");
        background-size: cover;
        opacity: 0.1;
    }
    .feature-card {
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 1.5rem;
        background: white;
    }
    .feature-card:hover {
        transform: translateY(-12px);
        box-shadow: 0 30px 60px rgba(115, 103, 240, 0.15);
        border-color: #7367f0;
    }
    .icon-wrapper {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 1rem;
        margin-bottom: 1.5rem;
    }
    .niche-badge {
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(10px);
        color: white;
        padding: 8px 20px;
        border-radius: 50px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.75rem;
        border: 1px solid rgba(255,255,255,0.2);
    }
    .text-gradient {
        background: linear-gradient(135deg, #7367f0 0%, #4831d4 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .animate-float {
        animation: floatMobile 4s ease-in-out infinite;
    }
    @keyframes floatMobile {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }
</style>
@endsection

@section('content')
<!-- Hero Section -->
<section class="solution-hero mb-12">
    <div class="container text-center position-relative z-1">
        <span class="niche-badge mb-5 d-inline-block">{{ str_replace('_', ' ', $slug) }}</span>
        <h1 class="display-2 fw-extrabold text-white mb-6">{{ $marketingData['hero_title'] }}</h1>
        <p class="lead text-white opacity-90 mb-10 mx-auto fs-4" style="max-width: 800px; line-height: 1.6;">
            {{ $marketingData['hero_subtitle'] }}
        </p>
        <div class="d-flex justify-content-center gap-4 flex-wrap">
            <a href="{{ route('auth-register-basic') }}" class="btn btn-white btn-xl text-primary fw-bold shadow-lg py-4 px-10">
                Começar agora <i class="ti tabler-arrow-right ms-2"></i>
            </a>
            <a href="#features" class="btn btn-outline-white btn-xl py-4 px-10">Explorar ferramentas</a>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section id="features" class="py-12">
    <div class="container">
        <div class="text-center mb-12">
            <h2 class="display-5 fw-bold mb-4">Desenvolvido para <span class="text-gradient">sua rotina</span></h2>
            <p class="text-muted fs-5 mx-auto" style="max-width: 600px;">Descubra como o Ghotme automatiza as tarefas que mais consomem seu tempo.</p>
        </div>
        
        <div class="row g-8">
            @foreach($marketingData['features'] as $index => $feature)
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 feature-card p-6 border-0 shadow-sm">
                    <div class="card-body">
                        @php
                            $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
                            $color = $colors[$index % 6];
                        @endphp
                        <div class="icon-wrapper bg-label-{{ $color }}">
                            <i class="ti {{ str_contains($feature['icon'], 'tabler-') ? $feature['icon'] : 'tabler-'.$feature['icon'] }} fs-1"></i>
                        </div>
                        <h4 class="fw-bold mb-4">{{ $feature['title'] }}</h4>
                        <p class="text-muted mb-0 fs-6" style="line-height: 1.7;">{{ $feature['description'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Pain Point Deep Dive -->
<section class="bg-label-primary py-16 mt-12 overflow-hidden">
    <div class="container">
        <div class="row align-items-center gy-10">
            <div class="col-lg-6 position-relative">
                <div class="badge bg-primary mb-4">A SOLUÇÃO DEFINITIVA</div>
                <h2 class="display-5 fw-bold mb-6">Chega de lutar contra a <span class="text-primary">desorganização</span></h2>
                <p class="fs-4 text-heading mb-8" style="line-height: 1.6;">
                    {{ $marketingData['pain_point'] }}
                </p>
                <div class="row g-4">
                    <div class="col-6">
                        <div class="d-flex align-items-center"><i class="ti tabler-circle-check-filled text-success fs-3 me-2"></i> <span class="fw-medium">Fácil de usar</span></div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center"><i class="ti tabler-circle-check-filled text-success fs-3 me-2"></i> <span class="fw-medium">Suporte via Whats</span></div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center"><i class="ti tabler-circle-check-filled text-success fs-3 me-2"></i> <span class="fw-medium">Nuvem 24/7</span></div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center"><i class="ti tabler-circle-check-filled text-success fs-3 me-2"></i> <span class="fw-medium">IA Integrada</span></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <div class="position-relative d-inline-block">
                    <img src="{{ asset('assets/img/front-pages/landing-page/hero-dashboard-light.png') }}" class="img-fluid shadow-2xl rounded-4 position-relative z-1 animate-float" style="max-width: 90%;" alt="Dashboard Ghotme">
                    <div class="position-absolute top-50 start-50 translate-middle bg-primary opacity-10 rounded-circle" style="width: 500px; height: 500px; filter: blur(100px);"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Final CTA -->
<section class="container py-20 text-center">
    <div class="card border-0 bg-dark text-white p-12 rounded-5 overflow-hidden position-relative shadow-2xl">
        <div class="position-relative z-1 py-6">
            <h2 class="display-4 text-white fw-bold mb-6">{{ $marketingData['cta_text'] ?? 'Pronto para começar?' }}</h2>
            <a href="{{ route('auth-register-basic') }}" class="btn btn-primary btn-xl py-4 px-12 fw-bold rounded-pill">Criar minha conta grátis</a>
        </div>
        <!-- Decorativos -->
        <div class="position-absolute top-0 end-0 mt-n10 me-n10 bg-primary opacity-25 rounded-circle" style="width: 400px; height: 400px; filter: blur(80px);"></div>
        <div class="position-absolute bottom-0 start-0 mb-n10 ms-n10 bg-info opacity-10 rounded-circle" style="width: 300px; height: 300px; filter: blur(60px);"></div>
    </div>
</section>
@endsection
