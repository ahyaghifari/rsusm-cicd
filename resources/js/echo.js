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
    /**
     * Echo secara default menambahkan prefiks "App.Events." pada setiap nama
     * event yang didengarkan (mis. #[On('echo:channel,SesiStatusBerubah')]
     * → mendengarkan "App\Events\SesiStatusBerubah"). Tapi setiap event di
     * sini mendefinisikan broadcastAs() dengan nama pendek tanpa namespace
     * ("SesiStatusBerubah", "PesanDikirim") — jadi nama yang benar-benar
     * disiarkan oleh server TIDAK PERNAH cocok dengan yang didengarkan Echo,
     * dan listener Livewire diam selamanya tanpa error. Mengosongkan
     * namespace membuat Echo mendengarkan nama event apa adanya.
     */
    namespace: '',
});
