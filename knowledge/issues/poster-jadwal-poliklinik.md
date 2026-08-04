# Feature: Poster Jadwal Poliklinik

## Latar Belakang

Humas RSU Syifa Medika saat ini membuat poster jadwal poliklinik secara manual (Canva, dll).
Fitur ini memungkinkan humas membuat template poster sendiri dan men-generate poster jadwal
secara otomatis dari data JadwalHarian / JadwalPraktek yang sudah ada di sistem.

Setiap RS memiliki branding berbeda — template sepenuhnya terisolasi per RS.

> **Catatan Multi-Cabang**: humas tiap cabang berbeda orang, desainernya juga berbeda — gaya
> poster antar cabang bisa sangat berbeda. Contoh di folder referensi `poster/`:
> - **Banjarbaru (Aurora EC)**: hero foto besar di atas, grid jadwal **2 kolom** dengan badge `shape_poli`
> - **Barabai**: tanpa foto hero, jadwal berupa **list 1 kolom** polos, branding lebih sederhana
>
> Karena itu **layout & pemakaian layer hero dijadikan opsi per template**, bukan fixed —
> lihat field `layout` dan `pakai_hero` di Config JSON.

---

## Arsitektur Poster: 3 Layer (Layer 1 opsional)

```
┌─────────────────────────────────────┐
│  LAYER 3 — Konten Dinamis           │
│  ├── Logo header (upload/template)  │
│  ├── Teks tanggal (per generate)    │
│  ├── Teks keterangan hero           │ ← hanya jika pakai_hero = true
│  └── Jadwal                         │
│      grid_2_kolom: shape_poli PNG   │
│      list_1_kolom: list teks polos  │
├─────────────────────────────────────┤
│  LAYER 2 — Template PNG             │
│  (upload per template, transparan)  │
│  ├── Background + gradasi           │
│  ├── Teks "JADWAL POLIKLINIK"       │
│  └── Footer kontak                  │
├─────────────────────────────────────┤
│  LAYER 1 — Foto Hero (opsional)     │
│  (upload tiap generate,             │
│   hanya jika config.pakai_hero)     │
└─────────────────────────────────────┘
```

> Saat `pakai_hero = false` (mis. template gaya Barabai), form generate **tidak menampilkan**
> field upload foto hero & keterangan hero, dan Layer 1 dilewati saat render.

### Layer 2 — Template PNG

Humas desain di Canva, export sebagai **PNG** (wajib PNG supaya transparansi terjaga).

Isi yang sudah di-bake ke PNG:
- Background warna/tekstur/gradasi
- Efek gradasi fade di area hero (area hero transparan, gradasi di atasnya)
- Teks "JADWAL POLIKLINIK" (didesain bebas — font, warna, dekorasi)
- Footer berisi kontak, sosmed, nomor telepon

Yang **tidak** ada di template PNG (di-render sistem di layer 3):
- Logo header
- Tanggal
- Keterangan hero
- Grid jadwal + shape poliklinik

### Layer 3 — Jadwal: 2 Varian Layout

Layout jadwal dipilih per template via `config.layout`: `grid_2_kolom` atau `list_1_kolom`.
Keduanya merender data poli yang sama (nama poli, badge unit layanan, list dokter + jam),
hanya berbeda struktur visual — supaya satu sistem bisa melayani gaya desain yang sangat
berbeda antar cabang/humas.

#### Varian `grid_2_kolom` (contoh: Banjarbaru/Aurora)

Shape poliklinik diupload sebagai PNG transparan terpisah (`shape_poli`).
Sistem overlay nama poli di atas shape, lalu list dokter di bawahnya.

```
zona_jadwal (2 kolom, fixed)
├── Kolom kiri              Kolom kanan
│   ┌────────────────┐      ┌────────────────┐
│   │ [shape_poli]   │      │ [shape_poli]   │
│   │ Poli Umum      │      │ Poli Anak      │
│   └────────────────┘      └────────────────┘
│   dr. A   08:00–12:00     dr. B  09:00–15:00
│   dr. C   [LIBUR]         dr. D  [Aurora EC]
```

#### Varian `list_1_kolom` (contoh: Barabai)

Tanpa `shape_poli` — nama poliklinik dirender sebagai teks/heading polos (warna & font dari
config), diikuti list dokter + jam di bawahnya, satu kolom penuh memanjang ke bawah.

