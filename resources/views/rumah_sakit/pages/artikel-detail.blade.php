<div>

<style>
    .artikel h1 { font-size: 1.75rem; font-weight: 700; color: var(--color-primary); margin-top: 1.5rem; margin-bottom: 0.75rem; line-height: 1.3; }
    .artikel h2 { font-size: 1.35rem; font-weight: 700; color: var(--color-primary); margin-top: 1.5rem; margin-bottom: 0.5rem; }
    .artikel h3 { font-size: 1.1rem; font-weight: 600; color: var(--color-on-surface); margin-top: 1.25rem; margin-bottom: 0.4rem; }
    .artikel p { margin-bottom: 1rem; }
    .artikel ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
    .artikel ol { list-style: decimal; padding-left: 1.5rem; margin-bottom: 1rem; }
    .artikel li { margin-bottom: 0.35rem; }
    .artikel strong { font-weight: 700; color: var(--color-on-surface); }
    .artikel em { font-style: italic; }
    .artikel a { color: var(--color-primary); text-decoration: underline; }
    .artikel blockquote { border-left: 4px solid var(--color-primary); padding-left: 1rem; margin: 1rem 0; color: #6b7280; font-style: italic; }
    .artikel hr { border-color: var(--color-outline-variant); margin: 1.5rem 0; opacity: 0.3; }
</style>

{{-- Breadcrumb --}}
<div class="max-w-340 mx-auto px-4 pt-8 pb-4">
    <a wire:navigate href="{{ rumahsakit_route('rumahsakit.artikel') }}"
       class="inline-flex items-center gap-1.5 text-sm text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
        Semua Artikel
    </a>
</div>

<div class="max-w-340 mx-auto px-4 pb-12">
    <div class="max-w-3xl mx-auto">

        {{-- Gambar cover --}}
        @if($artikel->gambar)
            <div class="rounded-3xl overflow-hidden mb-8 aspect-video bg-gray-50">
                <img src="{{ Storage::url($artikel->gambar) }}" alt="{{ $artikel->judul }}"
                     class="w-full h-full object-cover">
            </div>
        @endif

        {{-- Kategori --}}
        @if($artikel->kategori)
            <span class="inline-flex items-center text-xs font-bold uppercase tracking-widest
                         bg-primary/10 text-primary px-3 py-1.5 rounded-full mb-4">
                {{ $artikel->kategori->nama }}
            </span>
        @endif

        {{-- Judul --}}
        <h1 class="text-2xl md:text-3xl font-bold text-on-surface leading-snug mb-4">
            {{ $artikel->judul }}
        </h1>

        {{-- Meta: tanggal & penulis --}}
        <p class="text-sm text-on-surface-variant mb-6">
            {{ $artikel->tanggal_publish->translatedFormat('d F Y') }}
            @if($artikel->penulis) &middot; Oleh {{ $artikel->penulis }} @endif
        </p>

        <div class="h-1 w-14 bg-yellow-400 rounded-full mb-8"></div>

        {{-- Konten --}}
        <div class="artikel prose max-w-none text-on-surface/80 leading-relaxed
                    prose-headings:font-bold prose-headings:text-on-surface
                    prose-a:text-primary prose-a:no-underline hover:prose-a:underline">
            {!! $artikel->konten !!}
        </div>

    </div>
</div>

{{-- Artikel Lainnya --}}
@if($artikelLainnya->isNotEmpty())
<section class="border-t border-outline-variant/20 max-w-340 mx-auto px-4 py-12 w-11/12 mx-auto md:w-10/12">

    <div class="flex items-center gap-3 mb-8">
        <div class="h-1 w-10 bg-yellow-400 rounded-full"></div>
        <h2 class="text-xl font-bold text-on-surface">Artikel Lainnya</h2>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-6 ">
        @foreach($artikelLainnya as $a)
            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.artikel_detail', ['artikel' => $a->slug]) }}"
               class="group flex flex-col bg-white rounded-2xl overflow-hidden
                      border border-outline-variant/20 shadow-sm
                      hover:shadow-xl hover:-translate-y-1 transition-all duration-300">

                <div class="relative bg-gray-50 flex items-center justify-center overflow-hidden aspect-video">
                    @if($a->gambar)
                        <img src="{{ Storage::url($a->gambar) }}" alt="{{ $a->judul }}"
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                             loading="lazy">
                    @else
                        <span class="material-symbols-outlined text-5xl text-outline-variant">newspaper</span>
                    @endif
                </div>

                <div class="flex flex-col flex-1 p-4">
                    <h3 class="font-bold text-on-surface text-xs md:text-sm leading-snug mb-1 line-clamp-2">{{ $a->judul }}</h3>
                    <div class="mt-3 inline-flex items-center gap-1 text-[11px] md:text-xs font-semibold text-primary">
                        Baca Selengkapnya
                        <span class="material-symbols-outlined text-[13px] transition-transform group-hover:translate-x-1">arrow_forward</span>
                    </div>
                </div>

            </a>
        @endforeach
    </div>

</section>
@endif

</div>
