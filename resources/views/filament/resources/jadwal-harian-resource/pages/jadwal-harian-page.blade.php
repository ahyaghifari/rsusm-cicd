@once
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2/dist/css/tom-select.default.min.css" rel="stylesheet">
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2/dist/js/tom-select.complete.min.js"></script>
    @endpush
@endonce

<x-filament-panels::page>

{{-- CSS: sembunyikan sidebar Filament saat mode layar penuh --}}
<style>
    .jp-fullscreen .fi-sidebar { display: none !important; }
    .jp-fullscreen .fi-main    { margin-inline-start: 0 !important; width: 100% !important; }
    .jp-fullscreen .fi-topbar  { padding-inline-start: 1rem !important; }

    /* ── Tom Select — portal theme (Filament Adapted) ───────────────────────────── */
    .ts-portal-wrapper .ts-wrapper.single .ts-control {
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        border: 1px solid #d1d5db;
        background: #fff;
        font-size: 0.875rem;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        cursor: pointer;
        min-height: 36px;
    }
    .dark .ts-portal-wrapper .ts-wrapper.single .ts-control {
        background: #1f2937;
        border-color: #4b5563;
        color: #e5e7eb;
    }
    .ts-portal-wrapper .ts-wrapper.single.focus .ts-control,
    .ts-portal-wrapper .ts-wrapper.single .ts-control:hover {
        border-color: #d606b0;
        box-shadow: 0 0 0 1px #d606b0;
    }
    .ts-portal-wrapper .ts-dropdown {
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        font-size: 0.875rem;
        margin-top: 4px;
        background: #fff;
    }
    .dark .ts-portal-wrapper .ts-dropdown {
        background: #1f2937;
        border-color: #374151;
        color: #e5e7eb;
    }
    .ts-portal-wrapper .ts-dropdown .option {
        padding: 0.5rem 0.75rem;
    }
    .ts-portal-wrapper .ts-dropdown .option:hover,
    .ts-portal-wrapper .ts-dropdown .option.active {
        background: rgba(214, 6, 176, 0.1);
        color: #d606b0;
    }
    .ts-portal-wrapper .ts-dropdown .option.selected {
        background: rgba(214, 6, 176, 0.15);
        color: #d606b0;
        font-weight: 600;
    }
    .ts-portal-wrapper .ts-control > input {
        display: inline-block !important;
    }
</style>

<div class="space-y-4"
    x-data="{ isFs: false }"
    x-effect="isFs
        ? document.body.classList.add('jp-fullscreen')
        : document.body.classList.remove('jp-fullscreen')"