```
zona_jadwal (1 kolom, full width)
│  Jadwal Poliklinik Gigi Anak
│   drg. Aulia Rifki Syarif      14.00–17.00
│
│  Jadwal Poliklinik Anak
│   dr. Tri Catriyani Suparti, Sp.A     08.00–selesai
│
│  Jadwal Poliklinik Kandungan
│   dr. Ilanda Eka Susanti, Sp.OG       08.00–selesai
```

Badge unit layanan (kedua varian) otomatis dari relasi `poliklinik → unitLayanan`:
- `unitLayanan.nama` → teks badge
- `unitLayanan.warna` → warna badge
- Di varian `list_1_kolom`, badge ditampilkan inline di sebelah nama poli (bukan di atas shape)

---

## Manajemen Template (`/manage/poster-templates`)

### Asset Upload per Template

| Field | Format | Keterangan |
|---|---|---|
| `template_png` | PNG | Background + gradasi + judul + footer — desain bebas di Canva |
| `logo_header` | PNG/JPG | Logo RS, ditempatkan di layer 3 |
| `shape_poli` | PNG transparan | **Hanya untuk `layout: grid_2_kolom`** — background header nama poliklinik di grid. Disembunyikan di form jika layout = `list_1_kolom` |

### Config JSON

`layout` dan `pakai_hero` menentukan struktur poster — field zona/grid lain menyesuaikan
varian yang dipilih (mis. `shape_poli`/`grid` hanya relevan untuk `grid_2_kolom`,
`zona_hero`/`zona_keterangan` hanya relevan jika `pakai_hero: true`).

```json
{
  "layout": "grid_2_kolom",
  "pakai_hero": true,

  "zona_hero": {
    "x": 0, "y": 0, "w": 1080, "h": 760
  },
  "zona_logo": {
    "x": 60, "y": 60, "w": 300, "h": 120
  },
  "zona_tanggal": {
    "x": 80, "y": 940, "w": 900,
    "font": "Montserrat", "size": 40,
    "warna": "#FFFFFF", "align": "left"
  },
  "zona_keterangan": {
    "x": 80, "y": 1000, "w": 900,
    "font": "Poppins", "size": 24,
    "warna": "#F0C040", "align": "left"
  },
  "zona_jadwal": {
    "x": 40, "y": 1080, "w": 1000, "h": 780
  },
  "grid": {
    "kolom": 2,
    "gap": 16,
    "font_nama_poli": { "sumber": "google", "nama": "Montserrat" },
    "size_nama_poli": 15,
    "warna_nama_poli": "#FFFFFF",
    "font_isi": { "sumber": "upload", "path": "poster-fonts/rs-1/BrandFont.ttf" },
    "size_nama_dokter": 13,
    "warna_nama_dokter": "#1A1A1A",
    "size_jam": 12,
    "warna_jam": "#555555"
  },
  "font_tanggal":    { "sumber": "google", "nama": "Montserrat" },
  "font_keterangan": { "sumber": "upload", "path": "poster-fonts/rs-1/BrandFont.ttf" }
}
```

Contoh config untuk varian `list_1_kolom` tanpa hero (gaya Barabai) — tidak ada
`zona_hero`/`zona_keterangan`/`shape_poli`, dan `list` menggantikan `grid`:

```json
{
  "layout": "list_1_kolom",
  "pakai_hero": false,

  "zona_logo": {
    "x": 60, "y": 60, "w": 280, "h": 100
  },
  "zona_tanggal": {
    "x": 80, "y": 200, "w": 900,
    "font": "Montserrat", "size": 36,
    "warna": "#1A1A1A", "align": "left"
  },
  "zona_jadwal": {
    "x": 60, "y": 320, "w": 960, "h": 1500
  },
  "list": {
    "gap": 24,
    "font_nama_poli": { "sumber": "google", "nama": "Poppins" },
    "size_nama_poli": 20,
    "warna_nama_poli": "#1A56DB",
    "font_isi": { "sumber": "google", "nama": "Poppins" },
    "size_nama_dokter": 15,
    "warna_nama_dokter": "#1A1A1A",
    "size_jam": 13,
    "warna_jam": "#555555"
  },
  "font_tanggal": { "sumber": "google", "nama": "Montserrat" }
}
```

### Zone Editor (Drag & Drop)

Setelah template PNG diupload, form menampilkan preview poster (scaled) dengan
kotak draggable+resizable untuk tiap zona. Pilihan `layout` dan `pakai_hero`
ditentukan **sebelum** zone editor dibuka — zona yang ditampilkan menyesuaikan:

