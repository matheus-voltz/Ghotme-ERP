@php
use Illuminate\Support\Facades\Route;
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Login')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<style>
  /* Custom Login Page Styles */
  .authentication-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
  }

  /* Left Side Animation */
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

  /* Right Side Form Animation */
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

  /* Input Fields Polish */
  .form-control:focus,
  .input-group:focus-within .form-control,
  .input-group:focus-within .input-group-text {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.15);
  }

  /* Button Polish */
  .btn-primary {
    background: linear-gradient(45deg, var(--bs-primary), #696cff);
    /* Adjust gradient based on theme */
    border: none;
    transition: all 0.3s ease;
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(var(--bs-primary-rgb), 0.4);
  }

  /* Logo Animation */
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
        <img src="{{ asset('assets/img/illustrations/auth-login-illustration-' . $configData['theme'] . '.png') }}"
          alt="auth-login-cover" class="my-5 auth-illustration"
          data-app-light-img="illustrations/auth-login-illustration-light.png"
          data-app-dark-img="illustrations/auth-login-illustration-dark.png" />
        <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $configData['theme'] . '.png') }}"
          alt="auth-login-cover" class="platform-bg" data-app-light-img="illustrations/bg-shape-image-light.png"
          data-app-dark-img="illustrations/bg-shape-image-dark.png" />
      </div>
    </div>
    <!-- /Left Text -->

    <!-- Login -->
    <div class="d-flex col-12 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
      <div class="w-px-400 mx-auto mt-12 pt-5">
        <h4 class="mb-1">Bem-vindo ao {{ config('variables.templateName') }}! 👋</h4>
        <p class="mb-6">Acesse sua conta e gerencie seu negócio com facilidade.</p>

        @if (session('status'))
        <div class="alert alert-success mb-1 rounded-0" role="alert">
          <div class="alert-body">
            {{ session('status') }}
          </div>
        </div>
        @endif
        <form id="formAuthentication" class="mb-6" action="{{ route('login') }}" method="POST">
          @csrf
          <div class="mb-6">
            <label for="login-email" class="form-label">E-mail</label>
            <input type="text" class="form-control @error('email') is-invalid @enderror" id="login-email" name="email"
              placeholder="seu@email.com" autofocus value="{{ old('email') }}" />
            @error('email')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>
          <div class="mb-6 form-password-toggle">
            <label class="form-label" for="login-password">Senha</label>
            <div class="input-group input-group-merge @error('password') is-invalid @enderror">
              <input type="password" id="login-password" class="form-control @error('password') is-invalid @enderror"
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
          <div class="my-8">
            <div class="d-flex justify-content-between">
              <div class="form-check mb-0 ms-2">
                <input class="form-check-input" type="checkbox" id="remember-me" name="remember"
                  {{ old('remember') ? 'checked' : '' }} />
                <label class="form-check-label" for="remember-me"> Manter conectado </label>
              </div>
              @if (Route::has('password.request'))
              <a href="{{ route('password.request') }}">
                <p class="mb-0">Esqueceu a senha?</p>
              </a>
              @endif
            </div>
          </div>
          <button class="btn btn-primary d-grid w-100 shadow-primary" type="submit">Entrar</button>
        </form>

        <p class="text-center">
          <span>Ainda não tem conta?</span>
          @if (Route::has('register'))
          <a href="{{ route('register') }}">
            <span>Cadastre-se grátis</span>
          </a>
          @endif
        </p>
      </div>
    </div>
    <!-- /Login -->
  </div>
</div>
@endsection