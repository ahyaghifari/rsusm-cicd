<div wire:poll.30s>
    <x-page-hero title="Ketersediaan Rawat Inap" subtitle="Cek kamar kosong, terisi, dan reservasi secara real-time" />

    <div class="w-10/12 mx-auto pb-16">

        {{-- ============================================================ --}}
        {{-- PANEL FILTER — kontrol utama halaman, overlap hero supaya     --}}
        {{-- jadi hal pertama yang dilihat sebelum data ringkasan/kamar.   --}}
        {{-- ============================================================ --}}
        <div class="relative z-20 bg-white border-2 border-primary shadow-lg -translate-y-5
                    rounded-2xl w-full max-w-3xl mx-auto">

            <div class="flex items-center justify-between gap-3 px-5 py-3 border-b border-outline-variant/15 bg-primary/5 rounded-t-2xl">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[18px]">tune</span>
                    <span class="text-sm font-bold text-on-surface">Cari Kamar</span>
                </div>

                {{-- Indikator live + countdown — wire:key dibuat beda tiap render (timestamp
                     berubah) supaya Alpine mount ulang elemen ini dari nol setiap kali
                     wire:poll jalan, sehingga countdown ikut reset balik ke 30 detik --}}
                <div
                    wire:key="ketersediaan-countdown-{{ $loadedAt->timestamp }}"
                    x-data="{
                        seconds: 30,
                        timer: null,
                        init() {
                            this.timer = setInterval(() => {
                                this.seconds = this.seconds > 0 ? this.seconds - 1 : 0;
                            }, 1000);
                        },
                        destroy() {
                            clearInterval(this.timer);
                        }
                    }"
                    class="flex items-center gap-1.5 text-[11px] text-on-surface-variant shrink-0"
                    title="Dimuat pukul {{ $loadedAt->translatedFormat('H:i:s, d F Y') }}"
                >
                    <span class="relative flex size-2">
                        <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                        <span class="relative inline-flex rounded-full size-2 bg-emerald-500"></span>
                    </span>
                    <span class="hidden sm:inline">Live &middot; perbarui</span>
                    <span class="font-bold tabular-nums" x-text="seconds"></span>d
                </div>
            </div>

            <div class="px-5 pt-4 flex items-center gap-2">
                <span class="text-xs font-bold text-on-surface-variant shrink-0">Tampilan</span>
                <div class="inline-flex rounded-full border border-outline-variant/30 p-0.5 bg-surface-container-lowest">
                    <button type="button" wire:click="$set('groupBy', 'kamar')"
                        class="px-3 py-1 rounded-full text-xs font-bold transition-colors
                               {{ $groupBy === 'kamar' ? 'bg-primary text-white' : 'text-on-surface-variant hover:text-on-surface' }}">
                        Per Kamar
                    </button>
                    <button type="button" wire:click="$set('groupBy', 'kelas')"
                        class="px-3 py-1 rounded-full text-xs font-bold transition-colors
                               {{ $groupBy === 'kelas' ? 'bg-primary text-white' : 'text-on-surface-variant hover:text-on-surface' }}">
                        Per Kelas
                    </button>
                </div>
            </div>

            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                @include('rumah_sakit.pages._searchable-select', [
                    'property'     => 'kelasFilter',
                    'options'      => $kelasOptions->map(fn ($k) => ['value' => $k->id, 'label' => $k->nama])->values()->toArray(),
                    'placeholder'  => '— Semua Kelas —',
                    'currentValue' => $kelasFilter,
                    'wrapperClass' => 'w-full',
                ])

                @include('rumah_sakit.pages._searchable-select', [
                    'property'     => 'namaKamarFilter',
                    'options'      => $namaKamarOptions->map(fn ($n) => ['value' => $n, 'label' => $n])->values()->toArray(),
                    'placeholder'  => '— Semua Kamar —',
                    'currentValue' => $namaKamarFilter,
                    'wrapperClass' => 'w-full',
                ])
            </div>

            @if($totalBed > 0)
            <div class="px-5 pb-4 -mt-1 text-xs text-on-surface-variant">
                Menampilkan <span class="font-bold text-primary tabular-nums">{{ $jumlahHasil }}</span>
                dari <span class="font-bold tabular-nums">{{ $totalBed }}</span> tempat tidur
                @if($kelasFilter || $namaKamarFilter)
                    &middot; <span class="italic">filter aktif</span>
                @endif
            </div>
            @endif
        </div>

        {{-- ============================================================ --}}
        {{-- RINGKASAN STATUS                                              --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
            <div class="bg-white rounded-2xl border border-outline-variant/20 shadow-sm p-4 flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-emerald-600 text-[20px]">bed</span>
                </div>
                <div class="min-w-0">
                    <p class="text-2xl font-bold text-on-surface leading-none tabular-nums">{{ $ringkasan[1] }}</p>
                    <p class="text-xs text-on-surface-variant font-medium mt-1">Kosong</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant/20 shadow-sm p-4 flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-amber-600 text-[20px]">event_available</span>
                </div>
                <div class="min-w-0">
                    <p class="text-2xl font-bold text-on-surface leading-none tabular-nums">{{ $ringkasan[2] }}</p>
                    <p class="text-xs text-on-surface-variant font-medium mt-1">Reservasi</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant/20 shadow-sm p-4 flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-rose-50 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-rose-600 text-[20px]">person</span>
                </div>
                <div class="min-w-0">
                    <p class="text-2xl font-bold text-on-surface leading-none tabular-nums">{{ $ringkasan[3] }}</p>
                    <p class="text-xs text-on-surface-variant font-medium mt-1">Terisi</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-outline-variant/20 shadow-sm p-4 flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-slate-500 text-[20px]">build</span>
                </div>
                <div class="min-w-0">
                    <p class="text-2xl font-bold text-on-surface leading-none tabular-nums">{{ $ringkasan[6] }}</p>
                    <p class="text-xs text-on-surface-variant font-medium mt-1">Perbaikan</p>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- LIST KAMAR                                                    --}}
        {{-- ============================================================ --}}
        @if($kamarList->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-20 h-20 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-5">
                    <span class="material-symbols-outlined text-4xl text-primary">bed</span>
                </div>
                <p class="text-lg font-semibold text-on-surface">Data ketersediaan belum tersedia</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($kamarList as $beds)
                    @php $first = $beds->first(); @endphp
                    <div class="bg-white rounded-2xl border border-outline-variant/20 shadow-sm overflow-hidden flex flex-col">
                        <div class="h-1 w-full shrink-0"
                             style="background: linear-gradient(to right, var(--color-primary), color-mix(in srgb, var(--color-primary) 50%, transparent));"></div>

                        <div class="p-5">
                            <div class="flex items-start justify-between gap-2 mb-3">
                                @if($groupBy === 'kelas')
                                    <h3 class="font-bold text-on-surface leading-snug">{{ $first['kelas_nama'] ?? 'Non Kelas' }}</h3>
                                    <span class="shrink-0 text-[11px] font-bold uppercase tracking-wide bg-primary/10 text-primary px-2 py-1 rounded-full">
                                        {{ $beds->count() }} bed
                                    </span>
                                @else
                                    <h3 class="font-bold text-on-surface text-sm leading-snug">{{ $first['nama_kamar'] }}</h3>
                                    @if($first['kelas_nama'])
                                        <span class="shrink-0 text-[11px] font-bold uppercase tracking-wide bg-primary/10 text-primary px-2 py-1 rounded-full">
                                            {{ $first['kelas_nama'] }}
                                        </span>
                                    @endif
                                @endif
                            </div>

                            <div class="space-y-2">
                                @foreach($beds as $bed)
                                    @php
                                        $colorClass = match($bed['status']) {
                                            1 => 'bg-emerald-100 text-emerald-700',
                                            2 => 'bg-amber-100 text-amber-700',
                                            3 => 'bg-rose-100 text-rose-700',
                                            6 => 'bg-slate-200 text-slate-600',
                                            default => 'bg-slate-200 text-slate-600',
                                        };
                                    @endphp
                                    <div class="flex items-center justify-between gap-2 text-sm">
                                         @if($groupBy === 'kamar')
                                        <span class="text-on-surface-variant">{{ $bed['tempat_tidur'] }}</span>
                                        @else
                                        <span class="text-on-surface-variant">{{ $bed['tempat_tidur'] }} - {{ $bed['nama_kamar'] }}</span>
                                        @endif
                                        <span class="text-[11px] font-bold px-2 py-1 rounded-full {{ $colorClass }} shrink-0">
                                            {{ \App\Enums\StatusKetersediaanKamar::labelFor($bed['status']) }}
                                        </span>
                                    </div>
                                    @if($bed['keterangan'])
                                        <p class="text-xs text-on-surface-variant/80 italic">{{ $bed['keterangan'] }}</p>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- CTA ke halaman Rawat Inap --}}
        <div class="flex justify-center my-10">
            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.rawat_inap') }}"
               class="inline-flex items-center gap-2 text-sm font-bold text-white
                      bg-primary hover:bg-primary/90 px-6 py-3 rounded-full
                      shadow-lg shadow-primary/30 hover:shadow-xl hover:scale-105 active:scale-95
                      transition-all duration-150">
                <span class="material-symbols-outlined text-[18px]">bed</span>
                Lihat Kamar Rawat Inap dan Fasilitasnya
            </a>
        </div>

    </div>
</div>
