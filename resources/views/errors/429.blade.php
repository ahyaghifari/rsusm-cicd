<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terlalu Banyak Permintaan — RSU Syifa Medika</title>

    <link rel="icon" href="/img/favicon.png" sizes="192x192">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
</head>

<body class="bg-surface text-on-surface font-sans antialiased min-h-screen flex flex-col">

    <x-header-portal />

    <main class="flex-1 flex items-center justify-center px-4 py-16">
        <div class="max-w-md w-full text-center animate-fade-in">

            {{-- Ikon --}}
            <div class="flex justify-center mb-6">
                <div class="w-24 h-24 rounded-full bg-error/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-error" style="font-size: 3rem; font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 48;">
                        timer_off
                    </span>
                </div>
            </div>

            {{-- Kode & Judul --}}
            <p class="text-sm font-semibold text-primary uppercase tracking-widest mb-2">Error 429</p>
            <h1 class="text-2xl font-bold text-on-surface mb-3">Terlalu Banyak Permintaan</h1>
            <p class="text-on-surface-variant text-sm leading-relaxed mb-8">
                Kamu mengirim terlalu banyak permintaan dalam waktu singkat.<br>
                Mohon tunggu sebentar sebelum mencoba lagi.
            </p>

            {{-- Countdown --}}
            @php
                $retryAfter = (int) ($exception->getHeaders()['Retry-After'] ?? 60);
            @endphp

            <div class="bg-surface-variant rounded-2xl px-6 py-5 mb-8 inline-block w-full">
                <p class="text-xs text-on-surface-variant mb-1">Coba lagi dalam</p>
                <p class="text-4xl font-bold text-primary tabular-nums" id="countdown">{{ $retryAfter }}</p>
                <p class="text-xs text-on-surface-variant mt-1">detik</p>
            </div>

            {{-- Tombol kembali --}}
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="javascript:history.back()"
                   class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 transition-colors">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Kembali
                </a>
                <a href="/"
                   class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl border border-outline-variant text-on-surface text-sm font-semibold hover:bg-surface-variant transition-colors">
                    <span class="material-symbols-outlined text-base">home</span>
                    Halaman Utama
                </a>
            </div>

        </div>
    </main>

    <x-footer-portal />

    <script>
        (function () {
            let seconds = {{ $retryAfter }};
            const el = document.getElementById('countdown');
            if (!el || seconds <= 0) return;

            const timer = setInterval(function () {
                seconds--;
                el.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(timer);
                    window.location.reload();
                }
            }, 1000);
        })();
    </script>

</body>
</html>
