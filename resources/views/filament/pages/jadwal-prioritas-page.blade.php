@once
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2/dist/css/tom-select.default.min.css" rel="stylesheet">
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2/dist/js/tom-select.complete.min.js"></script>
    @endpush
@endonce

<x-filament-panels::page>

{{-- Tom Select — style diselaraskan dengan halaman Jadwal Harian --}}
<style>
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

    <div class="space-y-6">

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Untuk poliklinik yang jadwal dokternya tidak mengikuti pola mingguan tetap (mis. poli umum) —
                isi langsung per tanggal di sini. Semua jadwal di halaman ini otomatis ditandai
                <span class="font-semibold">executive</span>. Untuk edit/hapus jadwal yang sudah tersimpan,
                gunakan halaman <span class="font-semibold">Jadwal Harian</span>.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-end">
                <div>
                    {{ $this->filterForm }}
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2 block">Periode (Bulan &amp; Tahun)</label>
                    <input type="month" wire:model.live="periode"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>
        </div>

        @if (! $this->getActiveRumahSakitId())
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6 text-center text-sm text-gray-500">
            Pilih rumah sakit terlebih dahulu.
        </div>
        @elseif (! $periode)
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6 text-center text-sm text-gray-500">
            Pilih periode (bulan &amp; tahun) terlebih dahulu.
        </div>
        @else

        {{-- Jadwal yang sudah tersimpan untuk RS + periode ini --}}
        @php $existingJadwal = $this->getExistingJadwal(); @endphp
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-white/5">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-white">Jadwal Tersimpan Bulan Ini</h3>
            </div>
            @if ($existingJadwal->isEmpty())
            <p class="px-4 py-4 text-sm text-gray-400">Belum ada jadwal tersimpan untuk periode ini.</p>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-2 text-left">Tanggal</th>
                            <th class="px-3 py-2 text-left">Poliklinik</th>
                            <th class="px-3 py-2 text-left">Dokter</th>
                            <th class="px-3 py-2 text-left">Jam</th>
                            <th class="px-3 py-2 text-right w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach ($existingJadwal as $j)
                        <tr wire:key="existing-{{ $j->id }}">
                            <td class="px-3 py-2">{{ $j->tanggal->translatedFormat('d M Y') }}</td>
                            <td class="px-3 py-2">{{ $j->poliklinik?->nama ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $j->nama_dokter ?: ($j->dokter?->nama ?? '-') }}</td>
                            <td class="px-3 py-2 font-mono">{{ $j->jam_mulai?->format('H:i') }}–{{ $j->jam_selesai?->format('H:i') ?? 'selesai' }}</td>
                            <td class="px-3 py-2 text-right whitespace-nowrap">
                                <button type="button" wire:click="editExisting({{ $j->id }})"
                                        class="text-primary-600 hover:text-primary-700 text-xs font-medium mr-3">
                                    Edit
                                </button>
                                <button type="button"
                                        x-on:click="if (confirm('Hapus jadwal ini?')) $wire.hapusExisting({{ $j->id }})"
                                        class="text-red-500 hover:text-red-600 text-xs font-medium">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Form input baris baru — TANPA overflow-hidden di section ini supaya dropdown
             Tom Select (dokter) tidak terpotong; hanya tabelnya yang overflow-x-auto. --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="overflow-x-auto rounded-t-xl">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-2 text-left w-48">Poliklinik</th>
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
                                <select wire:model.live="rows.{{ $key }}.poliklinik_id"
                                        class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                    <option value="">— Pilih Poliklinik —</option>
                                    @foreach ($this->getPoliklinikOptions() as $id => $nama)
                                        <option value="{{ $id }}">{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="ts-portal-wrapper" wire:ignore x-data="{
                                    ts: null,
                                    init() {
                                        this.ts = new TomSelect(this.$refs.sel, { maxOptions: null });
                                        this.ts.setValue('{{ $row['dokter_id'] ?? '' }}', true);
                                        this.ts.on('change', (v) => $wire.set('rows.{{ $key }}.dokter_id', v || null));
                                    },
                                    destroy() { if (this.ts) { this.ts.destroy(); this.ts = null; } }
                                }" wire:key="ts-dokter-{{ $key }}">
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
                            <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-400">Belum ada baris — klik "Tambah Baris" di bawah.</td>
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