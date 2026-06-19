<div>

<x-page-hero
    title="Artikel & Berita"
    subtitle="Informasi, tips kesehatan, dan berita terbaru dari kami"
/>

{{-- Filter --}}
<div class="relative z-20 bg-white border-2 border-primary shadow-lg -translate-y-5
            p-4 rounded-xl w-10/12 md:w-9/12 mx-auto
            grid grid-cols-1 sm:grid-cols-2 gap-3">

    {{-- Cari judul / ringkasan --}}
    <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg class="size-4 text-primary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
            </svg>
        </div>
        <input type="text"
               wire:model.live.debounce.300ms="search"
               class="w-full pl-10 pr-4 py-2.5 bg-gray-100 border-transparent rounded-lg text-sm
                      text-gray-700 placeholder:text-gray-400
                      focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/30
                      outline-none transition"
               placeholder="Cari judul atau ringkasan artikel...">
    </div>

    {{-- Filter kategori (searchable) --}}
    @include('rumah_sakit.pages._searchable-select', [
        'property'     => 'kategori',
        'options'      => $data_kategori->map(fn($k) => ['value' => $k->slug, 'label' => $k->nama])->values()->toArray(),
        'placeholder'  => '— Semua Kategori —',
        'currentValue' => $kategori,
        'wrapperClass' => 'w-full',
    ])
</div>

<section class="bg-gradient-to-b from-surface-container/40 to-white">
<div class="w-11/12 lg:w-10/12 mx-auto py-12 lg:py-16">

    @if(!$artikelUnggulan && $artikelList->isEmpty())
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-20 h-20 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-5">
                <span class="material-symbols-outlined text-4xl text-primary">{{ $search || $kategori ? 'search_off' : 'newspaper' }}</span>
            </div>
            @if($search || $kategori)
                <p class="text-lg font-semibold text-on-surface">Artikel tidak ditemukan</p>
                <p class="text-sm text-on-surface-variant/60 mt-1.5">Coba ubah kata kunci atau kategori pencarian</p>
            @else
                <p class="text-lg font-semibold text-on-surface">Belum ada artikel tersedia</p>
            @endif
        </div>
    @else

        {{-- Artikel Unggulan --}}
        @if($artikelUnggulan)
        <a wire:navigate href="{{ rumahsakit_route('rumahsakit.artikel_detail', ['artikel' => $artikelUnggulan->slug]) }}"
           class="group flex flex-col lg:flex-row gap-6 bg-white rounded-2xl overflow-hidden
                  border border-outline-variant/20 shadow-sm hover:shadow-xl transition-all duration-300 mb-12">

            <div class="lg:w-1/2 bg-gray-50 flex items-center justify-center overflow-hidden aspect-video lg:aspect-auto">
                @if($artikelUnggulan->gambar)
                    <img src="{{ Storage::url($artikelUnggulan->gambar) }}" alt="{{ $artikelUnggulan->judul }}"
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                         loading="lazy">
                @else
                    <span class="material-symbols-outlined text-6xl text-outline-variant">newspaper</span>
                @endif
            </div>

            <div class="lg:w-1/2 flex flex-col justify-center p-6 lg:p-8">
                <span class="inline-flex items-center gap-1 text-xs font-bold uppercase tracking-widest
                             bg-yellow-400 text-primary px-3 py-1 rounded-full mb-4 w-fit">
                    <span class="material-symbols-outlined text-[12px]">star</span> Unggulan
                </span>
                @if($artikelUnggulan->kategori)
                    <span class="text-xs font-semibold text-primary mb-2">{{ $artikelUnggulan->kategori->nama }}</span>
                @endif
                <h2 class="text-xl lg:text-2xl font-bold text-on-surface leading-snug mb-3">
                    {{ $artikelUnggulan->judul }}
                </h2>
                @if($artikelUnggulan->ringkasan)
                    <p class="text-sm text-on-surface-variant leading-relaxed line-clamp-3 mb-4">
                        {{ $artikelUnggulan->ringkasan }}
                    </p>
                @endif
                <p class="text-xs text-on-surface-variant">
                    {{ $artikelUnggulan->tanggal_publish->translatedFormat('d F Y') }}
                    @if($artikelUnggulan->penulis) &middot; {{ $artikelUnggulan->penulis }} @endif
                </p>
            </div>
        </a>
        @endif

        {{-- Grid Artikel --}}
        @if($artikelList->isNotEmpty())

        {{-- Info hasil --}}
        <div class="flex items-center gap-2 mb-6">
            <div class="h-px flex-1 bg-outline-variant/30"></div>
            <p class="text-xs text-on-surface-variant shrink-0">
                <span class="font-semibold text-primary">{{ $artikelList->total() }}</span>
                artikel ditemukan
            </p>
            <div class="h-px flex-1 bg-outline-variant/30"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($artikelList as $artikel)
            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.artikel_detail', ['artikel' => $artikel->slug]) }}"
               class="group flex flex-col bg-white rounded-2xl overflow-hidden
                      border border-outline-variant/20 shadow-sm
                      hover:shadow-xl hover:-translate-y-1 transition-all duration-300">

                <div class="relative bg-gray-50 flex items-center justify-center overflow-hidden aspect-video">
                    @if($artikel->gambar)
                        <img src="{{ Storage::url($artikel->gambar) }}" alt="{{ $artikel->judul }}"
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                             loading="lazy">
                    @else
                        <span class="material-symbols-outlined text-5xl text-outline-variant">newspaper</span>
                    @endif
                </div>

                <div class="flex flex-col flex-1 p-5">
                    @if($artikel->kategori)
                        <span class="text-xs font-semibold text-primary mb-1.5">{{ $artikel->kategori->nama }}</span>
                    @endif
                    <h3 class="font-bold text-on-surface text-sm leading-snug mb-2 line-clamp-2">
                        {{ $artikel->judul }}
                    </h3>
                    @if($artikel->ringkasan)
                        <p class="text-xs text-on-surface-variant leading-relaxed line-clamp-2 mb-3">
                            {{ $artikel->ringkasan }}
                        </p>
                    @endif
                    <p class="text-xs text-on-surface-variant mt-auto">
                        {{ $artikel->tanggal_publish->translatedFormat('d F Y') }}
                    </p>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-10 flex flex-col items-center gap-3">
            {{ $artikelList->links('components.portal-pagination') }}
            @if($artikelList->hasPages())
                <p class="text-xs text-on-surface-variant/60">
                    Halaman {{ $artikelList->currentPage() }} dari {{ $artikelList->lastPage() }}
                    &nbsp;·&nbsp; {{ $artikelList->total() }} artikel
                </p>
            @endif
        </div>
        @endif

    @endif

</div>
</section>

</div>
