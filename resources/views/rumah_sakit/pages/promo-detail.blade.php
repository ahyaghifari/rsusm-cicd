<div>

{{-- Breadcrumb --}}
<div class="max-w-340 mx-auto px-4 pt-8 pb-4">
    <a wire:navigate href="{{ rumahsakit_route('rumahsakit.promo') }}"
       class="inline-flex items-center gap-1.5 text-sm text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
        Semua Promo
    </a>
</div>

{{-- Layout utama: gambar kiri, konten kanan --}}
<div class="max-w-340 mx-auto px-4 pb-12">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 items-start">

        {{-- Gambar — 2/5, sticky saat scroll --}}
        <div class="lg:col-span-2 lg:sticky lg:top-0">
            @if($promo->gambar)
                <div class="bg-gray-50 rounded-3xl overflow-hidden flex items-center justify-center p-4 min-h-80">
                    <img src="{{ Storage::url($promo->gambar) }}" alt="{{ $promo->judul }}"
                         class="w-full h-auto object-contain max-h-150">
                </div>
            @else
                <div class="bg-primary/8 rounded-3xl flex items-center justify-center min-h-80">
                    <span class="material-symbols-outlined text-[6rem] text-primary/20">local_offer</span>
                </div>
            @endif
        </div>

        {{-- Konten — 3/5 --}}
        <div class="lg:col-span-3">

            {{-- Badge Unggulan --}}
            @if($promo->popup)
                <span class="inline-flex items-center gap-1 text-xs font-bold uppercase tracking-widest
                             bg-yellow-400 text-primary px-3 py-1.5 rounded-full mb-4 shadow-sm">
                    <span class="material-symbols-outlined text-[12px]">star</span> Unggulan
                </span>
            @endif

            {{-- Tanggal --}}
            <p class="text-xs text-on-surface-variant mb-3">
                {{ $promo->created_at->translatedFormat('d F Y') }}
            </p>

            {{-- Judul --}}
            <h1 class="text-2xl md:text-3xl font-bold text-on-surface leading-snug mb-5">
                {{ $promo->judul }}
            </h1>

            {{-- Aksen kuning --}}
            <div class="h-1 w-14 bg-yellow-400 rounded-full mb-6"></div>

            {{-- Deskripsi --}}
            @if($promo->deskripsi)
                <div class="prose max-w-none text-on-surface/80 leading-relaxed
                            prose-headings:font-bold prose-headings:text-on-surface
                            prose-a:text-primary prose-a:no-underline hover:prose-a:underline">
                    {!! str($promo->deskripsi)->sanitizeHtml() !!}
                </div>
            @else
                <p class="text-on-surface-variant italic">Tidak ada deskripsi untuk promo ini.</p>
            @endif

        </div>
    </div>
</div>

{{-- Promo Lainnya --}}
@if($promoLainnya->isNotEmpty())
<section class="border-t border-outline-variant/20 max-w-340 mx-auto px-4 py-12">

    <div class="flex items-center gap-3 mb-8">
        <div class="h-1 w-10 bg-yellow-400 rounded-full"></div>
        <h2 class="text-xl font-bold text-on-surface">Promo Lainnya</h2>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($promoLainnya as $p)
            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.promo_detail', ['promo' => $p->slug]) }}"
               class="group flex flex-col bg-white rounded-2xl overflow-hidden
                      border border-outline-variant/20 shadow-sm
                      hover:shadow-xl hover:-translate-y-1 transition-all duration-300">

                <div class="relative bg-gray-50 flex items-center justify-center overflow-hidden h-64">
                    @if($p->gambar)
                        <img src="{{ Storage::url($p->gambar) }}" alt="{{ $p->judul }}"
                             class="h-full w-auto max-w-full object-contain
                                    transition-transform duration-500 group-hover:scale-105"
                             loading="lazy">
                    @else
                        <span class="material-symbols-outlined text-6xl text-outline-variant">local_offer</span>
                    @endif
                    @if($p->popup)
                        <div class="absolute top-3 left-3">
                            <span class="inline-flex items-center gap-1 text-xs font-bold uppercase
                                         tracking-widest bg-yellow-400 text-primary px-2 py-0.5 rounded-full">
                                <span class="material-symbols-outlined text-xs">star</span> Unggulan
                            </span>
                        </div>
                    @endif
                </div>

                <div class="flex flex-col flex-1 p-4 border-t-2 border-yellow-400">
                    <h3 class="font-bold text-on-surface text-sm leading-snug mb-1">{{ $p->judul }}</h3>
                    <div class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-primary">
                        Lihat Detail
                        <span class="material-symbols-outlined text-[13px] transition-transform group-hover:translate-x-1">arrow_forward</span>
                    </div>
                </div>

            </a>
        @endforeach
    </div>

</section>
@endif

</div>
