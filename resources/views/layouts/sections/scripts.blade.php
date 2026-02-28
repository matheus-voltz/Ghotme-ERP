<!-- BEGIN: Vendor JS-->

@vite(['resources/assets/vendor/libs/jquery/jquery.js', 'resources/assets/vendor/libs/popper/popper.js', 'resources/assets/vendor/js/bootstrap.js', 'resources/assets/vendor/libs/node-waves/node-waves.js'])

@if ($configData['hasCustomizer'])
@vite('resources/assets/vendor/libs/pickr/pickr.js')
@endif

@vite(['resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js', 'resources/assets/vendor/libs/hammer/hammer.js', 'resources/assets/vendor/js/menu.js'])

@yield('vendor-script')
<!-- END: Page Vendor JS-->

<!-- BEGIN: Theme JS-->
@vite(['resources/assets/js/main.js'])
<!-- END: Theme JS-->

<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->

<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->

<!-- app JS -->
@vite(['resources/js/app.js'])
<!-- END: app JS-->

@stack('modals')
@livewireScripts
@stack('scripts')

@stack('scripts')

@if(auth()->check() && auth()->user()->is_master)
<!-- Container para Toasts do Bootstrap -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 10000">
  <div id="masterToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header border-bottom">
      <i class="ti tabler-bell text-primary me-2"></i>
      <strong class="me-auto text-primary" id="toastTitle">Notificação</strong>
      <small class="text-muted">agora</small>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body" id="toastMessage" style="cursor: pointer;">
      ...
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('Monitor Master Inicializado com Bootstrap Toasts...');

    const playAlert = () => {
        const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3');
        audio.play().catch(e => console.log('Clique na página para habilitar o som.'));
    };

    const showToast = (title, message, url) => {
        const toastEl = document.getElementById('masterToast');
        const toastTitleEl = document.getElementById('toastTitle');
        const toastMessageEl = document.getElementById('toastMessage');

        // Limpa título (remove emojis se houver)
        const cleanTitle = title.replace(/[^\w\sÀ-ú]/gi, '').trim();

        toastTitleEl.innerText = cleanTitle;
        toastMessageEl.innerText = message;

        if (url) {
            toastMessageEl.onclick = () => { window.location.href = url; };
        }

        // Inicializa e mostra o Toast nativo do Bootstrap 5
        const toast = new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 10000
        });
        toast.show();
        playAlert();
    };

    // 1. Escuta via WebSocket (Real-time)
    if (typeof Echo !== 'undefined') {
        console.log('Conectado ao Echo. Ouvindo chat.' + {{ auth()->id() }});

        // Notificações de Sistema
        Echo.private('App.Models.User.' + {{ auth()->id() }})
            .notification((notification) => {
                showToast(notification.title, notification.message, notification.url);
            });

        // Novas Mensagens de Chat (Suporte)
        Echo.private('chat.' + {{ auth()->id() }})
            .listen('MessageReceived', (e) => handleChatMessage(e))
            .listen('.MessageReceived', (e) => handleChatMessage(e))
            .listen('.App\\Events\\MessageReceived', (e) => handleChatMessage(e));
    }

    function handleChatMessage(e) {
        if (!window.location.href.includes('/support/chat')) {
            showToast('Nova Mensagem de Suporte', e.message.message, '{{ url("/support/chat") }}');
        }
    }

    // 2. Polling de backup (10s)
    let lastNotificationId = null;
    let lastChatId = null;

    setInterval(() => {
        fetch('{{ route("notifications.unread-count") }}')
            .then(res => res.json())
            .then(data => {
                if (data.count > 0 && data.latest && data.latest.id !== lastNotificationId) {
                    if (lastNotificationId !== null) {
                        showToast(data.latest.data.title, data.latest.data.message, data.latest.data.url);
                    }
                    lastNotificationId = data.latest.id;
                }
            }).catch(e => {});

        fetch('{{ route("chat.unread-count") }}')
            .then(res => res.json())
            .then(data => {
                if (data.unread_count > 0 && data.last_message && data.last_message.id !== lastChatId) {
                    if (lastChatId !== null && !window.location.href.includes('/support/chat')) {
                        showToast('Nova Mensagem de Suporte', data.last_message.message, '{{ url("/support/chat") }}');
                    }
                    lastChatId = data.last_message.id;
                }
            }).catch(e => {});
    }, 10000);
  });
</script>
@endif