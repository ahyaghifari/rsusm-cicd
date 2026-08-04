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
        <span class="ml-1 text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">Perubahan Jadwal</span>
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
            'zona_konten'  => ['bg' => 'rgba(239,68,68,0.25)',  'border' => '#EF4444', 'label' => 'Konten'],
        ];

        $fallbackZones = [
            'zona_tanggal' => ['x' => 60, 'y' => 220, 'w' => 400, 'h' => 60],
            'zona_konten'  => ['x' => 60, 'y' => 320, 'w' => 960, 'h' => 1400],
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

        $initialMaxDokterPerHalaman = (int)  ($g['max_dokter_per_halaman'] ?? 4);
        $initialGapV               = (int)   ($g['gap_v']               ?? 24);
        $initialLabelExecutive     =         ($g['label_executive']     ?? 'Klinik Executive');
        $initialLabelReguler       =         ($g['label_reguler']       ?? 'Poliklinik Reguler');
        $initialSectionTitleWarna  =         ($g['section_title_warna'] ?? '#c0392b');
        $initialSectionTitleSize   = (int)   ($g['section_title_size']  ?? 32);
        $initialSectionTitleFont   =         ($g['section_title_font']['nama'] ?? 'Montserrat');
        $initialCardBg             =         ($g['card_bg_warna']       ?? '#ffffff');
        $initialCardBorderWarna    =         ($g['card_border_warna']   ?? '#c0392b');
        $initialCardBorderWidth    = (int)   ($g['card_border_width']   ?? 2);
        $initialCardRadius         = (int)   ($g['card_radius']         ?? 12);
        $initialFontNamaKlinik     =         ($g['font_nama_klinik']['nama'] ?? 'Montserrat');
        $initialFontIsi            =         ($g['font_isi']['nama']        ?? 'Poppins');
        $initialWarnaNamaKlinik    =         ($g['warna_nama_klinik']   ?? '#1a1a2e');
        $initialWarnaNamaDokter    =         ($g['warna_nama_dokter']   ?? '#1a1a2e');
        $initialSizeNamaKlinik     = (int)   ($g['size_nama_klinik']    ?? 26);
        $initialSizeNamaDokter     = (int)   ($g['size_nama_dokter']    ?? 24);
        $initialSizeJam            = (int)   ($g['size_jam']            ?? 20);
        $initialPillAwalBg         =         ($g['pill_awal_bg_warna']  ?? '#f3f4f6');
        $initialPillAwalC          =         ($g['pill_awal_warna']     ?? '#1a1a2e');
        $initialPillBaruBg         =         ($g['pill_baru_bg_warna']  ?? '#dcfce7');
        $initialPillBaruC          =         ($g['pill_baru_warna']     ?? '#166534');
        $initialBadgeLiburBg       =         ($g['badge_libur_bg_warna']?? '#dc2626');
        $initialBadgeLiburC        =         ($g['badge_libur_warna']   ?? '#ffffff');

        $initialTanggalFont     =         ($savedConfig['zona_tanggal']['font']      ?? 'Montserrat');
        $initialTanggalSize     = (int)   ($savedConfig['zona_tanggal']['size']      ?? 30);
        $initialTanggalWarna    =         ($savedConfig['zona_tanggal']['warna']     ?? '#ffffff');
        $initialTanggalBg       =         ($savedConfig['zona_tanggal']['bg_warna']  ?? '#c0392b');
        $initialTanggalAlign    =         ($savedConfig['zona_tanggal']['align']     ?? 'center');
        $initialTanggalPaddingX = (int)   ($savedConfig['zona_tanggal']['padding_x'] ?? 28);
        $initialTanggalPaddingY = (int)   ($savedConfig['zona_tanggal']['padding_y'] ?? 10);
        $initialTanggalRadius   = (int)   ($savedConfig['zona_tanggal']['radius']    ?? 999);

        $templatePngUrl = $this->templatePngUrl;
    @endphp

    <div
        x-data="zoneEditorPerubahanJadwal({
            initialZones: @js($activeZones),
            initialMaxDokterPerHalaman: {{ $initialMaxDokterPerHalaman }},
            initialGapV: {{ $initialGapV }},
            initialLabelExecutive: @js($initialLabelExecutive),
            initialLabelReguler: @js($initialLabelReguler),
            initialSectionTitleWarna: @js($initialSectionTitleWarna),
            initialSectionTitleSize: {{ $initialSectionTitleSize }},
            initialSectionTitleFont: @js($initialSectionTitleFont),
            initialCardBg: @js($initialCardBg),
            initialCardBorderWarna: @js($initialCardBorderWarna),
            initialCardBorderWidth: {{ $initialCardBorderWidth }},
            initialCardRadius: {{ $initialCardRadius }},
            initialFontNamaKlinik: @js($initialFontNamaKlinik),
            initialFontIsi: @js($initialFontIsi),
            initialWarnaNamaKlinik: @js($initialWarnaNamaKlinik),
            initialWarnaNamaDokter: @js($initialWarnaNamaDokter),
            initialSizeNamaKlinik: {{ $initialSizeNamaKlinik }},
            initialSizeNamaDokter: {{ $initialSizeNamaDokter }},
            initialSizeJam: {{ $initialSizeJam }},
            initialPillAwalBg: @js($initialPillAwalBg),
            initialPillAwalC: @js($initialPillAwalC),
            initialPillBaruBg: @js($initialPillBaruBg),
            initialPillBaruC: @js($initialPillBaruC),
            initialBadgeLiburBg: @js($initialBadgeLiburBg),
            initialBadgeLiburC: @js($initialBadgeLiburC),
            initialTanggalFont: @js($initialTanggalFont),
            initialTanggalSize: {{ $initialTanggalSize }},
            initialTanggalWarna: @js($initialTanggalWarna),
            initialTanggalBg: @js($initialTanggalBg),
            initialTanggalAlign: @js($initialTanggalAlign),
            initialTanggalPaddingX: {{ $initialTanggalPaddingX }},
            initialTanggalPaddingY: {{ $initialTanggalPaddingY }},
            initialTanggalRadius: {{ $initialTanggalRadius }},
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
                <div class="p-2 bg-amber-50 rounded-lg text-amber-600 shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900 tracking-tight">Konfigurasi Perubahan Jadwal</h2>
                    <p class="text-xs text-gray-500">Poster khusus jadwal yang berubah/libur mendadak</p>
                </div>
            </div>

            {{-- Label & Batas Halaman Section --}}
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-xs font-bold text-gray-600 uppercase tracking-wider">
                    <span class="text-lg">🏷️</span> Label &amp; Batas Halaman
                </label>
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm space-y-3">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Maks. Dokter / Halaman</span>
                        <input type="number" x-model.number="maxDokterPerHalaman" @input="saveConfig()" min="1" max="30"
                            class="w-16 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                    </div>
                    <p class="text-[10px] text-gray-400 -mt-2">Kalau Executive atau Reguler punya lebih banyak dokter berubah dari batas ini, poster otomatis lanjut ke halaman/foto berikutnya.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-600">Label Executive</span>
                        <input type="text" x-model="labelExecutive" @input="saveConfig()" class="w-44 text-xs border border-gray-200 rounded-lg py-1.5 px-2 shadow-sm">
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-600">Label Reguler</span>
                        <input type="text" x-model="labelReguler" @input="saveConfig()" class="w-44 text-xs border border-gray-200 rounded-lg py-1.5 px-2 shadow-sm">
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-600">Warna Judul Section</span>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="sectionTitleWarna" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                            <input type="text" x-model="sectionTitleWarna" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm">
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-600">Font Judul Section</span>
                        <select x-model="sectionTitleFont" @change="saveConfig()" class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 appearance-none bg-no-repeat"
                            style="background-image:url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E');background-position:right .5rem center;background-size:1.25rem;">
                            @foreach ($this::$availableFonts as $f)
                            <option value="{{ $f }}">{{ $f }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Ukuran Judul Section</span>
                        <input type="range" x-model.number="sectionTitleSize" @input="saveConfig()" min="16" max="60" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-purple-500">
                        <input type="number" x-model.number="sectionTitleSize" @input="saveConfig()" min="16" max="60" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                    </div>
                    <p class="text-[10px] text-gray-400 -mt-2">Alignment judul section selalu di tengah (tidak bisa diubah).</p>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Jarak Antar Kartu</span>
                        <input type="range" x-model.number="gapV" @input="saveConfig()" min="0" max="80" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500">
                        <input type="number" x-model.number="gapV" @input="saveConfig()" min="0" max="80" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                    </div>
                </div>
            </div>

            {{-- Style Kartu --}}
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-xs font-bold text-gray-600 uppercase tracking-wider">
                    <span class="text-lg">🎨</span> Style Kartu
                </label>
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-600">Background</span>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="cardBg" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                            <input type="text" x-model="cardBg" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm">
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-600">Border Color</span>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="cardBorderWarna" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                            <input type="text" x-model="cardBorderWarna" @input="saveConfig()" class="w-24 text-xs text-center border border-gray-200 rounded-lg py-1.5 font-mono shadow-sm">
                        </div>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Border Width</span>
                        <input type="range" x-model.number="cardBorderWidth" @input="saveConfig()" min="0" max="8" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500">
                        <input type="number" x-model.number="cardBorderWidth" @input="saveConfig()" min="0" max="8" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Radius</span>
                        <input type="range" x-model.number="cardRadius" @input="saveConfig()" min="0" max="32" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500">
                        <input type="number" x-model.number="cardRadius" @input="saveConfig()" min="0" max="32" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-600">Font Klinik</span>
                        <select x-model="fontNamaKlinik" @change="saveConfig()" class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 appearance-none bg-no-repeat"
                            style="background-image:url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E');background-position:right .5rem center;background-size:1.25rem;">
                            @foreach ($this::$availableFonts as $f)
                            <option value="{{ $f }}">{{ $f }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-600">Font Isi</span>
                        <select x-model="fontIsi" @change="saveConfig()" class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 appearance-none bg-no-repeat"
                            style="background-image:url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E');background-position:right .5rem center;background-size:1.25rem;">
                            @foreach ($this::$availableFonts as $f)
                            <option value="{{ $f }}">{{ $f }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Ukuran Nama Klinik</span>
                        <input type="range" x-model.number="sizeNamaKlinik" @input="saveConfig()" min="12" max="60" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500">
                        <input type="number" x-model.number="sizeNamaKlinik" @input="saveConfig()" min="12" max="60" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Ukuran Nama Dokter</span>
                        <input type="range" x-model.number="sizeNamaDokter" @input="saveConfig()" min="12" max="60" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500">
                        <input type="number" x-model.number="sizeNamaDokter" @input="saveConfig()" min="12" max="60" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Ukuran Jam</span>
                        <input type="range" x-model.number="sizeJam" @input="saveConfig()" min="12" max="50" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-blue-500">
                        <input type="number" x-model.number="sizeJam" @input="saveConfig()" min="12" max="50" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                    </div>
                </div>
            </div>

            {{-- Warna Pill Jam --}}
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-xs font-bold text-gray-600 uppercase tracking-wider">
                    <span class="text-lg">🕒</span> Pill Jam &amp; Badge Libur
                </label>
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-600">Jadwal Awal — Background</span>
                        <input type="color" x-model="pillAwalBg" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-600">Jadwal Awal — Teks</span>
                        <input type="color" x-model="pillAwalC" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-600">Jam Baru — Background</span>
                        <input type="color" x-model="pillBaruBg" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-600">Jam Baru — Teks</span>
                        <input type="color" x-model="pillBaruC" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-600">Badge Libur — Background</span>
                        <input type="color" x-model="badgeLiburBg" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-600">Badge Libur — Teks</span>
                        <input type="color" x-model="badgeLiburC" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                    </div>
                </div>
            </div>

            {{-- Zona Tanggal --}}
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-xs font-bold text-gray-600 uppercase tracking-wider">
                    <span class="text-lg">📅</span> Badge Tanggal
                </label>
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-600">Font</span>
                        <select x-model="tanggalFont" @change="saveConfig()" class="w-36 text-xs font-medium border border-gray-200 rounded-lg py-1.5 px-2 pr-7 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 appearance-none bg-no-repeat"
                            style="background-image:url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 14l-7 7m0 0l-7-7m7 7V3%22/%3E%3C/svg%3E');background-position:right .5rem center;background-size:1.25rem;">
                            @foreach ($this::$availableFonts as $f)
                            <option value="{{ $f }}">{{ $f }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Ukuran</span>
                        <input type="range" x-model.number="tanggalSize" @input="saveConfig()" min="16" max="60" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-green-500">
                        <input type="number" x-model.number="tanggalSize" @input="saveConfig()" min="16" max="60" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-600">Warna Teks</span>
                        <input type="color" x-model="tanggalWarna" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-600">Warna Background</span>
                        <input type="color" x-model="tanggalBg" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded-lg border border-gray-200 p-0.5 shadow-sm">
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Padding Horizontal</span>
                        <input type="range" x-model.number="tanggalPaddingX" @input="saveConfig()" min="0" max="80" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-green-500">
                        <input type="number" x-model.number="tanggalPaddingX" @input="saveConfig()" min="0" max="80" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Padding Vertikal</span>
                        <input type="range" x-model.number="tanggalPaddingY" @input="saveConfig()" min="0" max="60" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-green-500">
                        <input type="number" x-model.number="tanggalPaddingY" @input="saveConfig()" min="0" max="60" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">Corner Radius</span>
                        <input type="range" x-model.number="tanggalRadius" @input="saveConfig()" min="0" max="999" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none accent-green-500">
                        <input type="number" x-model.number="tanggalRadius" @input="saveConfig()" min="0" max="999" class="w-14 text-center text-xs font-bold border border-gray-200 rounded-lg py-1 shadow-sm">
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
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-amber-600/20 blur-[100px] pointer-events-none"></div>

            <div id="zoneEditorContainer" class="flex flex-col items-center gap-6 relative z-10 my-auto w-full">

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

                            @if ($key === 'zona_tanggal')
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <span :style="{ fontFamily: tanggalFont, fontWeight: 700, fontSize: (tanggalSize * 0.5) + 'px', color: tanggalWarna, background: tanggalBg, borderRadius: (tanggalRadius * 0.5) + 'px', padding: (tanggalPaddingY * 0.5) + 'px ' + (tanggalPaddingX * 0.5) + 'px' }">Sabtu, 07 Juni 2026</span>
                            </div>
                            @elseif ($key === 'zona_konten')
                            <div class="absolute pointer-events-none" style="left:4px; top:4px; right:4px; bottom:4px; overflow:hidden;">
                                <div :style="{ fontFamily: sectionTitleFont, fontWeight: 800, color: sectionTitleWarna, fontSize: (sectionTitleSize * 0.5) + 'px', textAlign: 'center', marginBottom: '6px' }" x-text="labelExecutive"></div>
                                <div :style="{ background: cardBg, border: (cardBorderWidth*0.5)+'px solid '+cardBorderWarna, borderRadius: (cardRadius*0.5)+'px', padding: '8px 10px' }">
                                    <div :style="{ fontFamily: fontNamaKlinik, fontWeight: 700, color: warnaNamaKlinik, fontSize: (sizeNamaKlinik*0.5)+'px', textAlign: 'center' }">Klinik Mata</div>
                                    <div :style="{ fontFamily: fontIsi, fontWeight: 600, color: warnaNamaDokter, fontSize: (sizeNamaDokter*0.5)+'px', textAlign: 'center', margin: '4px 0' }">dr. Contoh, Sp.M</div>
                                    <div style="display:flex; align-items:center; justify-content:center; gap:8px;">
                                        <span :style="{ background: pillAwalBg, color: pillAwalC, fontSize: (sizeJam*0.5)+'px', fontWeight: 700, borderRadius: '4px', padding: '2px 8px' }">13.30-14.30</span>
                                        <span style="color:#16a34a;">&#10148;</span>
                                        <span :style="{ background: pillBaruBg, color: pillBaruC, fontSize: (sizeJam*0.5)+'px', fontWeight: 700, borderRadius: '4px', padding: '2px 8px' }">12.00-13.00</span>
                                    </div>
                                </div>
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

    function zoneEditorPerubahanJadwal(config) {
        return {
            zones: config.initialZones,
            maxDokterPerHalaman: config.initialMaxDokterPerHalaman ?? 4,
            gapV:               config.initialGapV               ?? 24,
            labelExecutive:      config.initialLabelExecutive      ?? 'Klinik Executive',
            labelReguler:        config.initialLabelReguler        ?? 'Poliklinik Reguler',
            sectionTitleWarna:   config.initialSectionTitleWarna   ?? '#c0392b',
            sectionTitleSize:    config.initialSectionTitleSize    ?? 32,
            sectionTitleFont:    config.initialSectionTitleFont    ?? 'Montserrat',
            cardBg:              config.initialCardBg              ?? '#ffffff',
            cardBorderWarna:     config.initialCardBorderWarna     ?? '#c0392b',
            cardBorderWidth:     config.initialCardBorderWidth     ?? 2,
            cardRadius:          config.initialCardRadius          ?? 12,
            fontNamaKlinik:      config.initialFontNamaKlinik      ?? 'Montserrat',
            fontIsi:             config.initialFontIsi             ?? 'Poppins',
            warnaNamaKlinik:     config.initialWarnaNamaKlinik     ?? '#1a1a2e',
            warnaNamaDokter:     config.initialWarnaNamaDokter     ?? '#1a1a2e',
            sizeNamaKlinik:      config.initialSizeNamaKlinik      ?? 26,
            sizeNamaDokter:      config.initialSizeNamaDokter      ?? 24,
            sizeJam:             config.initialSizeJam             ?? 20,
            pillAwalBg:          config.initialPillAwalBg          ?? '#f3f4f6',
            pillAwalC:           config.initialPillAwalC           ?? '#1a1a2e',
            pillBaruBg:          config.initialPillBaruBg          ?? '#dcfce7',
            pillBaruC:           config.initialPillBaruC           ?? '#166534',
            badgeLiburBg:        config.initialBadgeLiburBg        ?? '#dc2626',
            badgeLiburC:         config.initialBadgeLiburC         ?? '#ffffff',
            tanggalFont:         config.initialTanggalFont         ?? 'Montserrat',
            tanggalSize:         config.initialTanggalSize         ?? 30,
            tanggalWarna:        config.initialTanggalWarna        ?? '#ffffff',
            tanggalBg:           config.initialTanggalBg           ?? '#c0392b',
            tanggalAlign:        config.initialTanggalAlign        ?? 'center',
            tanggalPaddingX:     config.initialTanggalPaddingX     ?? 28,
            tanggalPaddingY:     config.initialTanggalPaddingY     ?? 10,
            tanggalRadius:       config.initialTanggalRadius       ?? 999,
            state: config.state,

            init() {
                if (!this.state || Object.keys(this.state).length === 0) {
                    this.saveConfig();
                } else {
                    for (const key of ['zona_tanggal', 'zona_konten']) {
                        if (this.state[key]?.x !== undefined) {
                            this.zones[key] = { ...this.zones[key], x: this.state[key].x, y: this.state[key].y, w: this.state[key].w, h: this.state[key].h };
                        }
                    }
                    const g = this.state.grid ?? {};
                    if (g.max_dokter_per_halaman !== undefined) this.maxDokterPerHalaman = g.max_dokter_per_halaman;
                    if (g.gap_v               !== undefined) this.gapV             = g.gap_v;
                    if (g.label_executive     !== undefined) this.labelExecutive   = g.label_executive;
                    if (g.label_reguler       !== undefined) this.labelReguler     = g.label_reguler;
                    if (g.section_title_warna !== undefined) this.sectionTitleWarna = g.section_title_warna;
                    if (g.section_title_size  !== undefined) this.sectionTitleSize  = g.section_title_size;
                    if (g.section_title_font?.nama !== undefined) this.sectionTitleFont = g.section_title_font.nama;
                    if (g.card_bg_warna       !== undefined) this.cardBg           = g.card_bg_warna;
                    if (g.card_border_warna   !== undefined) this.cardBorderWarna  = g.card_border_warna;
                    if (g.card_border_width   !== undefined) this.cardBorderWidth  = g.card_border_width;
                    if (g.card_radius         !== undefined) this.cardRadius       = g.card_radius;
                    if (g.font_nama_klinik?.nama !== undefined) this.fontNamaKlinik = g.font_nama_klinik.nama;
                    if (g.font_isi?.nama      !== undefined) this.fontIsi          = g.font_isi.nama;
                    if (g.warna_nama_klinik   !== undefined) this.warnaNamaKlinik  = g.warna_nama_klinik;
                    if (g.warna_nama_dokter   !== undefined) this.warnaNamaDokter  = g.warna_nama_dokter;
                    if (g.size_nama_klinik    !== undefined) this.sizeNamaKlinik   = g.size_nama_klinik;
                    if (g.size_nama_dokter    !== undefined) this.sizeNamaDokter   = g.size_nama_dokter;
                    if (g.size_jam            !== undefined) this.sizeJam          = g.size_jam;
                    if (g.pill_awal_bg_warna  !== undefined) this.pillAwalBg       = g.pill_awal_bg_warna;
                    if (g.pill_awal_warna     !== undefined) this.pillAwalC        = g.pill_awal_warna;
                    if (g.pill_baru_bg_warna  !== undefined) this.pillBaruBg       = g.pill_baru_bg_warna;
                    if (g.pill_baru_warna     !== undefined) this.pillBaruC        = g.pill_baru_warna;
                    if (g.badge_libur_bg_warna!== undefined) this.badgeLiburBg     = g.badge_libur_bg_warna;
                    if (g.badge_libur_warna   !== undefined) this.badgeLiburC      = g.badge_libur_warna;
                    if (this.state.zona_tanggal?.font    !== undefined) this.tanggalFont  = this.state.zona_tanggal.font;
                    if (this.state.zona_tanggal?.size    !== undefined) this.tanggalSize  = this.state.zona_tanggal.size;
                    if (this.state.zona_tanggal?.warna   !== undefined) this.tanggalWarna = this.state.zona_tanggal.warna;
                    if (this.state.zona_tanggal?.bg_warna!== undefined) this.tanggalBg    = this.state.zona_tanggal.bg_warna;
                    if (this.state.zona_tanggal?.align   !== undefined) this.tanggalAlign = this.state.zona_tanggal.align;
                    if (this.state.zona_tanggal?.padding_x !== undefined) this.tanggalPaddingX = this.state.zona_tanggal.padding_x;
                    if (this.state.zona_tanggal?.padding_y !== undefined) this.tanggalPaddingY = this.state.zona_tanggal.padding_y;
                    if (this.state.zona_tanggal?.radius    !== undefined) this.tanggalRadius   = this.state.zona_tanggal.radius;
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
                        font: this.tanggalFont,
                        size: parseInt(this.tanggalSize) || 30,
                        warna: this.tanggalWarna,
                        bg_warna: this.tanggalBg,
                        align: this.tanggalAlign,
                        padding_x: parseInt(this.tanggalPaddingX) || 0,
                        padding_y: parseInt(this.tanggalPaddingY) || 0,
                        radius: parseInt(this.tanggalRadius) || 0,
                    },
                    zona_konten: { ...this.zones.zona_konten },
                    font_tanggal: { sumber: 'google', nama: this.tanggalFont },
                    grid: {
                        max_dokter_per_halaman: parseInt(this.maxDokterPerHalaman) || 4,
                        gap_v:                parseInt(this.gapV) || 0,
                        label_executive:      this.labelExecutive,
                        label_reguler:        this.labelReguler,
                        section_title_warna:  this.sectionTitleWarna,
                        section_title_size:   parseInt(this.sectionTitleSize) || 32,
                        section_title_font:   { sumber: 'google', nama: this.sectionTitleFont },
                        card_bg_warna:        this.cardBg,
                        card_border_warna:    this.cardBorderWarna,
                        card_border_width:    parseInt(this.cardBorderWidth) || 0,
                        card_radius:          parseInt(this.cardRadius) || 0,
                        font_nama_klinik:     { sumber: 'google', nama: this.fontNamaKlinik },
                        font_isi:             { sumber: 'google', nama: this.fontIsi },
                        warna_nama_klinik:    this.warnaNamaKlinik,
                        warna_nama_dokter:    this.warnaNamaDokter,
                        size_nama_klinik:     parseInt(this.sizeNamaKlinik) || 26,
                        size_nama_dokter:     parseInt(this.sizeNamaDokter) || 24,
                        size_jam:             parseInt(this.sizeJam) || 20,
                        pill_awal_bg_warna:   this.pillAwalBg,
                        pill_awal_warna:      this.pillAwalC,
                        pill_baru_bg_warna:   this.pillBaruBg,
                        pill_baru_warna:      this.pillBaruC,
                        badge_libur_bg_warna: this.badgeLiburBg,
                        badge_libur_warna:    this.badgeLiburC,
                    },
                };
            },
        };
    }
    </script>

    </body>
</html>
</div>
