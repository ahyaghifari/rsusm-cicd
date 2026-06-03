<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 11px;
        color: #1a1a2e;
        background: #fff;
        margin: 28px 32px;
    }

    /* ── Header ───────────────────────────────── */
    .header {
        border-bottom: 3px solid #d606b0;
        padding-bottom: 10px;
        margin-bottom: 16px;
        display: table;
        width: 100%;
    }
    .header-left  { display: table-cell; vertical-align: middle; }
    .header-right { display: table-cell; vertical-align: middle; text-align: right; }
    .rs-name {
        font-size: 16px;
        font-weight: bold;
        color: #d606b0;
    }
    .doc-title {
        font-size: 13px;
        font-weight: bold;
        color: #1a1a2e;
        margin-top: 3px;
    }
    .meta {
        font-size: 10px;
        color: #555;
        margin-top: 2px;
    }

    /* ── Badge ────────────────────────────────── */
    .badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: bold;
        background: #d606b0;
        color: #fff;
        margin-bottom: 6px;
    }

    /* ── Tabel ────────────────────────────────── */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
    }
    thead tr {
        background-color: #d606b0;
        color: #fff;
    }
    thead th {
        padding: 7px 8px;
        text-align: left;
        font-size: 10px;
        font-weight: bold;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }
    tbody tr {
        border-bottom: 1px solid #e0e0e0;
    }
    tbody tr:nth-child(even) {
        background-color: #fdf0fc;
    }
    tbody td {
        padding: 6px 8px;
        vertical-align: middle;
        font-size: 10.5px;
    }
    .no-col    { width: 32px; text-align: center; color: #888; }
    .jam-col   { white-space: nowrap; }
    .perjanjian-badge {
        display: inline-block;
        padding: 2px 7px;
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffe082;
        border-radius: 10px;
        font-size: 9px;
        font-weight: bold;
    }
    .dash { color: #bbb; }

    /* ── Empty state ──────────────────────────── */
    .empty {
        text-align: center;
        padding: 30px;
        color: #999;
        font-style: italic;
    }

    /* ── Footer ───────────────────────────────── */
    .footer {
        margin-top: 18px;
        padding-top: 10px;
        border-top: 1px solid #ddd;
        display: table;
        width: 100%;
    }
    .footer-left  { display: table-cell; font-size: 9px; color: #777; }
    .footer-right { display: table-cell; text-align: right; font-size: 9px; color: #777; }
    .disclaimer {
        margin-top: 8px;
        padding: 6px 10px;
        background: #fdf0fc;
        border-left: 3px solid #d606b0;
        font-size: 9.5px;
        color: #7a0060;
    }
</style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <div class="header-left">
        <div class="rs-name">{{ $rsName }}</div>
        <div class="doc-title">{{ $title }}</div>
        @if($unit)
            <div class="meta">Unit Layanan: {{ $unit }}</div>
        @endif
    </div>
    <div class="header-right">
        <div class="meta">Dicetak: {{ $tanggal }}</div>
        <div class="meta">Halaman <span class="pagenum"></span></div>
    </div>
</div>

{{-- ===================== MODE: PER HARI ===================== --}}
@if($viewMode === 'per_hari')

    <span class="badge">Jadwal Per Hari — {{ $hariLabel }}</span>

    @if($jadwals->isEmpty())
        <p class="empty">Tidak ada jadwal untuk hari {{ $hariLabel }}.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th class="no-col">#</th>
                    <th>Poliklinik</th>
                    <th>Dokter</th>
                    <th class="jam-col">Jam Mulai</th>
                    <th class="jam-col">Jam Selesai</th>
                    <th>Ket.</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jadwals as $j)
                    <tr>
                        <td class="no-col">{{ $loop->iteration }}</td>
                        <td>{{ $j->poliklinik?->nama ?? '—' }}</td>
                        <td>{{ $j->nama_dokter ?? $j->dokter?->nama ?? '<span class="dash">—</span>' }}</td>
                        <td class="jam-col">
                            {{ $j->waktu_mulai ? $j->waktu_mulai->format('H:i') : '<span class="dash">—</span>' }}
                        </td>
                        <td class="jam-col">
                            {{ $j->waktu_selesai ? $j->waktu_selesai->format('H:i') : '<span class="dash">Selesai</span>' }}
                        </td>
                        <td>
                            @if($j->sesuai_perjanjian)
                                <span class="perjanjian-badge">Perjanjian</span>
                            @else
                                <span class="dash">—</span>
                            @endif
                        </td>
                        <td>{{ $j->catatan ?: '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

{{-- ===================== MODE: PER DOKTER ===================== --}}
@else

    <span class="badge">Jadwal Per Dokter — {{ $dokterNama }}</span>

    @if($jadwals->isEmpty())
        <p class="empty">Tidak ada jadwal untuk dokter ini.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th class="no-col">#</th>
                    <th>Hari</th>
                    <th>Poliklinik</th>
                    <th class="jam-col">Jam Mulai</th>
                    <th class="jam-col">Jam Selesai</th>
                    <th>Ket.</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jadwals as $j)
                    <tr>
                        <td class="no-col">{{ $loop->iteration }}</td>
                        <td>{{ $j->hari->getLabel() }}</td>
                        <td>{{ $j->poliklinik?->nama ?? '—' }}</td>
                        <td class="jam-col">
                            {{ $j->waktu_mulai ? $j->waktu_mulai->format('H:i') : '—' }}
                        </td>
                        <td class="jam-col">
                            {{ $j->waktu_selesai ? $j->waktu_selesai->format('H:i') : 'Selesai' }}
                        </td>
                        <td>
                            @if($j->sesuai_perjanjian)
                                <span class="perjanjian-badge">Perjanjian</span>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $j->catatan ?: '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

@endif

{{-- Disclaimer --}}
<div class="disclaimer">
    ⚠ Jadwal dapat berubah sewaktu-waktu. Hubungi bagian pendaftaran untuk konfirmasi.
</div>

{{-- Footer --}}
<div class="footer">
    <div class="footer-left">{{ $rsName }}</div>
    <div class="footer-right">Dicetak oleh sistem informasi RS — {{ $tanggal }}</div>
</div>

</body>
</html>
