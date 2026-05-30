import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

// CSRF token
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

// اختر Reverb إذا موجود، وإلا Pusher
const hasReverbKey = !!import.meta.env.VITE_REVERB_APP_KEY
const hasPusherKey = !!import.meta.env.VITE_PUSHER_APP_KEY

if (hasReverbKey) {
  window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
    wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 80),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 443),
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
      headers: token ? { 'X-CSRF-TOKEN': token } : {},
    },
  })
} else if (hasPusherKey) {
  window.Pusher = Pusher
  window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,

    wsHost: import.meta.env.VITE_PUSHER_HOST,
    wsPort: Number(import.meta.env.VITE_PUSHER_PORT ?? 80),
    wssPort: Number(import.meta.env.VITE_PUSHER_PORT ?? 443),
    enabledTransports: ['ws', 'wss'],

    authEndpoint: '/broadcasting/auth',
    auth: {
      headers: token ? { 'X-CSRF-TOKEN': token } : {},
    },
  })
}
