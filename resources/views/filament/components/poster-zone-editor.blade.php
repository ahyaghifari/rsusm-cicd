{{-- resources/views/filament/components/poster-zone-editor.blade.php --}}

@php
    $zoneColors = [
        'zona_logo'        => ['bg' => 'rgba(59,130,246,0.25)',  'border' => '#3B82F6', 'label' => '🟦 Logo'],
        'zona_tanggal'     => ['bg' => 'rgba(234,179,8,0.25)',   'border' => '#EAB308', 'label' => '🟨 Tanggal'],
        'zona_keterangan'  => ['bg' => 'rgba(34,197,94,0.25)',   'border' => '#22C55E', 'label' => '🟩 Keterangan'],
        'zona_jadwal'      => ['bg' => 'rgba(239,68,68,0.25)',   'border' => '#EF4444', 'label' => '🟥 Jadwal'],
    ];

    $fallbackZones = [
        'zona_logo'        => ['x' => 60,  'y' => 60,   'w' => 300, 'h' => 120],
        'zona_tanggal'     => ['x' => 80,  'y' => 940,  'w' => 900, 'h' => 60],
        'zona_keterangan'  => ['x' => 80,  'y' => 1000, 'w' => 900, 'h' => 50],
        'zona_jadwal'      => ['x' => 40,  'y' => 1080, 'w' => 1000,'h' => 780],
    ];

    // Gunakan $getState() untuk mengambil value field saat ini dari state Livewire/Filament
    $savedConfig = $getState() ?? [];
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

    $record = $getRecord();
    $templatePngUrl = $record?->template_png
        ? \Illuminate\Support\Facades\Storage::url($record->template_png)
        : null;
    $shapePngUrl = $record?->shape_poli
        ? \Illuminate\Support\Facades\Storage::url($record->shape_poli)
        : null;

    $initialHeroPercent      = (int)   ($savedConfig['tinggi_hero']                  ?? 25);
    $initialShapeScale       = (int)   ($savedConfig['grid']['shape_scale']           ?? 100);
    $initialSizeTanggal      = (int)   ($savedConfig['zona_tanggal']['size']          ?? 40);
    $initialSizeKet          = (int)   ($savedConfig['zona_keterangan']['size']       ?? 24);
    $initialKetBgColor       =         ($savedConfig['zona_keterangan']['bg_warna']   ?? '');
    $initialKetPadding       = (int)   ($savedConfig['zona_keterangan']['padding']    ?? 8);
    $initialSizePoli         = (int)   ($savedConfig['grid']['size_nama_poli']        ?? 15);
    $initialSizeDokter       = (int)   ($savedConfig['grid']['size_nama_dokter']      ?? 13);
    $initialSizeJam          = (int)   ($savedConfig['grid']['size_jam']              ?? 12);
    $initialCardBg           =         ($savedConfig['grid']['card_bg_warna']         ?? '#ffffff');
    $initialCardRadius       = (int)   ($savedConfig['grid']['card_radius']           ?? 8);
    $initialCardBorderWarna  =         ($savedConfig['grid']['card_border_warna']     ?? '#e5e7eb');
    $initialCardBorderWidth  = (int)   ($savedConfig['grid']['card_border_width']     ?? 1);
    $initialCardMinHeight    = (int)   ($savedConfig['grid']['card_min_height']       ?? 0);
    $initialDokterValign     =         ($savedConfig['grid']['dokter_valign']         ?? 'top');
    $initialDokterRowGap     = (int)   ($savedConfig['grid']['dokter_row_gap']        ?? 2);
@endphp

<div
    x-data="zoneEditor({
        initialZones: @js($activeZones),
        initialHeroPercent: {{ $initialHeroPercent }},
        initialShapeScale:  {{ $initialShapeScale }},
        initialSizeTanggal: {{ $initialSizeTanggal }},
        initialSizeKet:     {{ $initialSizeKet }},
        initialKetBgColor:  @js($initialKetBgColor),
        initialKetPadding:  {{ $initialKetPadding }},
        initialSizePoli:         {{ $initialSizePoli }},
        initialSizeDokter:       {{ $initialSizeDokter }},
        initialSizeJam:          {{ $initialSizeJam }},
        initialCardBg:           @js($initialCardBg),
        initialCardRadius:       {{ $initialCardRadius }},
        initialCardBorderWarna:  @js($initialCardBorderWarna),
        initialCardBorderWidth:  {{ $initialCardBorderWidth }},
        initialCardMinHeight:    {{ $initialCardMinHeight }},
        initialDokterValign:     @js($initialDokterValign),
        initialDokterRowGap:     {{ $initialDokterRowGap }},
        state: $wire.$entangle('{{ $getStatePath() }}')
    })"
    x-init="init()"
    class="space-y-4"
