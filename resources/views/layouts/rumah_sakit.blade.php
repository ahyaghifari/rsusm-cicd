<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {!! SEO::generate() !!}
    <link rel="icon" href="/img/favicon.png" sizes="192x192">

    @livewireStyles
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&amp;display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet">
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>

    <!-- Assets (Tailwind v4 via Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/rumah-sakit.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2/dist/css/tom-select.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" rel="stylesheet">
</head>

<body class="bg-surface text-on-surface font-sans antialiased min-h-screen flex flex-col pb-16 lg:pb-0 overflow-x-hidden">

    <!-- TopNavBar component -->
    @include('rumah_sakit.header')

    @include('rumah_sakit.nav')

    <!-- Main Content — pb-24 desktop, pb-36 mobile (extra space for bottom bar) -->
    <main class="pb-36 lg:pb-24">
        {{ $slot }}
    </main>

    <!-- Footer component -->
    @include('rumah_sakit.footer')

    <!-- Copyright component -->
    @include('rumah_sakit.copyright')

    <!-- Global Search Modal -->
    <livewire:global-search />

    <!-- Promo FAB — floating mandiri -->
    @include('rumah_sakit.partials.promo-popup')
    

    <!-- Chatbot Floating Button -->
    @include('rumah_sakit.chatbot.floating')

    <!-- Global Konsultasi Notification Listener -->
    <div
        x-data="globalKonsultasiListener()"
        x-init="init()"
        @sesi-berakhir.window="hapusSesi()"
        class="fixed bottom-6 right-4 z-9999 flex flex-col gap-2 items-end pointer-events-none"
        style="max-width: 340px;"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-show="toast.visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="pointer-events-auto w-full bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden"
            >
                <div class="flex items-start gap-3 p-3.5">
                    <div class="w-9 h-9 shrink-0 rounded-full bg-primary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary text-[18px]">stethoscope</span>
                    </div>
                    <div class="flex-1 min-w-0 pt-0.5">
                        <p class="text-xs font-semibold text-gray-900 truncate" x-text="toast.title"></p>
                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-2" x-text="toast.body"></p>
                    </div>
                    <button
                        @click="tutupToast(toast.id)"
                        class="shrink-0 text-gray-300 hover:text-gray-500 transition-colors mt-0.5"
                    >
                        <span class="material-symbols-outlined text-[16px]">close</span>
                    </button>
                </div>
                <a
                    :href="toast.url"
                    class="block w-full px-3.5 py-2 bg-primary text-on-primary text-xs font-semibold text-center hover:bg-primary/90 transition-colors"
                >
                    Buka Chat Konsultasi
                </a>
            </div>
        </template>
    </div>

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <script>
        function initGlightbox() {
            if (window._glb) { try { window._glb.destroy(); } catch(e) {} }
            window._glb = GLightbox({ selector: '.glightbox', touchNavigation: true, loop: true });
        }
        initGlightbox();

        document.addEventListener('livewire:navigated', initGlightbox);
        document.addEventListener('livewire:update', () => setTimeout(initGlightbox, 80));
    </script>

    <script src="{{ asset('js/konsultasi-notifikasi.js') }}"></script>
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('globalKonsultasiListener', () => ({
            toasts:  [],
            channel: null,
            _nextId: 0,

            init() {
                // Coba subscribe saat init, dan ulang setiap kali Livewire navigate
                this._subscribe();
                document.addEventListener('livewire:navigated', () => this._subscribe());
            },

            _subscribe() {
                const raw = localStorage.getItem('konsultasi_sesi');
                if (!raw) return;

                let sesi;
                try { sesi = JSON.parse(raw); } catch { return; }
                if (!sesi?.token) return;

                // Jangan double-subscribe ke channel yang sama
                if (this.channel?.name === 'konsultasi.' + sesi.token) return;
                if (this.channel) {
                    window.Echo.leaveChannel(this.channel.name);
                }

                this.channel = window.Echo.channel('konsultasi.' + sesi.token)
                    .listen('PesanDikirim', (payload) => {
                        if (payload.pengirim !== 'DOKTER') return;

                        // Mainkan suara notifikasi di mana pun pasien berada:
                        // halaman chat itu sendiri, halaman lain di website yang sama, atau tab tidak aktif
                        playNotificationSound();

                        // Tampilkan toast hanya jika TIDAK sedang di halaman chat sesi ini
                        const isOnChat = window.location.href.includes(sesi.token);
                        if (!isOnChat) {
                            this._showToast(
                                'Pesan dari ' + sesi.dokterNama,
                                payload.isi ?? 'Anda menerima pesan baru.',
                                sesi.url,
                            );
                        }

                        // Browser notification jika tab tidak aktif (berlaku meski sedang di chat)
                        if (document.hidden && Notification.permission === 'granted') {
                            new Notification('Pesan dari ' + sesi.dokterNama, {
                                body:  payload.isi ?? 'Anda menerima pesan baru.',
                                icon:  '/img/favicon.png',
                                tag:   'konsultasi-tab-' + sesi.token,
                            });
                        }
                    });
            },

            hapusSesi() {
                if (this.channel) {
                    window.Echo.leaveChannel(this.channel.name);
                    this.channel = null;
                }
                localStorage.removeItem('konsultasi_sesi');
            },

            _showToast(title, body, url) {
                const id = ++this._nextId;
                this.toasts.push({ id, title, body, url, visible: true });

                // Auto-dismiss setelah 7 detik
                setTimeout(() => this.tutupToast(id), 7000);
            },

            tutupToast(id) {
                const t = this.toasts.find((t) => t.id === id);
                if (t) t.visible = false;
                setTimeout(() => { this.toasts = this.toasts.filter((t) => t.id !== id); }, 300);
            },
        }));
    });
    </script>

    @include('rumah_sakit.partials.mobile-bottom-bar')
</body>

</html>
