<div>
    <x-page-hero
        title="Rawat Jalan"
        subtitle="Temukan berbagai layanan poliklinik spesialis yang tersedia untuk mendukung kesehatan Anda dan keluarga."
    />

    {{-- ============================================================ --}}
    {{-- KONTEN UTAMA --}}
    {{-- ============================================================ --}}
    <div class="w-11/12 lg:w-10/12 mx-auto py-16">

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

                    {{-- Deskripsi singkat unit aktif (di atas tab) --}}
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
            <div wire:key="poli-{{ $activeUnitId }}"
                 x-data
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-3"
                 x-transition:enter-end="opacity-100 translate-y-0">

                @if($poliklinik->isEmpty())
                    <div class="text-center py-20 border-2 border-dashed border-outline-variant rounded-2xl">
                        <span class="material-symbols-outlined text-5xl text-outline/40 block mb-3">medication</span>
                        <p class="text-on-surface-variant">Belum ada poliklinik untuk unit ini.</p>
                    </div>

                @else
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-2 lg:gap-5">
                        @foreach($poliklinik as $poli)
                            <div
                                onclick="window.location='{{ route('rumahsakit.rawat_jalan_show', ['rumahsakit' => $rsSlug, 'poliklinik' => $poli->slug]) }}'"
                                wire:key="poli-card-{{ $poli->id }}"
                                class="group bg-white rounded-2xl shadow-sm border border-outline-variant/30
                                       hover:shadow-xl hover:-translate-y-1 transition-all duration-300
                                       overflow-hidden flex flex-col cursor-pointer">

                                {{-- Aksen atas --}}
                                <div class="h-1 w-full bg-linear-to-r from-primary to-secondary shrink-0"></div>

                                {{-- Gambar --}}
                                @if($poli->gambar)
                                    <a href="{{ Storage::url($poli->gambar) }}"
                                       class="glightbox relative w-full h-40 overflow-hidden shrink-0 block"
                                       data-gallery="poliklinik"
                                       data-title="{{ $poli->nama }}"
                                       onclick="event.stopPropagation()">
                                        <img
                                            src="{{ Storage::url($poli->gambar) }}"
                                            alt="{{ $poli->nama }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        <div class="absolute inset-0 bg-linear-to-t from-black/25 to-transparent"></div>
                                        <span class="absolute top-2 right-2 material-symbols-outlined text-white text-xl
                                                     opacity-0 group-hover:opacity-100 transition drop-shadow
                                                     bg-black/30 rounded-full p-0.5">zoom_in</span>
                                    </a>
                                @else
                                    <div class="relative w-full h-32 bg-linear-to-br from-primary/10 to-secondary/10
                                                flex items-center justify-center overflow-hidden shrink-0">
                                        <span class="material-symbols-outlined text-5xl text-primary/30">medical_services</span>
                                        <div class="absolute -bottom-4 -right-4 w-16 h-16 bg-primary/5 rounded-full pointer-events-none"></div>
                                    </div>
                                @endif

                                {{-- Konten --}}
                                <div class="p-4 flex flex-col flex-1">
                                    <h3 class="font-bold text-on-surface text-sm leading-snug
                                               group-hover:text-primary transition-colors duration-200 mb-2">
                                        {{ $poli->nama }}
                                    </h3>

                                    @if($poli->deskripsi)
                                        <p class="text-xs text-on-surface-variant leading-relaxed line-clamp-2 flex-1">
                                            {{ strip_tags($poli->deskripsi) }}
                                        </p>
                                    @else
                                        <p class="text-xs text-on-surface-variant/50 italic flex-1">
                                            Informasi belum tersedia.
                                        </p>
                                    @endif
                                </div>

                                {{-- Footer tombol --}}
                                <div class="px-4 pb-4 pt-0">
                                    <div class="h-px bg-outline-variant/20 mb-3"></div>
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-primary
                                                 group-hover:gap-2 transition-all duration-150">
                                        Lihat Detail
                                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                    </span>
                                </div>

                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
            {{-- END GRID POLIKLINIK --}}

            {{-- ==================================================== --}}
            {{-- DESKRIPSI UNIT LAYANAN — hanya jika unit > 1 --}}
            {{-- ==================================================== --}}
            @if($units->count() > 1 && $activeUnit && ($activeUnit->deskripsi || $activeUnit->gambar))
                @php $warna = $activeUnit->warnaHex() ?? '#4d51b2'; @endphp
                <div class="mt-12 rounded-2xl overflow-hidden border border-outline-variant/20 shadow-sm"
                     data-aos="fade-up"
                     wire:key="unit-desc-{{ $activeUnit->id }}">

                    {{-- Accent bar warna unit --}}
                    <div class="h-1 shrink-0"
                         style="background: linear-gradient(to right, {{ $warna }}, {{ $warna }}60);"></div>

                    <div class="bg-white">
                        @if($activeUnit->gambar)
                            {{-- Layout: gambar kiri + teks kanan --}}
                            <div class="grid grid-cols-1 md:grid-cols-3">

                                {{-- Gambar --}}
                                <div class="relative overflow-hidden min-h-56">
                                    <img src="{{ Storage::url($activeUnit->gambar) }}"
                                         alt="{{ $activeUnit->nama }}"
                                         class="w-full h-full object-cover">
                                    <div class="absolute inset-0"
                                         style="background: linear-gradient(to right, transparent, white 95%);"></div>
                                </div>

                                {{-- Teks --}}
                                <div class="md:col-span-2 p-8 flex flex-col justify-center">
                                    <div class="flex items-center gap-3 mb-4">
                                        <span class="w-1 h-10 rounded-full shrink-0"
                                              style="background-color: {{ $warna }};"></span>
                                        <h3 class="text-2xl font-bold text-on-surface">
                                            {{ $activeUnit->nama }}
                                        </h3>
                                    </div>
                                    @if($activeUnit->deskripsi)
                                        <p class="text-sm text-on-surface-variant leading-relaxed">
                                            {{ $activeUnit->deskripsi }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                        @else
                            {{-- Layout: teks saja --}}
                            <div class="p-8">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="w-1 h-10 rounded-full shrink-0"
                                          style="background-color: {{ $warna }};"></span>
                                    <h3 class="text-2xl font-bold text-on-surface">
                                        {{ $activeUnit->nama }}
                                    </h3>
                                </div>
                                @if($activeUnit->deskripsi)
                                    <p class="text-sm text-on-surface-variant leading-relaxed max-w-3xl">
                                        {{ $activeUnit->deskripsi }}
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        @endif

    </div>
    {{-- END KONTEN UTAMA --}}
</div>

@script
<script>
    document.addEventListener('livewire:updated', () => {
        if (window._glb) window._glb.reload();
    });
</script>
@endscript
