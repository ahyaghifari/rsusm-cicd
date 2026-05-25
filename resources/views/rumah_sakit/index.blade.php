<div>
    <!-- Carousel -->
    <div class="w-10/12 mx-auto">
        <div id="hs-carousel" class="relative"
            data-hs-carousel='{"loadingClasses": "opacity-0", "dotsItemClasses": "hs-carousel-active:bg-primary hs-carousel-active:border-primary size-3 border border-line-4 border-primary rounded-full cursor-pointer", "isAutoPlay": true, "isInfiniteLoop": true}'>
            <div class="hs-carousel relative w-full min-h-96 overflow-hidden">
                <!-- Carousel Body -->
                <div
                    class="hs-carousel-body flex flex-nowrap absolute top-0 bottom-0 inset-s-0 transition-transform duration-700 opacity-0">
                    <div class="hs-carousel-slide ">
                        <div class="flex justify-center h-full bg-surface p-6">
                            <span class="self-center text-4xl text-foreground transition duration-700">First slide</span>
                        </div>
                    </div>
                    <div class="hs-carousel-slide ">
                        <div class="flex justify-center h-full bg-surface-1 p-6">
                            <span class="self-center text-4xl text-foreground transition duration-700">Second
                                slide</span>
                        </div>
                    </div>
                </div>
                <!-- End Carousel Body -->
            </div>

            <!-- Arrows -->
            <button type="button"
                class="hs-carousel-prev hs-carousel-disabled:opacity-50 hs-carousel-disabled:cursor-default absolute top-1/2 inset-s-2 inline-flex justify-center items-center size-10 bg-layer text-layer-foreground rounded-full shadow-2xs hover:bg-layer-hover -translate-y-1/2 focus:outline-hidden">
                <span class="text-2xl" aria-hidden="true">
                    <svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </span>
                <span class="sr-only">Previous</span>
            </button>
            <button type="button"
                class="hs-carousel-next hs-carousel-disabled:opacity-50 hs-carousel-disabled:cursor-default absolute top-1/2 inset-e-2 inline-flex justify-center items-center size-10 bg-layer text-layer-foreground rounded-full shadow-2xs hover:bg-layer-hover -translate-y-1/2 focus:outline-hidden">
                <span class="sr-only">Next</span>
                <span class="text-2xl" aria-hidden="true">
                    <svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </span>
            </button>
            <!-- End Arrows -->

            <div class="hs-carousel-pagination flex justify-center absolute bottom-3 inset-s-0 inset-e-0 gap-x-2">
            </div>
            <!-- End Pagination -->
        </div>
    </div>
    <!-- End Carousel -->\

    <!-- link layanan -->
    @if($link_layanan->count() > 0)
    @php
        $fallbackIcons = ['hotel', 'calendar_clock', 'monitor_heart', 'vaccines', 'medical_services', 'emergency'];
    @endphp
    <section class="relative overflow-hidden py-16">
        {{-- Background gradient --}}
        <div class="absolute inset-0 bg-linear-to-br from-primary via-primary to-secondary/80"></div>
        {{-- Dekorasi lingkaran --}}
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/5 rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full pointer-events-none"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-150 h-150 bg-white/2 rounded-full pointer-events-none"></div>

        <div class="relative z-10 w-10/12 mx-auto">
            {{-- Header --}}
            <div class="text-center mb-8">
                <span class="inline-block text-white/60 uppercase tracking-[0.2em] text-xs font-semibold mb-4">Layanan Digital</span>
                <h2 class="font-bold text-white text-4xl leading-tight">Informasi & Layanan</h2>
                <p class="text-white/65 mt-4 max-w-lg mx-auto text-base leading-relaxed">
                    Akses berbagai layanan rumah sakit secara digital, kapan saja dan di mana saja.
                </p>
            </div>

            {{-- Cards --}}
            <div class="grid md:grid-cols-{{ min($link_layanan->count(), 3) }} gap-6">
                @foreach($link_layanan as $layanan)
                @php $icon = $fallbackIcons[$loop->index % count($fallbackIcons)]; @endphp
                <a href="{{ $layanan->link }}" target="_blank" rel="noopener noreferrer"
                    class="group relative bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl p-8 flex flex-col
                           hover:bg-white hover:border-white hover:shadow-[0_20px_60px_rgba(0,0,0,0.2)]
                           hover:-translate-y-2 transition-all duration-300 cursor-pointer overflow-hidden">

                    {{-- Aksen sudut --}}
                    <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 group-hover:bg-primary/5 rounded-bl-full transition-colors duration-300"></div>

                    {{-- Icon --}}
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-7 shrink-0
                                bg-white/20 group-hover:bg-primary/10 transition-colors duration-300">
                        @if($layanan->gambar)
                            <img src="{{ Storage::url($layanan->gambar) }}"
                                 class="w-10 h-10 object-cover rounded-xl" alt="{{ $layanan->label }}">
                        @else
                            <span class="material-symbols-outlined text-white group-hover:text-primary text-4xl transition-colors duration-300">
                                {{ $icon }}
                            </span>
                        @endif
                    </div>

                    {{-- Konten --}}
                    <div class="flex-1">
                        <h3 class="text-white group-hover:text-on-surface text-xl font-bold mb-3 leading-snug transition-colors duration-300">
                            {{ $layanan->label }}
                        </h3>
                        @if($layanan->deskripsi_singkat)
                        <p class="text-white/65 group-hover:text-on-surface-variant text-sm leading-relaxed transition-colors duration-300">
                            {{ $layanan->deskripsi_singkat }}
                        </p>
                        @endif
                    </div>

                    {{-- CTA --}}
                    <div class="mt-8 flex items-center gap-2 text-white/70 group-hover:text-primary font-semibold text-sm transition-colors duration-300">
                        <span>Akses Sekarang</span>
                        <span class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform duration-300">
                            arrow_forward
                        </span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    <!-- end link layanan -->

    <!-- kenapa memilih RSU -->
    <section class="grid grid-cols-2 mt-24">
        <div>
            <img src="{{ asset('img/syifa-medika.webp') }}" class="w-full h-full object-cover" alt="">
        </div>
        <div class="p-6 relative">
            <img src="{{ asset('img/bg-header.png') }}"
                class="absolute top-0 left-0 right-0 w-full h-full -z-10 opacity-50 bg-blend-overlay object-cover blur-xs"
                alt="">
            <h2 class="text-on-surface text-xl">Kenapa harus memilih</h2>
            <img src="{{ Storage::url(current_rumahsakit()->logo) }}" class="h-44 mt-2 object-contain" alt="">
            <p class="text-on-surface mt-6 leading-7">RSU Syifa Medika Banjarbaru hadir untuk masyarakat yang ingin mendapatkan
                pelayanan
                kesehatan yang berkualitas, RSU Syifa Medika Banjarbaru merupakan pelayanan kesehatan yang jujur dalam
                pelayanan dan selalu memberikan kemudahan karena di dukung oleh staff medis yang profesional,
                bersertifikasi, Ahli dibidangnya serta di dukung oleh peralatan yang mutakhir dan terkini sesuai dengan
                moto
                kami yaitu Pelayanan yang profesional dan terpercaya.</p>

        </div>
    </section>

    <!-- layanan unggulan -->
    @if(count($layanan_unggulan) > 0)
    <section id="unggulan" class="bg-secondary p-6 mt-24 relative">
        <h1 class="text-white font-bold text-center text-3xl">LAYANAN UNGGULAN KAMI</h1>
        <div class="grid grid-cols-4 w-10/12 mx-auto mt-10">

            @foreach($layanan_unggulan as $lu)
            <div class="p-6">
                <div class="bg-white">
                    <img src="{{ Storage::url($lu->gambar) }}" class="aspect-square w-full object-cover"
                    alt="">
                </div>
                <p class="text-white mt-3 text-center font-semibold">{{ $lu->nama }}</p>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- dokter kami -->
    <section class="mt-24">
        <div class="w-10/12 mx-auto">
            <div class s="flex justify-between">
                <div>
                    <h2 class="text-tertiary text-4xl text-center font-bold">Dokter Kami</h2>
                    <h4 class="text-on-surface-variant text-center mt-4 w-4/6 mx-auto">Kami memiliki banyak dokter
                        spesialis
                        hingga sub spesialis ahli dibidangnya dan melayani Anda secara profesional dan terpercaya.</h4>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 mt-8 gap-10">
                    @foreach($dokter_kami as $dk)
                    <div class="group relative overflow-hidden bg-white rounded-xl shadow-sm">
                        <div class="aspect-square overflow-hidden">
                            <img alt=""
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                src="{{ Storage::url($dk->foto) }}" />
                        </div>
                        <div class="p-6 text-center">
                            <h4 class="font-semibold text-on-surface">{{ $dk->nama }}</h4>
                            <p class=" w-fit p-1 px-2 text-sm  mt-2 text-primary rounded-lg  mx-auto font-medium mb-4">{{ $dk->spesialis->nama }}</p>
                            <a href="">
                                <button
                                    class="w-full sm:w-auto self-start border-2 border-tertiary  text-tertiary hover:bg-tertiary hover:text-white px-5 py-2 rounded-lg text-label-md font-label-md transition-colors flex items-center gap-2 text-xs mx-auto cursor-pointer">
                                    Lihat Jadwal Praktek
                                    <span class="material-symbols-outlined text-[15px] scale-75">arrow_forward</span>
                                </button>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>
        </div>
        <div class="mt-8">
            <a href="{{ rumahsakit_route('rumahsakit.dokter_kami') }}">
                <button
                    class="w-full sm:w-auto self-start border-2 border-tertiary bg-tertiary text-white px-6 py-2 rounded-lg text-label-md font-label-md transition-colors flex items-center gap-2 text-sm mx-auto cursor-pointer">
                    Lihat Selengkapnya
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </button>
            </a>
        </div>
    </section>

    <!-- asuransi dan perusahaan rekanan -->
    @if($partner_asuransi->count() > 0 || $partner_perusahaan->count() > 0)
    <section class="mt-24 py-16 bg-surface-container-low overflow-hidden">
        <div class="w-10/12 mx-auto mb-10">
            <h2 class="text-on-surface text-3xl font-bold text-center">Partner & Rekanan Kami</h2>
            <p class="text-on-surface-variant text-center mt-3">Kami bekerja sama dengan berbagai mitra terpercaya untuk memberikan pelayanan terbaik bagi pasien.</p>
        </div>

        {{-- Asuransi Slider (gerak ke kanan) --}}
        @if($partner_asuransi->count() > 0)
        <div class="mb-10">
            <div class="flex items-center gap-3 w-10/12 mx-auto mb-5">
                <div class="w-1 h-6 bg-primary rounded-full"></div>
                <h3 class="text-on-surface font-semibold text-lg">Asuransi</h3>
            </div>
            <div class="swiper swiper-asuransi">
                <div class="swiper-wrapper items-center">
                    @foreach($partner_asuransi as $partner)
                    <div class="swiper-slide">
                        <div class="mx-3 bg-white border border-outline-variant/30 rounded-2xl px-6 py-5 flex flex-col items-center justify-center gap-3 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 min-h-30">
                            @if($partner->logo)
                                <img src="{{ Storage::url($partner->logo) }}" alt="{{ $partner->nama }}" class="h-14 object-contain">
                            @else
                                <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                                    <span class="material-symbols-outlined text-primary text-2xl">shield</span>
                                </div>
                                <p class="text-on-surface text-center text-sm font-semibold leading-tight">{{ $partner->nama }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Perusahaan Slider (gerak ke kiri) --}}
        @if($partner_perusahaan->count() > 0)
        <div>
            <div class="flex items-center gap-3 w-10/12 mx-auto mb-5">
                <div class="w-1 h-6 bg-secondary rounded-full"></div>
                <h3 class="text-on-surface font-semibold text-lg">Perusahaan Rekanan</h3>
            </div>
            <div class="swiper swiper-perusahaan">
                <div class="swiper-wrapper items-center">
                    @foreach($partner_perusahaan as $partner)
                    <div class="swiper-slide">
                        <div class="mx-3 bg-white border border-outline-variant/30 rounded-2xl px-6 py-5 flex flex-col items-center justify-center gap-3 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 min-h-30">
                            @if($partner->logo)
                                <img src="{{ Storage::url($partner->logo) }}" alt="{{ $partner->nama }}" class="h-14 object-contain">
                            @else
                                <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center">
                                    <span class="material-symbols-outlined text-secondary text-2xl">business</span>
                                </div>
                                <p class="text-on-surface text-center text-sm font-semibold leading-tight">{{ $partner->nama }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </section>
    @endif

    <script>
        function initPartnerSwipers() {
            if (typeof window.Swiper === 'undefined') return;

            if (window._swiperAsuransi) { window._swiperAsuransi.destroy(true, true); }
            if (window._swiperPerusahaan) { window._swiperPerusahaan.destroy(true, true); }

            const asuransiEl = document.querySelector('.swiper-asuransi');
            if (asuransiEl) {
                window._swiperAsuransi = new window.Swiper('.swiper-asuransi', {
                    slidesPerView: 2,
                    spaceBetween: 0,
                    loop: true,
                    allowTouchMove: false,
                    speed: 3000,
                    autoplay: {
                        delay: 0,
                        disableOnInteraction: false,
                        reverseDirection: false,
                    },
                    breakpoints: {
                        640:  { slidesPerView: 3 },
                        768:  { slidesPerView: 4 },
                        1024: { slidesPerView: 5 },
                    },
                });
            }

            const perusahaanEl = document.querySelector('.swiper-perusahaan');
            if (perusahaanEl) {
                window._swiperPerusahaan = new window.Swiper('.swiper-perusahaan', {
                    slidesPerView: 2,
                    spaceBetween: 0,
                    loop: true,
                    allowTouchMove: false,
                    speed: 3000,
                    autoplay: {
                        delay: 0,
                        disableOnInteraction: false,
                        reverseDirection: true,
                    },
                    breakpoints: {
                        640:  { slidesPerView: 3 },
                        768:  { slidesPerView: 4 },
                        1024: { slidesPerView: 5 },
                    },
                });
            }
        }

        document.addEventListener('DOMContentLoaded', initPartnerSwipers);
        document.addEventListener('livewire:navigated', initPartnerSwipers);
    </script>
</div>