>
    {{-- ── Tinggi Foto Hero ──────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
        <span class="text-sm font-medium text-gray-700 shrink-0">📷 Tinggi Foto Hero</span>
        <input
            type="range"
            x-model="heroPercent"
            @input="saveConfig()"
            min="0" max="80" step="5"
            class="flex-1 accent-blue-500"
        >
        <div class="flex items-center gap-1 shrink-0">
            <input
                type="number"
                x-model="heroPercent"
                @input="saveConfig()"
                min="0" max="80" step="5"
                class="w-14 text-center text-sm border border-gray-300 rounded px-1 py-0.5"
            >
            <span class="text-sm text-gray-500">%</span>
        </div>
        <span class="text-xs text-gray-400 shrink-0" x-text="Math.round(heroPercent / 100 * 1920) + 'px'"></span>
    </div>

    {{-- ── Shape & Font ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">

        {{-- Shape scale --}}
        <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-gray-700 w-36 shrink-0">🔷 Skala Shape Poli</span>
            <input type="range" x-model="shapeScale" @input="saveConfig()" min="30" max="200" step="5" class="flex-1 accent-purple-500">
            <input type="number" x-model="shapeScale" @input="saveConfig()" min="30" max="200" step="5" class="w-14 text-center text-sm border border-gray-300 rounded px-1 py-0.5">
            <span class="text-sm text-gray-500 w-4">%</span>
        </div>

        <hr class="border-gray-200">

        {{-- Font sizes --}}
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Ukuran Font (px)</p>

        @foreach([
            ['label'=>'Tanggal',     'model'=>'sizeTanggal', 'min'=>16, 'max'=>80],
            ['label'=>'Keterangan',  'model'=>'sizeKet',     'min'=>12, 'max'=>60],
            ['label'=>'Nama Poli',   'model'=>'sizePoli',    'min'=>8,  'max'=>40],
            ['label'=>'Nama Dokter', 'model'=>'sizeDokter',  'min'=>8,  'max'=>32],
            ['label'=>'Jam',         'model'=>'sizeJam',     'min'=>6,  'max'=>28],
        ] as $f)
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 w-36 shrink-0">{{ $f['label'] }}</span>
            <input type="range" x-model="{{ $f['model'] }}" @input="saveConfig()" min="{{ $f['min'] }}" max="{{ $f['max'] }}" step="1" class="flex-1 accent-blue-400">
            <input type="number" x-model="{{ $f['model'] }}" @input="saveConfig()" min="{{ $f['min'] }}" max="{{ $f['max'] }}" class="w-14 text-center text-sm border border-gray-300 rounded px-1 py-0.5">
            <span class="text-sm text-gray-400 w-4">px</span>
        </div>
        @endforeach

        <hr class="border-gray-200">

        {{-- Keterangan background & padding --}}
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Latar Keterangan</p>

        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 w-36 shrink-0">Warna Latar</span>
            <input type="color" x-model="ketBgColor" @input="saveConfig()"
                   class="h-8 w-10 cursor-pointer rounded border border-gray-300 p-0.5 shrink-0">
            <input type="text" x-model="ketBgColor" @input="saveConfig()"
                   placeholder="kosong = transparan"
                   class="flex-1 text-sm border border-gray-300 rounded px-2 py-0.5 font-mono">
            <button type="button" @click="ketBgColor = ''; saveConfig()"
                    class="text-xs text-gray-400 hover:text-red-500 shrink-0" title="Hapus warna">✕</button>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 w-36 shrink-0">Padding</span>
            <input type="range" x-model="ketPadding" @input="saveConfig()" min="0" max="60" step="2" class="flex-1 accent-green-400">
            <input type="number" x-model="ketPadding" @input="saveConfig()" min="0" max="60" class="w-14 text-center text-sm border border-gray-300 rounded px-1 py-0.5">
            <span class="text-sm text-gray-400 w-4">px</span>
        </div>

        {{-- Live preview latar keterangan --}}
        <div class="flex items-center gap-2 text-sm">
            <span class="text-gray-500 shrink-0">Preview:</span>
            <span
                :style="{
                    background: ketBgColor || 'transparent',
                    padding: ketPadding + 'px',
                    borderRadius: '6px',
                    color: '#F0C040',
                    fontWeight: 600,
                    fontSize: '13px',
                    border: ketBgColor ? 'none' : '1px dashed #ccc',
                }"
            >Teks Keterangan Contoh</span>
        </div>

        <hr class="border-gray-200">

        {{-- Style kartu poli --}}
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Style Kartu Poli</p>

        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 w-36 shrink-0">Background</span>
            <input type="color" x-model="cardBg" @input="saveConfig()"
                   class="h-8 w-10 cursor-pointer rounded border border-gray-300 p-0.5 shrink-0">
            <input type="text" x-model="cardBg" @input="saveConfig()"
                   class="flex-1 text-sm border border-gray-300 rounded px-2 py-0.5 font-mono">
        </div>

        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 w-36 shrink-0">Border Radius</span>
            <input type="range" x-model="cardRadius" @input="saveConfig()" min="0" max="32" step="1" class="flex-1 accent-indigo-400">
            <input type="number" x-model="cardRadius" @input="saveConfig()" min="0" max="32" class="w-14 text-center text-sm border border-gray-300 rounded px-1 py-0.5">
            <span class="text-sm text-gray-400 w-4">px</span>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 w-36 shrink-0">Warna Outline</span>
            <input type="color" x-model="cardBorderWarna" @input="saveConfig()"
                   class="h-8 w-10 cursor-pointer rounded border border-gray-300 p-0.5 shrink-0">
            <input type="text" x-model="cardBorderWarna" @input="saveConfig()"
                   class="flex-1 text-sm border border-gray-300 rounded px-2 py-0.5 font-mono">
        </div>

        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 w-36 shrink-0">Ketebalan Outline</span>
            <input type="range" x-model="cardBorderWidth" @input="saveConfig()" min="0" max="10" step="1" class="flex-1 accent-indigo-400">
            <input type="number" x-model="cardBorderWidth" @input="saveConfig()" min="0" max="10" class="w-14 text-center text-sm border border-gray-300 rounded px-1 py-0.5">
            <span class="text-sm text-gray-400 w-4">px</span>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 w-36 shrink-0">Tinggi Minimum Kartu</span>
            <input type="range" x-model="cardMinHeight" @input="saveConfig()" min="0" max="600" step="10" class="flex-1 accent-indigo-400">
            <input type="number" x-model="cardMinHeight" @input="saveConfig()" min="0" max="600" class="w-14 text-center text-sm border border-gray-300 rounded px-1 py-0.5">
            <span class="text-sm text-gray-400 w-4">px</span>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 w-36 shrink-0">Posisi Dokter Vertikal</span>
            <select x-model="dokterValign" @change="saveConfig()" class="flex-1 text-sm border border-gray-300 rounded px-2 py-1">
                <option value="top">Atas (rapat)</option>
                <option value="center">Tengah (kalau kartu lebih tinggi dari isi)</option>
            </select>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 w-36 shrink-0">Jarak Antar Dokter</span>
            <input type="range" x-model="dokterRowGap" @input="saveConfig()" min="0" max="20" step="1" class="flex-1 accent-indigo-400">
            <input type="number" x-model="dokterRowGap" @input="saveConfig()" min="0" max="20" class="w-14 text-center text-sm border border-gray-300 rounded px-1 py-0.5">
            <span class="text-sm text-gray-400 w-4">px</span>
        </div>

        <hr class="border-gray-200">

        {{-- Mini preview kartu poli: shape scale + card styling sekaligus --}}
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">🃏 Preview Kartu</p>
        <div class="flex items-start gap-3">
            <span class="text-sm text-gray-500 w-36 shrink-0 pt-1">Hasil kombinasi:</span>
            <div
                style="width:160px; overflow:hidden;"
                :style="{
                    borderRadius: cardRadius + 'px',
                    border: cardBorderWidth + 'px solid ' + cardBorderWarna,
                }"
            >
                {{-- Shape header dengan skala --}}
                <div style="position:relative; overflow:hidden; background:#2d2d3a;">
                    @if ($shapePngUrl)
                    <img src="{{ $shapePngUrl }}" style="width:100%; display:block;"
                         :style="{ zoom: shapeScale / 100 }">
                    @else
                    <div style="height:40px; background:linear-gradient(135deg,#6366f1,#8b5cf6);"></div>
                    @endif
                    <span style="position:absolute;top:50%;left:0;right:0;transform:translateY(-50%);text-align:center;color:white;font-size:10px;font-weight:700;text-shadow:0 1px 3px rgba(0,0,0,0.6);">NAMA POLI</span>
                </div>
                {{-- Body dokter dengan card styling --}}
                <div style="padding:6px 8px;" :style="{ background: cardBg }">
                    <div style="font-size:9px;color:#333;margin-bottom:3px;">dr. Nama Dokter</div>
                    <div style="display:flex;justify-content:space-between;font-size:8px;color:#666;">
                        <span>dr. Dokter Lain</span><span>08:00–14:00</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Toolbar / Legend ──────────────────────────────────────────────── --}}
    <div class="flex items-center gap-4 flex-wrap text-sm">
        @foreach ($zoneColors as $key => $c)
            <span class="flex items-center gap-1">
                {{ $c['label'] }}
            </span>
        @endforeach
        <span class="ml-auto text-gray-400 italic">Drag & resize zona di atas preview</span>
    </div>

    {{-- ── Preview Canvas ──────────────────────────────────────────────────  --}}
    <div
        class="relative mx-auto overflow-hidden rounded-lg border border-gray-300 bg-gray-100"
        style="width:540px; height:960px;"
        id="zone-canvas"
    >
        @if ($templatePngUrl)
            <img
                src="{{ $templatePngUrl }}"
                class="absolute inset-0 w-full h-full object-cover pointer-events-none"
                alt="Template Preview"
            >
        @else
            <div class="absolute inset-0 flex items-center justify-center text-gray-400 text-sm">
                Upload template PNG terlebih dahulu
            </div>
        @endif

        {{-- Band foto hero --}}
        <div
            class="absolute left-0 top-0 w-full pointer-events-none"
            style="background: rgba(251,191,36,0.3); border-bottom: 2px dashed #F59E0B;"
            :style="{ height: (heroPercent / 100 * 100) + '%' }"
        >
            <span class="absolute bottom-1 left-1 text-[8px] font-bold text-amber-700 bg-amber-100 rounded px-1">
                📷 Hero
            </span>
        </div>

        @foreach ($activeZones as $key => $pos)
            @php $c = $zoneColors[$key]; @endphp
            <div
                class="zone-box absolute cursor-move select-none rounded"
                data-zone="{{ $key }}"
                style="
                    left:  {{ $pos['x'] / 1080 * 100 }}%;
                    top:   {{ $pos['y'] / 1920 * 100 }}%;
                    width: {{ $pos['w'] / 1080 * 100 }}%;
                    height:{{ $pos['h'] / 1920 * 100 }}%;
                    background: {{ $c['bg'] }};
                    border: 2px solid {{ $c['border'] }};
                "
            >
                <span class="absolute top-0 left-0 text-[9px] px-1 py-0.5 font-bold text-white rounded-br"
                      style="background:{{ $c['border'] }}">
                    {{ $c['label'] }}
                </span>
            </div>
        @endforeach
    </div>

    {{-- ── Koordinat (debug / konfirmasi) ─────────────────────────────────  --}}
    <div class="grid grid-cols-2 gap-2 text-xs text-gray-600">
        <template x-for="(val, key) in zones" :key="key">
            <div class="bg-gray-50 rounded p-2 font-mono">
                <span class="font-semibold" x-text="key"></span>:
                x=<span x-text="val.x"></span>
                y=<span x-text="val.y"></span>
                w=<span x-text="val.w"></span>
                h=<span x-text="val.h"></span>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/interactjs@1.10.27/dist/interact.min.js"></script>

