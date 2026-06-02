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
                             >
                            <p class="text-on-surface-variant text-sm leading-relaxed">{{ $activeUnit->deskripsi }}</p>
                        </div>
                    @endif
                </div>

            @else
                {{-- Judul unit tunggal --}}
                <div class="mb-10">
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
                    @php $warna = $activeUnit?->warna ?: '#d606b0'; @endphp
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 lg:gap-4">
                        @foreach($poliklinik as $poli)
                            <div
                                onclick="window.location='{{ route('rumahsakit.rawat_jalan_show', ['rumahsakit' => $rsSlug, 'poliklinik' => $poli->slug]) }}'"
                                wire:key="poli-card-{{ $poli->id }}"
                               
                                class="group bg-white rounded-2xl shadow-sm border border-outline-variant/30
                                       hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300
                                       overflow-hidden cursor-pointer">

                                {{-- Aksen atas --}}
                                <div class="h-1 w-full shrink-0"
                                     style="background: linear-gradient(to right, {{ $warna }}, {{ $warna }}88);"></div>

                                {{-- Body: ikon kiri, nama + tombol kanan --}}
                                <div class="flex items-center gap-5 px-5 py-5">

                                    {{-- Ikon --}}
                                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center shrink-0 overflow-hidden"
                                         style="background-color: {{ $warna }}12; border: 1px solid {{ $warna }}25;">
                                        @if($poli->gambar)
                                            <img src="{{ Storage::url($poli->gambar) }}"
                                                 alt="{{ $poli->nama }}"
                                                 class="w-11 h-11 object-contain
                                                        group-hover:scale-105 transition-transform duration-300">
                                        @else
                                            <span class="material-symbols-outlined text-[30px]
                                                         group-hover:scale-105 transition-transform duration-300"
                                                  style="color: {{ $warna }};">medical_services</span>
                                        @endif
                                    </div>

                                    {{-- Nama + Tombol --}}
                                    <div class="flex flex-col gap-2 flex-1 min-w-0">
                                        <h3 class="font-bold text-on-surface text-sm leading-snug
                                                   group-hover:text-primary transition-colors duration-200"
                                            style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            {{ $poli->nama }}
                                        </h3>
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold w-fit
                                                     group-hover:gap-2 transition-all duration-150"
                                              style="color: {{ $warna }};">
                                            Lihat Detail
                                            <span class="material-symbols-outlined text-[13px]">arrow_forward</span>
                                        </span>
                                    </div>

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

