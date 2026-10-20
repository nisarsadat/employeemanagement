import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import axios from 'axios';

window.Pusher = Pusher;

function broadcastAuthUrl() {
  const base = String(axios.defaults.baseURL || '').replace(/\/$/, '');

  if (base.endsWith('/api')) return `${base}/broadcasting/auth`;
  if (/^https?:\/\//i.test(base)) return `${base}/api/broadcasting/auth`;

  return '/api/broadcasting/auth';
}

function socketHost() {
  const host = window.location.hostname;
  if (host === 'localhost' || host === '::1' || host === '[::1]') {
    return '127.0.0.1';
  }

  return host;
}

function directPort() {
  const configured = Number(import.meta.env.VITE_REVERB_PORT || 6001);
  return configured === 80 || configured === 443 ? 6001 : configured;
}

function connect(mode) {
  const httpsPage = window.location.protocol === 'https:';
  const host = socketHost();
  const pagePort = Number(window.location.port || (httpsPage ? 443 : 80));
  const endpoint = mode === 'direct'
    ? { wsHost: host, wsPort: directPort(), wssPort: directPort(), forceTLS: false }
    : { wsHost: host, wsPort: pagePort, wssPort: pagePort, forceTLS: httpsPage };

  if (window.Echo) {
    try {
      window.Echo.disconnect();
    } catch {
      // A failed first attempt has nothing left to close.
    }
  }

  window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || 'local',
    ...endpoint,
    enabledTransports: endpoint.forceTLS ? ['wss'] : ['ws'],
    authorizer: (channel) => ({
      authorize: (socketId, callback) => {
        const token = sessionStorage.getItem('token');

        axios
          .post(
            broadcastAuthUrl(),
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

export function initReverb() {
  const port = window.location.port;
  const proxiedByWebServer = port === '' || port === '80' || port === '443';
  const echo = connect(proxiedByWebServer ? 'same-origin' : 'direct');

  if (!proxiedByWebServer || window.location.protocol === 'https:') {
    return echo;
  }

  let fellBack = false;
  const fallback = () => {
    if (fellBack) return;
    fellBack = true;
    connect('direct');
  };

  echo.connector.pusher.connection.bind('unavailable', fallback);
  echo.connector.pusher.connection.bind('failed', fallback);

  return echo;
}
