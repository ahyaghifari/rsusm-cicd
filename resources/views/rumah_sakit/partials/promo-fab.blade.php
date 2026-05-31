@if(isset($promo_popup) && $promo_popup->isNotEmpty())
{{-- Promo FAB — mandiri, fixed di kiri chatbot FAB --}}
<div class="fixed bottom-20 lg:bottom-6 right-24 z-100">
    <button
        onclick="window.__promoPopup && window.__promoPopup.open()"
        class="relative group flex items-center gap-2
               bg-linear-to-br from-yellow-400 to-amber-500
               text-primary font-bold text-sm
               px-4 py-2.5 rounded-2xl
               shadow-lg shadow-yellow-500/30
               hover:-translate-y-0.5 hover:shadow-yellow-500/50
               transition-all duration-200">
        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">local_offer</span>
        Promo
        <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-primary text-yellow-300
                     text-[10px] font-black rounded-full
                     flex items-center justify-center ring-2 ring-amber-300">
            {{ $promo_popup->count() }}
        </span>
    </button>
</div>
@endif
