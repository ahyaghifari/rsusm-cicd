{{--
    Template poster Perubahan Jadwal Praktik Dokter Spesialis.
    Dirender oleh Browsershot → PNG 1080×1920.
    Tidak pakai foto hero / logo header — cuma tanggal + daftar perubahan.
    Variabel: $template, $tanggal, $templateDataUri, $uploadFonts, $sections

    $sections: [
        ['key' => 'executive', 'label' => string, 'groups' => [
            ['poliklinik_nama' => string, 'items' => [
                ['dokter_nama' => string, 'jam_awal' => ?string, 'jam_baru' => ?string, 'libur' => bool],
            ]],
        ]],
        ...
    ]
--}}
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=1080">
<title>Poster Perubahan Jadwal</title>

@php
    $cfg = $template->config ?: \App\Models\PosterTemplate::defaultConfig((int) $template->rumah_sakit_id, \App\Enums\JenisPosterTemplate::PERUBAHAN_JADWAL);
    $g   = $cfg['grid'] ?? [];

    $zonaTanggal = $cfg['zona_tanggal'] ?? ['x' => 60, 'y' => 300, 'w' => 400, 'h' => 60, 'font' => 'Montserrat', 'size' => 30, 'warna' => '#ffffff', 'bg_warna' => '#c0392b', 'align' => 'center'];
    $zonaKonten  = $cfg['zona_konten']  ?? ['x' => 60, 'y' => 400, 'w' => 960, 'h' => 1300];

    $gapV         = (int) ($g['gap_v']       ?? 24);

    $labelExecutive = $g['label_executive'] ?? 'Klinik Executive';
    $labelReguler   = $g['label_reguler']   ?? 'Poliklinik Reguler';

    $sectionTitleWarna = $g['section_title_warna'] ?? '#c0392b';
    $sectionTitleSize  = (int) ($g['section_title_size'] ?? 32);
    $sectionTitleFont  = $g['section_title_font']['nama'] ?? 'Montserrat';

    $cardBg      = $g['card_bg_warna']     ?? '#ffffff';
    $cardBorderC = $g['card_border_warna'] ?? '#c0392b';
    $cardBorderW = (int) ($g['card_border_width'] ?? 2);
    $cardRadius  = (int) ($g['card_radius'] ?? 12);

    $fontNamaKlinik = $g['font_nama_klinik']['nama'] ?? 'Montserrat';
    $fontIsi        = $g['font_isi']['nama']         ?? 'Poppins';

    $warnaNamaKlinik = $g['warna_nama_klinik'] ?? '#1a1a2e';
    $warnaNamaDokter = $g['warna_nama_dokter'] ?? '#1a1a2e';
    $sizeNamaKlinik  = (int) ($g['size_nama_klinik'] ?? 26);
    $sizeNamaDokter  = (int) ($g['size_nama_dokter'] ?? 24);
    $sizeJam         = (int) ($g['size_jam']         ?? 20);

    $pillAwalBg   = $g['pill_awal_bg_warna']   ?? '#f3f4f6';
    $pillAwalC    = $g['pill_awal_warna']      ?? '#1a1a2e';
    $pillBaruBg   = $g['pill_baru_bg_warna']   ?? '#dcfce7';
    $pillBaruC    = $g['pill_baru_warna']      ?? '#166534';
    $badgeLiburBg = $g['badge_libur_bg_warna'] ?? '#dc2626';
    $badgeLiburC  = $g['badge_libur_warna']    ?? '#ffffff';

    $tanggalFont     = $zonaTanggal['font']      ?? 'Montserrat';
    $tanggalSize     = (int) ($zonaTanggal['size'] ?? 30);
    $tanggalWarna    = $zonaTanggal['warna']     ?? '#ffffff';
    $tanggalBg       = $zonaTanggal['bg_warna']  ?? '#c0392b';
    $tanggalPaddingX = (int) ($zonaTanggal['padding_x'] ?? 28);
    $tanggalPaddingY = (int) ($zonaTanggal['padding_y'] ?? 10);
    $tanggalRadius   = (int) ($zonaTanggal['radius']    ?? 999);

    $googleFonts = array_unique(array_filter([
        str_replace(' ', '+', $fontNamaKlinik)  . ':wght@400;600;700;800',
        str_replace(' ', '+', $fontIsi)         . ':wght@400;500;600;700',
        str_replace(' ', '+', $sectionTitleFont). ':wght@400;600;700;800',
    ]));
@endphp

@php $isScreenshot = app()->runningInConsole() || !request()->hasHeader('X-Livewire'); @endphp
@if(!$isScreenshot && count($googleFonts))
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family={{ implode('&family=', $googleFonts) }}&display=swap" rel="stylesheet">
@endif

@if(!empty($uploadFonts))
<style>
    @foreach($uploadFonts as $alias => $dataUri)
    @font-face { font-family: '{{ $alias }}'; src: url('{{ $dataUri }}') format('truetype'); }
    @endforeach
</style>
@endif

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    width: 1080px;
    height: 1920px;
    overflow: hidden;
    position: relative;
    font-family: '{{ $fontIsi }}', sans-serif;
    background: #f5f5f5;
}

