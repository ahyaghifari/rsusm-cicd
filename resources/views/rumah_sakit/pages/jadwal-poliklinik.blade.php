<div>
    <x-page-hero
        title="Jadwal Poliklinik"
        subtitle="Cek jadwal layanan poliklinik dan dokter yang tersedia setiap harinya."
    />

    <div class="w-10/12 mx-auto py-10">

        {{-- ============================================================ --}}
        {{-- TAB HARI --}}
        {{-- ============================================================ --}}
        <div class="mb-8 text-center">
            <p class="text-xs text-on-surface-variant uppercase tracking-widest font-semibold mb-3">Pilih Hari</p>
            <div class="inline-flex flex-wrap justify-center gap-2" role="tablist">
                @foreach($hariList as $hari)
                    @php
                        $isActive = $activeHari === $hari;
                        $isToday  = $hariIni === $hari;
                        $label    = ucfirst(strtolower($hari));
                    @endphp
                    <button
                        wire:click="setHari('{{ $hari }}')"
                        role="tab"
                        class="relative inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full font-semibold text-sm
                               transition-all duration-200 focus:outline-none
                               {{ $isActive
                                   ? 'bg-tertiary text-white shadow-lg shadow-tertiary/25'
                                   : 'bg-surface-container text-on-surface-variant hover:bg-tertiary/10 hover:text-tertiary border border-outline-variant' }}">
                        {{ $label }}
                        @if($isToday && $isActive)
                            <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block shrink-0"></span>
                        @elseif($isToday)
                            <span class="w-2 h-2 rounded-full bg-tertiary inline-block shrink-0"></span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- FILTER UNIT LAYANAN --}}
        {{-- ============================================================ --}}
        <div class="flex justify-center mb-8">
            <div class="relative w-full max-w-sm">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px] pointer-events-none">domain</span>
                <select
                    wire:model.live="unitLayananId"
                    class="w-full pl-12 pr-4 py-3 border border-outline-variant rounded-xl bg-white text-on-surface
                           focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary transition
                           text-sm appearance-none cursor-pointer">
                    <option value="">— Semua Unit Layanan —</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- RESULT COUNT --}}
        {{-- ============================================================ --}}
        <div class="flex items-center gap-3 mb-6">
            <div class="h-px flex-1 bg-outline-variant/30"></div>
            <p class="text-xs text-on-surface-variant shrink-0">
                <span class="font-semibold text-tertiary">{{ $jadwalPerPoli->count() }}</span>
                poliklinik buka hari
                <span class="font-semibold text-on-surface">{{ ucfirst(strtolower($activeHari)) }}</span>
            </p>
            <div class="h-px flex-1 bg-outline-variant/30"></div>
        </div>

        {{-- ============================================================ --}}
        {{-- GRID KARTU POLIKLINIK --}}
        {{-- ============================================================ --}}
        @if($jadwalPerPoli->isEmpty())

            <div class="text-center py-24">
                <span class="material-symbols-outlined text-7xl text-outline/40 block mb-4">event_busy</span>
                <p class="text-on-surface font-semibold text-xl mb-2">Tidak ada jadwal layanan</p>
                <p class="text-on-surface-variant text-sm max-w-sm mx-auto leading-relaxed">
                    Tidak ada poliklinik yang buka hari
                    <span class="font-semibold text-on-surface">{{ ucfirst(strtolower($activeHari)) }}</span>
                    @if($unitLayananId) pada unit layanan yang dipilih @endif.
                </p>
            </div>

        @else

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach($jadwalPerPoli as $poliId => $sesiList)
                    @php
                        $poli  = $sesiList->first()->poliklinik;
                        $warna = $poli->unitLayanan?->warnaHex() ?? '#4d51b2';
                    @endphp
                    <div
                        data-aos="fade-up"
                        data-aos-delay="{{ $loop->index * 50 }}"
                        wire:key="poli-{{ $poliId }}"
                        class="group bg-white rounded-2xl shadow-sm border border-outline-variant/30
                               hover:shadow-xl hover:-translate-y-1 transition-all duration-300
                               flex flex-col overflow-hidden">

                        {{-- Accent bar — warna dinamis --}}
                        <div class="h-1 w-full shrink-0"
                             style="background: linear-gradient(to right, {{ $warna }}, {{ $warna }}99);"></div>

                        {{-- Header poliklinik --}}
                        <div class="px-4 py-3" style="background-color: {{ $warna }};">
                            <p class="text-sm font-bold text-white leading-snug">
                                {{ $poli->nama }}
                            </p>
                            @if($poli->unitLayanan)
                                <p class="text-[10px] text-white/70 mt-0.5">
                                    {{ $poli->unitLayanan->nama }}
                                </p>
                            @endif
                        </div>

                        {{-- Daftar dokter --}}
                        <div class="divide-y divide-outline-variant/20 flex-1">
                            @foreach($sesiList as $s)
                                <div class="px-4 py-3">
                                    <p class="font-semibold text-sm text-on-surface leading-snug">
                                        {{ $s->nama_dokter }}
                                    </p>
                                    <div class="flex items-center justify-between gap-2 mt-1.5 flex-wrap">
                                        <div class="flex items-center gap-1.5 text-xs text-on-surface-variant">
                                            <span class="material-symbols-outlined text-[14px]"
                                                  style="color: {{ $warna }}; opacity: 0.7;">schedule</span>
                                            <span class="font-medium tabular-nums">
                                                {{ $s->jam_mulai?->format('H:i') }}
                                                @if($s->jam_selesai)
                                                    &ndash; {{ $s->jam_selesai->format('H:i') }}
                                                @else
                                                    &ndash; selesai
                                                @endif
                                            </span>
                                        </div>
                                        <span @class([
                                            'shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide',
                                            'bg-green-100 text-green-700' => $s->status_layanan->value === 'BUKA',
                                            'bg-red-100 text-red-700'     => $s->status_layanan->value === 'LIBUR',
                                        ])>{{ $s->status_layanan->getLabel() }}</span>
                                    </div>
                                    @if($s->catatan)
                                        <p class="text-[11px] text-on-surface-variant/60 italic mt-1 leading-snug">
                                            {{ $s->catatan }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- Footer link — hover background tint warna dinamis via Alpine --}}
                        <a href="{{ route('rumahsakit.rawat_jalan_show', ['rumahsakit' => $rs->slug, 'poliklinik' => $poli->slug]) }}"
                           x-data="{ hovered: false }"
                           x-on:mouseenter="hovered = true"
                           x-on:mouseleave="hovered = false"
                           :style="hovered
                               ? 'background-color: {{ $warna }}15; color: {{ $warna }};'
                               : 'background-color: transparent; color: {{ $warna }};'"
                           class="flex items-center justify-between px-4 py-2.5 border-t border-outline-variant/20
                                  transition-colors duration-150">
                            <span class="text-xs font-semibold">Lihat Detail Poli</span>
                            <span class="material-symbols-outlined text-[14px] transition-transform"
                                  :class="hovered ? 'translate-x-0.5' : ''">arrow_forward</span>
                        </a>

                    </div>
                @endforeach
            </div>

        @endif

    </div>
</div>
