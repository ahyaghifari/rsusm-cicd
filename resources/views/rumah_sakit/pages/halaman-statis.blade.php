<div>
<style>
    {{-- h1 dari rich-text otomatis diturunkan jadi h2 saat render (lihat blade di bawah) —
         supaya halaman ini tidak pernah punya 2 <h1> (satu lagi dari <x-page-hero>). --}}
    .halaman-konten h2 { font-size: 1.35rem; font-weight: 700; color: var(--color-primary); margin-top: 1.5rem; margin-bottom: 0.5rem; }
    .halaman-konten h3 { font-size: 1.1rem; font-weight: 600; color: var(--color-on-surface); margin-top: 1.25rem; margin-bottom: 0.4rem; }
    .halaman-konten p { margin-bottom: 1rem; }
    .halaman-konten ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
    .halaman-konten ol { list-style: decimal; padding-left: 1.5rem; margin-bottom: 1rem; }
    .halaman-konten li { margin-bottom: 0.35rem; }
    .halaman-konten strong { font-weight: 700; color: var(--color-on-surface); }
    .halaman-konten em { font-style: italic; }
    .halaman-konten a { color: var(--color-primary); text-decoration: underline; }
    .halaman-konten blockquote { border-left: 4px solid var(--color-primary); padding-left: 1rem; margin: 1rem 0; color: #6b7280; font-style: italic; }
    .halaman-konten hr { border-color: var(--color-outline-variant); margin: 1.5rem 0; opacity: 0.3; }
</style>

    <x-page-hero :title="$halaman->judul" />

    <div class="w-11/12 max-w-5xl mx-auto py-10 px-4 md:px-0">
        <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-start">

            {{-- Sidebar: Gambar RS --}}
            @php
                $gambarRS = $currentRumahSakit->gambar_tentang ?? $currentRumahSakit->gambar ?? null;
            @endphp
            @if($gambarRS)
            <aside class="w-full lg:w-72 shrink-0 lg:sticky lg:top-4">
                <div class="rounded-2xl overflow-hidden shadow-md border border-outline-variant/20">
                    <img src="{{ Storage::url($gambarRS) }}"
                         alt="{{ $currentRumahSakit->nama }}"
                         class="w-full h-56 lg:h-64 object-cover" loading="lazy">
                    <div class="bg-primary px-4 py-3">
                        <p class="text-white font-semibold text-sm leading-snug">{{ $currentRumahSakit->nama }}</p>
                        <p class="text-white/70 text-xs mt-0.5 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[13px]">location_on</span>
                            {{ $currentRumahSakit->lokasi }}
                        </p>
                    </div>
                </div>

                {{-- Navigasi halaman lain --}}
                @if($halaman_nav->count() > 1)
                <div class="mt-4 bg-white rounded-2xl border border-outline-variant/20 shadow-sm overflow-hidden">
                    <p class="text-xs font-bold text-on-surface-variant uppercase tracking-widest px-4 pt-4 pb-2">
                        Informasi Lainnya
                    </p>
                    @foreach($halaman_nav as $h)
                        <a wire:navigate
                           href="{{ rumahsakit_route('rumahsakit.halaman_statis', ['slug' => $h->slug]) }}"
                           class="flex items-center gap-2 px-4 py-2.5 text-sm transition-colors duration-150
                                  {{ $h->slug === $halaman->slug
                                      ? 'bg-primary/10 text-primary font-semibold border-l-4 border-primary'
                                      : 'text-on-surface hover:bg-gray-50' }}">
                            <span class="material-symbols-outlined text-[15px]">
                                {{ $h->slug === $halaman->slug ? 'article' : 'chevron_right' }}
                            </span>
                            {{ $h->judul }}
                        </a>
                    @endforeach
                </div>
                @endif
            </aside>
            @endif

            {{-- Konten Utama --}}
            <div class="flex-1 min-w-0">
                <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/20 p-6 md:p-10">
                    <div class="halaman-konten text-gray-700 leading-relaxed text-sm md:text-base">
                        {!! preg_replace('/<(\/?)h1(\s|>)/i', '<$1h2$2', $halaman->konten) !!}
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
