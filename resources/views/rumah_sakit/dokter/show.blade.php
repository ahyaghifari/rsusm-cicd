<div class="relative">
    <img src="{{ asset('img/bg-header.png') }}" class="h-full w-full object-cover blur-xs opacity-10 absolute -z-10" alt="">
    <x-page-hero title="Profil Dokter" />

    <div class="w-11/12 max-w-5xl mt-8 mx-auto pb-12">

        {{-- Foto + Info --}}
        <div class="p-4 md:p-5 grid grid-cols-1 md:grid-cols-3 h-fit gap-6 md:gap-8">
            <div class="flex justify-center md:block">
                <img src="{{ Storage::url($dokter->foto) }}" alt="{{ $dokter->nama }}"
                     class="w-48 md:w-full max-h-80 md:max-h-96 object-contain rounded-2xl">
            </div>
            <div class="col-span-1 md:col-span-2">
                <h1 class="text-2xl md:text-4xl font-bold text-primary leading-tight">{{ $dokter->nama }}</h1>
                <p class="font-bold text-white bg-linear-to-r from-yellow-500 to-amber-600
                          w-fit rounded-full px-3 py-1 mt-3 text-sm md:text-base">
                    {{ $dokter->spesialis->nama }}
                </p>
                <hr class="my-4 border-gray-300">
                <p class="text-gray-700 mt-3 leading-7 md:leading-8 text-sm md:text-base text-justify">
                    {{ $dokter->deskripsi }}
                </p>
            </div>
        </div>

        {{-- Jadwal Praktek --}}
        <h2 class="text-on-surface text-xl md:text-3xl font-semibold mt-8 mb-4">Jadwal Praktek</h2>

        @if($dokter->jadwalPraktek->isEmpty())
            <p class="text-on-surface-variant text-sm">Belum ada jadwal praktek terdaftar.</p>
        @else
            {{-- Mobile: card per hari --}}
            <div class="flex flex-col gap-2 md:hidden">
                @foreach($dokter->jadwalPraktek as $jp)
                <div class="flex items-center gap-3 rounded-xl border border-outline-variant/30 overflow-hidden">
                    <div class="bg-primary text-white text-sm font-semibold px-4 py-3 shrink-0 w-28 text-center">
                        {{ $jp->hari }}
                    </div>
                    <div class="py-2 px-3 text-sm">
                        @if($jp->libur)
                            <span class="text-red-600 font-bold">Libur</span>
                        @else
                            <span class="font-medium text-on-surface">
                                {{ \Carbon\Carbon::parse($jp->waktu_mulai)->format('H:i') }}
                                –
                                {{ $jp->waktu_selesai
                                    ? \Carbon\Carbon::parse($jp->waktu_selesai)->format('H:i')
                                    : 'Selesai' }}
                            </span>
                            @if($jp->sesuai_perjanjian)
                                <span class="block text-xs text-secondary mt-0.5">Sesuai Perjanjian</span>
                            @endif
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Desktop: tabel horizontal --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full mt-2">
                    <tr>
                        @foreach($dokter->jadwalPraktek as $jp)
                            <td class="bg-primary text-white text-center p-2 font-semibold text-sm">{{ $jp->hari }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($dokter->jadwalPraktek as $jp)
                            @if($jp->libur)
                                <td class="p-3 text-center border border-gray-300 text-red-600 bg-red-200 font-bold">-</td>
                            @else
                                <td class="p-3 text-center border border-gray-300 font-medium text-sm">
                                    {{ \Carbon\Carbon::parse($jp->waktu_mulai)->format('H:i') }}
                                    –
                                    {{ $jp->waktu_selesai
                                        ? \Carbon\Carbon::parse($jp->waktu_selesai)->format('H:i')
                                        : 'Selesai' }}
                                    @if($jp->sesuai_perjanjian)
                                        <br><span class="text-xs">Sesuai Perjanjian</span>
                                    @endif
                                </td>
                            @endif
                        @endforeach
                    </tr>
                </table>
            </div>
        @endif

        {{-- Pendidikan & Pelatihan --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 mt-12">
            {{-- Pendidikan --}}
            <div>
                <div class="flex items-center w-full mb-3">
                    <h2 class="text-on-surface text-lg md:text-xl font-semibold shrink-0">Pendidikan</h2>
                    <div class="w-full h-0.5 bg-on-surface/10 ml-3"></div>
                </div>
                <div class="prose prose-sm max-w-none text-gray-700 pl-1">
                    {!! $dokter->pendidikan !!}
                </div>
            </div>

            {{-- Pelatihan --}}
            <div>
                <div class="flex items-center w-full mb-3">
                    <h2 class="text-on-surface text-lg md:text-xl font-semibold shrink-0">Pelatihan</h2>
                    <div class="w-full h-0.5 bg-on-surface/10 ml-3"></div>
                </div>
                <div class="prose prose-sm max-w-none text-gray-700 pl-1">
                    {!! $dokter->pelatihan !!}
                </div>
            </div>
        </div>

    </div>
</div>
