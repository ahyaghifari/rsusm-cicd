@php
use App\Enums\StatusSesiKonsultasi;
@endphp

<x-filament-panels::page>
<div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

    {{-- ── Panel kiri: daftar riwayat ───────────────────────────────────── --}}
    <div class="lg:col-span-2">
        <div
            class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 flex flex-col overflow-hidden"
            style="height: 80vh; min-height: 520px;"
        >
            {{-- Search bar --}}
            <div class="shrink-0 px-4 py-3 border-b border-gray-100">
                <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
                    <x-filament::input
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Cari nama pasien…"
                    />
                </x-filament::input.wrapper>
            </div>

            {{-- List sesi --}}
            <div class="flex-1 overflow-y-auto divide-y divide-gray-50">
                @if($sesiList->isEmpty())
                    <div class="flex flex-col items-center justify-center h-full text-center p-8 gap-3">
                        <div class="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center">
                            <x-filament::icon icon="heroicon-o-folder-open" class="h-6 w-6 text-gray-400" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-600">Belum ada riwayat</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                @if($search)
                                    Tidak ditemukan untuk "<span class="font-medium">{{ $search }}</span>"
                                @else
                                    Sesi yang selesai akan tampil di sini
                                @endif
                            </p>
                        </div>
                    </div>
                @else
                    @foreach($sesiList as $sesi)
                        <button
                            type="button"
                            wire:click="lihatDetail({{ $sesi->id }})"
                            @class([
                                'w-full text-left px-4 py-3.5 transition-colors border-l-2',
                                'bg-primary-50 border-l-primary-500' => $detailSesiId === $sesi->id,
                                'hover:bg-gray-50 border-l-transparent' => $detailSesiId !== $sesi->id,
                            ])
                        >
                            <div class="flex items-start gap-3">
                                <div class="h-8 w-8 shrink-0 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-800">
                                    {{ strtoupper(substr($sesi->nama_pasien, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $sesi->nama_pasien }}</p>
                                        <x-filament::badge size="sm" :color="$sesi->status->getColor()">
                                            {{ $sesi->status->getLabel() }}
                                        </x-filament::badge>
                                    </div>
                                    <p class="text-xs text-gray-500 truncate mt-0.5">{{ $sesi->kontak_pasien }}</p>
                                    <div class="flex items-center gap-3 mt-1.5 text-[11px] text-gray-400">
                                        <span>
                                            <x-filament::icon icon="heroicon-o-calendar" class="inline h-3 w-3 mr-0.5 -mt-0.5" />
                                            {{ $sesi->created_at->format('d M Y') }}
                                        </span>
                                        <span>
                                            <x-filament::icon icon="heroicon-o-chat-bubble-left-ellipsis" class="inline h-3 w-3 mr-0.5 -mt-0.5" />
                                            {{ $sesi->pesan_count }} pesan
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </button>
                    @endforeach
                @endif
            </div>

            {{-- Pagination --}}
            @if($sesiList->hasPages())
                <div class="shrink-0 px-4 py-3 border-t border-gray-100 bg-gray-50/50">
                    {{ $sesiList->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ── Panel kanan: transkrip percakapan ────────────────────────────── --}}
    <div class="lg:col-span-3">
        <div
            class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 flex flex-col overflow-hidden"
            style="height: 80vh; min-height: 520px;"
        >
            @if(! $detailSesi)
                <div class="flex-1 flex flex-col items-center justify-center text-center p-10 gap-4">
                    <div class="h-16 w-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-document-text" class="h-8 w-8 text-gray-300" />
                    </div>
                    <div>
                        <p class="text-base font-semibold text-gray-600">Pilih sesi untuk melihat transkrip</p>
                        <p class="text-sm text-gray-400 mt-1">Klik salah satu sesi dari daftar di sebelah kiri</p>
                    </div>
                </div>
            @else
                {{-- Header transkrip --}}
                <div class="shrink-0 flex items-center justify-between gap-3 px-5 py-3.5 border-b border-gray-100 bg-gray-50/70 ">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="h-9 w-9 shrink-0 rounded-full bg-primary-100  flex items-center justify-center text-sm font-bold text-primary-700 ">
                            {{ strtoupper(substr($detailSesi->nama_pasien, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate leading-none">{{ $detailSesi->nama_pasien }}</p>
                            <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $detailSesi->kontak_pasien }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        {{-- Info durasi --}}
                        @if($detailSesi->mulai_at)
                            <div class="hidden sm:block text-right">
                                <p class="text-xs text-gray-500">{{ $detailSesi->mulai_at->format('d M Y, H:i') }}</p>
                                @if($detailSesi->berakhir_at)
                                    <p class="text-[10px] text-gray-400">
                                        Durasi: {{ $detailSesi->mulai_at->diffInMinutes($detailSesi->berakhir_at) }} menit
                                    </p>
                                @endif
                            </div>
                        @endif

                        <x-filament::badge :color="$detailSesi->status->getColor()">
                            {{ $detailSesi->status->getLabel() }}
                        </x-filament::badge>

                        <button
                            type="button"
                            wire:click="tutupDetail"
                            class="p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                        >
                            <x-filament::icon icon="heroicon-m-x-mark" class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                {{-- Transkrip pesan --}}
                <div class="flex-1 overflow-y-auto px-5 py-4 flex flex-col gap-3">
                    @forelse($detailSesi->pesan as $p)
                        @if($p->pengirim->value === 'PASIEN')
                            <div class="flex items-end gap-2 shrink-0">
                                <div class="h-6 w-6 shrink-0 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-700">
                                    {{ strtoupper(substr($detailSesi->nama_pasien, 0, 1)) }}
                                </div>
                                <div class="flex flex-col items-start max-w-[72%]">
                                    <div class="px-3.5 py-2 rounded-2xl rounded-bl-md bg-gray-100  text-sm text-gray-900  leading-snug break-words">
                                        {{ $p->isi }}
                                    </div>
                                    <span class="text-[10px] text-gray-400 mt-0.5 ml-1">{{ $p->created_at->format('H:i') }}</span>
                                </div>
                            </div>
                        @else
                            <div class="flex items-end justify-end gap-2 shrink-0">
                                <div class="flex flex-col items-end max-w-[72%]">
                                    <div class="px-3.5 py-2 rounded-2xl rounded-br-md bg-primary-600 text-white text-sm leading-snug break-words">
                                        {{ $p->isi }}
                                    </div>
                                    <span class="text-[10px] text-gray-400 mt-0.5 mr-1">{{ $p->created_at->format('H:i') }}</span>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="flex-1 flex items-center justify-center text-center py-10">
                            <p class="text-sm text-gray-400">Tidak ada pesan dalam sesi ini.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Kesimpulan dokter --}}
                <div class="shrink-0 border-t border-gray-100">
                    @if($editingKesimpulan)
                        <div class="px-5 py-4 flex flex-col gap-3 bg-blue-50/60">
                            <p class="text-xs font-semibold text-blue-800">Edit Kesimpulan / Catatan Dokter</p>
                            <textarea
                                wire:model="kesimpulanEdit"
                                rows="3"
                                maxlength="2000"
                                placeholder="Tuliskan kesimpulan atau catatan untuk pasien…"
                                class="w-full rounded-lg border border-blue-200 bg-white text-sm text-gray-900 placeholder-gray-400 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 resize-none leading-snug"
                            ></textarea>
                            <div class="flex items-center gap-2 justify-end">
                                <x-filament::button size="sm" color="gray" wire:click="batalEditKesimpulan">Batal</x-filament::button>
                                <x-filament::button size="sm" color="primary" icon="heroicon-o-check" wire:click="simpanKesimpulan" wire:loading.attr="disabled" wire:target="simpanKesimpulan">
                                    Simpan
                                </x-filament::button>
                            </div>
                        </div>
                    @else
                        <div class="px-5 py-3.5 flex items-start gap-3 bg-blue-50/40">
                            <div class="flex-1 min-w-0">
                                @if($detailSesi->kesimpulan)
                                    <p class="text-[11px] font-semibold text-blue-700 mb-1">Catatan Dokter</p>
                                    <p class="text-sm text-gray-700 leading-snug whitespace-pre-line">{{ $detailSesi->kesimpulan }}</p>
                                @else
                                    <p class="text-xs text-gray-400 italic">Belum ada catatan dokter untuk sesi ini.</p>
                                @endif
                            </div>
                            <button
                                type="button"
                                wire:click="mulaiEditKesimpulan"
                                class="shrink-0 flex items-center gap-1 text-[11px] text-blue-600 hover:text-blue-800 font-medium transition-colors mt-0.5"
                            >
                                <x-filament::icon icon="heroicon-o-pencil-square" class="h-3.5 w-3.5" />
                                {{ $detailSesi->kesimpulan ? 'Edit' : 'Tambah catatan' }}
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Footer info --}}
                <div class="shrink-0 px-5 py-2.5 border-t border-gray-100 bg-gray-50/70">
                    <p class="text-xs text-gray-400 text-center">
                        Total {{ $detailSesi->pesan->count() }} pesan
                        @if($detailSesi->mulai_at && $detailSesi->berakhir_at)
                            · {{ $detailSesi->mulai_at->format('H:i') }} – {{ $detailSesi->berakhir_at->format('H:i') }}
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

</div>
</x-filament-panels::page>
