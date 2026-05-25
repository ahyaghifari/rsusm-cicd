<div>
    {{-- Hero --}}
    <div id="hero" class="relative px-10 py-16">
        <h1 class="text-center text-4xl font-bold bg-primary text-white p-3 w-fit mx-auto">Partner & Rekanan Kami</h1>
    </div>

    {{-- Search --}}
    <div class="w-10/12 mx-auto my-10">
        <div class="relative max-w-md mx-auto">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Cari nama asuransi atau partner kami ..."
                class="w-full pl-12 pr-4 py-3 border border-outline-variant rounded-xl bg-white text-on-surface placeholder-on-surface-variant/60 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition"
            >
        </div>
    </div>

    <div class="w-10/12 mx-auto">

        {{-- Asuransi --}}
        @if($partner_asuransi->count() > 0)
        <div class="mb-14">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-1 h-7 bg-primary rounded-full"></div>
                <h2 class="text-on-surface font-bold text-2xl">Asuransi</h2>
                <span class="ml-1 bg-primary/10 text-primary text-xs font-semibold px-2.5 py-1 rounded-full">
                    {{ $partner_asuransi->count() }} mitra
                </span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                @foreach($partner_asuransi as $partner)
                <div data-aos="zoom-in-up" class="bg-white border border-outline-variant/30 rounded-2xl px-5 py-5 flex flex-col items-center justify-center gap-3 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 min-h-[130px]">
                    @if($partner->logo)
                        <img src="{{ Storage::url($partner->logo) }}" alt="{{ $partner->nama }}" class="h-12 object-contain">
                    @else
                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-primary text-2xl">shield</span>
                        </div>
                    @endif
                    <p class="text-on-surface text-center text-sm font-semibold leading-tight">{{ $partner->nama }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Perusahaan Rekanan --}}
        @if($partner_perusahaan->count() > 0)
        <div class="mb-14">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-1 h-7 bg-secondary rounded-full"></div>
                <h2 class="text-on-surface font-bold text-2xl">Perusahaan Rekanan</h2>
                <span class="ml-1 bg-secondary/10 text-secondary text-xs font-semibold px-2.5 py-1 rounded-full">
                    {{ $partner_perusahaan->count() }} mitra
                </span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                @foreach($partner_perusahaan as $partner)
                <div data-aos="zoom-in-up" class="bg-white border border-outline-variant/30 rounded-2xl px-5 py-5 flex flex-col items-center justify-center gap-3 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 min-h-[130px]">
                    @if($partner->logo)
                        <img src="{{ Storage::url($partner->logo) }}" alt="{{ $partner->nama }}" class="h-12 object-contain">
                    @else
                        <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-secondary text-2xl">business</span>
                        </div>
                    @endif
                    <p class="text-on-surface text-center text-sm font-semibold leading-tight">{{ $partner->nama }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Kosong --}}
        @if($partner_asuransi->count() === 0 && $partner_perusahaan->count() === 0)
        <div class="text-center py-20">
            <div class="w-16 h-16 bg-surface-container rounded-2xl flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-on-surface-variant text-3xl">search_off</span>
            </div>
            <p class="text-on-surface-variant font-medium">Tidak ada partner yang ditemukan untuk "<span class="text-on-surface">{{ $search }}</span>"</p>
        </div>
        @endif

    </div>
</div>
