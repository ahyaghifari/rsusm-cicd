<div>
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
    x-data="{ isFs: $wire.entangle('isFullscreen') }"
    x-init="document.body.classList.toggle('jp-fullscreen', isFs)"
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
                    @if($this->hasExecutiveClinic() && $this->hasJadwalHarianData() && $this->executiveClinicFilter !== 'all')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold
                                     {{ $this->executiveClinicFilter === 'eksekutif' ? 'bg-amber-100 text-amber-700 ring-1 ring-amber-300' : 'bg-blue-100 text-blue-700 ring-1 ring-blue-300' }}">
                            {{ $this->executiveClinicFilter === 'eksekutif' ? 'Eksekutif' : 'Reguler' }}
                            <button wire:click="$set('executiveClinicFilter', 'all')" class="hover:opacity-70">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </span>
                    @endif
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

                    @if(! $tanggalTerlalu && $this->canEditJadwal())
                        {{-- Kosongkan: hanya muncul kalau tanggal ini sudah punya jadwal
                             tersimpan DAN tampilan tidak sedang dipersempit ke Reguler/Eksekutif
                             saja (menghindari "kosongkan" yang cuma menghapus sebagian data) --}}
                        @if($this->hasJadwalHarianData() && ! $this->isJadwalFiltered())
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
                        @endif

                        {{-- Muat dari Jadwal Mingguan: hanya muncul kalau tanggal ini BELUM
                             punya jadwal tersimpan sama sekali (tidak terpengaruh filter
                             klinik). Begitu dimuat, langsung tersimpan ke database. --}}
                        @if(! $this->hasJadwalHarianData())
                            <x-filament::button
                                color="gray"
                                icon="heroicon-m-arrow-down-tray"
                                x-on:click="
                                    const count = Object.keys($wire.rows ?? {}).length;
                                    if (count > 0) {
                                        if (window.confirm('Baris yang belum disimpan akan diganti dengan template dari jadwal mingguan dan langsung tersimpan. Lanjutkan?')) {
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
                             @if($this->hasExecutiveClinic())
                            <th class="px-3 py-3 text-xs font-semibold text-amber-600 dark:text-amber-400 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-0.5">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    Exec
                                </span>
                            </th>
                            @endif
                            <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-28">Jam Mulai <span class="text-red-500">*</span></th>
                            <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-28">Jam Selesai</th>
                            <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-32">Status <span class="text-red-500">*</span></th>
                            <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-32">Catatan</th>
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
                                            if (! @js($this->canEditJadwal())) this.ts.disable();
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
                                            if (! @js($this->canEditJadwal())) this.ts.disable();
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
                                        @disabled(! $this->canEditJadwal())
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500"/>
                                </td>

                                 {{-- Executive --}}
                                @if($this->hasExecutiveClinic())
                                <td class="px-2 py-1.5 text-center">
                                    <input type="checkbox" wire:model.boolean="rows.{{ $uuid }}.is_executive"
                                        @disabled(! $this->canEditJadwal())
                                        class="rounded border-amber-300 text-amber-500 shadow-sm focus:ring-amber-400"/>
                                </td>
                                @endif

                                {{-- Jam Mulai --}}
                                <td class="px-2 py-1.5">
                                    <input type="time" wire:model="rows.{{ $uuid }}.jam_mulai"
                                        @disabled(! $this->canEditJadwal())
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500 font-mono"/>
                                </td>

                                {{-- Jam Selesai --}}
                                <td class="px-2 py-1.5">
                                    <input type="time" wire:model="rows.{{ $uuid }}.jam_selesai"
                                        @disabled(! $this->canEditJadwal())
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500 font-mono"
                                        placeholder="Opsional"/>
                                </td>

                                {{-- Status --}}
                                <td class="px-2 py-1.5">
                                    <select wire:model="rows.{{ $uuid }}.status_layanan"
                                        @disabled(! $this->canEditJadwal())
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500">
                                        @foreach(\App\Enums\StatusLayanan::cases() as $st)
                                            <option value="{{ $st->value }}">{{ $st->getLabel() }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                {{-- Catatan --}}
                                <td class="px-2 py-1.5">
                                    <input type="text" wire:model="rows.{{ $uuid }}.catatan" placeholder="Catatan..."
                                        @disabled(! $this->canEditJadwal())
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500"/>
                                </td>

                                {{-- Remove --}}
                                <td class="px-2 py-1.5 text-center">
                                    @if($this->canEditJadwal())
                                    <button wire:click="removeRow('{{ $uuid }}')" wire:confirm="Hapus baris ini?"
                                        class="p-1.5 rounded-md text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                    @endif
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
                @if(! $tanggalTerlalu && $this->canEditJadwal())
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

            @if(! $this->canEditJadwal())
                <p class="text-xs text-gray-400 italic mt-2">
                    Mode lihat saja — akun Anda tidak memiliki izin mengubah jadwal harian.
                </p>
            @elseif(! $tanggalTerlalu)
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
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between shrink-0 bg-gray-50/60 dark:bg-gray-800/40">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-9 h-9 rounded-lg bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-400 shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Riwayat Perubahan Jadwal</h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $this->getNamaHariAktif() }},
                        {{ $activeTanggal ? \Carbon\Carbon::parse($activeTanggal)->translatedFormat('d F Y') : '' }}
                    </p>
                </div>
            </div>
            <button wire:click="closePerubahan"
                    class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-300 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Konten --}}
        <div class="overflow-y-auto flex-1 px-6 py-5 space-y-6">

            {{-- DITAMBAH MANUAL --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400 shrink-0">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    </span>
                    <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Ditambah Manual
                    </h4>
                    <span class="text-xs font-semibold text-gray-400">{{ count($dataPerubahan['ditambah']) }}</span>
                </div>

                @forelse($dataPerubahan['ditambah'] as $p)
                    <div class="bg-green-50/70 dark:bg-green-900/10 border border-green-200 dark:border-green-800/60 rounded-xl px-4 py-3 mb-2">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                {{ $p['jadwal_harian']['poliklinik']['nama'] ?? '—' }}
                            </p>
                            @if($p['jadwal_harian']['jam_mulai'])
                                <span class="shrink-0 text-xs font-mono font-medium text-green-700 dark:text-green-400 bg-white dark:bg-gray-900 border border-green-200 dark:border-green-800 rounded-md px-2 py-0.5">
                                    {{ \Carbon\Carbon::parse($p['jadwal_harian']['jam_mulai'])->format('H:i') }}
                                    @if($p['jadwal_harian']['jam_selesai'])
                                        – {{ \Carbon\Carbon::parse($p['jadwal_harian']['jam_selesai'])->format('H:i') }}
                                    @endif
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Dokter: {{ $p['jadwal_harian']['nama_dokter'] ?? '—' }}
                        </p>
                        <div class="flex items-center gap-1.5 mt-2 pt-2 border-t border-green-200/60 dark:border-green-800/40">
                            <span class="flex items-center justify-center w-4 h-4 rounded-full bg-green-200 dark:bg-green-800 text-[9px] font-bold text-green-800 dark:text-green-200 shrink-0">
                                {{ mb_substr($p['user']['name'] ?? 'S', 0, 1) }}
                            </span>
                            <p class="text-[11px] text-gray-400">
                                {{ $p['user']['name'] ?? 'Sistem' }} · {{ \Carbon\Carbon::parse($p['created_at'])->translatedFormat('d M Y, H:i') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="flex items-center gap-2 text-gray-400 dark:text-gray-500 py-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm italic">Tidak ada penambahan manual.</p>
                    </div>
                @endforelse
            </div>

            {{-- DIUBAH --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 shrink-0">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </span>
                    <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Diubah
                    </h4>
                    <span class="text-xs font-semibold text-gray-400">{{ count($dataPerubahan['diubah']) }}</span>
                </div>

                @forelse($dataPerubahan['diubah'] as $p)
                    @php
                        $statusAsli = $p['status_layanan_asli'] ?? null;
                        $statusBaru = $p['status_layanan'] ?? null;
                        $jamAsli    = $p['jam_mulai_asli']
                            ? \Carbon\Carbon::parse($p['jam_mulai_asli'])->format('H:i') . ($p['jam_selesai_asli'] ? '–' . \Carbon\Carbon::parse($p['jam_selesai_asli'])->format('H:i') : '')
                            : null;
                        $jamBaru    = $p['jam_mulai']
                            ? \Carbon\Carbon::parse($p['jam_mulai'])->format('H:i') . ($p['jam_selesai'] ? '–' . \Carbon\Carbon::parse($p['jam_selesai'])->format('H:i') : '')
                            : null;
                    @endphp
                    <div class="bg-amber-50/70 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/60 rounded-xl px-4 py-3 mb-2">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                            {{ $p['jadwal_harian']['poliklinik']['nama'] ?? '—' }}
                        </p>

                        <div class="mt-2 space-y-1.5">
                            @if($statusAsli !== $statusBaru)
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="w-14 text-gray-400 shrink-0">Status</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-gray-500 bg-gray-100 dark:bg-gray-800 line-through decoration-gray-400">
                                        {{ \App\Enums\StatusLayanan::tryFrom($statusAsli)?->getLabel() ?? '—' }}
                                    </span>
                                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                    <span @class([
                                        'inline-flex items-center px-2 py-0.5 rounded-full font-bold',
                                        'text-red-700 bg-red-100 dark:bg-red-900/40 dark:text-red-300' => $statusBaru === 'LIBUR',
                                        'text-green-700 bg-green-100 dark:bg-green-900/40 dark:text-green-300' => $statusBaru === 'BUKA',
                                    ])>
                                        {{ \App\Enums\StatusLayanan::tryFrom($statusBaru)?->getLabel() ?? $statusBaru }}
                                    </span>
                                </div>
                            @endif

                            @if($jamAsli !== $jamBaru && $jamBaru)
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="w-14 text-gray-400 shrink-0">Jam</span>
                                    <span class="font-mono text-gray-500 line-through decoration-gray-400">{{ $jamAsli ?? '—' }}</span>
                                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                    <span class="font-mono font-semibold text-gray-700 dark:text-gray-300">{{ $jamBaru }}</span>
                                </div>
                            @endif

                            @if($p['catatan'])
                                <p class="text-xs text-gray-500 dark:text-gray-400 italic">"{{ $p['catatan'] }}"</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-1.5 mt-2 pt-2 border-t border-amber-200/60 dark:border-amber-800/40">
                            <span class="flex items-center justify-center w-4 h-4 rounded-full bg-amber-200 dark:bg-amber-800 text-[9px] font-bold text-amber-800 dark:text-amber-200 shrink-0">
                                {{ mb_substr($p['user']['name'] ?? 'S', 0, 1) }}
                            </span>
                            <p class="text-[11px] text-gray-400">
                                {{ $p['user']['name'] ?? 'Sistem' }} · {{ \Carbon\Carbon::parse($p['updated_at'])->translatedFormat('d M Y, H:i') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="flex items-center gap-2 text-gray-400 dark:text-gray-500 py-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm italic">Tidak ada perubahan nilai.</p>
                    </div>
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
</div>