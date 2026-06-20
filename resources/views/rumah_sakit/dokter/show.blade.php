<div class="relative">
    <img src="{{ asset('img/bg-header.png') }}" class="h-full w-full object-cover blur-xs opacity-20 absolute -z-10" alt="">
    <x-page-hero title="Profil Dokter" />

    <div class="w-11/12 max-w-5xl mt-8 mx-auto pb-12">

        {{-- Foto + Info --}}
        <div class="p-4 md:p-5 grid grid-cols-1 md:grid-cols-3 h-fit gap-6 md:gap-8">
            <div class="flex justify-center md:block">
                @if($dokter->foto)
                    <img src="{{ Storage::url($dokter->foto) }}" alt="{{ $dokter->nama }}"
                         class="w-48 md:w-full max-h-80 object-contain rounded-2xl" loading="lazy">
                @else
                    <div class="w-48 md:w-full max-h-80 md:max-h-96 bg-primary/10 rounded-2xl
                                flex items-center justify-center aspect-square">
                        <span class="material-symbols-outlined text-7xl text-primary/40">person</span>
                    </div>
                @endif
            </div>
            <div class="col-span-1 md:col-span-2">
                <h1 class="text-2xl md:text-4xl font-bold text-primary leading-tight">{{ $dokter->nama }}</h1>
                <p class="font-bold text-white bg-linear-to-r from-yellow-500 to-amber-600
                          w-fit rounded-full px-3 py-1 mt-3 text-sm md:text-base">
                    {{ $dokter->namaSpesialis() }}
                </p>
                <hr class="my-4 border-gray-300">
                <p class="text-gray-700 mt-3 leading-7 md:leading-8 text-sm md:text-base text-justify">
                    {{ $dokter->deskripsi }}
                </p>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- JADWAL PRAKTEK --}}
        {{-- ============================================================ --}}
        <h2 class="text-on-surface text-xl md:text-3xl font-semibold mt-8 mb-4">Jadwal Praktek</h2>

        @if($jadwal->isEmpty())
            <div class="text-center py-12 border-2 border-dashed border-outline-variant rounded-2xl">
                <span class="material-symbols-outlined text-5xl text-outline/40 block mb-3">calendar_today</span>
                <p class="text-on-surface-variant">Belum ada jadwal praktek terdaftar.</p>
            </div>
        @else
            @php
                $warna  = '#4d51b2';
                $perHari = $jadwal->groupBy(fn ($j) => $j->hari->value);
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($perHari as $hariValue => $sesiHari)
                    @php
                        $hariLabel  = \App\Enums\Hari::from($hariValue)->getLabel();
                        $hariIniVal = ['MINGGU','SENIN','SELASA','RABU','KAMIS','JUMAT','SABTU'][now()->dayOfWeek];
                        $isToday    = $hariValue === $hariIniVal;
                    @endphp
                    <div class="rounded-xl overflow-hidden border flex flex-col"
                         style="{{ $isToday ? "border-color: {$warna}66;" : 'border-color: #e5e7eb;' }}">
                        <div class="px-4 py-2 flex items-center justify-between shrink-0"
                             style="{{ $isToday
                                 ? "background-color: {$warna}; color: white;"
                                 : "background-color: {$warna}0f; color: {$warna};" }}">
                            <span class="text-sm font-bold">{{ $hariLabel }}</span>
                            @if($isToday)
                                <span class="text-[10px] font-bold uppercase tracking-widest
                                             bg-white/20 px-2 py-0.5 rounded-full">Hari Ini</span>
                            @endif
                        </div>
                        <div class="divide-y divide-outline-variant/15 flex-1">
                            @foreach($sesiHari as $sesi)
                                @include('rumah_sakit.dokter._jadwal-row', ['sesi' => $sesi, 'warna' => $warna, 'rs' => $rs])
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @php $punyaLinkDaftar = (bool) $rs->link_pendaftaran_online; @endphp
        <div class="mt-8 flex flex-col md:flex-row gap-4 md:items-stretch">
            @if($punyaLinkDaftar)
                <a href="{{ $rs->link_pendaftaran_online }}" target="_blank"
                   class="group shrink-0 inline-flex items-center justify-center gap-2.5 px-7 py-4 rounded-2xl
                          bg-tertiary hover:bg-tertiary/90 text-on-tertiary font-bold text-base
                          shadow-lg shadow-tertiary/30 hover:shadow-xl hover:shadow-tertiary/40
                          hover:-translate-y-1 active:scale-95
                          transition-all duration-200 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform duration-200">event</span>
                    <span>Daftar Sekarang</span>
                    <span class="material-symbols-outlined text-[16px] opacity-0 group-hover:opacity-100 -ml-1 transition-all duration-200">arrow_forward</span>
                </a>
            @endif
            @include('rumah_sakit.partials._jadwal-disclaimer', ['noCenter' => $punyaLinkDaftar])
        </div>

        {{-- ============================================================ --}}
        {{-- Pendidikan & Pelatihan --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 mt-12">
            <div>
                <div class="flex items-center w-full mb-3">
                    <h2 class="text-on-surface text-lg md:text-xl font-semibold shrink-0">Pendidikan</h2>
                    <div class="w-full h-0.5 bg-on-surface/10 ml-3"></div>
                </div>
                <div class="prose prose-sm max-w-none text-gray-700 pl-1">
                    {!! $dokter->pendidikan !!}
                </div>
            </div>
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