>

    {{-- =====================================================================
         SEKSI FILTER: RS + Unit Layanan via Filament filterForm
    ====================================================================== --}}
    <div class="rounded-xl overflow-hidden shadow-sm ring-1 ring-primary-200 dark:ring-primary-700/50">
        <div class="bg-linear-to-r from-primary-600 to-primary-500 dark:from-primary-700 dark:to-primary-600 px-4 py-3 flex items-center gap-2 justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-white/90 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-white tracking-wide">Filter</span>
            </div>
            <button
                type="button"
                x-on:click="isFs = !isFs"
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium
                       text-white/80 hover:text-white hover:bg-white/15 transition"
                :title="isFs ? 'Tampilkan Sidebar' : 'Sembunyikan Sidebar'"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    {{-- Ikon expand (sidebar visible) --}}
                    <path x-show="!isFs" stroke-linecap="round" stroke-linejoin="round"
                          d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/>
                    {{-- Ikon compress (sidebar hidden) --}}
                    <path x-show="isFs" stroke-linecap="round" stroke-linejoin="round"
                          d="M9 9L3.75 3.75M9 9H4.5M9 9V4.5M15 9l5.25-5.25M15 9h4.5M15 9V4.5M9 15l-5.25 5.25M9 15H4.5M9 15v4.5M15 15l5.25 5.25M15 15h4.5M15 15v4.5"/>
                </svg>
                <span x-text="isFs ? 'Tampilkan Sidebar' : 'Layar Penuh'"></span>
            </button>
        </div>
        <div class="bg-white dark:bg-gray-900 px-4 py-4">
            {{ $this->filterForm }}
        </div>
    </div>

    {{-- =====================================================================
         AREA JADWAL: Hanya tampil setelah RS dipilih
    ====================================================================== --}}
    @if(! $this->getActiveRumahSakitId())
        <x-filament::section>
            <div class="py-10 text-center flex flex-col items-center gap-3 text-gray-400 dark:text-gray-500">
                <svg class="w-10 h-10 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <p class="text-sm">Pilih <strong>Rumah Sakit</strong> terlebih dahulu.</p>
            </div>
        </x-filament::section>

    @elseif($this->mustPickUnit())
        <x-filament::section>
            <div class="py-10 text-center flex flex-col items-center gap-3 text-gray-400 dark:text-gray-500">
                <svg class="w-10 h-10 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                </svg>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Pilih <strong>Unit Layanan</strong> terlebih dahulu</p>
                <p class="text-xs text-gray-400">Rumah sakit ini memiliki lebih dari satu unit layanan. Pilih salah satu untuk melanjutkan.</p>
            </div>
        </x-filament::section>

    @elseif($this->getActiveRumahSakitId())

        {{-- =================================================================
             NAVIGASI TANGGAL — gradient banner dengan tombol ghost
        ================================================================= --}}
        <div class="rounded-xl overflow-hidden shadow-md">
            <div class="bg-linear-to-r from-primary-600 via-primary-500 to-fuchsia-500
                        dark:from-primary-800 dark:via-primary-700 dark:to-fuchsia-700
                        px-5 py-5">
                <div class="flex flex-col sm:flex-row items-center gap-4">

                    <button
                        wire:click="prevDay"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-lg
                               bg-white/20 hover:bg-white/30 text-gray-700 border border-white/30 transition shrink-0"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Kemarin
                    </button>

                    <div class="flex flex-col items-center gap-1.5 flex-1">
                        <input
                            type="date"
                            value="{{ $activeTanggal }}"
                            x-on:change="$wire.setActiveTanggal($event.target.value)"
                            class="rounded-lg bg-white/90 border-0 text-gray-700 text-sm px-3 py-1.5
                                   shadow focus:ring-2 focus:ring-white/70 focus:outline-none"
                        />
                        @if($activeTanggal)
                            <span class="text-base font-bold text-gray-700 drop-shadow">
                                {{ $this->getNamaHariAktif() }},
                                {{ \Carbon\Carbon::parse($activeTanggal)->translatedFormat('d F Y') }}
                            </span>
                        @endif
                    </div>

                    <button
                        wire:click="nextDay"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-lg
                               bg-white/20 hover:bg-white/30 text-gray-700 border border-white/30 transition shrink-0"
                    >
                        Besok
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                </div>
            </div>
        </div>

        {{-- =================================================================
             PANEL TABEL JADWAL
        ================================================================= --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                 bg-primary-100 dark:bg-primary-900/60 text-primary-700 dark:text-primary-300
                                 ring-1 ring-primary-200 dark:ring-primary-700">
                        Jadwal Harian
                    </span>
                    <span class="font-bold text-primary-600 dark:text-primary-400">
                        {{ $this->getNamaHariAktif() }}
                    </span>
                    <span class="text-gray-800 dark:text-gray-500 font-normal text-sm">
                        — {{ $activeTanggal ? \Carbon\Carbon::parse($activeTanggal)->translatedFormat('d F Y') : '' }}
                    </span>
                </div>
            </x-slot>

            <x-slot name="headerEnd">
                <div class="flex items-center gap-2">

                    {{-- Lihat Perubahan --}}
                    <x-filament::button
                        color="info"
                        icon="heroicon-m-eye"
                        outlined
                        wire:click="openPerubahan"
                        wire:loading.attr="disabled"
                        wire:target="openPerubahan"
                    >
                        Perubahan
                    </x-filament::button>

                    @php
                        $tanggalTerlalu = $activeTanggal
                            ? \Carbon\Carbon::parse($activeTanggal)->diffInDays(today(), false) > 7
                            : false;
                    @endphp

                    @if(! $tanggalTerlalu)
                        {{-- Kosongkan: hapus semua baris dari tampilan (belum tersimpan) --}}
                        <x-filament::button
                            color="danger"
                            icon="heroicon-m-trash"
                            outlined
                            x-on:click="
                                const count = Object.keys($wire.rows ?? {}).length;
                                if (count > 0 &&
                                    window.confirm('Yakin ingin mengosongkan semua baris? Perubahan yang belum disimpan akan hilang.')) {
                                    $wire.resetJadwal()
                                }
                            "
                        >
                            Kosongkan
                        </x-filament::button>

                        {{-- Muat dari Jadwal Mingguan: konfirmasi hanya jika baris sudah ada --}}
                        <x-filament::button
                            color="gray"
                            icon="heroicon-m-arrow-down-tray"
                            x-on:click="
                                const count = Object.keys($wire.rows ?? {}).length;
                                if (count > 0) {
                                    if (window.confirm('Jadwal {{ $this->getNamaHariAktif() }} yang sudah ada akan diganti dengan template dari jadwal mingguan. Lanjutkan?')) {
                                        $wire.muatDariJadwalMingguan()
                                    }
                                } else {
                                    $wire.muatDariJadwalMingguan()
                                }
                            "
                        >
                            Muat dari Jadwal Mingguan
                        </x-filament::button>
                    @endif

                </div>
            </x-slot>

            {{-- Custom Table for Jadwal Harian --}}
            <div class="overflow-x-auto bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm mt-4">
                <table class="w-full text-left text-sm text-gray-700 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="px-3 py-3 w-10 text-center text-xs font-semibold text-gray-600 dark:text-gray-300">No</th>
                            <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-48">Poliklinik <span class="text-red-500">*</span></th>
                            <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-48">Dokter</th>
                            <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-40">Nama Dokter</th>
                            <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-28">Jam Mulai <span class="text-red-500">*</span></th>
                            <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-28">Jam Selesai</th>
                            <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-32">Status <span class="text-red-500">*</span></th>
                            <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-32">Catatan</th>
                            @if($this->hasExecutiveClinic())
                            <th class="px-3 py-3 text-xs font-semibold text-amber-600 dark:text-amber-400 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-0.5">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    Exec
                                </span>
                            </th>
                            @endif
                            <th class="px-3 py-3 w-10 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($rows as $uuid => $row)
                            <tr wire:key="row-{{ $uuid }}"
                                @class([
                                    'transition-colors',
                                    'border-l-4 border-amber-400 bg-amber-50/70 dark:bg-amber-900/10 hover:bg-amber-100/70 dark:hover:bg-amber-900/20' => (bool) ($row['is_executive'] ?? false),
                                    'bg-white dark:bg-gray-900 hover:bg-blue-50/30 dark:hover:bg-blue-900/10' => ! (bool) ($row['is_executive'] ?? false),
                                ])>
                                <td class="px-3 py-2 text-xs text-gray-400 text-center select-none">
                                    <span class="inline-flex items-center justify-center gap-1">
                                        {{ $loop->iteration }}
                                        @if((bool) ($row['is_executive'] ?? false))
                                            <svg class="w-3 h-3 text-amber-500" viewBox="0 0 24 24" fill="currentColor">
                                                <title>Executive Clinic</title>
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                        @endif
                                    </span>
                                </td>
                                
                                {{-- Poliklinik --}}
                                <td class="px-2 py-1.5">
                                    <div class="ts-portal-wrapper" wire:ignore x-data="{
                                        ts: null,
                                        init() {
                                            this.ts = new TomSelect(this.$refs.sel, { maxOptions: null });
                                            this.ts.setValue('{{ $row['poliklinik_id'] ?? '' }}', true);
                                            this.ts.on('change', (v) => $wire.set('rows.{{ $uuid }}.poliklinik_id', v || null));
                                        },
                                        destroy() { if(this.ts) { this.ts.destroy(); this.ts = null; } }
                                    }">
                                        <select x-ref="sel" class="w-full text-sm">
                                            <option value="">— Pilih Poliklinik —</option>
                                            @foreach($this->getPoliklinikOptions() as $val => $label)
                                                <option value="{{ $val }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>

                                {{-- Dokter --}}
                                <td class="px-2 py-1.5">
                                    <div class="ts-portal-wrapper" wire:ignore x-data="{
                                        ts: null,
                                        init() {
                                            this.ts = new TomSelect(this.$refs.sel, { maxOptions: null });
                                            this.ts.setValue('{{ $row['dokter_id'] ?? '' }}', true);
                                            this.ts.on('change', (v) => {
                                                $wire.set('rows.{{ $uuid }}.dokter_id', v || null);
                                                $wire.set('rows.{{ $uuid }}.nama_dokter', v ? (@js($this->getDokterOptions()))[v] || null : null);
                                            });
                                        },
                                        destroy() { if(this.ts) { this.ts.destroy(); this.ts = null; } }
                                    }">
                                        <select x-ref="sel" class="w-full text-sm">
                                            <option value="">— Opsional —</option>
                                            @foreach($this->getDokterOptions() as $val => $label)
                                                <option value="{{ $val }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>

                                {{-- Nama Dokter --}}
                                <td class="px-2 py-1.5">
                                    <input type="text" wire:model="rows.{{ $uuid }}.nama_dokter" placeholder="Nama dokter..."
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500"/>
                                </td>

                                {{-- Jam Mulai --}}
                                <td class="px-2 py-1.5">
                                    <input type="time" wire:model="rows.{{ $uuid }}.jam_mulai"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500 font-mono"/>
                                </td>

                                {{-- Jam Selesai --}}
                                <td class="px-2 py-1.5">
                                    <input type="time" wire:model="rows.{{ $uuid }}.jam_selesai"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500 font-mono"
                                        placeholder="Opsional"/>
                                </td>

                                {{-- Status --}}
                                <td class="px-2 py-1.5">
                                    <select wire:model="rows.{{ $uuid }}.status_layanan"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500">
                                        @foreach(\App\Enums\StatusLayanan::cases() as $st)
                                            <option value="{{ $st->value }}">{{ $st->getLabel() }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                {{-- Catatan --}}
                                <td class="px-2 py-1.5">
                                    <input type="text" wire:model="rows.{{ $uuid }}.catatan" placeholder="Catatan..."
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500"/>
                                </td>

                                {{-- Executive --}}
                                @if($this->hasExecutiveClinic())
                                <td class="px-2 py-1.5 text-center">
                                    <input type="checkbox" wire:model="rows.{{ $uuid }}.is_executive"
                                        class="rounded border-amber-300 text-amber-500 shadow-sm focus:ring-amber-400"/>
                                </td>
                                @endif

                                {{-- Remove --}}
                                <td class="px-2 py-1.5 text-center">
                                    <button wire:click="removeRow('{{ $uuid }}')" wire:confirm="Hapus baris ini?"
                                        class="p-1.5 rounded-md text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $this->hasExecutiveClinic() ? 10 : 9 }}" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-8 h-8 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        <span class="text-sm">Belum ada data jadwal. Klik <strong>Tambah Baris</strong> untuk memulai.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if(! $tanggalTerlalu)
                <div class="bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-800 px-4 py-3 flex justify-center">
                    <button wire:click="addRow" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-primary-600 bg-primary-50 hover:bg-primary-100 dark:text-primary-400 dark:bg-primary-900/30 dark:hover:bg-primary-900/50 transition border border-primary-200 dark:border-primary-800">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Baris
                    </button>
                </div>
                @endif
            </div>

            @if(! $tanggalTerlalu)
                <div class="flex justify-end mt-2">
                    <x-filament::button wire:click="saveJadwal" icon="heroicon-m-cloud-arrow-up">
                        Simpan Jadwal
                        {{ $activeTanggal ? \Carbon\Carbon::parse($activeTanggal)->translatedFormat('d F Y') : '' }}
                    </x-filament::button>
                </div>
            @else
                <p class="text-xs text-gray-400 italic mt-2">
                    Jadwal lebih dari seminggu lalu tidak dapat diubah.
                </p>
            @endif

            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                <span class="text-red-400 font-bold">*</span> Wajib diisi. &nbsp;
                <strong>Jam Selesai</strong> opsional.
            </p>

            </div>
        </x-filament::section>

    @else
        <x-filament::section>
            <div class="py-10 text-center">
                <div class="flex flex-col items-center gap-3 text-gray-400 dark:text-gray-500">
                    <svg class="w-10 h-10 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <p class="text-sm">
                        Silakan pilih <strong>Rumah Sakit</strong> terlebih dahulu.
                    </p>
                </div>
            </div>
        </x-filament::section>
    @endif

</div>

{{-- =====================================================================
     MODAL: Lihat Perubahan
====================================================================== --}}
@if($showPerubahan)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-data x-on:keydown.escape.window="$wire.closePerubahan()">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50" wire:click="closePerubahan"></div>

    {{-- Panel --}}
    <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] flex flex-col overflow-hidden">

        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Perubahan Jadwal</h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $activeTanggal ? \Carbon\Carbon::parse($activeTanggal)->translatedFormat('d F Y') : '' }}
                    — {{ $this->getNamaHariAktif() }}
                </p>
            </div>
            <button wire:click="closePerubahan"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Konten --}}
        <div class="overflow-y-auto flex-1 px-6 py-4 space-y-6">

            {{-- DITAMBAH MANUAL --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 shrink-0"></span>
                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                        Ditambah Manual ({{ count($dataPerubahan['ditambah']) }})
                    </h4>
                </div>
                @forelse($dataPerubahan['ditambah'] as $p)
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3 mb-2">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                            {{ $p['jadwal_harian']['poliklinik']['nama'] ?? '—' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Dokter: {{ $p['jadwal_harian']['nama_dokter'] ?? '—' }}
                            @if($p['jadwal_harian']['jam_mulai'])
                                · {{ \Carbon\Carbon::parse($p['jadwal_harian']['jam_mulai'])->format('H:i') }}
                                @if($p['jadwal_harian']['jam_selesai'])
                                    – {{ \Carbon\Carbon::parse($p['jadwal_harian']['jam_selesai'])->format('H:i') }}
                                @endif
                            @endif
                        </p>
                        <p class="text-[11px] text-gray-400 mt-1">
                            Ditambah oleh: {{ $p['user']['name'] ?? 'Sistem' }}
                            · {{ \Carbon\Carbon::parse($p['created_at'])->translatedFormat('d M Y, H:i') }}
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 italic">Tidak ada penambahan manual.</p>
                @endforelse
            </div>

            {{-- DIUBAH --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                        Diubah ({{ count($dataPerubahan['diubah']) }})
                    </h4>
                </div>
                @forelse($dataPerubahan['diubah'] as $p)
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl px-4 py-3 mb-2">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                            {{ $p['jadwal_harian']['poliklinik']['nama'] ?? '—' }}
                        </p>
                        <div class="flex flex-wrap gap-2 mt-1">
                            @if($p['status_layanan'] === 'LIBUR')
                                <span class="inline-flex items-center text-[11px] font-bold text-red-600 bg-red-50 border border-red-200 px-2 py-0.5 rounded-full">
                                    Status: LIBUR
                                </span>
                            @endif
                            @if($p['jam_mulai'])
                                <span class="text-xs text-gray-600 dark:text-gray-400">
                                    Jam: {{ \Carbon\Carbon::parse($p['jam_mulai'])->format('H:i') }}
                                    @if($p['jam_selesai'])– {{ \Carbon\Carbon::parse($p['jam_selesai'])->format('H:i') }}@endif
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">
                            Diubah oleh: {{ $p['user']['name'] ?? 'Sistem' }}
                            · {{ \Carbon\Carbon::parse($p['updated_at'])->translatedFormat('d M Y, H:i') }}
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 italic">Tidak ada perubahan nilai.</p>
                @endforelse
            </div>

        </div>

        {{-- Footer --}}
        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 shrink-0 flex justify-end">
            <x-filament::button color="gray" wire:click="closePerubahan">Tutup</x-filament::button>
        </div>
    </div>
</div>
@endif

</x-filament-panels::page>