<script>
const CANVAS_W = 1080;
const CANVAS_H = 1920;
const SCALE    = 540 / 1080; // 0.5

function zoneEditor(config) {
    return {
        zones:       config.initialZones,
        heroPercent: config.initialHeroPercent ?? 25,
        shapeScale:  config.initialShapeScale  ?? 100,
        sizeTanggal: config.initialSizeTanggal ?? 40,
        sizeKet:     config.initialSizeKet     ?? 24,
        ketBgColor:  config.initialKetBgColor  ?? '',
        ketPadding:  config.initialKetPadding  ?? 8,
        sizePoli:        config.initialSizePoli        ?? 15,
        sizeDokter:      config.initialSizeDokter      ?? 13,
        sizeJam:         config.initialSizeJam         ?? 12,
        cardBg:          config.initialCardBg          ?? '#ffffff',
        cardRadius:      config.initialCardRadius      ?? 8,
        cardBorderWarna: config.initialCardBorderWarna ?? '#e5e7eb',
        cardBorderWidth: config.initialCardBorderWidth ?? 1,
        cardMinHeight:   config.initialCardMinHeight   ?? 0,
        dokterValign:    config.initialDokterValign    ?? 'top',
        dokterRowGap:    config.initialDokterRowGap    ?? 2,
        state: config.state,

        init() {
            if (!this.state || Object.keys(this.state).length === 0) {
                this.saveConfig();
            } else {
                if (this.state.tinggi_hero !== undefined)           this.heroPercent = this.state.tinggi_hero;
                if (this.state.grid?.shape_scale !== undefined)     this.shapeScale  = this.state.grid.shape_scale;
                if (this.state.zona_tanggal?.size !== undefined)        this.sizeTanggal = this.state.zona_tanggal.size;
                if (this.state.zona_keterangan?.size !== undefined)    this.sizeKet     = this.state.zona_keterangan.size;
                if (this.state.zona_keterangan?.bg_warna !== undefined) this.ketBgColor  = this.state.zona_keterangan.bg_warna;
                if (this.state.zona_keterangan?.padding !== undefined)  this.ketPadding  = this.state.zona_keterangan.padding;
                if (this.state.grid?.size_nama_poli !== undefined)    this.sizePoli        = this.state.grid.size_nama_poli;
                if (this.state.grid?.size_nama_dokter !== undefined)  this.sizeDokter      = this.state.grid.size_nama_dokter;
                if (this.state.grid?.size_jam !== undefined)          this.sizeJam         = this.state.grid.size_jam;
                if (this.state.grid?.card_bg_warna !== undefined)     this.cardBg          = this.state.grid.card_bg_warna;
                if (this.state.grid?.card_radius !== undefined)       this.cardRadius      = this.state.grid.card_radius;
                if (this.state.grid?.card_border_warna !== undefined) this.cardBorderWarna = this.state.grid.card_border_warna;
                if (this.state.grid?.card_border_width !== undefined) this.cardBorderWidth = this.state.grid.card_border_width;
                if (this.state.grid?.card_min_height !== undefined)   this.cardMinHeight   = this.state.grid.card_min_height;
                if (this.state.grid?.dokter_valign !== undefined)     this.dokterValign    = this.state.grid.dokter_valign;
                if (this.state.grid?.dokter_row_gap !== undefined)    this.dokterRowGap    = this.state.grid.dokter_row_gap;
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

                            zone.w = Math.max(50, Math.min(CANVAS_W - zone.x, zone.w));
                            zone.h = Math.max(20, Math.min(CANVAS_H - zone.y, zone.h));

                            this.applyPosition(box, zone);
                            this.saveConfig();
                        },
                    },
                    modifiers: [interact.modifiers.restrictSize({ min: { width: 50 * SCALE, height: 20 * SCALE } })],
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
                tinggi_hero:     parseInt(this.heroPercent) || 0,
                zona_logo:       { ...this.zones.zona_logo },
                zona_tanggal:    { ...(current.zona_tanggal ?? {}),    ...this.zones.zona_tanggal,    size: parseInt(this.sizeTanggal) || 40 },
                zona_keterangan: { ...(current.zona_keterangan ?? {}), ...this.zones.zona_keterangan, size: parseInt(this.sizeKet) || 24, bg_warna: this.ketBgColor, padding: parseInt(this.ketPadding) || 0 },
                zona_jadwal:     { ...this.zones.zona_jadwal },
                grid: {
                    ...(current.grid ?? {}),
                    shape_scale:      parseInt(this.shapeScale)      || 100,
                    size_nama_poli:   parseInt(this.sizePoli)       || 15,
                    size_nama_dokter: parseInt(this.sizeDokter)     || 13,
                    size_jam:         parseInt(this.sizeJam)        || 12,
                    card_bg_warna:    this.cardBg,
                    card_radius:      parseInt(this.cardRadius)     || 0,
                    card_border_warna: this.cardBorderWarna,
                    card_border_width: parseInt(this.cardBorderWidth) || 1,
                    card_min_height:   parseInt(this.cardMinHeight)   || 0,
                    dokter_valign:     this.dokterValign,
                    dokter_row_gap:    parseInt(this.dokterRowGap)    || 0,
                },
            };
        },
    };
}
</script>
@endpush