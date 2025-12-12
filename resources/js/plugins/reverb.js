// src/plugins/reverb.js
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import axios from 'axios'

window.Pusher = Pusher

export function initReverb() {
  const API_BASE = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000'
  const broadcastAuthUrl = `${API_BASE}/api/broadcasting/auth` // ✅ FIX

  window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,

    wsHost: import.meta.env.VITE_REVERB_HOST || '127.0.0.1',
    wsPort: Number(import.meta.env.VITE_REVERB_PORT || 6001),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT || 6001),

    forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https',
    enabledTransports: ['ws', 'wss'],

    // ✅ Always send fresh Bearer token
    authorizer: (channel) => {
      return {
        authorize: (socketId, callback) => {
          const token = sessionStorage.getItem('token') // ✅ your storage

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
                },
              }
            )
            .then((response) => callback(false, response.data))
            .catch((error) => callback(true, error))
        },
      }
    },
  })

  return window.Echo
}
