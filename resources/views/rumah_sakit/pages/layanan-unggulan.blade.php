<div>
    <x-page-hero title="Layanan Unggulan" />

    <div class="w-11/12 lg:w-10/12 mx-auto py-12 flex flex-col gap-10">

        @foreach($data as $layanan)
        <div
            data-aos="{{ $loop->odd ? 'fade-right' : 'fade-left' }}"
            data-aos-delay="{{ $loop->index * 60 }}"
            class="group bg-white rounded-2xl overflow-hidden shadow-sm border border-outline-variant/20
                   hover:shadow-xl transition-all duration-300
                   grid grid-cols-1 md:grid-cols-2"
        >

            {{-- Gambar — kiri untuk ganjil, kanan untuk genap --}}
            <a href="{{ Storage::url($layanan->gambar) }}"
               class="glightbox relative overflow-hidden block min-h-64 md:min-h-72
                      {{ $loop->even ? 'md:order-2' : '' }}"
               data-gallery="layanan-unggulan">

                <img src="{{ Storage::url($layanan->gambar) }}"
                     alt="{{ $layanan->nama }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 absolute inset-0">

                {{-- Overlay zoom --}}
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition flex items-center justify-center">
                    <span class="material-symbols-outlined text-white text-4xl drop-shadow
                                 opacity-0 scale-75 group-hover:opacity-100 group-hover:scale-100
                                 transition-all duration-300">zoom_in</span>
                </div>

                {{-- Badge nomor urut --}}
                <div class="absolute top-3 {{ $loop->odd ? 'right-3' : 'left-3' }}">
                    <span class="inline-flex items-center justify-center w-8 h-8
                                 bg-primary text-white text-sm font-bold rounded-full shadow">
                        {{ $loop->iteration }}
                    </span>
                </div>
            </a>

            {{-- Konten teks --}}
            <div class="p-6 md:p-8 flex flex-col justify-center
                        {{ $loop->even ? 'md:order-1' : '' }}">

                <div class="flex items-center gap-3 mb-4">
                    <span class="w-1 h-8 rounded-full bg-primary shrink-0"></span>
                    <h2 class="text-xl md:text-2xl font-bold text-on-surface leading-snug">
                        {{ $layanan->nama }}
                    </h2>
                </div>

                <div class="text-sm md:text-base text-on-surface-variant leading-relaxed
                            prose prose-sm max-w-none">
                    {!! str($layanan->deskripsi)->sanitizeHtml() !!}
                </div>
            </div>

        </div>
        @endforeach

        @if($data->isEmpty())
            <div class="text-center py-24">
                <span class="material-symbols-outlined text-7xl text-outline/40 block mb-4">star_border</span>
                <p class="text-on-surface-variant">Belum ada layanan unggulan tersedia.</p>
            </div>
        @endif

    </div>
</div>
