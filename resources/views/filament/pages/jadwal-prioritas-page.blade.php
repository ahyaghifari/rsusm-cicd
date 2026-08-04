@once
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2/dist/css/tom-select.default.min.css" rel="stylesheet">
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2/dist/js/tom-select.complete.min.js"></script>
    @endpush
@endonce

<x-filament-panels::page>

    <div class="space-y-6">

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Untuk poliklinik yang jadwal dokternya tidak mengikuti pola mingguan tetap (mis. poli umum) —
                pilih poliklinik &amp; periode, lalu isi langsung per tanggal di sini. Semua jadwal di halaman ini
                otomatis ditandai <span class="font-semibold">executive</span>. Untuk edit/hapus jadwal yang sudah
                tersimpan, gunakan halaman <span class="font-semibold">Jadwal Harian</span>.
            </p>

            {{ $this->filterForm }}
        </div>

        @if (! $this->getActiveRumahSakitId())
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6 text-center text-sm text-gray-500">
            Pilih rumah sakit terlebih dahulu.
        </div>
        @elseif (! $selectedPoliklinikId)
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6 text-center text-sm text-gray-500">
            Pilih poliklinik terlebih dahulu.
        </div>
        @else

        {{-- Jadwal yang sudah tersimpan untuk poliklinik + periode ini --}}
        @php $existingJadwal = $this->getExistingJadwal(); @endphp
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-white/5">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-white">Jadwal Tersimpan Bulan Ini</h3>
            </div>
            @if ($existingJadwal->isEmpty())
            <p class="px-4 py-4 text-sm text-gray-400">Belum ada jadwal tersimpan untuk poliklinik &amp; periode ini.</p>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-2 text-left">Tanggal</th>
                            <th class="px-3 py-2 text-left">Dokter</th>
                            <th class="px-3 py-2 text-left">Jam</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach ($existingJadwal as $j)
                        <tr>
                            <td class="px-3 py-2">{{ $j->tanggal->translatedFormat('d M Y') }}</td>
                            <td class="px-3 py-2">{{ $j->nama_dokter ?: ($j->dokter?->nama ?? '-') }}</td>
                            <td class="px-3 py-2 font-mono">{{ $j->jam_mulai?->format('H:i') }}–{{ $j->jam_selesai?->format('H:i') ?? 'selesai' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Form input baris baru --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-2 text-left w-64">Dokter</th>
                            <th class="px-3 py-2 text-left w-36">Tanggal</th>
                            <th class="px-3 py-2 text-left w-28">Jam Mulai</th>
                            <th class="px-3 py-2 text-left w-28">Jam Selesai</th>
                            <th class="px-3 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse ($rows as $key => $row)
                        <tr wire:key="row-{{ $key }}">
                            <td class="px-3 py-2 align-top">
                                <div class="ts-portal-wrapper" wire:ignore x-data="{
                                    ts: null,
                                    init() {
                                        this.ts = new TomSelect(this.$refs.sel, { maxOptions: null });
                                        this.ts.setValue('{{ $row['dokter_id'] ?? '' }}', true);
                                        this.ts.on('change', (v) => $wire.set('rows.{{ $key }}.dokter_id', v || null));
                                    },
                                    destroy() { if (this.ts) { this.ts.destroy(); this.ts = null; } }
                                }">
                                    <select x-ref="sel" class="w-full text-xs">
                                        <option value="">— Dokter (opsional) —</option>
                                        @foreach ($this->getDokterOptions() as $id => $nama)
                                            <option value="{{ $id }}">{{ $nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if (! $row['dokter_id'])
                                <input type="text" wire:model="rows.{{ $key }}.nama_dokter" placeholder="atau ketik nama bebas"
                                       class="mt-1 w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                @endif

                                {{-- Preview jadwal dokter terpilih di bulan ini — biar kelihatan bentrok atau tidak --}}
                                @if (! empty($dokterJadwalPreview[$key]))
                                <div class="mt-2 rounded-lg bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-900/30 p-2 space-y-1">
                                    <p class="text-[11px] font-semibold text-amber-700 dark:text-amber-400">Jadwal dokter ini bulan ini:</p>
                                    @foreach ($dokterJadwalPreview[$key] as $preview)
                                    <p class="text-[11px] text-amber-700 dark:text-amber-400">
                                        {{ $preview['tanggal'] }} — {{ $preview['poliklinik'] }} ({{ $preview['jam'] }})
                                    </p>
                                    @endforeach
                                </div>
                                @endif
                            </td>
                            <td class="px-3 py-2 align-top">
                                <input type="date" wire:model="rows.{{ $key }}.tanggal"
                                       class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            </td>
                            <td class="px-3 py-2 align-top">
                                <input type="time" wire:model="rows.{{ $key }}.jam_mulai"
                                       class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            </td>
                            <td class="px-3 py-2 align-top">
                                <input type="time" wire:model="rows.{{ $key }}.jam_selesai"
                                       class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            </td>
                            <td class="px-3 py-2 text-center align-top">
                                <button type="button" wire:click="removeRow('{{ $key }}')"
                                        class="text-gray-400 hover:text-red-500 transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-sm text-gray-400">Belum ada baris — klik "Tambah Baris" di bawah.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-3 py-3 border-t border-gray-100 dark:border-white/5">
                <x-filament::button type="button" color="gray" size="sm" wire:click="addRow" icon="heroicon-o-plus">
                    Tambah Baris
                </x-filament::button>
            </div>
        </div>

        <div class="flex gap-3">
            <x-filament::button wire:click="simpan" wire:loading.attr="disabled" icon="heroicon-o-check">
                Simpan Semua
            </x-filament::button>
        </div>

        @endif
    </div>

</x-filament-panels::page>