<x-action-section>
  <x-slot name="title">
    {{ __('Autenticação de Dois Fatores') }}
  </x-slot>

  <x-slot name="description">
    {{ __('Adicione segurança extra à sua conta usando autenticação de dois fatores.') }}
  </x-slot>

  <x-slot name="content">
    <h6>
      @if ($this->enabled)
      @if ($showingConfirmation)
      {{ __('Você está ativando a autenticação de dois fatores.') }}
      @else
      {{ __('Você ativou a autenticação de dois fatores.') }}
      @endif
      @else
      {{ __('Você não ativou a autenticação de dois fatores.') }}
      @endif
    </h6>

    <p class="card-text">
      {{ __('Quando a autenticação de dois fatores está ativada, será solicitado um token seguro e aleatório durante a autenticação. Você pode obter esse token no aplicativo Google Authenticator do seu telefone.') }}
    </p>

    @if ($this->enabled)
    @if ($showingQrCode)
    <p class="card-text mt-2">
      @if ($showingConfirmation)
      {{ __('Escaneie o seguinte código QR usando o aplicativo autenticador do seu telefone e confirme com o código OTP gerado.') }}
      @else
      {{ __('A autenticação de dois fatores agora está ativada. Escaneie o seguinte código QR usando o aplicativo autenticador do seu telefone.') }}
      @endif
    </p>

    <div class="mt-2">
      {!! $this->user->twoFactorQrCodeSvg() !!}
    </div>

    <div class="mt-4">
      <p class="fw-medium">
        {{ __('Chave de Configuração') }}: {{ decrypt($this->user->two_factor_secret) }}
      </p>
    </div>

    @if ($showingConfirmation)
    <div class="mt-2">
      <x-label for="code" value="{{ __('Código') }}" />
      <x-input id="code" class="d-block mt-3 w-100" type="text" inputmode="numeric" name="code" autofocus
        autocomplete="one-time-code" wire:model="code" wire:keydown.enter="confirmTwoFactorAuthentication" />
      <x-input-error for="code" class="mt-3" />
    </div>
    @endif
    @endif

    @if ($showingRecoveryCodes)
    <p class="card-text mt-2">
      {{ __('Armazene esses códigos de recuperação em um gerenciador de senhas seguro. Eles podem ser usados para recuperar o acesso à sua conta se o seu dispositivo de autenticação de dois fatores for perdido.') }}
    </p>

    <div class="bg-light rounded p-2">
      @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
      <div>{{ $code }}</div>
      @endforeach
    </div>
    @endif
    @endif

    <div class="mt-2">
      @if (!$this->enabled)
      <x-confirms-password wire:then="enableTwoFactorAuthentication">
        <x-button type="button" wire:loading.attr="disabled">
          {{ __('Ativar') }}
        </x-button>
      </x-confirms-password>
      @else
      @if ($showingRecoveryCodes)
      <x-confirms-password wire:then="regenerateRecoveryCodes">
        <x-secondary-button class="me-1">
          {{ __('Regenerar Códigos de Recuperação') }}
        </x-secondary-button>
      </x-confirms-password>
      @elseif ($showingConfirmation)
      <x-confirms-password wire:then="confirmTwoFactorAuthentication">
        <x-button type="button" wire:loading.attr="disabled">
          {{ __('Confirmar') }}
        </x-button>
      </x-confirms-password>
      @else
      <x-confirms-password wire:then="showRecoveryCodes">
        <x-secondary-button class="me-1">
          {{ __('Mostrar Códigos de Recuperação') }}
        </x-secondary-button>
      </x-confirms-password>
      @endif

      <x-confirms-password wire:then="disableTwoFactorAuthentication">
        <x-danger-button wire:loading.attr="disabled">
          {{ __('Desativar') }}
        </x-danger-button>
      </x-confirms-password>
      @endif
    </div>
  </x-slot>
</x-action-section>