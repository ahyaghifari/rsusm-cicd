{{--
    Template poster layout List Polos — RSU Syifa Medika Barabai
    Dirender oleh Browsershot → PNG 1080×1920
    Variabel: $template, $tanggal, $templateDataUri, $logoDataUri, $poliList,
              $uploadFonts, $keterangan
--}}
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=1080">
<title>Poster Jadwal</title>

@php
    $cfg  = $template->config ?: \App\Models\PosterTemplate::defaultConfig((int) $template->rumah_sakit_id);
    $g    = $cfg['grid'] ?? [];

    $zonaDate    = $cfg['zona_tanggal'] ?? ['x' => 40, 'y' => 380, 'w' => 1000, 'h' => 70, 'font' => 'Montserrat', 'size' => 36, 'warna' => '#1a1a2e', 'align' => 'center'];
    $zonaJadwal  = $cfg['zona_jadwal']  ?? ['x' => 40, 'y' => 480, 'w' => 1000, 'h' => 1400];

    $headerBg    = $g['header_bg_warna']  ?? '#1e3a5f';
    $headerBg2   = $g['header_bg_warna2'] ?? '';
    $headerGrad  = $headerBg2 ? "linear-gradient(to right, {$headerBg}, {$headerBg2})" : $headerBg;
    $headerRadius= (int) ($g['header_radius']     ?? 0);
    $cardBg      = $g['card_bg_warna']    ?? '#ffffff';
    $cardRadius  = (int) ($g['card_radius']       ?? 6);
    $cardBorderW = (int) ($g['card_border_width'] ?? 1);
    $cardBorderC = $g['card_border_warna']  ?? '#dee2e6';
    $gapV             = (int) ($g['gap_v']              ?? 16);
    $gapH             = (int) ($g['gap_h']              ?? 12);
    $colNamaPersen    = (int) ($g['col_nama_persen']    ?? 70);
    $gapHeaderDokter      = (int) ($g['gap_header_dokter']      ?? 0);
    $dokterRaise          = (int) ($g['dokter_raise']            ?? 20);
    $paddingDokterPertama = (int) ($g['padding_dokter_pertama']  ?? 0);
    $pdTop                = (int) ($g['padding_dokter_top']      ?? 7);
    $pdRight              = (int) ($g['padding_dokter_right']    ?? 8);
    $pdBottom             = (int) ($g['padding_dokter_bottom']   ?? 7);
    $pdLeft               = (int) ($g['padding_dokter_left']     ?? 14);
    $headerBorderW        = (int) ($g['header_border_width']     ?? 1);
    $headerBorderC        =       ($g['header_border_warna']     ?? '#dee2e6');

    $fontNamaPoli   = $g['font_nama_poli']['nama'] ?? 'Montserrat';
    $fontIsi        = $g['font_isi']['nama']        ?? 'Poppins';
    $warnaNamaPoli      = $g['warna_nama_poli']       ?? '#ffffff';
    $outlinePoliW       = (int) ($g['outline_poli_width']   ?? 0);
    $outlinePoliC       = $g['outline_poli_warna']    ?? '#000000';
    $warnaDokter        = $g['warna_nama_dokter']     ?? '#1A1A1A';
    $outlineDokterW     = (int) ($g['outline_dokter_width'] ?? 0);
    $outlineDokterC     = $g['outline_dokter_warna']  ?? '#000000';
    $warnaJam           = $g['warna_jam']             ?? '#1A1A1A';
    $outlineJamW        = (int) ($g['outline_jam_width']    ?? 0);
    $outlineJamC        = $g['outline_jam_warna']     ?? '#000000';
    $sizeNamaPoli   = (int) ($g['size_nama_poli']    ?? 30);
    $sizeNamaDokter = (int) ($g['size_nama_dokter']  ?? 26);
    $sizeJam        = (int) ($g['size_jam']          ?? 26);
    $weightDokter   = $g['weight_nama_dokter'] ?? '500';
    $weightJam      = $g['weight_jam']         ?? '400';

    $dateFontFamily = "'{$fontNamaPoli}', sans-serif";
    $dokterFontFamily = "'{$fontIsi}', sans-serif";
    $poliHeaderFont   = "'{$fontNamaPoli}', sans-serif";

    $dateSize        = (int) ($zonaDate['size']          ?? 36);
    $dateColor       = $zonaDate['warna']                ?? '#1a1a2e';
    $dateAlign       = $zonaDate['align']                ?? 'center';
    $dateJustify     = $dateAlign === 'center' ? 'center' : ($dateAlign === 'right' ? 'flex-end' : 'flex-start');
    $dateOutlineW    = (int) ($zonaDate['outline_width'] ?? 0);
    $dateOutlineC    = $zonaDate['outline_warna']        ?? '#000000';

    $jadwalW    = (int) ($zonaJadwal['w'] ?? 1000);

    // Google Fonts
    $googleFonts = array_unique(array_filter([
        str_replace(' ', '+', $fontNamaPoli) . ':wght@400;600;700;800',
        str_replace(' ', '+', $fontIsi)      . ':wght@400;500;600;700',
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

#layer-template {
    position: absolute;
    inset: 0;
    z-index: 1;
}
#layer-template img {
    width: 100%; height: 100%;
    object-fit: cover;
}

#layer-content {
    position: absolute;
    inset: 0;
    z-index: 3;
}

