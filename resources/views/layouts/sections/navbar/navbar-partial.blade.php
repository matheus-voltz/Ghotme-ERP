@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
@endphp

<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if (isset($navbarFull))
<div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4 ms-0">
  <a href="{{ url('/') }}" class="app-brand-link">
    <span class="app-brand-logo demo">@include('_partials.macros')</span>
    <span class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') }}</span>
  </a>

  <!-- Display menu close icon only for horizontal-menu with navbar-full -->
  @if (isset($menuHorizontal))
  <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
    <i class="icon-base ti tabler-x icon-sm d-flex align-items-center justify-content-center"></i>
  </a>
  @endif
</div>
@endif

<!-- ! Not required for layout-without-menu -->
@if (!isset($navbarHideToggle))
<div
  class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
  <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
    <i class="icon-base ti tabler-menu-2 icon-md"></i>
  </a>
</div>
@endif

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
  @if ($configData['hasCustomizer'] == true)
  <!-- Style Switcher -->
  <div class="navbar-nav align-items-center">
    <li class="nav-item dropdown me-2 me-xl-0">
      <a class="nav-link dropdown-toggle hide-arrow" id="nav-theme" href="javascript:void(0);"
        data-bs-toggle="dropdown">
        <i class="icon-base ti tabler-sun icon-md theme-icon-active"></i>
        <span class="d-none ms-2" id="nav-theme-text">Toggle theme</span>
      </a>
      <ul class="dropdown-menu dropdown-menu-start" aria-labelledby="nav-theme-text">
        <li>
          <button type="button" class="dropdown-item align-items-center active" data-bs-theme-value="light"
            aria-pressed="false">
            <span><i class="icon-base ti tabler-sun icon-22px me-3" data-icon="sun"></i>Light</span>
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark" aria-pressed="true">
            <span><i class="icon-base ti tabler-moon-stars icon-22px me-3" data-icon="moon-stars"></i>Dark</span>
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system"
            aria-pressed="false">
            <span><i class="icon-base ti tabler-device-desktop-analytics icon-22px me-3"
                data-icon="device-desktop-analytics"></i>System</span>
          </button>
        </li>
      </ul>
    </li>
  </div>
  <!-- / Style Switcher-->
  @endif

  <ul class="navbar-nav flex-row align-items-center ms-auto">
    <!-- Language -->
    <li class="nav-item dropdown me-2 me-xl-0">
      <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
        <i class="icon-base ti tabler-language icon-md"></i>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <a class="dropdown-item {{ app()->getLocale() === 'pt_BR' ? 'active' : '' }}" href="{{ url('lang/pt_BR') }}">
            <span class="align-middle">Português (Brasil)</span>
          </a>
        </li>
        <li>
          <a class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}" href="{{ url('lang/en') }}">
            <span class="align-middle">English</span>
          </a>
        </li>
        <li>
          <a class="dropdown-item {{ app()->getLocale() === 'es' ? 'active' : '' }}" href="{{ url('lang/es') }}">
            <span class="align-middle">Español</span>
          </a>
        </li>
        <li>
          <a class="dropdown-item {{ app()->getLocale() === 'fr' ? 'active' : '' }}" href="{{ url('lang/fr') }}">
            <span class="align-middle">Français</span>
          </a>
        </li>
      </ul>
    </li>
    <!--/ Language -->

    <!-- Notifications -->
    <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
      <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
        <div class="position-relative">
          <i class="icon-base ti tabler-bell icon-md"></i>
          @if(Auth::user()->unreadNotifications->count() > 0)
          <span class="badge rounded-pill bg-danger badge-dot badge-notifications position-absolute top-0 start-100 translate-middle-x"></span>
          @endif
        </div>
      </a>
      <ul class="dropdown-menu dropdown-menu-end py-0">
        <li class="dropdown-menu-header border-bottom">
          <div class="dropdown-header d-flex align-items-center py-3">
            <h6 class="mb-0 me-auto">Notificações</h6>
            <div class="d-flex align-items-center h6 mb-0">
              <span class="badge bg-label-primary me-2">{{ Auth::user()->unreadNotifications->count() }} Novas</span>
              <a href="javascript:void(0)" class="dropdown-notifications-all mark-as-read-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Marcar todas como lidas">
                <i class="icon-base ti tabler-mail-opened icon-md text-heading"></i>
              </a>
            </div>
          </div>
        </li>
        <li class="dropdown-notifications-list scrollable-container">
          <ul class="list-group list-group-flush">
            @forelse(Auth::user()->unreadNotifications->take(10) as $notification)
            <li class="list-group-item list-group-item-action dropdown-notifications-item" data-id="{{ $notification->id }}">
              <div class="d-flex">
                <div class="flex-shrink-0 me-3">
                  <div class="avatar">
                    <span class="avatar-initial rounded-circle bg-label-success">
                      <i class="icon-base ti tabler-currency-dollar"></i>
                    </span>
                  </div>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-1 small">{{ $notification->data['title'] ?? 'Notificação' }}</h6>
                  <small class="mb-1 d-block text-body">{{ $notification->data['message'] ?? '' }}</small>
                  <div class="d-flex align-items-center justify-content-between">
                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                    @php
                    $url = $notification->data['url'] ?? null;
                    if (isset($notification->data['budget_id'])) {
                    $url = route('budgets.approved');
                    }
                    @endphp
                    @if($url)
                    <a href="{{ $url }}" class="btn btn-xs btn-link p-0">Mais detalhes</a>
                    @endif
                  </div>
                </div>
                <div class="flex-shrink-0 dropdown-notifications-actions">
                  <a href="javascript:void(0)" class="dropdown-notifications-read individual-mark-as-read" title="Marcar como lida">
                    <span class="badge badge-dot"></span>
                  </a>
                </div>
              </div>
            </li>
            @empty
            <li class="list-group-item list-group-item-action dropdown-notifications-item">
              <div class="text-center p-3">
                <small class="text-muted">Nenhuma notificação</small>
              </div>
            </li>
            @endforelse
          </ul>
        </li>
        <li class="border-top">
          <div class="d-grid p-4">
            <a class="btn btn-primary btn-sm d-flex" href="{{ route('notifications.index') }}">
              <small class="align-middle">Ver todas as notificações</small>
            </a>
          </div>
        </li>
      </ul>
    </li>
    <!--/ Notifications -->

    <!-- User -->
    <li class="nav-item navbar-dropdown dropdown-user dropdown">
      <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
        <div class="avatar avatar-online">
          <img src="{{ Auth::user() ? Auth::user()->profile_photo_url : asset('assets/img/avatars/1.png') }}" alt
            class="rounded-circle" />
        </div>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <a class="dropdown-item mt-0"
            href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0 me-2">
                <div class="avatar avatar-online">
                  <img src="{{ Auth::user() ? Auth::user()->profile_photo_url : asset('assets/img/avatars/1.png') }}"
                    alt class="rounded-circle" />
                </div>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">
                  @if (Auth::check())
                  {{ Auth::user()->name }}
                  @else
                  Luke Skywalker
                  @endif
                </h6>
                <small class="text-body-secondary">{{ Auth::user()->role === 'admin' ? 'Administrador' : 'Funcionário' }}</small>
              </div>
            </div>
          </a>
        </li>
        <li>
          <div class="dropdown-divider my-1 mx-n2"></div>
        </li>
        <li>
          <a class="dropdown-item"
            href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
            <i class="icon-base ti tabler-user me-3 icon-md"></i><span class="align-middle">Meu Perfil</span> </a>
        </li>
        @if (Auth::check() && Auth::user()->role === 'admin' && Laravel\Jetstream\Jetstream::hasApiFeatures())
        <li>
          <a class="dropdown-item" href="{{ route('api-tokens.index') }}">
            <i class="icon-base ti tabler-settings me-3 icon-md"></i><span class="align-middle">Tokens de API</span> </a>
        </li>
        @endif
        @if (Auth::user()->role === 'admin')
        <li>
          <a class="dropdown-item" href="{{ route('settings') }}">
            <span class="d-flex align-items-center align-middle">
              <i class="flex-shrink-0 icon-base ti tabler-file-dollar me-3 icon-md"></i><span
                class="flex-grow-1 align-middle">Faturamento</span>
              <span class="flex-shrink-0 badge bg-danger d-flex align-items-center justify-content-center">4</span>
            </span>
          </a>
        </li>
        @endif
        @if (Auth::User() && Laravel\Jetstream\Jetstream::hasTeamFeatures())
        <li>
          <div class="dropdown-divider my-1 mx-n2"></div>
        </li>
        <li>
          <h6 class="dropdown-header">Gerenciar Equipe</h6>
        </li>
        <li>
          <div class="dropdown-divider my-1"></div>
        </li>
        <li>
          <a class="dropdown-item"
            href="{{ Auth::user() ? route('teams.show', Auth::user()->currentTeam->id) : 'javascript:void(0)' }}">
            <i class="icon-base bx bx-cog icon-md me-3"></i><span>Configurações da Equipe</span>
          </a>
        </li>
        @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
        <li>
          <a class="dropdown-item" href="{{ route('teams.create') }}">
            <i class="icon-base bx bx-user icon-md me-3"></i><span>Criar Nova Equipe</span>
          </a>
        </li>
        @endcan
        @if (Auth::user()->allTeams()->count() > 1)
        <li>
          <div class="dropdown-divider my-1"></div>
        </li>
        <li>
          <h6 class="dropdown-header">Trocar de Equipe</h6>
        </li>
        <li>
          <div class="dropdown-divider my-1"></div>
        </li>
        @endif
        @if (Auth::user())
        @foreach (Auth::user()->allTeams() as $team)
        {{-- Below commented code read by artisan command while installing jetstream. !! Do not remove if you want to use jetstream. --}}

        <x-switchable-team :team="$team" />
        @endforeach
        @endif
        @endif
        <li>
          <div class="dropdown-divider my-1 mx-n2"></div>
        </li>
        @if (Auth::check())
        <li>
          <a class="dropdown-item" href="{{ route('logout') }}"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Sair</span>
          </a>
        </li>
        <form method="POST" id="logout-form" action="{{ route('logout') }}">
          @csrf
        </form>
        @else
        <li>
          <div class="d-grid px-2 pt-2 pb-1">
            <a class="btn btn-sm btn-danger d-flex"
              href="{{ Route::has('login') ? route('login') : url('auth/login-basic') }}" target="_blank">
              <small class="align-middle">Entrar</small>
              <i class="icon-base ti tabler-login ms-2 icon-14px"></i>
            </a>
          </div>
        </li>
        @endif
      </ul>
    </li>
    <!--/ User -->
  </ul>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const markAsReadBtn = document.querySelector('.mark-as-read-btn');
    const updateHeaderBadge = (newCount) => {
      const badge = document.querySelector('.badge-notifications');
      const headerBadge = document.querySelector('.dropdown-header .badge');
      if (newCount === 0 && badge) badge.remove();
      if (headerBadge) headerBadge.textContent = `${newCount} Novas`;
    };

    // Marcar todas como lidas
    if (markAsReadBtn) {
      markAsReadBtn.addEventListener('click', function(e) {
        e.preventDefault();
        fetch('{{ route("notifications.mark-as-read") }}', {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Accept': 'application/json'
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              updateHeaderBadge(0);
              const list = document.querySelector('.dropdown-notifications-list ul');
              if (list) {
                list.innerHTML = `
                            <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                <div class="text-center p-3">
                                    <small class="text-muted">Nenhuma notificação nova</small>
                                </div>
                            </li>`;
              }
            }
          });
      });
    }

    // Marcar individual
    document.addEventListener('click', function(e) {
      const individualBtn = e.target.closest('.individual-mark-as-read');
      if (individualBtn) {
        e.preventDefault();
        const item = individualBtn.closest('.dropdown-notifications-item');
        const id = item.dataset.id;

        fetch(`/notifications/${id}/mark-as-read`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Accept': 'application/json'
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              item.remove();

              // Atualizar contadores
              const navCountElem = document.querySelector('.dropdown-header .badge');
              if (navCountElem) {
                const currentCount = parseInt(navCountElem.textContent);
                const newCount = Math.max(0, currentCount - 1);
                updateHeaderBadge(newCount);

                if (newCount === 0) {
                  const list = document.querySelector('.dropdown-notifications-list ul');
                  if (list) {
                    list.innerHTML = `
                                    <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                        <div class="text-center p-3">
                                            <small class="text-muted">Nenhuma notificação nova</small>
                                        </div>
                                    </li>`;
                  }
                }
              }
            }
          });
      }
    });
  });
</script>
<style>
  .marked-as-read {
    background-color: rgba(0, 0, 0, 0.02);
  }

  .marked-as-read .small {
    color: #a1a5b7 !important;
  }
</style>
@endpush