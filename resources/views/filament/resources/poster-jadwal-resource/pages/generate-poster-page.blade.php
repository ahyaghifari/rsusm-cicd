{{-- resources/views/filament/pages/generate-poster.blade.php --}}
<x-filament-panels::page>

{{-- Listener: open preview in new tab --}}
<div x-data x-on:open-preview.window="window.open($event.detail.url, '_blank', 'width=540,height=960,scrollbars=yes,resizable=yes')"></div>

<div class="space-y-3">

    {{-- ── Form Section ──────────────────────────────────────────────────────── --}}
    <form wire:submit.prevent="generate">
        {{ $this->form }}

        {{-- ── Step 3: Daftar Poliklinik (read-only) ─────────────────────────── --}}
        @if (count($this->poli_list))
        <div class="mt-6 fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    Poliklinik
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ count($this->poli_list) }} poliklinik &middot; {{ $this->dokterCount }} dokter masuk daftar.
                </p>
            </div>

            <div class="fi-section-content px-6 py-4">
                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach ($this->poli_list as $poli)
                    <li class="flex items-center gap-3 rounded-lg border bg-gray-50 px-4 py-3 dark:bg-gray-800 dark:border-gray-700">
                        <span class="flex-1 text-sm font-medium text-gray-900 dark:text-white">
                            {{ $poli['nama'] }}
                        </span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        {{-- ── Quick Config ─────────────────────────────────────────────── --}}
        @if (count($this->quickConfigFields))
        <div
            x-data="{ open: false }"
            class="mt-6 fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
        >
            <button
                type="button"
                class="w-full fi-section-header px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between"
                x-on:click="open = !open"
            >
                <div class="text-left">
                    <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        Sesuaikan Cepat
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        Override sementara konfigurasi template — tidak disimpan.
                    </p>
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                     :class="open ? 'rotate-180' : ''"
                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                </svg>
            </button>

            <div x-show="open" x-cloak class="fi-section-content px-6 py-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach ($this->quickConfigFields as $field)
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $field['label'] }}
                        </label>
                        <input
                            type="number"
                            wire:model.live="quickConfig.{{ $field['key'] }}"
                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600
                                   bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                   text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500
                                   px-3 py-2"
                        >
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- ── Page Navigator (hanya List Polos dengan > 1 halaman) ──────── --}}
        @if ($this->totalHalaman > 1)
        <div class="mt-6 fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content px-6 py-4 flex items-center justify-between gap-4">
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Halaman
                    <span class="text-primary-600 font-bold">{{ $this->activeHalaman }}</span>
                    dari
                    <span class="font-bold">{{ $this->totalHalaman }}</span>
                </span>
                <div class="flex items-center gap-2">
                    <x-filament::button
                        type="button"
                        color="gray"
                        size="sm"
                        wire:click="setHalaman({{ $this->activeHalaman - 1 }})"
                        :disabled="$this->activeHalaman <= 1"
                        icon="heroicon-m-chevron-left"
                    >
                        Sebelumnya
                    </x-filament::button>

                    @for ($h = 1; $h <= $this->totalHalaman; $h++)
                    <x-filament::button
                        type="button"
                        size="sm"
                        :color="$this->activeHalaman === $h ? 'primary' : 'gray'"
                        wire:click="setHalaman({{ $h }})"
                    >
                        {{ $h }}
                    </x-filament::button>
                    @endfor

                    <x-filament::button
                        type="button"
                        color="gray"
                        size="sm"
                        wire:click="setHalaman({{ $this->activeHalaman + 1 }})"
                        :disabled="$this->activeHalaman >= $this->totalHalaman"
                        icon="heroicon-m-chevron-right"
                        icon-position="after"
                    >
                        Berikutnya
                    </x-filament::button>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Action Buttons — cuma muncul kalau poliklinik & tanggal sudah ke-resolve --}}
        @if (count($this->poli_list))
        <div class="mt-6 flex gap-3">
            <x-filament::button
                type="button"
                color="gray"
                wire:click="previewPoster"
                wire:loading.attr="disabled"
                icon="heroicon-o-eye"
            >
                Preview
            </x-filament::button>

            <x-filament::button
                type="submit"
                wire:loading.attr="disabled"
                icon="heroicon-o-arrow-down-tray"
            >
                <span wire:loading.remove>Download PNG</span>
                <span wire:loading>Generating...</span>
            </x-filament::button>
        </div>
        @endif
    </form>

</div>

</x-filament-panels::page>