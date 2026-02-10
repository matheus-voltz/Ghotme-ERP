<x-action-section>
  <x-slot name="title">
    {{ __('Excluir Conta') }}
  </x-slot>

  <x-slot name="description">
    {{ __('Excluir permanentemente sua conta.') }}
  </x-slot>

  <x-slot name="content">
    <div>
      {{ __('Uma vez que sua conta for excluída, todos os seus recursos e dados serão excluídos permanentemente. Antes de excluir sua conta, faça o download de quaisquer dados ou informações que deseja manter.') }}
    </div>

    <div class="mt-3">
      <x-danger-button wire:click="confirmUserDeletion" wire:loading.attr="disabled">
        {{ __('Excluir Conta') }}
      </x-danger-button>
    </div>

    <!-- Delete User Confirmation Modal -->
    <x-dialog-modal wire:model.live="confirmingUserDeletion">
      <x-slot name="title">
        {{ __('Excluir Conta') }}
      </x-slot>

      <x-slot name="content">
        {{ __('Tem certeza de que deseja excluir sua conta? Uma vez que sua conta for excluída, todos os seus recursos e dados serão excluídos permanentemente. Digite sua senha para confirmar que deseja excluir permanentemente sua conta.') }}

        <div class="mt-2" x-data="{}"
          x-on:confirming-delete-user.window="setTimeout(() => $refs.password.focus(), 250)">
          <x-input type="password" class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
            placeholder="{{ __('Senha') }}" x-ref="password" wire:model="password" wire:keydown.enter="deleteUser" />

          <x-input-error for="password" />
        </div>
      </x-slot>

      <x-slot name="footer">
        <x-secondary-button wire:click="$toggle('confirmingUserDeletion')" wire:loading.attr="disabled">
          {{ __('Cancelar') }}
        </x-secondary-button>

        <x-danger-button class="ms-1" wire:click="deleteUser" wire:loading.attr="disabled">
          {{ __('Excluir Conta') }}
        </x-danger-button>
      </x-slot>
    </x-dialog-modal>
  </x-slot>

</x-action-section>