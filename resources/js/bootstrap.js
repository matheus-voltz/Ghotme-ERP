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
import 'notyf/notyf.min.css'; // Import Notyf styles

// Create an instance of Notyf
const notyf = new Notyf({
    duration: 5000,
    position: {
        x: 'right',
        y: 'top',
    },
    types: [
        {
            type: 'message',
            background: '#7367F0',
            icon: {
                className: 'ti ti-message-circle-2 text-white',
                tagName: 'i',
            }
        }
    ]
});


// Listen for messages
// Check if user is logged in (you might need a better way to get the ID, e.g., from a meta tag)
const userIdMeta = document.querySelector('meta[name="user-id"]');
if (userIdMeta) {
    const userId = userIdMeta.getAttribute('content');

    window.Echo.private(`chat.${userId}`)
        .listen('MessageReceived', (e) => {
            console.log('Message Received:', e.message);
            const senderName = e.message.sender ? e.message.sender.name : 'Alguém';
            const messageText = e.message.message;

            notyf.open({
                type: 'message',
                message: `<b>${senderName}</b>: ${messageText}`
            });
        });
}
