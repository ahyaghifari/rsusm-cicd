<div>
    <x-page-hero title="Rawat Inap" />

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
