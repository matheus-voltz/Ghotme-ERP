@extends('layouts/layoutMaster')

@section('title', 'Chat Suporte')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/maxLength/maxLength.scss',
  'resources/assets/vendor/libs/notyf/notyf.scss'
])
@endsection

@section('page-style')
@vite('resources/assets/vendor/scss/pages/app-chat.scss')
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/notyf/notyf.js')
@endsection

@section('page-script')
@vite('resources/assets/js/app-chat.js')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    window.addEventListener('new-chat-message', event => {
      const data = event.detail[0];
      
      if (typeof window.showToast === 'function') {
        window.showToast(
          `Mensagem de ${data.sender}`,
          data.message,
          'success'
        );
      }
      
      // Tocar um som discreto se quiser
      // new Audio('/assets/audio/notification.mp3').play();
    });
  });
</script>
@endsection

@section('content')
  @livewire('support-chat')
@endsection