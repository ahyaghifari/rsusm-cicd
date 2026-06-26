{{-- resources/views/poster/jadwal-template.blade.php
     Dirender oleh Browsershot → PNG 1080×1920
     $template  : PosterTemplate
     $tanggal   : Carbon
     $fotoHero  : string|null  (URL atau base64)
     $keterangan: string
     $poliList  : Collection<{poli, jadwal[]}>
--}}
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=1080">
<title>Poster Jadwal</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    width: 1080px;
    height: 1920px;
    overflow: hidden;
    position: relative;
    font-family: sans-serif;
    background: #1a1a2e;
}

/* ── Layer 1: Foto Hero — tinggi diatur via config tinggi_hero ── */
#layer-hero {
    position: absolute;
    top: 0; left: 0;
    width: 1080px;
    overflow: hidden;
    z-index: 1;
}
#layer-hero img {
    width: 100%; height: 100%;
    object-fit: cover;
    object-position: center top;
}

/* ── Layer 2: Template PNG ───────────────────────────────────── */
#layer-template {
    position: absolute;
    inset: 0;
    z-index: 2;
}
#layer-template img {
    width: 100%; height: 100%;
    object-fit: cover;
}

/* ── Layer 3: Konten Dinamis ─────────────────────────────────── */
#layer-content {
    position: absolute;
    inset: 0;
    z-index: 3;
}

/* ── Grid Jadwal ─────────────────────────────────────────────── */
.grid-jadwal {
    display: grid;
    align-content: start;
    /* Pastikan grid tidak bisa melebar melebihi zona yang ditetapkan */
    box-sizing: border-box;
}

.poli-card {
    display: flex;
    flex-direction: column;
    /* KRITIS: tanpa min-width:0, grid item bisa melebar melebihi 1fr
       karena default CSS Grid adalah min-width:auto */
    min-width: 0;
    overflow: hidden;
    box-sizing: border-box;
}

.poli-header {
    padding: 5px 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    /* Clamp header agar tidak bisa melar keluar card */
    min-width: 0;
    overflow: hidden;
}

.poli-body {
    display: flex;
    /* Ikut min-width:0 agar child flex item tidak overflow card */
    min-width: 0;
    overflow: hidden;
}

.poli-dokter {
    flex: 1;
    padding: 4px 8px;
    display: flex;
    flex-direction: column;
    min-width: 0;
    overflow: hidden;
}

    font-size: 9px;
    font-weight: 700;
    text-align: center;
    padding: 2px 4px;
}

.dokter-row {
    display: flex;
    align-items: center;
    line-height: 1.35;
    justify-content: space-between;
}

.poli-card-body-inner {
    overflow: hidden;
}
</style>

{{-- ── Google Fonts dynamic load ───────────────────────────────── --}}
@php
    $cfg          = $template->config ?: \App\Models\PosterTemplate::defaultConfig();
    $googleFonts  = [];

    $collectFont = function($fontObj) use (&$googleFonts) {
        if (isset($fontObj['sumber']) && $fontObj['sumber'] === 'google') {
            $googleFonts[] = str_replace(' ', '+', $fontObj['nama']) . ':wght@400;600;700';
        }
    };

    $collectFont($cfg['font_tanggal']    ?? []);
    $collectFont($cfg['font_keterangan'] ?? []);
    $collectFont($cfg['grid']['font_nama_poli'] ?? []);
    $collectFont($cfg['grid']['font_isi']       ?? []);

    $googleFonts = array_unique($googleFonts);
@endphp

{{-- ── Google Fonts — hanya dimuat di preview browser, bukan saat Browsershot render.
     Browsershot menggunakan font system sebagai fallback agar layout konsisten. --}}
@php
    // Deteksi apakah sedang di-render oleh Browsershot (tidak ada HTTP_HOST di CLI/Browsershot)
    $isScreenshot = app()->runningInConsole() || !request()->hasHeader('X-Livewire');
@endphp
@if (count($googleFonts) && !$isScreenshot)
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family={{ implode('&family=', $googleFonts) }}&display=swap" rel="stylesheet">
@endif

{{-- ── Upload font custom (@font-face) ─────────────────────────── --}}
{{-- $uploadFonts di-pass dari GeneratePosterPage::resolveUploadFonts() sebagai data URI --}}
@if (count($uploadFonts ?? []))
<style>
    @foreach ($uploadFonts as $alias => $dataUri)
    @font-face {
        font-family: '{{ $alias }}';
        src: url('{{ $dataUri }}') format('truetype');
    }
    @endforeach
