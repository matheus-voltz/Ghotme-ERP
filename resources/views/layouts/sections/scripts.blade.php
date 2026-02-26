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
<!-- SweetAlert2 para Alertas Master Globais -->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('Monitor Master Inicializado...');

    const playAlert = () => {
        const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3');
        audio.play().catch(e => console.log('Clique na página para habilitar o som.'));
    };

    const showToast = (title, message, url) => {
        // Limpeza de texto: Remove emojis e prefixos repetitivos
        const cleanTitle = title.replace(/[^\w\sÀ-ú]/gi, '').replace('Nova Mensagem no', '').trim();
        const isSupport = title.includes('Mensagem') || title.includes('Suporte');
        const icon = isSupport ? 'tabler-message-dots' : 'tabler-alert-circle';
        const color = isSupport ? 'primary' : 'danger';

        Swal.fire({
            html: `
            <div class="d-flex align-items-start">
                <div class="avatar avatar-md me-3">
                    <span class="avatar-initial rounded bg-label-${color}">
                        <i class="ti ${icon} fs-2"></i>
                    </span>
                </div>
                <div class="flex-grow-1 text-start">
                    <h6 class="mb-0 fw-bold text-heading" style="font-size: 0.95rem;">${cleanTitle}</h6>
                    <p class="mb-0 text-body small mt-1">${message}</p>
                </div>
            </div>
            `,
            toast: true,
            position: 'top-end',
            showConfirmButton: true,
            confirmButtonText: 'ABRIR',
            customClass: {
                confirmButton: `btn btn-xs btn-${color} waves-effect waves-light mt-2`,
                popup: 'rounded-3 border-0 shadow-lg p-3'
            },
            buttonsStyling: false,
            timer: 15000,
            timerProgressBar: true,
            showCloseButton: true
        }).then((result) => {
            if (result.isConfirmed && url) window.location.href = url;
        });
    };

    // 1. Escuta via WebSocket (Real-time)
    if (typeof Echo !== 'undefined') {
        Echo.private('App.Models.User.' + {{ auth()->id() }})
            .notification((notification) => {
                playAlert();
                showToast(notification.title, notification.message, notification.url);
            });
    }

    // 2. Polling de backup (10s)
    let lastNotificationId = null;
    setInterval(() => {
        fetch('{{ route("notifications.unread-count") }}')
            .then(res => res.json())
            .then(data => {
                if (data.count > 0 && data.latest && data.latest.id !== lastNotificationId) {
                    if (lastNotificationId !== null) {
                        playAlert();
                        showToast(data.latest.data.title, data.latest.data.message, data.latest.data.url);
                    }
                    lastNotificationId = data.latest.id;
                }
            }).catch(e => {});
    }, 10000);
  });
</script>
@endif