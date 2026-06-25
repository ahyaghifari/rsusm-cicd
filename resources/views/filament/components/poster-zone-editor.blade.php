{{-- resources/views/filament/components/poster-zone-editor.blade.php --}}

@php
    $zoneColors = [
        'zona_logo'   => ['bg' => 'rgba(59,130,246,0.25)', 'border' => '#3B82F6', 'label' => '🟦 Logo'],
        'zona_jadwal' => ['bg' => 'rgba(239,68,68,0.25)',  'border' => '#EF4444', 'label' => '🟥 Jadwal'],
    ];

    $fallbackZones = [
        'zona_logo'   => ['x' => 60, 'y' => 60,   'w' => 300,  'h' => 120],
        'zona_jadwal' => ['x' => 40, 'y' => 1080, 'w' => 1000, 'h' => 780],
    ];

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

    // Preview pakai jadwal praktek/harian hari ini (4 poli pertama RS ini). Font size auto dari
    // lebar 1 card (zona_jadwal dibagi kolom) — sama dengan formula yang dipakai render asli.
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

    // Tidak ada jadwal sama sekali untuk RS ini — isi dummy biar preview tidak kosong.
    if (empty($previewPoli)) {
        $previewPoli = [
            ['nama' => 'Poliklinik Umum', 'jadwal' => [['nama_dokter' => 'dr. Contoh A', 'jam_mulai' => '08:00', 'jam_selesai' => '14:00', 'is_executive' => false]]],
            ['nama' => 'Poliklinik Anak', 'jadwal' => [['nama_dokter' => 'dr. Contoh B', 'jam_mulai' => '09:00', 'jam_selesai' => '15:00', 'is_executive' => false]]],
            ['nama' => 'Poliklinik Gigi', 'jadwal' => [['nama_dokter' => 'dr. Contoh C', 'jam_mulai' => '10:00', 'jam_selesai' => '16:00', 'is_executive' => false]]],
            ['nama' => 'Poliklinik Saraf', 'jadwal' => [['nama_dokter' => 'dr. Contoh D', 'jam_mulai' => '08:00', 'jam_selesai' => '12:00', 'is_executive' => false]]],
        ];
    }

    // Tidak ada satupun jadwal executive clinic hari ini — tambahkan dummy di poli pertama
    // supaya badge EC tetap kelihatan di preview.
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
        state: $wire.$entangle('{{ $getStatePath() }}')
    })"
    x-init="init()"
    class="space-y-4"
