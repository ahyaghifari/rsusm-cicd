@if(isset($promo_popup) && $promo_popup->isNotEmpty())

<script>
function promoPopup(rsSlug) {
    return {
        visible: false,
        storageKey: 'promo_popup_' + rsSlug,
        init() {
            const last = localStorage.getItem(this.storageKey);
            const now  = Math.floor(Date.now() / 1000);
            if (!last || now - parseInt(last) > 86400) {
                setTimeout(() => { this.visible = true; }, 1500);
            }
            window.__promoPopup = this;
        },
        close() {
            this.visible = false;
            localStorage.setItem(this.storageKey, String(Math.floor(Date.now() / 1000)));
        },
        open() {
            this.visible = true;
        }
    };
}
</script>

<div
    x-data="promoPopup('{{ $currentRumahSakit->slug }}')"
    x-show="visible"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[200] flex items-center justify-center p-4"
    style="display:none;">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="close()"></div>

    {{-- Modal: lebar xl untuk side-by-side --}}
    <div class="relative z-10 w-full max-w-2xl bg-white rounded-3xl overflow-hidden shadow-2xl">

        {{-- Tombol tutup --}}
        <button @click="close()"
            class="absolute top-3 right-3 z-20 w-8 h-8 bg-black/25 hover:bg-black/50
                   text-white rounded-full flex items-center justify-center transition-colors duration-150">
            <span class="material-symbols-outlined text-[18px]">close</span>
        </button>

        <div x-data="{ current: 0 }">
            @foreach($promo_popup as $i => $p)
            <div x-show="current === {{ $i }}"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

                <a href="{{ route('rumahsakit.promo_detail', ['rumahsakit' => $currentRumahSakit->slug, 'promo' => $p->slug]) }}"
                   @click="close()"
                   class="flex flex-col sm:flex-row">

                    {{-- Gambar portrait — kiri --}}
                    <div class="sm:w-2/5 bg-gray-50 flex items-center justify-center overflow-hidden
                                min-h-56 sm:min-h-full">
                        @if($p->gambar)
                            <img src="{{ Storage::url($p->gambar) }}" alt="{{ $p->judul }}"
                                 class="w-full h-full object-contain max-h-96 sm:max-h-none sm:h-full">
                        @else
                            <span class="material-symbols-outlined text-6xl text-outline-variant">local_offer</span>
                        @endif
                    </div>

                    {{-- Konten — kanan --}}
                    <div class="flex-1 flex flex-col justify-between p-7">
                        <div>
                            <span class="inline-flex items-center gap-1 text-xs font-bold uppercase tracking-widest
                                         bg-yellow-400 text-primary px-3 py-1 rounded-full mb-4">
                                <span class="material-symbols-outlined text-[11px]">local_offer</span>
                                Promo
                                @if($promo_popup->count() > 1)
                                    &middot; {{ $i + 1 }}/{{ $promo_popup->count() }}
                                @endif
                            </span>
                            <h3 class="text-xl font-bold text-on-surface leading-snug mt-3 mb-3">
                                {{ $p->judul }}
                            </h3>
                            @if($p->deskripsi)
                                <p class="text-sm text-on-surface-variant line-clamp-4 leading-relaxed">
                                    {{ strip_tags($p->deskripsi) }}
                                </p>
                            @endif
                        </div>
                        <div class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-primary">
                            Lihat Detail
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </div>
                    </div>

                </a>
            </div>
            @endforeach

            {{-- Dots navigator --}}
            @if($promo_popup->count() > 1)
            <div class="flex justify-center gap-2 py-4 border-t border-outline-variant/20">
                @foreach($promo_popup as $i => $p)
                <button @click="current = {{ $i }}"
                    :class="current === {{ $i }} ? 'bg-primary w-6' : 'bg-outline-variant w-2.5'"
                    class="h-2.5 rounded-full transition-all duration-300"></button>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

@endif
