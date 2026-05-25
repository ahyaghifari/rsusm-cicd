<div>
    <div id="hero" class="relative px-10 py-16 bg-primary">
        <h1 class="text-center text-4xl font-bold text-white">Dokter Kami</h1>
    </div>

    <!-- filter -->
    <div class="bg-white border-2 border-primary shadow-lg -translate-y-5 p-4 rounded-xl w-9/12 mx-auto grid grid-cols-2 gap-5">
        <div class="relative">
            <input type="text" class="peer py-2.5 sm:py-3 px-4 ps-11 block w-full bg-gray-100 border-transparent rounded-lg sm:text-sm text-foreground placeholder:text-muted-foreground-1 focus:bg-layer focus:border-primary focus:ring-2 focus:ring-primary disabled:opacity-50 disabled:pointer-events-none focus:outline-0 focus:bg-transparent text-gray-700" placeholder="Cari Nama Dokter" wire:model.live.debounce.300ms="search">
            <div class="absolute inset-y-0 inset-s-0 flex items-center pointer-events-none ps-4 peer-disabled:opacity-50 peer-disabled:pointer-events-none">
                <svg class="shrink-0 size-4 text-primary" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
        </div>

        <div class="w-full flex flex-col items-start">
                    <div class="relative w-full">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">stethoscope</span>
                        <select
                            wire:model.live="spesialis"
                            class="w-full pl-10 pr-4 py-2.5 text-gray-700 bg-gray-100 border border-none rounded-lg text-body-md sm:text-sm font-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-none appearance-none cursor-pointer">
                            <option value="">- Semua Spesialis -</option>
                            @foreach ($data_spesialis as $s)
                                <option value="{{ $s->slug }}">{{ $s->nama }}</option>
                            @endforeach
                        </select>
                        <span
                            class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-primary pointer-events-none">expand_more</span>
                    </div>
                </div>
    </div>

    <!-- dokter -->
    <div class="grid grid-cols-2 gap-8 mx-20 mt-5">
        @foreach ($dokter as $d)
        <a wire:navigate href="{{ route('rumahsakit.dokter_show', [$rumahsakit->slug, $d->slug]) }}" class="cursor-pointer">
            <div class="bg-white rounded-xl p-5 flex hover:shadow-lg hover:scale-105 shadow-primary/10 transition-all duration-300 group">
                <div class="shrink-0 w-44 h-44 rounded-lg overflow-hidden bg-slate-200">
                    <img src="{{ $d->foto }}" alt="{{ $d->nama }}" class="w-full h-full object-cover group-hover:scale-105 transition-all duration-300 group-hover:shadow-lg">
                </div>
                <div class="ml-5 flex-1">
                    <p class="text-white text-xs w-fit rounded-full my-2 px-3 py-1 bg-linear-to-r from-yellow-500 to-amber-600 font-light">{{ $d->spesialis->nama }}</p>
                    <h2 class="text-lg font-bold text-primary">{{ $d->nama }}</h2>
                    <p class="text-slate-600 text-sm mt-2">{{ \Illuminate\Support\Str::limit($d->deskripsi, 100) }}</p>
                    <button
                        class="w-full sm:w-auto self-start border border-tertiary text-tertiary px-3 py-1 rounded-lg text-label-md font-label-md flex items-center gap-2mx-auto cursor-pointer text-sm mt-4 group-hover:bg-tertiary group-hover:text-white transition-all duration-300">
                        Lihat Jadwal Praktek
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </button>
                </div>
            </div>
        </a>
        @endforeach
    </div>

</div>
