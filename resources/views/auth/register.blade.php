@php
use Illuminate\Support\Facades\Route;
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Register Page')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<style>
  /* Reuse Custom Styles from Login */
  .authentication-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .auth-cover-bg .auth-illustration {
    animation: slideInLeft 1s ease-out;
  }

  @keyframes slideInLeft {
    from {
      opacity: 0;
      transform: translateX(-50px);
    }

    to {
      opacity: 1;
      transform: translateX(0);
    }
  }

  .authentication-bg {
    background-color: var(--bs-card-bg);
  }

  .w-px-400 {
    animation: fadeInUp 0.8s ease-out;
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .form-control:focus,
  .input-group:focus-within .form-control,
  .input-group:focus-within .input-group-text {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.15);
  }

  .btn-primary {
    background: linear-gradient(45deg, var(--bs-primary), #696cff);
    border: none;
    transition: all 0.3s ease;
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(var(--bs-primary-rgb), 0.4);
  }

  .app-brand-logo {
    animation: float 6s ease-in-out infinite;
  }

  @keyframes float {
    0% {
      transform: translateY(0px);
    }

    50% {
      transform: translateY(-6px);
    }

    100% {
      transform: translateY(0px);
    }
  }
</style>

<div class="authentication-wrapper authentication-cover">
  <!-- Logo -->
  <a href="{{ url('/') }}" class="app-brand auth-cover-brand">
    <span class="app-brand-logo demo">@include('_partials.macros')</span>
    <span class="app-brand-text demo text-heading fw-bold">{{ config('variables.templateName') }}</span>
  </a>
  <!-- /Logo -->
  <div class="authentication-inner row m-0">
    <!-- /Left Text -->
    <div class="d-none d-xl-flex col-xl-8 p-0">
      <div class="auth-cover-bg d-flex justify-content-center align-items-center">
        <img src="{{ asset('assets/img/illustrations/auth-register-illustration-' . $configData['theme'] . '.png') }}"
          alt="auth-register-cover" class="my-5 auth-illustration"
          data-app-light-img="illustrations/auth-register-illustration-light.png"
          data-app-dark-img="illustrations/auth-register-illustration-dark.png" />
        <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $configData['theme'] . '.png') }}"
          alt="auth-register-cover" class="platform-bg" data-app-light-img="illustrations/bg-shape-image-light.png"
          data-app-dark-img="illustrations/bg-shape-image-dark.png" />
      </div>
    </div>
    <!-- /Left Text -->

    <!-- Register -->
    <div class="d-flex col-12 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
      <div class="w-px-400 mx-auto mt-12 pt-5">
        <h4 class="mb-1">Comece sua jornada 🚀</h4>
        <p class="mb-6">Crie sua conta em segundos e transforme sua gestão.</p>

        <form id="formAuthentication" class="mb-6" action="{{ route('register') }}" method="POST">
          @csrf
          <div class="mb-6">
            <label for="username" class="form-label">Nome Completo</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="username" name="name"
              placeholder="Seu nome" autofocus value="{{ old('name') }}" />
            @error('name')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>

          <div class="mb-6">
            <label for="company_name" class="form-label">Nome da Empresa</label>
            <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name"
              placeholder="Ex: Oficina Mecânica Silva" value="{{ old('company_name') }}" />
            @error('company_name')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>

          <div class="mb-6">
            <label for="niche" class="form-label">Segmento (Nicho)</label>
            <select class="form-select @error('niche') is-invalid @enderror" id="niche" name="niche">
              <option value="" disabled {{ old('niche') ? '' : 'selected' }}>Selecione o seu segmento</option>
              @foreach(config('niche.niches') as $key => $niche)
              @if($key !== 'workshop')
              <option value="{{ $key }}" {{ old('niche') == $key ? 'selected' : '' }}>
                {{ $niche['labels']['entities'] ?? ucfirst($key) }}
              </option>
              @endif
              @endforeach
            </select>
            @error('niche')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>

          <div class="mb-6">
            <label for="cnpj" class="form-label">CNPJ da Empresa</label>
            <input type="text" class="form-control @error('cnpj') is-invalid @enderror" id="cnpj" name="cnpj"
              placeholder="00.000.000/0000-00" value="{{ old('cnpj') }}" maxlength="18" />
            @error('cnpj')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>

          <div class="mb-6">
            <label for="contact_number" class="form-label">WhatsApp / Celular</label>
            <input type="text" class="form-control @error('contact_number') is-invalid @enderror" id="contact_number" name="contact_number"
              placeholder="(00) 00000-0000" value="{{ old('contact_number') }}" />
            @error('contact_number')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>

          <div class="mb-6">
            <label for="email" class="form-label">E-mail</label>
            <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
              placeholder="seu@email.com" value="{{ old('email') }}" />
            @error('email')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>
          <div class="mb-6 form-password-toggle">
            <label class="form-label" for="password">Senha</label>
            <div class="input-group input-group-merge @error('password') is-invalid @enderror">
              <input type="password" id="password" class="form-control @error('password') is-invalid @enderror"
                name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                aria-describedby="password" />
              <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
            </div>
            @error('password')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>
          <div class="mb-6 form-password-toggle">
            <label class="form-label" for="password-confirm">Confirmar Senha</label>
            <div class="input-group input-group-merge">
              <input type="password" id="password-confirm" class="form-control" name="password_confirmation"
                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                aria-describedby="password" />
              <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
            </div>
          </div>
          @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
          <div class="mb-6 mt-8">
            <div class="form-check mb-8 ms-2 @error('terms') is-invalid @enderror">
              <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" id="terms"
                name="terms" />
              <label class="form-check-label" for="terms">
                Eu concordo com a
                <a href="{{ route('policy.show') }}" target="_blank">Política de Privacidade</a> e os
                <a href="{{ route('terms.show') }}" target="_blank">Termos de Uso</a>.
              </label>
            </div>
            @error('terms')
            <div class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </div>
            @enderror
          </div>
          @endif
          <button type="submit" class="btn btn-primary d-grid w-100 shadow-primary">Cadastrar</button>
        </form>

        <p class="text-center">
          <span>Já tem uma conta?</span>
          @if (Route::has('login'))
          <a href="{{ route('login') }}">
            <span>Faça Login</span>
          </a>
          @endif
        </p>
      </div>
    </div>
    <!-- /Register -->
  </div>
</div>
@endsection