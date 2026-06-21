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
           class="flex items-center justify-center gap-2 py-3.5
                  hover:bg-red-50 active:bg-red-50 transition-colors duration-150 min-w-0 px-2">
            <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-red-600 shrink-0">
                <span class="material-symbols-outlined text-white text-[17px] leading-none">emergency</span>
            </span>
            <span class="flex flex-col items-start min-w-0 leading-tight">
                <span class="text-[11px] font-bold text-red-600 uppercase tracking-wide truncate w-full">Emergency</span>
                <span class="text-[10px] font-medium text-on-surface-variant truncate w-full">
                    {{ $currentRumahSakit->no_emergency }}
                </span>
            </span>
        </a>
        @endif

        {{-- Hotline --}}
        @if($currentRumahSakit->no_hotline)
        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $currentRumahSakit->no_hotline) }}"
           class="flex items-center justify-center gap-2 py-3.5
                  hover:bg-green-50 active:bg-green-50 transition-colors duration-150 min-w-0 px-2">
            <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-green-600 shrink-0">
                <span class="material-symbols-outlined text-white text-[17px] leading-none">call</span>
            </span>
            <span class="flex flex-col items-start min-w-0 leading-tight">
                <span class="text-[11px] font-bold text-green-600 uppercase tracking-wide truncate w-full">Hotline</span>
                <span class="text-[10px] font-medium text-on-surface-variant truncate w-full">
                    {{ $currentRumahSakit->no_hotline }}
                </span>
            </span>
        </a>
        @endif

        {{-- Chatbot — avatar bulat putih (sama seperti header panel chat), bukan logo lepas
             tanpa bidang, supaya bobot visualnya setara dengan badge ikon Emergency/Hotline --}}
        <button
            x-data
            @click="$store.chatbot.toggle()"
            :aria-expanded="$store.chatbot.open"
            class="flex items-center justify-center gap-2 py-3.5
                   hover:bg-primary/5 active:bg-primary/5 transition-colors duration-150 cursor-pointer min-w-0 px-2"
            aria-label="Buka chatbot Tanya Syifa"
        >
            <span class="relative flex items-center justify-center w-8 h-8 rounded-full
                         bg-white border border-primary/25 shrink-0">
                <img src="{{ asset('img/tanya-syifa.png') }}"
                     alt=""
                     class="w-6 h-6 object-contain"
                     aria-hidden="true">
                <span x-show="$store.chatbot.showBadge && !$store.chatbot.open"
                      role="status"
                      aria-label="1 pesan baru"
                      class="absolute -top-0.5 -right-0.5 w-3 h-3 rounded-full
                             bg-red-500 border border-white text-[7px] font-bold
                             text-white flex items-center justify-center">1</span>
            </span>
            <span class="flex flex-col items-start min-w-0 leading-tight">
                <span class="text-[11px] font-bold text-primary uppercase tracking-wide truncate w-full">Tanya Syifa</span>
                <span class="text-[10px] font-medium text-on-surface-variant flex items-center gap-1 truncate w-full">
                    <span class="w-[6px] h-[6px] rounded-full bg-green-500 shrink-0"></span>
                    Online
                </span>
            </span>
        </button>

    </div>
</div>
@endif
