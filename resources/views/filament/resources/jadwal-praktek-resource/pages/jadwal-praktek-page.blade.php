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

<div
    class="space-y-4"
    x-data="{ isFs: false }"
    x-effect="isFs
        ? document.body.classList.add('jp-fullscreen')
        : document.body.classList.remove('jp-fullscreen')"
>

    {{-- =====================================================================
         FILTER
    ====================================================================== --}}
    <div class="rounded-xl overflow-hidden shadow-sm ring-1 ring-primary-200 dark:ring-primary-700/50">
        <div class="bg-linear-to-r from-primary-600 to-primary-500 dark:from-primary-700 dark:to-primary-600
                    px-4 py-3 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-white/90 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-white tracking-wide">Filter</span>
            </div>

            <div class="flex items-center gap-2">

            {{-- Export PDF — tampil jika filter sudah valid --}}
            @if($this->getActiveRumahSakitId() && !$this->mustPickUnit())
                @if($viewMode === 'per_hari' || ($viewMode === 'per_dokter' && $selectedDokterId))
                    <button
                        wire:click="exportPdf"
                        wire:loading.attr="disabled"
                        wire:target="exportPdf"
                        type="button"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-green-500 hover:green-600 text-white transition disabled:opacity-50"
                        title="Export jadwal ke PDF"
                    >
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span wire:loading.remove wire:target="exportPdf">Export PDF</span>
                        <span wire:loading wire:target="exportPdf">Membuat...</span>
                    </button>
                @endif
            @endif

            {{-- Fullscreen: sembunyikan sidebar Filament --}}
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

            </div>{{-- end flex buttons --}}
        </div>
        <div class="bg-white dark:bg-gray-900 px-4 py-4">
            {{ $this->filterForm }}
        </div>
    </div>

    {{-- =====================================================================
         KONTEN
    ====================================================================== --}}
    @if(! $this->getActiveRumahSakitId())
        {{-- Belum pilih RS --}}
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
        {{-- RS punya >1 unit tapi belum dipilih --}}
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

    @else

        {{-- ================================================================
             MODE: PER HARI
        ================================================================= --}}
        @if($viewMode === 'per_hari')

            {{-- Tab Hari --}}
            <div class="flex items-end gap-1 overflow-x-auto px-3 pt-3 rounded-t-xl
                        bg-linear-to-b from-primary-50 to-transparent
                        dark:from-primary-950/40 dark:to-transparent
                        border-b border-primary-200 dark:border-primary-800/60">
                @foreach(\App\Enums\Hari::cases() as $hari)
                    <button
                        wire:click="setActiveHari('{{ $hari->value }}')"
                        @class([
                            'px-6 py-3 text-sm font-semibold rounded-t-lg border border-b-0 whitespace-nowrap transition-all',
                            'bg-white dark:bg-gray-900 border-primary-300 dark:border-primary-600 text-primary-700 dark:text-primary-300 -mb-px z-10 shadow-sm'
                                => $activeHari === $hari->value,
                            'bg-white/50 dark:bg-gray-800/50 border-transparent text-gray-500 dark:text-gray-400 hover:bg-white/80 dark:hover:bg-gray-700/60 hover:text-primary-600 dark:hover:text-primary-400'
                                => $activeHari !== $hari->value,
                        ])
                    >
                        {{ $hari->getLabel() }}
                    </button>
                @endforeach
            </div>

            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                     bg-primary-100 dark:bg-primary-900/60 text-primary-700 dark:text-primary-300
                                     ring-1 ring-primary-200 dark:ring-primary-700">Jadwal Per Hari</span>
                        <span class="font-bold text-primary-600 dark:text-primary-400">
                            {{ \App\Enums\Hari::from($activeHari)->getLabel() }}
                        </span>
                    </div>
                </x-slot>

                <div class="flex flex-col gap-4">
                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-left border-collapse min-w-225">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-400 w-8 text-center">#</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-52">Poliklinik <span class="text-red-400">*</span></th>
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-44">Dokter</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-36">Nama Dokter</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-28">Jam Mulai</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-28">Jam Selesai</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-20 text-center">Perjanjian</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-32">Catatan</th>
                                    <th class="px-3 py-3 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($rows as $uuid => $row)
                                    <tr wire:key="row-{{ $uuid }}" class="bg-white dark:bg-gray-900 hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                                        <td class="px-3 py-2 text-xs text-gray-400 text-center select-none">{{ $loop->iteration }}</td>

                                        {{-- Poliklinik — Tom Select --}}
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
                                                    @foreach($this->getPoliklinikOptions() as $pId => $pNama)
                                                        <option value="{{ $pId }}">{{ $pNama }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>

                                        {{-- Dokter — Tom Select --}}
                                        <td class="px-2 py-1.5">
                                            <div class="ts-portal-wrapper" wire:ignore x-data="{
                                                ts: null,
                                                init() {
                                                    this.ts = new TomSelect(this.$refs.sel, { maxOptions: null });
                                                    this.ts.setValue('{{ $row['dokter_id'] ?? '' }}', true);
                                                    this.ts.on('change', (v) => {
                                                        $wire.set('rows.{{ $uuid }}.dokter_id', v || null);
                                                        $wire.set('rows.{{ $uuid }}.nama_dokter',
                                                            v ? (@js($this->getDokterOptions()))[v] || null : null
                                                        );
                                                    });
                                                },
                                                destroy() { if(this.ts) { this.ts.destroy(); this.ts = null; } }
                                            }">
                                                <select x-ref="sel" class="w-full text-sm">
                                                    <option value="">— Opsional —</option>
                                                    @foreach($this->getDokterOptions() as $dId => $dNama)
                                                        <option value="{{ $dId }}">{{ $dNama }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>

                                        <td class="px-2 py-1.5">
                                            <input type="text" wire:model="rows.{{ $uuid }}.nama_dokter" placeholder="Nama dokter..."
                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500"/>
                                        </td>

                                        <td class="px-2 py-1.5">
                                            <input type="time" wire:model="rows.{{ $uuid }}.waktu_mulai"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500 font-mono"/>
                                        </td>

                                        <td class="px-2 py-1.5">
                                            <input type="time" wire:model="rows.{{ $uuid }}.waktu_selesai"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500 font-mono"
                                                placeholder="Opsional"/>
                                        </td>

                                        <td class="px-2 py-1.5 text-center">
                                            <input type="checkbox"
                                                @php $spVal = $row['sesuai_perjanjian'] ?? '0'; @endphp
                                                {{ ($spVal === '1' || $spVal === true || $spVal === 1) ? 'checked' : '' }}
                                                @change="$wire.set('rows.{{ $uuid }}.sesuai_perjanjian', $event.target.checked ? '1' : '0')"
                                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 cursor-pointer"/>
                                        </td>

                                        <td class="px-2 py-1.5">
                                            <input type="text" wire:model="rows.{{ $uuid }}.catatan" placeholder="Catatan..."
                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500"/>
                                        </td>

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
                                    <tr><td colspan="9" class="px-6 py-10 text-center text-gray-400 text-sm">
                                        Belum ada jadwal. Klik <strong>+ Tambah Baris</strong> untuk mulai.
                                    </td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between">
                        <button wire:click="addRow"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg
                                   border border-dashed border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400
                                   hover:border-primary-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Baris
                        </button>
                        <x-filament::button wire:click="saveJadwal" icon="heroicon-m-cloud-arrow-up">
                            Simpan Jadwal {{ \App\Enums\Hari::from($activeHari)->getLabel() }}
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>

        {{-- ================================================================
             MODE: PER DOKTER
        ================================================================= --}}
        @elseif($viewMode === 'per_dokter')

            {{-- Dokter selector — di sini agar ganti dokter = ganti di konten, bukan filter --}}
            <x-filament::section>
                <div class="py-1">
                    {{ $this->dokterForm }}
                </div>
            </x-filament::section>

            @if(! $selectedDokterId)
                <x-filament::section>
                    <div class="py-12 text-center flex flex-col items-center gap-3 text-gray-400">
                        <svg class="w-10 h-10 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                        </svg>
                        <p class="text-sm">Pilih <strong>Dokter</strong> untuk melihat dan mengatur jadwalnya.</p>
                    </div>
                </x-filament::section>
            @else
                @php
                    $dokterTerpilih = \App\Models\Dokter::find($selectedDokterId);
                @endphp

                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                         bg-primary-100 dark:bg-primary-900/60 text-primary-700 dark:text-primary-300
                                         ring-1 ring-primary-200 dark:ring-primary-700">Jadwal Per Dokter</span>
                            <span class="font-bold text-primary-600 dark:text-primary-400">
                                {{ $dokterTerpilih?->nama }}
                            </span>
                        </div>
                    </x-slot>

                    <div class="flex flex-col gap-4">
                        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                            <table class="w-full text-left border-collapse min-w-200">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                        <th class="px-3 py-3 text-xs font-semibold text-gray-400 w-8 text-center">#</th>
                                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-32">Hari <span class="text-red-400">*</span></th>
                                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-52">Poliklinik <span class="text-red-400">*</span></th>
                                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-28">Jam Mulai</th>
                                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-28">Jam Selesai</th>
                                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-20 text-center">Perjanjian</th>
                                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-32">Catatan</th>
                                        <th class="px-3 py-3 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($dokterRows as $uuid => $row)
                                        <tr wire:key="dokter-row-{{ $uuid }}" class="bg-white dark:bg-gray-900 hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                                            <td class="px-3 py-2 text-xs text-gray-400 text-center select-none">{{ $loop->iteration }}</td>

                                            <td class="px-2 py-1.5">
                                                <select wire:model="dokterRows.{{ $uuid }}.hari"
                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500">
                                                    @foreach(\App\Enums\Hari::cases() as $h)
                                                        <option value="{{ $h->value }}" @selected($row['hari'] === $h->value)>{{ $h->getLabel() }}</option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            {{-- Poliklinik — Tom Select --}}
                                            <td class="px-2 py-1.5">
                                                <div class="ts-portal-wrapper" wire:ignore x-data="{
                                                    ts: null,
                                                    init() {
                                                        this.ts = new TomSelect(this.$refs.sel, { maxOptions: null });
                                                        this.ts.setValue('{{ $row['poliklinik_id'] ?? '' }}', true);
                                                        this.ts.on('change', (v) => $wire.set('dokterRows.{{ $uuid }}.poliklinik_id', v || null));
                                                    },
                                                    destroy() { if(this.ts) { this.ts.destroy(); this.ts = null; } }
                                                }">
                                                    <select x-ref="sel" class="w-full text-sm">
                                                        <option value="">— Pilih Poliklinik —</option>
                                                        @foreach($this->getPoliklinikOptions() as $pId => $pNama)
                                                            <option value="{{ $pId }}">{{ $pNama }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </td>

                                            <td class="px-2 py-1.5">
                                                <input type="time" wire:model="dokterRows.{{ $uuid }}.waktu_mulai"
                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500 font-mono"/>
                                            </td>

                                            <td class="px-2 py-1.5">
                                                <input type="time" wire:model="dokterRows.{{ $uuid }}.waktu_selesai"
                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500 font-mono"
                                                    placeholder="Opsional"/>
                                            </td>

                                            <td class="px-2 py-1.5 text-center">
                                                @php $spValD = $row['sesuai_perjanjian'] ?? '0'; @endphp
                                                <input type="checkbox"
                                                    {{ ($spValD === '1' || $spValD === true || $spValD === 1) ? 'checked' : '' }}
                                                    @change="$wire.set('dokterRows.{{ $uuid }}.sesuai_perjanjian', $event.target.checked ? '1' : '0')"
                                                    class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 cursor-pointer"/>
                                            </td>

                                            <td class="px-2 py-1.5">
                                                <input type="text" wire:model="dokterRows.{{ $uuid }}.catatan" placeholder="Catatan..."
                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500"/>
                                            </td>

                                            <td class="px-2 py-1.5 text-center">
                                                <button wire:click="removeDokterRow('{{ $uuid }}')" wire:confirm="Hapus baris ini?"
                                                    class="p-1.5 rounded-md text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="px-6 py-10 text-center text-gray-400 text-sm">
                                            Belum ada jadwal untuk <strong>{{ $dokterTerpilih?->nama }}</strong>.
                                        </td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="flex items-center justify-between">
                            <button wire:click="addDokterRow"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg
                                       border border-dashed border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400
                                       hover:border-primary-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Tambah Baris
                            </button>
                            <x-filament::button wire:click="saveDokterJadwal" icon="heroicon-m-cloud-arrow-up">
                                Simpan Jadwal {{ $dokterTerpilih?->nama }}
                            </x-filament::button>
                        </div>
                    </div>
                </x-filament::section>
            @endif

        @endif

    @endif

</div>
</x-filament-panels::page>
