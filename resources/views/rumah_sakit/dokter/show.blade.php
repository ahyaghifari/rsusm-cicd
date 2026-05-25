<div class="relative">
    <img src="{{ asset('img/bg-header.png') }}" class="h-full w-full object-cover blur-xs opacity-10 absolute -z-10" alt="">
    <div id="hero" class="relative px-10 py-16 bg-primary">
        <h1 class="text-center text-4xl font-bold text-white">Profil Dokter</h1>
    </div>

    <div class="w-11/12 mt-8 mx-auto">
        <div class="p-5 grid grid-cols-3  h-fit gap-8">
                <img src="{{ Storage::url($dokter->foto) }}" alt="{{ $dokter->nama }}" class="w-full h-96 object-contain">
            <div class="col-span-2">
                <h1 class="text-4xl font-bold text-primary">{{ $dokter->nama }}</h1>
                <p class=" font-bold text-white bg-linear-to-r from-yellow-500 to-amber-600 w-fit rounded-full px-3 py-1 mt-4">{{ $dokter->spesialis->nama }}</p>
                <hr class="my-5 border-gray-300">
                <p class=" text-gray-700 mt-5 leading-8 text-justify">{{ $dokter->deskripsi }}</p>
            </div>
        </div>

        {{-- jadwal prakter --}}
        <h1 class="text-on-surface text-3xl font-semibold mt-8">Jadwal Praktek</h1>
        <table class="w-full mt-5">
            <tr>
                @foreach($dokter->jadwalPraktek as $jp)
                    <td class="bg-primary text-white text-center p-2 font-semibold">{{ $jp->hari }}</td>
                @endforeach
            </tr>
            <tr>
                @foreach($dokter->jadwalPraktek as $jp)
                    @if($jp->libur)
                        <td class="p-3 text-center border border-gray-300 text-red-600 bg-red-200 font-bold">-</td>
                    @elseif($jp->sesuai_perjanjian)
                        <td class="p-3 text-center border border-gray-300 text-green-600 bg-green-200 text-sm font-bold italic">Sesuai Perjanjian</td>
                    @else
                        <td class="p-3 text-center border border-gray-300 font-medium">
                            {{ \Carbon\Carbon::parse($jp->waktu_mulai)->format('H:i') }}
                            -
                            {{ $jp->waktu_selesai
                                ? \Carbon\Carbon::parse($jp->waktu_selesai)->format('H:i')
                                : 'Selesai'
                            }}
                        </td>
                    @endif
                @endforeach
            </tr>
        </table>

        {{-- pendidikan pelatihan --}}
        <div class="grid grid-cols-2 gap-8 mt-16">
            {{-- pendidikan --}}
            <div>
                <div class="flex items-center w-full">
                    <h1 class="text-on-surface text-xl font-semibold">Pendidikan</h1>
                    <div class="w-full h-0.5 bg-on-surface/10 ml-2"></div>
                </div>
                <div class="block list-disc mt-2 pl-4">
                    {!! $dokter->pendidikan !!}
                </div>
            </div>

            {{-- pelatihan --}}
            <div>
                <div class="flex items-center w-full">
                    <h1 class="text-on-surface text-xl font-semibold">Pelatihan</h1>
                    <div class="w-full h-0.5 bg-on-surface/10 ml-2"></div>
                </div>
                <div class="block list-disc mt-2 pl-4">
                    {!! $dokter->pelatihan !!}
                </div>
            </div>
        </div>
    </div>

  
</div>
