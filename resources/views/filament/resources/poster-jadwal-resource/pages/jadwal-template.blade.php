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
}

.poli-card { overflow: hidden; }

.poli-header {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}
.poli-header img.shape { width: 100%; display: block; }
.poli-header .nama-poli {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    text-align: center;
    width: 100%;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.poli-dokter { padding: 4px 8px; }

.dokter-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    line-height: 1.35;
    margin-bottom: 2px;
}

/* Badge "Executive Clinic" di sudut kanan shape header */
.ec-header-badge {
    position: absolute;
    right: 6px;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(124,58,237,0.88);
    color: #fff;
    font-size: 8px;
    font-weight: 700;
    padding: 2px 5px;
    border-radius: 3px;
    letter-spacing: 0.4px;
    white-space: nowrap;
    z-index: 2;
}

/* Badge "Executive Clinic" di baris dokter (per-dokter) */
.ec-row-badge {
    display: inline-block;
    background: rgba(124,58,237,0.12);
    color: #7c3aed;
    border: 1px solid rgba(124,58,237,0.35);
    font-size: 8px;
    font-weight: 700;
    padding: 1px 5px;
    border-radius: 3px;
    letter-spacing: 0.3px;
    white-space: nowrap;
    line-height: 1.4;
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

@if (count($googleFonts))
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

    $fontDate  = $fontFor($cfg['font_tanggal']    ?? [], 'FontTanggal');
    $fontKet   = $fontFor($cfg['font_keterangan'] ?? [], 'FontKeterangan');
    $fontPoli  = $fontFor($grid['font_nama_poli'] ?? [], 'FontIsi');
    $fontIsi   = $fontFor($grid['font_isi']       ?? [], 'FontIsi');
@endphp
<div id="layer-content">

    {{-- Logo Header --}}
    @if ($logoDataUri)
    <img
        src="{{ $logoDataUri }}"
        alt="Logo"
        style="
            position:absolute;
            left:{{ $zonaLogo['x'] }}px; top:{{ $zonaLogo['y'] }}px;
            width:{{ $zonaLogo['w'] }}px; height:{{ $zonaLogo['h'] }}px;
            object-fit:contain;
        "
    >
    @endif

    {{-- Tanggal — Badge kartu (ikon kalender + hari + tanggal) --}}
    @php
        $dateFontSize = (int) ($zonaDate['size']  ?? 40);
        $dateColor    = $zonaDate['warna']         ?? '#1a1a2e';
        $dateBg       = $zonaDate['bg_warna']      ?? 'rgba(255,255,255,0.95)';
        $datePad      = round($dateFontSize * 0.35);
        $dateRadius   = round($dateFontSize * 0.4);
        $dateGap      = round($dateFontSize * 0.4);
        $iconSize     = round($dateFontSize * 0.9);
        $dateSmall    = round($dateFontSize * 0.68);
    @endphp
    <div style="
        position:absolute;
        left:{{ $zonaDate['x'] }}px; top:{{ $zonaDate['y'] }}px;
        background:{{ $dateBg }};
        border-radius:{{ $dateRadius }}px;
        padding:{{ $datePad }}px {{ round($datePad * 1.6) }}px {{ $datePad }}px {{ round($datePad * 1.2) }}px;
        display:inline-flex;
        align-items:center;
        gap:{{ $dateGap }}px;
        box-shadow:0 4px 24px rgba(0,0,0,0.14);
        max-width:{{ $zonaDate['w'] }}px;
    ">
        {{-- Ikon kalender SVG --}}
        <svg width="{{ $iconSize }}" height="{{ $iconSize }}" viewBox="0 0 24 24"
             fill="none" stroke="#7c3aed" stroke-width="2.2"
             stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
            <rect x="3" y="4" width="18" height="18" rx="3" ry="3"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8"  y1="2" x2="8"  y2="6"/>
            <line x1="3"  y1="10" x2="21" y2="10"/>
            <text x="12" y="19" text-anchor="middle"
                  font-size="8" fill="#7c3aed" font-weight="700"
                  stroke="none">{{ $tanggal->day }}</text>
        </svg>
        {{-- Teks hari dan tanggal --}}
        <div>
            <div style="
                font-family:{{ $fontDate }};
                font-size:{{ $dateFontSize }}px;
                color:{{ $dateColor }};
                font-weight:800;
                line-height:1.05;
                letter-spacing:-0.5px;
            ">{{ $tanggal->translatedFormat('l') }},</div>
            <div style="
                font-family:{{ $fontDate }};
                font-size:{{ $dateSmall }}px;
                color:{{ $dateColor }};
                font-weight:600;
                opacity:0.72;
                line-height:1.2;
            ">{{ $tanggal->translatedFormat('j F Y') }}</div>
        </div>
    </div>

    {{-- Keterangan Hero --}}
    @if ($keterangan)
    @php
        $ketBg      = $zonaKet['bg_warna'] ?? '';
        $ketPadding = (int) ($zonaKet['padding'] ?? 0);
    @endphp
    <div style="
        position:absolute;
        left:{{ $zonaKet['x'] }}px; top:{{ $zonaKet['y'] }}px;
        width:{{ $zonaKet['w'] }}px;
        font-family:{{ $fontKet }};
        font-size:{{ $zonaKet['size'] ?? 24 }}px;
        color:{{ $zonaKet['warna'] ?? '#F0C040' }};
        text-align:{{ $zonaKet['align'] ?? 'left' }};
        font-weight:600;
        white-space:pre-line;
        @if($ketBg) background:{{ $ketBg }}; @endif
        @if($ketPadding) padding:{{ $ketPadding }}px; border-radius:8px; @endif
    ">
        {{ $keterangan }}
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
            $cardStyle = "border-radius:{$cardRadius}px; border:{$cardBorderWidth}px solid {$cardBorderWarna}; overflow:hidden;";
        @endphp
        @foreach ($poliList as $item)
        @php
            $poli       = $item['poli'];
            $jadwalRows = $item['jadwal'];
        @endphp
        <div class="poli-card" style="{{ $cardStyle }}">

            {{-- Header Shape + Nama Poli --}}
            @php
                $shapeZoom = ($grid['shape_scale'] ?? 100) / 100;
                $hasExec   = !empty(array_filter($jadwalRows, fn($r) => !empty($r['is_executive'])));
            @endphp
            <div class="poli-header" style="overflow:hidden;">
                @if ($shapeDataUri)
                <img class="shape" src="{{ $shapeDataUri }}" alt=""
                     @if ($shapeZoom != 1.0) style="zoom:{{ $shapeZoom }};" @endif>
                @endif
                <span class="nama-poli" style="
                    font-family:{{ $fontPoli }};
                    font-size:{{ $grid['size_nama_poli'] ?? 15 }}px;
                    color:{{ $grid['warna_nama_poli'] ?? '#ffffff' }};
                    padding-right:{{ $hasExec ? '70px' : '0' }};
                ">{{ $poli->nama }}</span>
                @if ($hasExec)
                <span class="ec-header-badge">Executive Clinic</span>
                @endif
            </div>

            {{-- Daftar Dokter --}}
            @php $sizeJam = $grid['size_jam'] ?? 12; @endphp
            <div class="poli-dokter" style="background:{{ $cardBg }};">
                @forelse ($jadwalRows as $row)
                @php $isExec = ! empty($row['is_executive']); @endphp
                <div class="dokter-row">
                    {{-- Nama dokter --}}
                    <span style="
                        font-family:{{ $fontIsi }};
                        font-size:{{ $grid['size_nama_dokter'] ?? 13 }}px;
                        color:{{ $row['libur'] ? '#9ca3af' : ($grid['warna_nama_dokter'] ?? '#1A1A1A') }};
                        flex:1; min-width:0;
                        overflow:hidden;
                        text-overflow:ellipsis;
                        white-space:nowrap;
                    ">{{ $row['nama_dokter'] }}</span>

                    {{-- Jam / LIBUR / EC badge --}}
                    @if ($row['libur'])
                    <span style="
                        font-family:{{ $fontIsi }};
                        font-size:{{ $sizeJam }}px;
                        color:#ef4444;
                        font-weight:700;
                        white-space:nowrap;
                        margin-left:4px;
                    ">LIBUR</span>
                    @else
                    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:1px; margin-left:4px; flex-shrink:0;">
                        <span style="
                            font-family:{{ $fontIsi }};
                            font-size:{{ $sizeJam }}px;
                            color:{{ $grid['warna_jam'] ?? '#555555' }};
                            white-space:nowrap;
                        ">{{ $row['jam_mulai'] }}–{{ $row['jam_selesai'] ?? 'selesai' }}</span>
                        @if ($isExec)
                        <span class="ec-row-badge">Executive Clinic</span>
                        @endif
                    </div>
                    @endif
                </div>
                @empty
                <div style="color:#aaa; font-size:11px; font-style:italic;">Tidak ada jadwal</div>
                @endforelse
            </div>

        </div>
        @endforeach
    </div>

</div>
</body>
</html>