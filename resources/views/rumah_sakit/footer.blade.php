<footer class="bg-primary/15 px-10 py-20 md:py-22 lg:py-24 flex flex-col lg:grid lg:grid-cols-5 gap-5">
    <div class="lg:col-span-2">
        <img src="{{ Storage::url($currentRumahSakit->logo) }}" class="w-full md:w-52 lg:w-64" alt="">
        <p class="mt-2 text-on-surface text-sm">{{ $currentRumahSakit->alamat }}</p>

        {{-- Sosial Media --}}
        @php $sosialMedia = $kontakRumahSakit->where('kategori', 'SOSIAL MEDIA'); @endphp
        @if($sosialMedia->isNotEmpty())
            <div class="flex items-center gap-2 mt-4 mb-5">
                @foreach($sosialMedia as $kontak)
                    <a href="{{ $kontak->link ?? '#' }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       title="{{ $kontak->label }}"
                       class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center
                              text-primary hover:bg-primary hover:text-white transition-colors shrink-0">
                        @if($kontak->logo)
                            <span class="size-5 flex items-center justify-center">{!! $kontak->logo !!}</span>
                        @elseif($kontak->gambar)
                            <img src="{{ Storage::url($kontak->gambar) }}"
                                             alt="{{ $kontak->label }}"
                                             class="w-10 h-10 object-contain">
                        @else
                            <span class="material-symbols-outlined text-[18px]">share</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @else
            <div class="mb-5"></div>
        @endif

        @if($currentRumahSakit->lokasi_google_map)
            <div class="mt-3 rounded-lg overflow-hidden" style="height:220px;">
                <style>.map-wrapper iframe { width:100% !important; height:100% !important; border:0; display:block; }</style>
                <div class="map-wrapper" style="width:100%;height:100%;">
                    {!! $currentRumahSakit->lokasi_google_map !!}
                </div>
            </div>
        @endif
    </div>
    <div class="mt-5 lg:mt-0 lg:col-span-3 lg:border-l-2 border-primary/50 lg:pl-4">
        <p class="font-semibold text-xl lg:text-2xl text-on-surface mb-4">Hubungi Kami</p>

        @php
            $kontakTampil = $kontakRumahSakit->where('kategori', '!=', 'SOSIAL MEDIA');
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach($kontakTampil as $kontak)
            @php $hasLink = ! empty($kontak->link); @endphp

            @if($hasLink)
            <a href="{{ $kontak->link }}" target="_blank" rel="noopener noreferrer"
               class="group flex items-center gap-3 px-3 py-3 rounded-xl
                      bg-white/60 hover:bg-primary/8 border border-primary/10 hover:border-primary/30
                      transition-all duration-150 min-w-0">
            @else
            <div class="flex items-center gap-3 px-3 py-3 rounded-xl
                        bg-white/40 border border-primary/8 min-w-0">
            @endif

                {{-- Ikon --}}
                <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center shrink-0
                            @if($hasLink) group-hover:bg-primary/20 @endif transition-colors">
                    @if($kontak->logo)
                        <span class="size-6 text-primary flex items-center justify-center">
                            {!! $kontak->logo !!}
                        </span>
                    @elseif($kontak->gambar)
                        <img src="{{ Storage::url($kontak->gambar) }}"
                            alt="{{ $kontak->label }}"
                            class="w-10 h-10 object-contain">
                    @else
                        <span class="material-symbols-outlined text-primary text-[20px]">contact_phone</span>
                    @endif
                </div>

                {{-- Label + Value --}}
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide leading-tight">
                        {{ $kontak->label }}
                    </p>
                    <p class="text-base font-medium text-on-surface leading-snug truncate
                              @if($hasLink) group-hover:text-primary @endif transition-colors mt-0.5">
                        {{ $kontak->value }}
                    </p>
                </div>

                {{-- Panah jika ada link --}}
                @if($hasLink)
                <span class="material-symbols-outlined text-primary/40 group-hover:text-primary
                             group-hover:translate-x-0.5 text-[18px] shrink-0
                             transition-all duration-150">arrow_forward</span>
                @endif

            @if($hasLink)
            </a>
            @else
            </div>
            @endif

            @endforeach
        </div>
    </div>
</footer>