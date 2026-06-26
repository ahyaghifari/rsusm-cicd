{{-- resources/views/filament/pages/generate-poster.blade.php --}}
<x-filament-panels::page>

{{-- SortableJS CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>

{{-- SortableJS Alpine component (always available) --}}
<script>
function sortablePoli() {
    return {
        sortableInstance: null,
        init() {
            this.$nextTick(() => {
                const el = document.getElementById('poli-sortable');
                if (!el) return;

                if (this.sortableInstance) {
                    this.sortableInstance.destroy();
                    this.sortableInstance = null;
                }

                if (typeof Sortable === 'undefined') return;

                this.sortableInstance = Sortable.create(el, {
                    handle: 'li',
                    animation: 150,
                    onEnd: (evt) => {
                        @this.reorderPoli(evt.oldIndex, evt.newIndex);
                    },
                });
            });
        },
    };
}
</script>

{{-- Listener: open preview in new tab --}}
<div x-data x-on:open-preview.window="window.open($event.detail.url, '_blank', 'width=540,height=960,scrollbars=yes,resizable=yes')"></div>

<div class="space-y-6">

    {{-- ── Form Section ──────────────────────────────────────────────────────── --}}
    <form wire:submit.prevent="generate">
        {{ $this->form }}

        {{-- ── Step 3: Sortable & Toggle Poli ──────────────────────────────── --}}
        @if (count($this->poli_list))
        <div
            x-data="sortablePoli()"
            x-init="init()"
            wire:key="poli-sortable-{{ count($this->poli_list) }}"
            class="mt-6 fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
        >
            <div class="fi-section-header px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    3. Urutkan & Sembunyikan Poliklinik
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    Drag untuk mengubah urutan. Toggle untuk tampilkan/sembunyikan pada poster.
                </p>
            </div>

            <div class="fi-section-content px-6 py-4">
                <ul id="poli-sortable" class="space-y-2">
                    @foreach ($this->poli_list as $index => $poli)
                    <li
                        class="flex items-center gap-3 rounded-lg border bg-gray-50 px-4 py-3 dark:bg-gray-800 dark:border-gray-700 cursor-grab active:cursor-grabbing"
                        data-index="{{ $index }}"
                    >
                        {{-- Drag handle --}}
                        <span class="text-gray-400 dark:text-gray-500 select-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 8h16M4 16h16"/>
                            </svg>
                        </span>

                        {{-- Toggle visible --}}
                        <button
                            type="button"
                            wire:click="togglePoli({{ $index }})"
                            class="shrink-0 w-9 h-5 rounded-full transition-colors focus:outline-none
                                   {{ $poli['visible'] ? 'bg-primary-500' : 'bg-gray-300 dark:bg-gray-600' }}"
                            title="{{ $poli['visible'] ? 'Sembunyikan' : 'Tampilkan' }}"
                        >
                            <span class="block w-4 h-4 rounded-full bg-white shadow transform transition-transform mx-0.5
                                         {{ $poli['visible'] ? 'translate-x-4' : 'translate-x-0' }}">
                            </span>
                        </button>

                        {{-- Nama poli --}}
                        <span class="flex-1 text-sm font-medium {{ $poli['visible'] ? 'text-gray-900 dark:text-white' : 'text-gray-400 line-through' }}">
                            {{ $poli['nama'] }}
                        </span>

                        {{-- Urutan badge --}}
                        <span class="text-xs text-gray-400 tabular-nums">
                            #{{ $index + 1 }}
                        </span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        {{-- ── Action Buttons ──────────────────────────────────────────────── --}}
        <div class="mt-6 flex gap-3">
            <x-filament::button
                type="button"
                color="gray"
                wire:click="previewPoster"
                wire:loading.attr="disabled"
                icon="heroicon-o-eye"
            >
                Preview
            </x-filament::button>

            <x-filament::button
                type="submit"
                wire:loading.attr="disabled"
                icon="heroicon-o-arrow-down-tray"
            >
                <span wire:loading.remove>Download PNG</span>
                <span wire:loading>Generating...</span>
            </x-filament::button>
        </div>
    </form>

</div>

</x-filament-panels::page>