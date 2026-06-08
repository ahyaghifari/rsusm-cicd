<div>
    <!-- Carousel -->
    <div class="w-full mx-auto overflow-hidden">
        <div id="hs-carousel" class="relative"
            data-hs-carousel='{"loadingClasses": "opacity-0", "dotsItemClasses": "hs-carousel-active:bg-primary hs-carousel-active:border-primary size-3 border border-line-4 border-primary rounded-full cursor-pointer", "isAutoPlay": true, "isInfiniteLoop": true, "speed": 6000}'>
            <div class="hs-carousel relative w-full h-[50vh] md:h-[70vh] overflow-hidden">
                <!-- Carousel Body -->
                <div
                    class="hs-carousel-body flex flex-nowrap absolute top-0 bottom-0 inset-s-0 transition-transform duration-700 opacity-0">

                    {{-- ======================================================== --}}
                    {{-- SLIDE 1: Welcome + Quick Actions --}}
                    {{-- ======================================================== --}}
                    @php $hasFoto = !empty($currentRumahSakit->gambar); @endphp
                    <div class="hs-carousel-slide relative overflow-hidden
                                {{ $hasFoto ? '' : 'bg-white' }}">

                        {{-- Jika ada foto: tampilkan foto + overlay gelap netral --}}
                        @if($hasFoto)
                            <img src="{{ Storage::url($currentRumahSakit->gambar) }}"
                                 class="absolute inset-0 w-full h-full object-cover" alt="">
                            <div class="absolute inset-0 bg-black/40"></div>
                            {{-- Gradasi kuning halus di bawah agar tombol terbaca --}}
                            <div class="absolute bottom-0 left-0 right-0 h-1/3
                                        bg-linear-to-t from-black/40 to-transparent"></div>
                        @else
                            {{-- Tanpa foto: dot grid subtle di bg putih --}}
                            <div class="absolute inset-0 opacity-[0.035]"
                                 style="background-image:radial-gradient(circle,#000 1px,transparent 1px);background-size:20px 20px;"></div>
                            {{-- Aksen sudut kuning --}}
                            <div class="absolute top-0 right-0 w-48 h-48 bg-yellow-400/8 rounded-bl-full pointer-events-none"></div>
                            <div class="absolute bottom-0 left-0 w-32 h-32 bg-primary/5 rounded-tr-full pointer-events-none"></div>
                        @endif

                        {{-- Konten slide --}}
                        <div class="relative z-10 h-full flex flex-col items-center justify-center text-center
                                    px-6 py-6 md:py-10 gap-4 md:gap-5">

                            {{-- Nama RS --}}
                            <div>
                                <p class="{{ $hasFoto ? 'text-white/65' : 'text-on-surface-variant' }}
                                          text-[10px] md:text-xs uppercase tracking-[0.25em] font-semibold mb-1">
                                    Selamat Datang di
                                </p>
                                <h1 class="{{ $hasFoto ? 'text-white' : 'text-on-surface' }}
                                           text-xl md:text-4xl lg:text-5xl font-bold leading-tight uppercase">
                                    {{ $currentRumahSakit->nama }}
                                </h1>
                                <div class="mt-2.5 h-0.5 w-12 md:w-16 bg-yellow-400 rounded-full mx-auto"></div>
                            </div>

                            {{-- Quick Contact --}}
                            <div class="flex flex-wrap justify-center gap-2">
                                @if($currentRumahSakit->no_emergency)
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $currentRumahSakit->no_emergency) }}"
                                   class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700
                                          text-white px-3 md:px-4 py-1.5 rounded-full
                                          text-xs md:text-sm font-bold shadow-lg transition-colors">
                                    <span class="text-[14px]">Emergency</span>
                                    {{ $currentRumahSakit->no_emergency }}
                                </a>
                                @endif
                                @if($currentRumahSakit->no_hotline)
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $currentRumahSakit->no_hotline) }}"
                                   class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700
                                          text-white px-3 md:px-4 py-1.5 rounded-full
                                          text-xs md:text-sm font-bold shadow-lg transition-colors">
                                    <span class="text-[14px]">Hotline</span>
                                    {{ $currentRumahSakit->no_hotline }}
                                </a>
                                @endif
                                @if($currentRumahSakit->link_pendaftaran_online)
                                    <a href="{{ $currentRumahSakit->link_pendaftaran_online }}" target="_blank"
                                    class="inline-flex items-center gap-1.5 bg-yellow-400 hover:bg-yellow-300
                                            text-primary px-3 md:px-4 py-1.5 rounded-full
                                            text-xs md:text-sm font-bold shadow-lg transition-colors z-100">
                                            <button>
                                                <span class="text-[14px]">Buat Janji Dokter</span>
                                            </button>
                                    </a>
                                @endif
                            </div>

                            {{-- Quick Nav --}}
                            @php
                                $navClass = $hasFoto
                                    ? 'bg-white/15 hover:bg-white/25 border border-white/25 text-white backdrop-blur-sm'
                                    : 'bg-surface-container hover:bg-surface-container-high border border-outline-variant/30 text-on-surface';
                            @endphp
                            <div class="flex flex-wrap justify-center gap-2">
                                <a wire:navigate href="{{ rumahsakit_route('rumahsakit.dokter_kami') }}"
                                   class="inline-flex items-center gap-1.5 {{ $navClass }}
                                          px-3 py-1.5 rounded-full text-xs font-semibold transition-colors">
                                    <span class="material-symbols-outlined text-[13px]">stethoscope</span>
                                    Cari Dokter
                                </a>
                                <a wire:navigate href="{{ rumahsakit_route('rumahsakit.jadwal_praktek') }}"
                                   class="inline-flex items-center gap-1.5 {{ $navClass }}
                                          px-3 py-1.5 rounded-full text-xs font-semibold transition-colors">
                                    <span class="material-symbols-outlined text-[13px]">calendar_month</span>
                                    Jadwal Praktek
                                </a>
                                <a wire:navigate href="{{ rumahsakit_route('rumahsakit.rawat_inap') }}"
                                   class="inline-flex items-center gap-1.5 {{ $navClass }}
                                          px-3 py-1.5 rounded-full text-xs font-semibold transition-colors">
                                    <span class="material-symbols-outlined text-[13px]">bed</span>
                                    Rawat Inap
                                </a>
                                <a wire:navigate href="{{ rumahsakit_route('rumahsakit.unggulan') }}"
                                   class="inline-flex items-center gap-1.5 {{ $navClass }}
                                          px-3 py-1.5 rounded-full text-xs font-semibold transition-colors">
                                    <span class="material-symbols-outlined text-[13px]">star</span>
                                    Layanan Unggulan
                                </a>
                            </div>

                        </div>
                    </div>
                    {{-- End Welcome Slide --}}

                    @foreach($banner as $banner)
                    <div class="hs-carousel-slide ">
                        <img src="{{ Storage::url($banner->gambar) }}" class="w-full h-full object-contain" alt="">
                    </div>
                    @endforeach
                   
                </div>
                <!-- End Carousel Body -->
            </div>

            <!-- Arrows -->
            <button type="button"
                class="hs-carousel-prev hs-carousel-disabled:opacity-50 hs-carousel-disabled:cursor-default absolute top-1/2 inset-s-2 inline-flex justify-center items-center size-10 bg-layer text-layer-foreground rounded-full shadow-2xs hover:bg-layer-hover -translate-y-1/2 focus:outline-hidden bg-linear-to-l from-yellow-400 to-amber-500 text-white">
                <span class="text-2xl" aria-hidden="true">
                    <svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" class="shrink-0 size-5" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </span>
                <span class="sr-only">Previous</span>
            </button>
            <button type="button"
                class="hs-carousel-next hs-carousel-disabled:opacity-50 hs-carousel-disabled:cursor-default absolute top-1/2 inset-e-2 inline-flex justify-center items-center size-10 bg-layer text-layer-foreground rounded-full shadow-2xs hover:bg-layer-hover -translate-y-1/2 focus:outline-hidden bg-linear-to-r from-yellow-400 to-amber-500 text-white">
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
    <!-- End Carousel -->

    {{-- <script>
        (function () {
            function initCarouselDrag() {
                const carouselEl = document.getElementById('hs-carousel');
                if (!carouselEl) return;

                const body = carouselEl.querySelector('.hs-carousel-body');
                const prevBtn = carouselEl.querySelector('.hs-carousel-prev');
                const nextBtn = carouselEl.querySelector('.hs-carousel-next');
                if (!body || !prevBtn || !nextBtn) return;

                // Cegah re-init ganda
                if (body.dataset.dragInit) return;
                body.dataset.dragInit = '1';

                let startX = 0;
                let dragging = false;
                const THRESHOLD = 50;

                body.style.cursor = 'grab';

                // Nonaktifkan drag bawaan gambar
                carouselEl.querySelectorAll('img').forEach(img => { img.draggable = false; });

                body.addEventListener('pointerdown', (e) => {
                    startX = e.clientX;
                    dragging = true;
                    body.setPointerCapture(e.pointerId);
                    body.style.cursor = 'grabbing';
                });

                body.addEventListener('pointermove', (e) => {
                    if (!dragging) return;
                    e.preventDefault();
                }, { passive: false });

                body.addEventListener('pointerup', (e) => {
                    if (!dragging) return;
                    dragging = false;
                    body.style.cursor = 'grab';

                    const diff = startX - e.clientX;
                    if (Math.abs(diff) > THRESHOLD) {
                        diff > 0 ? nextBtn.click() : prevBtn.click();
                    }
                });

                body.addEventListener('pointercancel', () => {
                    dragging = false;
                    body.style.cursor = 'grab';
                });
            }

            document.addEventListener('DOMContentLoaded', initCarouselDrag);
            document.addEventListener('livewire:navigated', initCarouselDrag);
        })();
    </script> --}}

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
                                 class="w-10 h-10 object-cover rounded-xl" alt="{{ $layanan->label }}" loading="lazy">
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
    @if($rs->tentang_kami)
    <section class="flex flex-col lg:grid lg:grid-cols-2 mt-24">
        <div>
            <img
                src="{{ $rs->gambar_tentang ? Storage::url($rs->gambar_tentang) : asset('img/syifa-medika.webp') }}"
                class="w-full h-full object-cover"
                alt="{{ $rs->nama }}" loading="lazy">
        </div>
        <div class="p-6 relative">
            <img src="{{ asset('img/bg-header.png') }}"
                class="absolute top-0 left-0 right-0 w-full h-full -z-10 opacity-50 bg-blend-overlay object-cover blur-xs"
                alt="" loading="lazy">
            <h2 class="text-on-surface text-lg lg:text-xl">Kenapa harus memilih</h2>
            @if($rs->logo)
                <img src="{{ Storage::url($rs->logo) }}" class="h-24 lg:h-28 mt-2 object-contain" alt="{{ $rs->nama }}" loading="lazy">
            @endif
            <div class="text-sm lg:text-base text-on-surface mt-6 leading-7">
                {!! str($rs->tentang_kami)->sanitizeHtml() !!}
            </div>
        </div>
    </section>
    @endif

    <!-- layanan unggulan -->
    @if(count($layanan_unggulan) > 0)
    <section id="unggulan" class="bg-secondary mt-24 relative overflow-hidden py-16">

        {{-- Dekorasi background --}}
        <div class="absolute inset-0 opacity-[0.06] pointer-events-none"
             style="background-image:radial-gradient(circle, white 1.5px, transparent 1.5px); background-size:28px 28px;"></div>
        <div class="absolute -top-16 -right-16 w-64 h-64 bg-white/8 rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full pointer-events-none"></div>

        <div class="relative z-10 w-11/12 lg:w-10/12 mx-auto">

            {{-- Header --}}
            <div class="text-center mb-12">
                <h2 class="text-white font-bold text-2xl md:text-3xl leading-tight">Layanan Unggulan</h2>
                <div class="h-0.5 w-12 bg-white/40 rounded-full mx-auto mt-4"></div>
            </div>

            {{-- Grid kartu --}}
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($layanan_unggulan as $lu)
                <a wire:navigate href="{{ rumahsakit_route('rumahsakit.unggulan') }}"
                   class="group flex flex-col items-center bg-white/10 hover:bg-white/20
                          border border-white/15 hover:border-white/30
                          rounded-2xl p-5 text-center backdrop-blur-sm
                          transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-black/20">

                    {{-- Gambar bulat --}}
                    <div class="w-20 h-20 rounded-full overflow-hidden border-4 border-white/25
                                group-hover:border-white/50 transition-colors duration-300
                                shadow-md mb-4 shrink-0">
                        <img src="{{ Storage::url($lu->gambar) }}"
                             alt="{{ $lu->nama }}"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                             loading="lazy">
                    </div>

                    {{-- Nama --}}
                    <p class="text-white font-semibold text-sm leading-snug">{{ $lu->nama }}</p>

                    {{-- Arrow hint --}}
                    <span class="material-symbols-outlined text-white/40 group-hover:text-white/80
                                 group-hover:translate-x-0.5 text-[15px] mt-2 transition-all duration-200">
                        arrow_forward
                    </span>
                </a>
                @endforeach
            </div>

            {{-- Lihat semua --}}
            <div class="text-center mt-10">
                <a wire:navigate href="{{ rumahsakit_route('rumahsakit.unggulan') }}"
                   class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full
                          border-2 border-white/40 text-white text-sm font-semibold
                          hover:bg-white hover:text-secondary transition-all duration-200">
                    Lihat Semua Layanan
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>

        </div>
    </section>
    @endif

    <!-- dokter kami -->
    <section class="mt-24">
        <div class="w-11/12 lg:w-10/12 mx-auto">
            <h2 class="text-tertiary text-4xl text-center font-bold">Dokter Kami</h2>
            <p class="text-on-surface-variant text-center mt-4 w-4/6 mx-auto text-sm md:text-base">
                Kami memiliki banyak dokter spesialis hingga sub spesialis ahli dibidangnya
                dan melayani Anda secara profesional dan terpercaya.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 mt-8 gap-8 md:gap-10">
                @foreach($dokter_kami as $dk)
                <div class="group overflow-hidden bg-white rounded-2xl shadow-sm border border-outline-variant/20
                            hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col">

                    {{-- Foto --}}
                    <div class="aspect-square overflow-hidden bg-gray-100">
                        <img alt="{{ $dk->nama }}"
                             class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500"
                             src="{{ Storage::url($dk->foto) }}" loading="lazy">
                    </div>

                    {{-- Info --}}
                    <div class="p-5 text-center flex flex-col flex-1">
                        <h4 class="font-semibold text-on-surface leading-snug">{{ $dk->nama }}</h4>
                        <span class="inline-block mt-2 mb-4 px-3 py-1 text-xs font-semibold
                                     text-primary bg-primary/10 rounded-full mx-auto">
                            {{ $dk->namaSpesialis() }}
                        </span>

                        {{-- Tombol --}}
                        <div class="mt-auto">
                            <a wire:navigate
                               href="{{ route('rumahsakit.dokter_show', [$currentRumahSakit->slug, $dk->slug]) }}"
                               class="inline-flex items-center justify-center gap-1.5 w-full px-4 py-2 rounded-lg
                                      bg-tertiary text-white text-sm font-semibold
                                      hover:opacity-90 transition">
                                <span class="material-symbols-outlined text-[16px]">person</span>
                                Lihat Profil
                            </a>
                        </div>
                    </div>

                </div>
                @endforeach
            </div>
        </div>
        <div class="mt-8">
            <a href="{{ rumahsakit_route('rumahsakit.dokter_kami') }}">
                <button
                    class="w-fit sm:w-auto self-start border-2 border-tertiary bg-tertiary text-white px-6 py-2 rounded-lg text-label-md font-label-md transition-colors flex items-center gap-2 text-sm mx-auto cursor-pointer">
                    Lihat Selengkapnya
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </button>
            </a>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- CTA — Siap Melayani                                          --}}
    {{-- ============================================================ --}}
    <section class="mt-24 relative overflow-hidden" data-aos="fade-up">

        <div class="absolute inset-0"
             style="background: linear-gradient(135deg, #4d51b2 0%, #3a3e99 50%, #2e3180 100%);"></div>
        <div class="absolute inset-0 opacity-[0.06]"
             style="background-image:radial-gradient(circle,white 1px,transparent 1px);background-size:24px 24px;"></div>
        <div class="absolute -top-20 -right-20 w-80 h-80 rounded-full opacity-10" style="background:white;"></div>
        <div class="absolute -bottom-20 -left-20 w-64 h-64 rounded-full opacity-10" style="background:white;"></div>

        <div class="relative z-10 w-10/12 mx-auto py-16 flex flex-col lg:flex-row items-center justify-between gap-10">

            {{-- Teks kiri --}}
            <div class="text-center lg:text-left">
                <span class="inline-block text-white/60 text-xs uppercase tracking-[0.2em] font-semibold mb-3">
                    Layanan Kami
                </span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-white leading-tight">
                    Siap Melayani<br>Kesehatan Anda
                </h2>
                <div class="mt-4 h-1 w-12 bg-yellow-400 rounded-full lg:mx-0 mx-auto"></div>
                <p class="mt-4 text-white/70 text-base max-w-md leading-relaxed">
                    Tim dokter spesialis dan tenaga medis profesional kami siap memberikan
                    pelayanan kesehatan terbaik untuk Anda dan keluarga.
                </p>
            </div>

            {{-- Tombol kanan --}}
            <div class="flex flex-col gap-3 shrink-0 w-full lg:w-auto">
                @if($rs->link_pendaftaran_online)
                <a href="{{ $rs->link_pendaftaran_online }}" target="_blank"
                   class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl
                          font-bold text-primary bg-yellow-400 hover:bg-yellow-300
                          shadow-xl transition-all duration-150 text-base">
                    <span class="material-symbols-outlined text-[22px]">assignment</span>
                    Buat Janji Sekarang
                </a>
                @endif
                <div class="flex gap-3">
                    @if($rs->no_emergency)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $rs->no_emergency) }}"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl
                              bg-red-600 hover:bg-red-700 text-white font-semibold text-sm shadow-lg transition-all">
                        <span class="material-symbols-outlined text-[18px]">emergency</span>
                        {{ $rs->no_emergency }}
                    </a>
                    @endif
                    @if($rs->no_hotline)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $rs->no_hotline) }}"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl
                              bg-green-600 hover:bg-green-700 text-white font-semibold text-sm shadow-lg transition-all">
                        <span class="material-symbols-outlined text-[18px]">call</span>
                        {{ $rs->no_hotline }}
                    </a>
                    @endif
                </div>
            </div>

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
                                <img src="{{ Storage::url($partner->logo) }}" alt="{{ $partner->nama }}" class="h-14 object-contain" loading="lazy">
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
                                <img src="{{ Storage::url($partner->logo) }}" alt="{{ $partner->nama }}" class="h-14 object-contain" loading="lazy">
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

    {{-- ============================================================ --}}
    {{-- PROMO                                                        --}}
    {{-- ============================================================ --}}
    @if($promos->count() > 0)
    <section class="mt-24" data-aos="fade-up">
        <div class="w-11/12 lg:w-10/12 mx-auto">

            <div class="flex items-end justify-between mb-8">
                <div>
                    <span class="text-xs text-on-surface-variant uppercase tracking-widest font-semibold">Penawaran Spesial</span>
                    <h2 class="text-2xl md:text-3xl font-bold text-on-surface mt-1">Promo & Penawaran</h2>
                </div>
                <a wire:navigate href="{{ rumahsakit_route('rumahsakit.promo') }}"
                   class="inline-flex items-center gap-1 text-sm font-semibold text-primary hover:gap-2 transition-all shrink-0">
                    Lihat Semua <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($promos as $p)
                <a wire:navigate
                   href="{{ route('rumahsakit.promo_detail', ['rumahsakit' => $rs->slug, 'promo' => $p->slug]) }}"
                   class="group bg-white rounded-2xl overflow-hidden shadow-sm border border-outline-variant/20
                          hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col animate-fade-in"
                   style="animation-delay: {{ $loop->index * 50 }}ms">

                    @if($p->gambar)
                    <div class="h-48 overflow-hidden relative bg-surface-container/30">
                        <img src="{{ Storage::url($p->gambar) }}" alt="{{ $p->judul }}"
                             class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500"
                             loading="lazy">
                        @if($p->popup)
                        <div class="absolute top-3 right-3">
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-yellow-400 text-primary px-2 py-0.5 rounded-full">
                                <span class="material-symbols-outlined text-[10px]">star</span> Unggulan
                            </span>
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="h-48 bg-primary/8 flex items-center justify-center relative">
                        <span class="material-symbols-outlined text-6xl text-primary/30">local_offer</span>
                        @if($p->popup)
                        <div class="absolute top-3 right-3">
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-yellow-400 text-primary px-2 py-0.5 rounded-full">
                                <span class="material-symbols-outlined text-[10px]">star</span> Unggulan
                            </span>
                        </div>
                        @endif
                    </div>
                    @endif

                    <div class="p-4 flex flex-col flex-1">
                        <h3 class="font-bold text-on-surface text-sm leading-snug mb-2 line-clamp-2
                                   group-hover:text-primary transition-colors duration-200">
                            {{ $p->judul }}
                        </h3>
                        @if($p->deskripsi)
                        <p class="text-xs text-on-surface-variant line-clamp-2 flex-1 leading-relaxed">
                            {{ strip_tags($p->deskripsi) }}
                        </p>
                        @else
                        <div class="flex-1"></div>
                        @endif
                        <div class="flex items-center gap-1 mt-3 text-xs font-semibold text-primary
                                    pt-3 border-t border-outline-variant/20">
                            Lihat Detail
                            <span class="material-symbols-outlined text-[13px] group-hover:translate-x-0.5 transition-transform">
                                arrow_forward
                            </span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

        </div>
    </section>
    @endif

    {{-- ============================================================ --}}
    {{-- FAQ                                                          --}}
    {{-- ============================================================ --}}
    @if($faqs->isNotEmpty())
    <section class="mt-24" data-aos="fade-up">
        <div class="w-11/12 lg:w-10/12 mx-auto">

            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
                <div>
                    <div class="inline-flex items-center gap-2 bg-primary/8 text-primary
                                text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full mb-3">
                        <span class="material-symbols-outlined text-[13px]">help</span>
                        Pertanyaan Umum
                    </div>
                    <h2 class="text-2xl md:text-3xl font-bold text-on-surface leading-tight">
                        Ada yang ingin <span class="text-primary">kamu tanyakan?</span>
                    </h2>
                    <p class="text-on-surface-variant text-sm mt-2 max-w-md leading-relaxed">
                        Temukan jawaban atas pertanyaan yang paling sering diajukan.
                    </p>
                </div>
                <a wire:navigate href="{{ rumahsakit_route('rumahsakit.faq') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full border-2 border-primary
                          text-primary text-sm font-semibold hover:bg-primary hover:text-white
                          transition-all duration-200 shrink-0 self-start sm:self-auto">
                    Lihat Semua FAQ
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>

            {{-- Accordion --}}
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 items-start">

                {{-- Ilustrasi / Dekorasi kiri (desktop) --}}
                <div class="hidden lg:flex lg:col-span-2 flex-col items-center justify-center gap-6 sticky top-24">
                    <div class="w-full bg-linear-to-br from-primary/8 to-secondary/8
                                rounded-3xl p-8 flex flex-col items-center text-center gap-4 border border-primary/10">
                        <div class="w-16 h-16 rounded-2xl bg-primary flex items-center justify-center shadow-lg shadow-primary/25">
                            <span class="material-symbols-outlined text-white text-[28px]"
                                  style="font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 48;">
                                quiz
                            </span>
                        </div>
                        <div>
                            <p class="font-bold text-on-surface text-lg">Masih ada pertanyaan?</p>
                            <p class="text-on-surface-variant text-sm mt-1 leading-relaxed">
                                Kami siap membantu anda.
                            </p>
                        </div>
                        <a wire:navigate href="{{ rumahsakit_route('rumahsakit.hubungi_kami') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white
                                  text-sm font-semibold rounded-full hover:bg-primary/90
                                  transition-colors shadow-sm shadow-primary/30 w-full justify-center">
                            <span class="material-symbols-outlined text-[16px]">chat</span>
                            Hubungi Kami
                        </a>
                    </div>
                </div>

                {{-- Daftar FAQ --}}
                <div class="lg:col-span-3 flex flex-col gap-3">
                    @foreach($faqs as $faq)
                    <div
                        x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }"
                        class="rounded-2xl border transition-all duration-200 overflow-hidden"
                        :class="open
                            ? 'border-primary/30 bg-primary/4 shadow-sm shadow-primary/10'
                            : 'border-outline-variant/30 bg-white hover:border-primary/20 hover:bg-primary/2'"
                    >
                        <button
                            @click="open = !open"
                            class="w-full flex items-start gap-4 px-5 py-4 text-left cursor-pointer"
                            :aria-expanded="open"
                        >
                            {{-- Nomor --}}
                            <span class="shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold mt-0.5 transition-colors duration-200"
                                  :class="open ? 'bg-primary text-white' : 'bg-outline-variant/20 text-on-surface-variant'">
                                {{ $loop->index + 1 }}
                            </span>

                            <span class="flex-1 font-semibold text-on-surface text-sm md:text-base leading-snug">
                                {{ $faq->judul }}
                            </span>

                            <span class="material-symbols-outlined shrink-0 text-[20px] mt-0.5 transition-all duration-200"
                                  :class="open ? 'text-primary rotate-180' : 'text-on-surface-variant/50 rotate-0'">
                                expand_more
                            </span>
                        </button>

                        <div x-show="open" x-collapse>
                            <div class="px-5 pb-5 pl-16 text-sm text-on-surface-variant leading-relaxed">
                                {!! str($faq->deskripsi)->sanitizeHtml() !!}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>

        </div>
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
