import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

(window as unknown as { Pusher: typeof Pusher }).Pusher = Pusher;

export const echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_KEY,
  wsHost: import.meta.env.VITE_REVERB_HOST,
  wsPort: Number(import.meta.env.VITE_REVERB_PORT),
  wssPort: Number(import.meta.env.VITE_REVERB_PORT),
  forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
  enabledTransports: ['ws', 'wss'],
  authEndpoint: `${import.meta.env.VITE_API_URL}/broadcasting/auth`,
  auth: {
    headers: { Authorization: `Bearer ${localStorage.getItem('token') ?? ''}` },
  },
});
