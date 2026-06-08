<div>
    @if($isOpen)
    {{-- Overlay --}}
    <div class="fixed inset-0 z-200 flex items-start justify-center pt-16 px-4"
         x-data
         x-on:keydown.escape.window="$wire.close()"
         x-on:keydown.ctrl.k.window.prevent="$wire.close()">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"
             wire:click="close"></div>

        {{-- Modal --}}
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden animate-fade-in">

            {{-- Search Input --}}
            <div class="flex items-center gap-3 px-4 py-4 border-b border-outline-variant">
                <span class="material-symbols-outlined text-on-surface-variant shrink-0">search</span>
                <input
                    type="text"
                    wire:model.live.debounce.350ms="query"
                    placeholder="Cari dokter, poliklinik, promo, FAQ..."
                    class="flex-1 bg-transparent text-on-surface placeholder-on-surface-variant text-sm outline-none"
                    autofocus
                    autocomplete="off"
                />
                @if($query)
                    <button wire:click="$set('query', '')"
                            class="text-on-surface-variant hover:text-on-surface transition-colors">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                @else
                    <kbd class="hidden sm:inline-flex items-center gap-1 px-1.5 py-0.5 rounded border border-outline-variant text-[10px] text-on-surface-variant font-mono">Esc</kbd>
                @endif
            </div>

            {{-- Results --}}
            <div class="max-h-[60vh] overflow-y-auto overscroll-contain">

                {{-- Hint awal --}}
                @if(!$searched)
                    <div class="flex flex-col items-center justify-center py-12 text-on-surface-variant">
                        <span class="material-symbols-outlined text-[40px] mb-3 opacity-40">manage_search</span>
                        <p class="text-sm">Ketik minimal 2 huruf untuk mencari</p>
                    </div>

                {{-- Tidak ada hasil --}}
                @elseif($searched && !$hasResults)
                    <div class="flex flex-col items-center justify-center py-12 text-on-surface-variant">
                        <span class="material-symbols-outlined text-[40px] mb-3 opacity-40">search_off</span>
                        <p class="text-sm font-medium text-on-surface">Tidak ditemukan</p>
                        <p class="text-xs mt-1">Coba kata kunci lain</p>
                    </div>

                {{-- Hasil --}}
                @else
                    <div class="py-2">

                        {{-- Dokter --}}
                        @if($results['dokter']->isNotEmpty())
                            <p class="px-4 pt-3 pb-1.5 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Dokter</p>
                            @foreach($results['dokter'] as $dokter)
                                <a wire:navigate
                                   href="{{ route('rumahsakit.dokter_show', ['rumahsakit' => $rsSlug, 'dokter' => $dokter->slug]) }}"
                                   wire:click="close"
                                   class="flex items-center gap-3 px-4 py-2.5 hover:bg-surface-variant transition-colors">
                                    <div class="shrink-0 w-9 h-9 rounded-full overflow-hidden bg-primary/10 flex items-center justify-center">
                                        @if($dokter->foto)
                                            <img src="{{ asset('storage/' . $dokter->foto) }}"
                                                 alt="{{ $dokter->nama }}"
                                                 class="w-full h-full object-cover"
                                                 loading="lazy">
                                        @else
                                            <span class="material-symbols-outlined text-primary text-[18px]">person</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-on-surface truncate">{{ $dokter->nama }}</p>
                                        @if($dokter->spesialis)
                                            <p class="text-xs text-on-surface-variant truncate">{{ $dokter->spesialis->nama }}</p>
                                        @endif
                                    </div>
                                    <span class="material-symbols-outlined text-on-surface-variant text-[16px] ml-auto shrink-0">chevron_right</span>
                                </a>
                            @endforeach
                        @endif

                        {{-- Poliklinik --}}
                        @if($results['poliklinik']->isNotEmpty())
                            <p class="px-4 pt-3 pb-1.5 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Poliklinik</p>
                            @foreach($results['poliklinik'] as $poli)
                                <a wire:navigate
                                   href="{{ route('rumahsakit.rawat_jalan_show', ['rumahsakit' => $rsSlug, 'poliklinik' => $poli->slug]) }}"
                                   wire:click="close"
                                   class="flex items-center gap-3 px-4 py-2.5 hover:bg-surface-variant transition-colors">
                                    <div class="shrink-0 w-9 h-9 rounded-full overflow-hidden bg-secondary/10 flex items-center justify-center">
                                        @if($poli->gambar)
                                            <img src="{{ asset('storage/' . $poli->gambar) }}"
                                                 alt="{{ $poli->nama }}"
                                                 class="w-full h-full object-cover"
                                                 loading="lazy">
                                        @else
                                            <span class="material-symbols-outlined text-secondary text-[18px]">local_hospital</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-on-surface truncate">{{ $poli->nama }}</p>
                                    </div>
                                    <span class="material-symbols-outlined text-on-surface-variant text-[16px] ml-auto shrink-0">chevron_right</span>
                                </a>
                            @endforeach
                        @endif

                        {{-- Promo --}}
                        @if($results['promo']->isNotEmpty())
                            <p class="px-4 pt-3 pb-1.5 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Promo</p>
                            @foreach($results['promo'] as $promo)
                                <a wire:navigate
                                   href="{{ route('rumahsakit.promo_detail', ['rumahsakit' => $rsSlug, 'promo' => $promo->slug]) }}"
                                   wire:click="close"
                                   class="flex items-center gap-3 px-4 py-2.5 hover:bg-surface-variant transition-colors">
                                    <div class="shrink-0 w-9 h-9 rounded-full bg-yellow-100 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-yellow-600 text-[18px]">local_offer</span>
                                    </div>
                                    <p class="text-sm font-semibold text-on-surface truncate">{{ $promo->judul }}</p>
                                    <span class="material-symbols-outlined text-on-surface-variant text-[16px] ml-auto shrink-0">chevron_right</span>
                                </a>
                            @endforeach
                        @endif

                        {{-- FAQ --}}
                        @if($results['faq']->isNotEmpty())
                            <p class="px-4 pt-3 pb-1.5 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">FAQ</p>
                            @foreach($results['faq'] as $faq)
                                <a wire:navigate
                                   href="{{ route('rumahsakit.faq', ['rumahsakit' => $rsSlug]) }}"
                                   wire:click="close"
                                   class="flex items-center gap-3 px-4 py-2.5 hover:bg-surface-variant transition-colors">
                                    <div class="shrink-0 w-9 h-9 rounded-full bg-tertiary/10 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-tertiary text-[18px]">help</span>
                                    </div>
                                    <p class="text-sm font-semibold text-on-surface truncate">{{ $faq->judul }}</p>
                                    <span class="material-symbols-outlined text-on-surface-variant text-[16px] ml-auto shrink-0">chevron_right</span>
                                </a>
                            @endforeach
                        @endif

                        {{-- Halaman --}}
                        @if($results['halaman']->isNotEmpty())
                            <p class="px-4 pt-3 pb-1.5 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Halaman</p>
                            @foreach($results['halaman'] as $halaman)
                                <a wire:navigate
                                   href="{{ route('rumahsakit.halaman_statis', ['rumahsakit' => $rsSlug, 'slug' => $halaman->slug]) }}"
                                   wire:click="close"
                                   class="flex items-center gap-3 px-4 py-2.5 hover:bg-surface-variant transition-colors">
                                    <div class="shrink-0 w-9 h-9 rounded-full bg-on-surface/10 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-on-surface-variant text-[18px]">article</span>
                                    </div>
                                    <p class="text-sm font-semibold text-on-surface truncate">{{ $halaman->judul }}</p>
                                    <span class="material-symbols-outlined text-on-surface-variant text-[16px] ml-auto shrink-0">chevron_right</span>
                                </a>
                            @endforeach
                        @endif

                    </div>
                @endif

            </div>

            {{-- Footer hint --}}
            <div class="px-4 py-2.5 border-t border-outline-variant bg-surface flex items-center gap-4">
                <span class="text-[10px] text-on-surface-variant flex items-center gap-1">
                    <kbd class="px-1 py-0.5 rounded border border-outline-variant font-mono text-[9px]">Esc</kbd>
                    tutup
                </span>
                <span class="text-[10px] text-on-surface-variant flex items-center gap-1">
                    <kbd class="px-1 py-0.5 rounded border border-outline-variant font-mono text-[9px]">↵</kbd>
                    buka
                </span>
                <span class="text-[10px] text-on-surface-variant ml-auto">
                    Pencarian dalam RS ini
                </span>
            </div>

        </div>
    </div>
    @endif
</div>
