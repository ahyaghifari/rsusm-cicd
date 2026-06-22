@php
    $galeri      = $kamarInap->gambar ?? collect();
    $namaKelas   = $kamarInap->kelasRawatInap?->nama;
    $isVip       = (bool) $kamarInap->kelasRawatInap?->is_vip;
@endphp

<div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-outline-variant/15
            hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group flex flex-col">

    {{-- Gambar utama --}}
    <div class="relative overflow-hidden h-56 shrink-0">

        <a href="{{ Storage::url($kamarInap->thumbnail) }}"
           class="glightbox absolute inset-0 block"
           data-gallery="rawat-inap-{{ $kamarInap->id }}">

            <img src="{{ Storage::url($kamarInap->thumbnail) }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                 alt="{{ $kamarInap->nama }}" loading="lazy">

            <div class="absolute inset-0 flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-4xl drop-shadow-lg
                             opacity-0 scale-75 group-hover:opacity-100 group-hover:scale-100
                             transition-all duration-300">zoom_in</span>
            </div>
        </a>

        {{-- Badge kelas (bottom left) --}}
        @if($namaKelas)
        <div class="absolute bottom-3 left-3 z-10 pointer-events-none">
            @if($isVip)
                <span class="inline-flex items-center gap-1 bg-amber-400 text-amber-900
                             text-[11px] font-bold px-2.5 py-1 rounded-full shadow">
                    <span class="material-symbols-outlined text-[11px]"
                          style="font-variation-settings:'FILL' 1">star</span>
                    {{ $namaKelas }}
                </span>
            @else
                <span class="inline-flex items-center gap-1 bg-white/90 backdrop-blur-sm text-primary
                             text-[11px] font-bold px-2.5 py-1 rounded-full shadow">
                    <span class="material-symbols-outlined text-[11px]">hotel</span>
                    {{ $namaKelas }}
                </span>
            @endif
        </div>
        @endif

        {{-- Badge jumlah foto (top right) --}}
        @if($galeri->isNotEmpty())
            <span class="absolute top-3 right-3 z-10 inline-flex items-center gap-1
                         bg-black/50 backdrop-blur-sm text-white text-[11px] font-semibold
                         px-2 py-1 rounded-full pointer-events-none">
                <span class="material-symbols-outlined text-[12px]">photo_library</span>
                {{ $galeri->count() + 1 }}
            </span>
        @endif

        {{-- Preview 360° — badge interaktif (beda dari badge info di atas, ini benar-benar
             bisa diklik), diletakkan di pojok yang masih kosong (bottom-right), berpasangan
             dengan badge kelas di bottom-left supaya "info row" bawah foto konsisten: kiri =
             apa kamarnya, kanan = cara lain melihatnya. Selalu tampil kalau foto_360 ada,
             tidak digabung ke carousel galeri supaya tetap terlihat walau galeri foto
             biasanya masih kosong (progres foto ulang per kamar belum selesai semua).

             Trigger-nya saja yang ditaruh di sini — modal-nya sendiri dirender di
             rawat-inap.blade.php (level halaman, di luar kartu ini), karena kartu kamar
             punya `hover:-translate-y-1` (CSS transform). Ancestor dengan transform jadi
             containing block untuk descendant `position: fixed`, jadi kalau modal-nya
             ditaruh di sini, dia "fixed" relatif ke kartu yang sedang ter-hover itu (bukan
             ke viewport) — kelihatan seperti kejepit/render di dalam kartu. --}}
        @if($kamarInap->foto_360)
            <button type="button" onclick="openModal360({{ $kamarInap->id }})"
                    aria-label="Preview 360° kamar {{ $kamarInap->nama }}"
                    class="absolute bottom-3 right-3 z-10 inline-flex items-center gap-1
                           bg-primary text-white text-[11px] font-bold px-2.5 py-1 rounded-full
                           shadow-lg shadow-primary/40 ring-1 ring-white/40
                           hover:ring-white/80 hover:scale-105 active:scale-95
                           transition-all duration-200">
                <span class="material-symbols-outlined text-[12px]">360</span>
                360°
            </button>
        @endif
    </div>

    {{-- Hidden links galeri --}}
    @foreach($galeri as $g)
        <a href="{{ Storage::url($g->gambar) }}"
           class="glightbox hidden"
           data-gallery="rawat-inap-{{ $kamarInap->id }}"></a>
    @endforeach

    {{-- Carousel thumbnail galeri --}}
    @if($galeri->isNotEmpty())
        <div class="px-3 pt-3 pb-0 bg-surface-container/30">
            <div data-hs-carousel='{
                     "loadingClasses": "opacity-0",
                     "slidesQty": { "xs": 3, "sm": 4 },
                     "isAutoPlay": false,
                     "isInfiniteLoop": false
                 }'
                 class="relative">
                <div class="hs-carousel overflow-hidden rounded-lg">
                    <div class="hs-carousel-body flex gap-2 transition-transform duration-300 opacity-0">
                        @foreach($galeri as $g)
                            <div class="hs-carousel-slide">
                                <a href="{{ Storage::url($g->gambar) }}"
                                   class="glightbox block relative overflow-hidden rounded-lg aspect-square group/thumb"
                                   data-gallery="rawat-inap-{{ $kamarInap->id }}">
                                    <img src="{{ Storage::url($g->gambar) }}"
                                         alt="{{ $kamarInap->nama }}"
                                         class="w-full h-full object-cover group-hover/thumb:scale-110 transition-transform duration-300"
                                         loading="lazy">
                                    <div class="absolute inset-0 bg-black/0 group-hover/thumb:bg-black/30 transition flex items-center justify-center">
                                        <span class="material-symbols-outlined text-white text-base opacity-0 group-hover/thumb:opacity-100 transition">zoom_in</span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                @if($galeri->count() > 4)
                    <button type="button"
                            class="hs-carousel-prev hs-carousel-disabled:opacity-20 hs-carousel-disabled:cursor-not-allowed
                                   absolute -left-3 top-1/2 -translate-y-1/2 z-10
                                   w-8 h-8 rounded-full bg-white shadow border border-outline-variant/30
                                   flex items-center justify-center text-primary hover:bg-primary hover:text-white transition">
                        <span class="material-symbols-outlined text-[16px]">chevron_left</span>
                    </button>
                    <button type="button"
                            class="hs-carousel-next hs-carousel-disabled:opacity-20 hs-carousel-disabled:cursor-not-allowed
                                   absolute -right-3 top-1/2 -translate-y-1/2 z-10
                                   w-8 h-8 rounded-full bg-white shadow border border-outline-variant/30
                                   flex items-center justify-center text-primary hover:bg-primary hover:text-white transition">
                        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    </button>
                @endif
            </div>
        </div>
    @endif

    {{-- Info kamar --}}
    <div class="p-5 flex flex-col flex-1">

        {{-- Nama & Harga --}}
        <div class="flex items-start justify-between gap-3 mb-3">
            <h4 class="font-bold text-base md:text-lg text-on-surface leading-snug">{{ $kamarInap->nama }}</h4>
            <div class="text-right shrink-0 rounded-xl">
                <p class="text-white font-bold text-base md:text-lg leading-none tabular-nums bg-tertiary rounded-full py-1 px-2">
                    Rp {{ number_format($kamarInap->harga, 0, ',', '.') }}
                </p>
                <p class="text-xs md:text-sm text-on-surface-variant mt-0.5">/malam</p>
            </div>
        </div>

        {{-- Kapasitas (non-VIP) --}}
        @if(!$isVip && $kamarInap->kapasitas)
            <div class="flex items-center gap-1.5 text-xs text-on-surface-variant mb-4">
                <span class="material-symbols-outlined text-[14px] text-secondary">person</span>
                Kapasitas <span class="font-semibold text-secondary">{{ $kamarInap->kapasitas }} pasien</span>
            </div>
        @endif

        {{-- Fasilitas --}}
        @if($kamarInap->fasilitasRawatInap->isNotEmpty())
            @php
                $totalFasilitas = $kamarInap->fasilitasRawatInap->count();
                $sisanya        = max(0, $totalFasilitas - 6);
            @endphp
            <div class="border-t border-outline-variant/20 pt-3 mt-auto"
                 x-data="{ expanded: false }">
                <p class="text-xs text-on-surface-variant uppercase tracking-widest font-semibold mb-2">Fasilitas</p>
                <div class="flex flex-wrap gap-1.5">

                    {{-- 6 pertama selalu tampil --}}
                    @foreach($kamarInap->fasilitasRawatInap->take(6) as $f)
                        <span class="inline-flex items-center gap-1 text-xs md:text-sm text-on-surface
                                     bg-surface-container border border-outline-variant/20 px-2.5 py-1.5 rounded-full leading-none">
                            {{ $f->nama }}
                        </span>
                    @endforeach

                    {{-- Sisanya — hanya tampil saat expanded --}}
                    @if($sisanya > 0)
                        @foreach($kamarInap->fasilitasRawatInap->skip(6) as $f)
                            <span x-show="expanded"
                                  x-transition:enter="transition ease-out duration-150"
                                  x-transition:enter-start="opacity-0 scale-90"
                                  x-transition:enter-end="opacity-100 scale-100"
                                  class="inline-flex items-center gap-1 text-xs md:text-sm text-on-surface
                                         bg-surface-container border border-outline-variant/20 px-2.5 py-1.5 rounded-full leading-none">
                                {{ $f->nama }}
                            </span>
                        @endforeach

                        {{-- Tombol toggle --}}
                        <button @click="expanded = !expanded"
                                class="inline-flex items-center gap-1 text-xs md:text-sm font-semibold
                                       text-primary hover:text-primary/70 px-2.5 py-1.5 rounded-full
                                       border border-primary/30 hover:bg-primary/8 transition-colors leading-none">
                            <span x-show="!expanded">+{{ $sisanya }} lainnya</span>
                            <span x-show="expanded" class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">expand_less</span>
                                Sembunyikan
                            </span>
                        </button>
                    @endif

                </div>
            </div>
        @endif
    </div>

</div>
