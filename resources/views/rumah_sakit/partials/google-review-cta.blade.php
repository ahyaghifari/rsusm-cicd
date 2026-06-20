@if($currentRumahSakit->google_place_id)
<section class="mt-24 min-h-screen" data-aos="fade-up">
    <div class="w-11/12 lg:w-10/12 mx-auto">

        <div class="relative bg-white rounded-3xl border border-outline-variant/30 shadow-sm overflow-hidden">

            {{-- Aksen garis 4 warna Google --}}
            <div class="h-1.5 w-full flex">
                <span class="flex-1 bg-[#4285F4]"></span>
                <span class="flex-1 bg-[#EA4335]"></span>
                <span class="flex-1 bg-[#FBBC05]"></span>
                <span class="flex-1 bg-[#34A853]"></span>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-center gap-8 lg:gap-12 p-8 lg:p-12">

                {{-- Konten kiri --}}
                <div class="flex-1">
                    <div class="inline-flex items-center gap-2 bg-primary/8 text-primary
                                text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full mb-4">
                        <span class="material-symbols-outlined text-[13px]">favorite</span>
                        Ulasan Pasien
                    </div>

                    {{-- Bintang dekoratif --}}
                    <div class="flex items-center gap-1 mb-4">
                        @for ($i = 0; $i < 5; $i++)
                            <span class="material-symbols-outlined text-amber-400 text-[22px] animate-fade-in"
                                  style="animation-delay: {{ $i * 80 }}ms; font-variation-settings: 'FILL' 1;">
                                star
                            </span>
                        @endfor
                    </div>

                    <h2 class="text-2xl md:text-3xl font-bold text-on-surface leading-tight mb-3">
                        Bagaimana Pengalaman Anda <span class="text-primary">Bersama Kami?</span>
                    </h2>
                    <p class="text-on-surface-variant text-sm md:text-base leading-relaxed max-w-lg">
                        Cerita dan masukan Anda membantu kami terus meningkatkan kualitas pelayanan,
                        serta membantu calon pasien lain menemukan perawatan terbaik untuk keluarganya.
                    </p>
                </div>

                {{-- Tombol kanan --}}
                <div class="flex flex-col sm:flex-row lg:flex-col gap-3 shrink-0 w-full lg:w-auto">
                    <a href="{{ $currentRumahSakit->googleWriteReviewUrl() }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-2xl
                              bg-tertiary text-on-tertiary font-bold text-sm
                              border-2 border-white shadow-lg shadow-tertiary/40
                              hover:shadow-xl hover:scale-105 active:scale-95
                              transition-all duration-150 whitespace-nowrap">
                        <svg class="size-[18px] shrink-0" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3c-1.6 4.7-6.1 8-11.3 8-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.1 8 3l5.7-5.7C34.5 6.1 29.5 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.7-.4-3.5z"/>
                            <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 16 19 13 24 13c3.1 0 5.8 1.1 8 3l5.7-5.7C34.5 6.1 29.5 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/>
                            <path fill="#4CAF50" d="M24 44c5.4 0 10.3-2.1 13.9-5.4l-6.4-5.4C29.6 35 26.9 36 24 36c-5.2 0-9.6-3.3-11.3-7.9l-6.6 5.1C9.6 39.6 16.3 44 24 44z"/>
                            <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.2 4.2-4.1 5.6l6.4 5.4C41.5 35.6 44 30.2 44 24c0-1.3-.1-2.7-.4-3.5z"/>
                        </svg>
                        Tulis Ulasan Anda
                    </a>
                    <a href="{{ $currentRumahSakit->googleReviewsUrl() }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl
                              border-2 border-primary text-primary font-semibold text-sm
                              hover:bg-primary hover:text-white transition-all duration-200 whitespace-nowrap">
                        Lihat Ulasan Lainnya
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>

            </div>
        </div>

    </div>
</section>
@endif
