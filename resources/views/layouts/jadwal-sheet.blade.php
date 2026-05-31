@php $title = $title ?? 'Jadwal'; @endphp
<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="h-full"
    x-data="{ darkMode: $persist(false).as('filament.theme') }"
    x-bind:class="{ 'dark': darkMode }"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — {{ config('app.name') }}</title>

    @filamentStyles
    @livewireStyles
    @vite(['resources/css/app.css'])
</head>
<body class="h-full bg-gray-50 dark:bg-gray-950 antialiased">

    {{-- =====================================================================
         TOP BAR MINIMAL
         Tidak ada sidebar. Hanya breadcrumb, brand, dan toggle dark mode.
    ====================================================================== --}}
    <header class="sticky top-0 z-40 flex items-center gap-3 px-4 h-11
                   bg-white dark:bg-gray-900
                   border-b border-gray-200 dark:border-gray-800
                   shadow-sm">

        {{-- Tombol kembali ke panel admin --}}
        <a
            href="{{ url('/admin') }}"
            class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400
                   hover:text-primary-600 dark:hover:text-primary-400 transition"
        >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="hidden sm:inline text-xs font-medium">Admin</span>
        </a>

        <span class="text-gray-300 dark:text-gray-700 select-none">|</span>

        {{-- Nama aplikasi --}}
        <span class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate">
            {{ config('app.name') }}
        </span>

        <span class="text-gray-300 dark:text-gray-700 select-none">/</span>

        {{-- Judul halaman (diteruskan via prop) --}}
        <span class="text-sm font-semibold text-primary-600 dark:text-primary-400 truncate">
            {{ $title }}
        </span>

        <div class="ml-auto flex items-center gap-1">
            {{-- Toggle dark mode --}}
            <button
                x-on:click="darkMode = !darkMode"
                class="p-1.5 rounded-lg text-gray-400 dark:text-gray-500
                       hover:bg-gray-100 dark:hover:bg-gray-800
                       hover:text-gray-600 dark:hover:text-gray-300
                       transition"
                title="Toggle dark mode"
            >
                <svg x-show="!darkMode" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <svg x-show="darkMode" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </button>
        </div>
    </header>

    {{-- =====================================================================
         KONTEN HALAMAN
    ====================================================================== --}}
    <main class="p-4 sm:p-6">
        {{ $slot }}
    </main>

    {{-- Filament notification overlay —
         Wajib ada agar Notification::make()->send() tampil di halaman ini. --}}
    @livewire(\Filament\Notifications\Livewire\Notifications::class)

    @livewireScripts
    @filamentScripts(null, true)

</body>
</html>
