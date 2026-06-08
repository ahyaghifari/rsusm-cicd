<div>

<x-page-hero
    title="Promo & Penawaran"
    subtitle="Dapatkan penawaran terbaik dari layanan kesehatan kami"
    icon=""
/>

<section class="w-10/12 mx-auto px-4 py-12">

    @if($promos->isEmpty())
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <span class="material-symbols-outlined text-6xl text-outline-variant mb-4">sell</span>
            <p class="text-lg font-semibold text-on-surface-variant">Belum ada promo saat ini</p>
            <p class="text-sm text-on-surface-variant/70 mt-1">Pantau terus halaman ini untuk penawaran menarik dari kami</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($promos as $p)
            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.promo_detail', ['promo' => $p->slug]) }}"
               class="group flex flex-col bg-white rounded-2xl overflow-hidden
                      border border-outline-variant/20 shadow-sm
                      hover:shadow-xl hover:-translate-y-1 transition-all duration-300">

                {{-- Area Gambar —tinggi untuk poster portrait --}}
                <div class="relative bg-gray-50 flex items-center justify-center overflow-hidden h-72">
                    @if($p->gambar)
                        <img src="{{ Storage::url($p->gambar) }}" alt="{{ $p->judul }}"
                             class="h-full w-auto max-w-full object-contain
                                    transition-transform duration-500 group-hover:scale-105"
                             loading="lazy">
                    @else
                        {{-- Placeholder dengan pola dot --}}
                        <div class="absolute inset-0 opacity-5"
                             style="background-image: radial-gradient(circle, currentColor 1px, transparent 1px); background-size: 16px 16px;">
                        </div>
                        <span class="material-symbols-outlined text-7xl text-outline-variant relative z-10">
                            local_offer
                        </span>
                    @endif

                    {{-- Badge Unggulan --}}
                    @if($p->popup)
                        <div class="absolute top-3 left-3">
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase
                                         tracking-widest bg-yellow-400 text-primary px-2.5 py-1 rounded-full shadow-sm">
                                <span class="material-symbols-outlined text-[10px]">star</span> Unggulan
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Konten --}}
                <div class="flex flex-col flex-1 p-5 border-t-2 border-yellow-400">
                    <h3 class="font-bold text-on-surface text-base leading-snug mb-2">
                        {{ $p->judul }}
                    </h3>
                    @if($p->deskripsi)
                        <p class="text-sm text-on-surface-variant leading-relaxed line-clamp-3 flex-1">
                            {{ strip_tags($p->deskripsi) }}
                        </p>
                    @else
                        <div class="flex-1"></div>
                    @endif
                    <div class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-primary">
                        Lihat Detail
                        <span class="material-symbols-outlined text-[16px]
                                     transition-transform group-hover:translate-x-1">arrow_forward</span>
                    </div>
                </div>

            </a>
            @endforeach
        </div>
    @endif

</section>

</div>
