<div>
    {{-- ============================================================ --}}
    {{-- HERO --}}
    {{-- ============================================================ --}}
    <div class="relative overflow-hidden min-h-48 flex items-end bg-primary">

        {{-- Gambar background (jika ada) --}}
       
        {{-- Dekorasi fallback --}}
        <div class="absolute inset-0 pointer-events-none opacity-[0.04]"
                style="background-image: radial-gradient(circle, white 1.5px, transparent 1.5px); background-size: 28px 28px;"></div>
        <div class="absolute -top-10 -right-10 w-56 h-56 bg-white/8 rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-14 -left-14 w-64 h-64 bg-white/5 rounded-full pointer-events-none"></div>

        <div class="relative z-10 w-10/12 mx-auto pb-10">
           
            {{-- Badge unit layanan --}}
            <span class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm text-white
                         text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full mb-4">
                <span class="material-symbols-outlined text-[14px]">domain</span>
                {{ $poliklinik->unitLayanan->nama }}
            </span>

            {{-- Judul --}}
            <div class="flex items-center gap-4">
                @if($poliklinik->gambar)
                    <div class="w-14 h-14 rounded-2xl bg-white/90 flex items-center justify-center shrink-0 overflow-hidden shadow-lg">
                        <img src="{{ Storage::url($poliklinik->gambar) }}"
                             alt="{{ $poliklinik->nama }}"
                             class="w-10 h-10 object-contain">
                    </div>
                @endif
                <h1 class="text-3xl md:text-4xl font-bold text-white leading-tight">
                    {{ $poliklinik->nama }}
                </h1>
            </div>
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
                                text-base text-on-surface-variant leading-relaxed prose prose-sm max-w-none">
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
                                    $hariIniMap = ['MINGGU','SENIN','SELASA','RABU','KAMIS','JUMAT','SABTU'];
                                    $isToday   = $hari === $hariIniMap[now()->dayOfWeek];
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
                                            @php
                                                $namaTampil = $s->nama_dokter ?? $s->dokter?->nama ?? '—';
                                            @endphp
                                            <div class="px-4 py-3">
                                                <div class="flex items-center gap-2.5">
                                                    {{-- Avatar dokter --}}
                                                    @if($s->dokter?->foto)
                                                        <img src="{{ Storage::url($s->dokter->foto) }}"
                                                             alt="{{ $namaTampil }}"
                                                             class="w-8 h-8 rounded-full object-cover shrink-0">
                                                    @else
                                                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                                                             style="background-color: {{ $warna }}18;">
                                                            <span class="material-symbols-outlined text-[15px]"
                                                                  style="color: {{ $warna }};">person</span>
                                                        </div>
                                                    @endif
                                                    <div class="flex-1 min-w-0">
                                                        <p class="font-semibold text-sm text-on-surface leading-snug truncate">
                                                            {{ $namaTampil }}
                                                        </p>
                                                        <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                                                            @if($s->waktu_mulai)
                                                                <span class="flex items-center gap-1 text-xs text-on-surface-variant">
                                                                    <span class="material-symbols-outlined text-[12px]"
                                                                          style="color: {{ $warna }}; opacity: 0.7;">schedule</span>
                                                                    <span class="tabular-nums">
                                                                        {{ $s->waktu_mulai->format('H:i') }}
                                                                        @if($s->waktu_selesai)
                                                                            &ndash; {{ $s->waktu_selesai->format('H:i') }}
                                                                        @else
                                                                            &ndash; Selesai
                                                                        @endif
                                                                    </span>
                                                                </span>
                                                            @endif
                                                            @if($s->sesuai_perjanjian)
                                                                <span class="inline-flex items-center gap-0.5 text-[10px] font-bold
                                                                             text-amber-700 bg-amber-50 border border-amber-200
                                                                             px-1.5 py-0.5 rounded-full shrink-0">
                                                                    <span class="material-symbols-outlined text-[10px]">calendar_clock</span>
                                                                    Perjanjian
                                                                </span>
                                                            @endif
                                                        </div>
                                                        @if($s->catatan)
                                                            <p class="text-[11px] text-on-surface-variant/60 italic mt-0.5 leading-snug">
                                                                {{ $s->catatan }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
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
