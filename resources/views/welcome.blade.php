@extends('layouts.portal-layout')

@section('title', 'RSU SYIFA MEDIKA - Pelayanan Professional & Terpercaya')

@section('content')
<main class="grow w-full">

    {{-- overflow-visible agar Tom Select dropdown tidak terpotong --}}
    <section class="relative min-h-[75vh] flex flex-col items-center justify-center">

        {{-- Dekorasi + overlay dibungkus overflow-hidden sendiri agar lingkaran tidak meluap --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            {{-- Overlay gradient --}}
            <div class="absolute inset-0"
                 style="background: linear-gradient(160deg, rgba(214,6,176,0.88) 0%, rgba(214,6,176,0.78) 40%, rgba(0,108,75,0.82) 100%);"></div>
            {{-- Pola titik --}}
            <div class="absolute inset-0 opacity-[0.08]"
                 style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 22px 22px;"></div>
            {{-- Lingkaran dekorasi --}}
            <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full opacity-10" style="background:white;"></div>
            <div class="absolute -bottom-40 -left-20 w-80 h-80 rounded-full opacity-10" style="background:white;"></div>
            {{-- Fade ke bawah --}}
            <div class="absolute bottom-0 left-0 right-0 h-40"
                 style="background: linear-gradient(to bottom, transparent, rgba(248,249,255,0.9));"></div>
        </div>

        {{-- ── Konten ──────────────────────────────────────────────── --}}
        <div class="relative w-full max-w-4xl mx-auto px-6 flex flex-col items-center pt-16 pb-10 gap-10"
             style="z-index: 10;">

            {{-- Teks welcome --}}
            <div class="text-center" data-aos="fade-down">

                {{-- Label atas --}}
                <span class="inline-flex items-center gap-2 text-white/70 text-xs uppercase tracking-[0.22em] font-semibold mb-4">
                    <span class="w-8 h-px bg-white/40 inline-block"></span>
                    Selamat Datang di
                    <span class="w-8 h-px bg-white/40 inline-block"></span>
                </span>

                {{-- Nama RS --}}
                <h1 class="text-3xl md:text-5xl lg:text-6xl font-black text-white leading-tight tracking-tight uppercase drop-shadow-md">
                    RSU SYIFA MEDIKA
                </h1>

                {{-- Divider + slogan --}}
                <div class="flex items-center justify-center gap-3 mt-5 mb-4">
                    <div class="h-px flex-1 max-w-16 bg-white/30"></div>
                    <div class="w-10 h-1 bg-yellow-400 rounded-full"></div>
                    <div class="h-px flex-1 max-w-16 bg-white/30"></div>
                </div>

                <p class="text-yellow-300 text-sm md:text-base font-semibold tracking-wide italic mb-3">
                    "Layanan Profesional dan Terpercaya"
                </p>

                <p class="text-white/65 text-sm md:text-base max-w-md mx-auto leading-relaxed">
                    Temukan informasi dokter spesialis, jadwal praktek, dan layanan kesehatan untuk Anda dan keluarga.
                </p>
            </div>

            {{-- ── Search Card ──────────────────────────────────────── --}}
            {{-- z-index tinggi agar Tom Select dropdown tidak tertutup section bawah --}}
            <div class="w-full" style="z-index: 50; position: relative;" data-aos="fade-up" data-aos-delay="100">
                <form id="searchForm"
                      class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-white/60 p-5 md:p-6">

                    <p class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[16px]">search</span>
                        Cari Dokter Spesialis
                    </p>

                    <div class="flex flex-col md:flex-row gap-4 items-end">

                        {{-- RS Select --}}
                        <div class="w-full md:flex-1">
                            <label class="block text-xs font-semibold text-on-surface-variant mb-1.5">
                                Rumah Sakit <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2
                                             text-primary text-[20px] pointer-events-none z-10">local_hospital</span>
                                <select id="rsSelect"
                                    class="w-full pl-11 pr-4 py-3 bg-surface-container/60 border border-outline-variant
                                           rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20
                                           outline-none appearance-none cursor-pointer transition" required>
                                    <option value="">— Pilih Rumah Sakit —</option>
                                    @foreach($rumahsakit as $rs)
                                        <option value="{{ $rs->slug }}">{{ $rs->nama }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2
                                             text-on-surface-variant text-[18px] pointer-events-none">expand_more</span>
                            </div>
                        </div>

                        {{-- Spesialis (Tom Select) --}}
                        <div class="w-full md:flex-1 ts-portal-wrapper" id="spesialisWrapper">
                            <label class="block text-xs font-semibold text-on-surface-variant mb-1.5">
                                Spesialis
                            </label>
                            <select id="spesialisSelect">
                                <option value="">— Semua Spesialis —</option>
                            </select>
                        </div>

                        {{-- Tombol --}}
                        <div class="w-full md:w-auto shrink-0">
                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 px-7 py-3 rounded-xl
                                       font-semibold text-sm text-white transition-all duration-150
                                       shadow-lg shadow-primary/30 hover:opacity-90 active:scale-95"
                                style="background: linear-gradient(135deg, #d606b0, #a004a0);">
                                <span class="material-symbols-outlined text-[18px]">search</span>
                                Cari Dokter
                            </button>
                        </div>

                    </div>
                </form>
            </div>

        </div>
    </section>

    {{-- =====================================================================
         HOSPITAL CARDS
    ====================================================================== --}}
    <section class="relative py-16" style="background: rgba(248,249,255,0.72); backdrop-filter: blur(2px);">
        <div class="w-11/12 lg:w-10/12 mx-auto">

            <div class="text-center mb-10" data-aos="fade-up">
                <h2 class="text-2xl md:text-3xl font-bold text-on-surface">Kunjungi Rumah Sakit Kami</h2>
                <p class="text-on-surface-variant mt-2 text-sm md:text-base max-w-xl mx-auto">
                    Pilih rumah sakit terdekat dan temukan layanan kesehatan yang Anda butuhkan.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-10">
                @foreach ($rumahsakit as $rs)
                <div class="group bg-white rounded-2xl overflow-hidden shadow-sm
                            hover:shadow-xl hover:-translate-y-1 transition-all duration-300
                            border border-outline-variant/20 flex flex-col cursor-pointer"
                     onclick="window.location='/{{ $rs->slug }}'"
                     data-aos="fade-up">

                    {{-- Gambar --}}
                    <div class="h-56 overflow-hidden relative bg-primary/10">
                        @if($rs->gambar)
                            <img alt="{{ $rs->nama }}"
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                 src="{{ Storage::url($rs->gambar) }}">
                            <div class="absolute inset-0 bg-linear-to-t from-black/30 to-transparent"></div>
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <span class="material-symbols-outlined text-6xl text-primary/30">local_hospital</span>
                            </div>
                        @endif

                        {{-- Badge lokasi --}}
                        <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1
                                    rounded-full text-xs font-semibold text-primary flex items-center gap-1 shadow-sm">
                            <span class="material-symbols-outlined text-[14px]">location_on</span>
                            {{ $rs->lokasi }}
                        </div>
                    </div>

                    {{-- Konten --}}
                    <div class="p-6 flex flex-col flex-1">

                        <div class="flex items-start justify-between gap-3 mb-2">
                            <h2 class="text-lg font-bold text-primary leading-snug group-hover:text-primary/80 transition-colors">
                                {{ $rs->nama }}
                            </h2>
                            @if($rs->logo)
                                <img alt="{{ $rs->nama }}"
                                     class="h-8 w-auto object-contain shrink-0"
                                     src="{{ Storage::url($rs->logo) }}"
                                     onerror="this.style.display='none'">
                            @endif
                        </div>

                        <p class="text-sm text-on-surface-variant grow leading-relaxed">
                            {{ $rs->alamat }}
                        </p>

                        {{-- Kontak — tombol besar agar langsung terlihat --}}
                        @if($rs->no_emergency || $rs->no_hotline)
                        <div class="flex flex-wrap gap-3 mt-4 pt-4 border-t border-outline-variant/30">
                            @if($rs->no_emergency)
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $rs->no_emergency) }}"
                               onclick="event.stopPropagation()"
                               class="flex-1 inline-flex items-center gap-3 px-4 py-3 rounded-xl
                                      bg-red-50 border border-red-200 hover:bg-red-100 transition-colors group/em">
                                <span class="material-symbols-outlined text-[22px] text-white bg-red-600
                                             p-2 rounded-lg shrink-0 shadow-sm group-hover/em:bg-red-700 transition-colors">emergency</span>
                                <div class="min-w-0">
                                    <span class="block text-[10px] font-bold text-red-600 uppercase tracking-wide">Emergency</span>
                                    <span class="block text-sm font-bold text-red-700 truncate">{{ $rs->no_emergency }}</span>
                                </div>
                            </a>
                            @endif
                            @if($rs->no_hotline)
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $rs->no_hotline) }}"
                               onclick="event.stopPropagation()"
                               class="flex-1 inline-flex items-center gap-3 px-4 py-3 rounded-xl
                                      bg-green-50 border border-green-200 hover:bg-green-100 transition-colors group/ht">
                                <span class="material-symbols-outlined text-[22px] text-white bg-green-600
                                             p-2 rounded-lg shrink-0 shadow-sm group-hover/ht:bg-green-700 transition-colors">call</span>
                                <div class="min-w-0">
                                    <span class="block text-[10px] font-bold text-green-600 uppercase tracking-wide">Hotline</span>
                                    <span class="block text-sm font-bold text-green-700 truncate">{{ $rs->no_hotline }}</span>
                                </div>
                            </a>
                            @endif
                        </div>
                        @endif

                        {{-- Quick links --}}
                        <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-outline-variant/20">
                            <a href="/{{ $rs->slug }}/dokter-kami" onclick="event.stopPropagation()"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium
                                      bg-surface-container border border-outline-variant/40 text-on-surface-variant
                                      hover:bg-primary/10 hover:text-primary hover:border-primary/30 transition-colors">
                                <span class="material-symbols-outlined text-[13px]">stethoscope</span>
                                Dokter Kami
                            </a>
                            <a href="/{{ $rs->slug }}/jadwal-praktek" onclick="event.stopPropagation()"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium
                                      bg-surface-container border border-outline-variant/40 text-on-surface-variant
                                      hover:bg-primary/10 hover:text-primary hover:border-primary/30 transition-colors">
                                <span class="material-symbols-outlined text-[13px]">calendar_month</span>
                                Jadwal Praktek
                            </a>
                            @foreach($rs->linkLayanan as $ll)
                            <a href="{{ $ll->link }}" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation()"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium
                                      bg-surface-container border border-outline-variant/40 text-on-surface-variant
                                      hover:bg-surface-container-high hover:text-on-surface transition-colors">
                                {{ $ll->label }}
                            </a>
                            @endforeach
                        </div>

                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </section>

    <!-- Promo Section -->
    <section id="promo-section"
        class="mt-16 md:mt-24 mb-section-gap bg-secondary rounded-3xl p-8 md:p-10 shadow-[0_20px_50px_rgba(0,108,75,0.15)] border border-secondary-container/20 relative overflow-hidden w-full lg:w-10/12 mx-auto"
        data-aos="fade-up">
        <!-- Background Decorative Blobs -->
        <div class="absolute -top-24 -right-24 w-72 h-72 bg-secondary-container/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-primary/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="text-center max-w-3xl mx-auto my-10 relative z-10">
            <span class="px-4 py-1.5 bg-secondary/10 text-white border border-white/20 rounded-full text-label-md font-bold tracking-wider uppercase mb-3 inline-block">
                Promo & Penawaran Spesial
            </span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight drop-shadow-sm">
                PROMO KESEHATAN MENARIK
            </h2>
            <p class="text-white/80 mt-2 max-w-xl mx-auto text-body-md">
                Temukan penawaran pelayanan kesehatan terbaik dan diskon khusus yang tersedia di RSU Syifa Medika.
            </p>
        </div>

        <!-- Promo Filter -->
        <div class="flex flex-col sm:flex-row justify-center items-center gap-4 mb-12 max-w-md mx-auto px-4 relative z-10">
            <label class="text-label-md font-bold text-white/95 whitespace-nowrap">RUMAH SAKIT</label>
            <div class="relative w-full">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-white/60">location_on</span>
                <select id="promo-hospital-filter"
                    class="w-full pl-10 pr-10 py-3 bg-white/10 border border-white/20 rounded-xl text-body-md font-body-md text-white focus:bg-slate-900 focus:border-white focus:ring-2 focus:ring-white/20 outline-none appearance-none cursor-pointer transition-all shadow-sm hover:bg-white/15">
                    <option class="text-slate-900 bg-white" value="all">Semua Rumah Sakit</option>
                    @foreach($rumahsakit as $rs)
                    <option class="text-slate-900 bg-white" value="{{ $rs->slug }}">{{ $rs->nama }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-white/60 pointer-events-none">expand_more</span>
            </div>
        </div>

        <!-- Promo Grid -->
        <div id="promo-grid"
            class="{{ $promos->isEmpty() ? 'hidden' : 'grid' }} grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 w-full relative z-10 transition-all duration-300">
            @foreach($promos as $p)
            <div class="promo-card group bg-surface-container-lowest rounded-2xl overflow-hidden shadow-[0_10px_25px_-5px_rgba(0,0,0,0.05)] hover:shadow-[0_20px_40px_-10px_rgba(0,0,0,0.1)] border border-outline-variant/30 transition-all duration-300 hover:-translate-y-1.5 flex flex-col"
                data-hospital="{{ $p->rumahSakit->slug }}" style="transition: all 0.3s ease, opacity 0.3s ease, transform 0.3s ease;">

                @if($p->gambar)
                <div class="h-56 overflow-hidden relative cursor-pointer promo-img-wrapper">
                    <img alt="{{ $p->judul }}"
                        class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105"
                        src="{{ Storage::url($p->gambar) }}">
                    <div class="absolute top-4 left-4 bg-primary text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-md tracking-wider uppercase">
                        {{ $p->rumahSakit->lokasi }}
                    </div>
                    @if($p->popup)
                    <div class="absolute top-4 right-4">
                        <span class="inline-flex items-center gap-0.5 text-[10px] font-bold uppercase bg-yellow-400 text-primary px-2 py-0.5 rounded-full shadow-sm">
                            <span class="material-symbols-outlined text-[10px]">star</span> Unggulan
                        </span>
                    </div>
                    @endif
                    <div class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-[2px]">
                        <span class="material-symbols-outlined text-white text-4xl bg-primary-container/85 p-3 rounded-full shadow-lg transform scale-75 group-hover:scale-100 transition-transform duration-300">zoom_in</span>
                    </div>
                </div>
                @else
                <div class="h-56 overflow-hidden relative bg-secondary/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-7xl text-secondary/30">local_offer</span>
                    <div class="absolute top-4 left-4 bg-primary text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-md tracking-wider uppercase">
                        {{ $p->rumahSakit->lokasi }}
                    </div>
                </div>
                @endif

                <div class="p-6 flex flex-col grow">
                    <h3 class="text-lg font-bold text-on-surface mb-2 line-clamp-2 group-hover:text-secondary transition-colors">
                        {{ $p->judul }}
                    </h3>
                    @if($p->deskripsi)
                    <p class="text-body-sm text-on-surface-variant mb-4 line-clamp-3 grow">
                        {{ strip_tags($p->deskripsi) }}
                    </p>
                    @else
                    <div class="grow"></div>
                    @endif
                    <div class="border-t border-outline-variant/30 pt-4 flex justify-end items-center mt-auto">
                        <a href="{{ route('rumahsakit.promo_detail', ['rumahsakit' => $p->rumahSakit->slug, 'promo' => $p->slug]) }}"
                           class="text-label-md font-bold text-secondary hover:text-secondary-container flex items-center gap-1 transition-colors">
                            Lihat Detail
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Empty State -->
        <div id="promo-empty-state" class="text-center py-16 max-w-md mx-auto px-4 @if($promos->isNotEmpty()) hidden @endif">
            <span class="material-symbols-outlined text-6xl mb-3 block text-white">campaign</span>
            <h3 class="text-xl font-bold text-on-secondary">Tidak ada Promo Aktif</h3>
            <p class="text-body-md text-on-secondary mt-2">
                Saat ini belum ada promo aktif. Silakan kembali lagi nanti atau hubungi customer service kami.
            </p>
        </div>
    </section>

    <!-- Lightbox Modal -->
    <div id="promo-lightbox"
        class="fixed inset-0 z-100 bg-black/85 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300 backdrop-blur-sm">
        <button id="promo-lightbox-close"
            class="absolute top-6 right-6 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2.5 rounded-full transition-all cursor-pointer shadow-lg">
            <span class="material-symbols-outlined text-2xl">close</span>
        </button>
        <div class="relative max-w-4xl max-h-[85vh] w-full flex flex-col items-center transform scale-95 opacity-0 transition-all duration-300"
            id="promo-lightbox-content">
            <img id="promo-lightbox-img" src="" alt="Promo Poster"
                class="max-h-[70vh] w-auto max-w-full object-contain rounded-xl shadow-2xl border border-white/10">
            <div class="mt-4 text-center text-white px-6 py-3 bg-white/15 backdrop-blur-md rounded-2xl max-w-xl shadow-lg border border-white/5">
                <h3 id="promo-lightbox-title" class="text-lg font-bold"></h3>
                <p id="promo-lightbox-desc" class="text-sm text-white/85 mt-1"></p>
            </div>
        </div>
    </div>

</main>

{{-- =====================================================================
     SCRIPTS — Tom Select + Fetch (no jQuery)
====================================================================== --}}
<script src="{{ asset('js/promo-lightbox.js') }}"></script>
<script>
(function () {
    // ── Tom Select: Spesialis ──────────────────────────────────────────────
    let spesialisTs = null;

    function initSpesialisTs() {
        if (spesialisTs) { spesialisTs.destroy(); spesialisTs = null; }

        spesialisTs = new TomSelect('#spesialisSelect', {
            placeholder: '— Semua Spesialis —',
            allowEmptyOption: true,
            maxItems: 1,
            create: false,
            sortField: 'text',
            maxOptions: 100,
        });
        spesialisTs.disable();
    }

    function loadSpesialis(rsSlug) {
        if (!rsSlug) {
            spesialisTs.clear();
            spesialisTs.clearOptions();
            spesialisTs.addOption({ value: '', text: '— Semua Spesialis —' });
            spesialisTs.disable();
            return;
        }

        fetch('/cari-spesialis?rs=' + encodeURIComponent(rsSlug))
            .then(r => r.json())
            .then(data => {
                spesialisTs.clear();
                spesialisTs.clearOptions();
                spesialisTs.addOption({ value: '', text: '— Semua Spesialis —' });
                data.forEach(s => spesialisTs.addOption({ value: s.slug, text: s.nama }));
                spesialisTs.setValue('', true);
                spesialisTs.enable();
            })
            .catch(() => {
                spesialisTs.enable();
            });
    }

    // ── Form submit ────────────────────────────────────────────────────────
    function setupForm() {
        const rsSelect     = document.getElementById('rsSelect');
        const searchForm   = document.getElementById('searchForm');
        if (! rsSelect || ! searchForm) return;

        rsSelect.addEventListener('change', () => loadSpesialis(rsSelect.value));

        searchForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const rsSlug    = rsSelect.value;
            const spesialis = spesialisTs ? spesialisTs.getValue() : '';

            if (! rsSlug) {
                alert('Silakan pilih Rumah Sakit terlebih dahulu.');
                return;
            }

            let url = '/' + rsSlug + '/dokter-kami';
            if (spesialis) url += '?spesialis=' + encodeURIComponent(spesialis);
            window.location.href = url;
        });
    }

    // ── Promo filter ───────────────────────────────────────────────────────
    function setupPromoFilter() {
        const filter = document.getElementById('promo-hospital-filter');
        const grid   = document.getElementById('promo-grid');
        if (! filter || ! grid) return;

        filter.addEventListener('change', function () {
            const val   = this.value;
            const cards = grid.querySelectorAll('.promo-card');
            cards.forEach(card => {
                card.style.display = (val === 'all' || card.dataset.hospital === val)
                    ? 'flex' : 'none';
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initSpesialisTs();
        setupForm();
        setupPromoFilter();
    });
})();
</script>
@endsection
