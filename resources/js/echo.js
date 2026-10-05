import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

let echoInstance = null;

export function getEcho() {
    if (!echoInstance) {
        const key = import.meta.env.VITE_REVERB_APP_KEY || import.meta.env.VITE_PUSHER_APP_KEY || '';
        const host = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
        const port = Number(import.meta.env.VITE_REVERB_PORT) || 8080;
        const scheme = import.meta.env.VITE_REVERB_SCHEME || 'http';

        echoInstance = new Echo({
            broadcaster: 'reverb',
            key: key,
            wsHost: host,
            wsPort: port,
            wssPort: port,
            forceTLS: scheme === 'https',
            enabledTransports: ['ws', 'wss'],
        });
    }

    return echoInstance;
}

export function disconnectEcho() {
    if (echoInstance) {
        try {
            echoInstance.disconnect();
        } catch (e) {
            console.error('Error disconnecting Echo:', e);
        }
        echoInstance = null;
    }
}

