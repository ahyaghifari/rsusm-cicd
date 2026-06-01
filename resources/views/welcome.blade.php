@extends('layouts.portal-layout')

@section('title', 'RSU SYIFA MEDIKA - Pelayanan Professional & Terpercaya')

@section('content')
    <!-- Main Content -->
    <main class="grow w-full px-margin-mobile md:px-margin-desktop py-section-gap">

        <h1 class="text-center text-3xl text-primary mb-8 font-montserrat font-bold">Selamat Datang di, <br> <span
                class="text-white font-bold mt-2 px-2 bg-tertiary">RUMAH SAKIT UMUM SYIFA MEDIKA</span></h1>

        <!-- Search & Filter Section -->
        <section class="mb-section-gap text-center max-w-4xl mx-auto shadow rounded">
            <form action="" id="searchForm">
            <div
                class="bg-inverse-primary p-6 rounded-xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.05)] border border-outline-variant/30 flex flex-col md:flex-row gap-4 items-end">
                <div class="w-full md:w-2/5 flex flex-col items-start">
                    <label class="text-label-md font-label-md text-on-surface-variant mb-2">RUMAH SAKIT</label>
                    <div class="relative w-full">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">location_on</span>
                        <select
                            id="rsSelect"
                            class="w-full pl-10 pr-4 py-3 bg-surface border border-outline-variant rounded-lg text-body-md font-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-none appearance-none cursor-pointer" required>
                            <option>- Pilih Rumah Sakit -</option>
                            @foreach($rumahsakit as $rs)
                                <option value="{{$rs->slug}}">{{ $rs->nama }}</option>
                            @endforeach
                        </select>
                        <span
                            class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none">expand_more</span>
                    </div>
                </div>
                <div class="w-full md:w-2/5 flex flex-col items-start">
                    <label class="text-label-md font-label-md text-on-surface-variant mb-2">SPESIALIS</label>
                    <div class="relative w-full">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">stethoscope</span>
                        <select
                            id="spesialisSelect"
                            class="w-full pl-10 pr-4 py-3 bg-surface border border-outline-variant rounded-lg text-body-md font-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-none appearance-none cursor-pointer disabled:opacity-50">
                            <option value="">- Semua Spesialis -</option>
                        </select>
                        <span
                            class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none">expand_more</span>
                    </div>
                </div>
                <div class="w-full md:w-1/5">
                    <button
                        class="w-full bg-tertiary text-on-primary py-3 rounded-lg text-label-md font-label-md hover:bg-primary-container transition-colors flex items-center justify-center gap-2 cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">search</span>
                        Cari
                    </button>
                </div>
            </div>
            </form>
        </section>

        <h2 class="text-center text-2xl font-bold mb-8
                   bg-linear-to-r from-primary to-tertiary bg-clip-text text-transparent"
            data-aos="fade-up">Kunjungi rumah sakit terdekat</h2>

        <!-- Hospital Cards Section -->
        <section class="grid grid-cols-1 md:grid-cols-2 gap-10 w-11/12 lg:w-10/12 mx-auto">
            <!-- Card 1: MediGroup Pusat -->
            @foreach ($rumahsakit as $rs)
            <div class="group bg-surface-container-lowest rounded-2xl overflow-hidden shadow-[0_10px_25px_-5px_rgba(0,0,0,0.05)] hover:shadow-[0_20px_40px_-10px_rgba(0,0,0,0.1)] transition-all duration-300 hover:-translate-y-1 flex flex-col border border-outline-variant/20 cursor-pointer"
                onclick="window.location='/{{ $rs->slug }}'"
                data-aos="fade-up">
                    <div class="h-64 overflow-hidden relative bg-primary/10">
                        @if($rs->gambar)
                        <img alt="{{ $rs->nama }}"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            src="{{ Storage::url($rs->gambar) }}">
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-6xl text-primary/30">local_hospital</span>
                        </div>
                        @endif
                        <div
                            class="absolute top-4 left-4 bg-surface-container-lowest/90 backdrop-blur-sm px-3 py-1 rounded-full text-label-md font-label-md text-primary flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">local_hospital</span>
                            {{ $rs->lokasi }}
                        </div>
                    </div>
                    <div class="p-6 md:p-8 flex flex-col">
                        <div class="flex flex-col gap-2 mb-3
                                    md:flex-row md:items-start md:justify-between md:gap-3">
                            <h2 class="text-headline-lg-mobile md:text-headline-lg
                                       font-headline-lg-mobile md:font-headline-lg
                                       text-primary font-bold leading-snug">
                                {{ $rs->nama }}
                            </h2>
                            @if($rs->logo)
                            <img alt=""
                                 class="h-8 w-auto object-contain self-start shrink-0 md:mt-0.5"
                                 src="{{ Storage::url($rs->logo) }}"
                                 onerror="this.style.display='none'">
                            @endif
                        </div>
                        <p class="text-body-md font-body-md text-on-surface-variant  grow">
                            {{ $rs->alamat }}
                        </p>
                        @if($rs->no_emergency || $rs->no_hotline)
                        <div class="flex flex-wrap items-center gap-3 text-body-sm font-body-sm text-on-surface-variant mt-5 border-t border-outline-variant/30 pt-4">
                            @if($rs->no_emergency)
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="material-symbols-outlined text-xl p-2 text-white bg-red-600 shrink-0 rounded-lg">emergency</span>
                                <div class="flex flex-col min-w-0">
                                    <span class="font-bold text-red-600 text-xs">EMERGENCY</span>
                                    <span class="text-xs truncate">{{ $rs->no_emergency }}</span>
                                </div>
                            </div>
                            @endif
                            @if($rs->no_hotline)
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="material-symbols-outlined text-xl p-2 text-white bg-green-600 shrink-0 rounded-lg">call</span>
                                <div class="flex flex-col min-w-0">
                                    <span class="font-bold text-green-600 text-xs">HOTLINE</span>
                                    <span class="text-xs truncate">{{ $rs->no_hotline }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif

                        {{-- Pill navigation buttons --}}
                        <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-outline-variant/20">
                            <a href="/{{ $rs->slug }}/dokter-kami"
                               onclick="event.stopPropagation()"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium
                                      bg-surface-container border border-outline-variant/40 text-on-surface-variant
                                      hover:bg-surface-container-high hover:text-on-surface transition-colors">
                                <span class="material-symbols-outlined text-[13px]">stethoscope</span>
                                Dokter Kami
                            </a>
                            <a href="/{{ $rs->slug }}/jadwal-praktek"
                               onclick="event.stopPropagation()"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium
                                      bg-surface-container border border-outline-variant/40 text-on-surface-variant
                                      hover:bg-surface-container-high hover:text-on-surface transition-colors">
                                <span class="material-symbols-outlined text-[13px]">calendar_month</span>
                                Jadwal Praktek
                            </a>
                            @foreach($rs->linkLayanan as $ll)
                            <a href="{{ $ll->link }}" target="_blank" rel="noopener noreferrer"
                               onclick="event.stopPropagation()"
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
        </section>

        <!-- Promo Section -->
        <section id="promo-section"
            class="mt-16 md:mt-24 mb-section-gap bg-secondary rounded-3xl p-8 md:p-10 shadow-[0_20px_50px_rgba(0,108,75,0.15)] border border-secondary-container/20 relative overflow-hidden w-full lg:w-10/12 mx-auto"
            data-aos="fade-up">
            <!-- Background Decorative Blobs -->
            <div
                class="absolute -top-24 -right-24 w-72 h-72 bg-secondary-container/15 rounded-full blur-3xl pointer-events-none">
            </div>
            <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-primary/20 rounded-full blur-3xl pointer-events-none">
            </div>

            <div class="text-center max-w-3xl mx-auto my-10 relative z-10">
                <span
                    class="px-4 py-1.5 bg-secondary/10 text-white border border-white/20 rounded-full text-label-md font-bold tracking-wider uppercase mb-3 inline-block">
                    Promo & Penawaran Spesial
                </span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-white font-montserrat tracking-tight drop-shadow-sm">
                    PROMO KESEHATAN MENARIK</h2>
                <p class="text-white/80 mt-2 max-w-xl mx-auto text-body-md">
                    Temukan penawaran pelayanan kesehatan terbaik dan diskon khusus yang tersedia di RSU Syifa Medika.
                </p>
            </div>

            <!-- Promo Filter -->
            <div
                class="flex flex-col sm:flex-row justify-center items-center gap-4 mb-12 max-w-md mx-auto px-4 relative z-10">
                <label class="text-label-md font-bold text-white/95 whitespace-nowrap">RUMAH SAKIT</label>
                <div class="relative w-full">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-white/60">location_on</span>
                    <select id="promo-hospital-filter"
                        class="w-full pl-10 pr-10 py-3 bg-white/10 border border-white/20 rounded-xl text-body-md font-body-md text-white focus:bg-slate-900 focus:border-white focus:ring-2 focus:ring-white/20 outline-none appearance-none cursor-pointer transition-all shadow-sm hover:bg-white/15">
                        <option class="text-slate-900 bg-white" value="all">Semua Rumah Sakit</option>
                        @foreach($rumahsakit as $rs)
                        <option class="text-slate-900 bg-white" value="{{ $rs->slug }}">{{ $rs->nama }}</option>
                        @endforeach
                    </select>
                    <span
                        class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-white/60 pointer-events-none">expand_more</span>
                </div>
            </div>

            <!-- Promo Grid -->
            <div id="promo-grid"
                class="{{ $promos->isEmpty() ? 'hidden' : 'grid' }} grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 w-full relative z-10 transition-all duration-300">
                @foreach($promos as $p)
                <div class="promo-card group bg-surface-container-lowest rounded-2xl overflow-hidden shadow-[0_10px_25px_-5px_rgba(0,0,0,0.05)] hover:shadow-[0_20px_40px_-10px_rgba(0,0,0,0.1)] border border-outline-variant/30 transition-all duration-300 hover:-translate-y-1.5 flex flex-col"
                    data-hospital="{{ $p->rumahSakit->slug }}" style="transition: all 0.3s ease, opacity 0.3s ease, transform 0.3s ease;">

                    {{-- Gambar --}}
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
                        <h3 class="text-lg font-bold text-on-surface mb-2 line-clamp-2 group-hover:text-secondary transition-colors font-montserrat">
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

            <!-- Empty State (no promos at all, or after filtering) -->
            <div id="promo-empty-state" class="text-center py-16 max-w-md mx-auto px-4 @if($promos->isNotEmpty()) hidden @endif">
                <span class="material-symbols-outlined text-6xl mb-3 block text-white">campaign</span>
                <h3 class="text-xl font-bold text-on-secondary">Tidak ada Promo Aktif</h3>
                <p class="text-body-md text-on-secondary mt-2">
                    Saat ini belum ada promo aktif khusus untuk rumah sakit yang dipilih. Silakan kembali lagi nanti atau
                    hubungi customer service kami.
                </p>
            </div>
        </section>

        <!-- Lightbox Modal -->
        <div id="promo-lightbox"
            class="fixed inset-0 z-100 bg-black/85 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300 backdrop-blur-sm">
            <!-- Close Button -->
            <button id="promo-lightbox-close"
                class="absolute top-6 right-6 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2.5 rounded-full transition-all cursor-pointer shadow-lg">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
            <!-- Modal Box -->
            <div class="relative max-w-4xl max-h-[85vh] w-full flex flex-col items-center transform scale-95 opacity-0 transition-all duration-300"
                id="promo-lightbox-content">
                <img id="promo-lightbox-img" src="" alt="Promo Poster"
                    class="max-h-[70vh] w-auto max-w-full object-contain rounded-xl shadow-2xl border border-white/10">
                <div
                    class="mt-4 text-center text-white px-6 py-3 bg-white/15 backdrop-blur-md rounded-2xl max-w-xl shadow-lg border border-white/5">
                    <h3 id="promo-lightbox-title" class="text-lg font-bold">Judul Promo</h3>
                    <p id="promo-lightbox-desc" class="text-body-sm text-white/85 mt-1">Deskripsi lengkap dari promo yang
                        dipilih.</p>
                </div>
            </div>
        </div>
    </main>

    
    <script src="https://code.jquery.com/jquery-4.0.0.min.js" integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>
    <script src="{{ asset('js/promo-lightbox.js') }}"></script>
    <script>
        $(document).ready(function() {
            
            $('#rsSelect').change(function (e) { 
                e.preventDefault();
                let value = $(this).val()
                $('#spesialisSelect').attr('disabled')
                $.ajax({
                    type: "GET",
                    url: "/cari-spesialis",
                    data: {
                        rs : value
                    },
                    dataType: "json",
                    success: function (response) {
                        // 1. Seleksi elemen select, lalu kosongkan semua option yang ada
                        let dropdown = $('#spesialisSelect');
                        dropdown.empty(); 

                        // 2. Tambahkan kembali option default/placeholder jika diperlukan
                        dropdown.append('<option value="">- Semua Spesialis -</option>');

                        // 3. Looping data dari response AJAX dan masukkan ke dalam select
                        // Asumsi response berbentuk array of objects: [{id: 1, nama: 'Cardiology'}, ...]
                        $.each(response, function(key, value) {
                            dropdown.append('<option value="' + value.slug + '">' + value.nama + '</option>');
                        });

                        dropdown.removeAttr('disabled')
                    }
                });
            });

            $('#searchForm').on('submit', function(e) {
                // 1. Mencegah form melakukan reload/submit bawaan browser
                e.preventDefault();

                // 2. Mengambil value slug RS dan Spesialis
                let rsSlug = $('#rsSelect').val();
                let spesialis = $('#spesialisSelect').val();

                // Validasi jika user belum memilih rumah sakit (jika atribut 'required' di-bypass)
                if (rsSlug == "") {
                    alert('Silakan pilih Rumah Sakit terlebih dahulu.');
                    return;
                }

                // 3. Bangun URL dasar menggunakan slug rumah sakit
                // Contoh keluaran awal: /rumah-sakit-medika
                let targetUrl = '/' + rsSlug + '/dokter-kami'; 

                // 4. Cek jika spesialis dipilih, tambahkan query string ?spesialis=
                if (spesialis) {
                    targetUrl += '?spesialis=' + encodeURIComponent(spesialis.toLowerCase());
                }

                // 5. Alihkan halaman ke URL yang telah dibuat
                window.location.href = targetUrl;
            });
        });
    </script>
@endsection
