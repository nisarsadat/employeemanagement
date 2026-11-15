import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import axios from 'axios';

window.Pusher = Pusher;

const REVERB_PORT = 6001;
const MAX_ATTEMPTS = 5;
const RETRY_DELAY_MS = 700;

let attempt = 0;
let retryTimer = 0;

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

function socketEndpoint() {
  const httpsPage = window.location.protocol === 'https:';
  const host = socketHost();

  if (httpsPage) {
    const pagePort = Number(window.location.port || 443);

    return {
      wsHost: host,
      wsPort: pagePort,
      wssPort: pagePort,
      forceTLS: true,
    };
  }

  return {
    wsHost: host,
    wsPort: REVERB_PORT,
    wssPort: REVERB_PORT,
    forceTLS: false,
  };
}

function connect() {
  const endpoint = socketEndpoint();

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

function scheduleRetry() {
  if (attempt >= MAX_ATTEMPTS) return;

  window.clearTimeout(retryTimer);
  retryTimer = window.setTimeout(connectWithRetry, RETRY_DELAY_MS);
}

function connectWithRetry() {
  attempt += 1;
  const echo = connect();
  const connection = echo.connector?.pusher?.connection;
  if (!connection) return echo;

  const onConnected = () => {
    window.clearTimeout(retryTimer);
    connection.unbind('unavailable', onGiveUp);
    connection.unbind('failed', onGiveUp);
  };

  const onGiveUp = () => {
    connection.unbind('connected', onConnected);
    scheduleRetry();
  };

  connection.bind('connected', onConnected);
  connection.bind('unavailable', onGiveUp);
  connection.bind('failed', onGiveUp);

  return echo;
}

export function initReverb() {
  attempt = 0;
  window.clearTimeout(retryTimer);

  return connectWithRetry();
}
