<x-filament-panels::page>
<div
    class="space-y-4"
    x-data="{ isFs: false }"
    x-on:fullscreenchange.window="isFs = !!document.fullscreenElement"
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

            {{-- Fullscreen button --}}
            <button
                type="button"
                x-on:click="isFs ? document.exitFullscreen() : document.documentElement.requestFullscreen()"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium
                       text-white/80 hover:text-white hover:bg-white/15 transition"
                title="Toggle Fullscreen"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path x-show="!isFs" stroke-linecap="round" stroke-linejoin="round"
                          d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/>
                    <path x-show="isFs" stroke-linecap="round" stroke-linejoin="round"
                          d="M9 9L3.75 3.75M9 9H4.5M9 9V4.5M15 9l5.25-5.25M15 9h4.5M15 9V4.5M9 15l-5.25 5.25M9 15H4.5M9 15v4.5M15 15l5.25 5.25M15 15h4.5M15 15v4.5"/>
                </svg>
                <span x-text="isFs ? 'Keluar' : 'Layar Penuh'"></span>
            </button>
        </div>
        <div class="bg-white dark:bg-gray-900 px-4 py-4">
            {{ $this->filterForm }}
        </div>
    </div>

    {{-- =====================================================================
         KONTEN: hanya tampil setelah RS dipilih
    ====================================================================== --}}
    @if($this->getActiveRumahSakitId())

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
                        <table class="w-full text-left border-collapse min-w-250">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-400 w-8 text-center">#</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-45">Poliklinik <span class="text-red-400">*</span></th>
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-40">Dokter</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-40">Nama Dokter</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-28">Jam Mulai</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-28">Jam Selesai</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-24 text-center">Perjanjian</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-36">Catatan</th>
                                    <th class="px-3 py-3 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($rows as $i => $row)
                                    <tr class="bg-white dark:bg-gray-900 hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                                        <td class="px-3 py-2 text-xs text-gray-400 text-center select-none">{{ $i + 1 }}</td>

                                        <td class="px-2 py-1.5">
                                            <select wire:model="rows.{{ $i }}.poliklinik_id"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500">
                                                <option value="">— Pilih —</option>
                                                @foreach($this->getPoliklinikOptions() as $pId => $pNama)
                                                    <option value="{{ $pId }}" @selected((string)$row['poliklinik_id'] === (string)$pId)>{{ $pNama }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="px-2 py-1.5">
                                            <select wire:model.live="rows.{{ $i }}.dokter_id"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500">
                                                <option value="">— Opsional —</option>
                                                @foreach($this->getDokterOptions() as $dId => $dNama)
                                                    <option value="{{ $dId }}" @selected((string)$row['dokter_id'] === (string)$dId)>{{ $dNama }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="px-2 py-1.5">
                                            <input type="text" wire:model="rows.{{ $i }}.nama_dokter" placeholder="Nama dokter..."
                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500"/>
                                        </td>

                                        <td class="px-2 py-1.5">
                                            <input type="time" wire:model="rows.{{ $i }}.waktu_mulai"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500 font-mono"/>
                                        </td>

                                        <td class="px-2 py-1.5">
                                            <input type="time" wire:model="rows.{{ $i }}.waktu_selesai"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500 font-mono"/>
                                        </td>

                                        <td class="px-2 py-1.5 text-center">
                                            <input type="checkbox" wire:model="rows.{{ $i }}.sesuai_perjanjian" value="1"
                                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 cursor-pointer"/>
                                        </td>

                                        <td class="px-2 py-1.5">
                                            <input type="text" wire:model="rows.{{ $i }}.catatan" placeholder="Catatan..."
                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500"/>
                                        </td>

                                        <td class="px-2 py-1.5 text-center">
                                            <button wire:click="removeRow({{ $i }})" wire:confirm="Hapus baris ini?"
                                                class="p-1.5 rounded-md text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-10 text-center text-gray-400 text-sm">
                                            Belum ada jadwal untuk hari <strong>{{ \App\Enums\Hari::from($activeHari)->getLabel() }}</strong>.
                                            Klik <strong>+ Tambah Baris</strong> untuk mulai.
                                        </td>
                                    </tr>
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

            @if(! $selectedDokterId)
                <x-filament::section>
                    <div class="py-12 text-center flex flex-col items-center gap-3 text-gray-400">
                        <svg class="w-10 h-10 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                        </svg>
                        <p class="text-sm">Pilih <strong>Dokter</strong> di atas untuk melihat dan mengatur jadwalnya.</p>
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
                                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-45">Poliklinik <span class="text-red-400">*</span></th>
                                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-28">Jam Mulai</th>
                                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-28">Jam Selesai</th>
                                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 w-24 text-center">Perjanjian</th>
                                        <th class="px-3 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 min-w-36">Catatan</th>
                                        <th class="px-3 py-3 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @forelse($dokterRows as $i => $row)
                                        <tr class="bg-white dark:bg-gray-900 hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                                            <td class="px-3 py-2 text-xs text-gray-400 text-center select-none">{{ $i + 1 }}</td>

                                            <td class="px-2 py-1.5">
                                                <select wire:model="dokterRows.{{ $i }}.hari"
                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500">
                                                    @foreach(\App\Enums\Hari::cases() as $h)
                                                        <option value="{{ $h->value }}" @selected($row['hari'] === $h->value)>{{ $h->getLabel() }}</option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td class="px-2 py-1.5">
                                                <select wire:model="dokterRows.{{ $i }}.poliklinik_id"
                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500">
                                                    <option value="">— Pilih —</option>
                                                    @foreach($this->getPoliklinikOptions() as $pId => $pNama)
                                                        <option value="{{ $pId }}" @selected((string)$row['poliklinik_id'] === (string)$pId)>{{ $pNama }}</option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td class="px-2 py-1.5">
                                                <input type="time" wire:model="dokterRows.{{ $i }}.waktu_mulai"
                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500 font-mono"/>
                                            </td>

                                            <td class="px-2 py-1.5">
                                                <input type="time" wire:model="dokterRows.{{ $i }}.waktu_selesai"
                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500 font-mono"/>
                                            </td>

                                            <td class="px-2 py-1.5 text-center">
                                                <input type="checkbox" wire:model="dokterRows.{{ $i }}.sesuai_perjanjian" value="1"
                                                    class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 cursor-pointer"/>
                                            </td>

                                            <td class="px-2 py-1.5">
                                                <input type="text" wire:model="dokterRows.{{ $i }}.catatan" placeholder="Catatan..."
                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500"/>
                                            </td>

                                            <td class="px-2 py-1.5 text-center">
                                                <button wire:click="removeDokterRow({{ $i }})" wire:confirm="Hapus baris ini?"
                                                    class="p-1.5 rounded-md text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-6 py-10 text-center text-gray-400 text-sm">
                                                Belum ada jadwal untuk <strong>{{ $dokterTerpilih?->nama }}</strong>.
                                                Klik <strong>+ Tambah Baris</strong> untuk mulai.
                                            </td>
                                        </tr>
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

    @else
        <x-filament::section>
            <div class="py-10 text-center flex flex-col items-center gap-3 text-gray-400">
                <svg class="w-10 h-10 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <p class="text-sm">Pilih <strong>Rumah Sakit</strong> terlebih dahulu.</p>
            </div>
        </x-filament::section>
    @endif

</div>
</x-filament-panels::page>
