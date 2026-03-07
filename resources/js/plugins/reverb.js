import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import axios from 'axios';

window.Pusher = Pusher;

export function initReverb() {
  const API_BASE = import.meta.env.VITE_API_URL || 'https://200.100.100.1';
  const broadcastAuthUrl = `${API_BASE}/api/broadcasting/auth`;

  window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,

    wsHost: import.meta.env.VITE_REVERB_HOST || '200.100.100.1',
    wsPort: Number(import.meta.env.VITE_REVERB_PORT || 443),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT || 443),
    wsPath: '/reverb/',

    forceTLS: true,
    enabledTransports: ['wss', 'ws'],

    authorizer: (channel) => ({
      authorize: (socketId, callback) => {
        const token = sessionStorage.getItem('token');

        axios
          .post(
            broadcastAuthUrl,
            {
              socket_id: socketId,
              channel_name: channel.name,
            },
            {
              headers: {
                Authorization: token ? `Bearer ${token}` : '',
                Accept: 'application/json',
                'Content-Type': 'application/json',
              },
            }
          )
          .then((response) => callback(false, response.data))
          .catch((error) => callback(true, error));
      },
    }),
  });

  return window.Echo;
}