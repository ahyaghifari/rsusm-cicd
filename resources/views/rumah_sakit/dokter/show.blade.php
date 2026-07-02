@push('seo_schema')
<script type="application/ld+json">{!! json_encode(array_filter([
    '@context'    => 'https://schema.org',
    '@type'       => 'Physician',
    'name'        => $dokter->nama,
    'url'         => request()->url(),
    'image'       => $dokter->foto ? asset('storage/' . $dokter->foto) : null,
    'description' => $dokter->deskripsi ? \Illuminate\Support\Str::limit(strip_tags($dokter->deskripsi), 200) : null,
    'medicalSpecialty' => $dokter->spesialis?->nama,
    'worksFor'    => [
        '@type' => 'MedicalBusiness',
        'name'  => $rs->nama,
        '@id'   => url('/' . $rs->slug),
    ],
]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

<div class="relative">
    <img src="{{ asset('img/bg-header.png') }}" class="h-full w-full object-cover blur-xs opacity-20 absolute -z-10" alt="">
    <x-page-hero title="Profil Dokter" />

    <div class="w-11/12 max-w-5xl mt-8 mx-auto pb-16">

        {{-- ============================================================ --}}
        {{-- IDENTITAS DOKTER — foto & teks dibuat sama tinggi lewat grid  --}}
        {{-- items-stretch, jadi foto selalu mengisi penuh setinggi blok  --}}
        {{-- nama+spesialis+deskripsi di sebelahnya (bukan ukuran tetap). --}}
        {{-- ============================================================ --}}
        <div class="relative bg-white/80 backdrop-blur-sm rounded-3xl border border-outline-variant/15
                    shadow-sm p-5 md:p-8 overflow-hidden">

            {{-- Aksen cahaya — satu-satunya flourish dekoratif di halaman ini --}}
            <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-tertiary/10 blur-3xl pointer-events-none"></div>

            <div class="relative grid grid-cols-1 md:grid-cols-[300px_1fr] gap-6 md:gap-10 md:items-stretch">

                {{-- Foto --}}
                <div class="w-48 md:w-full mx-auto md:mx-0 md:h-full md:min-h-72">
                    @if($dokter->foto)
                        <img src="{{ Storage::url($dokter->foto) }}" alt="{{ $dokter->nama }}"
                             class="w-full h-64 md:h-full object-cover rounded-2xl shadow-lg" loading="lazy">
                    @else
                        <div class="w-full h-64 md:h-full rounded-2xl bg-tertiary/8
                                    flex items-center justify-center">
                            <span class="material-symbols-outlined text-7xl text-tertiary/30">person</span>
                        </div>
                    @endif
                </div>

                {{-- Nama, spesialis, deskripsi --}}
                <div class="flex flex-col md:justify-center text-center md:text-left">
                    <h1 class="text-3xl md:text-4xl font-extrabold text-on-surface leading-tight tracking-tight">
                        {{ $dokter->nama }}
                    </h1>
                    {{-- Badge emas — sengaja dipertahankan sebagai penanda penghargaan ke
                         dokter, bukan diganti warna brand biasa. --}}
                    <p class="inline-flex items-center justify-center md:justify-start font-bold text-white
                              bg-linear-to-r from-yellow-500 to-amber-600 w-fit mx-auto md:mx-0
                              rounded-full px-3 py-1 mt-3 text-sm md:text-base">
                        {{ $dokter->namaSpesialis() }}
                    </p>
                    @if($dokter->deskripsi)
                        <p class="text-sm md:text-base text-on-surface-variant leading-7 md:leading-8 mt-4">
                            {{ $dokter->deskripsi }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- JADWAL PRAKTEK --}}
        {{-- ============================================================ --}}
        <div class="mt-14 md:mt-20">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary mb-2">Ketersediaan</p>
            <h2 class="text-2xl md:text-3xl font-extrabold text-on-surface tracking-tight mb-6">Jadwal Praktek</h2>

        @if($dokter->kuota_pasien)
            <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 mb-4">
                <span class="material-symbols-outlined text-amber-500 text-[20px] shrink-0 mt-0.5"
                      style="font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;">
                    groups
                </span>
                <p class="text-sm text-amber-800 leading-relaxed">{{ $dokter->kuota_pasien }}</p>
            </div>
        @endif

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
                                <span class="text-xs font-bold uppercase tracking-widest
                                             bg-white/20 px-2 py-0.5 rounded-full">Hari Ini</span>
                            @endif
                        </div>
                        <div class="divide-y divide-outline-variant/15 flex-1">
                            @foreach($sesiHari as $sesi)
                                @include('rumah_sakit.dokter._jadwal-row', [
                                    'sesi'      => $sesi,
                                    'warna'     => $warna,
                                    'rs'        => $rs,
                                    'perubahan' => $isToday ? ($perubahanHariIni[$sesi->poliklinik_id] ?? null) : null,
                                ])
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        </div>

        {{-- ============================================================ --}}
        {{-- AKSI: antrian, pendaftaran, disclaimer — diberi 1 ritme jarak  --}}
        {{-- yang sama dengan section lain (mt-14/mt-20), bukan mt-8 yang  --}}
        {{-- berbeda-beda seperti sebelumnya.                              --}}
        {{-- ============================================================ --}}
        <div class="mt-14 md:mt-20 space-y-4">
            {{-- Antrian live — status diambil langsung dari API antrian tiap halaman dimuat
                 (tidak disimpan ke database), cuma tampil kalau berhasil diambil. Dipisah dari
                 CTA "Daftar Sekarang" karena ini info aksi-sekarang, beda urgensi dari daftar
                 untuk kunjungan mendatang. --}}
            @if($antrian)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4
                            bg-primary/5 border border-primary/20 rounded-2xl px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary text-[22px] shrink-0">confirmation_number</span>
                        <div>
                            <p class="font-semibold text-on-surface text-sm">
                                Antrian {{ $antrian['nama_poli'] ?? 'Poliklinik' }}
                            </p>
                            <p class="text-xs text-on-surface-variant mt-0.5">
                                Nomor Poli
                                <span class="font-bold text-on-surface">{{ $antrian['id'] ?? '-' }}</span>
                            </p>
                        </div>
                    </div>
                    <span class="shrink-0 inline-flex items-center justify-center text-sm font-bold text-primary
                                 border border-primary/30 px-4 py-2 rounded-full whitespace-nowrap">
                        {{ $antrian['status'] ?? '-' }}
                    </span>
                </div>
            @endif

            @php $punyaLinkDaftar = (bool) $rs->link_pendaftaran_online; @endphp
            <div class="flex flex-col md:flex-row gap-4 md:items-stretch">
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
        </div>

        {{-- ============================================================ --}}
        {{-- PENDIDIKAN & PELATIHAN --}}
        {{-- ============================================================ --}}
        @php
            $adaPendidikan = filled(trim(strip_tags($dokter->pendidikan ?? '')));
            $adaPelatihan  = filled(trim(strip_tags($dokter->pelatihan ?? '')));
        @endphp
        @if($adaPendidikan || $adaPelatihan)
            <div class="mt-14 md:mt-20">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary mb-2">Riwayat</p>
                <h2 class="text-2xl md:text-3xl font-extrabold text-on-surface tracking-tight mb-6">Pendidikan &amp; Pelatihan</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Pendidikan --}}
                    <div class="bg-white rounded-3xl border border-outline-variant/15 shadow-sm p-6">
                        <div class="flex items-center gap-2.5 mb-4">
                            <span class="w-9 h-9 rounded-xl bg-tertiary/10 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-tertiary text-[18px]">school</span>
                            </span>
                            <h3 class="font-bold text-on-surface">Pendidikan</h3>
                        </div>
                        @if($adaPendidikan)
                            <div class="prose prose-sm max-w-none text-on-surface-variant marker:text-tertiary">
                                {!! $dokter->pendidikan !!}
                            </div>
                        @else
                            <p class="text-sm text-on-surface-variant/70 italic">Belum ada informasi pendidikan tercatat.</p>
                        @endif
                    </div>

                    {{-- Pelatihan --}}
                    <div class="bg-white rounded-3xl border border-outline-variant/15 shadow-sm p-6">
                        <div class="flex items-center gap-2.5 mb-4">
                            <span class="w-9 h-9 rounded-xl bg-secondary/10 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-secondary text-[18px]">workspace_premium</span>
                            </span>
                            <h3 class="font-bold text-on-surface">Pelatihan</h3>
                        </div>
                        @if($adaPelatihan)
                            <div class="prose prose-sm max-w-none text-on-surface-variant marker:text-secondary">
                                {!! $dokter->pelatihan !!}
                            </div>
                        @else
                            <p class="text-sm text-on-surface-variant/70 italic">Belum ada informasi pelatihan tercatat.</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
