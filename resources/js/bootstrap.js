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

console.log('Initializing Echo with Reverb...');
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('Echo connected to Reverb!');
});

window.Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('Echo connection error:', err);
});

import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';

const notyf = new Notyf({
    duration: 5000,
    position: { x: 'right', y: 'top' },
    types: [
        {
            type: 'message',
            background: '#7367F0',
            icon: {
                className: 'icon-base ti tabler-message-circle-2',
                tagName: 'i',
                color: 'white'
            }
        }
    ]
});

// Listen for messages
// Check if user is logged in
const userIdMeta = document.querySelector('meta[name="user-id"]');
if (userIdMeta) {
    const userId = userIdMeta.getAttribute('content');
    console.log('Subscribing to private channel: chat.' + userId);

    window.Echo.private(`chat.${userId}`)
        .subscribed(() => {
            console.log('Successfully subscribed to chat.' + userId);
        })
        .listen('MessageReceived', (e) => {
            console.log('Event MessageReceived captured!', e);
            // Se eu sou o remetente, não mostro notificação para mim mesmo
            if (String(e.message.sender_id) === String(userId)) {
                console.log('Skipping toast: User is the sender.');
                return;
            }

            console.log('Showing toast for message:', e.message.message);
            const senderName = e.message.sender ? e.message.sender.name : 'Alguém';
            const messageText = e.message.message || 'Enviou uma imagem';

            notyf.open({
                type: 'message',
                message: `<b>${senderName}</b><br>${messageText}`
            });
        });
} else {
    console.warn('User ID meta tag not found. Real-time chat notifications disabled.');
}
