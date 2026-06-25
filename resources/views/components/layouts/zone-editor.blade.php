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
    <title>Zone Editor — {{ config('app.name') }}</title>

    @filamentStyles
    @livewireStyles
    @vite(['resources/css/app.css'])
</head>
<body class="h-full bg-gray-50 dark:bg-gray-950 antialiased">

    {{-- Minimal Top Bar — no sidebar, clean --}}
    <header class="sticky top-0 z-40 flex items-center gap-3 px-4 h-12
                   bg-white dark:bg-gray-900
                   border-b border-gray-200 dark:border-gray-800
                   shadow-sm">
        {{-- Back to list --}}
        <a href="{{ $backUrl }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400
                  hover:text-primary-600 dark:hover:text-primary-400 transition shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="text-xs font-medium">Kembali</span>
        </a>

        <span class="text-gray-300 dark:text-gray-700 select-none">|</span>

        <span class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate">
            Zone Editor
        </span>

        <span class="text-gray-300 dark:text-gray-700 select-none">/</span>

        <span class="text-sm font-semibold text-primary-600 dark:text-primary-400 truncate">
            {{ $recordName }}
        </span>

        <div class="ml-auto flex items-center gap-2">
            {{-- Save Button --}}
            <button
                wire:click="save"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-4 py-1.5 text-sm font-semibold text-white
                       bg-primary-600 hover:bg-primary-500 rounded-lg
                       transition shadow-sm disabled:opacity-50"
            >
                <svg wire:loading.remove class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span wire:loading.remove>Simpan</span>
                <span wire:loading>Menyimpan...</span>
            </button>

            {{-- Toggle dark mode --}}
            <button
                x-on:click="darkMode = !darkMode"
                class="p-1.5 rounded-lg text-gray-400 dark:text-gray-500
                       hover:bg-gray-100 dark:hover:bg-gray-800
                       hover:text-gray-600 dark:hover:text-gray-300 transition"
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

    {{-- Main Content — full remaining height --}}
    <main style="height: calc(100vh - 48px);">
        {{ $slot }}
    </main>

    {{-- Filament notification overlay --}}
    @livewire(\Filament\Notifications\Livewire\Notifications::class)

    @livewireScripts
    @filamentScripts(null, true)

</body>
</html>