</style>
@endif

</head>
<body>

{{-- ── LAYER 1: Foto Hero ──────────────────────────────────────── --}}
@php $tinggiHero = (int) round(($cfg['tinggi_hero'] ?? 25) / 100 * 1920); @endphp
@if ($fotoHeroDataUri && $tinggiHero > 0)
<div id="layer-hero" style="height:{{ $tinggiHero }}px;">
    <img src="{{ $fotoHeroDataUri }}" alt="Hero">
</div>
@endif

{{-- ── LAYER 2: Template PNG ───────────────────────────────────── --}}
<div id="layer-template">
    @if($templateDataUri)
    <img src="{{ $templateDataUri }}" alt="Template">
    @endif
</div>

{{-- ── LAYER 3: Konten Dinamis ─────────────────────────────────── --}}
@php
    $zonaLogo        = $cfg['zona_logo']       ?? ['x'=>60,  'y'=>60,  'w'=>300,'h'=>120];
    $zonaDate        = $cfg['zona_tanggal']     ?? ['x'=>80,  'y'=>940, 'w'=>900];
    $zonaKet         = $cfg['zona_keterangan']  ?? ['x'=>80,  'y'=>1000,'w'=>900];
    $zonaJadwal      = $cfg['zona_jadwal']      ?? ['x'=>40,  'y'=>1080,'w'=>1000,'h'=>780];
    $grid            = $cfg['grid']             ?? [];

    // Font resolvers
    $fontFor = function($fontObj, $fallbackAlias) use ($uploadFonts) {
        if (!isset($fontObj['sumber'])) return 'sans-serif';
        if ($fontObj['sumber'] === 'google') return "'{$fontObj['nama']}', sans-serif";
        return "'{$fallbackAlias}', sans-serif";
    };

    $fontDate  = $fontFor(['sumber' => 'google', 'nama' => $zonaDate['font'] ?? 'Montserrat'], 'FontTanggal');
    $fontKet   = $fontFor(['sumber' => 'google', 'nama' => $zonaKet['font'] ?? 'Poppins'], 'FontKeterangan');
    $fontPoli  = $fontFor($grid['font_nama_poli'] ?? [], 'FontIsi');
    $fontIsi   = $fontFor($grid['font_isi']       ?? [], 'FontIsi');
    $fontNamaDokter = $fontFor(['sumber' => 'google', 'nama' => $grid['font_nama_dokter'] ?? 'Poppins'], 'FontIsi');
    $fontJam   = $fontFor(['sumber' => 'google', 'nama' => $grid['font_jam'] ?? 'Poppins'], 'FontIsi');
