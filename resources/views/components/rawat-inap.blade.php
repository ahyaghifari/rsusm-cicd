@php $galeri = $kamarInap->gambar ?? collect(); @endphp

<div class="rounded-2xl overflow-hidden shadow-sm group">

    {{-- Gambar utama (bagian dari gallery lightbox room ini) --}}
    <a href="{{ Storage::url($kamarInap->thumbnail) }}"
       class="glightbox h-64 w-full overflow-hidden relative block"
       data-gallery="rawat-inap-{{ $kamarInap->id }}"
       data-title="{{ $kamarInap->nama }} — {{ $kamarInap->kelas }}">
        <img src="{{ Storage::url($kamarInap->thumbnail) }}"
             class="h-full w-full object-cover group-hover:scale-110 transition-all ease-in-out"
             alt="{{ $kamarInap->nama }}">
        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition flex items-center justify-center">
            <span class="material-symbols-outlined text-white text-4xl opacity-0 group-hover:opacity-100 transition drop-shadow">zoom_in</span>
        </div>
        {{-- Badge jumlah foto jika ada galeri --}}
        @if($galeri->isNotEmpty())
            <span class="absolute bottom-3 right-3 inline-flex items-center gap-1
                         bg-black/50 backdrop-blur-sm text-white text-xs font-semibold
                         px-2 py-1 rounded-full pointer-events-none">
                <span class="material-symbols-outlined text-[13px]">photo_library</span>
                {{ $galeri->count() + 1 }} foto
            </span>
        @endif
    </a>

    {{-- Hidden links galeri agar masuk ke lightbox group yang sama --}}
    @foreach($galeri as $g)
        <a href="{{ Storage::url($g->gambar) }}"
           class="glightbox hidden"
           data-gallery="rawat-inap-{{ $kamarInap->id }}"
           data-title="{{ $g->deskripsi ?? $kamarInap->nama }}"></a>
    @endforeach

    {{-- Carousel thumbnail galeri (jika ada) --}}
    @if($galeri->isNotEmpty())
        <div class="px-3 pt-3 pb-1">
            <div data-hs-carousel='{
                     "loadingClasses": "opacity-0",
                     "slidesQty": { "xs": 3, "sm": 4 },
                     "isAutoPlay": false,
                     "isInfiniteLoop": false
                 }'
                 class="relative">

                <div class="hs-carousel overflow-hidden rounded-lg">
                    <div class="hs-carousel-body flex gap-2 transition-transform duration-300 opacity-0">
                        @foreach($galeri as $i => $g)
                            <div class="hs-carousel-slide">
                                <a href="{{ Storage::url($g->gambar) }}"
                                   class="glightbox block relative overflow-hidden rounded-lg aspect-square group/thumb"
                                   data-gallery="rawat-inap-{{ $kamarInap->id }}"
                                   data-title="{{ $g->deskripsi ?? $kamarInap->nama }}">
                                    <img src="{{ Storage::url($g->gambar) }}"
                                         alt="{{ $g->deskripsi ?? $kamarInap->nama }}"
                                         class="w-full h-full object-cover group-hover/thumb:scale-110 transition-transform duration-300">
                                    <div class="absolute inset-0 bg-black/0 group-hover/thumb:bg-black/30 transition flex items-center justify-center">
                                        <span class="material-symbols-outlined text-white text-lg opacity-0 group-hover/thumb:opacity-100 transition">zoom_in</span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Prev/Next hanya muncul jika slide lebih dari 4 --}}
                @if($galeri->count() > 4)
                    <button type="button"
                            class="hs-carousel-prev hs-carousel-disabled:opacity-20 hs-carousel-disabled:cursor-not-allowed
                                   absolute -left-3 top-1/2 -translate-y-1/2 z-10
                                   w-7 h-7 rounded-full bg-white shadow border border-outline-variant/30
                                   flex items-center justify-center text-primary hover:bg-primary hover:text-white transition">
                        <span class="material-symbols-outlined text-[16px]">chevron_left</span>
                    </button>
                    <button type="button"
                            class="hs-carousel-next hs-carousel-disabled:opacity-20 hs-carousel-disabled:cursor-not-allowed
                                   absolute -right-3 top-1/2 -translate-y-1/2 z-10
                                   w-7 h-7 rounded-full bg-white shadow border border-outline-variant/30
                                   flex items-center justify-center text-primary hover:bg-primary hover:text-white transition">
                        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    </button>
                @endif
            </div>
        </div>
    @endif

    {{-- Info kamar --}}
    <div class="p-3">
        <h4 class="font-extrabold text-xl text-primary">{{ $kamarInap->nama }}</h4>
        <div class="mt-5 flex justify-between items-center">
            @if(in_array($kamarInap->kelas, ['VIP', 'VVIP', 'VIP Plus']))
                <p class="bg-linear-to-r from-yellow-500 to-amber-500 p-3 rounded-xl text-xs text-white font-semibold shadow">{{ $kamarInap->kelas }}</p>
            @else
                <p class="text-sm text-gray-600">Untuk <span class="font-bold text-secondary">{{ $kamarInap->kapasitas }} Pasien</span></p>
            @endif
            <p class="px-2 py-1 text-white bg-secondary text-sm shadow-lg">
                Rp. {{ number_format($kamarInap->harga, 0, ',', '.') }}<span class="text-xs">/malam</span>
            </p>
        </div>
        <hr class="my-5 border-gray-200">
        <div class="mt-5 grid grid-cols-2 gap-2">
            @foreach($kamarInap->fasilitasRawatInap as $f)
                <div class="flex items-center text-primary text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" class="size-5" width="24px" fill="currentColor">
                        <path d="m424-408-86-86q-11-11-28-11t-28 11q-11 11-11 28t11 28l114 114q12 12 28 12t28-12l226-226q11-11 11-28t-11-28q-11-11-28-11t-28 11L424-408Zm56 328q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
                    </svg>
                    <span class="ml-2 text-on-surface">{{ $f->nama }}</span>
                </div>
            @endforeach
        </div>
    </div>

</div>
