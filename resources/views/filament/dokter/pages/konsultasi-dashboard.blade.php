@php

use App\Enums\StatusSesiKonsultasi;

@endphp
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
                            'bg-success-500' => $tersediaKonsultasi,
                            'bg-gray-400 dark:bg-gray-500' => ! $tersediaKonsultasi,
                        ])></span>
                        <div>
                            <p class="text-sm font-semibold text-gray-950 dark:text-white">Status Ketersediaan</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                @if($tersediaKonsultasi)
                                    Anda <span class="font-medium text-success-600 dark:text-success-400">tersedia</span> menerima konsultasi baru.
                                @else
                                    Anda <span class="font-medium text-gray-600 dark:text-gray-300">tidak tersedia</span> untuk konsultasi baru.
                                @endif
                            </p>
                        </div>
                    </div>

                    {{--
                        Toggle bawaan Filament (kelas `fi-fo-toggle` & markup yang sama persis
                        dipakai di form admin) di-entangle ke variabel khusus $tersediaKonsultasi
                        — bukan langsung ke atribut model — supaya state UI & persistensi DB
                        terpisah jelas. Lihat KonsultasiDashboard::updatedTersediaKonsultasi().
                    --}}
                    <div class="flex shrink-0 items-center gap-2">
                        <button
                            type="button"
                            role="switch"
                            x-data="{ state: $wire.entangle('tersediaKonsultasi').live }"
                            x-on:click="state = ! state"
                            x-bind:aria-checked="state?.toString()"
                            x-bind:class="state ? 'bg-success-600 dark:bg-success-500' : 'bg-gray-200 dark:bg-gray-700'"
                            wire:loading.attr="disabled"
                            wire:target="tersediaKonsultasi"
                            class="fi-fo-toggle relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent outline-none transition-colors duration-200 ease-in-out disabled:pointer-events-none disabled:opacity-70"
                        >
                            <span
                                class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                x-bind:class="{ 'translate-x-5 rtl:-translate-x-5': state, 'translate-x-0': ! state }"
                            ></span>
                        </button>

                        <span @class([
                            'text-xs font-semibold',
                            'text-success-600 dark:text-success-400' => $tersediaKonsultasi,
                            'text-gray-500 dark:text-gray-400' => ! $tersediaKonsultasi,
                        ])>
                            {{ $tersediaKonsultasi ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
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
                            <div
                                role="button"
                                tabindex="0"
                                wire:click="pilihSesi({{ $sesi->id }})"
                                wire:key="antrean-{{ $sesi->id }}"
                                x-on:keydown.enter="$wire.pilihSesi({{ $sesi->id }})"
                                @class([
                                    'w-full text-left rounded-xl border p-3 transition-colors duration-150 cursor-pointer',
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
                            </div>
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
                        wire:poll.visible.5s
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