@endphp
<div id="layer-content">

    {{-- Logo Header —— dengan scale, opacity, padding, dan background dari config --}}
    @if ($logoDataUri)
    @php
        $logoScale = (int) ($zonaLogo['scale'] ?? 100);
        $logoOpacity = (int) ($zonaLogo['opacity'] ?? 100);
        $logoPadding = (int) ($zonaLogo['padding'] ?? 0);
        $logoBg = $zonaLogo['bg_warna'] ?? 'transparent';
    @endphp
    <div style="
        position:absolute;
        left:{{ $zonaLogo['x'] }}px; top:{{ $zonaLogo['y'] }}px;
        width:{{ $zonaLogo['w'] }}px; height:{{ $zonaLogo['h'] }}px;
        background:{{ $logoBg }};
        padding:{{ $logoPadding }}px;
        display:flex;
        align-items:center;
        justify-content:center;
    ">
        <img
            src="{{ $logoDataUri }}"
            alt="Logo"
            style="
                width:100%;
                height:100%;
                object-fit:contain;
                transform:scale({{ $logoScale / 100 }});
                opacity:{{ $logoOpacity / 100 }};
                transform-origin:center;
            "
        >
    </div>
    @endif

    {{-- Tanggal —— mengikuti config zona_tanggal --}}
    @php
        $dateFontSize = (int) ($zonaDate['size']  ?? 40);
        $dateColor    = $zonaDate['warna']         ?? '#1a1a2e';
        $dateBg       = $zonaDate['bg_warna']      ?? '';
        $dateFont     = $fontFor(['sumber' => 'google', 'nama' => $zonaDate['font'] ?? 'Montserrat'], 'FontTanggal');
        $dateWeight   = $zonaDate['weight']        ?? '400';
        $dateAlign    = $zonaDate['align'] ?? 'left';
    @endphp
    <div style="
        position:absolute;
        left:{{ $zonaDate['x'] }}px; top:{{ $zonaDate['y'] }}px;
        width:{{ $zonaDate['w'] }}px;
        height:{{ $zonaDate['h'] ?? 60 }}px;
        display:flex;
        align-items:center;
        justify-content:{{ $dateAlign === 'center' ? 'center' : ($dateAlign === 'right' ? 'flex-end' : 'flex-start') }};
        @if($dateBg) background:{{ $dateBg }}; @endif
    ">
        <div style="
            font-family:{{ $dateFont }};
            font-size:{{ $dateFontSize }}px;
            font-weight:{{ $dateWeight }};
            color:{{ $dateColor }};
            text-align:{{ $dateAlign }};
            line-height:1.2;
        ">{{ $tanggal->translatedFormat('l, j F Y') }}</div>
    </div>

    {{-- Keterangan Hero —— mengikuti config zona_keterangan --}}
    @if ($keterangan)
    @php
        $ketBg      = $zonaKet['bg_warna'] ?? '';
        $ketFont    = $fontFor(['sumber' => 'google', 'nama' => $zonaKet['font'] ?? 'Poppins'], 'FontKeterangan');
        $ketSize    = (int) ($zonaKet['size'] ?? 24);
        $ketColor   = $zonaKet['warna'] ?? '#F0C040';
        $ketWeight  = $zonaKet['weight']   ?? '600';
        $ketAlign   = $zonaKet['align'] ?? 'left';
    @endphp
    <div style="
        position:absolute;
        left:{{ $zonaKet['x'] }}px; top:{{ $zonaKet['y'] }}px;
        width:{{ $zonaKet['w'] }}px;
        height:{{ $zonaKet['h'] ?? 60 }}px;
        font-family:{{ $ketFont }};
        font-size:{{ $ketSize }}px;
        font-weight:{{ $ketWeight }};
        color:{{ $ketColor }};
        text-align:{{ $ketAlign }};
        display:flex;
        align-items:center;
        @if($ketBg) background:{{ $ketBg }}; border-radius:6px; padding:8px; @endif
    ">
        <div style="width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $keterangan }}</div>
    </div>
    @endif

    {{-- Grid Jadwal --}}
    @php $kolom = $grid['kolom'] ?? 2; @endphp
    <div
        class="grid-jadwal"
        style="
            position:absolute;
            left:{{ $zonaJadwal['x'] }}px;
            top:{{ $zonaJadwal['y'] }}px;
            width:{{ $zonaJadwal['w'] }}px;
            height:{{ $zonaJadwal['h'] }}px;
            overflow:hidden;
            gap:{{ $grid['gap'] ?? 16 }}px;
            grid-template-columns: repeat({{ $kolom }}, 1fr);
        "
    >
        @php
            $cardBg          = $grid['card_bg_warna']    ?? '#ffffff';
            $cardRadius      = (int) ($grid['card_radius']       ?? 8);
            $cardBorderWarna = $grid['card_border_warna'] ?? '#e5e7eb';
            $cardBorderWidth = (int) ($grid['card_border_width'] ?? 1);
            $cardMinHeight   = (int) ($grid['card_min_height'] ?? 0);
            $dokterValign    = ($grid['dokter_valign'] ?? 'top') === 'center' ? 'center' : 'flex-start';
            $dokterRowGap    = (int) ($grid['dokter_row_gap'] ?? 2);
            $bodyStyle = "border-radius:{$cardRadius}px; border:{$cardBorderWidth}px solid {$cardBorderWarna}; overflow:hidden;"
                . ($cardMinHeight > 0 ? " min-height:{$cardMinHeight}px;" : '');

            $headerBg1    = $grid['header_bg_warna']  ?? '#7c3aed';
            $headerBg2    = $grid['header_bg_warna2'] ?? '';
            $headerBg     = $headerBg2 ? "linear-gradient(135deg, {$headerBg1}, {$headerBg2})" : $headerBg1;
            $headerRadius = (int) ($grid['header_radius'] ?? 8);

            // Font size: gunakan dari config jika tersedia, fallback ke auto-calc
            $cardWidthPx     = ($kolom > 0) ? (($zonaJadwal['w'] - ($grid['gap'] ?? 16) * ($kolom - 1)) / $kolom) : $zonaJadwal['w'];
            $headerFontPx    = (int) ($grid['size_nama_poli'] ?? max(8, round($cardWidthPx * 0.045)));
            $sizeNamaDokter  = (int) ($grid['size_nama_dokter'] ?? max(7, round($cardWidthPx * 0.04)));
            $sizeJam         = (int) ($grid['size_jam'] ?? max(6, round($cardWidthPx * 0.035)));
            $weightNamaDokter = $grid['weight_nama_dokter'] ?? '600';
            $weightJam       = $grid['weight_jam'] ?? '500';

            // Box dokter ditarik ke atas, sebagian "di belakang" header nama poli (overlap = setengah
            // tinggi header). Tinggi header dihitung dari padding (5px atas+bawah) + tinggi baris teks.
            $headerHeightPx = 10 + (int) round($headerFontPx * 1.2);
            $overlapPx      = (int) round($headerHeightPx / 2);
            $cardPaddingTop = (int) ($grid['card_padding_top'] ?? 8);
        @endphp
        @foreach ($poliList as $item)
        @php
            $poli        = $item['poli'];
            $jadwalRows  = $item['jadwal'];
        @endphp
        <div class="poli-card">

            <div class="poli-header" style="background:{{ $headerBg }}; border-radius:{{ $headerRadius }}px; width:{{ $grid['header_width_pct'] ?? 70 }}%; position:relative; z-index:2;">
                <span style="
                    font-family:{{ $fontPoli }};
                    font-size:{{ $headerFontPx }}px;
                    color:{{ $grid['warna_nama_poli'] ?? '#ffffff' }};
                    font-weight:{{ $grid['header_font_weight'] ?? '700' }};
                    font-style:{{ $grid['header_font_style'] ?? 'normal' }};
                ">{{ $poli->nama }}</span>
            </div>

            <div class="poli-body" style="{{ $bodyStyle }} position:relative; z-index:1; margin-top:-{{ $overlapPx }}px; display:flex;">
                {{-- Regular Dokter Column --}}
                <div class="poli-dokter" style="background:{{ $cardBg }}; justify-content:{{ $dokterValign }}; gap:{{ $dokterRowGap }}px; padding-top:{{ $overlapPx + $cardPaddingTop }}px; flex:1;">
                    @forelse ($jadwalRows as $row)
                    <div style="display:flex; align-items:flex-start; line-height:1.35; justify-content:space-between;">
                        <span style="
                            font-family:{{ $fontNamaDokter }};
                            font-size:{{ $sizeNamaDokter }}px;
                            font-weight:{{ $weightNamaDokter }};
                            color:{{ $grid['warna_nama_dokter'] }};
                            flex:1; min-width:0;
                            word-break:break-word;
                        ">{{ $row['nama_dokter'] }}</span>

                        @if ($row['libur'])
                        <span style="
                            font-family:{{ $fontJam }};
                            font-size:{{ $sizeJam }}px;
                            color:#ef4444;
                            font-weight:700;
                            white-space:nowrap;
                            margin-left:8px;
                        ">LIBUR</span>
                        @elseif (!empty($row['sesuai_perjanjian']))
                        <span style="font-size:{{ $sizeJam }}px; white-space:nowrap; margin-left:8px; color:#16a34a; font-style:italic;">Sesuai Perjanjian</span>
                        @else
                        <span style="
                            font-family:{{ $fontJam }};
                            font-size:{{ $sizeJam }}px;
                            font-weight:{{ $weightJam }};
                            color:{{ $grid['warna_nama_dokter'] ?? '#1A1A1A' }};
                            white-space:nowrap;
                            margin-left:8px;
                        ">{{ $row['jam_mulai'] }}–{{ $row['jam_selesai'] ?? 'selesai' }}</span>
                        @endif
                    </div>

                    {{-- Catatan (only when filled) --}}
                    @if (!empty($row['catatan']))
                    <div style="
                        align-self:flex-start;
                        background:{{ $grid['catatan_bg_warna'] ?? '#fef9c3' }};
                        color:{{ $grid['catatan_warna'] ?? '#1a1a2e' }};
                        border:1px solid {{ $grid['catatan_border_warna'] ?? '#fde68a' }};
                        border-radius:{{ $grid['catatan_radius'] ?? 4 }}px;
                        padding:3px 6px;
                        font-size:{{ $grid['catatan_size'] ?? 8 }}px;
                        font-family:{{ $grid['catatan_font'] ?? 'Poppins' }};
                        font-weight:{{ $grid['catatan_weight'] ?? '400' }};
                        margin-top:2px;
                        line-height:1.35;
                    ">{{ $row['catatan'] }}</div>
                    @endif
                    @empty
                    <div style="color:#aaa; font-size:11px; font-style:italic;">Tidak ada jadwal</div>
                    @endforelse
                </div>
            </div>

        </div>
        @endforeach
    </div>

</div>
</body>
</html>