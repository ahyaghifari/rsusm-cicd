<div>
    {{-- ============================================================ --}}
    {{-- HERO --}}
    {{-- ============================================================ --}}
    <div class="relative overflow-hidden min-h-48 flex items-end bg-primary">

        {{-- Gambar background (jika ada) --}}
        @if($poliklinik->gambar)
            <img
                src="{{ Storage::url($poliklinik->gambar) }}"
                alt="{{ $poliklinik->nama }}"
                class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-linear-to-t from-black/80 via-black/40 to-black/10"></div>
        @else
            {{-- Dekorasi fallback --}}
            <div class="absolute inset-0 pointer-events-none opacity-[0.04]"
                 style="background-image: radial-gradient(circle, white 1.5px, transparent 1.5px); background-size: 28px 28px;"></div>
            <div class="absolute -top-10 -right-10 w-56 h-56 bg-white/8 rounded-full pointer-events-none"></div>
            <div class="absolute -bottom-14 -left-14 w-64 h-64 bg-white/5 rounded-full pointer-events-none"></div>
        @endif

        <div class="relative z-10 w-10/12 mx-auto pb-10">
           
            {{-- Badge unit layanan --}}
            <span class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm text-white
                         text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full mb-4">
                <span class="material-symbols-outlined text-[14px]">domain</span>
                {{ $poliklinik->unitLayanan->nama }}
            </span>

            {{-- Judul --}}
            <h1 class="text-3xl md:text-4xl font-bold text-white leading-tight">
                {{ $poliklinik->nama }}
            </h1>
        </div>
    </div>
    {{-- END HERO --}}

    {{-- ============================================================ --}}
    {{-- KONTEN --}}
    {{-- ============================================================ --}}
    <div class="w-10/12 mx-auto py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

            {{-- ---------------------------------------------------- --}}
            {{-- KIRI: Deskripsi lengkap (2/3 lebar) --}}
            {{-- ---------------------------------------------------- --}}
            <div class="lg:col-span-2" data-aos="fade-right">
                @if($poliklinik->deskripsi)
                    <div class="flex items-center gap-3 mb-6">
                        <span class="w-1.5 h-8 bg-primary rounded-full shrink-0"></span>
                        <h2 class="text-xl font-bold text-on-surface">Tentang Layanan Ini</h2>
                    </div>
                    <div class="bg-white rounded-2xl border border-outline-variant/20 shadow-sm p-8
                                text-sm text-on-surface-variant leading-relaxed prose prose-sm max-w-none">
                        {!! str($poliklinik->deskripsi)->sanitizeHtml() !!}
                    </div>
                @else
                    <div class="text-center py-20 border-2 border-dashed border-outline-variant rounded-2xl">
                        <span class="material-symbols-outlined text-5xl text-outline/40 block mb-3">description</span>
                        <p class="text-on-surface-variant">Informasi layanan belum tersedia.</p>
                    </div>
                @endif
            </div>

            {{-- ---------------------------------------------------- --}}
            {{-- KANAN: Jadwal layanan (1/3 lebar) --}}
            {{-- ---------------------------------------------------- --}}
            <div data-aos="fade-left">
                @php $warna = $poliklinik->unitLayanan?->warnaHex() ?? '#4d51b2'; @endphp
                <div class="sticky top-4">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="w-1.5 h-8 rounded-full shrink-0"
                              style="background-color: {{ $warna }};"></span>
                        <h2 class="text-xl font-bold text-on-surface">Jadwal Layanan</h2>
                    </div>

                    @if($jadwalMingguan->isEmpty())
                        <div class="text-center py-12 border-2 border-dashed border-outline-variant rounded-2xl">
                            <span class="material-symbols-outlined text-5xl text-outline/40 block mb-3">event_busy</span>
                            <p class="text-on-surface-variant text-sm">Belum ada jadwal yang tersedia.</p>
                        </div>
                    @else
                        <div class="flex flex-col gap-3">
                            @foreach($jadwalMingguan as $hari => $sesi)
                                @php
                                    $hariLabel = \App\Enums\Hari::from($hari)->getLabel();
                                    $hariIni   = strtoupper(now()->locale('id')->dayName);
                                    $isToday   = $hari === $hariIni;
                                @endphp
                                <div class="bg-white rounded-xl border overflow-hidden shadow-sm"
                                     style="{{ $isToday
                                         ? "border-color: {$warna}66; box-shadow: 0 0 0 1px {$warna}33;"
                                         : 'border-color: #e5e7eb;' }}">

                                    {{-- Header hari --}}
                                    <div class="px-4 py-2.5 flex items-center justify-between border-b border-outline-variant/20"
                                         style="{{ $isToday
                                             ? "background-color: {$warna}; color: white; border-bottom-color: transparent;"
                                             : "background-color: {$warna}0f; color: {$warna};" }}">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                                            <span class="text-sm font-bold">{{ $hariLabel }}</span>
                                        </div>
                                        @if($isToday)
                                            <span class="text-[10px] font-bold uppercase tracking-widest
                                                         bg-white/20 px-2 py-0.5 rounded-full">Hari Ini</span>
                                        @endif
                                    </div>

                                    {{-- Daftar sesi --}}
                                    <div class="divide-y divide-outline-variant/20">
                                        @foreach($sesi as $s)
                                            <div class="px-4 py-3">
                                                <div class="flex items-start justify-between gap-2">
                                                    <span class="font-semibold text-sm text-on-surface leading-snug">
                                                        {{ $s->nama_dokter }}
                                                    </span>
                                                    <span @class([
                                                        'shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide',
                                                        'bg-green-100 text-green-700' => $s->status_layanan->value === 'BUKA',
                                                        'bg-red-100 text-red-700'     => $s->status_layanan->value === 'LIBUR',
                                                    ])>{{ $s->status_layanan->getLabel() }}</span>
                                                </div>
                                                <div class="flex items-center gap-1.5 mt-1">
                                                    <span class="material-symbols-outlined text-[13px]"
                                                          style="color: {{ $warna }}; opacity: 0.7;">schedule</span>
                                                    <span class="text-xs text-on-surface-variant">
                                                        {{ $s->jam_mulai?->format('H:i') }}
                                                        @if($s->jam_selesai) – {{ $s->jam_selesai->format('H:i') }} @endif
                                                    </span>
                                                </div>
                                                @if($s->catatan)
                                                    <p class="text-xs text-on-surface-variant/70 mt-1.5 italic leading-snug">
                                                        {{ $s->catatan }}
                                                    </p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
    {{-- END KONTEN --}}
</div>