>
    {{-- Load semua pilihan font header supaya preview di editor ini ikut berubah saat dipilih --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Poppins:wght@700&family=Roboto:wght@700&family=Open+Sans:wght@700&family=Lato:wght@700&family=Nunito:wght@700&family=Raleway:wght@700&family=Inter:wght@700&family=Oswald:wght@700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

    {{-- ── Tinggi Foto Hero ──────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
        <span class="text-sm font-medium text-gray-700 shrink-0">📷 Tinggi Foto Hero</span>
        <input type="range" x-model="heroPercent" @input="saveConfig()" min="0" max="80" step="5" class="flex-1 accent-blue-500">
        <div class="flex items-center gap-1 shrink-0">
            <input type="number" x-model="heroPercent" @input="saveConfig()" min="0" max="80" step="5"
                   class="w-14 text-center text-sm border border-gray-300 rounded px-1 py-0.5">
            <span class="text-sm text-gray-500">%</span>
        </div>
        <span class="text-xs text-gray-400 shrink-0" x-text="Math.round(heroPercent / 100 * 1920) + 'px'"></span>
    </div>

    {{-- ── Grid: jumlah kolom & gap antar card ─────────────────────────────── --}}
    <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-600">Jumlah Kolom</span>
            <input type="number" x-model.number="kolom" @input="saveConfig()" min="1" max="4"
                   class="w-14 text-center text-sm border border-gray-300 rounded px-1 py-0.5">
        </div>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-600">Gap Antar Card</span>
            <input type="number" x-model.number="gap" @input="saveConfig()" min="0" max="60"
                   class="w-14 text-center text-sm border border-gray-300 rounded px-1 py-0.5">
            <span class="text-sm text-gray-400">px</span>
        </div>
    </div>

    {{-- ── Zone Editor Card Poli ───────────────────────────────────────────── --}}
    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 space-y-2">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">🎨 Style Card Poli</p>

        <p class="text-xs text-gray-600 font-medium">Header (Nama Poli)</p>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 w-40 shrink-0">Warna Background</span>
            <input type="color" x-model="headerBg1" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded border border-gray-300 p-0.5 shrink-0">
            <input type="text" x-model="headerBg1" @input="saveConfig()" class="flex-1 text-sm border border-gray-300 rounded px-2 py-0.5 font-mono">
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 w-40 shrink-0">Warna Gradasi (opsional)</span>
            <input type="color" x-model="headerBg2" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded border border-gray-300 p-0.5 shrink-0">
            <input type="text" x-model="headerBg2" @input="saveConfig()" placeholder="kosong = solid"
                   class="flex-1 text-sm border border-gray-300 rounded px-2 py-0.5 font-mono">
            <button type="button" @click="headerBg2 = ''; saveConfig()"
                    class="text-xs text-gray-400 hover:text-red-500 shrink-0" title="Hapus gradasi">✕</button>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 w-40 shrink-0">Border Radius</span>
            <input type="range" x-model="headerRadius" @input="saveConfig()" min="0" max="32" class="flex-1 accent-purple-400">
            <input type="number" x-model="headerRadius" @input="saveConfig()" min="0" max="32" class="w-14 text-center text-sm border border-gray-300 rounded px-1 py-0.5">
            <span class="text-sm text-gray-400 w-4">px</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 w-40 shrink-0">Font Family</span>
            <select x-model="headerFont" @change="saveConfig()" class="flex-1 text-sm border border-gray-300 rounded px-2 py-1">
                @foreach (['Montserrat','Poppins','Roboto','Open Sans','Lato','Nunito','Raleway','Inter','Oswald','Playfair Display'] as $f)
                <option value="{{ $f }}">{{ $f }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 w-40 shrink-0">Warna Teks</span>
            <input type="color" x-model="headerWarna" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded border border-gray-300 p-0.5 shrink-0">
            <input type="text" x-model="headerWarna" @input="saveConfig()" class="flex-1 text-sm border border-gray-300 rounded px-2 py-0.5 font-mono">
        </div>

        <hr class="border-gray-200">

        <p class="text-xs text-gray-600 font-medium">Box Dokter</p>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 w-40 shrink-0">Warna Background</span>
            <input type="color" x-model="cardBg" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded border border-gray-300 p-0.5 shrink-0">
            <input type="text" x-model="cardBg" @input="saveConfig()" class="flex-1 text-sm border border-gray-300 rounded px-2 py-0.5 font-mono">
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 w-40 shrink-0">Warna Border</span>
            <input type="color" x-model="cardBorderWarna" @input="saveConfig()" class="h-8 w-10 cursor-pointer rounded border border-gray-300 p-0.5 shrink-0">
            <input type="text" x-model="cardBorderWarna" @input="saveConfig()" class="flex-1 text-sm border border-gray-300 rounded px-2 py-0.5 font-mono">
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 w-40 shrink-0">Ketebalan Border</span>
            <input type="range" x-model="cardBorderWidth" @input="saveConfig()" min="0" max="10" class="flex-1 accent-indigo-400">
            <input type="number" x-model="cardBorderWidth" @input="saveConfig()" min="0" max="10" class="w-14 text-center text-sm border border-gray-300 rounded px-1 py-0.5">
            <span class="text-sm text-gray-400 w-4">px</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 w-40 shrink-0">Border Radius</span>
            <input type="range" x-model="cardRadius" @input="saveConfig()" min="0" max="32" class="flex-1 accent-indigo-400">
            <input type="number" x-model="cardRadius" @input="saveConfig()" min="0" max="32" class="w-14 text-center text-sm border border-gray-300 rounded px-1 py-0.5">
            <span class="text-sm text-gray-400 w-4">px</span>
        </div>

        {{-- Live preview --}}
        <div class="pt-1">
            <span class="text-sm text-gray-500 block mb-1">Preview:</span>
            <div style="width:200px;">
                <div :style="{
                    background: headerBg2 ? ('linear-gradient(135deg,' + headerBg1 + ',' + headerBg2 + ')') : headerBg1,
                    borderRadius: headerRadius + 'px',
                    padding: '5px 10px',
                    width: '50%',
                    position: 'relative', zIndex: 2, lineHeight: 1,
                }">
                    <span :style="{ fontFamily: headerFont, color: headerWarna, fontWeight: 700, fontSize: '12px', textTransform: 'uppercase', display: 'block' }">Nama Poliklinik</span>
                </div>
                <div :style="{ background: cardBg, border: cardBorderWidth + 'px solid ' + cardBorderWarna, borderRadius: cardRadius + 'px', marginTop: '-9px', padding: '13px 8px 6px', fontSize: '10px', color: '#333', position: 'relative', zIndex: 1, overflow: 'hidden' }">
                    dr. Contoh Dokter — 08:00–14:00
                </div>
            </div>
        </div>
    </div>

    {{-- ── Toolbar / Legend ──────────────────────────────────────────────── --}}
    <div class="flex items-center gap-4 flex-wrap text-sm">
        @foreach ($zoneColors as $key => $c)
            <span class="flex items-center gap-1">{{ $c['label'] }}</span>
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

                {{-- Preview 4 card poli pakai jadwal praktek hari ini, ikut jumlah kolom & style card --}}
                @if ($key === 'zona_jadwal')
                <div class="absolute" style="left:4px; top:4px; right:4px;"
                     :style="{ display: 'grid', gridTemplateColumns: 'repeat(' + kolom + ', 1fr)', gap: '4px', alignContent: 'start', alignItems: 'start' }">
                    @foreach ($previewPoli as $p)
                    <div style="position:relative;">
                        <div :style="{
                            background: headerBg2 ? ('linear-gradient(135deg,' + headerBg1 + ',' + headerBg2 + ')') : headerBg1,
                            borderRadius: headerRadius + 'px',
                            width: '50%', padding: '2px 4px',
                            position: 'relative', zIndex: 2, lineHeight: 1,
                        }">
                            <span :style="{ fontFamily: headerFont, color: headerWarna, fontWeight: 700, fontSize: '{{ $previewSizeNamaPoli }}px', textTransform: 'uppercase', display: 'block' }">{{ $p['nama'] }}</span>
                        </div>
                        <div :style="{ background: cardBg, border: cardBorderWidth + 'px solid ' + cardBorderWarna, borderRadius: cardRadius + 'px', marginTop: '-5px', padding: '7px 4px 2px', color: '#333', position: 'relative', zIndex: 1, overflow: 'hidden' }">
                            @forelse ($p['jadwal'] as $row)
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:{{ $previewSizeNamaDokter }}px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $row['nama_dokter'] }}</span>
                                <span style="display:flex; align-items:center; gap:2px; flex-shrink:0; margin-left:3px;">
                                    <span style="font-size:{{ $previewSizeJam }}px; color:#555; white-space:nowrap;">{{ $row['jam_mulai'] }}–{{ $row['jam_selesai'] ?? 'selesai' }}</span>
                                    @if ($row['is_executive'])
                                    <span style="background:#7c3aed; color:#fff; font-size:{{ max(4, $previewSizeJam - 2) }}px; font-weight:700; padding:0 2px; border-radius:2px; white-space:nowrap;">EC</span>
                                    @endif
                                </span>
                            </div>
                            @empty
                            <span style="font-size:{{ $previewSizeNamaDokter }}px; color:#aaa; font-style:italic;">Tidak ada jadwal</span>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
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
        zones: config.initialZones,
        kolom: config.initialKolom ?? 2,
        gap:   config.initialGap   ?? 16,
        heroPercent: config.initialHeroPercent ?? 25,
        headerBg1: config.initialHeaderBg1 ?? '#7c3aed',
        headerBg2: config.initialHeaderBg2 ?? '',
        headerRadius: config.initialHeaderRadius ?? 8,
        headerFont: config.initialHeaderFont ?? 'Montserrat',
        headerWarna: config.initialHeaderWarna ?? '#FFFFFF',
        cardBg: config.initialCardBg ?? '#ffffff',
        cardBorderWarna: config.initialCardBorderWarna ?? '#e5e7eb',
        cardBorderWidth: config.initialCardBorderWidth ?? 1,
        cardRadius: config.initialCardRadius ?? 8,
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
                if (this.state.grid?.header_radius !== undefined)    this.headerRadius = this.state.grid.header_radius;
                if (this.state.grid?.font_nama_poli?.nama !== undefined) this.headerFont = this.state.grid.font_nama_poli.nama;
                if (this.state.grid?.warna_nama_poli !== undefined)  this.headerWarna = this.state.grid.warna_nama_poli;
                if (this.state.grid?.card_bg_warna !== undefined)     this.cardBg = this.state.grid.card_bg_warna;
                if (this.state.grid?.card_border_warna !== undefined) this.cardBorderWarna = this.state.grid.card_border_warna;
                if (this.state.grid?.card_border_width !== undefined) this.cardBorderWidth = this.state.grid.card_border_width;
                if (this.state.grid?.card_radius !== undefined)      this.cardRadius = this.state.grid.card_radius;
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
                zona_logo:   { ...this.zones.zona_logo },
                zona_jadwal: { ...this.zones.zona_jadwal },
                tinggi_hero: parseInt(this.heroPercent) || 0,
                grid: {
                    ...(current.grid ?? {}),
                    kolom: parseInt(this.kolom) || 1,
                    gap:   parseInt(this.gap)   || 0,
                    header_bg_warna:  this.headerBg1,
                    header_bg_warna2: this.headerBg2,
                    header_radius:    parseInt(this.headerRadius) || 0,
                    font_nama_poli:   { sumber: 'google', nama: this.headerFont },
                    warna_nama_poli:  this.headerWarna,
                    card_bg_warna:     this.cardBg,
                    card_border_warna: this.cardBorderWarna,
                    card_border_width: parseInt(this.cardBorderWidth) || 0,
                    card_radius:       parseInt(this.cardRadius) || 0,
                },
            };
        },
    };
}
</script>
@endpush
