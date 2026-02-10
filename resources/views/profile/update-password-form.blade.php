<x-form-section submit="updatePassword">
  <x-slot name="title">
    {{ __('Atualizar Senha') }}
  </x-slot>

  <x-slot name="description">
    {{ __('Garanta que sua conta esteja usando uma senha longa e aleatória para se manter segura.') }}
  </x-slot>

  <x-slot name="form">
    <x-action-message class="me-3" on="saved">
      {{ __('Salvo.') }}
    </x-action-message>
    <div class="mb-5">
      <x-label class="form-label" for="current_password" value="{{ __('Senha Atual') }}" />
      <x-input id="current_password" type="password" class="{{ $errors->has('current_password') ? 'is-invalid' : '' }}"
        wire:model="state.current_password" autocomplete="current-password" />
      <x-input-error for="current_password" />
    </div>

    <div class="mb-6">
      <x-label class="form-label" for="password" value="{{ __('Nova Senha') }}" />
      <x-input id="password" type="password" class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
        wire:model="state.password" autocomplete="new-password" />
      <x-input-error for="password" />
    </div>

    <div class="mb-6">
      <x-label class="form-label" for="password_confirmation" value="{{ __('Confirmar Senha') }}" />
      <x-input id="password_confirmation" type="password"
        class="{{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}" wire:model="state.password_confirmation"
        autocomplete="new-password" />
      <x-input-error for="password_confirmation" />
    </div>
  </x-slot>

  <x-slot name="actions">
    <x-button>
      {{ __('Salvar') }}
    </x-button>
  </x-slot>
</x-form-section>