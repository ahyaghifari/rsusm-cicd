{{-- Kartu 1 poliklinik. Di-include dari baris prioritas (atas) maupun grid multi-kolom biasa.
     Mengandalkan variabel yang sudah dihitung di scope pemanggil (jadwal-template.blade.php):
     $grid, $shapePoliDataUri, $fontPoli, $fontNamaDokter, $fontJam, $fontIsi, $cardBg,
     $bodyStyle, $dokterValign, $dokterRowGap, $cardPaddingTop, $overlapPx, $headerBg,
     $headerRadius, $headerFontPx, $sizeNamaDokter, $sizeJam, $weightNamaDokter, $weightJam.
     $item: ['poli' => PoliKlinik, 'jadwal' => array]
--}}
@php
    $poli       = $item['poli'];
    $jadwalRows = $item['jadwal'];
    $headerOffsetX     = (int) ($grid['header_offset_x'] ?? 0);
    $headerPaddingLeft = (int) ($grid['header_padding_left'] ?? 10);
@endphp
<div class="poli-card" style="margin-bottom:{{ $gapV }}px;">

    @if (!empty($shapePoliDataUri))
    <div class="poli-header" style="
        background-image:url('{{ $shapePoliDataUri }}');
        background-size:100% 100%;
        background-repeat:no-repeat;
        background-color:transparent;
        border-radius:0;
        width:{{ $grid['header_width_pct'] ?? 70 }}%;
        margin-left:{{ $headerOffsetX }}px;
        padding-left:{{ $headerPaddingLeft }}px;
        position:relative; z-index:2;
    ">
    @else
    <div class="poli-header" style="background:{{ $headerBg }}; border-radius:{{ $headerRadius }}px; width:{{ $grid['header_width_pct'] ?? 70 }}%; margin-left:{{ $headerOffsetX }}px; position:relative; z-index:2;">
    @endif
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
            <div style="display:flex; align-items:{{ !empty($row['jam_list']) ? 'center' : 'flex-start' }}; line-height:1.35; justify-content:space-between;">
                <span style="
                    font-family:{{ $fontNamaDokter }};
                    font-size:{{ $sizeNamaDokter }}px;
                    font-weight:{{ $weightNamaDokter }};
                    color:{{ $grid['warna_nama_dokter'] }};
                    flex:1; min-width:0;
                    word-break:break-word;
                ">{{ $row['nama_dokter'] }}</span>

                @if (!empty($row['jam_list']))
                {{-- Dokter dengan beberapa jadwal di hari yang sama — jam ditumpuk --}}
                <div style="display:flex; flex-direction:column; align-items:flex-end; gap:0px; margin-left:8px;">
                    @foreach ($row['jam_list'] as $t)
                    <span style="
                        font-family:{{ $fontJam }};
                        font-size:{{ $sizeJam }}px;
                        font-weight:{{ $t['libur'] ? 700 : $weightJam }};
                        color:{{ $t['libur'] ? '#ef4444' : ($grid['warna_nama_dokter'] ?? '#1A1A1A') }};
                        white-space:nowrap;
                    ">
                        @if ($t['libur'])
                            LIBUR
                        @elseif (!empty($t['sesuai_perjanjian']))
                            Sesuai Perjanjian
                        @else
                            {{ $t['jam_mulai'] }}–{{ $t['jam_selesai'] ?? 'Selesai' }}
                        @endif
                    </span>
                    @endforeach
                </div>
                @elseif ($row['libur'])
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
                ">{{ $row['jam_mulai'] }}–{{ $row['jam_selesai'] ?? 'Selesai' }}</span>
                @endif
            </div>

            {{-- Catatan (only when filled) --}}
            @if (!empty($row['catatan']))
            @php
                $isDariPerubahan = $row['catatan_dari_perubahan'] ?? false;
                $catatanBg    = $isDariPerubahan
                    ? ($grid['catatan_perubahan_bg_warna'] ?? '#fff7ed')
                    : ($grid['catatan_bg_warna']           ?? '#fef9c3');
                $catatanWarna = $isDariPerubahan
                    ? ($grid['catatan_perubahan_warna']    ?? '#92400e')
                    : ($grid['catatan_warna']              ?? '#1a1a2e');
            @endphp
            <div style="
                align-self:flex-start;
                background:{{ $catatanBg }};
                color:{{ $catatanWarna }};
                border:1px solid {{ $grid['catatan_border_warna'] ?? '#fde68a' }};
                border-radius:{{ $grid['catatan_radius'] ?? 4 }}px;
                padding:3px 6px;
                font-size:{{ $grid['catatan_size'] ?? 8 }}px;
                font-family:{{ $fontIsi }};
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