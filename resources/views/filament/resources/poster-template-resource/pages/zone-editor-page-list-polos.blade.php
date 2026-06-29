<div>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Zone Editor — {{ $this->record->nama ?? 'Poster' }} — {{ config('app.name') }}</title>
        @filamentStyles
        @livewireStyles
        @vite(['resources/css/app.css'])
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="h-full bg-gray-50 antialiased">

    <header class="sticky top-0 z-40 flex items-center gap-3 px-4 h-12 bg-white border-b border-gray-200 shadow-sm">
        <a href="{{ \App\Filament\Resources\PosterTemplateResource::getUrl('index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-primary-600 transition shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="text-xs font-medium">Kembali</span>
        </a>
        <span class="text-gray-300 select-none">|</span>
        <span class="text-sm font-bold text-gray-800 truncate">Zone Editor</span>
        <span class="text-gray-300 select-none">/</span>
        <span class="text-sm font-semibold text-primary-600 truncate">{{ $this->record->nama ?? '' }}</span>
        <span class="ml-1 text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full font-medium">List Polos</span>
        <div class="ml-auto flex items-center gap-2">
            <button wire:click="save" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1.5 px-4 py-1.5 text-sm font-semibold text-white bg-pink-600 hover:bg-pink-500 rounded-lg transition shadow-sm disabled:opacity-50">
                <svg wire:loading.remove class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span wire:loading.remove>Simpan</span>
                <span wire:loading>Menyimpan...</span>
            </button>
        </div>
    </header>

    <main style="height: calc(100vh - 48px);">
    @php
        $zoneColors = [
            'zona_tanggal' => ['bg' => 'rgba(34,197,94,0.25)',  'border' => '#22C55E', 'label' => 'Tanggal'],
            'zona_jadwal'  => ['bg' => 'rgba(239,68,68,0.25)',  'border' => '#EF4444', 'label' => 'Jadwal'],
        ];

        $fallbackZones = [
            'zona_tanggal' => ['x' => 40,  'y' => 380, 'w' => 1000, 'h' => 70],
            'zona_jadwal'  => ['x' => 40,  'y' => 480, 'w' => 1000, 'h' => 1400],
        ];

        $savedConfig = $this->config ?? [];
        if (is_string($savedConfig)) {
            $savedConfig = json_decode($savedConfig, true) ?? [];
        }

        $activeZones = [];
        foreach ($fallbackZones as $key => $fallback) {
            $saved = $savedConfig[$key] ?? [];
            $activeZones[$key] = [
                'x' => (int) ($saved['x'] ?? $fallback['x']),
                'y' => (int) ($saved['y'] ?? $fallback['y']),
                'w' => (int) ($saved['w'] ?? $fallback['w']),
                'h' => (int) ($saved['h'] ?? $fallback['h']),
            ];
        }

        $g = $savedConfig['grid'] ?? [];

        $initialGapV              = (int)   ($g['gap_v']               ?? 16);
        $initialGapH              = (int)   ($g['gap_h']               ?? 12);
        $initialColNamaPersen     = (int)   ($g['col_nama_persen']     ?? 70);
        $initialGapHeaderDokter       = (int)   ($g['gap_header_dokter']      ?? 0);
        $initialDokterRaise           = (int)   ($g['dokter_raise']            ?? 20);
        $initialPaddingDokterPertama  = (int)   ($g['padding_dokter_pertama']  ?? 0);
        $initialPdTop                 = (int)   ($g['padding_dokter_top']      ?? 7);
        $initialPdRight               = (int)   ($g['padding_dokter_right']    ?? 8);
        $initialPdBottom              = (int)   ($g['padding_dokter_bottom']   ?? 7);
        $initialPdLeft                = (int)   ($g['padding_dokter_left']     ?? 14);
        $initialHeaderBorderWarna =         ($g['header_border_warna'] ?? '#dee2e6');
        $initialHeaderBorderWidth = (int)   ($g['header_border_width'] ?? 1);
        $initialHeaderBg1        =         ($g['header_bg_warna']    ?? '#1e3a5f');
        $initialHeaderBg2        =         ($g['header_bg_warna2']   ?? '');
        $initialHeaderRadius     = (int)   ($g['header_radius']      ?? 0);
        $initialHeaderFont       =         ($g['font_nama_poli']['nama'] ?? 'Montserrat');
        $initialHeaderWarna      =         ($g['warna_nama_poli']       ?? '#ffffff');
        $initialOutlinePoliW     = (int)   ($g['outline_poli_width']    ?? 0);
        $initialOutlinePoliC     =         ($g['outline_poli_warna']    ?? '#000000');
        $initialHeaderFontSize   = (int)   ($g['size_nama_poli']     ?? 30);
        $initialHeaderFontWeight =         ($g['header_font_weight'] ?? '700');
        $initialCardBg           =         ($g['card_bg_warna']      ?? '#f8f9fa');
        $initialCardRadius       = (int)   ($g['card_radius']        ?? 8);
        $initialCardBorderWarna  =         ($g['card_border_warna']  ?? '#dee2e6');
        $initialCardBorderWidth  = (int)   ($g['card_border_width']  ?? 1);
        $initialIsiFont          =         ($g['font_isi']['nama']   ?? 'Poppins');
        $initialWarnaDokter      =         ($g['warna_nama_dokter']     ?? '#1A1A1A');
        $initialOutlineDokterW   = (int)   ($g['outline_dokter_width']  ?? 0);
        $initialOutlineDokterC   =         ($g['outline_dokter_warna']  ?? '#000000');
        $initialWarnaJam         =         ($g['warna_jam']             ?? '#1A1A1A');
        $initialOutlineJamW      = (int)   ($g['outline_jam_width']     ?? 0);
        $initialOutlineJamC      =         ($g['outline_jam_warna']     ?? '#000000');
        $initialSizeNamaDokter   = (int)   ($g['size_nama_dokter']   ?? 26);
        $initialSizeJam          = (int)   ($g['size_jam']           ?? 26);
        $initialWeightNamaDokter =         ($g['weight_nama_dokter'] ?? '500');
        $initialWeightJam        =         ($g['weight_jam']         ?? '400');

        $initialTanggalFont         =         ($savedConfig['zona_tanggal']['font']          ?? $savedConfig['font_tanggal']['nama'] ?? 'Montserrat');
        $initialTanggalSize         = (int)   ($savedConfig['zona_tanggal']['size']          ?? 36);
        $initialTanggalWarna        =         ($savedConfig['zona_tanggal']['warna']         ?? '#1a1a2e');
        $initialTanggalWeight       =         ($savedConfig['zona_tanggal']['weight']        ?? '700');
        $initialTanggalAlign        =         ($savedConfig['zona_tanggal']['align']         ?? 'center');
        $initialTanggalOutlineWidth = (int)   ($savedConfig['zona_tanggal']['outline_width'] ?? 0);
        $initialTanggalOutlineWarna =         ($savedConfig['zona_tanggal']['outline_warna'] ?? '#000000');

        $templatePngUrl = $this->templatePngUrl;

        // Preview poli
        $previewPoli = [];
        if ($this->record?->rumah_sakit_id) {
            $rsId    = $this->record->rumah_sakit_id;
            $hariIni = strtoupper(now()->locale('id')->dayName);

            $jadwalHarian = \App\Models\JadwalHarian::whereDate('tanggal', now())
                ->whereHas('poliklinik', fn ($q) => $q->where('rumah_sakit_id', $rsId))
                ->with(['poliklinik', 'dokter'])->get()->groupBy('poliklinik_id');

            $jadwalPraktek = \App\Models\JadwalPraktek::where('hari', $hariIni)
                ->whereHas('poliklinik', fn ($q) => $q->where('rumah_sakit_id', $rsId))
                ->with(['poliklinik', 'dokter'])->get()->groupBy('poliklinik_id');

            $merged = $jadwalHarian->union($jadwalPraktek);

            $previewPoli = \App\Models\PoliKlinik::where('rumah_sakit_id', $rsId)
                ->where('aktif', true)->orderBy('nama')->limit(4)->get()
                ->map(fn ($poli) => [
                    'nama'   => $poli->nama,
                    'jadwal' => $merged->get($poli->id, collect())->map(fn ($r) => [
                        'nama_dokter' => $r->dokter?->nama ?? $r->nama_dokter ?? '-',
                        'jam_mulai'   => ($r->waktu_mulai ?? $r->jam_mulai)?->format('H:i'),
                        'jam_selesai' => ($r->waktu_selesai ?? $r->jam_selesai)?->format('H:i'),
                    ])->values()->all(),
                ])->values()->all();
        }

        if (empty($previewPoli)) {
            $previewPoli = [
                ['nama' => 'Poliklinik Gigi Umum', 'jadwal' => [
                    ['nama_dokter' => 'drg. Contoh A', 'jam_mulai' => '08:00', 'jam_selesai' => '14:00'],
                    ['nama_dokter' => 'drg. Contoh B', 'jam_mulai' => '14:00', 'jam_selesai' => '20:00'],
                ]],
                ['nama' => 'Poliklinik Anak', 'jadwal' => [
                    ['nama_dokter' => 'dr. Contoh C, Sp.A', 'jam_mulai' => '09:00', 'jam_selesai' => '15:00'],
                ]],
                ['nama' => 'Poliklinik Kandungan', 'jadwal' => [
                    ['nama_dokter' => 'dr. Contoh D, Sp.OG', 'jam_mulai' => '10:00', 'jam_selesai' => '16:00'],
                ]],
                ['nama' => 'Poliklinik Saraf', 'jadwal' => [
                    ['nama_dokter' => 'dr. Contoh E, Sp.S', 'jam_mulai' => '08:00', 'jam_selesai' => '12:00'],
                ]],
            ];
        }
    @endphp

    <div
        x-data="zoneEditorListPolos({
            initialZones: @js($activeZones),
            initialGapV: {{ $initialGapV }},
            initialGapH: {{ $initialGapH }},
            initialColNamaPersen: {{ $initialColNamaPersen }},
            initialGapHeaderDokter: {{ $initialGapHeaderDokter }},
            initialDokterRaise: {{ $initialDokterRaise }},
            initialPaddingDokterPertama: {{ $initialPaddingDokterPertama }},
            initialPdTop: {{ $initialPdTop }},
            initialPdRight: {{ $initialPdRight }},
            initialPdBottom: {{ $initialPdBottom }},
            initialPdLeft: {{ $initialPdLeft }},
            initialHeaderBorderWarna: @js($initialHeaderBorderWarna),
            initialHeaderBorderWidth: {{ $initialHeaderBorderWidth }},
            initialOutlinePoliW: {{ $initialOutlinePoliW }},
            initialOutlinePoliC: @js($initialOutlinePoliC),
            initialHeaderBg1: @js($initialHeaderBg1),
            initialHeaderBg2: @js($initialHeaderBg2),
            initialHeaderRadius: {{ $initialHeaderRadius }},
            initialHeaderFont: @js($initialHeaderFont),
            initialHeaderWarna: @js($initialHeaderWarna),
            initialHeaderFontSize: {{ $initialHeaderFontSize }},
            initialHeaderFontWeight: @js($initialHeaderFontWeight),
            initialCardBg: @js($initialCardBg),
            initialCardBorderWarna: @js($initialCardBorderWarna),
            initialCardBorderWidth: {{ $initialCardBorderWidth }},
            initialCardRadius: {{ $initialCardRadius }},
            initialIsiFont: @js($initialIsiFont),
            initialWarnaDokter: @js($initialWarnaDokter),
            initialOutlineDokterW: {{ $initialOutlineDokterW }},
            initialOutlineDokterC: @js($initialOutlineDokterC),
            initialWarnaJam: @js($initialWarnaJam),
            initialOutlineJamW: {{ $initialOutlineJamW }},
            initialOutlineJamC: @js($initialOutlineJamC),
            initialSizeNamaDokter: {{ $initialSizeNamaDokter }},
            initialSizeJam: {{ $initialSizeJam }},
            initialWeightNamaDokter: @js($initialWeightNamaDokter),
            initialWeightJam: @js($initialWeightJam),
            initialTanggalFont: @js($initialTanggalFont),
            initialTanggalSize: {{ $initialTanggalSize }},
            initialTanggalWarna: @js($initialTanggalWarna),
            initialTanggalWeight: @js($initialTanggalWeight),
            initialTanggalAlign: @js($initialTanggalAlign),
            initialTanggalOutlineWidth: {{ $initialTanggalOutlineWidth }},
            initialTanggalOutlineWarna: @js($initialTanggalOutlineWarna),
            state: $wire.$entangle('config')
        })"
        x-init="init()"
        class="flex h-full bg-gray-100"
    >
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="{{ $this->googleFontsUrl }}" rel="stylesheet">

        {{-- ── LEFT PANEL ──────────────────────────────────────────────────── --}}
        <div class="w-105 shrink-0 overflow-y-auto bg-white/90 backdrop-blur-xl border-r border-gray-200 p-6 space-y-6 shadow-2xl relative z-10">
            <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                <div class="p-2 bg-blue-50 rounded-lg text-blue-600 shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900 tracking-tight">Konfigurasi List Polos</h2>
                    <p class="text-xs text-gray-500">Layout jadwal tanpa hero (Barabai)</p>
                </div>
            </div>

            {{-- Gap Antar Card ──────────────────────────────────────────────── --}}
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-xs font-bold text-gray-600 uppercase tracking-wider">
                    <span class="text-lg">📏</span> Jarak
                </label>
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm space-y-3">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold text-gray-600 w-24 shrink-0">Antar Poli (V)</span>
                        <input type="range" x-model.number="gapV" @input="saveConfig()" min="0" max="60" step="2"
                            class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                        <div class="relative flex items-center">
                            <input type="number" x-model.number="gapV" @input="saveConfig()" min="0" max="60"
                                class="w-16 text-center text-sm font-bold bg-white border border-gray-200 rounded-xl pl-1 pr-4 py-1.5 focus:ring-2 focus:ring-blue-500 shadow-sm">
                            <span class="absolute right-2 text-xs font-semibold text-gray-400 pointer-events-none">px</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold text-gray-600 w-24 shrink-0">Lebar Nama %</span>
                        <input type="range" x-model.number="colNamaPersen" @input="saveConfig()" min="10" max="90" step="1"
                            class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                        <div class="relative flex items-center">
                            <input type="number" x-model.number="colNamaPersen" @input="saveConfig()" min="10" max="90"
                                class="w-16 text-center text-sm font-bold bg-white border border-gray-200 rounded-xl pl-1 pr-4 py-1.5 focus:ring-2 focus:ring-blue-500 shadow-sm">
                            <span class="absolute right-2 text-xs font-semibold text-gray-400 pointer-events-none">%</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold text-gray-600 w-24 shrink-0">Kolom (H)</span>
                        <input type="range" x-model.number="gapH" @input="saveConfig()" min="0" max="60" step="2"
                            class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                        <div class="relative flex items-center">
                            <input type="number" x-model.number="gapH" @input="saveConfig()" min="0" max="60"
                                class="w-16 text-center text-sm font-bold bg-white border border-gray-200 rounded-xl pl-1 pr-4 py-1.5 focus:ring-2 focus:ring-blue-500 shadow-sm">
                            <span class="absolute right-2 text-xs font-semibold text-gray-400 pointer-events-none">px</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold text-gray-600 w-24 shrink-0">Poli → Dokter</span>
                        <input type="range" x-model.number="gapHeaderDokter" @input="saveConfig()" min="0" max="40" step="1"
                            class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                        <div class="relative flex items-center">
                            <input type="number" x-model.number="gapHeaderDokter" @input="saveConfig()" min="0" max="40"
                                class="w-16 text-center text-sm font-bold bg-white border border-gray-200 rounded-xl pl-1 pr-4 py-1.5 focus:ring-2 focus:ring-blue-500 shadow-sm">
                            <span class="absolute right-2 text-xs font-semibold text-gray-400 pointer-events-none">px</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold text-gray-600 w-24 shrink-0">Naik Dokter</span>
                        <input type="range" x-model.number="dokterRaise" @input="saveConfig()" min="0" max="80" step="2"
                            class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                        <div class="relative flex items-center">
                            <input type="number" x-model.number="dokterRaise" @input="saveConfig()" min="0" max="80"
                                class="w-16 text-center text-sm font-bold bg-white border border-gray-200 rounded-xl pl-1 pr-4 py-1.5 focus:ring-2 focus:ring-blue-500 shadow-sm">
                            <span class="absolute right-2 text-xs font-semibold text-gray-400 pointer-events-none">px</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold text-gray-600 w-24 shrink-0">Padding Dokter 1</span>
                        <input type="range" x-model.number="paddingDokterPertama" @input="saveConfig()" min="0" max="60" step="2"
                            class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                        <div class="relative flex items-center">
                            <input type="number" x-model.number="paddingDokterPertama" @input="saveConfig()" min="0" max="60"
                                class="w-16 text-center text-sm font-bold bg-white border border-gray-200 rounded-xl pl-1 pr-4 py-1.5 focus:ring-2 focus:ring-blue-500 shadow-sm">
                            <span class="absolute right-2 text-xs font-semibold text-gray-400 pointer-events-none">px</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Padding Baris Dokter ────────────────────────────────────────── --}}
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-xs font-bold text-gray-600 uppercase tracking-wider">
                    <span class="text-lg">📐</span> Padding Baris Dokter
                </label>
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm space-y-2">
                    {{-- Atas --}}
                    <div class="flex items-center justify-center gap-2">
                        <span class="text-[10px] font-bold text-gray-400 w-10 text-right">Atas</span>
                        <input type="number" x-model.number="pdTop" @input="saveConfig()" min="0" max="60"
                            class="w-16 text-center text-xs font-bold bg-white border border-gray-200 rounded-lg py-1 shadow-sm focus:ring-2 focus:ring-blue-500">
                        <span class="text-[10px] text-gray-400 w-4">px</span>
                    </div>
                    {{-- Kiri & Kanan --}}
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1">
                            <span class="text-[10px] font-bold text-gray-400">Kiri</span>
                            <input type="number" x-model.number="pdLeft" @input="saveConfig()" min="0" max="60"
                                class="w-16 text-center text-xs font-bold bg-white border border-gray-200 rounded-lg py-1 shadow-sm focus:ring-2 focus:ring-blue-500">
                            <span class="text-[10px] text-gray-400">px</span>
                        </div>
                        <div class="h-6 w-px bg-gray-200"></div>
                        <div class="flex items-center gap-1">
                            <span class="text-[10px] font-bold text-gray-400">Kanan</span>
                            <input type="number" x-model.number="pdRight" @input="saveConfig()" min="0" max="60"
                                class="w-16 text-center text-xs font-bold bg-white border border-gray-200 rounded-lg py-1 shadow-sm focus:ring-2 focus:ring-blue-500">
                            <span class="text-[10px] text-gray-400">px</span>
                        </div>
                    </div>
                    {{-- Bawah --}}
                    <div class="flex items-center justify-center gap-2">
                        <span class="text-[10px] font-bold text-gray-400 w-10 text-right">Bawah</span>
                        <input type="number" x-model.number="pdBottom" @input="saveConfig()" min="0" max="60"
                            class="w-16 text-center text-xs font-bold bg-white border border-gray-200 rounded-lg py-1 shadow-sm focus:ring-2 focus:ring-blue-500">
                        <span class="text-[10px] text-gray-400 w-4">px</span>
                    </div>
                </div>
            </div>

            {{-- Style Card Poli ─────────────────────────────────────────────── --}}
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-xs font-bold text-gray-600 uppercase tracking-wider">
                    <span class="text-lg">🎨</span> Style Card Poli
                </label>

                <div class="space-y-5 p-5 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm">

                    {{-- Header Card --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="h-px flex-1 bg-gray-200"></div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Header Card (Nama Poli)</span>
                            <div class="h-px flex-1 bg-gray-200"></div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Background</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="headerBg1" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="headerBg1" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Gradasi <span class="text-[10px] text-gray-400 font-normal">(opsional)</span></span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="headerBg2" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <div class="relative">
                                        <input type="text" x-model="headerBg2" @input="saveConfig()" placeholder="kosong"
                                            class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-blue-500 pr-6">
                                        <button type="button" @click="headerBg2 = ''; saveConfig()"
                                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Radius (px)</span>
                                <input type="range" x-model.number="headerRadius" @input="saveConfig()" min="0" max="32" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500">
                                <input type="number" x-model.number="headerRadius" @input="saveConfig()" min="0" max="32" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Border Color</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="headerBorderWarna" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="headerBorderWarna" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Border Width</span>
                                <input type="range" x-model.number="headerBorderWidth" @input="saveConfig()" min="0" max="8" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500">
                                <input type="number" x-model.number="headerBorderWidth" @input="saveConfig()" min="0" max="8" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Warna Teks</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="headerWarna" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="headerWarna" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Outline (px)</span>
                                <input type="range" x-model.number="outlinePoliW" @input="saveConfig()" min="0" max="10" step="1" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500">
                                <input type="number" x-model.number="outlinePoliW" @input="saveConfig()" min="0" max="10" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Outline Color</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="outlinePoliC" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="outlinePoliC" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Font</span>
                                <select x-model="headerFont" @change="saveConfig()"
                                    class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 appearance-none bg-no-repeat"
                                    style="background-image:url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E');background-position:right .5rem center;background-size:1.25rem;">
                                    @foreach ($this::$availableFonts as $f)
                                    <option value="{{ $f }}">{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Font Weight</span>
                                <select x-model="headerFontWeight" @change="saveConfig()"
                                    class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 appearance-none bg-no-repeat"
                                    style="background-image:url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E');background-position:right .5rem center;background-size:1.25rem;">
                                    @foreach ($this::$availableFontWeights as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Ukuran Font (px)</span>
                                <input type="range" x-model.number="headerFontSize" @input="saveConfig()" min="12" max="60" step="1" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500">
                                <input type="number" x-model.number="headerFontSize" @input="saveConfig()" min="12" max="60" step="1" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>
                        </div>
                    </div>

                    {{-- Box Dokter + Jam --}}
                    <div class="pt-2">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="h-px flex-1 bg-gray-200"></div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Box Dokter / Jam</span>
                            <div class="h-px flex-1 bg-gray-200"></div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Background</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="cardBg" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="cardBg" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Border Color</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="cardBorderWarna" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="cardBorderWarna" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Border Width</span>
                                <input type="range" x-model.number="cardBorderWidth" @input="saveConfig()" min="0" max="8" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500">
                                <input type="number" x-model.number="cardBorderWidth" @input="saveConfig()" min="0" max="8" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Radius (px)</span>
                                <input type="range" x-model.number="cardRadius" @input="saveConfig()" min="0" max="32" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500">
                                <input type="number" x-model.number="cardRadius" @input="saveConfig()" min="0" max="32" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Font</span>
                                <select x-model="isiFont" @change="saveConfig()"
                                    class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 appearance-none bg-no-repeat"
                                    style="background-image:url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E');background-position:right .5rem center;background-size:1.25rem;">
                                    @foreach ($this::$availableFonts as $f)
                                    <option value="{{ $f }}">{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Nama Dokter --}}
                            <div class="flex items-center gap-2 mt-1">
                                <div class="h-px flex-1 bg-gray-100"></div>
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Nama Dokter</span>
                                <div class="h-px flex-1 bg-gray-100"></div>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Ukuran (px)</span>
                                <input type="range" x-model.number="sizeNamaDokter" @input="saveConfig()" min="12" max="60" step="1" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500">
                                <input type="number" x-model.number="sizeNamaDokter" @input="saveConfig()" min="12" max="60" step="1" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Weight</span>
                                <select x-model="weightNamaDokter" @change="saveConfig()"
                                    class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 appearance-none bg-no-repeat"
                                    style="background-image:url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E');background-position:right .5rem center;background-size:1.25rem;">
                                    @foreach ($this::$availableFontWeights as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Warna</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="warnaDokter" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="warnaDokter" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Outline (px)</span>
                                <input type="range" x-model.number="outlineDokterW" @input="saveConfig()" min="0" max="10" step="1" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500">
                                <input type="number" x-model.number="outlineDokterW" @input="saveConfig()" min="0" max="10" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Outline Color</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="outlineDokterC" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="outlineDokterC" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            {{-- Jam --}}
                            <div class="flex items-center gap-2 mt-1">
                                <div class="h-px flex-1 bg-gray-100"></div>
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Jam</span>
                                <div class="h-px flex-1 bg-gray-100"></div>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Ukuran (px)</span>
                                <input type="range" x-model.number="sizeJam" @input="saveConfig()" min="12" max="60" step="1" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500">
                                <input type="number" x-model.number="sizeJam" @input="saveConfig()" min="12" max="60" step="1" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Weight</span>
                                <select x-model="weightJam" @change="saveConfig()"
                                    class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 appearance-none bg-no-repeat"
                                    style="background-image:url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E');background-position:right .5rem center;background-size:1.25rem;">
                                    @foreach ($this::$availableFontWeights as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Warna</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="warnaJam" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="warnaJam" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Outline (px)</span>
                                <input type="range" x-model.number="outlineJamW" @input="saveConfig()" min="0" max="10" step="1" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500">
                                <input type="number" x-model.number="outlineJamW" @input="saveConfig()" min="0" max="10" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Outline Color</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="outlineJamC" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="outlineJamC" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Konfigurasi Zona ─────────────────────────────────────────────── --}}
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-xs font-bold text-gray-600 uppercase tracking-wider">
                    <span class="text-lg">⚙️</span> Konfigurasi Zona
                </label>

                <div class="space-y-5 p-5 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm">
                    {{-- Zona Tanggal --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="h-px flex-1 bg-gray-200"></div>
                            <span class="text-[10px] font-bold text-green-500 uppercase tracking-widest">● Tanggal</span>
                            <div class="h-px flex-1 bg-gray-200"></div>
                        </div>
                        <div class="space-y-3">
                            {{-- Posisi X/Y/W/H --}}
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">X</span>
                                    <input type="number" x-model.number="zones.zona_tanggal.x"
                                        @input="applyPosition(document.querySelector('[data-zone=zona_tanggal]'), zones.zona_tanggal); saveConfig()"
                                        min="0" max="1080"
                                        class="w-full text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm mt-0.5">
                                </div>
                                <div>
                                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Y</span>
                                    <input type="number" x-model.number="zones.zona_tanggal.y"
                                        @input="applyPosition(document.querySelector('[data-zone=zona_tanggal]'), zones.zona_tanggal); saveConfig()"
                                        min="0" max="1920"
                                        class="w-full text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm mt-0.5">
                                </div>
                                <div>
                                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">W</span>
                                    <input type="number" x-model.number="zones.zona_tanggal.w"
                                        @input="applyPosition(document.querySelector('[data-zone=zona_tanggal]'), zones.zona_tanggal); saveConfig()"
                                        min="50" max="1080"
                                        class="w-full text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm mt-0.5">
                                </div>
                                <div>
                                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">H</span>
                                    <input type="number" x-model.number="zones.zona_tanggal.h"
                                        @input="applyPosition(document.querySelector('[data-zone=zona_tanggal]'), zones.zona_tanggal); saveConfig()"
                                        min="20" max="1920"
                                        class="w-full text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm mt-0.5">
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Font Size (px)</span>
                                <div class="flex items-center gap-2">
                                    <input type="range" x-model.number="tanggalSize" @input="saveConfig()" min="16" max="80" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-green-500" style="max-width:150px;">
                                    <input type="number" x-model.number="tanggalSize" @input="saveConfig()" min="16" max="80" class="w-16 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Warna Teks</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="tanggalWarna" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="tanggalWarna" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Font Family</span>
                                <select x-model="tanggalFont" @change="saveConfig()"
                                    class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 appearance-none bg-no-repeat"
                                    style="background-image:url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E');background-position:right .5rem center;background-size:1.25rem;">
                                    @foreach ($this::$availableFonts as $f)
                                    <option value="{{ $f }}">{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Font Weight</span>
                                <select x-model="tanggalWeight" @change="saveConfig()"
                                    class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 appearance-none bg-no-repeat"
                                    style="background-image:url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E');background-position:right .5rem center;background-size:1.25rem;">
                                    @foreach ($this::$availableFontWeights as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Alignment</span>
                                <div class="flex gap-1">
                                    @foreach (['left' => 'L', 'center' => 'C', 'right' => 'R'] as $val => $lbl)
                                    <button type="button"
                                        @click="tanggalAlign='{{ $val }}'; saveConfig()"
                                        :class="tanggalAlign === '{{ $val }}' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600'"
                                        class="px-3 py-1.5 text-xs font-bold rounded-lg transition">{{ $lbl }}</button>
                                    @endforeach
                                </div>
                            </div>
                            {{-- Outline --}}
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Outline (px)</span>
                                <input type="range" x-model.number="tanggalOutlineWidth" @input="saveConfig()" min="0" max="10" step="1" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500">
                                <input type="number" x-model.number="tanggalOutlineWidth" @input="saveConfig()" min="0" max="10" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Outline Color</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="tanggalOutlineWarna" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="tanggalOutlineWarna" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Zona Jadwal --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="h-px flex-1 bg-gray-200"></div>
                            <span class="text-[10px] font-bold text-red-500 uppercase tracking-widest">● Jadwal</span>
                            <div class="h-px flex-1 bg-gray-200"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">X</span>
                                <input type="number" x-model.number="zones.zona_jadwal.x"
                                    @input="applyPosition(document.querySelector('[data-zone=zona_jadwal]'), zones.zona_jadwal); saveConfig()"
                                    min="0" max="1080"
                                    class="w-full text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm mt-0.5">
                            </div>
                            <div>
                                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Y</span>
                                <input type="number" x-model.number="zones.zona_jadwal.y"
                                    @input="applyPosition(document.querySelector('[data-zone=zona_jadwal]'), zones.zona_jadwal); saveConfig()"
                                    min="0" max="1920"
                                    class="w-full text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm mt-0.5">
                            </div>
                            <div>
                                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">W</span>
                                <input type="number" x-model.number="zones.zona_jadwal.w"
                                    @input="applyPosition(document.querySelector('[data-zone=zona_jadwal]'), zones.zona_jadwal); saveConfig()"
                                    min="50" max="1080"
                                    class="w-full text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm mt-0.5">
                            </div>
                            <div>
                                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">H</span>
                                <input type="number" x-model.number="zones.zona_jadwal.h"
                                    @input="applyPosition(document.querySelector('[data-zone=zona_jadwal]'), zones.zona_jadwal); saveConfig()"
                                    min="50" max="1920"
                                    class="w-full text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm mt-0.5">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Zone coordinate debug --}}
            <div class="p-4 bg-gray-900 rounded-2xl border border-gray-800 shadow-inner">
                <div class="flex items-center gap-2 mb-2">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Data Koordinat Zona (px)</p>
                </div>
                <div class="grid grid-cols-1 gap-1.5">
                    <template x-for="(val, key) in zones" :key="key">
                        <div class="font-mono text-[10px] text-gray-300 bg-gray-800/50 px-2 py-1.5 rounded-lg border border-gray-700/50 flex justify-between">
                            <span class="font-bold text-green-400" x-text="key.replace('zona_', '')"></span>
                            <div class="space-x-1">
                                <span class="text-gray-500">x:</span><span class="text-gray-100" x-text="val.x"></span>
                                <span class="text-gray-500">y:</span><span class="text-gray-100" x-text="val.y"></span>
                                <span class="text-gray-500">w:</span><span class="text-gray-100" x-text="val.w"></span>
                                <span class="text-gray-500">h:</span><span class="text-gray-100" x-text="val.h"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>{{-- end left panel --}}

        {{-- ── RIGHT PANEL ──────────────────────────────────────────────────── --}}
        <div id="previewPanel" class="flex-1 flex flex-col overflow-y-auto p-6 relative bg-slate-900"
             style="background-image: radial-gradient(#334155 1px, transparent 1px); background-size: 20px 20px;">

            <div class="absolute inset-0 bg-slate-900/90 pointer-events-none"></div>
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-blue-600/20 blur-[100px] pointer-events-none"></div>

            <div id="zoneEditorContainer" class="flex flex-col items-center gap-6 relative z-10 my-auto w-full">

                {{-- Legend Pill --}}
                <div class="flex items-center gap-6 text-sm bg-white/10 backdrop-blur-md px-6 py-3 rounded-full border border-white/10 shadow-2xl">
                    <div class="flex items-center gap-4">
                        @foreach ($zoneColors as $key => $c)
                            <span class="flex items-center gap-2 text-xs font-semibold text-slate-200 tracking-wide">
                                <span class="w-3 h-3 rounded-full shadow-[0_0_10px_rgba(255,255,255,0.2)]" style="background:{{ $c['border'] }}"></span>
                                {{ $c['label'] }}
                            </span>
                        @endforeach
                    </div>
                    <div class="w-px h-4 bg-white/20"></div>
                    <span class="text-xs font-medium text-slate-300 italic flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/>
                        </svg>
                        Drag &amp; resize zona di canvas
                    </span>
                </div>

                {{-- Canvas 540×960 (50% of 1080×1920) --}}
                <div class="relative rounded-xl border border-white/20 bg-slate-800 shadow-[0_20px_60px_-15px_rgba(0,0,0,0.7)] ring-1 ring-white/10"
                     style="width:540px; height:960px;"
                     id="zone-canvas">

                    @if ($templatePngUrl)
                        <img src="{{ $templatePngUrl }}"
                             class="absolute inset-0 w-full h-full object-cover pointer-events-none rounded-xl"
                             alt="Template Preview">
                    @else
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 rounded-xl bg-slate-800/80">
                            <div class="p-6 bg-slate-900/50 rounded-2xl border border-slate-700/50 flex flex-col items-center text-center shadow-2xl">
                                <span class="text-5xl mb-4">🖼️</span>
                                <h3 class="text-white font-bold mb-1">Template Belum Tersedia</h3>
                                <p class="text-xs max-w-50">Upload template PNG terlebih dahulu di halaman edit</p>
                            </div>
                        </div>
                    @endif

                    @foreach ($activeZones as $key => $pos)
                        @php $c = $zoneColors[$key]; @endphp
                        <div class="zone-box absolute cursor-move select-none rounded-lg shadow-lg backdrop-blur-[2px] hover:brightness-110"
                             data-zone="{{ $key }}"
                             style="
                                 left:  {{ $pos['x'] / 1080 * 100 }}%;
                                 top:   {{ $pos['y'] / 1920 * 100 }}%;
                                 width: {{ $pos['w'] / 1080 * 100 }}%;
                                 height:{{ $pos['h'] / 1920 * 100 }}%;
                                 background: {{ str_replace('0.25', '0.15', $c['bg']) }};
                                 border: 2px solid {{ $c['border'] }};
                             ">
                            <span class="absolute top-0 left-0 text-[10px] px-2 py-1 font-bold text-white rounded-br-lg shadow-sm tracking-wide"
                                  style="background:{{ $c['border'] }}">
                                {{ $c['label'] }}
                            </span>

                            @if ($key === 'zona_jadwal')
                            {{-- Preview table --}}
                            <div class="absolute" style="left:4px; top:24px; right:4px; bottom:4px; overflow:hidden;">
                                <table style="width:100%; border-collapse:separate; border-spacing:0; table-layout:fixed;">
                                    <colgroup>
                                        <col>
                                        <col :style="`width:${gapH * 0.5}px`">
                                        <col style="width:27%">
                                    </colgroup>
                                    <tbody>
                                    @foreach ($previewPoli as $idx => $poli)
                                    @if ($idx > 0)
                                    <tr><td colspan="3" :style="`height:${Math.max(0,(gapV-dokterRaise))*0.5}px; padding:0; border:none; background:transparent;`"></td></tr>
                                    @endif
                                    <tr style="position:relative; z-index:2;">
                                        <td :style="`background:${headerBg2 ? 'linear-gradient(to right,'+headerBg1+','+headerBg2+')' : headerBg1}; padding:3px 6px; font-family:'${headerFont}',sans-serif; font-size:${headerFontSize*0.5}px; color:${headerWarna}; font-weight:${headerFontWeight}; border:${headerBorderWidth*0.5}px solid ${headerBorderWarna}; border-radius:${headerRadius*0.5}px; line-height:1.2;`">{{ $poli['nama'] }}</td>
                                        <td style="background:transparent; padding:0; border:none;"></td>
                                        <td :style="`background:${headerBg2 ? 'linear-gradient(to right,'+headerBg1+','+headerBg2+')' : headerBg1}; padding:3px 4px; font-family:'${headerFont}',sans-serif; font-size:${headerFontSize*0.5}px; color:${headerWarna}; font-weight:${headerFontWeight}; text-align:center; border:${headerBorderWidth*0.5}px solid ${headerBorderWarna}; border-radius:${headerRadius*0.5}px;`">JAM</td>
                                    </tr>
                                    <template x-if="gapHeaderDokter > 0">
                                        <tr><td colspan="3" :style="`height:${gapHeaderDokter*0.5}px; padding:0; border:none; background:transparent;`"></td></tr>
                                    </template>
                                    @foreach ($poli['jadwal'] as $dRow)
                                    <tr :style="`position:relative; top:-${dokterRaise*0.5}px; z-index:1;`">
                                        <td style="background:transparent; padding:0; vertical-align:top;">
                                            <div :style="`display:block; width:${colNamaPersen}%; background:${cardBg}; padding-top:${ {{ $loop->first ? 1 : 0 }} ? (paddingDokterPertama||pdTop)*0.5 : pdTop*0.5}px; padding-right:0; padding-bottom:${pdBottom*0.5}px; padding-left:${pdLeft*0.5}px; font-family:'${isiFont}',sans-serif; font-size:${sizeNamaDokter*0.5}px; color:${warnaDokter}; font-weight:${weightNamaDokter}; border-left:${cardBorderWidth*0.5}px solid ${cardBorderWarna}; border-bottom:${cardBorderWidth*0.5}px solid ${cardBorderWarna}; line-height:1.3; box-sizing:border-box;`">{{ $dRow['nama_dokter'] }}</div>
                                        </td>
                                        <td style="background:transparent; padding:0;"></td>
                                        <td :style="`background:${cardBg}; padding-top:${ {{ $loop->first ? 1 : 0 }} ? (paddingDokterPertama||pdTop)*0.5 : pdTop*0.5}px; padding-right:${pdRight*0.5}px; padding-bottom:${pdBottom*0.5}px; padding-left:0; font-family:'${isiFont}',sans-serif; font-size:${sizeJam*0.5}px; color:${warnaJam}; font-weight:${weightJam}; text-align:center; border-right:${cardBorderWidth*0.5}px solid ${cardBorderWarna}; border-bottom:${cardBorderWidth*0.5}px solid ${cardBorderWarna}; line-height:1.3;`">{{ $dRow['jam_mulai'] }}</td>
                                    </tr>
                                    @endforeach
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @elseif ($key === 'zona_tanggal')
                            {{-- Preview Tanggal --}}
                            <div :style="{ position:'absolute', inset:0, display:'flex', alignItems:'center', justifyContent: tanggalAlign === 'center' ? 'center' : (tanggalAlign === 'right' ? 'flex-end' : 'flex-start'), padding:'0 4px', pointerEvents:'none' }">
                                <div :style="{ fontSize: (tanggalSize * 0.5) + 'px', color: tanggalWarna, fontFamily: tanggalFont, fontWeight: tanggalWeight, lineHeight: 1.2, WebkitTextStroke: tanggalOutlineWidth > 0 ? (tanggalOutlineWidth * 0.5) + 'px ' + tanggalOutlineWarna : 'unset' }">Sabtu, 07 Juni 2026</div>
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    </main>

    @livewire(\Filament\Notifications\Livewire\Notifications::class)
    @livewireScripts
    @filamentScripts(null, true)

    <script src="https://cdn.jsdelivr.net/npm/interactjs@1.10.27/dist/interact.min.js"></script>
    <script>
    const CANVAS_W = 1080;
    const CANVAS_H = 1920;
    const SCALE    = 540 / 1080;

    function zoneEditorListPolos(config) {
        return {
            zones: config.initialZones,
            gapV:                config.initialGapV              ?? 16,
            gapH:                config.initialGapH              ?? 12,
            colNamaPersen:       config.initialColNamaPersen     ?? 70,
            gapHeaderDokter:          config.initialGapHeaderDokter         ?? 0,
            dokterRaise:              config.initialDokterRaise              ?? 20,
            paddingDokterPertama:     config.initialPaddingDokterPertama     ?? 0,
            pdTop:                    config.initialPdTop                    ?? 7,
            pdRight:                  config.initialPdRight                  ?? 8,
            pdBottom:                 config.initialPdBottom                 ?? 7,
            pdLeft:                   config.initialPdLeft                   ?? 14,
            headerBorderWarna:   config.initialHeaderBorderWarna ?? '#dee2e6',
            headerBorderWidth:   config.initialHeaderBorderWidth ?? 1,
            outlinePoliW:        config.initialOutlinePoliW      ?? 0,
            outlinePoliC:        config.initialOutlinePoliC      ?? '#000000',
            headerBg1:         config.initialHeaderBg1       ?? '#1e3a5f',
            headerBg2:         config.initialHeaderBg2       ?? '',
            headerRadius:      config.initialHeaderRadius    ?? 0,
            headerFont:        config.initialHeaderFont      ?? 'Montserrat',
            headerWarna:       config.initialHeaderWarna     ?? '#ffffff',
            headerFontSize:    config.initialHeaderFontSize  ?? 30,
            headerFontWeight:  config.initialHeaderFontWeight ?? '700',
            cardBg:            config.initialCardBg          ?? '#f8f9fa',
            cardBorderWarna:   config.initialCardBorderWarna ?? '#dee2e6',
            cardBorderWidth:   config.initialCardBorderWidth ?? 1,
            cardRadius:        config.initialCardRadius      ?? 8,
            isiFont:           config.initialIsiFont         ?? 'Poppins',
            warnaDokter:       config.initialWarnaDokter      ?? '#1A1A1A',
            outlineDokterW:    config.initialOutlineDokterW  ?? 0,
            outlineDokterC:    config.initialOutlineDokterC  ?? '#000000',
            warnaJam:          config.initialWarnaJam        ?? '#1A1A1A',
            outlineJamW:       config.initialOutlineJamW     ?? 0,
            outlineJamC:       config.initialOutlineJamC     ?? '#000000',
            sizeNamaDokter:    config.initialSizeNamaDokter  ?? 26,
            sizeJam:           config.initialSizeJam         ?? 26,
            weightNamaDokter:  config.initialWeightNamaDokter ?? '500',
            weightJam:         config.initialWeightJam       ?? '400',
            tanggalFont:       config.initialTanggalFont     ?? 'Montserrat',
            tanggalSize:       config.initialTanggalSize     ?? 36,
            tanggalWarna:      config.initialTanggalWarna    ?? '#1a1a2e',
            tanggalWeight:     config.initialTanggalWeight   ?? '700',
            tanggalAlign:        config.initialTanggalAlign        ?? 'center',
            tanggalOutlineWidth: config.initialTanggalOutlineWidth ?? 0,
            tanggalOutlineWarna: config.initialTanggalOutlineWarna ?? '#000000',
            state: config.state,

            init() {
                if (!this.state || Object.keys(this.state).length === 0) {
                    this.saveConfig();
                } else {
                    if (this.state.zona_tanggal?.x     !== undefined) this.zones.zona_tanggal = { ...this.zones.zona_tanggal, x: this.state.zona_tanggal.x, y: this.state.zona_tanggal.y, w: this.state.zona_tanggal.w, h: this.state.zona_tanggal.h };
                    if (this.state.zona_jadwal?.x      !== undefined) this.zones.zona_jadwal  = { ...this.zones.zona_jadwal,  x: this.state.zona_jadwal.x,  y: this.state.zona_jadwal.y,  w: this.state.zona_jadwal.w,  h: this.state.zona_jadwal.h  };
                    if (this.state.grid?.gap_v                  !== undefined) this.gapV               = this.state.grid.gap_v;
                    if (this.state.grid?.gap_h                  !== undefined) this.gapH               = this.state.grid.gap_h;
                    if (this.state.grid?.col_nama_persen        !== undefined) this.colNamaPersen       = this.state.grid.col_nama_persen;
                    if (this.state.grid?.gap_header_dokter      !== undefined) this.gapHeaderDokter       = this.state.grid.gap_header_dokter;
                    if (this.state.grid?.dokter_raise           !== undefined) this.dokterRaise           = this.state.grid.dokter_raise;
                    if (this.state.grid?.padding_dokter_pertama !== undefined) this.paddingDokterPertama  = this.state.grid.padding_dokter_pertama;
                    if (this.state.grid?.padding_dokter_top    !== undefined) this.pdTop                  = this.state.grid.padding_dokter_top;
                    if (this.state.grid?.padding_dokter_right  !== undefined) this.pdRight                = this.state.grid.padding_dokter_right;
                    if (this.state.grid?.padding_dokter_bottom !== undefined) this.pdBottom               = this.state.grid.padding_dokter_bottom;
                    if (this.state.grid?.padding_dokter_left   !== undefined) this.pdLeft                 = this.state.grid.padding_dokter_left;
                    if (this.state.grid?.header_border_warna    !== undefined) this.headerBorderWarna  = this.state.grid.header_border_warna;
                    if (this.state.grid?.header_border_width    !== undefined) this.headerBorderWidth  = this.state.grid.header_border_width;
                    if (this.state.grid?.outline_poli_width     !== undefined) this.outlinePoliW       = this.state.grid.outline_poli_width;
                    if (this.state.grid?.outline_poli_warna     !== undefined) this.outlinePoliC       = this.state.grid.outline_poli_warna;
                    if (this.state.grid?.header_bg_warna        !== undefined) this.headerBg1       = this.state.grid.header_bg_warna;
                    if (this.state.grid?.header_bg_warna2       !== undefined) this.headerBg2       = this.state.grid.header_bg_warna2;
                    if (this.state.grid?.header_radius          !== undefined) this.headerRadius    = this.state.grid.header_radius;
                    if (this.state.grid?.font_nama_poli?.nama   !== undefined) this.headerFont      = this.state.grid.font_nama_poli.nama;
                    if (this.state.grid?.warna_nama_poli        !== undefined) this.headerWarna     = this.state.grid.warna_nama_poli;
                    if (this.state.grid?.size_nama_poli         !== undefined) this.headerFontSize  = this.state.grid.size_nama_poli;
                    if (this.state.grid?.header_font_weight     !== undefined) this.headerFontWeight = this.state.grid.header_font_weight;
                    if (this.state.grid?.card_bg_warna          !== undefined) this.cardBg          = this.state.grid.card_bg_warna;
                    if (this.state.grid?.card_border_warna      !== undefined) this.cardBorderWarna = this.state.grid.card_border_warna;
                    if (this.state.grid?.card_border_width      !== undefined) this.cardBorderWidth = this.state.grid.card_border_width;
                    if (this.state.grid?.card_radius            !== undefined) this.cardRadius      = this.state.grid.card_radius;
                    if (this.state.grid?.font_isi?.nama         !== undefined) this.isiFont         = this.state.grid.font_isi.nama;
                    if (this.state.grid?.warna_nama_dokter      !== undefined) this.warnaDokter     = this.state.grid.warna_nama_dokter;
                    if (this.state.grid?.outline_dokter_width   !== undefined) this.outlineDokterW  = this.state.grid.outline_dokter_width;
                    if (this.state.grid?.outline_dokter_warna   !== undefined) this.outlineDokterC  = this.state.grid.outline_dokter_warna;
                    if (this.state.grid?.warna_jam              !== undefined) this.warnaJam        = this.state.grid.warna_jam;
                    if (this.state.grid?.outline_jam_width      !== undefined) this.outlineJamW     = this.state.grid.outline_jam_width;
                    if (this.state.grid?.outline_jam_warna      !== undefined) this.outlineJamC     = this.state.grid.outline_jam_warna;
                    if (this.state.grid?.size_nama_dokter       !== undefined) this.sizeNamaDokter  = this.state.grid.size_nama_dokter;
                    if (this.state.grid?.size_jam               !== undefined) this.sizeJam         = this.state.grid.size_jam;
                    if (this.state.grid?.weight_nama_dokter     !== undefined) this.weightNamaDokter = this.state.grid.weight_nama_dokter;
                    if (this.state.grid?.weight_jam             !== undefined) this.weightJam       = this.state.grid.weight_jam;
                    if (this.state.zona_tanggal?.font           !== undefined) this.tanggalFont     = this.state.zona_tanggal.font;
                    if (this.state.zona_tanggal?.size           !== undefined) this.tanggalSize     = this.state.zona_tanggal.size;
                    if (this.state.zona_tanggal?.warna          !== undefined) this.tanggalWarna    = this.state.zona_tanggal.warna;
                    if (this.state.zona_tanggal?.weight         !== undefined) this.tanggalWeight   = this.state.zona_tanggal.weight;
                    if (this.state.zona_tanggal?.align          !== undefined) this.tanggalAlign        = this.state.zona_tanggal.align;
                    if (this.state.zona_tanggal?.outline_width  !== undefined) this.tanggalOutlineWidth = this.state.zona_tanggal.outline_width;
                    if (this.state.zona_tanggal?.outline_warna  !== undefined) this.tanggalOutlineWarna = this.state.zona_tanggal.outline_warna;
                }
                this.$nextTick(() => this.setupInteract());
            },

            setupInteract() {
                const canvas = document.getElementById('zone-canvas');
                const panel  = document.getElementById('previewPanel');
                if (!canvas) return;

                interact('.zone-box', { context: canvas })
                    .draggable({
                        listeners: {
                            start: () => { if (panel) panel.style.overflowY = 'hidden'; },
                            end:   () => { if (panel) panel.style.overflowY = 'auto';   },
                            move: (event) => {
                                const box  = event.target;
                                const key  = box.dataset.zone;
                                const zone = this.zones[key];

                                zone.x = Math.round(zone.x + event.dx / SCALE);
                                zone.y = Math.round(zone.y + event.dy / SCALE);
                                zone.x = Math.max(0, Math.min(CANVAS_W - zone.w, zone.x));
                                zone.y = Math.max(0, Math.min(CANVAS_H - zone.h, zone.y));

                                this.applyPosition(box, zone);
                                this.saveConfig();
                            },
                        },
                    })
                    .resizable({
                        edges: { right: true, bottom: true, bottomRight: true },
                        listeners: {
                            move: (event) => {
                                const box  = event.target;
                                const key  = box.dataset.zone;
                                const zone = this.zones[key];

                                zone.w = Math.round(event.rect.width  / SCALE);
                                zone.h = Math.round(event.rect.height / SCALE);
                                zone.w = Math.min(CANVAS_W - zone.x, zone.w);
                                zone.h = Math.min(CANVAS_H - zone.y, zone.h);

                                this.applyPosition(box, zone);
                                this.saveConfig();
                            },
                        },
                    });
            },

            applyPosition(el, zone) {
                el.style.left   = (zone.x / CANVAS_W * 100) + '%';
                el.style.top    = (zone.y / CANVAS_H * 100) + '%';
                el.style.width  = (zone.w / CANVAS_W * 100) + '%';
                el.style.height = (zone.h / CANVAS_H * 100) + '%';
            },

            saveConfig() {
                this.state = {
                    zona_tanggal: {
                        ...this.zones.zona_tanggal,
                        font:   this.tanggalFont,
                        size:   parseInt(this.tanggalSize)  || 36,
                        warna:  this.tanggalWarna,
                        weight: this.tanggalWeight,
                        align:         this.tanggalAlign,
                        outline_width: parseInt(this.tanggalOutlineWidth) || 0,
                        outline_warna: this.tanggalOutlineWarna,
                    },
                    zona_jadwal: { ...this.zones.zona_jadwal },
                    font_tanggal: { sumber: 'google', nama: this.tanggalFont },
                    grid: {
                        gap_v:                parseInt(this.gapV)              || 0,
                        gap_h:                parseInt(this.gapH)              || 0,
                        col_nama_persen:      parseInt(this.colNamaPersen)     || 70,
                        gap_header_dokter:       parseInt(this.gapHeaderDokter)       || 0,
                        dokter_raise:            parseInt(this.dokterRaise)           || 0,
                        padding_dokter_pertama:  parseInt(this.paddingDokterPertama)  || 0,
                        padding_dokter_top:      parseInt(this.pdTop)                 || 0,
                        padding_dokter_right:    parseInt(this.pdRight)               || 0,
                        padding_dokter_bottom:   parseInt(this.pdBottom)              || 0,
                        padding_dokter_left:     parseInt(this.pdLeft)                || 0,
                        header_border_warna:  this.headerBorderWarna,
                        header_border_width:  parseInt(this.headerBorderWidth) || 1,
                        outline_poli_width:   parseInt(this.outlinePoliW)      || 0,
                        outline_poli_warna:   this.outlinePoliC,
                        header_bg_warna:    this.headerBg1,
                        header_bg_warna2:   this.headerBg2,
                        header_radius:      parseInt(this.headerRadius)   || 0,
                        header_font_weight: this.headerFontWeight,
                        card_bg_warna:      this.cardBg,
                        card_radius:        parseInt(this.cardRadius)     || 0,
                        card_border_warna:  this.cardBorderWarna,
                        card_border_width:  parseInt(this.cardBorderWidth)|| 1,
                        font_nama_poli:     { sumber: 'google', nama: this.headerFont },
                        warna_nama_poli:    this.headerWarna,
                        font_isi:           { sumber: 'google', nama: this.isiFont },
                        warna_nama_dokter:    this.warnaDokter,
                        outline_dokter_width: parseInt(this.outlineDokterW) || 0,
                        outline_dokter_warna: this.outlineDokterC,
                        warna_jam:            this.warnaJam,
                        outline_jam_width:    parseInt(this.outlineJamW)    || 0,
                        outline_jam_warna:    this.outlineJamC,
                        size_nama_poli:     parseInt(this.headerFontSize)    || 30,
                        size_nama_dokter:   parseInt(this.sizeNamaDokter)    || 26,
                        size_jam:           parseInt(this.sizeJam)           || 26,
                        weight_nama_dokter: this.weightNamaDokter,
                        weight_jam:         this.weightJam,
                    },
                };
            },
        };
    }
    </script>

    </body>
</html>
</div>
