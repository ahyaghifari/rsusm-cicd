<div>
    <x-page-hero title="Dokter Kami" subtitle="Temukan dokter spesialis terbaik kami untuk kebutuhan kesehatan Anda." />

    {{-- Filter --}}
    <div class="bg-white border-2 border-primary shadow-lg -translate-y-5
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
            'options'      => $data_spesialis->map(fn($s) => ['value' => (string)$s->id, 'label' => $s->nama])->values()->toArray(),
            'placeholder'  => '— Semua Spesialis —',
            'currentValue' => $spesialis,
            'wrapperClass' => 'w-full',
        ])
    </div>

    {{-- Daftar Dokter --}}
    <div class="w-full md:w-10/12 mx-auto px-4 md:px-0 mt-2 pb-8">
        @if($dokter->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">person_search</span>
                <p class="font-semibold text-on-surface-variant">Dokter tidak ditemukan</p>
                <p class="text-sm text-on-surface-variant/70 mt-1">Coba ubah filter atau kata kunci pencarian</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                @foreach ($dokter as $d)
                <a wire:navigate href="{{ route('rumahsakit.dokter_show', [$rumahsakit->slug, $d->slug]) }}"
                   class="group flex flex-col sm:flex-row bg-white rounded-2xl overflow-hidden
                          shadow-sm hover:shadow-xl hover:-translate-y-0.5
                          border border-outline-variant/20 transition-all duration-300">

                    {{-- Foto --}}
                    <div class="w-full sm:w-40 shrink-0 bg-slate-100
                                h-52 sm:h-auto overflow-hidden">
                        <img src="{{ Storage::url($d->foto) }}" alt="{{ $d->nama }}"
                             class="w-full h-full object-contain
                                    group-hover:scale-105 transition-transform duration-500">
                    </div>

                    {{-- Konten --}}
                    <div class="flex flex-col justify-between p-4 flex-1">
                        <div>
                            <span class="inline-block text-white text-xs rounded-full px-3 py-1 mb-2
                                         bg-linear-to-r from-yellow-500 to-amber-600 font-medium">
                                {{ $d->spesialis->nama }}
                            </span>
                            <h2 class="text-base md:text-lg font-bold text-primary leading-snug">
                                {{ $d->nama }}
                            </h2>
                            @if($d->deskripsi)
                                <p class="text-slate-500 text-xs md:text-sm mt-1.5 line-clamp-3 leading-relaxed">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($d->deskripsi), 100) }}
                                </p>
                            @endif
                        </div>
                        <div class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-tertiary
                                    group-hover:gap-2 transition-all duration-200">
                            Lihat Profil
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        @endif
    </div>

</div>
