<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('chatbot', {
        open: false,
        showBadge: true,
        toggle() {
            this.open = !this.open;
            if (this.open) this.showBadge = false;
        },
    });
});
</script>

{{-- Chat Panel container --}}
<div
    x-data
    x-show="$store.chatbot.open"
    x-transition:enter="transition ease-[cubic-bezier(.34,1.28,.64,1)] duration-220"
    x-transition:enter-start="opacity-0 scale-90 translate-y-4"
    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-90 translate-y-4"
    class="fixed z-150
           inset-0
           lg:inset-x-auto lg:inset-y-auto lg:bottom-6 lg:right-6"
    style="transform-origin: bottom right;"
>
    <livewire:chatbot.panel />
</div>

{{-- Desktop FAB --}}
<div
    x-data="{
        showTooltip: false,
        get open()      { return $store.chatbot.open; },
        get showBadge() { return $store.chatbot.showBadge; },
    }"
    x-init="setTimeout(() => { showTooltip = true; setTimeout(() => showTooltip = false, 3500) }, 1200)"
    x-show="!open"
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0 scale-90"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-90"
    class="hidden lg:flex fixed bottom-6 right-6 z-100 flex-col items-end gap-2"
>
    <div x-show="showTooltip && !open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-x-2"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="bg-tertiary text-white text-[12px] px-3 py-1.5 rounded-full whitespace-nowrap pointer-events-none">
        Ada yang bisa kami bantu?
    </div>

    <div class="relative">
        <button
            @click="$store.chatbot.toggle()"
            :aria-expanded="open"
            :aria-label="open ? 'Tutup chatbot' : 'Buka chatbot Syifa Assistant'"
            class="relative h-14 rounded-full bg-primary hover:bg-primary active:scale-95 border-none flex items-center justify-center cursor-pointer transition-all duration-150 shadow-lg"
            :class="open ? 'w-14' : 'pl-4 pr-5 gap-2'"
        >
            <span class="relative w-6.5 h-6.5 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-white text-[26px] absolute transition-all duration-200"
                      :class="open ? 'opacity-0 scale-50 rotate-45' : 'opacity-100 scale-100 rotate-0'"
                      aria-hidden="true">chat_bubble</span>
                <span class="material-symbols-outlined text-white text-[26px] absolute transition-all duration-200"
                      :class="open ? 'opacity-100 scale-100 rotate-0' : 'opacity-0 scale-50 -rotate-45'"
                      aria-hidden="true">close</span>
            </span>
            <span x-show="!open"
                  x-transition:enter="transition ease-out duration-150"
                  x-transition:enter-start="opacity-0"
                  x-transition:enter-end="opacity-100"
                  class="text-white text-sm font-semibold whitespace-nowrap">Tanya Syifa</span>
            <span x-show="showBadge && !open"
                  x-transition:leave="transition duration-150"
                  x-transition:leave-end="scale-0"
                  class="absolute -top-0.5 -right-0.5 w-4.5 h-4.5 rounded-full bg-red-500 border-2 border-white text-[10px] font-medium text-white flex items-center justify-center">1</span>
        </button>
    </div>
</div>
