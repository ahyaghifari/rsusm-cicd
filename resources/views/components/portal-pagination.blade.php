@if ($paginator->hasPages())
<nav class="flex items-center justify-center gap-1" aria-label="Pagination"
     x-data
     x-on:livewire:navigated.window="window.scrollTo({top: 0, behavior: 'smooth'})">

    {{-- Prev --}}
    @if ($paginator->onFirstPage())
        <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl
                     text-on-surface-variant/30 cursor-not-allowed">
            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
        </span>
    @else
        <button wire:click="previousPage" wire:loading.attr="disabled"
                @click="$nextTick(() => window.scrollTo({top: 0, behavior: 'smooth'}))"
                class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-outline-variant/30
                       text-on-surface-variant hover:bg-primary hover:text-white hover:border-primary
                       transition-all duration-150 cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
        </button>
    @endif

    {{-- Halaman --}}
    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="inline-flex items-center justify-center w-9 h-9 text-sm text-on-surface-variant/50">
                &hellip;
            </span>
        @endif

        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl
                                 bg-primary text-white text-sm font-bold shadow-sm">
                        {{ $page }}
                    </span>
                @else
                    <button wire:click="gotoPage({{ $page }})"
                            @click="$nextTick(() => window.scrollTo({top: 0, behavior: 'smooth'}))"
                            class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-outline-variant/30
                                   text-on-surface-variant text-sm hover:bg-primary hover:text-white hover:border-primary
                                   transition-all duration-150 cursor-pointer">
                        {{ $page }}
                    </button>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <button wire:click="nextPage" wire:loading.attr="disabled"
                @click="$nextTick(() => window.scrollTo({top: 0, behavior: 'smooth'}))"
                class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-outline-variant/30
                       text-on-surface-variant hover:bg-primary hover:text-white hover:border-primary
                       transition-all duration-150 cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
        </button>
    @else
        <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl
                     text-on-surface-variant/30 cursor-not-allowed">
            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
        </span>
    @endif

</nav>
@endif
