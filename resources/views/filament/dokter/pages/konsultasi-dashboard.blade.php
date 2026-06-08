<?php

use App\Enums\StatusSesiKonsultasi;

?>
<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Kolom kiri: status & antrean ─────────────────────────────── --}}
        <div class="lg:col-span-1 flex flex-col gap-6">

            {{-- Quick-toggle ketersediaan --}}
            <x-filament::section>
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2.5">
                        <span @class([
                            'flex h-2.5 w-2.5 rounded-full',
                            'bg-success-500' => $dokter->tersedia_konsultasi,
                            'bg-gray-400 dark:bg-gray-500' => ! $dokter->tersedia_konsultasi,
                        ])></span>
                        <div>
                            <p class="text-sm font-semibold text-gray-950 dark:text-white">Status Ketersediaan</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                @if($dokter->tersedia_konsultasi)
                                    Anda <span class="font-medium text-success-600 dark:text-success-400">tersedia</span> menerima konsultasi baru.
                                @else
                                    Anda <span class="font-medium text-gray-600 dark:text-gray-300">tidak tersedia</span> untuk konsultasi baru.
                                @endif
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        wire:click="toggleTersedia"
                        wire:loading.attr="disabled"
                        wire:target="toggleTersedia"
                        role="switch"
                        aria-checked="{{ $dokter->tersedia_konsultasi ? 'true' : 'false' }}"
                        @class([
                            'group flex shrink-0 items-center gap-2 rounded-full border py-1 pl-1 pr-3 transition-colors duration-200 disabled:cursor-not-allowed disabled:opacity-60',
                            'border-success-600 bg-success-600 dark:border-success-500 dark:bg-success-500' => $dokter->tersedia_konsultasi,
                            'border-gray-300 bg-gray-100 dark:border-gray-600 dark:bg-gray-700' => ! $dokter->tersedia_konsultasi,
                        ])
                    >
                        <span @class([
                            'inline-flex h-4 w-4 shrink-0 transform rounded-full bg-white shadow transition-transform duration-200',
                            'translate-x-4' => $dokter->tersedia_konsultasi,
                            'translate-x-0' => ! $dokter->tersedia_konsultasi,
                        ])></span>
                        <span @class([
                            'text-xs font-semibold',
                            'text-white' => $dokter->tersedia_konsultasi,
                            'text-gray-600 dark:text-gray-300' => ! $dokter->tersedia_konsultasi,
                        ])>
                            {{ $dokter->tersedia_konsultasi ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </button>
                </div>
            </x-filament::section>

            {{-- Antrean sesi --}}
            <x-filament::section>
                <x-slot name="heading">Antrean Konsultasi</x-slot>

                @if($antrean->isEmpty())
                    <div class="flex flex-col items-center justify-center text-center py-10 gap-2">
                        <x-filament::icon icon="heroicon-o-inbox" class="h-8 w-8 text-gray-400" />
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada sesi konsultasi yang menunggu atau berlangsung.</p>
                    </div>
                @else
                    <div class="flex flex-col gap-2">
                        @foreach($antrean as $sesi)
                            <button
                                type="button"
                                wire:click="pilihSesi({{ $sesi->id }})"
                                @class([
                                    'w-full text-left rounded-xl border p-3 transition-colors duration-150',
                                    'border-primary-500 bg-primary-50 dark:bg-primary-500/10' => $sesiAktif?->id === $sesi->id,
                                    'border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5' => $sesiAktif?->id !== $sesi->id,
                                ])
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-medium text-gray-950 dark:text-white truncate">{{ $sesi->nama_pasien }}</p>
                                    <x-filament::badge :color="$sesi->status->getColor()">
                                        {{ $sesi->status->getLabel() }}
                                    </x-filament::badge>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Diminta {{ $sesi->created_at->diffForHumans() }}
                                </p>

                                @if($sesi->status === StatusSesiKonsultasi::MENUNGGU)
                                    <div class="mt-2.5">
                                        <x-filament::button
                                            size="xs"
                                            color="success"
                                            icon="heroicon-o-check"
                                            wire:click.stop="terima({{ $sesi->id }})"
                                        >
                                            Terima Sesi
                                        </x-filament::button>
                                    </div>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>
        </div>

        {{-- ── Kolom kanan: jendela chat sesi aktif ─────────────────────── --}}
        <div class="lg:col-span-2">
            <x-filament::section>
                @if(! $sesiAktif)
                    <div class="flex flex-col items-center justify-center text-center py-20 gap-2">
                        <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="h-10 w-10 text-gray-400" />
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Pilih sesi dari antrean untuk membuka percakapan, atau terima sesi yang sedang menunggu.
                        </p>
                    </div>
                @else
                    <div
                        id="dokter-chat-msgs"
                        class="flex flex-col gap-3 h-[55vh] min-h-[360px] overflow-y-auto pr-1"
                        x-init="
                            const el = $el;
                            el.scrollTop = el.scrollHeight;
                            const observer = new MutationObserver(() => {
                                el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
                            });
                            observer.observe(el, { childList: true, subtree: true });
                        "
                    >
                        <div class="flex items-center justify-between gap-3 pb-3 border-b border-gray-200 dark:border-white/10 shrink-0">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-950 dark:text-white truncate">{{ $sesiAktif->nama_pasien }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $sesiAktif->kontak_pasien }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <x-filament::badge :color="$sesiAktif->status->getColor()">
                                    {{ $sesiAktif->status->getLabel() }}
                                </x-filament::badge>

                                @if($sesiAktif->status === StatusSesiKonsultasi::BERLANGSUNG)
                                    <x-filament::button
                                        size="xs"
                                        color="danger"
                                        icon="heroicon-o-stop-circle"
                                        wire:click="akhiri({{ $sesiAktif->id }})"
                                        wire:confirm="Akhiri sesi konsultasi ini sekarang?"
                                    >
                                        Akhiri Sesi
                                    </x-filament::button>
                                @endif
                            </div>
                        </div>

                        @forelse($sesiAktif->pesan as $p)
                            @if($p->pengirim->value === 'PASIEN')
                            <div class="flex flex-row-reverse gap-2 items-end shrink-0">
                                <div class="flex flex-col items-end max-w-[75%]">
                                    <div class="px-3 py-2 rounded-[15px] rounded-br-[4px] bg-gray-100 dark:bg-white/5 text-sm leading-snug break-words w-full">
                                        {{ $p->isi }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">{{ $p->created_at->format('H:i') }}</div>
                                </div>
                            </div>
                            @else
                            <div class="flex flex-row gap-2 items-end shrink-0">
                                <div class="flex flex-col items-start max-w-[75%]">
                                    <div class="px-3 py-2 rounded-[15px] rounded-bl-[4px] bg-primary-600 text-white text-sm leading-snug break-words w-full">
                                        {{ $p->isi }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">{{ $p->created_at->format('H:i') }}</div>
                                </div>
                            </div>
                            @endif
                        @empty
                            <div class="flex-1 flex items-center justify-center">
                                <p class="text-sm text-gray-400">Belum ada percakapan pada sesi ini.</p>
                            </div>
                        @endforelse
                    </div>

                    @if($sesiAktif->status === StatusSesiKonsultasi::BERLANGSUNG)
                        <form wire:submit="kirimBalasan" class="mt-4 flex gap-2 items-start shrink-0">
                            <div class="flex-1">
                                <x-filament::input.wrapper>
                                    <x-filament::input
                                        type="text"
                                        wire:model="balasan"
                                        wire:keydown.enter.prevent="kirimBalasan"
                                        placeholder="Ketik balasan Anda…"
                                        maxlength="1000"
                                        wire:loading.attr="disabled"
                                        wire:target="kirimBalasan"
                                    />
                                </x-filament::input.wrapper>
                                @error('balasan')
                                    <p class="text-xs text-danger-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <x-filament::button
                                type="submit"
                                icon="heroicon-o-paper-airplane"
                                wire:loading.attr="disabled"
                                wire:target="kirimBalasan"
                            >
                                Kirim
                            </x-filament::button>
                        </form>
                    @elseif($sesiAktif->status === StatusSesiKonsultasi::MENUNGGU)
                        <p class="mt-4 text-xs text-gray-500 dark:text-gray-400 text-center">
                            Terima sesi ini terlebih dahulu dari daftar antrean untuk mulai membalas.
                        </p>
                    @else
                        <p class="mt-4 text-xs text-gray-500 dark:text-gray-400 text-center">
                            Sesi ini sudah {{ $sesiAktif->status->getLabel() }} — percakapan bersifat baca saja.
                        </p>
                    @endif
                @endif
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>
