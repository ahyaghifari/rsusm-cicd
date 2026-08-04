# Dokumentasi Sistem Poster Jadwal

Dokumen ini menjelaskan arsitektur lengkap sistem poster jadwal: layout, config, zone editor, generate poster, quick config, dan cara ekstensi. Ditulis untuk dipahami oleh AI model maupun developer baru.

---

## Daftar Isi

1. [Gambaran Besar](#1-gambaran-besar)
2. [Alur Data End-to-End](#2-alur-data-end-to-end)
3. [Sistem Layout (PosterLayout)](#3-sistem-layout-posterlayout)
4. [Config JSON Template](#4-config-json-template)
5. [Generate Poster — GeneratePosterPage](#5-generate-poster--generateposterpage)
6. [Rendering Pipeline (Blade + Browsershot)](#6-rendering-pipeline-blade--browsershot)
7. [Quick Config Panel](#7-quick-config-panel)
8. [Zone Editor: Konsep Zona](#8-zone-editor-konsep-zona)
9. [Zone Editor: interact.js](#9-zone-editor-interactjs)
10. [Zone Editor: Alpine.js & Entangle](#10-zone-editor-alpinejs--entangle)
11. [Integrasi JadwalHarianPerubahan di Poster](#11-integrasi-jadwalharianperubahan-di-poster)
12. [Cara Menambah Konfigurasi Baru](#12-cara-menambah-konfigurasi-baru)
13. [Cara Menambah Layout Baru (Cabang Baru)](#13-cara-menambah-layout-baru-cabang-baru)
14. [Pagination List Polos](#14-pagination-list-polos)
15. [File Map Lengkap](#15-file-map-lengkap)

---

## 1. Gambaran Besar

```
PosterTemplate (DB)
  └── config (JSON)          ← semua pengaturan visual tersimpan di sini
        ├── zona_tanggal     ← posisi + style teks tanggal
        ├── zona_jadwal      ← posisi area tabel jadwal
        ├── zona_keterangan  ← posisi + style teks keterangan hero (Grid Shape only)
        ├── zona_logo        ← posisi logo RS (Grid Shape only)
        ├── tinggi_hero      ← % tinggi foto hero dari total 1920px (Grid Shape only)
        ├── font_tanggal     ← { sumber: 'google'|'upload', nama: string }
        ├── font_keterangan  ← { sumber: 'google'|'upload', nama: string }
        └── grid             ← konfigurasi tampilan kartu poli (warna, ukuran, jarak)

          ▼ zone editor mengubah config ini
          ▼ generate poster membaca config ini

GeneratePosterPage (Filament + Livewire)
  └── buildHtml()            ← render Blade template → HTML string
        └── Browsershot      ← headless Chrome → PNG 1080×1920
```

Setiap **cabang rumah sakit** punya **layout** berbeda. Layout menentukan:
- Tampilan poster (blade view mana yang dipakai)
- Zone editor mana yang dibuka
- Default config apa yang dipakai saat template baru dibuat
- Field apa yang tersedia di Quick Config panel

---

## 2. Alur Data End-to-End

```
[User buka Generate Poster]
  → pilih template + tanggal
  → loadPoliList() — query JadwalHarian per poli, tanggal, filter executive
  → tampilkan daftar poli (bisa toggle/reorder)
  → (opsional) buka "Sesuaikan Cepat" → override nilai grid config sementara
  → klik Preview / Download
  → generate() → buildHtml(template, tanggal, halaman)
      → apply quick config overrides ke $template->config (in-memory, no save)
      → query JadwalHarian + JadwalHarianPerubahan
      → render Blade → HTML string
      → Browsershot::html(html)->save(path) → PNG
      → response()->streamDownload(...)
```

```
[User buka Zone Editor]
  → PosterTemplateResource::getPages() → route ke ZoneEditorPage atau ZoneEditorPageListPolos
  → BaseZoneEditorPage::mount() → load $this->config dari DB
  → blade dikirim ke browser
  → Alpine.js init() → baca config dari Livewire entangle
  → interact.js → drag/resize zona di canvas
  → setiap perubahan → saveConfig() → entangle → $this->config update otomatis
  → klik Simpan → save() → update ke DB
```

---

## 3. Sistem Layout (PosterLayout)

### Interface

```
app/Filament/PosterLayouts/Contracts/PosterLayout.php
```

Setiap layout harus implement 5 method:

| Method | Keterangan |
|--------|-----------|
| `label()` | Nama yang ditampilkan di UI |
| `defaultConfig()` | Array config awal saat template baru dibuat |
| `zoneEditorPageClass()` | FQCN Filament Page untuk zone editor |
| `templateView()` | Nama Blade view untuk render poster |
| `quickConfigFields()` | Field grid yang muncul di panel "Sesuaikan Cepat" |

### Registry

```
app/Filament/PosterLayouts/LayoutRegistry.php
```

Mapping RS ID → Layout class:

```php
private const MAP = [
    1 => GridShapeLayout::class,  // RSU Syifa Medika Banjarbaru
    2 => ListPolosLayout::class,  // RSU Syifa Medika Barabai
];
```

**Cara pakai:**

```php
$layout = LayoutRegistry::for($template->rumah_sakit_id);
// atau via model:
$layout = $template->layout();  // PosterTemplate::layout() delegate ke LayoutRegistry
```

### Layout yang ada

| File | RS | Ciri khas |
|------|----|-----------|
| `GridShapeLayout.php` | Banjarbaru (ID 1) | Shape PNG per poli, foto hero, logo, keterangan, grid multi-kolom |
| `ListPolosLayout.php` | Barabai (ID 2) | Tabel baris polos tanpa shape PNG, pagination multi-halaman |

### Routing Zone Editor

Di `PosterTemplateResource::getPages()`:

```php
'zone-editor'            => ZoneEditorPage::route('/{record}/zone-editor'),
'zone-editor-list-polos' => ZoneEditorPageListPolos::route('/{record}/zone-editor-list-polos'),
```

Tombol "Edit Zone" di tabel menentukan route mana yang dipakai berdasarkan layout aktif template.

---

## 4. Config JSON Template

Tersimpan di kolom `config` (JSON cast) di tabel `poster_templates`.

### Struktur Grid Shape (Banjarbaru)

```json
{
  "tinggi_hero": 25,
  "zona_logo": { "x": 60, "y": 60, "w": 300, "h": 120, "scale": 100, "opacity": 100, "padding": 0, "bg_warna": "transparent" },
  "zona_tanggal": {
    "x": 80, "y": 940, "w": 900,
    "font": "Montserrat", "size": 40, "weight": "400",
    "warna": "#1a1a2e", "bg_warna": "rgba(255,255,255,0.95)", "align": "left"
  },
  "zona_keterangan": {
    "x": 80, "y": 1000, "w": 900,
    "font": "Poppins", "size": 24, "weight": "600",
    "warna": "#F0C040", "bg_warna": "", "align": "left", "padding": 8
  },
  "zona_jadwal": { "x": 40, "y": 1080, "w": 1000, "h": 780 },
  "font_tanggal":    { "sumber": "google", "nama": "Montserrat" },
  "font_keterangan": { "sumber": "google", "nama": "Poppins" },
  "grid": {
    "kolom": 2,
    "gap_h": 16,
    "gap_v": 16,
    "header_bg_warna": "#7c3aed",
    "header_bg_warna2": "",
    "header_radius": 8,
    "header_width_pct": 70,
    "header_offset_x": 0,
    "header_padding_left": 10,
    "header_font_weight": "700",
    "header_font_style": "normal",
    "card_bg_warna": "#ffffff",
    "card_radius": 8,
    "card_border_warna": "#e5e7eb",
    "card_border_width": 1,
    "card_min_height": 0,
    "card_padding_top": 8,
    "dokter_valign": "top",
    "dokter_row_gap": 2,
    "font_nama_poli": { "sumber": "google", "nama": "Montserrat" },
    "font_isi":       { "sumber": "google", "nama": "Poppins" },
    "warna_nama_poli": "#FFFFFF",
    "warna_nama_dokter": "#1A1A1A",
    "warna_jam": "#1A1A1A",
    "size_nama_poli": 8,
    "size_nama_dokter": 9,
    "size_jam": 9,
    "weight_nama_dokter": "600",
    "weight_jam": "500",
    "catatan_bg_warna": "#fef9c3",
    "catatan_warna": "#1a1a2e",
    "catatan_border_warna": "#fde68a",
    "catatan_radius": 4,
    "catatan_size": 8
  }
}
```

### Struktur List Polos (Barabai)

```json
{
  "zona_tanggal": {
    "x": 40, "y": 380, "w": 1000, "h": 70,
    "font": "Montserrat", "size": 36,
    "warna": "#1a1a2e", "align": "center",
    "outline_width": 0, "outline_warna": "#000000"
  },
  "zona_jadwal": { "x": 40, "y": 480, "w": 1000, "h": 1400 },
  "font_tanggal": { "sumber": "google", "nama": "Montserrat" },
  "grid": {
    "poli_per_halaman": 5,
    "gap_v": 16,
    "gap_h": 12,
    "col_nama_persen": 70,
    "gap_header_dokter": 0,
    "dokter_raise": 20,
    "padding_dokter_pertama": 0,
    "padding_dokter_top": 7,
    "padding_dokter_right": 8,
    "padding_dokter_bottom": 7,
    "padding_dokter_left": 14,
    "header_bg_warna": "#1e3a5f",
    "header_bg_warna2": "",
    "header_radius": 0,
    "header_border_warna": "#dee2e6",
    "header_border_width": 1,
    "card_bg_warna": "#f8f9fa",
    "card_radius": 8,
    "card_border_warna": "#dee2e6",
    "card_border_width": 1,
    "font_nama_poli": { "sumber": "google", "nama": "Montserrat" },
    "font_isi":       { "sumber": "google", "nama": "Poppins" },
    "warna_nama_poli": "#ffffff",
    "outline_poli_width": 0, "outline_poli_warna": "#000000",
    "warna_nama_dokter": "#1A1A1A",
    "outline_dokter_width": 0, "outline_dokter_warna": "#000000",
    "warna_jam": "#1A1A1A",
    "outline_jam_width": 0, "outline_jam_warna": "#000000",
    "size_nama_poli": 30,
    "size_nama_dokter": 26,
    "size_jam": 26,
    "weight_nama_dokter": "500",
    "weight_jam": "400"
  }
}
```

### Cara akses di Blade template

```php
$cfg  = $template->config ?: PosterTemplate::defaultConfig((int) $template->rumah_sakit_id);
$g    = $cfg['grid'] ?? [];

$gapV = (int) ($g['gap_v'] ?? 16);           // selalu beri fallback default
$warnaPoli = $g['warna_nama_poli'] ?? '#fff';
```

> **Penting:** Selalu beri nilai default (`?? 16`) karena template lama mungkin tidak punya key baru yang ditambahkan kemudian.

### Font Object

Dua jenis sumber font:

```php
// Google Fonts — dimuat via @import saat preview browser
['sumber' => 'google', 'nama' => 'Montserrat']

// Upload — di-embed sebagai data URI via @font-face
['sumber' => 'upload', 'path' => 'poster-fonts/xxx.ttf']
```

Font upload di-resolve oleh `GeneratePosterPage::resolveUploadFonts()` ke data URI sebelum dikirim ke blade view (`$uploadFonts`). Alias font upload bersifat fixed: `FontTanggal`, `FontKeterangan`, `FontIsi`, `FontNamaPoli`.

---

## 5. Generate Poster — GeneratePosterPage

```
app/Filament/Pages/GeneratePosterPage.php
```

Filament Page yang menggunakan `InteractsWithForms`. Semua state form tersimpan di `public array $data = []`.

### Public properties penting

| Property | Tipe | Keterangan |
|----------|------|-----------|
| `$data` | array | State semua Filament form field (template_id, tanggal, keterangan, foto_hero, executive_clinic_filter) |
| `$poli_list` | array | `[{id, nama, visible, order}]` — daftar poli yang bisa di-toggle/reorder |
| `$hospitalHasExecutiveClinic` | bool | Apakah RS aktif punya fitur executive clinic |
| `$activeHalaman` | int | Halaman aktif (hanya List Polos) |
| `$totalHalaman` | int | Total halaman pagination |
| `$quickConfig` | array | Override nilai grid config sementara (tidak disimpan ke DB) |
| `$quickConfigFields` | array | Definisi field quick config dari layout aktif |

### Flow load poli list

```
loadPoliList(get)
  → resolvedRumahSakitId()
  → query PoliKlinik.whereHas(jadwalHarian) per tanggal + filter executive
  → map ke [{id, nama, visible:true, order:urutan}]
  → recalcPagination(template)
```

### buildHtml()

Titik sentral rendering:

```php
private function buildHtml(PosterTemplate $template, Carbon $tanggal, int $halaman = 1): string
{
    // 1. Query jadwal harian + perubahan untuk semua poli yang visible
    $jadwalHarian = JadwalHarian::whereDate(...)
        ->with(['poliklinik', 'dokter', 'perubahan'])
        ->get()
        ->groupBy('poliklinik_id');

    // 2. Slice poli per halaman (List Polos only)
    $poliList = collect($this->poli_list)
        ->where('visible', true)
        ->sortBy('order')
        ->when($isListPolos, fn ($c) => $c->slice(...))
        ->map(fn ($item) => [
            'poli'   => $poli,
            'jadwal' => $rows->map(fn ($r) => [
                'nama_dokter'       => $r->nama_dokter ?: $r->dokter?->nama,
                'jam_mulai'         => $p?->jam_mulai ?? $r->jam_mulai,     // perubahan override
                'jam_selesai'       => $p?->jam_selesai ?? $r->jam_selesai,
                'libur'             => $statusRaw === 'LIBUR',
                'sesuai_perjanjian' => (bool) $r->sesuai_perjanjian,
                'catatan'           => $p?->catatan ?: $r->catatan,
            ]),
        ]);

    // 3. Apply quick config overrides (in-memory, no save)
    $overrides = array_filter($this->quickConfig, fn ($v) => $v !== null && $v !== '');
    if ($overrides) {
        $cfg['grid'] = array_merge($cfg['grid'] ?? [], array_map('intval', $overrides));
        $template->config = $cfg;  // mutate model in-memory saja
    }

    // 4. Render blade → string HTML
    return view($template->layout()->templateView(), [
        'template'        => $template,
        'tanggal'         => $tanggal,
        'fotoHeroDataUri' => $this->getFotoHeroDataUri(),
        'templateDataUri' => ...,   // template PNG → data URI
        'logoDataUri'     => ...,   // logo → data URI
        'shapePoliDataUri'=> ...,   // shape poli PNG → data URI
        'uploadFonts'     => $this->resolveUploadFonts($template),
        'keterangan'      => $this->getKeterangan(),
        'poliList'        => $poliList,
    ])->render();
}
```

### Nama dokter: prioritas

Di `buildHtml()`, nama dokter diambil dengan prioritas:

```php
'nama_dokter' => $r->nama_dokter ?: ($r->dokter?->nama ?? '-')
```

`nama_dokter` adalah kolom teks bebas di tabel `jadwal_harian` yang bisa diisi manual (override nama).
Jika kosong, fallback ke `dokter.nama` via relasi.

---

## 6. Rendering Pipeline (Blade + Browsershot)

### Ukuran poster

- **Poster asli:** 1080 × 1920 px
- **Browsershot:** `->windowSize(1080, 1920)->deviceScaleFactor(1)->fullPage()`

### Layer Grid Shape (`jadwal-template.blade.php`)

```
Layer 1 (z-index: 1) — Foto Hero
  → div#layer-hero, tinggi = tinggi_hero% × 1920px
  → img object-fit: cover

Layer 2 (z-index: 2) — Template PNG
  → div#layer-template, inset:0
  → img template PNG sebagai background dekoratif

Layer 3 (z-index: 3) — Konten Dinamis
  → Logo RS (absolute, posisi dari zona_logo)
  → Tanggal (absolute, posisi dari zona_tanggal)
    → format: "Kamis,\n2 Juli 2026" (explicit <br> bukan CSS wrapping)
  → Keterangan (absolute, posisi dari zona_keterangan)
    → nl2br(e($keterangan)) — XSS safe, support line break
    → background per baris via box-decoration-break: clone
  → Grid jadwal (absolute, posisi dari zona_jadwal)
    → CSS column-count: $kolom, column-gap: gap_h
    → tiap poli card: poli-header + poli-body (overlap via negative margin-top)
```

### Layer List Polos (`jadwal-template-list-polos.blade.php`)

```
Layer 1 (z-index: 1) — Template PNG background
Layer 2 (z-index: 3) — Konten Dinamis
  → Tanggal (absolute, posisi dari zona_tanggal)
    → format: "l, j F Y" (satu baris, tidak ada explicit break)
  → Tabel jadwal (absolute, posisi dari zona_jadwal)
    → HTML <table> dengan col-group (nama 73%, spacer gap_h, jam 27%)
    → Per poli: tr.poli-header (h-nama + h-jam) + tr.d-row per dokter
    → dokter_raise: baris dokter naik (position:relative, top:-raise) untuk
      efek overlap ke bawah header poli
    → gap_header_dokter: tr.header-gap opsional antara header dan dokter pertama
```

### Font loading di Browsershot

Google Fonts **tidak** dimuat saat Browsershot render (tidak ada network access):

```php
$isScreenshot = app()->runningInConsole() || !request()->hasHeader('X-Livewire');
// @if(!$isScreenshot) → <link Google Fonts> hanya untuk preview browser
```

Font upload di-embed sebagai data URI di `<style>@font-face</style>` — ini yang **dipakai Browsershot** untuk font custom.

Untuk Google Fonts agar bekerja di Browsershot: perlu font terinstall di sistem, atau gunakan fitur upload font.

### Jam: format dan null handling

```php
// Di poliList mapping:
'jam_mulai'  => $jamMulai?->format('H:i'),    // null jika tidak ada
'jam_selesai'=> $jamSelesai?->format('H:i'),  // null jika tidak ada / sesuai perjanjian

// Di blade Grid Shape:
{{ $row['jam_mulai'] }}–{{ $row['jam_selesai'] ?? 'Selesai' }}

// Di blade List Polos:
{{ $row['jam_mulai'] ?? '' }} s.d {{ !empty($row['jam_selesai']) ? $row['jam_selesai'] : 'Selesai' }}
```

---

## 7. Quick Config Panel

Panel "Sesuaikan Cepat" di halaman Generate Poster memungkinkan override sementara nilai `grid` config sebelum generate/preview — **tanpa menyimpan ke database**.

### Cara kerja

1. User pilih template → `afterStateUpdated` memanggil `loadQuickConfig($template)`
2. `loadQuickConfig()` baca `$template->layout()->quickConfigFields()`, ambil nilai aktif dari `$template->config['grid']`, isi ke `$this->quickConfig`
3. User buka panel collapsible, ubah nilai input
4. Klik Preview/Download → `buildHtml()` merge `$this->quickConfig` ke `$cfg['grid']` in-memory sebelum render

```php
// apply override di buildHtml():
$overrides = array_filter($this->quickConfig, fn ($v) => $v !== null && $v !== '');
if ($overrides) {
    $cfg['grid'] = array_merge($cfg['grid'] ?? [], array_map('intval', $overrides));
    $template->config = $cfg;  // no save()
}
```

### Definisi quickConfigFields() per layout

Setiap layout mendeklarasikan field quicknya sendiri. Format setiap entry:

```php
['key' => 'gap_v', 'label' => 'Jarak antar poli', 'quick_setting' => true]
```

Hanya entry dengan `quick_setting === true` yang ditampilkan di panel.

**Grid Shape (Banjarbaru):**

| Key | Label |
|-----|-------|
| `kolom` | Jumlah kolom |
| `gap_v` | Jarak vertikal antar kartu |
| `gap_h` | Jarak horizontal antar kartu |
| `size_nama_poli` | Ukuran font nama poli |
| `size_nama_dokter` | Ukuran font nama dokter |
| `size_jam` | Ukuran font jam |
| `card_padding_top` | Padding atas kartu |
| `dokter_row_gap` | Jarak antar baris dokter |

**List Polos (Barabai):**

| Key | Label |
|-----|-------|
| `poli_per_halaman` | Poli per halaman |
| `gap_v` | Jarak antar poli |
| `gap_header_dokter` | Jarak header ke baris dokter |
| `dokter_raise` | Overlap dokter ke header (px) |
| `size_nama_poli` | Ukuran font nama poli |
| `size_nama_dokter` | Ukuran font nama dokter |
| `size_jam` | Ukuran font jam |
| `col_nama_persen` | Lebar kolom nama (%) |
| `padding_dokter_top` | Padding atas baris dokter |
| `padding_dokter_bottom` | Padding bawah baris dokter |

### Menambah field quick config baru

1. Pastikan key ada di `defaultConfig()['grid']` layout
2. Tambah entry di `quickConfigFields()` dengan `'quick_setting' => true`
3. Selesai — panel akan otomatis menampilkan field baru

---

## 8. Zone Editor: Konsep Zona

Zona adalah **area persegi di atas canvas** yang bisa digeser dan di-resize. Posisi dan ukurannya disimpan dalam config sebagai `x, y, w, h` dalam satuan **pixel poster asli (1080×1920)**.

### Canvas vs Poster

| | Poster asli | Canvas editor |
|--|-------------|---------------|
| Ukuran | 1080 × 1920 px | 540 × 960 px |
| Skala | 1× | 0.5× (SCALE = 0.5) |

Karena itu semua ukuran di preview canvas dibagi 2:

```js
const CANVAS_W = 1080;
const CANVAS_H = 1920;
const SCALE    = 540 / 1080;  // = 0.5

// Di Alpine style binding:
fontSize: (tanggalSize * 0.5) + 'px'
// Di interactjs listener:
zone.x = Math.round(zone.x + event.dx / SCALE);
```

### Render zona di canvas

Zona dirender sebagai `<div class="zone-box absolute">` dengan posisi CSS berbasis persentase dari ukuran canvas:

```html
<div class="zone-box absolute"
     data-zone="zona_tanggal"
     style="
         left:  {{ $pos['x'] / 1080 * 100 }}%;
         top:   {{ $pos['y'] / 1920 * 100 }}%;
         width: {{ $pos['w'] / 1080 * 100 }}%;
         height:{{ $pos['h'] / 1920 * 100 }}%;
     ">
```

---

## 9. Zone Editor: interact.js

interact.js adalah library drag & resize untuk elemen DOM.

### Setup

```js
interact('.zone-box', { context: canvas })
    .draggable({
        listeners: {
            move: (event) => {
                const zone = this.zones[event.target.dataset.zone];

                zone.x = Math.round(zone.x + event.dx / SCALE);
                zone.y = Math.round(zone.y + event.dy / SCALE);

                // Batasi agar tidak keluar canvas
                zone.x = Math.max(0, Math.min(CANVAS_W - zone.w, zone.x));
                zone.y = Math.max(0, Math.min(CANVAS_H - zone.h, zone.y));

                this.applyPosition(event.target, zone);
                this.saveConfig();
            },
        },
    })
    .resizable({
        edges: { right: true, bottom: true, bottomRight: true },
        listeners: {
            move: (event) => {
                zone.w = Math.round(event.rect.width  / SCALE);
                zone.h = Math.round(event.rect.height / SCALE);
                this.applyPosition(box, zone);
                this.saveConfig();
            },
        },
    });
```

### applyPosition()

Update CSS elemen DOM secara langsung (bukan reaktif Alpine, agar responsif saat drag):

```js
applyPosition(el, zone) {
    el.style.left   = (zone.x / CANVAS_W * 100) + '%';
    el.style.top    = (zone.y / CANVAS_H * 100) + '%';
    el.style.width  = (zone.w / CANVAS_W * 100) + '%';
    el.style.height = (zone.h / CANVAS_H * 100) + '%';
},
```

---

## 10. Zone Editor: Alpine.js & Entangle

### Entangle

`$wire.$entangle('config')` menghubungkan property Alpine `state` dengan Livewire property `$config`. Setiap kali `state` diubah di Alpine, Livewire otomatis menerima perubahan.

```js
x-data="zoneEditorListPolos({
    ...,
    state: $wire.$entangle('config')
})"
```

### Flow simpan config

```
User geser zona / ubah slider
  → event handler Alpine → saveConfig()
      → this.state = { zona_tanggal: {...}, grid: {...}, ... }
      → entangle otomatis kirim ke Livewire $this->config
  → User klik "Simpan"
      → save() → $this->record->update(['config' => $this->config])
```

> `saveConfig()` dipanggil setiap perubahan (live preview), tapi simpan ke DB hanya saat klik Simpan.

### Struktur Alpine state

```js
return {
    zones: config.initialZones,
    gapV: config.initialGapV ?? 16,
    // ... semua field config sebagai Alpine property

    init() {
        // Override dari state DB (lebih up-to-date dari initialXxx)
        if (this.state?.grid?.gap_v !== undefined) this.gapV = this.state.grid.gap_v;
        this.$nextTick(() => this.setupInteract());
    },

    saveConfig() {
        // Rebuild seluruh config object → kirim ke Livewire via entangle
        this.state = {
            zona_tanggal: { ...this.zones.zona_tanggal, font: this.tanggalFont, ... },
            grid: { gap_v: parseInt(this.gapV) || 0, ... },
        };
    },
}
```

---

## 11. Integrasi JadwalHarianPerubahan di Poster

Tabel `jadwal_harian_perubahan` menyimpan perubahan mendadak dari jadwal harian (jam berubah atau libur). Model: `JadwalHarianPerubahan`.

### Cara kerja di buildHtml()

```php
$jadwalHarian = JadwalHarian::...->with(['poliklinik', 'dokter', 'perubahan'])->get();

// Di mapping tiap baris jadwal:
$p = $r->perubahan;  // JadwalHarianPerubahan|null (HasOne)

$jamMulai   = $p?->jam_mulai   ?? $r->jam_mulai;
$jamSelesai = $p?->jam_selesai ?? $r->jam_selesai;
$statusRaw  = $p?->status_layanan ?? ($r->status_layanan?->value ?? 'BUKA');

return [
    'jam_mulai'  => $jamMulai?->format('H:i'),
    'jam_selesai'=> $jamSelesai?->format('H:i'),
    'libur'      => $statusRaw === 'LIBUR',   // true → tampil "LIBUR" / tidak ditampilkan
    'catatan'    => $p?->catatan ?: $r->catatan,
];
```

Logika: perubahan selalu override nilai asli. Jika `$p` null, tampilkan nilai jadwal harian asli.

### Casts di JadwalHarianPerubahan

```php
protected function casts(): array
{
    return [
        'jam_mulai'           => 'datetime:H:i',
        'jam_selesai'         => 'datetime:H:i',
        'jam_mulai_asli'      => 'datetime:H:i',
        'jam_selesai_asli'    => 'datetime:H:i',
        'status_layanan'      => \App\Enums\StatusLayanan::class,
        'status_layanan_asli' => \App\Enums\StatusLayanan::class,
    ];
}
```

`StatusLayanan` enum: `BUKA` | `LIBUR`.

---

## 12. Cara Menambah Konfigurasi Baru

Contoh: menambahkan `border_radius_card` ke List Polos.

### Langkah 1 — Default config

```php
// app/Filament/PosterLayouts/Layouts/ListPolosLayout.php
'grid' => [
    // ...
    'border_radius_card' => 8,
]
```

### Langkah 2 — Blade template poster

```php
// jadwal-template-list-polos.blade.php
$borderRadiusCard = (int) ($g['border_radius_card'] ?? 8);

// Lalu gunakan dalam CSS/style:
border-radius: {{ $borderRadiusCard }}px;
```

### Langkah 3 — Zone editor: init value di PHP

```php
// zone-editor-page-list-polos.blade.php → bagian @php
$initialBorderRadiusCard = (int) ($g['border_radius_card'] ?? 8);
```

### Langkah 4 — Pass ke x-data Alpine

```php
initialBorderRadiusCard: {{ $initialBorderRadiusCard }},
```

### Langkah 5 — Alpine property + load dari state

```js
borderRadiusCard: config.initialBorderRadiusCard ?? 8,

// Dalam init():
if (this.state.grid?.border_radius_card !== undefined)
    this.borderRadiusCard = this.state.grid.border_radius_card;

// Dalam saveConfig() → grid:
border_radius_card: parseInt(this.borderRadiusCard) || 8,
```

### Langkah 6 — UI control di panel zone editor

```html
<input type="range" x-model.number="borderRadiusCard" @input="saveConfig()" min="0" max="30">
```

### Langkah 7 (opsional) — Quick Config

Tambahkan ke `quickConfigFields()` di layout:

```php
['key' => 'border_radius_card', 'label' => 'Radius sudut kartu', 'quick_setting' => true],
```

---

## 13. Cara Menambah Layout Baru (Cabang Baru)

Misalkan ada cabang baru RS ID 3.

### 1. Buat Layout class

```php
// app/Filament/PosterLayouts/Layouts/NewBranchLayout.php
class NewBranchLayout implements PosterLayout
{
    public function label(): string { return 'Layout Cabang Baru'; }

    public function quickConfigFields(): array
    {
        return [
            ['key' => 'gap_v', 'label' => 'Jarak antar poli', 'quick_setting' => true],
            // ... field lain
        ];
    }

    public function defaultConfig(): array { return ['zona_tanggal' => [...], 'grid' => [...]]; }
    public function zoneEditorPageClass(): string { return ZoneEditorPageNewBranch::class; }
    public function templateView(): string { return 'filament.resources.poster-jadwal-resource.pages.jadwal-template-new-branch'; }
}
```

### 2. Daftarkan di LayoutRegistry

```php
private const MAP = [
    1 => GridShapeLayout::class,
    2 => ListPolosLayout::class,
    3 => NewBranchLayout::class,   // ← tambah
];
```

### 3. Buat zone editor page

```php
// app/Filament/Resources/PosterTemplateResource/Pages/ZoneEditorPageNewBranch.php
class ZoneEditorPageNewBranch extends BaseZoneEditorPage
{
    protected static string $view = 'filament.resources.poster-template-resource.pages.zone-editor-page-new-branch';
}
```

### 4. Daftarkan route di PosterTemplateResource

```php
'zone-editor-new-branch' => Pages\ZoneEditorPageNewBranch::route('/{record}/zone-editor-new-branch'),
```

### 5. Tambahkan ke routing tombol "Edit Zone"

```php
$routeKey = match(true) {
    $layout instanceof ListPolosLayout => 'zone-editor-list-polos',
    $layout instanceof NewBranchLayout => 'zone-editor-new-branch',
    default                            => 'zone-editor',
};
```

### 6. Buat blade files

- `jadwal-template-new-branch.blade.php` — template poster (di-render Browsershot)
- `zone-editor-page-new-branch.blade.php` — UI zone editor

---

## 14. Pagination List Polos

Hanya aktif untuk `ListPolosLayout`. Dikontrol lewat `grid.poli_per_halaman`.

### Cara kerja

```
Total poli visible = 12
poli_per_halaman  = 5
totalHalaman      = ceil(12 / 5) = 3

Halaman 1 → poli index 0–4
Halaman 2 → poli index 5–9
Halaman 3 → poli index 10–11
```

Setiap halaman di-generate dan di-download terpisah. User navigasi halaman via tombol di UI, lalu download masing-masing.

### Nama file download

- Halaman tunggal: `poster-jadwal-01-07-2026.png`
- Multi halaman: `poster-jadwal-01-07-2026-hal2.png`

### recalcPagination() dipanggil saat:

- Template dipilih
- Tanggal berubah
- Poli di-toggle (visible/hidden)
- Quick config `poli_per_halaman` berubah (lewat reload generate)

---

## 15. File Map Lengkap

```
app/Filament/PosterLayouts/
  Contracts/PosterLayout.php                   ← interface (label, defaultConfig, zoneEditorPageClass, templateView, quickConfigFields)
  LayoutRegistry.php                           ← RS ID → layout class
  Layouts/
    GridShapeLayout.php                        ← Banjarbaru — grid multi-kolom, shape PNG, foto hero
    ListPolosLayout.php                        ← Barabai — tabel baris, pagination

app/Filament/Resources/PosterTemplateResource/
  Pages/
    BaseZoneEditorPage.php                     ← base class: auth, save(), $config property
    ZoneEditorPage.php                         ← Grid Shape zone editor (extends Base)
    ZoneEditorPageListPolos.php                ← List Polos zone editor (extends Base)

app/Filament/Pages/
  GeneratePosterPage.php                       ← generate + preview + download poster
                                               ← state: $data, $poli_list, $quickConfig, $quickConfigFields

app/Models/
  PosterTemplate.php                           ← model; layout() delegate ke LayoutRegistry
  JadwalHarianPerubahan.php                   ← perubahan mendadak; HasOne dari JadwalHarian

resources/views/filament/resources/
  poster-jadwal-resource/pages/
    jadwal-template.blade.php                  ← template poster Grid Shape (Browsershot 1080×1920)
    jadwal-template-list-polos.blade.php       ← template poster List Polos (Browsershot 1080×1920)
    generate-poster-page.blade.php             ← UI generate poster (Filament page)
  poster-template-resource/pages/
    zone-editor-page.blade.php                 ← UI zone editor Grid Shape
    zone-editor-page-list-polos.blade.php      ← UI zone editor List Polos
```

---

## Catatan Penting untuk AI

- **Config selalu punya fallback.** Template lama tidak punya semua key baru. Selalu gunakan `$g['key'] ?? defaultValue`.
- **Quick config hanya in-memory.** `$template->config` dimutasi tanpa `save()` — perubahan tidak pernah ke DB.
- **`nama_dokter` bisa override via kolom teks.** `jadwal_harian.nama_dokter` (string) > `dokter.nama` (relasi).
- **Browsershot tidak akses internet.** Google Fonts tidak dimuat saat generate PNG — hanya font sistem atau font upload (data URI).
- **Perubahan jadwal (`JadwalHarianPerubahan`) selalu menang.** Jika ada perubahan, jam/status dari perubahan yang dipakai, bukan nilai asli `jadwal_harian`.
- **`poli_per_halaman` adalah quick field List Polos** — mengubahnya lewat quick config mempengaruhi pagination tapi `recalcPagination` tidak otomatis dipanggil ulang; user perlu trigger generate ulang.
- **Tanggal di Grid Shape** selalu diformat 2 baris: `translatedFormat('l,') . '<br>' . translatedFormat('j F Y')` — jangan ubah ke `l, j F Y` single string karena break-nya tidak terkontrol.
- **Keterangan** mendukung newline via `nl2br(e($keterangan))` — background per-baris lewat `box-decoration-break: clone` pada `<span>` inline.