table.jadwal {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: fixed;
}
table.jadwal col.col-spacer{ width: {{ $gapH }}px; }
table.jadwal col.col-jam   { width: 27%; }

/* Header poli */
td.h-nama {
    background: {{ $headerGrad }};
    padding: 10px 14px;
    font-family: {{ $poliHeaderFont }};
    font-size: {{ $sizeNamaPoli }}px;
    font-weight: 700;
    color: {{ $warnaNamaPoli }};
    line-height: 1.2;
    border: {{ $headerBorderW }}px solid {{ $headerBorderC }};
    border-radius: {{ $headerRadius }}px;
    @if($outlinePoliW > 0)-webkit-text-stroke: {{ $outlinePoliW }}px {{ $outlinePoliC }};@endif
}
td.h-jam {
    background: {{ $headerGrad }};
    padding: 10px 8px;
    font-family: {{ $poliHeaderFont }};
    font-size: {{ $sizeNamaPoli }}px;
    font-weight: 700;
    color: {{ $warnaNamaPoli }};
    text-align: center;
    border: {{ $headerBorderW }}px solid {{ $headerBorderC }};
    border-radius: {{ $headerRadius }}px;
    @if($outlinePoliW > 0)-webkit-text-stroke: {{ $outlinePoliW }}px {{ $outlinePoliC }};@endif
}
tr.header-gap td { height: {{ $gapHeaderDokter }}px; padding: 0; border: none; background: transparent; }
td.h-spacer {
    background: transparent;
    padding: 0;
    border: none;
}

/* Baris dokter */
td.d-nama {
    background: transparent;
    padding: 0;
    vertical-align: top;
}
.d-nama-inner {
    display: block;
    width: {{ $colNamaPersen }}%;
    background: {{ $cardBg }};
    padding: {{ $pdTop }}px 0 {{ $pdBottom }}px {{ $pdLeft }}px;
    font-family: {{ $dokterFontFamily }};
    font-size: {{ $sizeNamaDokter }}px;
    font-weight: {{ $weightDokter }};
    color: {{ $warnaDokter }};
    line-height: 1.3;
    border-left: {{ $cardBorderW }}px solid {{ $cardBorderC }};
    border-bottom: {{ $cardBorderW }}px solid {{ $cardBorderC }};
    box-sizing: border-box;
    @if($outlineDokterW > 0)-webkit-text-stroke: {{ $outlineDokterW }}px {{ $outlineDokterC }};@endif
}
tr.first-dokter .d-nama-inner { padding-top: {{ $paddingDokterPertama ?: $pdTop }}px; }
td.d-jam {
    background: {{ $cardBg }};
    padding: {{ $pdTop }}px {{ $pdRight }}px {{ $pdBottom }}px 0;
    font-family: {{ $dokterFontFamily }};
    font-size: {{ $sizeJam }}px;
    font-weight: {{ $weightJam }};
    color: {{ $warnaJam }};
    text-align: center;
    line-height: 1.3;
    border-right: {{ $cardBorderW }}px solid {{ $cardBorderC }};
    border-bottom: {{ $cardBorderW }}px solid {{ $cardBorderC }};
    @if($outlineJamW > 0)-webkit-text-stroke: {{ $outlineJamW }}px {{ $outlineJamC }};@endif
}
tr.first-dokter td.d-jam  { padding-top: {{ $paddingDokterPertama ?: $pdTop }}px; }
td.d-spacer {
    background: transparent;
    padding: 0;
}

