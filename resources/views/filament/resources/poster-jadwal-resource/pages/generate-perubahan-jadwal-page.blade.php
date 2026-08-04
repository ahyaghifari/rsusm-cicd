<x-filament-panels::page>

{{-- Listener: open preview in new tab --}}
<div x-data x-on:open-preview.window="window.open($event.detail.url, '_blank', 'width=540,height=960,scrollbars=yes,resizable=yes')"></div>

<div class="space-y-6">

    <form wire:submit.prevent="generate">
        {{ $this->form }}

        {{-- ── Toggle Perubahan ────────────────────────────────────────────── --}}
        @if (count($this->perubahan_list))
        <div class="mt-6 fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    Pilih Perubahan yang Ditampilkan
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    Toggle untuk tampilkan/sembunyikan baris pada poster.
                </p>
            </div>

            <div class="fi-section-content px-6 py-4">
                <ul class="space-y-2">
                    @foreach ($this->perubahan_list as $index => $row)
                    <li class="flex items-center gap-3 rounded-lg border bg-gray-50 px-4 py-3 dark:bg-gray-800 dark:border-gray-700">
                        <button
                            type="button"
                            wire:click="togglePerubahan({{ $index }})"
                            class="shrink-0 w-9 h-5 rounded-full transition-colors focus:outline-none
                                   {{ $row['visible'] ? 'bg-primary-500' : 'bg-gray-300 dark:bg-gray-600' }}"
                            title="{{ $row['visible'] ? 'Sembunyikan' : 'Tampilkan' }}"
                        >
                            <span class="block w-4 h-4 rounded-full bg-white shadow transform transition-transform mx-0.5
                                         {{ $row['visible'] ? 'translate-x-4' : 'translate-x-0' }}">
                            </span>
                        </button>

                        <span class="shrink-0 text-xs font-semibold px-2 py-0.5 rounded-full {{ $row['is_executive'] ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $row['is_executive'] ? 'Executive' : 'Reguler' }}
                        </span>

                        <span class="flex-1 text-sm font-medium {{ $row['visible'] ? 'text-gray-900 dark:text-white' : 'text-gray-400 line-through' }}">
                            {{ $row['poliklinik_nama'] }} — {{ $row['dokter_nama'] }}
                        </span>

                        <span class="text-xs text-gray-500 tabular-nums">
                            {{ $row['jam_awal'] ?? 'LIBUR' }} → {{ $row['libur'] ? 'LIBUR' : $row['jam_baru'] }}
                        </span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        {{-- ── Action Buttons ──────────────────────────────────────────────── --}}
        <div class="mt-6 flex gap-3">
            <x-filament::button
                type="button"
                color="gray"
                wire:click="previewPerubahan"
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
    </form>

</div>

</x-filament-panels::page>
