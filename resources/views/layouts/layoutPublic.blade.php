@extends('layouts/commonMaster')

@section('layoutContent')
<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-4">
      <!-- Logo -->
      <div class="app-brand justify-content-center mb-4">
        <a href="{{ url('/') }}" class="app-brand-link gap-2">
          <span class="app-brand-logo demo">@include('_partials.macros')</span>
          <span class="app-brand-text demo text-body fw-bold">{{ config('variables.templateName') }}</span>
        </a>
      </div>
      <!-- /Logo -->

      @yield('content')

    </div>
  </div>
</div>
@endsection