/* Header poli selalu di atas baris dokter */
tr.poli-header { position: relative; z-index: 2; }

/* Semua baris dokter naik sejauh dokterRaise */
tr.d-row { position: relative; top: -{{ $dokterRaise }}px; z-index: 1; }



/* Baris spacer antar poli — dikompensasi supaya visual gap tetap = gapV */
tr.gap-row td { height: {{ max(0, $gapV - $dokterRaise) }}px; padding: 0; border: none; background: transparent; }
</style>
</head>
<body>

{{-- Layer 1: Template PNG background --}}
<div id="layer-template">
    @if($templateDataUri)
    <img src="{{ $templateDataUri }}" alt="Template">
    @endif
</div>

{{-- Layer 2: Konten dinamis (tanggal + poli cards) —
     Header/logo sudah bagian dari template PNG --}}
<div id="layer-content" style="position:absolute; inset:0; z-index:3;">

    {{-- Tanggal --}}
    <div style="
        position:absolute;
        left:{{ $zonaDate['x'] ?? 40 }}px;
        top:{{ $zonaDate['y'] ?? 200 }}px;
        width:{{ $zonaDate['w'] ?? 1000 }}px;
        height:{{ $zonaDate['h'] ?? 80 }}px;
        display:flex;
        align-items:center;
        justify-content:{{ $dateJustify }};
    ">
        <div style="
            font-family:{{ $dateFontFamily }};
            font-size:{{ $dateSize }}px;
            font-weight:700;
            color:{{ $dateColor }};
            text-align:{{ $dateAlign }};
            line-height:1.2;
            @if($dateOutlineW > 0)-webkit-text-stroke:{{ $dateOutlineW }}px {{ $dateOutlineC }};@endif
        ">{{ $tanggal->translatedFormat('l, j F Y') }}</div>
    </div>

    {{-- Daftar poli cards --}}
    <div style="
        position:absolute;
        left:{{ $zonaJadwal['x'] ?? 40 }}px;
        top:{{ $zonaJadwal['y'] ?? 320 }}px;
        width:{{ $jadwalW }}px;
        height:{{ $zonaJadwal['h'] ?? 1560 }}px;
        overflow:hidden;
    ">
        <table class="jadwal">
            <colgroup>
                <col class="col-nama">
                <col class="col-spacer">
                <col class="col-jam">
            </colgroup>
            <tbody>
            @foreach($poliList as $idx => $item)
            @php $poli = $item['poli']; $jadwal = $item['jadwal']; @endphp

            @if($idx > 0)
            <tr class="gap-row"><td colspan="3"></td></tr>
            @endif

            <tr class="poli-header">
                <td class="h-nama">{{ $poli->nama }}</td>
                <td class="h-spacer"></td>
                <td class="h-jam">JAM</td>
            </tr>
            @if($gapHeaderDokter > 0)
            <tr class="header-gap"><td colspan="3"></td></tr>
            @endif

            @foreach($jadwal as $row)
            <tr class="d-row{{ $loop->first ? ' first-dokter' : '' }}">
                <td class="d-nama"><div class="d-nama-inner">{{ $row['nama_dokter'] }}</div></td>
                <td class="d-spacer"></td>
                <td class="d-jam">
                    @if($row['sesuai_perjanjian'] ?? false)
                        Sesuai Perjanjian
                    @elseif($row['libur'] ?? false)
                        LIBUR
                    @else
                        {{ $row['jam_mulai'] ?? '' }}–{{ !empty($row['jam_selesai']) ? $row['jam_selesai'] : 'Selesai' }}
                    @endif
                </td>
            </tr>
            @endforeach

            @endforeach
            </tbody>
        </table>
    </div>

</div>
</body>
</html>
