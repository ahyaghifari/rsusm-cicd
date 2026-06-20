<div>
    <x-page-hero title="Rawat Inap" />

    <div class="mt-5 w-10/12 mx-auto flex justify-center">
        <a wire:navigate href="{{ rumahsakit_route('rumahsakit.ketersediaan_rawat_inap') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-primary
                  border border-primary/30 hover:bg-primary/8 px-4 py-2 rounded-full transition-colors">
            <span class="material-symbols-outlined text-[18px]">monitor_heart</span>
            Cek Ketersediaan Kamar
        </a>
    </div>

    <div class="mt-5 w-10/12 mx-auto">
        @if($hasGedung)
            @foreach($gedungs as $gedung)

                <section class="mt-20">
                    <div class="grid grid-cols-5 items-center gap-5">
                        <div class="h-0.5 bg-primary/50 w-full"></div>
                        <h2 class="text-4xl text-center font-bold col-span-3">Gedung <span class="text-primary">{{ $gedung->nama }}</span></h2>
                        <div class="h-0.5 bg-primary/50 w-full"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10">

                        @foreach($gedung->rawatInap as $room)
                                <div data-aos="fade-up"> 
                                    <x-rawat-inap :rawat-inap="$room" />
                                </div>
                        @endforeach

                    </div>
                </section>

            @endforeach
        
        @else
                @foreach($rawatInap as $room)

                    <div data-aos="fade-up"> 
                        <x-rawat-inap :rawat-inap="$room" />
                    </div>

                @endforeach
        @endif
    </div>
</div>
