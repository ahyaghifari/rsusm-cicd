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
                {{-- Gradient border wrapper — 1px amber/emas halus --}}
                <div class="p-[1.5px] rounded-2xl animate-fade-in
                             bg-outline-variant/25
                             hover:bg-linear-to-br hover:from-amber-400 hover:via-primary hover:to-amber-300
                             shadow-sm hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-400"
                     style="animation-delay: {{ $loop->index * 60 }}ms">
                <a wire:navigate href="{{ route('rumahsakit.dokter_show', [$rumahsakit->slug, $d->slug]) }}"
                   class="group bg-white rounded-2xl overflow-hidden flex flex-col h-full">

                    {{-- Foto dengan overlay --}}
                    <div class="relative overflow-hidden h-72 bg-gray-100 shrink-0">
                        <img src="{{ Storage::url($d->foto) }}" alt="{{ $d->nama }}"
                             class="w-full h-full object-contain object-bottom
                                    group-hover:scale-105 transition-transform duration-700"
                             loading="lazy">

                        {{-- Badge spesialis (pojok kiri bawah foto) --}}
                        <div class="absolute bottom-0 left-0 right-0 px-4 pb-3">
                            <span class="inline-flex items-center bg-linear-to-r from-amber-500 to-amber-400
                                         text-white text-[11px] font-bold uppercase tracking-wider
                                         px-2.5 py-1 rounded-full shadow-sm max-w-full truncate">
                                {{ $d->namaSpesialis() }}
                            </span>
                        </div>

                    </div>

                    {{-- Info --}}
                    <div class="p-5 flex flex-col flex-1">

                        {{-- Nama --}}
                        <h2 class="font-bold text-on-surface text-base md:text-lg leading-snug mb-2
                                   group-hover:text-primary transition-colors duration-200">
                            {{ $d->nama }}
                        </h2>

                        {{-- Deskripsi --}}
                        @if($d->deskripsi)
                            <p class="text-sm text-on-surface-variant/80 leading-relaxed line-clamp-2 flex-1">
                                {{ \Illuminate\Support\Str::limit(strip_tags($d->deskripsi), 120) }}
                            </p>
                        @else
                            <div class="flex-1"></div>
                        @endif

                        {{-- Footer --}}
                        <div class="flex items-center justify-between mt-4 pt-3 border-t border-outline-variant/20">
                            <span class="text-xs text-on-surface-variant uppercase tracking-widest font-semibold">
                                {{ $d->spesialis?->nama ? 'SPESIALIS' : 'UMUM';}}
                            </span>
                            <span class="inline-flex items-center gap-1 text-sm font-bold text-primary
                                         group-hover:gap-2 transition-all duration-200">
                                Lihat Profil
                                <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                            </span>
                        </div>
                    </div>
                </a>
                </div>{{-- end gradient border wrapper --}}
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
