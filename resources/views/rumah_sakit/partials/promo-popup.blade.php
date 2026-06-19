@if(isset($promo_popup) && $promo_popup->isNotEmpty())

<script>
function promoPopup(rsSlug) {
    return {
        visible: false,
        storageKey: 'promo_popup_' + rsSlug,
        init() {
            const last = localStorage.getItem(this.storageKey);
            const now  = Math.floor(Date.now() / 1000);
            if (!last || now - parseInt(last) > 3600) {
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

    {{-- Modal: kontainer poster, width fit ke ukuran poster --}}
    <div class="relative z-10 w-fit max-w-[90vw] overflow-hidden">

        {{-- Tombol tutup — absolute langsung di atas poster --}}
        <button @click="close()"
            class="absolute top-3 right-3 z-20 w-8 h-8 bg-black/40 hover:bg-black/60
                   text-white rounded-full flex items-center justify-center transition-colors duration-150">
            <span class="material-symbols-outlined text-[18px]">close</span>
        </button>

        <div x-data="{ current: 0 }">
            @foreach($promo_popup as $i => $p)
            <div x-show="current === {{ $i }}"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

                {{-- Gambar --}}
                <div class="flex items-center justify-center overflow-hidden h-80 sm:h-[70vh]">
                    <div class="w-full h-full flex items-center justify-center">
                        @if($p->gambar)
                            <img src="{{ Storage::url($p->gambar) }}" alt="{{ $p->judul }}"
                                 class="max-w-full max-h-full object-contain"
                                 loading="lazy">
                        @else
                            <span class="material-symbols-outlined text-6xl text-outline-variant">local_offer</span>
                        @endif
                    </div>
                </div>

                {{-- Deskripsi & CTA --}}
                <div class="p-6 flex flex-col gap-4">
                    @if($p->deskripsi)
                        <p class="text-sm text-on-surface-variant leading-relaxed">
                            {{ strip_tags($p->deskripsi) }}
                        </p>
                    @endif
                    <a href="{{ route('rumahsakit.promo_detail', ['rumahsakit' => $currentRumahSakit->slug, 'promo' => $p->slug]) }}"
                       @click="close()"
                       class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl
                              bg-primary text-white text-sm font-semibold hover:opacity-90 transition-colors">
                        Lihat Detail
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>
            </div>
            @endforeach

        </div>
    </div>
</div>

@endif
