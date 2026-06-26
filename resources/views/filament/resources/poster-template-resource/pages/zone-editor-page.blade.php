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

    {{-- Minimal Top Bar — no sidebar, clean --}}
    <header class="sticky top-0 z-40 flex items-center gap-3 px-4 h-12
                   bg-white
                   border-b border-gray-200
                   shadow-sm">
        <a href="{{ \App\Filament\Resources\PosterTemplateResource::getUrl('index') }}"
        class="inline-flex items-center gap-1.5 text-sm text-gray-500
                hover:text-primary-600 transition shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="text-xs font-medium">Kembali</span>
        </a>
        <span class="text-gray-300 select-none">|</span>
        <span class="text-sm font-bold text-gray-800 truncate">Zone Editor</span>
        <span class="text-gray-300 select-none">/</span>
        <span class="text-sm font-semibold text-primary-600 truncate">
            {{ $this->record->nama ?? '' }}
        </span>
        <div class="ml-auto flex items-center gap-2">
            <button wire:click="save" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1.5 px-4 py-1.5 text-sm font-semibold text-white
                           bg-pink-600 hover:bg-pink-500 rounded-lg
                           transition shadow-sm disabled:opacity-50">
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
            'zona_logo'   => ['bg' => 'rgba(59,130,246,0.25)', 'border' => '#3B82F6', 'label' => 'Logo'],
            'zona_tanggal' => ['bg' => 'rgba(34,197,94,0.25)', 'border' => '#22C55E', 'label' => 'Tanggal'],
            'zona_keterangan' => ['bg' => 'rgba(168,85,247,0.25)', 'border' => '#A855F7', 'label' => 'Keterangan'],
            'zona_jadwal' => ['bg' => 'rgba(239,68,68,0.25)',  'border' => '#EF4444', 'label' => 'Jadwal'],
        ];

        $fallbackZones = [
            'zona_logo'   => ['x' => 60, 'y' => 60,   'w' => 300,  'h' => 120],
            'zona_tanggal' => ['x' => 80, 'y' => 940, 'w' => 900, 'h' => 50],
            'zona_keterangan' => ['x' => 80, 'y' => 1000, 'w' => 900, 'h' => 50],
            'zona_jadwal' => ['x' => 40, 'y' => 1080, 'w' => 1000, 'h' => 780],
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

        $record = $this->record;
        $templatePngUrl = $this->templatePngUrl;

        $initialKolom = (int) ($savedConfig['grid']['kolom'] ?? 2);
        $initialGap   = (int) ($savedConfig['grid']['gap']   ?? 16);
        $initialHeroPercent = (int) ($savedConfig['tinggi_hero'] ?? 25);

        $initialHeaderBg1   =         ($savedConfig['grid']['header_bg_warna']         ?? '#7c3aed');
        $initialHeaderBg2   =         ($savedConfig['grid']['header_bg_warna2']        ?? '');
        $initialHeaderRadius= (int)   ($savedConfig['grid']['header_radius']           ?? 8);
        $initialHeaderFont  =         ($savedConfig['grid']['font_nama_poli']['nama']  ?? 'Montserrat');
        $initialHeaderWarna =         ($savedConfig['grid']['warna_nama_poli']         ?? '#FFFFFF');
        $initialCardBg          =     ($savedConfig['grid']['card_bg_warna']           ?? '#ffffff');
        $initialCardBorderWarna =     ($savedConfig['grid']['card_border_warna']       ?? '#e5e7eb');
        $initialCardBorderWidth = (int) ($savedConfig['grid']['card_border_width']     ?? 1);
        $initialCardRadius  = (int)   ($savedConfig['grid']['card_radius']             ?? 8);

        // Catatan (note) style
        $initialCatatanBg     = ($savedConfig['grid']['catatan_bg_warna']     ?? '#fef9c3');
        $initialCatatanWarna  = ($savedConfig['grid']['catatan_warna']        ?? '#1a1a2e');
        $initialCatatanBorder = ($savedConfig['grid']['catatan_border_warna'] ?? '#fde68a');
        $initialCatatanRadius = (int) ($savedConfig['grid']['catatan_radius'] ?? 4);
        $initialCatatanSize   = (int) ($savedConfig['grid']['catatan_size']   ?? 8);
        $initialCatatanFont   =         ($savedConfig['grid']['catatan_font']   ?? 'Poppins');
        $initialCatatanWeight =         ($savedConfig['grid']['catatan_weight'] ?? '400');

        // Preview pakai jadwal praktek/harian hari ini (4 poli pertama RS ini)
        $previewCardWidthPx = (($activeZones['zona_jadwal']['w'] ?? 1000) - ($initialGap * ($initialKolom - 1))) / max($initialKolom, 1);
        $previewSizeNamaPoli  = max(8, round($previewCardWidthPx * 0.045));
        $previewSizeNamaDokter = max(7, round($previewCardWidthPx * 0.04));
        $previewSizeJam       = max(6, round($previewCardWidthPx * 0.035));

        $previewPoli = [];
        if ($record?->rumah_sakit_id) {
            $hariIni = strtoupper(now()->locale('id')->dayName);
            $rsId    = $record->rumah_sakit_id;

            $jadwalHarian = \App\Models\JadwalHarian::whereDate('tanggal', now())
                ->whereHas('poliklinik', fn ($q) => $q->where('rumah_sakit_id', $rsId))
                ->with(['poliklinik', 'dokter'])
                ->get()
                ->groupBy('poliklinik_id');

            $jadwalPraktek = \App\Models\JadwalPraktek::where('hari', $hariIni)
                ->whereHas('poliklinik', fn ($q) => $q->where('rumah_sakit_id', $rsId))
                ->with(['poliklinik', 'dokter'])
                ->get()
                ->groupBy('poliklinik_id');

            $merged = $jadwalHarian->union($jadwalPraktek);

            $previewPoli = \App\Models\PoliKlinik::where('rumah_sakit_id', $rsId)
                ->where('aktif', true)
                ->orderBy('nama')
                ->limit(4)
                ->get()
                ->map(function ($poli) use ($merged) {
                    $rows = $merged->get($poli->id, collect());
                    return [
                        'nama'   => $poli->nama,
                        'jadwal' => $rows->map(fn ($r) => [
                            'nama_dokter' => $r->dokter?->nama ?? $r->nama_dokter ?? '-',
                            'jam_mulai'   => ($r->waktu_mulai ?? $r->jam_mulai)?->format('H:i'),
                            'jam_selesai' => ($r->waktu_selesai ?? $r->jam_selesai)?->format('H:i'),
                            'is_executive' => (bool) ($r->is_executive ?? false),
                        ])->values()->all(),
                    ];
                })->values()->all();
        }

        if (empty($previewPoli)) {
            $previewPoli = [
                ['nama' => 'Poliklinik Umum', 'jadwal' => [['nama_dokter' => 'dr. Contoh A', 'jam_mulai' => '08:00', 'jam_selesai' => '14:00', 'is_executive' => false]]],
                ['nama' => 'Poliklinik Anak', 'jadwal' => [['nama_dokter' => 'dr. Contoh B', 'jam_mulai' => '09:00', 'jam_selesai' => '15:00', 'is_executive' => false]]],
                ['nama' => 'Poliklinik Gigi', 'jadwal' => [['nama_dokter' => 'dr. Contoh C', 'jam_mulai' => '10:00', 'jam_selesai' => '16:00', 'is_executive' => false]]],
                ['nama' => 'Poliklinik Saraf', 'jadwal' => [['nama_dokter' => 'dr. Contoh D', 'jam_mulai' => '08:00', 'jam_selesai' => '12:00', 'is_executive' => false]]],
            ];
        }

        if (! collect($previewPoli)->flatMap(fn ($p) => $p['jadwal'])->contains(fn ($r) => $r['is_executive'] ?? false)) {
            $previewPoli[0]['jadwal'][] = [
                'nama_dokter' => 'dr. Contoh EC', 'jam_mulai' => '13:00', 'jam_selesai' => '15:00', 'is_executive' => true,
            ];
        }
    @endphp

    <div
        x-data="zoneEditor({
            initialZones: @js($activeZones),
            initialKolom: {{ $initialKolom }},
            initialGap:   {{ $initialGap }},
            initialHeroPercent: {{ $initialHeroPercent }},
            initialHeaderBg1: @js($initialHeaderBg1),
            initialHeaderBg2: @js($initialHeaderBg2),
            initialHeaderRadius: {{ $initialHeaderRadius }},
            initialHeaderFont: @js($initialHeaderFont),
            initialHeaderWarna: @js($initialHeaderWarna),
            initialCardBg: @js($initialCardBg),
            initialCardBorderWarna: @js($initialCardBorderWarna),
            initialCardBorderWidth: {{ $initialCardBorderWidth }},
            initialCardRadius: {{ $initialCardRadius }},
            initialHeaderFontSize: {{ (int) ($savedConfig['grid']['size_nama_poli'] ?? $previewSizeNamaPoli) }},
            initialSizeNamaDokter: {{ (int) ($savedConfig['grid']['size_nama_dokter'] ?? $previewSizeNamaDokter) }},
            initialSizeJam: {{ (int) ($savedConfig['grid']['size_jam'] ?? $previewSizeJam) }},
            initialEcBg: @js($savedConfig['grid']['ec_bg_warna'] ?? '#F0C040'),
            initialEcText: @js($savedConfig['grid']['ec_text_warna'] ?? '#1a1a2e'),
            initialCatatanBg: @js($initialCatatanBg),
            initialCatatanWarna: @js($initialCatatanWarna),
            initialCatatanBorder: @js($initialCatatanBorder),
            initialCatatanRadius: {{ $initialCatatanRadius }},
            initialCatatanSize: {{ $initialCatatanSize }},
            initialCatatanFont: @js($initialCatatanFont),
            initialCatatanWeight: @js($initialCatatanWeight),
            state: $wire.$entangle('config')
        })"
        x-init="init()"
        class="flex h-full bg-gray-100"
    >
        {{-- Google Fonts — daftar font dikelola di ZoneEditorPage::$availableFonts --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="{{ $this->googleFontsUrl }}" rel="stylesheet">

        {{-- ── LEFT PANEL: Controls ─────────────────────────────────────────── --}}
        <div class="w-[420px] shrink-0 overflow-y-auto bg-white/90 backdrop-blur-xl border-r border-gray-200 p-6 space-y-6 shadow-2xl relative z-10 custom-scrollbar">
            <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600 shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900 tracking-tight">Konfigurasi Desain</h2>
                    <p class="text-xs text-gray-500">Sesuaikan tampilan poster Anda</p>
                </div>
            </div>

            {{-- ── Tinggi Foto Hero ──────────────────────────────────────── --}}
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-xs font-bold text-gray-600 uppercase tracking-wider">
                    <span class="text-lg">📷</span> Tinggi Foto Hero
                </label>
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm transition-all hover:shadow hover:border-indigo-200">
                    <div class="flex items-center gap-4">
                        <input type="range" x-model="heroPercent" @input="saveConfig()" min="0" max="80" step="5" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                        <div class="relative flex items-center">
                            <input type="number" x-model="heroPercent" @input="saveConfig()" min="0" max="80" step="5"
                                class="w-16 text-center text-sm font-bold bg-white border border-gray-200 rounded-xl pl-1 pr-4 py-1.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                            <span class="absolute right-2 text-xs font-semibold text-gray-400 pointer-events-none">%</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-200 text-xs font-medium text-gray-500 flex items-center justify-between">
                        <span>Ukuran Piksel:</span>
                        <span class="font-mono bg-white px-2 py-0.5 rounded shadow-sm border border-gray-100" x-text="Math.round(heroPercent / 100 * 1920) + 'px'"></span>
                    </div>
                </div>
            </div>

            {{-- ── Grid Settings ────────────────────────────────────────── --}}
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-xs font-bold text-gray-600 uppercase tracking-wider">
                    <span class="text-lg">📱</span> Layout Grid
                </label>
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm transition-all hover:shadow hover:border-indigo-200 grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <span class="text-xs font-semibold text-gray-500">Jumlah Kolom</span>
                        <input type="number" x-model.number="kolom" @input="saveConfig()" min="1" max="4"
                            class="w-full text-center text-sm font-bold bg-white border border-gray-200 rounded-xl px-2 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                    </div>
                    <div class="space-y-1.5">
                        <span class="text-xs font-semibold text-gray-500">Gap Antar Card</span>
                        <div class="relative flex items-center">
                            <input type="number" x-model.number="gap" @input="saveConfig()" min="0" max="60"
                                class="w-full text-center text-sm font-bold bg-white border border-gray-200 rounded-xl pl-2 pr-6 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                            <span class="absolute right-3 text-xs font-semibold text-gray-400 pointer-events-none">px</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Card Style ──────────────────────────────────────────── --}}
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-xs font-bold text-gray-600 uppercase tracking-wider">
                    <span class="text-lg">🎨</span> Style Card Poli
                </label>

                <div class="space-y-5 p-5 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm transition-all hover:shadow hover:border-indigo-200">
                    
                    {{-- Header Style --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="h-px flex-1 bg-gray-200"></div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Header Card</span>
                            <div class="h-px flex-1 bg-gray-200"></div>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Background</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="headerBg1" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="headerBg1" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Gradasi <span class="text-[10px] text-gray-400 font-normal">(opsional)</span></span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="headerBg2" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <div class="relative">
                                        <input type="text" x-model="headerBg2" @input="saveConfig()" placeholder="kosong"
                                            class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-indigo-500 pr-6">
                                        <button type="button" @click="headerBg2 = ''; saveConfig()"
                                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition" title="Hapus gradasi">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Radius (px)</span>
                                <input type="range" x-model="headerRadius" @input="saveConfig()" min="0" max="32" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-indigo-500">
                                <input type="number" x-model="headerRadius" @input="saveConfig()" min="0" max="32" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Lebar Header (%)</span>
                                <input type="range" x-model.number="headerWidthPct" @input="saveConfig()" min="30" max="100" step="5" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-indigo-500">
                                <input type="number" x-model.number="headerWidthPct" @input="saveConfig()" min="30" max="100" step="5" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Font Weight</span>
                                <select x-model="headerFontWeight" @change="saveConfig()" class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-indigo-500 appearance-none bg-no-repeat" style="background-image:url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E');background-position:right .5rem center;background-size:1.25rem;">
                                    <option value="300">Light (300)</option>
                                    <option value="400">Normal (400)</option>
                                    <option value="500">Medium (500)</option>
                                    <option value="600">Semibold (600)</option>
                                    <option value="700">Bold (700)</option>
                                    <option value="800">Extra Bold (800)</option>
                                    <option value="900">Black (900)</option>
                                </select>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Font Style</span>
                                <div class="flex gap-1">
                                    <button type="button" @click="headerFontStyle='normal'; saveConfig()" :class="headerFontStyle === 'normal' ? 'bg-indigo-500 text-white' : 'bg-gray-200 text-gray-600'" class="px-3 py-1.5 text-xs font-bold rounded-lg transition">Normal</button>
                                    <button type="button" @click="headerFontStyle='italic'; saveConfig()" :class="headerFontStyle === 'italic' ? 'bg-indigo-500 text-white' : 'bg-gray-200 text-gray-600'" class="px-3 py-1.5 text-xs italic rounded-lg transition">Italic</button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Font Text</span>
                                <select x-model="headerFont" @change="saveConfig()" class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-no-repeat bg-right bg-white shadow-sm focus:ring-2 focus:ring-indigo-500" style="background-size: 1.25rem; background-position: right 0.25rem center;">
                                    @foreach ($this::$availableFonts as $f)
                                    <option value="{{ $f }}">{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Warna Teks</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="headerWarna" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="headerWarna" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Ukuran Font (px)</span>
                                <input type="range" x-model.number="headerFontSize" @input="saveConfig()" min="5" max="32" step="1" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-indigo-500">
                                <input type="number" x-model.number="headerFontSize" @input="saveConfig()" min="5" max="32" step="1" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>
                        </div>
                    </div>

                    {{-- Body Style --}}
                    <div class="pt-2">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="h-px flex-1 bg-gray-200"></div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Box Dokter</span>
                            <div class="h-px flex-1 bg-gray-200"></div>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Background</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="cardBg" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="cardBg" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Border Color</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="cardBorderWarna" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="cardBorderWarna" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Border Width</span>
                                <input type="range" x-model="cardBorderWidth" @input="saveConfig()" min="0" max="10" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-violet-500">
                                <input type="number" x-model="cardBorderWidth" @input="saveConfig()" min="0" max="10" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>
                            
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Radius (px)</span>
                                <input type="range" x-model="cardRadius" @input="saveConfig()" min="0" max="32" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-violet-500">
                                <input type="number" x-model="cardRadius" @input="saveConfig()" min="0" max="32" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Ukuran Nama Dokter (px)</span>
                                <input type="range" x-model.number="sizeNamaDokter" @input="saveConfig()" min="5" max="20" step="1" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-violet-500">
                                <input type="number" x-model.number="sizeNamaDokter" @input="saveConfig()" min="5" max="20" step="1" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Ukuran Jam (px)</span>
                                <input type="range" x-model.number="sizeJam" @input="saveConfig()" min="5" max="16" step="1" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-violet-500">
                                <input type="number" x-model.number="sizeJam" @input="saveConfig()" min="5" max="16" step="1" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Font Nama Dokter</span>
                                <select x-model="fontNamaDokter" @change="saveConfig()" class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-indigo-500 appearance-none bg-no-repeat" style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E'); background-position: right 0.5rem center; background-size: 1.25rem;">
                                    @foreach ($this::$availableFonts as $f)
                                    <option value="{{ $f }}">{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Font Jam</span>
                                <select x-model="fontJam" @change="saveConfig()" class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-indigo-500 appearance-none bg-no-repeat" style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E'); background-position: right 0.5rem center; background-size: 1.25rem;">
                                    @foreach ($this::$availableFonts as $f)
                                    <option value="{{ $f }}">{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Weight Nama Dokter</span>
                                <select x-model="weightNamaDokter" @change="saveConfig()" class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-indigo-500 appearance-none bg-no-repeat" style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E'); background-position: right 0.5rem center; background-size: 1.25rem;">
                                    @foreach ($this::$availableFontWeights as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Weight Jam</span>
                                <select x-model="weightJam" @change="saveConfig()" class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-indigo-500 appearance-none bg-no-repeat" style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E'); background-position: right 0.5rem center; background-size: 1.25rem;">
                                    @foreach ($this::$availableFontWeights as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Padding Atas Box Dokter (px)</span>
                                <input type="range" x-model.number="cardPaddingTop" @input="saveConfig()" min="0" max="30" step="1" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-violet-500">
                                <input type="number" x-model.number="cardPaddingTop" @input="saveConfig()" min="0" max="30" step="1" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>

                        </div>
                    </div>

                    {{-- Catatan Style --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="h-px flex-1 bg-gray-200"></div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Catatan / Note</span>
                            <div class="h-px flex-1 bg-gray-200"></div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Background</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="catatanBg" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="catatanBg" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Warna Teks</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="catatanWarna" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="catatanWarna" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Border</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="catatanBorder" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="catatanBorder" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Radius (px)</span>
                                <input type="range" x-model.number="catatanRadius" @input="saveConfig()" min="0" max="20" step="1" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-violet-500">
                                <input type="number" x-model.number="catatanRadius" @input="saveConfig()" min="0" max="20" step="1" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Ukuran Font (px)</span>
                                <input type="range" x-model.number="catatanSize" @input="saveConfig()" min="5" max="16" step="1" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-violet-500">
                                <input type="number" x-model.number="catatanSize" @input="saveConfig()" min="5" max="16" step="1" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Font Family</span>
                                <select x-model="catatanFont" @change="saveConfig()" class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-indigo-500 appearance-none bg-no-repeat" style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E'); background-position: right 0.5rem center; background-size: 1.25rem;">
                                    @foreach ($this::$availableFonts as $f)
                                    <option value="{{ $f }}">{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Font Weight</span>
                                <select x-model="catatanWeight" @change="saveConfig()" class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-indigo-500 appearance-none bg-no-repeat" style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E'); background-position: right 0.5rem center; background-size: 1.25rem;">
                                    @foreach ($this::$availableFontWeights as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Mini Preview --}}
                    <div class="pt-5 mt-2 border-t border-gray-200">
                        <div class="flex justify-center">
                            <div style="width:240px; position:relative; padding-top:16px;" class="transition-all duration-300">
                                {{-- Header Nama Poli (70% overlap) --}}
                                <div :style="{
                                    background: headerBg2 ? ('linear-gradient(135deg,' + headerBg1 + ',' + headerBg2 + ')') : headerBg1,
                                    borderRadius: headerRadius + 'px',
                                    padding: '8px 14px',
                                    width: headerWidthPct + '%',
                                    position: 'relative', zIndex: 2, lineHeight: 1.2,
                                    boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)'
                                }">
                                    <span :style="{ fontFamily: headerFont, color: headerWarna, fontWeight: parseInt(headerFontWeight), fontStyle: headerFontStyle, fontSize: headerFontSize + 'px', textTransform: 'uppercase', display: 'block', letterSpacing: '0.05em' }">Poli</span>
                                </div>

                                {{-- Body Card (Regular + EC sejajar) --}}
                                <div :style="{
                                    background: cardBg,
                                    border: cardBorderWidth + 'px solid ' + cardBorderWarna,
                                    borderRadius: cardRadius + 'px',
                                    marginTop: '-6px',
                                    padding: '14px 8px 8px',
                                    color: '#1f2937', position: 'relative', zIndex: 1,
                                    boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
                                    display: 'flex', gap: '4px'
                                }">
                                    {{-- Regular Dokter Column --}}
                                    <div style="flex:1;">
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2px;">
                                            <span :style="{ fontSize: sizeNamaDokter + 'px', fontFamily: fontNamaDokter, fontWeight: parseInt(weightNamaDokter), overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }">dr. Dokter</span>
                                            <span :style="{ fontSize: sizeJam + 'px', fontFamily: fontJam, color: '#1A1A1A', whiteSpace: 'nowrap', fontWeight: parseInt(weightJam), marginLeft: '4px' }">08:00</span>
                                        </div>
                                        {{-- Sample catatan --}}
                                        <div :style="{
                                            display: 'inline-block',
                                            background: catatanBg,
                                            color: catatanWarna,
                                            border: '1px solid ' + catatanBorder,
                                            borderRadius: catatanRadius + 'px',
                                            padding: '3px 6px',
                                            fontSize: catatanSize + 'px',
                                            fontFamily: catatanFont,
                                            fontWeight: parseInt(catatanWeight),
                                            marginTop: '4px',
                                            lineHeight: 1.3
                                        }">Khusus dengan jaminan Umum &amp; Asuransi</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ZONA STYLING --}}
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-xs font-bold text-gray-600 uppercase tracking-wider">
                    <span class="text-lg">⚙️</span> Konfigurasi Zona
                </label>

                <div class="space-y-5 p-5 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm">
                    {{-- Zona Logo --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="h-px flex-1 bg-gray-200"></div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Logo</span>
                            <div class="h-px flex-1 bg-gray-200"></div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Skala Logo (%)</span>
                                <input type="range" x-model.number="logoScale" @input="saveConfig()" min="50" max="150" step="10" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500" style="max-width:150px;">
                                <input type="number" x-model.number="logoScale" @input="saveConfig()" min="50" max="150" step="10" class="w-16 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Opacity</span>
                                <input type="range" x-model.number="logoOpacity" @input="saveConfig()" min="0" max="100" step="10" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500" style="max-width:150px;">
                                <input type="number" x-model.number="logoOpacity" @input="saveConfig()" min="0" max="100" step="10" class="w-16 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Padding (px)</span>
                                <input type="range" x-model.number="logoPadding" @input="saveConfig()" min="0" max="50" step="5" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500" style="max-width:150px;">
                                <input type="number" x-model.number="logoPadding" @input="saveConfig()" min="0" max="50" step="5" class="w-16 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Background Color</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="logoBg" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="logoBg" @input="saveConfig()" placeholder="transparent" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Zona Tanggal --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="h-px flex-1 bg-gray-200"></div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tanggal</span>
                            <div class="h-px flex-1 bg-gray-200"></div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Font Size (px)</span>
                                <div class="flex items-center gap-2">
                                    <input type="range" x-model.number="tanggalSize" @input="saveConfig()" min="8" max="80" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-indigo-500" style="max-width:150px;">
                                    <input type="number" x-model.number="tanggalSize" @input="saveConfig()" min="8" max="80" class="w-16 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Warna Teks</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="tanggalWarna" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="tanggalWarna" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Background</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="tanggalBg" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="tanggalBg" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Font Family</span>
                                <select x-model="tanggalFont" @change="saveConfig()" class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-indigo-500 appearance-none bg-no-repeat" style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E'); background-position: right 0.5rem center; background-size: 1.25rem;">
                                    @foreach ($this::$availableFonts as $f)
                                    <option value="{{ $f }}">{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Font Weight</span>
                                <select x-model="tanggalWeight" @change="saveConfig()" class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-indigo-500 appearance-none bg-no-repeat" style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E'); background-position: right 0.5rem center; background-size: 1.25rem;">
                                    @foreach ($this::$availableFontWeights as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Alignment</span>
                                <div class="flex gap-1">
                                    @foreach (['left' => 'L', 'center' => 'C', 'right' => 'R'] as $val => $label)
                                    <button type="button" @click="tanggalAlign='{{ $val }}'; saveConfig()" :class="tanggalAlign === '{{ $val }}' ? 'bg-indigo-500 text-white' : 'bg-gray-200 text-gray-600'" class="px-3 py-1.5 text-xs font-bold rounded-lg transition">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Zona Keterangan --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="h-px flex-1 bg-gray-200"></div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Keterangan</span>
                            <div class="h-px flex-1 bg-gray-200"></div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Font Size (px)</span>
                                <div class="flex items-center gap-2">
                                    <input type="range" x-model.number="keteranganSize" @input="saveConfig()" min="8" max="60" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-indigo-500" style="max-width:150px;">
                                    <input type="number" x-model.number="keteranganSize" @input="saveConfig()" min="8" max="60" class="w-16 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Warna Teks</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="keteranganWarna" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="keteranganWarna" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Font Family</span>
                                <select x-model="keteranganFont" @change="saveConfig()" class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-indigo-500 appearance-none bg-no-repeat" style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E'); background-position: right 0.5rem center; background-size: 1.25rem;">
                                    @foreach ($this::$availableFonts as $f)
                                    <option value="{{ $f }}">{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Background</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="keteranganBg" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                                    <input type="text" x-model="keteranganBg" @input="saveConfig()" placeholder="kosong" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Font Weight</span>
                                <select x-model="keteranganWeight" @change="saveConfig()" class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-indigo-500 appearance-none bg-no-repeat" style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E'); background-position: right 0.5rem center; background-size: 1.25rem;">
                                    @foreach ($this::$availableFontWeights as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-600">Alignment</span>
                                <div class="flex gap-1">
                                    @foreach (['left' => 'L', 'center' => 'C', 'right' => 'R'] as $val => $label)
                                    <button type="button" @click="keteranganAlign='{{ $val }}'; saveConfig()" :class="keteranganAlign === '{{ $val }}' ? 'bg-indigo-500 text-white' : 'bg-gray-200 text-gray-600'" class="px-3 py-1.5 text-xs font-bold rounded-lg transition">{{ $label }}</button>
                                    @endforeach
                                </div>
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
                            <span class="font-bold text-indigo-400" x-text="key.replace('zona_', '')"></span>
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
        </div>

        {{-- ── RIGHT PANEL: Preview Canvas ────────────────────────────────── --}}
        <div id="previewPanel" class="flex-1 flex flex-col overflow-y-auto p-6 relative bg-slate-900" style="background-image: radial-gradient(#334155 1px, transparent 1px); background-size: 20px 20px;">

            {{-- Aesthetic Background Elements --}}
            <div class="absolute inset-0 bg-slate-900/90 pointer-events-none"></div>
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-indigo-600/20 blur-[100px] pointer-events-none"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-blue-600/20 blur-[100px] pointer-events-none"></div>

            {{-- Preview Image Display (hidden by default) --}}
            <div id="previewImageContainer" class="hidden relative z-10 w-full items-center gap-4" style="display:none; flex-direction:column;">
                <style>
                    #previewImageContainer:not(.hidden) { display: flex; flex-direction: column; }
                </style>
                <button onclick="document.getElementById('previewImageContainer').classList.add('hidden'); document.getElementById('zoneEditorContainer').classList.remove('hidden');" class="text-sm px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg transition">
                    ← Kembali ke Zone Editor
                </button>
                <img id="previewImageDisplay" src="" alt="Poster Preview" class="max-w-full h-auto rounded-lg shadow-2xl" style="max-width:600px; max-height:80vh;">
            </div>

            {{-- Zone Editor Container --}}
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
                        <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                        Drag & resize zona di canvas
                    </span>
                </div>

                {{-- Canvas — 540×960 (50% of 1080×1920) --}}
                <div
                    class="relative rounded-xl border border-white/20 bg-slate-800 shadow-[0_20px_60px_-15px_rgba(0,0,0,0.7)] ring-1 ring-white/10 transition-transform hover:scale-[1.01] duration-500 ease-out"
                    style="width:540px; height:960px;"
                    id="zone-canvas"
                >
                    @if ($templatePngUrl)
                        <img
                            src="{{ $templatePngUrl }}"
                            class="absolute inset-0 w-full h-full object-cover pointer-events-none rounded-xl"
                            alt="Template Preview"
                        >
                    @else
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 rounded-xl bg-slate-800/80 backdrop-blur-sm">
                            <div class="p-6 bg-slate-900/50 rounded-2xl border border-slate-700/50 flex flex-col items-center text-center shadow-2xl">
                                <span class="text-5xl mb-4 drop-shadow-lg">🖼️</span>
                                <h3 class="text-white font-bold mb-1">Template Belum Tersedia</h3>
                                <p class="text-xs max-w-[200px]">Upload template PNG terlebih dahulu di halaman edit</p>
                            </div>
                        </div>
                    @endif

                    {{-- Band foto hero --}}
                    <div
                        class="absolute left-0 top-0 w-full pointer-events-none transition-all duration-300"
                        style="background: rgba(251,191,36,0.15); border-bottom: 2px dashed rgba(245, 158, 11, 0.6);"
                        :style="{ height: (heroPercent / 100 * 100) + '%' }"
                    >
                        <span class="absolute bottom-2 left-2 text-[9px] font-bold text-amber-900 bg-amber-400 rounded-md px-2 py-1 shadow-lg backdrop-blur-sm">
                            📷 HERO SECTION
                        </span>
                    </div>

                    @foreach ($activeZones as $key => $pos)
                        @php $c = $zoneColors[$key]; @endphp
                        <div
                            class="zone-box absolute cursor-move select-none rounded-lg shadow-lg backdrop-blur-[2px] transition-colors duration-200 hover:brightness-110"
                            data-zone="{{ $key }}"
                            style="
                                left:  {{ $pos['x'] / 1080 * 100 }}%;
                                top:   {{ $pos['y'] / 1920 * 100 }}%;
                                width: {{ $pos['w'] / 1080 * 100 }}%;
                                height:{{ $pos['h'] / 1920 * 100 }}%;
                                background: {{ str_replace('0.25', '0.15', $c['bg']) }};
                                border: 2px solid {{ $c['border'] }};
                            "
                        >
                            <span class="absolute top-0 left-0 text-[10px] px-2 py-1 font-bold text-white rounded-br-lg shadow-sm tracking-wide"
                                style="background:{{ $c['border'] }}">
                                {{ preg_replace('/^[🟦🟥]\s*/', '', $c['label']) }}
                            </span>

                            @if ($key === 'zona_jadwal')
                            <div class="absolute" style="left:6px; top:32px; right:6px;"
                                :style="{ display: 'grid', gridTemplateColumns: 'repeat(' + kolom + ', 1fr)', gap: gap * 0.5 + 'px', alignContent: 'start', alignItems: 'start' }">
                                @foreach ($previewPoli as $p)
                                @php
                                    $regularJadwal = $p['jadwal'];
                                @endphp
                                <div style="position:relative; width:100%;" class="transform transition-all duration-300">
                                    {{-- Header Nama Poli (70% overlap) --}}
                                    <div :style="{
                                        background: headerBg2 ? ('linear-gradient(135deg,' + headerBg1 + ',' + headerBg2 + ')') : headerBg1,
                                        borderRadius: (headerRadius * 0.5) + 'px',
                                        padding: (4 * 0.5) + 'px ' + (8 * 0.5) + 'px',
                                        width: headerWidthPct + '%',
                                        position: 'relative', zIndex: 2, lineHeight: 1.2,
                                        boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)'
                                    }">
                                        <span :style="{ fontFamily: headerFont, color: headerWarna, fontWeight: parseInt(headerFontWeight), fontStyle: headerFontStyle, fontSize: (headerFontSize * 0.5) + 'px', textTransform: 'uppercase', display: 'block' }">{{ $p['nama'] }}</span>
                                    </div>

                                    {{-- Body Card dengan Regular + EC sejajar --}}
                                    <div :style="{
                                        background: cardBg,
                                        border: (cardBorderWidth * 0.5) + 'px solid ' + cardBorderWarna,
                                        borderRadius: (cardRadius * 0.5) + 'px',
                                        marginTop: '-4px',
                                        padding: (cardPaddingTop * 0.5) + 'px ' + (6 * 0.5) + 'px ' + (4 * 0.5) + 'px',
                                        color: '#1f2937', position: 'relative', zIndex: 1,
                                        boxShadow: '0 2px 4px -1px rgba(0, 0, 0, 0.05)',
                                        display: 'flex', gap: '3px'
                                    }">
                                        {{-- Regular Dokter Column --}}
                                        <div style="flex:1;">
                                            @forelse ($regularJadwal as $row)
                                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 1px;">
                                                <span :style="{ fontSize: (sizeNamaDokter * 0.5) + 'px', fontFamily: fontNamaDokter, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', fontWeight: parseInt(weightNamaDokter) }">{{ $row['nama_dokter'] }}</span>
                                                <span :style="{ fontSize: (sizeJam * 0.5) + 'px', fontFamily: fontJam, color: '#1A1A1A', whiteSpace: 'nowrap', fontWeight: parseInt(weightJam), marginLeft: '3px' }">{{ $row['jam_mulai'] }}–{{ $row['jam_selesai'] ?? '' }}</span>
                                            </div>
                                            @empty
                                            <span :style="{ fontSize: (sizeNamaDokter * 0.5) + 'px', color: '#9ca3af', fontStyle: 'italic' }">—</span>
                                            @endforelse

                                            {{-- Sample catatan (first poli only) --}}
                                            @if ($loop->first)
                                            <div :style="{
                                                display: 'inline-block',
                                                background: catatanBg,
                                                color: catatanWarna,
                                                border: '0.5px solid ' + catatanBorder,
                                                borderRadius: (catatanRadius * 0.5) + 'px',
                                                padding: '1.5px 3px',
                                                fontSize: (catatanSize * 0.5) + 'px',
                                                fontFamily: catatanFont,
                                                fontWeight: parseInt(catatanWeight),
                                                marginTop: '2px',
                                                lineHeight: 1.3
                                            }">Khusus dengan jaminan Umum &amp; Asuransi</div>
                                            @endif
                                        </div>

                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @elseif ($key === 'zona_tanggal')
                            {{-- Preview Tanggal --}}
                            <div :style="{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 0, display: 'flex', alignItems: 'center', justifyContent: tanggalAlign === 'center' ? 'center' : (tanggalAlign === 'right' ? 'flex-end' : 'flex-start'), paddingLeft: tanggalAlign === 'left' ? '4px' : 0, paddingRight: tanggalAlign === 'right' ? '4px' : 0, pointerEvents: 'none' }">
                                <div :style="{ fontSize: (tanggalSize * 0.5) + 'px', color: tanggalWarna, fontFamily: tanggalFont, fontWeight: parseInt(tanggalWeight), lineHeight: 1.2 }">Senin, 25 Juni 2026</div>
                            </div>

                            @elseif ($key === 'zona_keterangan')
                            {{-- Preview Keterangan --}}
                            <div :style="{ position: 'absolute', inset: '2px', background: keteranganBg, borderRadius: '3px', padding: '4px', display: 'flex', alignItems: 'center', justifyContent: keteranganAlign === 'center' ? 'center' : (keteranganAlign === 'right' ? 'flex-end' : 'flex-start'), pointerEvents: 'none' }">
                                <div :style="{ fontSize: (keteranganSize * 0.5) + 'px', color: keteranganWarna, fontFamily: keteranganFont, fontWeight: parseInt(keteranganWeight), overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }">Promo Spesial</div>
                            </div>

                            @elseif ($key === 'zona_logo')
                            {{-- Preview Logo --}}
                            <div :style="{ position: 'absolute', inset: '0', display: 'flex', alignItems: 'center', justifyContent: 'center', pointerEvents: 'none', background: logoBg, opacity: logoOpacity / 100, transform: 'scale(' + (logoScale / 100) + ')', padding: (logoPadding * 0.5) + 'px' }">
                                <span style="font-size:12px; font-weight:700; color:#7c3aed; text-align:center;">LOGO</span>
                            </div>

                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/interactjs@1.10.27/dist/interact.min.js"></script>

    <script>
    // Listen untuk showPosterPreview event dari Livewire
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('showPosterPreview', (data) => {
            const previewContainer = document.getElementById('previewImageContainer');
            const zoneEditorContainer = document.getElementById('zoneEditorContainer');
            const previewImageDisplay = document.getElementById('previewImageDisplay');

            previewImageDisplay.src = data.url;
            previewContainer.classList.remove('hidden');
            previewContainer.style.display = 'flex';
            zoneEditorContainer.classList.add('hidden');
        });
    });
    </script>

    <script>
    const CANVAS_W = 1080;
    const CANVAS_H = 1920;
    const SCALE    = 540 / 1080; // 0.5

    function zoneEditor(config) {
        return {
            zones: config.initialZones,
            kolom: config.initialKolom ?? 2,
            gap:   config.initialGap   ?? 16,
            heroPercent: config.initialHeroPercent ?? 25,
            headerBg1: config.initialHeaderBg1 ?? '#7c3aed',
            headerBg2: config.initialHeaderBg2 ?? '',
            headerRadius: config.initialHeaderRadius ?? 8,
            headerWidthPct: config.initialHeaderWidthPct ?? 70,
            headerFontWeight: config.initialHeaderFontWeight ?? '700',
            headerFontStyle: config.initialHeaderFontStyle ?? 'normal',
            headerFont: config.initialHeaderFont ?? 'Montserrat',
            headerWarna: config.initialHeaderWarna ?? '#FFFFFF',
            cardBg: config.initialCardBg ?? '#ffffff',
            cardBorderWarna: config.initialCardBorderWarna ?? '#e5e7eb',
            cardBorderWidth: config.initialCardBorderWidth ?? 1,
            cardRadius: config.initialCardRadius ?? 8,
            headerFontSize: config.initialHeaderFontSize ?? 8,
            sizeNamaDokter: config.initialSizeNamaDokter ?? 9,
            sizeJam: config.initialSizeJam ?? 9,
            fontNamaDokter: config.initialFontNamaDokter ?? 'Poppins',
            fontJam: config.initialFontJam ?? 'Poppins',
            weightNamaDokter: config.initialWeightNamaDokter ?? '600',
            weightJam: config.initialWeightJam ?? '500',
            cardPaddingTop: config.initialCardPaddingTop ?? 8,
            logoScale: config.initialLogoScale ?? 100,
            logoOpacity: config.initialLogoOpacity ?? 100,
            logoPadding: config.initialLogoPadding ?? 0,
            logoBg: config.initialLogoBg ?? 'transparent',
            tanggalSize: config.initialTanggalSize ?? 40,
            tanggalWarna: config.initialTanggalWarna ?? '#1a1a2e',
            tanggalBg: config.initialTanggalBg ?? 'rgba(255,255,255,0.95)',
            tanggalFont: config.initialTanggalFont ?? 'Montserrat',
            tanggalWeight: config.initialTanggalWeight ?? '400',
            tanggalAlign: config.initialTanggalAlign ?? 'left',
            keteranganSize: config.initialKeteranganSize ?? 24,
            keteranganWarna: config.initialKeteranganWarna ?? '#F0C040',
            keteranganFont: config.initialKeteranganFont ?? 'Poppins',
            keteranganWeight: config.initialKeteranganWeight ?? '600',
            keteranganBg: config.initialKeteranganBg ?? '',
            keteranganAlign: config.initialKeteranganAlign ?? 'left',
            catatanBg: config.initialCatatanBg ?? '#fef9c3',
            catatanWarna: config.initialCatatanWarna ?? '#1a1a2e',
            catatanBorder: config.initialCatatanBorder ?? '#fde68a',
            catatanRadius: config.initialCatatanRadius ?? 4,
            catatanSize: config.initialCatatanSize ?? 8,
            catatanFont: config.initialCatatanFont ?? 'Poppins',
            catatanWeight: config.initialCatatanWeight ?? '400',
            state: config.state,

            init() {
                if (!this.state || Object.keys(this.state).length === 0) {
                    this.saveConfig();
                } else {
                    if (this.state.grid?.kolom !== undefined) this.kolom = this.state.grid.kolom;
                    if (this.state.grid?.gap   !== undefined) this.gap   = this.state.grid.gap;
                    if (this.state.tinggi_hero !== undefined) this.heroPercent = this.state.tinggi_hero;
                    if (this.state.grid?.header_bg_warna !== undefined)  this.headerBg1 = this.state.grid.header_bg_warna;
                    if (this.state.grid?.header_bg_warna2 !== undefined) this.headerBg2 = this.state.grid.header_bg_warna2;
                    if (this.state.grid?.header_radius !== undefined)     this.headerRadius = this.state.grid.header_radius;
                    if (this.state.grid?.header_width_pct !== undefined)   this.headerWidthPct = this.state.grid.header_width_pct;
                    if (this.state.grid?.header_font_weight !== undefined)  this.headerFontWeight = this.state.grid.header_font_weight;
                    if (this.state.grid?.header_font_style !== undefined)   this.headerFontStyle = this.state.grid.header_font_style;
                    if (this.state.grid?.font_nama_poli?.nama !== undefined) this.headerFont = this.state.grid.font_nama_poli.nama;
                    if (this.state.grid?.warna_nama_poli !== undefined)  this.headerWarna = this.state.grid.warna_nama_poli;
                    if (this.state.grid?.card_bg_warna !== undefined)     this.cardBg = this.state.grid.card_bg_warna;
                    if (this.state.grid?.card_border_warna !== undefined) this.cardBorderWarna = this.state.grid.card_border_warna;
                    if (this.state.grid?.card_border_width !== undefined) this.cardBorderWidth = this.state.grid.card_border_width;
                    if (this.state.grid?.card_radius !== undefined)      this.cardRadius = this.state.grid.card_radius;
                    if (this.state.grid?.size_nama_poli !== undefined)    this.headerFontSize = this.state.grid.size_nama_poli;
                    if (this.state.grid?.size_nama_dokter !== undefined)  this.sizeNamaDokter = this.state.grid.size_nama_dokter;
                    if (this.state.grid?.size_jam !== undefined)          this.sizeJam = this.state.grid.size_jam;
                    if (this.state.grid?.font_nama_dokter !== undefined)  this.fontNamaDokter = this.state.grid.font_nama_dokter;
                    if (this.state.grid?.font_jam !== undefined)          this.fontJam = this.state.grid.font_jam;
                    if (this.state.grid?.weight_nama_dokter !== undefined) this.weightNamaDokter = this.state.grid.weight_nama_dokter;
                    if (this.state.grid?.weight_jam !== undefined)         this.weightJam = this.state.grid.weight_jam;
                    if (this.state.grid?.card_padding_top !== undefined)  this.cardPaddingTop = this.state.grid.card_padding_top;
                    if (this.state.grid?.catatan_bg_warna !== undefined)     this.catatanBg = this.state.grid.catatan_bg_warna;
                    if (this.state.grid?.catatan_warna !== undefined)        this.catatanWarna = this.state.grid.catatan_warna;
                    if (this.state.grid?.catatan_border_warna !== undefined) this.catatanBorder = this.state.grid.catatan_border_warna;
                    if (this.state.grid?.catatan_radius !== undefined)       this.catatanRadius = this.state.grid.catatan_radius;
                    if (this.state.grid?.catatan_size !== undefined)         this.catatanSize = this.state.grid.catatan_size;
                    if (this.state.grid?.catatan_font !== undefined)         this.catatanFont = this.state.grid.catatan_font;
                    if (this.state.grid?.catatan_weight !== undefined)       this.catatanWeight = this.state.grid.catatan_weight;
                    if (this.state.zona_logo?.scale !== undefined)       this.logoScale = this.state.zona_logo.scale;
                    if (this.state.zona_logo?.opacity !== undefined)     this.logoOpacity = this.state.zona_logo.opacity;
                    if (this.state.zona_logo?.padding !== undefined)     this.logoPadding = this.state.zona_logo.padding;
                    if (this.state.zona_logo?.bg_warna !== undefined)    this.logoBg = this.state.zona_logo.bg_warna;
                    if (this.state.zona_tanggal?.size !== undefined)      this.tanggalSize = this.state.zona_tanggal.size;
                    if (this.state.zona_tanggal?.warna !== undefined)     this.tanggalWarna = this.state.zona_tanggal.warna;
                    if (this.state.zona_tanggal?.bg_warna !== undefined)  this.tanggalBg = this.state.zona_tanggal.bg_warna;
                    if (this.state.zona_tanggal?.font !== undefined)      this.tanggalFont = this.state.zona_tanggal.font;
                    if (this.state.zona_tanggal?.weight !== undefined)    this.tanggalWeight = this.state.zona_tanggal.weight;
                    if (this.state.zona_tanggal?.align !== undefined)     this.tanggalAlign = this.state.zona_tanggal.align;
                    if (this.state.zona_keterangan?.size !== undefined)   this.keteranganSize = this.state.zona_keterangan.size;
                    if (this.state.zona_keterangan?.warna !== undefined)  this.keteranganWarna = this.state.zona_keterangan.warna;
                    if (this.state.zona_keterangan?.font !== undefined)   this.keteranganFont = this.state.zona_keterangan.font;
                    if (this.state.zona_keterangan?.weight !== undefined) this.keteranganWeight = this.state.zona_keterangan.weight;
                    if (this.state.zona_keterangan?.bg_warna !== undefined) this.keteranganBg = this.state.zona_keterangan.bg_warna;
                    if (this.state.zona_keterangan?.align !== undefined)  this.keteranganAlign = this.state.zona_keterangan.align;
                }
                this.$nextTick(() => this.setupInteract());
            },

            setupInteract() {
                const canvas = document.getElementById('zone-canvas');
                if (!canvas) return;

                interact('.zone-box', { context: canvas })
                    .draggable({
                        listeners: {
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
                const current = (this.state && typeof this.state === 'object')
                    ? JSON.parse(JSON.stringify(this.state))
                    : {};
                this.state = {
                    ...current,
                    zona_logo:   { ...this.zones.zona_logo, scale: parseInt(this.logoScale) || 100, opacity: parseInt(this.logoOpacity) || 100, padding: parseInt(this.logoPadding) || 0, bg_warna: this.logoBg },
                    zona_tanggal: { ...this.zones.zona_tanggal, size: parseInt(this.tanggalSize) || 40, warna: this.tanggalWarna, bg_warna: this.tanggalBg, font: this.tanggalFont, weight: this.tanggalWeight, align: this.tanggalAlign },
                    zona_keterangan: { ...this.zones.zona_keterangan, size: parseInt(this.keteranganSize) || 24, warna: this.keteranganWarna, font: this.keteranganFont, weight: this.keteranganWeight, bg_warna: this.keteranganBg, align: this.keteranganAlign },
                    zona_jadwal: { ...this.zones.zona_jadwal },
                    tinggi_hero: parseInt(this.heroPercent) || 0,
                    grid: {
                        ...(current.grid ?? {}),
                        kolom: parseInt(this.kolom) || 1,
                        gap:   parseInt(this.gap)   || 0,
                        header_bg_warna:  this.headerBg1,
                        header_bg_warna2: this.headerBg2,
                        header_radius:    parseInt(this.headerRadius) || 0,
                        header_width_pct:   parseInt(this.headerWidthPct) || 70,
                        header_font_weight: this.headerFontWeight,
                        header_font_style:  this.headerFontStyle,
                        font_nama_poli:   { sumber: 'google', nama: this.headerFont },
                        warna_nama_poli:  this.headerWarna,
                        card_bg_warna:     this.cardBg,
                        card_border_warna: this.cardBorderWarna,
                        card_border_width: parseInt(this.cardBorderWidth) || 0,
                        card_radius:       parseInt(this.cardRadius) || 0,
                        size_nama_poli:    parseInt(this.headerFontSize) || 12,
                        size_nama_dokter:  parseInt(this.sizeNamaDokter) || 10,
                        size_jam:          parseInt(this.sizeJam) || 8,
                        font_nama_dokter:  this.fontNamaDokter,
                        font_jam:          this.fontJam,
                        weight_nama_dokter: this.weightNamaDokter,
                        weight_jam:        this.weightJam,
                        card_padding_top:  parseInt(this.cardPaddingTop) || 0,
                        catatan_bg_warna:     this.catatanBg,
                        catatan_warna:        this.catatanWarna,
                        catatan_border_warna: this.catatanBorder,
                        catatan_radius:       parseInt(this.catatanRadius) || 0,
                        catatan_size:         parseInt(this.catatanSize) || 8,
                        catatan_font:         this.catatanFont,
                        catatan_weight:       this.catatanWeight,
                    },
                };
            },
        };
    }
    </script>

    </main>

    @livewire(\Filament\Notifications\Livewire\Notifications::class)

    @livewireScripts
    @filamentScripts(null, true)

    </body>
</html>
</div>