<div>
    {{-- ============================================================ --}}
    {{-- HERO --}}
    {{-- ============================================================ --}}
    <div id="hero" class="relative overflow-hidden bg-primary py-16 px-6">
        {{-- Dekorasi geometris --}}
        <div class="absolute -top-16 -right-16 w-72 h-72 bg-white/5 rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full pointer-events-none"></div>
        <div class="absolute top-6 right-1/4 w-24 h-24 border-2 border-white/10 rounded-xl rotate-12 pointer-events-none"></div>
        <div class="absolute bottom-8 left-1/3 w-16 h-16 border-2 border-white/10 rounded-full pointer-events-none"></div>
        <div class="absolute top-1/2 -translate-y-1/2 left-8 w-3 h-32 bg-yellow-400/40 rounded-full pointer-events-none"></div>
        <div class="absolute top-1/2 -translate-y-1/2 left-14 w-3 h-20 bg-yellow-400/20 rounded-full pointer-events-none"></div>

        <div class="relative z-10 text-center max-w-2xl mx-auto">
            <h1 class="text-4xl md:text-5xl font-bold text-white leading-tight mb-4">Rawat Jalan</h1>
            <p class="text-white/65 text-base leading-relaxed">
                Temukan berbagai layanan poliklinik spesialis yang tersedia untuk mendukung kesehatan Anda dan keluarga.
            </p>
        </div>
    </div>
    {{-- END HERO --}}

    {{-- ============================================================ --}}
    {{-- KONTEN UTAMA --}}
    {{-- ============================================================ --}}
    <div class="w-10/12 mx-auto py-16">

        @if($units->isEmpty())

            <div class="text-center py-24">
                <span class="material-symbols-outlined text-7xl text-outline/40 block mb-4">healing</span>
                <p class="text-on-surface-variant text-lg">Belum ada unit layanan tersedia.</p>
            </div>

        @else

            {{-- ==================================================== --}}
            {{-- TAB — hanya muncul jika unit > 1 --}}
            {{-- ==================================================== --}}
            @if($units->count() > 1)
                <div class="mb-10">
                    <p class="text-on-surface-variant text-xs uppercase tracking-widest font-semibold mb-4">
                        Pilih Unit Layanan
                    </p>
                    <div class="flex flex-wrap gap-3" role="tablist">
                        @foreach($units as $unit)
                            <button
                                wire:click="setUnit({{ $unit->id }})"
                                wire:key="tab-{{ $unit->id }}"
                                role="tab"
                                class="relative inline-flex items-center gap-2 px-6 py-3 rounded-full font-semibold text-sm
                                       transition-all duration-200 focus:outline-none
                                       {{ $activeUnitId === $unit->id
                                           ? 'bg-primary text-white shadow-lg shadow-primary/25'
                                           : 'bg-surface-container text-on-surface-variant hover:bg-primary/10 hover:text-primary border border-outline-variant' }}">
                                <span class="material-symbols-outlined text-[18px]">
                                    {{ $activeUnitId === $unit->id ? 'check_circle' : 'radio_button_unchecked' }}
                                </span>
                                {{ $unit->nama }}
                                @if($activeUnitId === $unit->id)
                                    <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-yellow-400 rounded-full ring-2 ring-white"></span>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    {{-- Deskripsi unit aktif --}}
                    @php $activeUnit = $units->firstWhere('id', $activeUnitId); @endphp
                    @if($activeUnit?->deskripsi)
                        <div class="mt-5 p-4 bg-primary/5 border-l-4 border-primary rounded-r-xl max-w-2xl"
                             data-aos="fade-right">
                            <p class="text-on-surface-variant text-sm leading-relaxed">{{ $activeUnit->deskripsi }}</p>
                        </div>
                    @endif
                </div>

            @else
                {{-- Judul unit tunggal --}}
                <div class="mb-10" data-aos="fade-up">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-1.5 h-8 bg-primary rounded-full inline-block"></span>
                        <h2 class="text-2xl font-bold text-on-surface">{{ $units->first()->nama }}</h2>
                    </div>
                    @if($units->first()->deskripsi)
                        <p class="text-on-surface-variant text-sm ml-5 max-w-2xl leading-relaxed mt-1">
                            {{ $units->first()->deskripsi }}
                        </p>
                    @endif
                </div>
            @endif

            {{-- ==================================================== --}}
            {{-- GRID POLIKLINIK --}}
            {{-- ==================================================== --}}
            <div wire:key="poli-{{ $activeUnitId }}">

                @if($poliklinik->isEmpty())
                    <div class="text-center py-20 border-2 border-dashed border-outline-variant rounded-2xl">
                        <span class="material-symbols-outlined text-5xl text-outline/40 block mb-3">medication</span>
                        <p class="text-on-surface-variant">Belum ada poliklinik untuk unit ini.</p>
                    </div>

                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($poliklinik as $poli)
                            <div
                                data-aos="fade-up"
                                data-aos-delay="{{ $loop->index * 60 }}"
                                x-data="{ expanded: false }"
                                class="group bg-white rounded-2xl shadow-sm border border-outline-variant/40
                                       hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden
                                       flex flex-col">

                                {{-- Aksen atas --}}
                                <div class="h-1.5 w-full bg-linear-to-r from-primary to-secondary"></div>

                                {{-- Gambar (jika ada) --}}
                                @if($poli->gambar)
                                    <div class="relative w-full h-48 overflow-hidden">
                                        <img
                                            src="{{ Storage::url($poli->gambar) }}"
                                            alt="{{ $poli->nama }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        <div class="absolute inset-0 bg-linear-to-t from-black/30 to-transparent"></div>
                                    </div>
                                @else
                                    {{-- Placeholder ikon jika tidak ada gambar --}}
                                    <div class="relative w-full h-36 bg-linear-to-br from-primary/10 to-secondary/10 flex items-center justify-center overflow-hidden">
                                        <span class="material-symbols-outlined text-6xl text-primary/30">medical_services</span>
                                        <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-primary/5 rounded-full"></div>
                                        <div class="absolute -top-4 -left-4 w-16 h-16 bg-secondary/5 rounded-full"></div>
                                    </div>
                                @endif

                                {{-- Konten --}}
                                <div class="p-6 flex flex-col flex-1">
                                    {{-- Badge unit (khusus multi-unit agar jelas) --}}
                                    @if($units->count() > 1)
                                        @php $unitNama = $units->firstWhere('id', $poli->unit_layanan_id)?->nama; @endphp
                                        @if($unitNama)
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest text-primary bg-primary/10 px-2.5 py-1 rounded-full w-fit mb-3">
                                                <span class="material-symbols-outlined text-[12px]">domain</span>
                                                {{ $unitNama }}
                                            </span>
                                        @endif
                                    @endif

                                    {{-- Nama poliklinik --}}
                                    <h3 class="text-lg font-bold text-on-surface mb-3 leading-snug group-hover:text-primary transition-colors duration-200">
                                        {{ $poli->nama }}
                                    </h3>

                                    {{-- Deskripsi dengan expand/collapse --}}
                                    @if($poli->deskripsi)
                                        <div class="flex-1">
                                            {{-- Teks pendek (default) --}}
                                            <div x-show="!expanded"
                                                 class="text-on-surface-variant text-sm leading-relaxed line-clamp-4">
                                                {!! str($poli->deskripsi)->sanitizeHtml() !!}
                                            </div>

                                            {{-- Teks lengkap --}}
                                            <div x-show="expanded"
                                                 x-transition:enter="transition ease-out duration-200"
                                                 x-transition:enter-start="opacity-0"
                                                 x-transition:enter-end="opacity-100"
                                                 class="text-on-surface-variant text-sm leading-relaxed">
                                                {!! str($poli->deskripsi)->sanitizeHtml() !!}
                                            </div>

                                            {{-- Toggle button --}}
                                            <button
                                                @click="expanded = !expanded"
                                                class="mt-3 inline-flex items-center gap-1 text-primary text-xs font-semibold
                                                       hover:underline focus:outline-none transition-colors duration-150">
                                                <span x-text="expanded ? 'Sembunyikan' : 'Selengkapnya'"></span>
                                                <span class="material-symbols-outlined text-[14px] transition-transform duration-200"
                                                      :class="expanded ? 'rotate-180' : ''">expand_more</span>
                                            </button>
                                        </div>
                                    @else
                                        <p class="flex-1 text-on-surface-variant/50 text-sm italic">Informasi belum tersedia.</p>
                                    @endif
                                </div>

                                {{-- Footer card --}}
                                <div class="px-6 pb-5 pt-0">
                                    <div class="h-px bg-outline-variant/30 mb-4"></div>
                                    <div class="flex items-center gap-2 text-xs text-on-surface-variant/60">
                                        <span class="material-symbols-outlined text-[14px] text-primary">schedule</span>
                                        Lihat jadwal di halaman Jadwal Praktek
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
            {{-- END GRID POLIKLINIK --}}

        @endif

    </div>
    {{-- END KONTEN UTAMA --}}
</div>
