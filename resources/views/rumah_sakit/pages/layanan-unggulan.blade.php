<div>
    <x-page-hero title="Layanan Unggulan" />

    <div class="flex flex-col gap-12 w-10/12 mx-auto py-16">
        @foreach($data as $layanan)
            <div class="">
                <h2 class="text-3xl font-bold text-primary">{{ $layanan->nama }}</h2>
                <div class="grid grid-cols-4 gap-5 mt-5">
                    <a href="{{ Storage::url($layanan->gambar) }}"
                       class="glightbox block overflow-hidden rounded-xl relative group"
                       data-gallery="layanan-unggulan">
                        <img src="{{ Storage::url($layanan->gambar) }}" alt="{{ $layanan->nama }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition flex items-center justify-center">
                            <span class="material-symbols-outlined text-white text-3xl opacity-0 group-hover:opacity-100 transition drop-shadow">zoom_in</span>
                        </div>
                    </a>
                    <div class="col-span-3 bg-white p-5 rounded-xl text-on-surface shadow">
                        <p>{!! str($layanan->deskripsi)->sanitizeHtml() !!}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

