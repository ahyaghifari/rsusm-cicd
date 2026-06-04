<!-- TopNavBar Portal -->
<header class="bg-white sticky top-0 z-50 border-b-4 border-primary shadow-sm">
    <div class="flex items-center justify-between px-4 md:px-8 py-3 max-w-7xl mx-auto">

        {{-- Logo kiri: Syifa Medika --}}
        <a href="/" class="shrink-0">
            @if(file_exists(public_path('img/syifa-medika.png')))
                <img src="{{ asset('img/syifa-medika.png') }}"
                     alt="RSU Syifa Medika"
                     class="h-12 md:h-14 w-auto object-contain">
            @else
                <div class="text-primary font-bold leading-tight">
                    <span class="block text-xs text-gray-600 font-semibold uppercase tracking-wide">Rumah Sakit Umum</span>
                    <span class="block text-lg md:text-xl">SYIFA MEDIKA</span>
                </div>
            @endif
        </a>

        {{-- Logo kanan: SGG --}}
        <a href="/" class="shrink-0">
            @if(file_exists(public_path('img/sgg.png')))
                <img src="{{ asset('img/sgg.png') }}"
                     alt="SGG"
                     class="h-12 md:h-14 w-auto object-contain">
            @endif
        </a>

    </div>
</header>
