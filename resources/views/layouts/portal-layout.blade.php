<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'RSU SYIFA MEDIKA - Pelayanan Professional & Terpercaya')</title>

    <link rel="icon" href="/img/favicon.png" sizes="192x192">
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
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2/dist/css/tom-select.css" rel="stylesheet">
</head>

<body class="bg-surface text-on-surface font-sans antialiased min-h-screen flex flex-col">

    <!-- TopNavBar component -->
    <x-header-portal />

    <!-- Main Content -->
    @yield('content')

    <!-- Footer component -->
    <x-footer-portal />

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2/dist/js/tom-select.complete.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <script>
        AOS.init({ once: false, duration: 600, easing: 'ease-out-cubic' });

        function initGlightbox() {
            if (window._glb) { try { window._glb.destroy(); } catch(e) {} }
            window._glb = GLightbox({ selector: '.glightbox', touchNavigation: true, loop: true });
        }
        initGlightbox();

        document.addEventListener('livewire:update', () => setTimeout(initGlightbox, 50));
    </script>
</body>

</html>
