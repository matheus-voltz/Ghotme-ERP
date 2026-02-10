<x-form-section submit="updateProfileInformation">
  <x-slot name="title">
    {{ __('Informações do Perfil') }}
  </x-slot>

  <x-slot name="description">
    {{ __('Atualize as informações de perfil e endereço de e-mail da sua conta.') }}
  </x-slot>

  <x-slot name="form">

    <x-action-message on="saved">
      {{ __('Salvo.') }}
    </x-action-message>

    <!-- Profile Photo -->
    @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
    <div class="mb-6" x-data="{photoName: null, photoPreview: null}">
      <!-- Profile Photo File Input -->
      <input type="file" hidden wire:model.live="photo" x-ref="photo"
        x-on:change=" photoName = $refs.photo.files[0].name; const reader = new FileReader(); reader.onload = (e) => { photoPreview = e.target.result;}; reader.readAsDataURL($refs.photo.files[0]);" />

      <!-- Current Profile Photo -->
      <div class="mt-2" x-show="! photoPreview">
        <img src="{{ $this->user->profile_photo_url }}" class="rounded-circle" height="80px" width="80px">
      </div>

      <!-- New Profile Photo Preview -->
      <div class="mt-2" x-show="photoPreview">
        <img x-bind:src="photoPreview" class="rounded-circle" width="80px" height="80px">
      </div>

      <x-secondary-button class="mt-2 me-2" type="button" x-on:click.prevent="$refs.photo.click()">
        {{ __('Selecionar Nova Foto') }}
      </x-secondary-button>

      @if ($this->user->profile_photo_path)
      <button type="button" class="btn btn-danger mt-2" wire:click="deleteProfilePhoto">
        {{ __('Remover Foto') }}
      </button>
      @endif

      <x-input-error for="photo" class="mt-2" />
    </div>
    @endif

    <!-- Name -->
    <div class="mb-6">
      <x-label class="form-label" for="name" value="{{ __('Nome') }}" />
      <x-input id="name" type="text" class="{{ $errors->has('name') ? 'is-invalid' : '' }}" wire:model="state.name"
        autocomplete="name" />
      <x-input-error for="name" />
    </div>

    <!-- Email -->
    <div class="mb-6">
      <x-label class="form-label" for="email" value="{{ __('E-mail') }}" />
      <x-input id="email" type="email" class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
        wire:model="state.email" />
      <x-input-error for="email" />
    </div>
  </x-slot>

  <x-slot name="actions">
    <div class="d-flex align-items-baseline">
      <x-button>
        {{ __('Salvar') }}
      </x-button>
    </div>
  </x-slot>
</x-form-section>