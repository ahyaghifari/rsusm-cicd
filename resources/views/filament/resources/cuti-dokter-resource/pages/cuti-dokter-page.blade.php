<div>
<x-filament-panels::page>

{{-- Sembunyikan sidebar Filament saat mode layar penuh (sama seperti Jadwal Harian) --}}
<style>
    .jp-fullscreen .fi-sidebar { display: none !important; }
    .jp-fullscreen .fi-main    { margin-inline-start: 0 !important; width: 100% !important; }
    .jp-fullscreen .fi-topbar  { padding-inline-start: 1rem !important; }
</style>

<div class="space-y-4"
    x-data="{ isFs: $wire.entangle('isFullscreen') }"
    x-init="document.body.classList.toggle('jp-fullscreen', isFs)"
    x-effect="isFs
        ? document.body.classList.add('jp-fullscreen')
        : document.body.classList.remove('jp-fullscreen')"
>

    {{-- ===================== FILTER (khusus super admin) ===================== --}}
    @if(\App\Filament\Resources\CutiDokterResource::isSuperAdmin())
    <div class="rounded-xl overflow-hidden shadow-sm ring-1 ring-primary-200 dark:ring-primary-700/50">
        <div class="bg-linear-to-r from-primary-600 to-primary-500 dark:from-primary-700 dark:to-primary-600 px-4 py-3 flex items-center gap-2 justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-white/90 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-white tracking-wide">Filter</span>
            </div>
            <button type="button" x-on:click="isFs = !isFs"
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium text-white/80 hover:text-white hover:bg-white/15 transition"
                :title="isFs ? 'Tampilkan Sidebar' : 'Sembunyikan Sidebar'">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path x-show="!isFs" stroke-linecap="round" stroke-linejoin="round"
                          d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/>
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
    @endif

    @if(! $selectedRumahSakitId)
        <x-filament::section>
            <div class="py-10 text-center flex flex-col items-center gap-3 text-gray-400 dark:text-gray-500">
                <p class="text-sm">Pilih <strong>Rumah Sakit</strong> terlebih dahulu.</p>
            </div>
        </x-filament::section>
    @else
        @php($dokterTerpilih = $this->getSelectedDokter())

        <div class="space-y-4">

            {{-- ===================== PILIH DOKTER (combobox) ===================== --}}
            <x-filament::section>
                <x-slot name="heading">Pilih Dokter</x-slot>

                @unless(\App\Filament\Resources\CutiDokterResource::isSuperAdmin())
                    <x-slot name="headerEnd">
                        <button type="button" x-on:click="isFs = !isFs"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                            <span x-text="isFs ? 'Tampilkan Sidebar' : 'Layar Penuh'"></span>
                        </button>
                    </x-slot>
                @endunless

                <div wire:key="combo-{{ $selectedRumahSakitId }}"
                     x-data="{ cari: @js($dokterTerpilih?->nama ?? ''), open: false }"
                     x-on:click.outside="open = false">
                    <div class="relative">
                        <input type="text" x-model="cari" placeholder="Cari nama dokter..."
                            x-on:focus="open = true; $el.select()"
                            x-on:input="open = true"
                            style="border-color:#d606b0;"
                            class="w-full rounded-lg border-2 bg-primary-50 dark:bg-primary-900/20 text-gray-900 dark:text-white text-sm font-medium py-2.5 pl-4 pr-10 shadow-sm focus:ring-2 focus:ring-primary-500 placeholder:text-primary-400 dark:placeholder:text-primary-300">
                        @if($dokterTerpilih)
                            <button type="button" title="Ganti dokter"
                                x-on:click="cari = ''; open = true; $wire.hapusPilihan()"
                                class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded-md text-gray-400 hover:text-red-500 transition">
                                <x-filament::icon icon="heroicon-m-x-mark" class="w-4 h-4"/>
                            </button>
                        @endif
                    </div>

                    <div x-show="open" x-cloak
                         class="mt-2 space-y-1 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700 p-1 bg-white dark:bg-gray-900"
                         style="max-height: 18rem;">
                        @forelse($this->getDokterList() as $d)
                            <button type="button" wire:key="dokter-{{ $d->id }}"
                                x-on:click="cari = @js($d->nama); open = false; $wire.pilihDokter({{ $d->id }})"
                                data-nama="{{ \Illuminate\Support\Str::lower($d->nama) }}"
                                x-show="cari === '' || $el.dataset.nama.includes(cari.toLowerCase())"
                                class="w-full flex items-center justify-between gap-2 text-left px-3 py-2 rounded-md text-sm transition hover:bg-primary-50 dark:hover:bg-primary-900/30 text-gray-700 dark:text-gray-300">
                                <span>{{ $d->nama }}</span>
                                @if($d->cuti_mendatang)
                                    <span class="shrink-0 text-xs font-bold rounded-full px-2 py-0.5 bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                        {{ $d->cuti_mendatang }} cuti
                                    </span>
                                @endif
                            </button>
                        @empty
                            <p class="text-sm text-gray-400 italic py-3 text-center">Belum ada dokter aktif di rumah sakit ini.</p>
                        @endforelse
                    </div>
                </div>
            </x-filament::section>

            {{-- ===================== CUTI DOKTER TERPILIH ===================== --}}
            <div class="space-y-4">
                @if(! $dokterTerpilih)
                    <x-filament::section>
                        <div class="py-10 text-center text-gray-400 dark:text-gray-500">
                            <p class="text-sm">Klik salah satu <strong>dokter</strong> untuk melihat dan mengisi cutinya.</p>
                        </div>
                    </x-filament::section>
                @else
                    {{-- Daftar cuti --}}
                    <x-filament::section>
                        <x-slot name="heading">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary-100 dark:bg-primary-900/60 text-primary-700 dark:text-primary-300 ring-1 ring-primary-200 dark:ring-primary-700">Cuti</span>
                                <span class="font-bold text-primary-600 dark:text-primary-400">{{ $dokterTerpilih->nama }}</span>
                            </div>
                        </x-slot>

                        @if($this->canEditCuti())
                            <x-slot name="headerEnd">
                                <x-filament::button wire:click="bukaFormTambah" icon="heroicon-m-plus">
                                    Tambah Cuti
                                </x-filament::button>
                            </x-slot>
                        @endif

                        <div class="overflow-x-auto bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
                            <table class="w-full text-left text-sm text-gray-700 dark:text-gray-300">
                                <thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                                    <tr>
                                        <th class="px-3 py-3 w-10 text-center text-xs font-semibold">No</th>
                                        <th class="px-3 py-3 text-xs font-semibold">Tanggal Mulai</th>
                                        <th class="px-3 py-3 text-xs font-semibold">Tanggal Sampai</th>
                                        <th class="px-3 py-3 text-xs font-semibold">Alasan</th>
                                        <th class="px-3 py-3 w-24"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @forelse($this->getCutiList() as $cuti)
                                        <tr wire:key="cuti-{{ $cuti->id }}"
                                            @class(['bg-primary-50/50 dark:bg-primary-900/10' => $editingId === $cuti->id])>
                                            <td class="px-3 py-2 text-xs text-gray-400 text-center">{{ $loop->iteration }}</td>
                                            <td class="px-3 py-2">{{ $cuti->tanggal_mulai->translatedFormat('d F Y') }}</td>
                                            <td class="px-3 py-2">{{ $cuti->tanggal_selesai->translatedFormat('d F Y') }}</td>
                                            <td class="px-3 py-2">{{ $cuti->alasan }}</td>
                                            <td class="px-3 py-2 text-right whitespace-nowrap">
                                                @if($this->canEditCuti())
                                                    <button wire:click="editCuti({{ $cuti->id }})" title="Ubah"
                                                        class="p-1.5 rounded-md text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition">
                                                        <x-filament::icon icon="heroicon-m-pencil-square" class="w-4 h-4"/>
                                                    </button>
                                                    <button wire:click="hapusCuti({{ $cuti->id }})"
                                                        wire:confirm="Hapus cuti ini? Jadwal harian pada tanggal tersebut dikembalikan ke Buka."
                                                        title="Hapus"
                                                        class="p-1.5 rounded-md text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                                        <x-filament::icon icon="heroicon-m-trash" class="w-4 h-4"/>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500 text-sm">
                                                Belum ada cuti untuk dokter ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-filament::section>

                    {{-- Form tambah / ubah cuti (modal) --}}
                    @if($this->canEditCuti())
                    <x-filament::modal id="cuti-form-modal" icon="heroicon-o-calendar-days" width="2xl">
                        <x-slot name="heading">{{ $editingId ? 'Ubah Cuti' : 'Tambah Cuti' }}</x-slot>

                        {{-- Pilih mode (sembunyi saat mengubah cuti lama — mode sudah ditentukan dari data) --}}
                        @unless($editingId)
                        <x-filament::tabs label="Mode cuti" class="mb-6">
                            @foreach(['satu_hari' => '1 Hari', 'rentang' => 'Rentang Tanggal', 'per_tanggal' => 'Per Tanggal'] as $key => $label)
                                <x-filament::tabs.item
                                    :active="$mode === $key"
                                    wire:click="$set('mode', '{{ $key }}')"
                                >
                                    {{ $label }}
                                </x-filament::tabs.item>
                            @endforeach
                        </x-filament::tabs>
                        @endunless

                        <div class="space-y-6 mb-2">
                            @if($mode === 'satu_hari')
                                <div>
                                    <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white mb-2 block">Tanggal <span class="text-danger-600">*</span></label>
                                    <x-filament::input.wrapper :valid="! $errors->has('tanggalSatuHari')">
                                        <x-filament::input type="date" wire:model="tanggalSatuHari" />
                                    </x-filament::input.wrapper>
                                    @error('tanggalSatuHari') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            @elseif($mode === 'rentang')
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white mb-2 block">Tanggal Mulai <span class="text-danger-600">*</span></label>
                                        <x-filament::input.wrapper :valid="! $errors->has('tanggalMulai')">
                                            <x-filament::input type="date" wire:model="tanggalMulai" />
                                        </x-filament::input.wrapper>
                                        @error('tanggalMulai') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white mb-2 block">Tanggal Sampai <span class="text-danger-600">*</span></label>
                                        <x-filament::input.wrapper :valid="! $errors->has('tanggalSelesai')">
                                            <x-filament::input type="date" wire:model="tanggalSelesai" />
                                        </x-filament::input.wrapper>
                                        @error('tanggalSelesai') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            @else
                                <div>
                                    <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white mb-2 block">Daftar Tanggal <span class="text-danger-600">*</span></label>
                                    <div class="space-y-2">
                                        @foreach($tanggalList as $i => $tgl)
                                            <div class="flex items-center gap-2" wire:key="tgl-{{ $i }}">
                                                <x-filament::input.wrapper class="flex-1" :valid="! $errors->has('tanggalList.'.$i)">
                                                    <x-filament::input type="date" wire:model="tanggalList.{{ $i }}" />
                                                </x-filament::input.wrapper>
                                                @if(count($tanggalList) > 1)
                                                    <x-filament::icon-button
                                                        icon="heroicon-m-x-mark"
                                                        color="danger"
                                                        label="Hapus tanggal"
                                                        wire:click="hapusTanggal({{ $i }})"
                                                    />
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('tanggalList') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
                                    @error('tanggalList.*') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror

                                    <x-filament::link tag="button" type="button" icon="heroicon-m-plus-circle" wire:click="tambahTanggal" class="mt-2">
                                        Tambah Tanggal
                                    </x-filament::link>
                                </div>
                            @endif

                            <div>
                                <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white mb-2 block">
                                    Alasan <span class="text-danger-600">*</span>
                                    <span class="font-normal text-gray-400">(tampil sebagai catatan di Jadwal Harian)</span>
                                </label>
                                <x-filament::input.wrapper :valid="! $errors->has('alasan')">
                                    <x-filament::input type="text" wire:model="alasan" maxlength="255" />
                                </x-filament::input.wrapper>
                                @error('alasan') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <x-slot name="footerActions">
                            <x-filament::button color="gray" wire:click="resetForm">Batal</x-filament::button>
                            <x-filament::button wire:click="simpanCuti" icon="heroicon-m-cloud-arrow-up">
                                {{ $editingId ? 'Simpan Perubahan' : 'Simpan Cuti' }}
                            </x-filament::button>
                        </x-slot>
                    </x-filament::modal>
                    @else
                        <p class="text-xs text-gray-400 italic">Mode lihat saja — akun Anda tidak memiliki izin mengubah cuti.</p>
                    @endif
                @endif
            </div>

            {{-- ===================== DOKTER YANG AKAN CUTI (mendatang, semua) ===================== --}}
            <x-filament::section>
                <x-slot name="heading">Dokter yang Akan Cuti</x-slot>
                <x-slot name="description">Semua cuti dari hari ini dan seterusnya, tanpa batas.</x-slot>

                <div class="overflow-x-auto bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
                    <table class="w-full text-left text-sm text-gray-700 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th class="px-3 py-3 w-10 text-center text-xs font-semibold">No</th>
                                <th class="px-3 py-3 text-xs font-semibold">Dokter</th>
                                <th class="px-3 py-3 text-xs font-semibold">Tanggal Mulai</th>
                                <th class="px-3 py-3 text-xs font-semibold">Tanggal Sampai</th>
                                <th class="px-3 py-3 text-xs font-semibold">Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($this->getCutiMendatang() as $cuti)
                                <tr wire:key="mendatang-{{ $cuti->id }}">
                                    <td class="px-3 py-2 text-xs text-gray-400 text-center">{{ $loop->iteration }}</td>
                                    <td class="px-3 py-2 font-medium text-primary-600 dark:text-primary-400">{{ $cuti->dokter?->nama }}</td>
                                    <td class="px-3 py-2">{{ $cuti->tanggal_mulai->translatedFormat('d F Y') }}</td>
                                    <td class="px-3 py-2">{{ $cuti->tanggal_selesai->translatedFormat('d F Y') }}</td>
                                    <td class="px-3 py-2">{{ $cuti->alasan }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500 text-sm">
                                        Belum ada cuti mendatang di rumah sakit ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>
    @endif

</div>

</x-filament-panels::page>
</div>
