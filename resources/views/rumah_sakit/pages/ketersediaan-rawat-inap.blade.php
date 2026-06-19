<div wire:poll.30s>
    <x-page-hero title="Ketersediaan Rawat Inap" subtitle="Cek kamar kosong, terisi, dan reservasi secara real-time" />

    <div class="mt-5 w-10/12 mx-auto pb-16">

        {{-- Indikator waktu dimuat + countdown — wire:key dibuat beda tiap render (timestamp
             berubah) supaya Livewire mount ulang elemen ini dari nol setiap kali wire:poll
             jalan, sehingga countdown ikut reset balik ke 30 detik selaras dengan poll-nya --}}
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
            class="flex items-center justify-center gap-2 text-xs text-on-surface-variant text-center mb-6"
        >
            <span>Dimuat pukul {{ $loadedAt->translatedFormat('H:i:s, d F Y') }}</span>
            <span class="text-outline-variant">&middot;</span>
            <span class="inline-flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">autorenew</span>
                Memperbarui dalam <span class="font-bold tabular-nums" x-text="seconds"></span> detik
            </span>
        </div>

        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-green-600 rounded-2xl p-4 text-center shadow-sm">
                <p class="text-3xl font-bold text-white">{{ $ringkasan[1] }}</p>
                <p class="text-sm text-white/90 font-medium mt-1">Kosong</p>
            </div>
            <div class="bg-amber-500 rounded-2xl p-4 text-center shadow-sm">
                <p class="text-3xl font-bold text-white">{{ $ringkasan[2] }}</p>
                <p class="text-sm text-white/90 font-medium mt-1">Reservasi</p>
            </div>
            <div class="bg-red-600 rounded-2xl p-4 text-center shadow-sm">
                <p class="text-3xl font-bold text-white">{{ $ringkasan[3] }}</p>
                <p class="text-sm text-white/90 font-medium mt-1">Terisi</p>
            </div>
            <div class="bg-gray-600 rounded-2xl p-4 text-center shadow-sm">
                <p class="text-3xl font-bold text-white">{{ $ringkasan[6] }}</p>
                <p class="text-sm text-white/90 font-medium mt-1">Perbaikan</p>
            </div>
        </div>

        {{-- Tombol ke halaman Rawat Inap --}}
        <div class="flex justify-center mb-8">
            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.rawat_inap') }}"
               class="inline-flex items-center gap-2 text-sm font-semibold text-primary
                      border border-primary/30 hover:bg-primary/8 px-4 py-2 rounded-full transition-colors">
                <span class="material-symbols-outlined text-[18px]">list_alt</span>
                Lihat Daftar Rawat Inap
            </a>
        </div>

        {{-- Filter --}}
        <div class="flex flex-col sm:flex-row gap-3 mb-8">
            @include('rumah_sakit.pages._searchable-select', [
                'property'     => 'kelasFilter',
                'options'      => $kelasOptions->map(fn ($k) => ['value' => $k->id, 'label' => $k->nama])->values()->toArray(),
                'placeholder'  => '— Semua Kelas —',
                'currentValue' => $kelasFilter,
                'wrapperClass' => 'w-full sm:w-64',
            ])

            @include('rumah_sakit.pages._searchable-select', [
                'property'     => 'namaKamarFilter',
                'options'      => $namaKamarOptions->map(fn ($n) => ['value' => $n, 'label' => $n])->values()->toArray(),
                'placeholder'  => '— Semua Kamar —',
                'currentValue' => $namaKamarFilter,
                'wrapperClass' => 'w-full sm:w-64',
            ])
        </div>

        {{-- List Kamar --}}
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
                    <div class="bg-white rounded-2xl border border-outline-variant/20 shadow-sm p-5">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <h3 class="font-bold text-on-surface text-sm leading-snug">{{ $first['nama_kamar'] }}</h3>
                            @if($first['kelas_nama'])
                                <span class="shrink-0 text-[11px] font-bold uppercase tracking-wide bg-primary/10 text-primary px-2 py-1 rounded-full">
                                    {{ $first['kelas_nama'] }}
                                </span>
                            @endif
                        </div>

                        <div class="space-y-2">
                            @foreach($beds as $bed)
                                @php
                                    $colorClass = match($bed['status']) {
                                        1 => 'bg-green-100 text-green-700',
                                        2 => 'bg-amber-100 text-amber-700',
                                        3 => 'bg-red-100 text-red-700',
                                        6 => 'bg-gray-200 text-gray-600',
                                        default => 'bg-gray-200 text-gray-600',
                                    };
                                @endphp
                                <div class="flex items-center justify-between gap-2 text-sm">
                                    <span class="text-on-surface-variant">{{ $bed['tempat_tidur'] }}</span>
                                    <span class="text-[11px] font-bold px-2 py-1 rounded-full {{ $colorClass }}">
                                        {{ \App\Enums\StatusKetersediaanKamar::labelFor($bed['status']) }}
                                    </span>
                                </div>
                                @if($bed['keterangan'])
                                    <p class="text-xs text-on-surface-variant/80 italic">{{ $bed['keterangan'] }}</p>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>
