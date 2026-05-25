<footer class="bg-primary/15 px-10 py-24 grid grid-cols-6 gap-5">
    <div class="col-span-2">
        <img src="{{ asset('img/syifa-medika-banjarbaru.png') }}" class="w-52" alt="">
        <p class="mt-2 text-on-surface text-sm mb-5"> Jl. RO Ulin No.93, Loktabat Selatan, Kec. Banjarbaru Selatan, Kota Banjar Baru, Kalimantan Selatan, 70712</p>
        
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3985.83410305978!2d115.39570239999999!3d-2.5607591!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2de569004ef9aa4f%3A0xbcf05d96238cf8b7!2sRSU%20Syifa%20Medika%20Barabai!5e0!3m2!1sid!2sid!4v1779529871230!5m2!1sid!2sid" width="350" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
    <div class="col-span-3 border-l border-primary/50 pl-4">
        <p class="font-semibold text-xl text-on-surface">Hubungi Kami</p>
        <table class="border-separate border-spacing-2 mt-4 w-full text-sm">
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
    <div class="border-l border-primary/50 pl-4">
        <p class="font-semibold text-xl text-on-surface">Tentang Kami</p>
        <div class="mt-4 flex flex-col gap-y-2">
            <a href="">Tentang Perusahaan</a>
            <a href="">Partner Kami</a>
        </div>
    </div>
</footer>