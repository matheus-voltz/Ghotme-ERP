<div>
  <!-- Generate API Token -->
  <x-form-section submit="createApiToken">
    <x-slot name="title">
      {{ __('Criar Token de API') }}
    </x-slot>

    <x-slot name="description">
      {{ __('Tokens de API permitem que serviços externos e automações se autentiquem no seu ERP.') }}
    </x-slot>

    <x-slot name="form">
      <x-action-message on="created">
        {{ __('Criado.') }}
      </x-action-message>

      <!-- Token Name -->
      <div class="mb-6">
        <x-label for="name" class="form-label" value="{{ __('Nome do Token (Ex: Integração RD Station)') }}" />
        <x-input id="name" type="text" class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
          wire:model="createApiTokenForm.name" autofocus />
        <x-input-error for="name" />
      </div>

      <!-- Token Permissions -->
      @if (Laravel\Jetstream\Jetstream::hasPermissions())
      <div>
        <x-label class="form-label" for="permissions" value="{{ __('Permissões') }}" />

        <div class="mt-2 row">
          @foreach (Laravel\Jetstream\Jetstream::$permissions as $permission)
          <div class="col-6">
            <div class="mb-3">
              <div class="form-check">
                <x-checkbox wire:model="createApiTokenForm.permissions" id="{{ 'create-' . $permission }}"
                  :value="$permission" />
                <label class="form-check-label" for="{{ 'create-' . $permission }}">
                  {{ $permission }}
                </label>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
      @endif
    </x-slot>

    <x-slot name="actions">
      <x-action-message on="created">
        {{ __('Criado com sucesso.') }}
      </x-action-message>
      <x-button>
        {{ __('Gerar Token') }}
      </x-button>
    </x-slot>
  </x-form-section>

  @if ($this->user->tokens->isNotEmpty())

  <!-- Manage API Tokens -->
  <div class="mt-4">
    <x-action-section>
      <x-slot name="title">
        {{ __('Gerenciar Tokens de API') }}
      </x-slot>

      <x-slot name="description">
        {{ __('Revogue o acesso de tokens antigos ou que não estão mais em uso. Isso garante a segurança dos seus dados.') }}
      </x-slot>

      <!-- API Token List -->
      <x-slot name="content">
        <div>
          @foreach ($this->user->tokens->sortBy('name') as $token)
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-medium">
              {{ $token->name }}
            </div>

            <div class="d-flex">
              @if ($token->last_used_at)
              <div class="text-body-secondary me-3">
                {{ __('Último uso') }} {{ $token->last_used_at->diffForHumans() }}
              </div>
              @endif

              @if (Laravel\Jetstream\Jetstream::hasPermissions())
              <button class="btn btn-link py-0 text-secondary me-2" wire:click="manageApiTokenPermissions({{ $token->id }})">
                {{ __('Permissões') }}
              </button>
              @endif

              <button class="btn btn-link py-0 text-danger text-decoration-none"
                wire:click="confirmApiTokenDeletion({{ $token->id }})">
                {{ __('Excluir') }}
              </button>
            </div>
          </div>
          @endforeach
        </div>
      </x-slot>
    </x-action-section>
  </div>
  @endif

  <!-- Token Value Modal -->
  <x-dialog-modal wire:model.live="displayingToken">
    <x-slot name="title">
      {{ __('Token de API Gerado') }}
    </x-slot>

    <x-slot name="content">
      <div class="mb-2">
        {{ __('Copie o seu novo token de API. Por motivos de segurança, ele nunca mais será exibido. Caso perca, você terá que gerar um novo.') }}
      </div>

      <div>
        <x-input x-ref="plaintextToken" type="text" readonly :value="$plainTextToken" autofocus autocomplete="off"
          autocorrect="off" autocapitalize="off" spellcheck="false"
          @showing-token-modal.window="setTimeout(() => $refs.plaintextToken.select(), 250)" />
      </div>
    </x-slot>

    <x-slot name="footer">
      <x-secondary-button wire:click="$set('displayingToken', false)" wire:loading.attr="disabled">
        {{ __('Fechar') }}
      </x-secondary-button>
    </x-slot>
  </x-dialog-modal>

  <!-- API Token Permissions Modal -->
  <x-dialog-modal wire:model.live="managingApiTokenPermissions">
    <x-slot name="title">
      {{ __('Modificar Permissões do Token') }}
    </x-slot>

    <x-slot name="content">
      <div class="mt-2 row">
        @foreach (Laravel\Jetstream\Jetstream::$permissions as $permission)
        <div class="col-6">
          <div class="mb-3">
            <div class="form-check">
              <x-checkbox wire:model="updateApiTokenForm.permissions" id="{{ 'update-' . $permission }}"
                :value="$permission" />
              <label class="form-check-label" for="{{ 'update-' . $permission }}">
                {{ $permission }}
              </label>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </x-slot>

    <x-slot name="footer">
      <x-secondary-button wire:click="$set('managingApiTokenPermissions', false)" wire:loading.attr="disabled">
        {{ __('Cancelar') }}
      </x-secondary-button>

      <x-button wire:click="updateApiToken" wire:loading.attr="disabled">
        {{ __('Salvar') }}
      </x-button>
    </x-slot>
  </x-dialog-modal>

  <!-- Delete Token Confirmation Modal -->
  <x-confirmation-modal wire:model.live="confirmingApiTokenDeletion">
    <x-slot name="title">
      {{ __('Revogar Token de API') }}
    </x-slot>

    <x-slot name="content">
      {{ __('Tem certeza de que deseja excluir este token? Os serviços que dependem dele perderão o acesso imediatamente.') }}
    </x-slot>

    <x-slot name="footer">
      <x-secondary-button wire:click="$toggle('confirmingApiTokenDeletion')" wire:loading.attr="disabled">
        {{ __('Cancelar') }}
      </x-secondary-button>

      <x-danger-button wire:loading.attr="disabled" wire:click="deleteApiToken">
        {{ __('Sim, Excluir') }}
      </x-danger-button>
    </x-slot>
  </x-confirmation-modal>
</div>
