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
            <span class="material-symbols-outlined text-5xl text-white/60 block mb-3">lab_panel</span>
            <h1 class="text-4xl md:text-5xl font-bold text-white leading-tight mb-4">Penunjang Medis</h1>
            <p class="text-white/65 text-base leading-relaxed">
                Layanan penunjang diagnostik dan medis berkualitas tinggi untuk mendukung proses perawatan Anda.
            </p>
        </div>
    </div>
    {{-- END HERO --}}

    {{-- ============================================================ --}}
    {{-- KONTEN UTAMA --}}
    {{-- ============================================================ --}}
    <div class="w-10/12 mx-auto py-16">

        @if($data->isEmpty())
            <div class="text-center py-24">
                <span class="material-symbols-outlined text-7xl text-outline/40 block mb-4">lab_panel</span>
                <p class="text-on-surface-variant text-lg">Belum ada layanan penunjang medis tersedia.</p>
            </div>

        @else
            <div class="flex flex-col gap-16">
                @foreach($data as $medis)
                    <div
                        data-aos="{{ $loop->odd ? 'fade-right' : 'fade-left' }}"
                        data-aos-delay="{{ $loop->index * 80 }}"
                        class="group grid grid-cols-1 md:grid-cols-2 rounded-2xl overflow-hidden shadow-sm
                               border border-outline-variant/30 hover:shadow-xl transition-all duration-300">

                        @if($loop->odd)
                            {{-- Gambar kiri --}}
                            <div class="relative overflow-hidden min-h-64">
                                <img
                                    src="{{ Storage::url($medis->gambar) }}"
                                    alt="{{ $medis->nama }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute inset-0 bg-linear-to-r from-black/20 to-transparent"></div>
                                <div class="absolute bottom-4 left-4">
                                    <span class="inline-flex items-center gap-1.5 bg-white/90 backdrop-blur-sm text-primary
                                                 text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full shadow">
                                        <span class="material-symbols-outlined text-[14px]">medical_services</span>
                                        Layanan
                                    </span>
                                </div>
                            </div>

                            {{-- Teks kanan --}}
                            <div class="bg-white p-8 flex flex-col justify-center">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="w-1.5 h-10 bg-primary rounded-full inline-block shrink-0"></span>
                                    <h3 class="text-2xl font-bold text-on-surface group-hover:text-primary transition-colors duration-200 leading-snug">
                                        {{ $medis->nama }}
                                    </h3>
                                </div>
                                <div class="text-sm text-on-surface-variant leading-relaxed pl-5 border-l border-outline-variant/30">
                                    {!! str($medis->deskripsi)->sanitizeHtml() !!}
                                </div>
                                <div class="mt-6 pl-5 flex items-center gap-2 text-xs text-on-surface-variant/60">
                                    <span class="material-symbols-outlined text-[14px] text-primary">verified</span>
                                    Tersedia di rumah sakit kami
                                </div>
                            </div>

                        @else
                            {{-- Teks kiri --}}
                            <div class="bg-surface-container p-8 flex flex-col justify-center order-2 md:order-1">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="w-1.5 h-10 bg-primary rounded-full inline-block shrink-0"></span>
                                    <h3 class="text-2xl font-bold text-on-surface group-hover:text-primary transition-colors duration-200 leading-snug">
                                        {{ $medis->nama }}
                                    </h3>
                                </div>
                                <div class="text-sm text-on-surface-variant leading-relaxed pl-5 border-l border-outline-variant/30">
                                    {!! str($medis->deskripsi)->sanitizeHtml() !!}
                                </div>
                                <div class="mt-6 pl-5 flex items-center gap-2 text-xs text-on-surface-variant/60">
                                    <span class="material-symbols-outlined text-[14px] text-primary">verified</span>
                                    Tersedia di rumah sakit kami
                                </div>
                            </div>

                            {{-- Gambar kanan --}}
                            <div class="relative overflow-hidden min-h-64 order-1 md:order-2">
                                <img
                                    src="{{ Storage::url($medis->gambar) }}"
                                    alt="{{ $medis->nama }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute inset-0 bg-linear-to-l from-black/20 to-transparent"></div>
                                <div class="absolute bottom-4 right-4">
                                    <span class="inline-flex items-center gap-1.5 bg-white/90 backdrop-blur-sm text-primary
                                                 text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full shadow">
                                        <span class="material-symbols-outlined text-[14px]">medical_services</span>
                                        Layanan
                                    </span>
                                </div>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        @endif

    </div>
    {{-- END KONTEN UTAMA --}}
</div>