```
┌──────────────────────────────┐  ← preview 1080×1920 (scaled)
│  🟪 zona_hero                │  hanya muncul jika pakai_hero = true
│  🟦 zona_logo                │  drag → simpan x,y,w,h
│  🟨 zona_tanggal             │  drag → simpan x,y + config font
│  🟩 zona_keterangan          │  hanya muncul jika pakai_hero = true
│  🟥 zona_jadwal              │  drag+resize → simpan x,y,w,h
└──────────────────────────────┘
```

- `layout: grid_2_kolom` → form menampilkan upload `shape_poli` + pengaturan `grid` (kolom, gap, font)
- `layout: list_1_kolom` → form menyembunyikan `shape_poli`, menampilkan pengaturan `list` (gap, font) sebagai gantinya

Implementasi: **interact.js** via Alpine.js di custom Filament form component.
Koordinat dalam px relatif terhadap 1080×1920.

---

## Generate Poster (`/manage/generate-poster`)

### Input Form

```
┌─────────────────────────────────────┐
│  1. Pilih Template  [▼ Template A]  │
│  2. Pilih Tanggal   [04/06/2026]    │
│                                     │
│  3. Urutkan & Sembunyikan Poli      │
│     ┌────────────────────────────┐  │
│     │ ☰  ✅  Poli Umum          │  │  ← drag untuk sort
│     │ ☰  ✅  Poli Anak          │  │  ← toggle untuk show/hide
│     │ ☰  ❌  Poli Bedah         │  │
│     │ ☰  ✅  Poli Jantung       │  │
│     └────────────────────────────┘  │
│                                     │
│  4. Upload Foto Hero  [Upload...]   │  ← hanya tampil jika
│  5. Keterangan Hero   [________]    │     template.config.pakai_hero = true
│     "Tindakan EXILIS Aurora..."     │     (mis. disembunyikan utk template Barabai)
│                                     │
│  [ Preview ]   [ Download PNG ]     │
└─────────────────────────────────────┘
```

### Alur Generate

```
Pilih template + tanggal
        ↓
Load semua poli yang punya jadwal di tanggal + RS tsb
(JadwalHarian prioritas, fallback JadwalPraktek)
        ↓
Humas sort + toggle poli (visible/hidden)
        ↓
Input foto hero + keterangan      ← dilewati jika config.pakai_hero = false
        ↓
Render HTML 1080×1920:
  Layer 1: foto hero di zona_hero        (hanya jika pakai_hero = true)
  Layer 2: template PNG (full cover)
  Layer 3: logo + tanggal
           + keterangan                  (hanya jika pakai_hero = true)
           + jadwal sesuai config.layout:
             - grid_2_kolom → shape_poli + grid 2 kolom
             - list_1_kolom → list 1 kolom polos
           (hanya poli yang visible, urutan sesuai sort)
        ↓
Browsershot screenshot → PNG 1080×1920
        ↓
Download
```

### Data Jadwal per Poliklinik

```php
// Prioritas JadwalHarian
$jadwal = JadwalHarian::whereDate('tanggal', $tanggal)
    ->whereHas('poliklinik.unitLayanan', fn($q) =>
        $q->where('rumah_sakit_id', $rsId)
    )
    ->with(['poliklinik.unitLayanan'])
    ->get()
    ->groupBy('poliklinik_id');

// Fallback JadwalPraktek jika JadwalHarian kosong
```

---

## Database

```sql
poster_templates
├── id
├── rumah_sakit_id        FK → rumah_sakit
├── nama                  string
├── template_png          string    ← path file PNG
├── logo_header           string    ← path file PNG/JPG
├── shape_poli            string    ← path file PNG transparan
├── config                JSON      ← zona + grid styling
├── is_default            boolean   default false
├── created_at
└── updated_at
```

---

## File yang Perlu Dibuat

```
app/
├── Models/
│   └── PosterTemplate.php
├── Filament/Resources/
│   └── PosterTemplateResource.php
│       └── Pages/
│           ├── ListPosterTemplates.php
│           ├── CreatePosterTemplate.php
│           └── EditPosterTemplate.php
├── Filament/Pages/
│   └── GeneratePosterPage.php

resources/views/
├── filament/pages/
│   └── generate-poster.blade.php
└── poster/
    ├── jadwal-template.blade.php       ← shell 3-layer, di-render Browsershot
    ├── partials/
    │   ├── jadwal-grid-2-kolom.blade.php   ← partial layout grid_2_kolom (shape_poli)
    │   └── jadwal-list-1-kolom.blade.php   ← partial layout list_1_kolom (polos)
    └── (partial dipilih sesuai config.layout saat render)

database/migrations/
└── xxxx_create_poster_templates_table.php
```

---

## Teknologi

