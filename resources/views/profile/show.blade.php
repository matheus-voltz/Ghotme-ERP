@extends('layouts.layoutMaster')

@php
$breadcrumbs = [['link' => 'home', 'name' => 'Início'], ['link' => 'javascript:void(0)', 'name' => 'Usuário'], ['name' =>
'Perfil']];
@endphp

@section('title', 'Perfil')


@section('content')

@if (Laravel\Fortify\Features::canUpdateProfileInformation())
<div class="nav-align-top">
  <ul class="nav nav-pills flex-column flex-md-row mb-6">
    <li class="nav-item">
      <a class="nav-link active" href="#"><i class="icon-base ti tabler-users icon-sm me-1_5"></i> Minha Conta</a>
    </li>
    @if (auth()->user()->role === 'admin')
    <li class="nav-item">
      <a class="nav-link" href="{{ route('settings') }}"><i class="icon-base ti tabler-bookmark icon-sm me-1_5"></i> Faturamento & Planos</a>
    </li>
    @endif
  </ul>
</div>
<div class="mb-6">
  @livewire('profile.update-profile-information-form')
</div>
@endif

@if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
<div class="mb-6">
  @livewire('profile.update-password-form')
</div>
@endif

@if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
<div class="mb-6">
  @livewire('profile.two-factor-authentication-form')
</div>
@endif

<div class="mb-6">
  @livewire('profile.logout-other-browser-sessions-form')
</div>

@if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
@livewire('profile.delete-user-form')
@endif

@endsection