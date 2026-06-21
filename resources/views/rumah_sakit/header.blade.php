<!-- TopNavBar -->
<header class="bg-white docked full-width top-0 z-50 border-b-4 border-primary grid grid-cols-3 lg:grid-cols-4 p-4 relative gap-5 lg:gap-0">
    <img src="{{ asset('img/bg-header.png') }}" class="w-full absolute top-0 left-0 h-full object-cover -z-10 opacity-40" alt="">

    {{-- Logo RS --}}
    <div class="flex items-center">
        <img src="{{ Storage::url(current_rumahsakit()->logo) }}" class="h-12 object-contain" alt="">
    </div>

    {{-- Logo Partner --}}
    <img src="{{ asset('img/sgg.png') }}" class="h-10 lg:h-auto lg:w-44 w-auto object-contain justify-self-center" alt="">

    {{-- Cabang — mobile only (lg digantikan oleh kolom info di bawah) --}}
    <div class="lg:hidden flex flex-col items-end justify-center gap-1">
        <span class="text-[9px] font-bold text-primary uppercase tracking-widest flex items-center gap-1">
            <span class="material-symbols-outlined text-[12px]">location_on</span>Cabang
        </span>
        <select onchange="window.location.href='/' + this.value"
                class="text-[10px] text-primary border border-primary/40 rounded-lg
                       px-2 py-1 outline-none uppercase cursor-pointer bg-white
                       focus:ring-2 focus:ring-primary/20 transition-all">
            @foreach ($daftarRS as $rs)
                <option class="text-slate-900 bg-white uppercase" value="{{ $rs->slug }}"
                    @if($rs->id == current_rumahsakit()->id) selected @endif>
                    {{ $rs->lokasi }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Emergency + Hotline + Cabang — desktop only --}}
    <div class="hidden lg:flex justify-around col-span-2">
        @if($currentRumahSakit->no_emergency)
        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $currentRumahSakit->no_emergency) }}"
           class="group flex items-center gap-2.5 bg-red-50 hover:bg-red-100
                  border border-red-200 hover:border-red-300 rounded-xl
                  pl-1.5 pr-3 py-1.5 shadow-sm hover:shadow-md
                  transition-[background-color,border-color,box-shadow]">
            <span class="material-symbols-outlined text-white bg-red-600 p-2 rounded-lg shrink-0 shadow-sm shadow-red-600/40">emergency</span>
            <div class="text-left">
                <p class="text-[11px] text-red-700 font-bold tracking-wide">EMERGENCY</p>
                <p class="text-xs text-red-900 font-semibold group-hover:underline">{{ $currentRumahSakit->no_emergency }}</p>
            </div>
        </a>
        @endif
        @if($currentRumahSakit->no_hotline)
        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $currentRumahSakit->no_hotline) }}"
           class="group flex items-center gap-2.5 bg-green-50 hover:bg-green-100
                  border border-green-200 hover:border-green-300 rounded-xl
                  pl-1.5 pr-3 py-1.5 shadow-sm hover:shadow-md
                  transition-[background-color,border-color,box-shadow]">
            <span class="material-symbols-outlined text-white bg-green-600 p-2 rounded-lg shrink-0 shadow-sm shadow-green-600/40">call</span>
            <div class="text-left">
                <p class="text-[11px] text-green-700 font-bold tracking-wide">HOTLINE</p>
                <p class="text-xs text-green-900 font-semibold group-hover:underline">{{ $currentRumahSakit->no_hotline }}</p>
            </div>
        </a>
        @endif
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-white bg-primary p-2 rounded-xl">location_on</span>
            <div>
                <p class="text-xs text-primary font-semibold">CABANG</p>
                <select onchange="window.location.href='/' + this.value"
                        class="w-full text-xs px-3 py-1 bg-white/10 border border-primary rounded-xl
                               text-primary focus:ring-2 focus:ring-white/20 outline-none
                               appearance-none cursor-pointer transition-all shadow-sm
                               hover:bg-white/15 uppercase">
                    @foreach ($daftarRS as $rs)
                        <option class="text-slate-900 bg-white uppercase" value="{{ $rs->slug }}"
                            @if($rs->id == current_rumahsakit()->id) selected @endif>
                            RSUSM {{ $rs->lokasi }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</header>