#layer-template { position: absolute; inset: 0; z-index: 1; }
#layer-template img { width: 100%; height: 100%; object-fit: cover; }

#layer-content { position: absolute; inset: 0; z-index: 3; }

.tanggal-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: '{{ $tanggalFont }}', sans-serif;
    font-size: {{ $tanggalSize }}px;
    font-weight: 700;
    color: {{ $tanggalWarna }};
    background: {{ $tanggalBg }};
    border-radius: {{ $tanggalRadius }}px;
    padding: {{ $tanggalPaddingY }}px {{ $tanggalPaddingX }}px;
}

.section-title {
    font-family: '{{ $sectionTitleFont }}', sans-serif;
    font-size: {{ $sectionTitleSize }}px;
    font-weight: 800;
    color: {{ $sectionTitleWarna }};
    text-align: center;
    margin-bottom: 16px;
}

.card {
    background: {{ $cardBg }};
    border: {{ $cardBorderW }}px solid {{ $cardBorderC }};
    border-radius: {{ $cardRadius }}px;
    padding: 18px 22px;
    margin-bottom: {{ $gapV }}px;
}

.card .nama-klinik {
    font-family: '{{ $fontNamaKlinik }}', sans-serif;
    font-size: {{ $sizeNamaKlinik }}px;
    font-weight: 700;
    color: {{ $warnaNamaKlinik }};
    text-align: center;
    margin-bottom: 4px;
}

.card .nama-dokter {
    font-family: '{{ $fontIsi }}', sans-serif;
    font-size: {{ $sizeNamaDokter }}px;
    font-weight: 600;
    color: {{ $warnaNamaDokter }};
    text-align: center;
    border: 1px solid #e5e7eb;
    border-radius: 999px;
    padding: 6px 16px;
    margin: 10px auto 14px;
    display: table;
}

.jadwal-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
}

.jadwal-col { text-align: center; flex: 1; }
.jadwal-col .label {
    font-family: '{{ $fontIsi }}', sans-serif;
    font-size: {{ (int) ($sizeJam * 0.7) }}px;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 6px;
}
.jadwal-col .pill {
    display: inline-block;
    font-family: '{{ $fontIsi }}', sans-serif;
    font-size: {{ $sizeJam }}px;
    font-weight: 700;
    border-radius: 8px;
    padding: 8px 18px;
}
.pill-awal { background: {{ $pillAwalBg }}; color: {{ $pillAwalC }}; }
.pill-baru { background: {{ $pillBaruBg }}; color: {{ $pillBaruC }}; }
.pill-libur { background: {{ $badgeLiburBg }}; color: {{ $badgeLiburC }}; }

.arrow { font-size: {{ $sizeJam }}px; color: #16a34a; }
</style>
</head>
<body>

<div id="layer-template">
    @if($templateDataUri)
    <img src="{{ $templateDataUri }}" alt="Template">
    @endif
</div>

<div id="layer-content">

    {{-- Tanggal --}}
    <div style="position:absolute; left:{{ $zonaTanggal['x'] }}px; top:{{ $zonaTanggal['y'] }}px; width:{{ $zonaTanggal['w'] }}px; height:{{ $zonaTanggal['h'] }}px; display:flex; align-items:center; justify-content:{{ ($zonaTanggal['align'] ?? 'center') === 'left' ? 'flex-start' : (($zonaTanggal['align'] ?? 'center') === 'right' ? 'flex-end' : 'center') }};">
        <span class="tanggal-badge">{{ $tanggal->translatedFormat('l, j F Y') }}</span>
    </div>

    {{-- Konten: section per kategori (Executive / Reguler) --}}
    <div style="position:absolute; left:{{ $zonaKonten['x'] }}px; top:{{ $zonaKonten['y'] }}px; width:{{ $zonaKonten['w'] }}px; height:{{ $zonaKonten['h'] }}px; overflow:hidden;">
        @foreach($sections as $section)
        @if(count($section['groups']))
        <div class="section-title">{{ $section['label'] }}</div>

        @foreach($section['groups'] as $group)
        <div class="card">
            <div class="nama-klinik">{{ $group['poliklinik_nama'] }}</div>

            @foreach($group['items'] as $item)
            <div class="nama-dokter">{{ $item['dokter_nama'] }}</div>
            <div class="jadwal-row" style="margin-bottom:{{ $loop->last ? 0 : 14 }}px;">
                <div class="jadwal-col">
                    <div class="label">Jadwal Awal</div>
                    <span class="pill pill-awal">{{ $item['jam_awal'] ?? '-' }}</span>
                </div>
                <span class="arrow">&#10148;</span>
                <div class="jadwal-col">
                    <div class="label">Berubah Menjadi</div>
                    @if($item['libur'])
                        <span class="pill pill-libur">LIBUR</span>
                    @else
                        <span class="pill pill-baru">{{ $item['jam_baru'] ?? '-' }}</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
        @endif
        @endforeach
    </div>

</div>
</body>
</html>
