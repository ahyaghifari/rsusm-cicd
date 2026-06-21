<div>
    <x-page-hero title="Dokter Kami" subtitle="Temukan dokter spesialis terbaik kami untuk kebutuhan kesehatan Anda." />

    {{-- Filter --}}
    <div class="relative z-20 bg-white border-2 border-primary shadow-lg -translate-y-5
                p-4 rounded-xl w-10/12 md:w-9/12 mx-auto
                grid grid-cols-1 sm:grid-cols-2 gap-3">

        {{-- Cari nama --}}
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="size-4 text-primary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   class="w-full pl-10 pr-4 py-2.5 bg-gray-100 border-transparent rounded-lg text-sm
                          text-gray-700 placeholder:text-gray-400
                          focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/30
                          outline-none transition"
                   placeholder="Cari nama dokter...">
        </div>

        {{-- Filter spesialis (searchable) --}}
        @include('rumah_sakit.pages._searchable-select', [
            'property'     => 'spesialis',
            'options'      => $data_spesialis->map(fn($s) => ['value' => $s->slug, 'label' => $s->nama])->values()->toArray(),
            'placeholder'  => '— Semua Spesialis —',
            'currentValue' => $spesialis,
            'wrapperClass' => 'w-full',
        ])
    </div>

    {{-- Daftar Dokter --}}
    <div class="w-11/12 lg:w-10/12 mx-auto pb-12 mt-12">

        @if($dokter->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-20 h-20 rounded-full bg-surface-container flex items-center justify-center mb-5">
                    <span class="material-symbols-outlined text-4xl text-on-surface-variant/50">person_search</span>
                </div>
                <p class="font-semibold text-on-surface-variant text-lg">Dokter tidak ditemukan</p>
                <p class="text-sm text-on-surface-variant/60 mt-1.5">Coba ubah filter atau kata kunci pencarian</p>
            </div>

        @else
            {{-- Info hasil --}}
            <div class="flex items-center gap-2 mb-6">
                <div class="h-px flex-1 bg-outline-variant/30"></div>
                <p class="text-xs text-on-surface-variant shrink-0">
                    <span class="font-semibold text-primary">{{ $dokter->total() }}</span>
                    dokter ditemukan
                </p>
                <div class="h-px flex-1 bg-outline-variant/30"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($dokter as $d)
                    <x-dokter-card :dokter="$d" :rumahsakit-slug="$rumahsakit->slug" :delay="$loop->index * 60" />
                @endforeach
            </div>

            {{-- Pagination kustom --}}
            <div class="mt-10 flex flex-col items-center gap-3">
                {{ $dokter->links('components.portal-pagination') }}
                @if($dokter->hasPages())
                    <p class="text-xs text-on-surface-variant/60">
                        Halaman {{ $dokter->currentPage() }} dari {{ $dokter->lastPage() }}
                        &nbsp;·&nbsp; {{ $dokter->total() }} dokter
                    </p>
                @endif
            </div>

        @endif
    </div>

</div>
