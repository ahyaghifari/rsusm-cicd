<div>
    <x-page-hero
        title="Fasilitas Pendukung"
        subtitle="Berbagai fasilitas lengkap kami hadirkan untuk mendukung kenyamanan dan kemudahan selama kunjungan Anda."
    />

    {{-- ============================================================ --}}
    {{-- KONTEN UTAMA --}}
    {{-- ============================================================ --}}
    <div class="w-10/12 mx-auto py-16">

        @if($fasilitas->isEmpty())
            <div class="text-center py-24">
                <span class="material-symbols-outlined text-7xl text-outline/40 block mb-4">apartment</span>
                <p class="text-on-surface-variant text-lg">Belum ada fasilitas pendukung tersedia.</p>
            </div>

        @else
            @php
                $featured = $fasilitas->first();
                $rest     = $fasilitas->skip(1);
            @endphp

            {{-- Featured card (item pertama, full-width horizontal) --}}
            <div
                data-aos="fade-up"
                class="group grid grid-cols-1 md:grid-cols-2 rounded-2xl overflow-hidden shadow-sm
                       border border-outline-variant/30 hover:shadow-xl transition-all duration-300
                       {{ $rest->isNotEmpty() ? 'mb-10' : '' }}">

                {{-- Gambar kiri --}}
                <a href="{{ Storage::url($featured->gambar) }}"
                   class="glightbox relative overflow-hidden min-h-72 block"
                   data-gallery="fasilitas"
                   data-title="{{ $featured->nama }}">
                    <img
                        src="{{ Storage::url($featured->gambar) }}"
                        alt="{{ $featured->nama }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-linear-to-r from-black/30 to-transparent"></div>
                    <span class="absolute top-4 left-4 inline-flex items-center gap-1.5 bg-white/90 backdrop-blur-sm
                                 text-primary text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full shadow">
                        <span class="material-symbols-outlined text-[14px]">apartment</span>
                        Fasilitas
                    </span>
                    <span class="absolute bottom-4 right-4 material-symbols-outlined text-white text-3xl opacity-0 group-hover:opacity-100 transition drop-shadow">zoom_in</span>
                </a>

                {{-- Konten kanan --}}
                <div class="bg-white p-8 md:p-10 flex flex-col justify-center">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-1.5 h-10 bg-primary rounded-full inline-block shrink-0"></span>
                        <h3 class="text-2xl font-bold text-on-surface group-hover:text-primary transition-colors duration-200 leading-snug">
                            {{ $featured->nama }}
                        </h3>
                    </div>
                    <div class="text-sm text-on-surface-variant leading-relaxed pl-5 border-l border-outline-variant/30">
                        {!! str($featured->deskripsi)->sanitizeHtml() !!}
                    </div>
                
                </div>
            </div>

            {{-- Grid 3 kolom untuk item sisanya --}}
            @if($rest->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($rest as $f)
                        <div
                            data-aos="fade-up"
                            data-aos-delay="{{ $loop->index * 80 }}"
                            class="group bg-white rounded-2xl overflow-hidden shadow-sm
                                   border border-outline-variant/20 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">

                            {{-- Gambar --}}
                            <a href="{{ Storage::url($f->gambar) }}"
                               class="glightbox relative overflow-hidden h-52 block"
                               data-gallery="fasilitas"
                               data-title="{{ $f->nama }}">
                                <img
                                    src="{{ Storage::url($f->gambar) }}"
                                    alt="{{ $f->nama }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute inset-0 bg-linear-to-t from-black/40 to-transparent"></div>
                                <span class="absolute bottom-3 right-3 material-symbols-outlined text-white text-2xl opacity-0 group-hover:opacity-100 transition drop-shadow">zoom_in</span>
                            </a>

                            {{-- Konten --}}
                            <div class="p-6">
                                <h3 class="text-lg font-bold text-on-surface group-hover:text-primary transition-colors duration-200 mb-3 leading-snug">
                                    {{ $f->nama }}
                                </h3>
                                <div class="text-sm text-on-surface-variant leading-relaxed line-clamp-4">
                                    {!! str($f->deskripsi)->sanitizeHtml() !!}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        @endif

    </div>
    {{-- END KONTEN UTAMA --}}
</div>
