{{-- Mobile bottom bar — hanya tampil di bawah lg --}}
@if($currentRumahSakit->no_emergency || $currentRumahSakit->no_hotline)
<div class="fixed bottom-0 left-0 right-0 z-50 lg:hidden
            bg-white/95 backdrop-blur-sm border-t border-outline-variant/25
            shadow-[0_-2px_16px_rgba(0,0,0,0.07)]"
     style="padding-bottom: env(safe-area-inset-bottom, 0);">
    <div class="grid divide-x divide-outline-variant/20"
         style="grid-template-columns: repeat({{ ($currentRumahSakit->no_emergency ? 1 : 0) + ($currentRumahSakit->no_hotline ? 1 : 0) + 1 }}, 1fr);">

        {{-- Emergency --}}
        @if($currentRumahSakit->no_emergency)
        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $currentRumahSakit->no_emergency) }}"
           class="flex flex-col items-center justify-center gap-0.5 py-2
                  active:bg-red-50 transition-colors duration-150 min-w-0 px-2">
            {{-- Ikon + label satu baris --}}
            <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-white bg-red-600
                             text-[11px] p-1 rounded leading-none shrink-0">emergency</span>
                <p class="text-[11px] font-bold text-red-600 uppercase tracking-wide truncate">Emergency</p>
            </div>
            {{-- Nomor di bawah --}}
            <p class="text-[10px] font-medium text-on-surface-variant truncate w-full text-center">
                {{ $currentRumahSakit->no_emergency }}
            </p>
        </a>
        @endif

        {{-- Hotline --}}
        @if($currentRumahSakit->no_hotline)
        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $currentRumahSakit->no_hotline) }}"
           class="flex flex-col items-center justify-center gap-0.5 py-2
                  active:bg-green-50 transition-colors duration-150 min-w-0 px-2">
            <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-white bg-green-600
                             text-[11px] p-1 rounded leading-none shrink-0">call</span>
                <p class="text-[11px] font-bold text-green-600 uppercase tracking-wide truncate">Hotline</p>
            </div>
            <p class="text-[10px] font-medium text-on-surface-variant truncate w-full text-center">
                {{ $currentRumahSakit->no_hotline }}
            </p>
        </a>
        @endif

        {{-- Chatbot --}}
        <button
            x-data
            @click="$store.chatbot.toggle()"
            :aria-expanded="$store.chatbot.open"
            class="flex flex-col items-center justify-center gap-0.5 py-2
                   active:bg-primary/5 transition-colors duration-150 cursor-pointer min-w-0 px-2"
            aria-label="Buka chatbot"
        >
            <div class="flex items-center gap-1.5">
                <div class="relative shrink-0 w-7 h-7 flex items-center justify-center">
                    <img src="{{ asset('img/tanya-syifa.png') }}"
                         alt=""
                         class="w-fit h-6"
                         aria-hidden="true">
                    <span x-show="$store.chatbot.showBadge && !$store.chatbot.open"
                          class="absolute -top-0.5 -right-0.5 w-3 h-3 rounded-full
                                 bg-red-500 border border-white text-[7px] font-bold
                                 text-white flex items-center justify-center">1</span>
                </div>
                <p class="text-[11px] font-bold text-primary uppercase tracking-wide truncate">Tanya Syifa</p>
            </div>
            <p class="text-[10px] font-medium text-on-surface-variant truncate w-full text-center">
                Tanya Kami
            </p>
        </button>

    </div>
</div>
@endif