| Kebutuhan | Solusi |
|---|---|
| Screenshot HTML → PNG | `spatie/browsershot` (Node.js tersedia di server) |
| Drag+resize zone editor | `interact.js` via Alpine.js |
| Sortable poli list | `SortableJS` via Alpine.js |
| Upload file | Filament FileUpload + Laravel Storage |
| Font sistem | Hybrid: Google Fonts CDN **atau** upload file `.ttf`/`.woff2` |
| Load font di Blade | Google → `@import` CDN, Upload → `@font-face` dari storage |

---

## Fase Implementasi

### Fase 1 — Pondasi Data
- [ ] Migrasi tabel `poster_templates`
- [ ] Model `PosterTemplate` (cast `config` → array)
- [ ] `PosterTemplateResource` CRUD di Filament, scoped per RS
- [ ] Upload field: `template_png`, `logo_header`, `shape_poli`
- [ ] Install `spatie/browsershot`

### Fase 2 — Zone Editor
- [ ] Custom Filament component: zone editor (interact.js + Alpine.js)
- [ ] Preview template PNG sebagai background
- [ ] 4 zona draggable: logo, tanggal, keterangan, jadwal
- [ ] Koordinat tersimpan ke config JSON

### Fase 3 — Blade Poster
- [ ] `resources/views/poster/jadwal-template.blade.php` (shell 3-layer, hero opsional sesuai `pakai_hero`)
- [ ] Partial `jadwal-grid-2-kolom.blade.php`: shape_poli PNG + overlay nama poli + list dokter
- [ ] Partial `jadwal-list-1-kolom.blade.php`: heading nama poli polos + list dokter, 1 kolom
- [ ] Pilih partial sesuai `config.layout` saat render
- [ ] Badge unit layanan (nama + warna dari `unitLayanan`) — tampil di kedua varian layout
- [ ] Google Fonts dynamic load dari config

### Fase 4 — Generate Page
- [ ] `GeneratePosterPage` Filament
- [ ] Load poli dari JadwalHarian (fallback JadwalPraktek)
- [ ] Sortable + toggle visible/hidden per poli (SortableJS)
- [ ] Form: pilih template, tanggal, upload hero + keterangan (field hero disembunyikan jika `template.config.pakai_hero = false`)
- [ ] Browsershot render → download PNG 1080×1920

### Fase 5 — Polish
- [ ] Preview poster sebelum download
- [ ] Validasi: minimal 1 poli visible sebelum generate
- [ ] Notifikasi sukses/gagal

---

## Keputusan yang Sudah Disepakati

- [x] 3 layer: foto hero (bawah, opsional) → template PNG → konten dinamis (atas)
- [x] Template PNG isinya: background + gradasi + judul + footer (baked-in di Canva)
- [x] Logo, tanggal, keterangan, jadwal → layer 3 (render sistem)
- [x] **Layout jadwal dipilih per template** via `config.layout`: `grid_2_kolom` (shape_poli, gaya Banjarbaru) atau `list_1_kolom` (polos, gaya Barabai) — karena humas & desainer tiap cabang berbeda gaya
- [x] **Layer hero bersifat opsional** via `config.pakai_hero` — template tanpa foto hero (mis. Barabai) menyembunyikan field upload hero & keterangan di form generate, dan melewati Layer 1 saat render
- [x] Shape poli → upload PNG transparan per template, **hanya untuk layout `grid_2_kolom`**
- [x] Zona posisi → draggable di zone editor, koordinat px di config JSON; zona yang ditampilkan menyesuaikan `layout` & `pakai_hero`
- [x] Foto hero → upload tiap generate (bukan disimpan di template), hanya jika `pakai_hero = true`
- [x] Keterangan hero → input bebas tiap generate (sesuai konteks foto), hanya jika `pakai_hero = true`
- [x] Poli list saat generate → sortable (drag) + bisa hidden (toggle)
- [x] Badge unit layanan → otomatis dari `unitLayanan.nama` + `unitLayanan.warna`
- [x] Output → PNG 1080×1920
- [x] Generate → Browsershot (Node.js tersedia di server)
- [x] Template terisolasi per RS, satu RS bisa banyak template
- [x] Font → hybrid: pilih dari Google Fonts (dropdown ~25 font populer dengan preview) ATAU upload file `.ttf`/`.woff2` sendiri
- [x] Font config tersimpan sebagai `{ "sumber": "google"|"upload", "nama"|"path": "..." }`
- [x] Berlaku per elemen: font tanggal, font keterangan, font nama poli, font isi grid (bisa beda-beda)
