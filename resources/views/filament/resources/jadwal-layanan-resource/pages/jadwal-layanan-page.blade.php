<x-filament-panels::page>
<div class="space-y-4">

    {{-- =====================================================================
         SEKSI FILTER: Rumah Sakit + Unit Layanan
    ====================================================================== --}}
    <div class="rounded-xl overflow-hidden shadow-sm ring-1 ring-primary-200 dark:ring-primary-700/50">
        <div class="bg-linear-to-r from-primary-600 to-primary-500 dark:from-primary-700 dark:to-primary-600 px-4 py-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-white/90 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            <span class="text-sm font-semibold text-white tracking-wide">Filter</span>
        </div>
        <div class="bg-white dark:bg-gray-900 px-4 py-4">
            {{ $this->filterForm }}
        </div>
    </div>

    {{-- =====================================================================
         AREA JADWAL: Hanya tampil setelah RS dipilih
    ====================================================================== --}}
    @if($this->getActiveRumahSakitId())

        {{-- =================================================================
             TAB HARI: SENIN – MINGGU
        ================================================================= --}}
        <div class="flex items-end gap-1 overflow-x-auto px-3 pt-3 rounded-t-xl
                    bg-linear-to-b from-primary-50 to-transparent
                    dark:from-primary-950/40 dark:to-transparent
                    border-b border-primary-200 dark:border-primary-800/60">
            @foreach(\App\Enums\Hari::cases() as $hari)
                <button
                    wire:click="setActiveHari('{{ $hari->value }}')"
                    @class([
                        'px-6 py-3 text-sm font-semibold rounded-t-lg border border-b-0 whitespace-nowrap transition-all',
                        'bg-white dark:bg-gray-900 border-primary-300 dark:border-primary-600 text-primary-700 dark:text-primary-300 -mb-px z-10 shadow-sm shadow-primary-100 dark:shadow-none'
                            => $activeHari === $hari->value,
                        'bg-white/50 dark:bg-gray-800/50 border-transparent text-gray-500 dark:text-gray-400 hover:bg-white/80 dark:hover:bg-gray-700/60 hover:text-primary-600 dark:hover:text-primary-400 mb-0'
                            => $activeHari !== $hari->value,
                    ])
                >
                    {{ $hari->getLabel() }}
                </button>
            @endforeach
        </div>

        {{-- =================================================================
             PANEL TABEL JADWAL per Hari Aktif
        ================================================================= --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                 bg-primary-100 dark:bg-primary-900/60 text-primary-700 dark:text-primary-300
                                 ring-1 ring-primary-200 dark:ring-primary-700">
                        Jadwal Mingguan
                    </span>
                    <span class="font-bold text-primary-600 dark:text-primary-400">
                        {{ \App\Enums\Hari::from($activeHari)->getLabel() }}
                    </span>
                </div>
            </x-slot>

            <div class="flex flex-col gap-4">

                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-left border-collapse min-w-250">

                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                <th class="px-3 py-3 text-xs font-semibold text-gray-400 dark:text-gray-500 w-8 text-center">#</th>
                                <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-45">
                                    Poliklinik <span class="text-red-400">*</span>
                                </th>
                                <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-40">
                                    Dokter
                                </th>
                                <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-40">
                                    Nama Dokter
                                </th>
                                <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-30">
                                    Jam Mulai <span class="text-red-400">*</span>
                                </th>
                                <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-30">
                                    Jam Selesai
                                </th>
                                <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-30">
                                    Status <span class="text-red-400">*</span>
                                </th>
                                <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-40">
                                    Catatan
                                </th>
                                <th class="px-3 py-3 w-10"></th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($rows as $i => $row)
                                <tr class="bg-white dark:bg-gray-900 hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">

                                    <td class="px-3 py-2 text-xs text-gray-400 text-center select-none">
                                        {{ $i + 1 }}
                                    </td>

                                    <td class="px-2 py-1.5">
                                        <select
                                            wire:model="rows.{{ $i }}.poliklinik_id"
                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                        >
                                            <option value="">— Pilih Poliklinik —</option>
                                            @foreach($this->getPoliklinikOptions() as $poliId => $poliNama)
                                                <option value="{{ $poliId }}" @selected((string)$row['poliklinik_id'] === (string)$poliId)>
                                                    {{ $poliNama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-2 py-1.5">
                                        <select
                                            wire:model.live="rows.{{ $i }}.dokter_id"
                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                        >
                                            <option value="">— Opsional —</option>
                                            @foreach($this->getDokterOptions() as $dokterId => $dokterNama)
                                                <option value="{{ $dokterId }}" @selected((string)$row['dokter_id'] === (string)$dokterId)>
                                                    {{ $dokterNama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-2 py-1.5">
                                        <input
                                            type="text"
                                            wire:model="rows.{{ $i }}.nama_dokter"
                                            placeholder="Nama dokter..."
                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                        />
                                    </td>

                                    <td class="px-2 py-1.5">
                                        <input
                                            type="time"
                                            wire:model="rows.{{ $i }}.jam_mulai"
                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500 font-mono"
                                        />
                                    </td>

                                    <td class="px-2 py-1.5">
                                        <input
                                            type="time"
                                            wire:model="rows.{{ $i }}.jam_selesai"
                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500 font-mono"
                                        />
                                    </td>

                                    <td class="px-2 py-1.5">
                                        <select
                                            wire:model="rows.{{ $i }}.status_layanan"
                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                        >
                                            <option value="">— Pilih —</option>
                                            @foreach(\App\Enums\StatusLayanan::cases() as $status)
                                                <option value="{{ $status->value }}" @selected($row['status_layanan'] === $status->value)>
                                                    {{ $status->getLabel() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-2 py-1.5">
                                        <input
                                            type="text"
                                            wire:model="rows.{{ $i }}.catatan"
                                            placeholder="Catatan tambahan..."
                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                        />
                                    </td>

                                    <td class="px-2 py-1.5 text-center">
                                        <button
                                            wire:click="removeRow({{ $i }})"
                                            wire:confirm="Hapus baris ini?"
                                            class="p-1.5 rounded-md text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition"
                                            title="Hapus baris"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center gap-2 text-gray-400 dark:text-gray-500">
                                            <svg class="w-8 h-8 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <p class="text-sm">
                                                Belum ada jadwal untuk hari
                                                <strong>{{ \App\Enums\Hari::from($activeHari)->getLabel() }}</strong>.
                                            </p>
                                            <p class="text-xs">Klik <strong>+ Tambah Baris</strong> di bawah untuk mulai mengisi.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between">
                    <button
                        wire:click="addRow"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg
                               border border-dashed border-gray-300 dark:border-gray-600
                               text-gray-600 dark:text-gray-400
                               hover:border-primary-400 hover:text-primary-600 dark:hover:text-primary-400
                               hover:bg-primary-50 dark:hover:bg-primary-900/10 transition"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Baris
                    </button>

                    <x-filament::button wire:click="saveJadwal" icon="heroicon-m-cloud-arrow-up">
                        Simpan Jadwal {{ \App\Enums\Hari::from($activeHari)->getLabel() }}
                    </x-filament::button>
                </div>

                <p class="text-xs text-gray-400 dark:text-gray-500">
                    <span class="text-red-400 font-bold">*</span> Wajib diisi. &nbsp;
                    Kolom <strong>Jam Selesai</strong> bersifat opsional —
                    jika dikosongkan akan ditampilkan sebagai <em>"Selesai"</em> di halaman publik.
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
                        Silakan pilih <strong>Rumah Sakit</strong> terlebih dahulu
                        untuk melihat dan mengatur jadwal layanan.
                    </p>
                </div>
            </div>
        </x-filament::section>
    @endif

</div>
</x-filament-panels::page>
