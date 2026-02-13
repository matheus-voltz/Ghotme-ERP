<x-action-section>
  <x-slot name="title">
    {{ __('Sessões do Navegador') }}
  </x-slot>

  <x-slot name="description">
    {{ __('Gerencie e saia das suas sessões ativas em outros navegadores e dispositivos.') }}
  </x-slot>

  <x-slot name="content">
    <x-action-message on="loggedOut">
      {{ __('Concluído.') }}
    </x-action-message>

    <p class="card-text">
      {{ __('Se necessário, você pode sair de todas as suas outras sessões de navegador em todos os seus dispositivos. Algumas de suas sessões recentes estão listadas abaixo; no entanto, esta lista pode não ser exaustiva. Se você acha que sua conta foi comprometida, você também deve atualizar sua senha.') }}
    </p>

    @if (count($this->sessions) > 0 || auth()->user()->tokens->count() > 0)
    <div class="mt-6">
      <!-- Other Browser Sessions -->
      @foreach ($this->sessions as $session)
      <div class="d-flex mb-4">
        <div>
          @if ($session->agent->isDesktop())
          <svg fill="none" width="32" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            viewBox="0 0 24 24" stroke="currentColor" class="text-body-secondary">
            <path
              d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
            </path>
          </svg>
          @else
          <svg xmlns="http://www.w3.org/2000/svg" width="32" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
            fill="none" stroke-linecap="round" stroke-linejoin="round" class="text-body-secondary">
            <path d="M0 0h24v24H0z" stroke="none"></path>
            <rect x="7" y="4" width="10" height="16" rx="1"></rect>
            <path d="M11 5h2M12 17v.01"></path>
          </svg>
          @endif
        </div>

        <div class="ms-2">
          <div>
            {{ $session->agent->platform() ? $session->agent->platform() : 'Unknown' }} -
            {{ $session->agent->browser() ? $session->agent->browser() : 'Unknown' }}
          </div>

          <div>
            <div class="small text-body-secondary">
              {{ $session->ip_address }},

              @if ($session->is_current_device)
              <span class="text-success fw-medium">{{ __('Este dispositivo') }}</span>
              @else
              {{ __('Última atividade') }} {{ $session->last_active }}
              @endif
            </div>
          </div>
        </div>
      </div>
      @endforeach

      <!-- Mobile Tokens (Sanctum) -->
      @foreach (auth()->user()->tokens as $token)
      <div class="d-flex mb-4">
        <div>
          <svg xmlns="http://www.w3.org/2000/svg" width="32" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
            fill="none" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
            <path d="M0 0h24v24H0z" stroke="none"></path>
            <rect x="7" y="4" width="10" height="16" rx="1"></rect>
            <path d="M11 5h2M12 17v.01"></path>
          </svg>
        </div>

        <div class="ms-2">
          <div>
            Dispositivo Móvel ({{ $token->name }})
          </div>

          <div>
            <div class="small text-body-secondary">
              {{ __('Ativo desde') }} {{ $token->created_at->diffForHumans() }}
              <span class="badge bg-label-info ms-2">App Mobile</span>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
    @endif

    <div class="d-flex mt-6">
      <x-button wire:click="confirmLogout" wire:loading.attr="disabled">
        {{ __('Sair de Outras Sessões de Navegador') }}
      </x-button>
    </div>

    <!-- Log out Other Devices Confirmation Modal -->
    <x-dialog-modal wire:model.live="confirmingLogout">
      <x-slot name="title">
        {{ __('Sair de Outras Sessões de Navegador') }}
      </x-slot>

      <x-slot name="content">
        {{ __('Digite sua senha para confirmar que deseja sair de todas as suas outras sessões de navegador em todos os seus dispositivos.') }}

        <div class="mt-3" x-data="{}"
          x-on:confirming-logout-other-browser-sessions.window="setTimeout(() => $refs.password.focus(), 250)">
          <x-input type="password" placeholder="{{ __('Senha') }}" x-ref="password"
            class="{{ $errors->has('password') ? 'is-invalid' : '' }}" wire:model="password"
            wire:keydown.enter="logoutOtherBrowserSessions" />

          <x-input-error for="password" class="mt-2" />
        </div>
      </x-slot>

      <x-slot name="footer">
        <x-secondary-button wire:click="$toggle('confirmingLogout')" wire:loading.attr="disabled">
          {{ __('Cancelar') }}
        </x-secondary-button>

        <button class="btn btn-danger ms-1" wire:click="logoutOtherBrowserSessions" wire:loading.attr="disabled">
          {{ __('Sair de Outras Sessões de Navegador') }}
        </button>
      </x-slot>
    </x-dialog-modal>
  </x-slot>

</x-action-section>