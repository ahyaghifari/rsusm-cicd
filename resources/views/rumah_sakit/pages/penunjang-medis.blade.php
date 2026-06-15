<div>
    <x-page-hero
        title="Penunjang Medis"
        subtitle="Layanan penunjang diagnostik dan medis berkualitas tinggi untuk mendukung proses perawatan Anda."
    />

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
                            <a href="{{ Storage::url($medis->gambar) }}"
                               class="glightbox relative overflow-hidden min-h-64 block"
                               data-gallery="penunjang-medis">
                                <img
                                    src="{{ Storage::url($medis->gambar) }}"
                                    alt="{{ $medis->nama }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    loading="lazy">
                                <div class="absolute inset-0 bg-linear-to-r from-black/20 to-transparent"></div>
                                <div class="absolute bottom-4 left-4">
                                    <span class="inline-flex items-center gap-1.5 bg-white/90 backdrop-blur-sm text-primary
                                                 text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full shadow">
                                        <span class="material-symbols-outlined text-[14px]">medical_services</span>
                                        Layanan
                                    </span>
                                </div>
                                <span class="absolute top-4 right-4 material-symbols-outlined text-white text-3xl opacity-0 group-hover:opacity-100 transition drop-shadow">zoom_in</span>
                            </a>

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
                            <a href="{{ Storage::url($medis->gambar) }}"
                               class="glightbox relative overflow-hidden min-h-64 block order-1 md:order-2"
                               data-gallery="penunjang-medis">
                                <img
                                    src="{{ Storage::url($medis->gambar) }}"
                                    alt="{{ $medis->nama }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    loading="lazy">
                                <div class="absolute inset-0 bg-linear-to-l from-black/20 to-transparent"></div>
                                <div class="absolute bottom-4 right-4">
                                    <span class="inline-flex items-center gap-1.5 bg-white/90 backdrop-blur-sm text-primary
                                                 text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full shadow">
                                        <span class="material-symbols-outlined text-[14px]">medical_services</span>
                                        Layanan
                                    </span>
                                </div>
                                <span class="absolute top-4 left-4 material-symbols-outlined text-white text-3xl opacity-0 group-hover:opacity-100 transition drop-shadow">zoom_in</span>
                            </a>
                        @endif

                    </div>
                @endforeach
            </div>
        @endif

    </div>
    {{-- END KONTEN UTAMA --}}
</div>

