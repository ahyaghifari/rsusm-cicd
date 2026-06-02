{{-- Hanya tampil di mobile/tablet (hidden di lg ke atas) --}}
@if($currentRumahSakit->no_emergency || $currentRumahSakit->no_hotline)
<div class="fixed bottom-0 left-0 right-0 z-50 lg:hidden
            bg-white border-t border-outline-variant/30
            shadow-[0_-4px_24px_rgba(0,0,0,0.09)]">
    <div class="grid grid-cols-3 divide-x divide-outline-variant/20">

        {{-- Emergency --}}
        @if($currentRumahSakit->no_emergency)
        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $currentRumahSakit->no_emergency) }}"
           class="flex items-center justify-center gap-3 py-3 px-4
                  active:bg-red-50 transition-colors duration-150">
            <span class="material-symbols-outlined text-white bg-red-600 rounded-xl
                         text-[20px] p-2 leading-none shrink-0">emergency</span>
            <div>
                <p class="text-[10px] font-bold text-red-600 uppercase tracking-wide">Emergency</p>
                <p class="text-xs font-medium text-on-surface">{{ $currentRumahSakit->no_emergency }}</p>
            </div>
        </a>
        @else
        <div class="flex items-center justify-center gap-3 py-3 px-4 opacity-35">
            <span class="material-symbols-outlined text-white bg-red-400 rounded-xl
                         text-[20px] p-2 leading-none shrink-0">emergency</span>
            <div>
                <p class="text-[10px] font-bold text-red-400 uppercase tracking-wide">Emergency</p>
                <p class="text-xs text-on-surface-variant">—</p>
            </div>
        </div>
        @endif

        {{-- Hotline --}}
        @if($currentRumahSakit->no_hotline)
        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $currentRumahSakit->no_hotline) }}"
           class="flex items-center justify-center gap-3 py-3 px-4
                  active:bg-green-50 transition-colors duration-150">
            <span class="material-symbols-outlined text-white bg-green-600 rounded-xl
                         text-[20px] p-2 leading-none shrink-0">call</span>
            <div>
                <p class="text-[10px] font-bold text-green-600 uppercase tracking-wide">Hotline</p>
                <p class="text-xs font-medium text-on-surface">{{ $currentRumahSakit->no_hotline }}</p>
            </div>
        </a>
        @else
        <div class="flex items-center justify-center gap-3 py-3 px-4 opacity-35">
            <span class="material-symbols-outlined text-white bg-green-400 rounded-xl
                         text-[20px] p-2 leading-none shrink-0">call</span>
            <div>
                <p class="text-[10px] font-bold text-green-400 uppercase tracking-wide">Hotline</p>
                <p class="text-xs text-on-surface-variant">—</p>
            </div>
        </div>
        @endif

        {{-- Chatbot --}}
        <button
            x-data
            @click="$store.chatbot.toggle()"
            :aria-expanded="$store.chatbot.open"
            class="flex items-center justify-center gap-3 py-3 px-4 active:bg-primary/5 transition-colors duration-150 cursor-pointer"
            aria-label="Buka chatbot"
        >
            <div class="relative shrink-0">
                <span class="material-symbols-outlined text-white bg-primary rounded-xl text-[20px] p-2 leading-none block" aria-hidden="true">chat_bubble</span>
                <span
                    x-show="$store.chatbot.showBadge && !$store.chatbot.open"
                    class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-500 border-2 border-white text-[9px] font-bold text-white flex items-center justify-center"
                >1</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-primary uppercase tracking-wide">Asisten</p>
                <p class="text-xs font-medium text-on-surface">Tanya Kami</p>
            </div>
        </button>

    </div>
</div>
@endif
