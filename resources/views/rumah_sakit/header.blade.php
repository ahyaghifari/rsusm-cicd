<!-- TopNavBar -->
<header class="bg-white docked full-width top-0 z-50 border-b-4 border-primary grid grid-cols-2 md:grid-cols-4 p-4 relative">
    <img src="{{ asset('img/bg-header.png') }}" class="w-full absolute top-0 left-0 h-full object-cover -z-10 opacity-70 blur-xs" alt="">
    <div
        class="flex justify-between items-center w-full px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
        <div class="text-headline-md font-headline-md font-bold text-primary">
            <img src="{{ asset('img/syifa-medika-banjarbaru.png') }}" class="w-44" alt="">
        </div>
    </div>
    <img src="{{ asset('img/sgg.png') }}" class="w-44" alt="">
    <div class="hidden lg:flex justify-around col-span-2">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-white bg-red-600 p-2 ">emergency</span>  
            <div>
                <p class="text-xs text-red-600 font-semibold">EMERGENCY</p>
                <p class="text-xs">0811 5144 460</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-white bg-green-600 p-2 ">call</span>  
            <div>
                <p class="text-xs text-green-600 font-semibold">HOTLINE</p>
                <p class="text-xs">0811 5144 460</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-white bg-primary p-2 ">location_on</span>  
            <div>
                <p class="text-xs text-primary font-semibold">CABANG</p>
                <form action="">
                    <select id="promo-hospital-filter"
                        class="w-full text-xs px-3 py-1 bg-white/10 border border-primary rounded-xl font-body-md text-primary focus:ring-2 focus:ring-white/20 outline-none appearance-none cursor-pointer transition-all shadow-sm hover:bg-white/15">
                        <option class="text-slate-900 bg-white" value="banjarbaru">RSUSM BANJARBARU</option>
                        <option class="text-slate-900 bg-white" value="barabai">RSUSM BARABAI</option>
                    </select>
                </form>
            </div>
        </div>
    </div>
</header>
