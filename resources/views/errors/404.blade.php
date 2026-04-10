@php
  $customizerHidden = 'customizer-hide';
  $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', '404 - Not Found')

@section('page-style')
<style>
  body {
    background: #0f172a; /* Fundo escuro profundo */
    color: #fff;
    margin: 0;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Public Sans', sans-serif;
    overflow: hidden;
  }

  .error-wrap {
    text-align: center;
    position: relative;
  }

  /* Efeito Glitch no 404 */
  .glitch {
    font-size: 18rem;
    font-weight: 900;
    position: relative;
    text-shadow: 0.05em 0 0 #ff00ff, -0.025em -0.05em 0 #00ffff,
                 0.025em 0.05em 0 #ff0000;
    animation: glitch 1s infinite;
    user-select: none;
    line-height: 1;
  }

  .glitch span {
    position: absolute;
    top: 0;
    left: 0;
  }

  .not-found {
    font-size: 1.5rem;
    letter-spacing: 15px;
    text-transform: uppercase;
    margin-top: -20px;
    color: #94a3b8;
    font-weight: 300;
    animation: pulse 2s infinite;
  }

  @keyframes glitch {
    0% {
      text-shadow: 0.05em 0 0 #ff00ff, -0.025em -0.05em 0 #00ffff,
                   0.025em 0.05em 0 #ff0000;
    }
    14% {
      text-shadow: 0.05em 0 0 #ff00ff, -0.025em -0.05em 0 #00ffff,
                   0.025em 0.05em 0 #ff0000;
    }
    15% {
      text-shadow: -0.05em -0.025em 0 #ff00ff, 0.025em 0.025em 0 #00ffff,
                   -0.05em -0.05em 0 #ff0000;
    }
    49% {
      text-shadow: -0.05em -0.025em 0 #ff00ff, 0.025em 0.025em 0 #00ffff,
                   -0.05em -0.05em 0 #ff0000;
    }
    50% {
      text-shadow: 0.025em 0.05em 0 #ff00ff, 0.05em 0 0 #00ffff,
                   0.05em -0.05em 0 #ff0000;
    }
    99% {
      text-shadow: 0.025em 0.05em 0 #ff00ff, 0.05em 0 0 #00ffff,
                   0.05em -0.05em 0 #ff0000;
    }
    100% {
      text-shadow: -0.025em 0 0 #ff00ff, -0.025em -0.025em 0 #00ffff,
                   -0.025em -0.05em 0 #ff0000;
    }
  }

  @keyframes pulse {
    0%, 100% { opacity: 0.5; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.05); }
  }

  .back-home {
    margin-top: 50px;
    display: inline-block;
    color: #7367f0;
    text-decoration: none;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    border: 1px solid #7367f0;
    padding: 10px 25px;
    border-radius: 4px;
    transition: all 0.3s;
  }

  .back-home:hover {
    background: #7367f0;
    color: #fff;
    box-shadow: 0 0 20px rgba(115, 103, 240, 0.5);
  }
</style>
@endsection

@section('content')
<div class="error-wrap">
  <div class="glitch" data-text="404">404</div>
  <div class="not-found">{{ __('Página Não Encontrada') }}</div>
  
  <a href="{{ url('/') }}" class="back-home">
    {{ __('Voltar ao Início') }}
  </a>
</div>
@endsection
