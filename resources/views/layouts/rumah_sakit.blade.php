<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {!! SEO::generate() !!}

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
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>

<body class="bg-surface text-on-surface font-sans antialiased min-h-screen flex flex-col pb-20 lg:pb-0">

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

    <!-- Promo FAB — floating mandiri -->
    @include('rumah_sakit.partials.promo-fab')
    @include('rumah_sakit.partials.promo-popup')

    <!-- Chatbot Floating Button -->
    {{-- @include('rumah_sakit.chatbot.floating') --}}

    @livewireScripts
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init()
    </script>

    @include('rumah_sakit.partials.mobile-bottom-bar')
</body>

</html>
