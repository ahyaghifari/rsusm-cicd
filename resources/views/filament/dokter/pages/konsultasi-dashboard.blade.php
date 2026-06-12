@php
use App\Enums\StatusSesiKonsultasi;
use Illuminate\Support\Str;
@endphp

<x-filament-panels::page>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── Kolom kiri: status & antrean ─────────────────────────────────── --}}
    <div class="lg:col-span-1 flex flex-col gap-4">

        {{-- Toggle ketersediaan --}}
        <x-filament::section>
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div @class([
                        'relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full',
                        'bg-success-50' => $tersediaKonsultasi,
                        'bg-gray-100'          => ! $tersediaKonsultasi,
                    ])>
                        <span @class([
                            'h-3.5 w-3.5 rounded-full',
                            'bg-success-500' => $tersediaKonsultasi,
                            'bg-gray-400' => !$tersediaKonsultasi,
                        ])></span>
                        @if($tersediaKonsultasi)
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-success-400 opacity-25"></span>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900  leading-none">
                            {{ $tersediaKonsultasi ? 'Siap Menerima' : 'Tidak Tersedia' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $tersediaKonsultasi ? 'Pasien dapat menghubungi Anda' : 'Aktifkan untuk menerima sesi baru' }}
                        </p>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <button
                        type="button"
                        role="switch"
                        x-data="{ state: $wire.entangle('tersediaKonsultasi').live }"
                        x-on:click="state = ! state"
                        x-bind:aria-checked="state?.toString()"
                        x-bind:class="state ? 'bg-success-600' : 'bg-gray-200'"
                        wire:loading.attr="disabled"
                        wire:target="tersediaKonsultasi"
                        class="fi-fo-toggle relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent outline-none transition-colors duration-200 ease-in-out disabled:pointer-events-none disabled:opacity-70"
                    >
                        <span
                            class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                            x-bind:class="{ 'translate-x-5 rtl:-translate-x-5': state, 'translate-x-0': ! state }"
                        ></span>
                    </button>
                </div>
            </div>
        </x-filament::section>

        {{-- Antrean sesi --}}
        <x-filament::section class="flex-1">
            <x-slot name="heading">
                <div class="flex items-center justify-between w-full">
                    <span>Antrean</span>
                    @if($antrean->isNotEmpty())
                        <span class="inline-flex items-center justify-center h-5 min-w-[1.25rem] px-1.5 rounded-full bg-primary-100 text-xs font-bold text-primary-700">
                            {{ $antrean->count() }}
                        </span>
                    @endif
                </div>
            </x-slot>

            @if($antrean->isEmpty())
                <div class="flex flex-col items-center justify-center text-center py-10 gap-3">
                    <div class="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-inbox" class="h-6 w-6 text-gray-400" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-600">Antrean kosong</p>
                        <p class="text-xs text-gray-400 mt-0.5">Belum ada sesi yang menunggu</p>
                    </div>
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
                                'w-full text-left rounded-xl border p-3.5 transition-all duration-150 cursor-pointer',
                                // Dipilih + sedang BERLANGSUNG → hijau
                                'border-success-300 bg-success-50 shadow-sm ring-1 ring-success-200/60'
                                    => $sesiAktif?->id === $sesi->id && $sesi->status === StatusSesiKonsultasi::BERLANGSUNG,
                                // Dipilih + masih MENUNGGU → biru/primary
                                'border-primary-300 bg-primary-50 shadow-sm'
                                    => $sesiAktif?->id === $sesi->id && $sesi->status === StatusSesiKonsultasi::MENUNGGU,
                                // Tidak dipilih + BERLANGSUNG → tint hijau tipis agar tetap terlihat aktif
                                'border-success-200 bg-success-50/50 hover:bg-success-50 hover:border-success-300'
                                    => $sesiAktif?->id !== $sesi->id && $sesi->status === StatusSesiKonsultasi::BERLANGSUNG,
                                // Tidak dipilih + MENUNGGU → normal
                                'border-gray-200 hover:bg-gray-50 hover:border-gray-300'
                                    => $sesiAktif?->id !== $sesi->id && $sesi->status === StatusSesiKonsultasi::MENUNGGU,
                            ])
                        >
                            <div class="flex items-start gap-2.5">
                                <div class="h-8 w-8 shrink-0 rounded-full bg-primary-100 flex items-center justify-center text-xs font-bold text-primary-700">
                                    {{ strtoupper(substr($sesi->nama_pasien, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1.5">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $sesi->nama_pasien }}</p>
                                        <x-filament::badge size="sm" :color="$sesi->status->getColor()">
                                            {{ $sesi->status->getLabel() }}
                                        </x-filament::badge>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $sesi->kontak_pasien }}</p>

                                    {{-- Preview pesan terakhir + badge belum dibaca --}}
                                    <div class="flex items-center justify-between gap-2 mt-1.5">
                                        <p class="text-[11px] truncate flex-1 {{ $sesi->belum_dibaca > 0 ? 'text-gray-700 font-medium' : 'text-gray-400' }}">
                                            @if($sesi->latestPesan)
                                                @if($sesi->latestPesan->pengirim->value === 'DOKTER')
                                                    <span class="text-gray-400">Anda: </span>
                                                @endif
                                                {{ Str::limit($sesi->latestPesan->isi, 38) }}
                                            @else
                                                <span class="italic">Belum ada pesan</span>
                                            @endif
                                        </p>
                                        @if($sesi->belum_dibaca > 0)
                                            <span class="shrink-0 inline-flex items-center justify-center h-[18px] min-w-[18px] px-1 rounded-full bg-danger-500 text-white text-[10px] font-bold leading-none">
                                                {{ $sesi->belum_dibaca > 9 ? '9+' : $sesi->belum_dibaca }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($sesi->status === StatusSesiKonsultasi::MENUNGGU)
                                <div class="mt-2.5 pt-2.5 border-t border-gray-100">
                                    <x-filament::button
                                        size="xs"
                                        color="success"
                                        icon="heroicon-o-check-circle"
                                        wire:click.stop="terima({{ $sesi->id }})"
                                        class="w-full justify-center"
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

    {{-- ── Kolom kanan: jendela chat ─────────────────────────────────────── --}}
    <div class="lg:col-span-2">
        {{--
            Kartu custom (bukan <x-filament::section>) supaya kita punya kendali penuh
            atas layout flex: header & input area tetap (shrink-0), hanya area pesan
            yang di-scroll (flex-1 overflow-y-auto) — header tidak ikut terbawa saat scroll.
        --}}
        <div
            class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 flex flex-col overflow-hidden"
            style="height: 76vh; min-height: 520px;"
        >
            @if(! $sesiAktif)
                {{-- ── Empty state ──────────────────────────────────────────── --}}
                <div class="flex-1 flex flex-col items-center justify-center text-center p-10 gap-4">
                    <div class="h-16 w-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="h-8 w-8 text-gray-300" />
                    </div>
                    <div>
                        <p class="text-base font-semibold text-gray-600">Belum ada sesi terpilih</p>
                        <p class="text-sm text-gray-400 mt-1 max-w-xs mx-auto">
                            Pilih sesi dari antrean di sebelah kiri, atau terima sesi yang sedang menunggu.
                        </p>
                    </div>
                </div>
            @else
                {{-- ── Header: nama pasien + timer + aksi — TIDAK IKUT SCROLL ── --}}
                <div class="shrink-0 flex items-center justify-between gap-3 px-5 py-3.5 border-b border-gray-100 bg-gray-50/70">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="h-9 w-9 shrink-0 rounded-full bg-primary-100 flex items-center justify-center text-sm font-bold text-primary-700">
                            {{ strtoupper(substr($sesiAktif->nama_pasien, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate leading-none">{{ $sesiAktif->nama_pasien }}</p>
                            <p class="text-xs text-gray-500 truncate mt-0.5">{{ $sesiAktif->kontak_pasien }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <x-filament::badge :color="$sesiAktif->status->getColor()">
                            {{ $sesiAktif->status->getLabel() }}
                        </x-filament::badge>

                        {{-- Countdown sisa waktu sesi --}}
                        @if($sesiAktif->status === StatusSesiKonsultasi::BERLANGSUNG && $sesiAktif->berakhir_at)
                            <span
                                x-data="{ r: {{ $sesiAktif->sisaDetik() }} }"
                                x-init="
                                    const t = setInterval(() => { if (r > 0) r--; }, 1000);
                                    $watch('r', val => {
                                        if (val === 0 && !$wire.tampilFormAkhiri) $wire.siapAkhiri();
                                    });
                                    $cleanup(() => clearInterval(t));
                                "
                                :class="r <= 60 ? 'text-xs font-mono bg-red-50 text-red-600 px-2 py-1 rounded-lg border border-red-200' : 'text-xs font-mono bg-orange-50 text-orange-600 px-2 py-1 rounded-lg border border-orange-200'"
                                x-text="Math.floor(r/60).toString().padStart(2,'0') + ':' + (r%60).toString().padStart(2,'0')"
                            ></span>
                        @endif

                        @if($sesiAktif->status === StatusSesiKonsultasi::BERLANGSUNG)
                            @if($tampilFormAkhiri)
                                <x-filament::button size="xs" color="gray" wire:click="batalAkhiri">
                                    Batalkan
                                </x-filament::button>
                            @else
                                <x-filament::button
                                    size="xs"
                                    color="danger"
                                    icon="heroicon-o-stop-circle"
                                    wire:click="siapAkhiri"
                                >
                                    Akhiri Sesi
                                </x-filament::button>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- ── Area pesan: HANYA INI yang bisa discroll ─────────────── --}}
                <div
                    id="dokter-chat-msgs"
                    class="flex-1 overflow-y-auto px-5 py-4 flex flex-col gap-3"
                    wire:poll.visible.5s
                    x-init="
                        const el = $el;
                        el.scrollTop = el.scrollHeight;
                        const obs = new MutationObserver(() => el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' }));
                        obs.observe(el, { childList: true, subtree: true });
                        $cleanup(() => obs.disconnect());
                    "
                >
                    @forelse($sesiAktif->pesan as $p)
                        @if($p->pengirim->value === 'PASIEN')
                            <div class="flex items-end gap-2 shrink-0">
                                <div class="h-6 w-6 shrink-0 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-600 ">
                                    {{ strtoupper(substr($sesiAktif->nama_pasien, 0, 1)) }}
                                </div>
                                <div class="flex flex-col items-start max-w-[72%]">
                                    <div class="px-3.5 py-2 rounded-2xl rounded-bl-md bg-gray-100 text-sm text-gray-900 leading-snug break-words">
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
                        <div class="flex-1 flex flex-col items-center justify-center gap-2 text-center py-10">
                            <x-filament::icon icon="heroicon-o-chat-bubble-oval-left" class="h-8 w-8 text-gray-300" />
                            <p class="text-sm text-gray-400">Belum ada pesan. Tunggu pasien memulai percakapan.</p>
                        </div>
                    @endforelse
                </div>

                {{-- ── Area bawah: input chat / form kesimpulan — TIDAK IKUT SCROLL ─ --}}
                <div class="shrink-0 border-t border-gray-100 bg-gray-50/70">
                    @if($sesiAktif->status === StatusSesiKonsultasi::BERLANGSUNG)

                        @if($tampilFormAkhiri)
                            {{-- Form kesimpulan sebelum akhiri sesi --}}
                            <div class="px-5 py-4 flex flex-col gap-3">
                                <div>
                                    <p class="text-xs font-semibold text-gray-700 mb-1.5">
                                        Kesimpulan / Catatan Dokter
                                        <span class="font-normal text-gray-400">(opsional)</span>
                                    </p>
                                    <textarea
                                        wire:model="kesimpulanInput"
                                        rows="3"
                                        maxlength="2000"
                                        placeholder="Tuliskan kesimpulan, saran, atau catatan untuk pasien. Pasien dapat membaca ini setelah sesi selesai."
                                        class="w-full rounded-lg border border-gray-200 bg-white text-sm text-gray-900 placeholder-gray-400 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 resize-none leading-snug"
                                    ></textarea>
                                </div>
                                <div class="flex items-center gap-2 justify-end">
                                    <x-filament::button size="sm" color="gray" wire:click="batalAkhiri">
                                        Batalkan
                                    </x-filament::button>
                                    <x-filament::button
                                        size="sm"
                                        color="danger"
                                        icon="heroicon-o-stop-circle"
                                        wire:click="akhiriDenganKesimpulan"
                                        wire:loading.attr="disabled"
                                        wire:target="akhiriDenganKesimpulan"
                                    >
                                        Simpan & Akhiri Sesi
                                    </x-filament::button>
                                </div>
                            </div>
                        @else
                            {{-- Input chat normal --}}
                            <div class="px-5 py-3.5">
                                <form wire:submit="kirimBalasan" class="flex gap-2 items-center">
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
                            </div>
                        @endif

                    @elseif($sesiAktif->status === StatusSesiKonsultasi::MENUNGGU)
                        <div class="px-5 py-3.5">
                            <p class="text-xs text-gray-400 text-center">
                                Terima sesi ini dari antrean untuk mulai membalas.
                            </p>
                        </div>
                    @else
                        <div class="px-5 py-3.5">
                            <p class="text-xs text-gray-400 text-center">
                                Sesi ini sudah <span class="font-semibold">{{ $sesiAktif->status->getLabel() }}</span> — percakapan bersifat baca saja.
                            </p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

</div>

<script src="{{ asset('js/konsultasi-notifikasi.js') }}"></script>
<script>
    document.addEventListener('livewire:init', () => {
        window.Echo.private('konsultasi.dokter.{{ $dokter->id }}')
            .listen('SesiStatusBerubah', (payload) => {
                if (payload.status === 'MENUNGGU') {
                    playAntrianSound();
                }
            });
    });
</script>
</x-filament-panels::page>
