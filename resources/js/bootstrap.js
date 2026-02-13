import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

import { Notyf } from 'notyf';
// Notyf removed in favor of Bootstrap Toasts

// Listen for messages
// Check if user is logged in
const userIdMeta = document.querySelector('meta[name="user-id"]');
if (userIdMeta) {
    const userId = userIdMeta.getAttribute('content');

    window.Echo.private(`chat.${userId}`)
        .listen('MessageReceived', (e) => {
            console.log('Message Received:', e.message);
            const senderName = e.message.sender ? e.message.sender.name : 'Alguém';
            const messageText = e.message.message;

            // 1. Ensure Toast Container Exists
            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                toastContainer.style.zIndex = '1090';
                document.body.appendChild(toastContainer);
            }

            // 2. Create Toast HTML
            const toastId = 'toast-' + Date.now();
            const toastHtml = `
            <div id="${toastId}" class="bs-toast toast fade" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <i class="icon-base ti tabler-message-circle-2 icon-xs me-2 text-primary"></i>
                    <div class="me-auto fw-medium">${senderName}</div>
                    <small class="text-body-secondary">Agora</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${messageText}
                </div>
            </div>
        `;

            // 3. Append to Container
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = toastHtml.trim();
            const toastElement = tempDiv.firstChild;
            toastContainer.appendChild(toastElement);

            // 4. Initialize and Show using global bootstrap object (provided by template)
            if (window.bootstrap) {
                const toast = new window.bootstrap.Toast(toastElement);
                toast.show();

                // Cleanup after hide
                toastElement.addEventListener('hidden.bs.toast', () => {
                    toastElement.remove();
                });
            } else {
                console.error('Bootstrap is not loaded');
            }
        });
}
