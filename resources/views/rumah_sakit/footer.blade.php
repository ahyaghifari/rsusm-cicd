<footer class="bg-primary/15 px-10 py-20 md:py-22 lg:py-24 flex flex-col lg:grid lg:grid-cols-5 gap-5">
    <div class="lg:col-span-2">
        <img src="{{ Storage::url($currentRumahSakit->logo) }}" class="w-full md:w-52" alt="">
        <p class="mt-2 text-on-surface text-sm mb-5">{{ $currentRumahSakit->alamat }}</p>
        
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
        <p class="font-semibold text-lg lg:text-xl text-on-surface">Hubungi Kami</p>
        <table class="border-separate border-spacing-2 mt-4 w-full text-xs md:text-sm">
            <tbody>
                @foreach($kontakRumahSakit as $kontak)
                <tr>
                    <td>
                        @if($kontak->logo != null)
                        <span class="size-4 text-primary">
                            {!! $kontak->logo !!}
                        </span>
                        @else
                        -
                        @endif
                    </td>
                    <td>{{ $kontak->label }}</td>
                    <td>
                        @if($kontak->link != null)
                        <a href="{{ $kontak->link }}"
                                target="_blank"
                                rel="noopener noreferrer" class="text-primary hover:text-on-surface">{{ $kontak->value }} <span class="material-symbols-outlined text-base opacity-60">open_in_new</span></a>
                            @else
                        {{ $kontak->value }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</footer>