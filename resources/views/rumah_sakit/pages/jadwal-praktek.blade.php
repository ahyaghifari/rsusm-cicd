<div>
    <x-page-hero
        title="Jadwal Praktek"
        subtitle="Cek jadwal praktek dokter di setiap poliklinik."
    />

    <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-10 py-10">

        {{-- ============================================================ --}}
        {{-- TOGGLE MODE: Per Hari / Per Poli --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-2 gap-4 max-w-md mx-auto mb-10">

            <button wire:click="setViewMode('hari')"
                class="flex flex-col items-center gap-2 p-4 rounded-2xl border-2 transition-all duration-200 focus:outline-none
                       {{ $viewMode === 'hari'
                           ? 'border-primary bg-primary text-white shadow-lg shadow-primary/20'
                           : 'border-outline-variant bg-white text-on-surface-variant hover:border-primary/50 hover:bg-primary/5' }}">
                <span class="material-symbols-outlined text-[32px]">calendar_month</span>
                <div class="text-center">
                    <p class="font-bold text-sm leading-tight">Per Hari</p>
                    <p class="text-xs mt-0.5 leading-snug opacity-80">Lihat jadwal hari tertentu</p>
                </div>
            </button>

            <button wire:click="setViewMode('poli')"
                class="flex flex-col items-center gap-2 p-4 rounded-2xl border-2 transition-all duration-200 focus:outline-none
                       {{ $viewMode === 'poli'
                           ? 'border-primary bg-primary text-white shadow-lg shadow-primary/20'
                           : 'border-outline-variant bg-white text-on-surface-variant hover:border-primary/50 hover:bg-primary/5' }}">
                <span class="material-symbols-outlined text-[32px]">local_hospital</span>
                <div class="text-center">
                    <p class="font-bold text-sm leading-tight">Per Poli</p>
                    <p class="text-xs mt-0.5 leading-snug opacity-80">Lihat 1 poliklinik semua hari</p>
                </div>
            </button>

        </div>

        {{-- ============================================================ --}}
        {{-- MODE: PER HARI --}}
        {{-- ============================================================ --}}
        @if($viewMode === 'hari')

            {{-- Tab Hari --}}
            <div class="mb-8 text-center">
                <p class="text-xs text-on-surface-variant uppercase tracking-widest font-semibold mb-3">Pilih Hari</p>
                <div class="inline-flex flex-wrap justify-center gap-2" role="tablist">
                    @foreach($hariList as $hari)
                        @php $isActive = $activeHari === $hari; $isToday = $hariIni === $hari; @endphp
                        <button wire:click="setHari('{{ $hari }}')" role="tab"
                            class="relative inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full font-semibold text-sm
                                   transition-all duration-200 focus:outline-none
                                   {{ $isActive
                                       ? 'bg-primary text-white shadow-lg shadow-primary/25'
                                       : 'bg-surface-container text-on-surface-variant hover:bg-primary/10 hover:text-primary border border-outline-variant' }}">
                            {{ ucfirst(strtolower($hari)) }}
                            @if($isToday && $isActive)
                                <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block shrink-0"></span>
                            @elseif($isToday)
                                <span class="w-2 h-2 rounded-full bg-primary inline-block shrink-0"></span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Filter Poliklinik (searchable) --}}
            <div class="flex justify-center mb-8">
                @include('rumah_sakit.pages._searchable-select', [
                    'property'     => 'poliklinikId',
                    'options'      => $poliklinikList->map(fn($p) => ['value' => (string)$p->id, 'label' => $p->nama])->values()->toArray(),
                    'placeholder'  => '— Semua Poliklinik —',
                    'currentValue' => $poliklinikId,
                ])
            </div>

            {{-- Result count --}}
            <div class="flex items-center gap-3 mb-6">
                <div class="h-px flex-1 bg-outline-variant/30"></div>
                <p class="text-xs text-on-surface-variant shrink-0">
                    <span class="font-semibold text-primary">{{ $jadwalPerPoli->count() }}</span>
                    poliklinik buka hari
                    <span class="font-semibold text-on-surface">{{ ucfirst(strtolower($activeHari)) }}</span>
                </p>
                <div class="h-px flex-1 bg-outline-variant/30"></div>
            </div>

            @if($jadwalPerPoli->isEmpty())
                <div class="text-center py-24">
                    <span class="material-symbols-outlined text-7xl text-outline/40 block mb-4">event_busy</span>
                    <p class="text-on-surface font-semibold text-xl mb-2">Tidak ada jadwal praktek</p>
                    <p class="text-on-surface-variant text-sm max-w-sm mx-auto leading-relaxed">
                        Tidak ada jadwal praktek hari
                        <span class="font-semibold text-on-surface">{{ ucfirst(strtolower($activeHari)) }}</span>
                        @if($poliklinikId) untuk poliklinik yang dipilih @endif.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-3 gap-7">
                    @foreach($jadwalPerPoli as $poliId => $sesiList)
                        @php
                            $poli  = $sesiList->first()->poliklinik;
                            $warna = '#4d51b2';
                        @endphp
                        <div
                            wire:key="poli-{{ $poliId }}"
                            class="bg-white rounded-2xl shadow-sm border border-outline-variant/30
                                   hover:shadow-xl hover:-translate-y-1 transition-all duration-300
                                   flex flex-col overflow-hidden animate-fade-in"
                            style="animation-delay: {{ $loop->index * 40 }}ms">

                            <div class="h-1 w-full shrink-0"
                                 style="background: linear-gradient(to right, {{ $warna }}, {{ $warna }}99);"></div>

                            <div class="px-4 py-3 flex items-center gap-3" style="background-color: {{ $warna }};">
                                <div class="w-9 h-9 rounded-full bg-white flex items-center justify-center shrink-0 overflow-hidden shadow-sm">
                                    @if($poli->gambar)
                                        <img src="{{ Storage::url($poli->gambar) }}"
                                             alt="{{ $poli->nama }}"
                                             class="w-7 h-7 object-contain" loading="lazy">
                                    @else
                                        <span class="material-symbols-outlined text-[18px]"
                                              style="color: {{ $warna }};">local_hospital</span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-white leading-snug truncate">{{ $poli->nama }}</p>
                                </div>
                            </div>

                            <div class="divide-y divide-outline-variant/15 flex-1">
                                @foreach($sesiList->groupBy(fn($s) => $s->dokter_id ? 'd'.$s->dokter_id : 'n'.($s->nama_dokter ?? '')) as $sesiGroup)
                                    @include('rumah_sakit.pages._jadwal-praktek-row', ['sesiGroup' => $sesiGroup, 'warna' => $warna])
                                @endforeach
                            </div>

                            <a href="{{ route('rumahsakit.rawat_jalan_show', ['rumahsakit' => $rs->slug, 'poliklinik' => $poli->slug]) }}"
                               x-data="{ hovered: false }"
                               x-on:mouseenter="hovered = true" x-on:mouseleave="hovered = false"
                               :style="hovered ? 'background-color:{{ $warna }}15;color:{{ $warna }};' : 'background-color:transparent;color:{{ $warna }};'"
                               class="flex items-center justify-between px-4 py-2.5 border-t border-outline-variant/20 transition-colors duration-150">
                                <span class="text-xs font-semibold">Lihat Detail Poli</span>
                                <span class="material-symbols-outlined text-[14px] transition-transform"
                                      :class="hovered ? 'translate-x-0.5' : ''">arrow_forward</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif

        {{-- ============================================================ --}}
        {{-- MODE: PER POLI --}}
        {{-- ============================================================ --}}
        @else

            {{-- Filter Poliklinik (searchable) --}}
            <div class="flex justify-center mb-8">
                @include('rumah_sakit.pages._searchable-select', [
                    'property'     => 'poliklinikId',
                    'options'      => $poliklinikList->map(fn($p) => ['value' => (string)$p->id, 'label' => $p->nama])->values()->toArray(),
                    'placeholder'  => '— Pilih Poliklinik —',
                    'currentValue' => $poliklinikId,
                ])
            </div>

            @if(! $poliklinikId)
                <div class="text-center py-24">
                    <span class="material-symbols-outlined text-7xl text-outline/40 block mb-4">local_hospital</span>
                    <p class="text-on-surface font-semibold text-xl mb-2">Pilih Poliklinik</p>
                    <p class="text-on-surface-variant text-sm max-w-sm mx-auto leading-relaxed">
                        Pilih poliklinik dari dropdown di atas untuk melihat jadwal lengkap semua hari.
                    </p>
                </div>
            @else
                @php $warna = '#4d51b2'; @endphp

                <div class="flex items-center gap-3 mb-6">
                    @if($selectedPoli?->gambar)
                        <img src="{{ Storage::url($selectedPoli->gambar) }}"
                             alt="{{ $selectedPoli->nama }}"
                             class="w-10 h-10 rounded-full object-cover shrink-0"
                             style="outline: 2px solid {{ $warna }}55; outline-offset: 2px;"
                             loading="lazy">
                    @else
                        <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                             style="background-color: {{ $warna }}18; outline: 2px solid {{ $warna }}40; outline-offset: 2px;">
                            <span class="material-symbols-outlined text-[18px]" style="color:{{ $warna }};">local_hospital</span>
                        </div>
                    @endif
                    <div>
                        <h2 class="text-xl font-bold text-on-surface">{{ $selectedPoli?->nama }}</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                    @foreach($hariList as $hari)
                        @php
                            $sesiHari  = $jadwalPerHari[$hari] ?? collect();
                            $isToday   = $hariIni === $hari;
                        @endphp
                        <div class="bg-white rounded-2xl shadow-sm border overflow-hidden flex flex-col"
                             style="{{ $isToday ? "border-color:{$warna}66;box-shadow:0 0 0 1px {$warna}22;" : 'border-color:#e5e7eb;' }}">

                            <div class="px-4 py-2.5 flex items-center justify-between shrink-0"
                                 style="{{ $isToday ? "background-color:{$warna};color:white;" : "background-color:{$warna}0f;color:{$warna};" }}">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[15px]">calendar_today</span>
                                    <span class="text-sm font-bold">{{ ucfirst(strtolower($hari)) }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    @if($isToday)
                                        <span class="text-xs font-bold uppercase px-2 py-0.5 rounded-full"
                                              style="background-color:rgba(255,255,255,0.2);">Hari Ini</span>
                                    @endif
                                    <span class="text-[11px] font-semibold opacity-70">{{ $sesiHari->count() }}x</span>
                                </div>
                            </div>

                            @if($sesiHari->isEmpty())
                                <div class="px-4 py-4 flex items-center gap-2 text-on-surface-variant/50 flex-1">
                                    <span class="material-symbols-outlined text-[16px]">event_busy</span>
                                    <span class="text-xs italic">Tidak ada jadwal</span>
                                </div>
                            @else
                                <div class="divide-y divide-outline-variant/15 flex-1">
                                    @foreach($sesiHari->groupBy(fn($s) => $s->dokter_id ? 'd'.$s->dokter_id : 'n'.($s->nama_dokter ?? '')) as $sesiGroup)
                                        @include('rumah_sakit.pages._jadwal-praktek-row', ['sesiGroup' => $sesiGroup, 'warna' => $warna])
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        @php $punyaLinkDaftar = (bool) $rs->link_pendaftaran_online; @endphp
        <div class="mt-8 flex flex-col md:flex-row gap-4 md:items-stretch">
            @if($punyaLinkDaftar)
                <a href="{{ $rs->link_pendaftaran_online }}" target="_blank"
                   class="group shrink-0 inline-flex items-center justify-center gap-2.5 px-7 py-4 rounded-2xl
                          bg-tertiary hover:bg-tertiary/90 text-on-tertiary font-bold text-base
                          shadow-lg shadow-tertiary/30 hover:shadow-xl hover:shadow-tertiary/40
                          hover:-translate-y-1 active:scale-95
                          transition-all duration-200 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform duration-200">event</span>
                    <span>Daftar Sekarang</span>
                    <span class="material-symbols-outlined text-[16px] opacity-0 group-hover:opacity-100 -ml-1 transition-all duration-200">arrow_forward</span>
                </a>
            @endif
            @include('rumah_sakit.partials._jadwal-disclaimer', ['noCenter' => $punyaLinkDaftar])
        </div>

    </div>
</div>

