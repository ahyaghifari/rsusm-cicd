<x-filament-panels::page>

    <div class="space-y-6">

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Untuk poliklinik yang jadwal dokternya tidak mengikuti pola mingguan tetap (mis. poli umum) —
                isi langsung per tanggal di sini. Untuk edit/hapus jadwal yang sudah tersimpan, gunakan halaman
                <span class="font-semibold">Jadwal Harian</span>.
            </p>

            {{ $this->filterForm }}
        </div>

        @if (! $this->getActiveRumahSakitId())
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6 text-center text-sm text-gray-500">
            Pilih rumah sakit terlebih dahulu.
        </div>
        @else

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-2 text-left w-48">Poliklinik</th>
                            <th class="px-3 py-2 text-left w-48">Dokter</th>
                            <th class="px-3 py-2 text-left w-36">Tanggal</th>
                            <th class="px-3 py-2 text-left w-28">Jam Mulai</th>
                            <th class="px-3 py-2 text-left w-28">Jam Selesai</th>
                            <th class="px-3 py-2 text-center w-20">Executive</th>
                            <th class="px-3 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse ($rows as $key => $row)
                        <tr wire:key="row-{{ $key }}">
                            <td class="px-3 py-2">
                                <select wire:model="rows.{{ $key }}.poliklinik_id"
                                        class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                    <option value="">— Pilih Poliklinik —</option>
                                    @foreach ($this->getPoliklinikOptions() as $id => $nama)
                                        <option value="{{ $id }}">{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2">
                                <select wire:model="rows.{{ $key }}.dokter_id"
                                        class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                    <option value="">— Dokter (opsional) —</option>
                                    @foreach ($this->getDokterOptions($row['poliklinik_id']) as $id => $nama)
                                        <option value="{{ $id }}">{{ $nama }}</option>
                                    @endforeach
                                </select>
                                @if (! $row['dokter_id'])
                                <input type="text" wire:model="rows.{{ $key }}.nama_dokter" placeholder="atau ketik nama bebas"
                                       class="mt-1 w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <input type="date" wire:model="rows.{{ $key }}.tanggal"
                                       class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            </td>
                            <td class="px-3 py-2">
                                <input type="time" wire:model="rows.{{ $key }}.jam_mulai"
                                       class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            </td>
                            <td class="px-3 py-2">
                                <input type="time" wire:model="rows.{{ $key }}.jam_selesai"
                                       class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            </td>
                            <td class="px-3 py-2 text-center">
                                <input type="checkbox" wire:model="rows.{{ $key }}.is_executive"
                                       class="rounded border-gray-300 text-primary-600 shadow-sm">
                            </td>
                            <td class="px-3 py-2 text-center">
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
                            <td colspan="7" class="px-3 py-6 text-center text-sm text-gray-400">Belum ada baris — klik "Tambah Baris" di bawah.</td>
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
