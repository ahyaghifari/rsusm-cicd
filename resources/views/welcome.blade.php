@extends('layouts.portal-layout')

@section('title', 'RSU SYIFA MEDIKA - Portal Rumah Sakit')

@section('content')
    <!-- Main Content -->
    <main class="grow w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-section-gap">

        <h1 class="text-center text-3xl text-primary mb-8 font-montserrat font-bold">Selamat Datang di, <br> <span
                class="text-white font-bold mt-2 px-2 bg-tertiary">RUMAH SAKIT UMUM SYIFA MEDIKA</span></h1>

        <!-- Search & Filter Section -->
        <section class="mb-section-gap text-center max-w-4xl mx-auto shadow rounded">
            <div
                class="bg-inverse-primary p-6 rounded-xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.05)] border border-outline-variant/30 flex flex-col md:flex-row gap-4 items-end">
                <div class="w-full md:w-2/5 flex flex-col items-start">
                    <label class="text-label-md font-label-md text-on-surface-variant mb-2">RUMAH SAKIT</label>
                    <div class="relative w-full">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">location_on</span>
                        <select
                            class="w-full pl-10 pr-4 py-3 bg-surface border border-outline-variant rounded-lg text-body-md font-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-none appearance-none cursor-pointer">
                            <option>- Pilih Rumah Sakit -</option>
                            <option>RSU SYIFA MEDIKA BANJARBARU</option>
                            <option>RSU SYIFA MEDIKA BARABAI</option>
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
                            class="w-full pl-10 pr-4 py-3 bg-surface border border-outline-variant rounded-lg text-body-md font-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-none appearance-none cursor-pointer">
                            <option>- Semua Spesialis -</option>
                            <option>Cardiology</option>
                            <option>Neurology</option>
                            <option>Orthopedics</option>
                            <option>Pediatrics</option>
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
        </section>

        <h2 class="text-center text-2xl text-primary mb-8" data-aos="fade-up">Kunjungi rumah sakit terdekat</h2>

        <!-- Hospital Cards Section -->
        <section class="grid grid-cols-1 md:grid-cols-2 gap-10 w-11/12 lg:w-10/12 mx-auto">
            <!-- Card 1: MediGroup Pusat -->
            <div class="group bg-surface-container-lowest rounded-2xl overflow-hidden shadow-[0_10px_25px_-5px_rgba(0,0,0,0.05)] hover:shadow-[0_20px_40px_-10px_rgba(0,0,0,0.1)] transition-all duration-300 hover:-translate-y-1 flex flex-col border border-outline-variant/20"
                data-aos="fade-up">
                <div class="h-64 overflow-hidden relative">
                    <img alt="RSU SYIFA MEDIKA BANJARBARU"
                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                        src="{{ asset('img/syifa-medika.webp') }}">
                    <div
                        class="absolute top-4 left-4 bg-surface-container-lowest/90 backdrop-blur-sm px-3 py-1 rounded-full text-label-md font-label-md text-primary flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">local_hospital</span>
                        BANJARBARU
                    </div>
                </div>
                <div class="p-8 flex flex-col flex-grow">
                    <div class="flex items-center justify-between">
                        <h2
                            class="text-headline-lg-mobile md:text-headline-lg font-headline-lg-mobile md:font-headline-lg text-on-surface mb-3 text-primary font-bold">
                            RSU SYIFA MEDIKA BANJARBARU</h2>
                        <img alt="RSU SYIFA MEDIKA BANJARBARU" class="h-8"
                            src="{{ asset('img/syifa-medika-banjarbaru.png') }}">
                    </div>
                    <p class="text-body-md font-body-md text-on-surface-variant  flex-grow">
                        Jl. RO Ulin No.93, Loktabat Selatan, Kec. Banjarbaru Selatan, Kota Banjar Baru, Kalimantan Selatan
                        70712
                    </p>
                    <div
                        class="flex items-center gap-6 text-body-sm font-body-sm text-on-surface-variant mb-8 mt-5 border-t border-outline-variant/30 pt-4">
                        <div class="flex items-center gap-2"><span
                                class="material-symbols-outlined text-xl p-2 text-white bg-red-600">emergency</span>
                            <div class="flex flex-col">
                                <span class="font-bold text-red-600">EMERGENCY</span>
                                <span class="">0811 504 2424</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2"><span
                                class="material-symbols-outlined text-xl p-2 text-white bg-green-600">call</span>
                            <div class="flex flex-col">
                                <span class="font-bold text-green-600">HOTLINE</span>
                                <span class="">0511 5910 889</span>
                            </div>
                        </div>
                    </div>
                    <!-- <button
                            class="w-full sm:w-auto self-start border-2 border-secondary bg-secondary  text-on-secondary hover:bg-primary px-6 py-3 rounded-lg text-label-md font-label-md transition-colors flex items-center gap-2">
                            Lihat Detail
                            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </button> -->
                </div>
            </div>
            <!-- Card 1: MediGroup Pusat -->
            <div class="group bg-surface-container-lowest rounded-2xl overflow-hidden shadow-[0_10px_25px_-5px_rgba(0,0,0,0.05)] hover:shadow-[0_20px_40px_-10px_rgba(0,0,0,0.1)] transition-all duration-300 hover:-translate-y-1 flex flex-col border border-outline-variant/20"
                data-aos="fade-up">
                <div class="h-64 overflow-hidden relative">
                    <img alt="RSU SYIFA MEDIKA BARABAI"
                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                        src="{{ asset('img/syifa-medika.webp') }}">
                    <div
                        class="absolute top-4 left-4 bg-surface-container-lowest/90 backdrop-blur-sm px-3 py-1 rounded-full text-label-md font-label-md text-primary flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">local_hospital</span>
                        BARABAI
                    </div>
                </div>
                <div class="p-8 flex flex-col flex-grow">
                    <div class="flex items-center justify-between">
                        <h2
                            class="text-headline-lg-mobile md:text-headline-lg font-headline-lg-mobile md:font-headline-lg text-on-surface mb-3 text-secondary font-bold">
                            RSU SYIFA MEDIKA BARABAI</h2>
                        <img alt="RSU SYIFA MEDIKA BANJARBARU" class="h-16"
                            src="{{ asset('img/syifa-medika-barabai.png') }}">
                    </div>
                    <p class="text-body-md font-body-md text-on-surface-variant  flex-grow">
                        Jl Lingkar Walangsi Kapar KM. 5.2, Barabai, Kalimantan Selatan, Indonesia
                    </p>
                    <div
                        class="flex items-center gap-6 text-body-sm font-body-sm text-on-surface-variant mb-8 mt-5 border-t border-outline-variant/30 pt-4">
                        <div class="flex items-center gap-2"><span
                                class="material-symbols-outlined text-xl p-2 text-white bg-red-600">emergency</span>
                            <div class="flex flex-col">
                                <span class="font-bold text-red-600">EMERGENCY</span>
                                <span class="">-</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2"><span
                                class="material-symbols-outlined text-xl p-2 text-white bg-green-600">call</span>
                            <div class="flex flex-col">
                                <span class="font-bold text-green-600">HOTLINE</span>
                                <span class="">-</span>
                            </div>
                        </div>
                    </div>
                    <!-- <button
                            class="w-full sm:w-auto self-start border-2 border-secondary bg-secondary  text-on-secondary hover:bg-primary px-6 py-3 rounded-lg text-label-md font-label-md transition-colors flex items-center gap-2">
                            Lihat Detail
                            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </button> -->
                </div>
            </div>


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
                        <option class="text-slate-900 bg-white" value="banjarbaru">RSU SYIFA MEDIKA BANJARBARU</option>
                        <option class="text-slate-900 bg-white" value="barabai">RSU SYIFA MEDIKA BARABAI</option>
                    </select>
                    <span
                        class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-white/60 pointer-events-none">expand_more</span>
                </div>
            </div>

            <!-- Promo Grid -->
            <div id="promo-grid"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 w-full relative z-10 transition-all duration-300">
                <!-- Promo Card 1 (Banjarbaru) -->
                <div class="promo-card group bg-surface-container-lowest rounded-2xl overflow-hidden shadow-[0_10px_25px_-5px_rgba(0,0,0,0.05)] hover:shadow-[0_20px_40px_-10px_rgba(0,0,0,0.1)] border border-outline-variant/30 transition-all duration-300 hover:-translate-y-1.5 flex flex-col"
                    data-hospital="banjarbaru" style="transition: all 0.3s ease, opacity 0.3s ease, transform 0.3s ease;">
                    <div class="h-56 overflow-hidden relative cursor-pointer promo-img-wrapper">
                        <img alt="Promo MCU Platinum"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=600&q=80">
                        <div
                            class="absolute top-4 left-4 bg-primary text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-md tracking-wider">
                            BANJARBARU
                        </div>
                        <!-- Hover Overlay -->
                        <div
                            class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-[2px]">
                            <span
                                class="material-symbols-outlined text-white text-4xl bg-primary-container/85 p-3 rounded-full shadow-lg transform scale-75 group-hover:scale-100 transition-transform duration-300">zoom_in</span>
                        </div>
                    </div>
                    <div class="p-6 flex flex-col flex-grow">
                        <span class="text-xs font-bold text-secondary mb-1">PAKET PEMERIKSAAN</span>
                        <h3
                            class="text-lg font-bold text-on-surface mb-2 line-clamp-2 group-hover:text-secondary transition-colors font-montserrat">
                            Paket Medical Check Up Platinum
                        </h3>
                        <p class="text-body-sm text-on-surface-variant mb-4 line-clamp-3 flex-grow">
                            Nikmati pemeriksaan kesehatan lengkap dan menyeluruh dengan layanan dokter spesialis
                            berpengalaman. Diskon 15% khusus pemesanan bulan ini.
                        </p>
                        <div class="border-t border-outline-variant/30 pt-4 flex justify-between items-center mt-auto">
                            <span class="text-xs text-outline font-medium">Berlaku s.d. 30 Juni 2026</span>
                            <button
                                class="promo-view-btn text-label-md font-bold text-secondary hover:text-secondary-container flex items-center gap-1 transition-colors cursor-pointer">
                                Lihat Poster
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Promo Card 3 (Banjarbaru) -->
                <div class="promo-card group bg-surface-container-lowest rounded-2xl overflow-hidden shadow-[0_10px_25px_-5px_rgba(0,0,0,0.05)] hover:shadow-[0_20px_40px_-10px_rgba(0,0,0,0.1)] border border-outline-variant/30 transition-all duration-300 hover:-translate-y-1.5 flex flex-col"
                    data-hospital="banjarbaru" style="transition: all 0.3s ease, opacity 0.3s ease, transform 0.3s ease;">
                    <div class="h-56 overflow-hidden relative cursor-pointer promo-img-wrapper">
                        <img alt="Promo Kesehatan Jantung"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            src="https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&w=600&q=80">
                        <div
                            class="absolute top-4 left-4 bg-secondary text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-md tracking-wider">
                            BANJARBARU
                        </div>
                        <!-- Hover Overlay -->
                        <div
                            class="absolute inset-0 bg-secondary/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-[2px]">
                            <span
                                class="material-symbols-outlined text-white text-4xl bg-secondary-container/85 p-3 rounded-full shadow-lg transform scale-75 group-hover:scale-100 transition-transform duration-300">zoom_in</span>
                        </div>
                    </div>
                    <div class="p-6 flex flex-col flex-grow">
                        <span class="text-xs font-bold text-secondary mb-1">PAKET JANTUNG</span>
                        <h3
                            class="text-lg font-bold text-on-surface mb-2 line-clamp-2 group-hover:text-secondary transition-colors font-montserrat">
                            Paket Screening Jantung Sehat
                        </h3>
                        <p class="text-body-sm text-on-surface-variant mb-4 line-clamp-3 flex-grow">
                            Deteksi dini masalah jantung Anda dengan Paket Jantung Sehat. Meliputi EKG, Konsultasi Dokter
                            Spesialis Jantung, dan pemeriksaan laboratorium penunjang.
                        </p>
                        <div class="border-t border-outline-variant/30 pt-4 flex justify-between items-center mt-auto">
                            <span class="text-xs text-outline font-medium">Berlaku s.d. 31 Juli 2026</span>
                            <button
                                class="promo-view-btn text-label-md font-bold text-secondary hover:text-secondary-container flex items-center gap-1 transition-colors cursor-pointer">
                                Lihat Poster
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State for filtered promos -->
            <div id="promo-empty-state" class="hidden text-center py-16 max-w-md mx-auto px-4">
                <span class="material-symbols-outlined text-outline text-6xl mb-3 block text-white">campaign</span>
                <h3 class="text-xl font-bold text-on-secondary">Tidak ada Promo Aktif</h3>
                <p class="text-body-md text-on-secondary mt-2">
                    Saat ini belum ada promo aktif khusus untuk rumah sakit yang dipilih. Silakan kembali lagi nanti atau
                    hubungi customer service kami.
                </p>
            </div>
        </section>

        <!-- Lightbox Modal -->
        <div id="promo-lightbox"
            class="fixed inset-0 z-[100] bg-black/85 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300 backdrop-blur-sm">
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

    <script src="{{ asset('js/promo-lightbox.js') }}"></script>
@endsection
