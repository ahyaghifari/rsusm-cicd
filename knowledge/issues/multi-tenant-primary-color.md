# Planning: Warna Primary per Rumah Sakit (Multi-Tenant Theming)

> Status: **PLANNING ONLY** — belum dieksekusi.

## 1. Latar Belakang / Masalah

Saat ini warna `primary` (dan token terkait: `on-primary`, `primary-container`,
`on-primary-container`, `tertiary`, dst) di-define sekali secara global di
`resources/css/app.css` lewat blok `@theme`:

```css
@theme {
    --color-primary: #d606b0;
    --color-on-primary: #ffffff;
    --color-primary-container: #b649a9;
    --color-on-primary-container: #fffbff;
    --color-tertiary: #4d51b2;
    ...
}
```

Class Tailwind seperti `bg-primary`, `text-primary`, `border-primary`, dll
dipakai **399 kali** di seluruh `resources/views`. Pertanyaannya: kalau setiap
RS (Banjarbaru, Barabai, dst) ingin punya warna brand sendiri, apakah Tailwind
harus di-build ulang per RS / per request? Jawabannya **tidak perlu** — lihat
insight di bawah.

## 2. Insight Kunci — Tailwind v4 sudah pakai CSS Variable

Tailwind v4 men-generate utility class yang **memanggil** CSS custom property,
bukan hardcode hex. Dibuktikan dari compiled CSS (`public/build/assets/app-*.css`):

```css
:root, :host { --color-primary: #d606b0; ... }

.bg-primary  { background-color: var(--color-primary); }
.text-primary{ color: var(--color-primary); }
```

Bahkan varian opacity (`bg-primary/10`, `text-primary/40`, dst) juga ikut
`var()` lewat `color-mix()`:

```css
.bg-primary\/4 { background-color: color-mix(in oklab, var(--color-primary) 4%, transparent); }
```

**Artinya:** kita tidak perlu rebuild/watch Tailwind sama sekali untuk ganti
warna per RS. Build tetap sekali (semua nama class sudah ada di CSS), yang
berubah per request hanyalah **nilai** `--color-primary` dkk — bisa di-override
lewat satu blok `<style>` inline kecil di `<head>`, berdasarkan data RS yang
aktif (`$currentRumahSakit`, sudah ter-share lewat `RumahSakitMiddleware`).

## 3. Scope Token Warna

Dari hasil grep utility class yang dipakai di `resources/views`:

| Token CSS variable             | Dipakai sebagai class                                  |
|---------------------------------|---------------------------------------------------------|
| `--color-primary`               | `bg-primary`, `text-primary`, `border-primary`, `ring-primary`, `from/via/to-primary`, `shadow-primary`, `decoration-primary` (+ varian opacity) |
| `--color-on-primary`            | `text-on-primary`                                       |
| `--color-primary-container`     | `bg-primary-container`                                  |
| `--color-on-primary-container`  | (jarang, untuk konsistensi)                             |
| `--color-tertiary` / `on-tertiary` | dipakai sbg warna aksen sekunder (saat ini fix `#4d51b2`, sering ditulis manual via inline `style="background-color: {{ $warna }}"` di halaman jadwal praktek, bukan lewat Tailwind class) |

Untuk v1, token yang **wajib** ditema: `primary`, `on-primary`,
`primary-container`, `on-primary-container`. Token `tertiary` opsional /
fase 2 (banyak dipakai sebagai hardcoded `$warna = '#4d51b2'` di PHP, bukan
CSS var — perlu treatment terpisah kalau mau ikut ditema).

## 4. Opsi Pendekatan

### Opsi A — Seed color → auto-generate full M3 palette
Admin pilih 1 warna dasar, sistem generate seluruh token (primary,
on-primary, container, dst) memakai algoritma Material Color Utilities
(HCT / tonal palette — ini yang dipakai utk generate palette `app.css` saat
ini).
- ✅ Hasil konsisten & "Material-correct", admin cukup 1 input.
- ❌ Perlu port algoritma HCT ke PHP (tidak ada di Tailwind/Laravel secara
  native). Ada lib unofficial (`material-color-utilities` versi JS/Dart, port
  PHP jarang & belum tentu well-maintained).

### Opsi B — Beberapa token warna manual via Color Picker
Admin isi langsung 2–4 warna: `primary`, `on-primary`, (opsional)
`primary-container`, `on-primary-container`.
- ✅ Simple, tidak perlu library tambahan, full control.
- ❌ Admin harus paham kontras — kalau `on-primary` dipilih sama terangnya
  dengan `primary`, teks jadi tidak terbaca.
- Mitigasi: hitung kontras otomatis (WCAG luminance) → kalau kontras
  `primary` vs putih/hitam terlalu rendah, beri warning di form, atau
  auto-pilih `on-primary` (putih/hitam) berdasar luminance `primary`.

### Opsi C — Preset tema terkurasi
Sediakan beberapa preset (misal 6–8 skema warna) yang sudah didesain lengkap
(termasuk kontras), admin pilih salah satu via swatch/dropdown.
- ✅ Paling aman secara desain, implementasi paling sederhana, tidak ada
  resiko salah kontras.
- ❌ RS tidak bebas pilih warna sesuai brand masing-masing.

### Rekomendasi
**Opsi B (simplified) dengan auto-derive**: admin hanya pilih **1 warna
primary**. `on-primary` di-derive otomatis (hitam/putih berdasarkan luminance
primary), `primary-container` & `on-primary-container` di-derive via
lighten/darken HSL sederhana di PHP (tidak perlu algoritma HCT penuh).
Kalau hasilnya dirasa kurang pas, baru buka opsi "advanced" untuk override
manual per token (hybrid B). Opsi A & C disimpan sebagai catatan alternatif.

## 5. Rencana Implementasi (high-level, belum dieksekusi)

### 5.1 Database
Migration tambah kolom ke tabel `rumah_sakit`:
- `warna_primary` (`string`, nullable) — hex (`#rrggbb`). `null` = pakai
  default global dari `app.css` (behavior saat ini, tidak ada perubahan).
- (opsional, fase advanced) `warna_on_primary`, `warna_primary_container`,
  `warna_on_primary_container` — nullable, override manual kalau auto-derive
  tidak cocok.

### 5.2 Model
`RumahSakit`: tambah kolom baru ke `$fillable`. Tambah helper
method/accessor, misal `getThemeColorsAttribute()` atau service
`RumahSakitTheme::resolve($rs)` yang return array token siap pakai
(`primary`, `on_primary`, `primary_container`, `on_primary_container`),
sudah mengisi default kalau kolom null & sudah meng-handle derive.

### 5.3 Layout — inject CSS variable override
Di `<head>` `layouts/rumah_sakit.blade.php`, tambah blok kecil setelah
`@vite(...)`:

```blade
@if($currentRumahSakit->warna_primary)
<style>
    :root {
        --color-primary: {{ $theme['primary'] }};
        --color-on-primary: {{ $theme['on_primary'] }};
        --color-primary-container: {{ $theme['primary_container'] }};
        --color-on-primary-container: {{ $theme['on_primary_container'] }};
    }
</style>
@endif
```

`$theme` disiapkan oleh `RumahSakitMiddleware` (sejalan dengan
`$currentRumahSakit`, `$daftarRS`, dst yang sudah di-share saat ini).

### 5.4 Helper derive warna (PHP, tanpa lib tambahan)
- `on_primary`: hitung relative luminance `primary` (formula WCAG) → kalau
  gelap pakai putih, kalau terang pakai hitam/`on-surface`.
- `primary_container`: lighten `primary` (HSL, naikkan lightness ~25–30%,
  turunkan saturation sedikit).
- `on_primary_container`: kebalikan — gunakan `primary` itu sendiri atau
  darken sedikit, supaya kontras dgn `primary_container`.
- Semua ini fungsi murni, mudah di-unit-test.

### 5.5 Filament — input warna
- Tambah `Forms\Components\ColorPicker::make('warna_primary')` di
  `RumahSakitResource` (form utama, superadmin) **dan/atau** widget
  dashboard tersendiri mengikuti pola `JadwalPoliklinikPopupWidget` /
  `PromoPopupWidget` (auto-scope RS utk admin/humas/informasi, RS selector
  utk superadmin).
- Tampilkan live preview kecil (swatch primary / on-primary / container)
  di sebelah color picker.
- Tombol "Reset ke default" → set `warna_primary = null`.

### 5.6 Edge cases
- `warna_primary = null` → tidak ada `<style>` override → behavior identik
  dgn sekarang (zero risk utk RS yang belum diatur).
- Validasi format hex di form (regex `^#[0-9a-fA-F]{6}$`).
- View cache (`view:cache`) tidak masalah karena `<style>` block isinya
  dynamic (di-render per request, bukan di-compile).
- Token `tertiary` (fase 2, kalau dibutuhkan) perlu treatment beda karena
  banyak dipakai sebagai variabel PHP `$warna = '#4d51b2'` inline, bukan
  Tailwind class — bisa diarahkan untuk baca dari `$theme['tertiary']` juga.

### 5.7 Testing checklist
- 2 RS dengan `warna_primary` berbeda → cek visual di halaman beranda,
  jadwal praktek, popup promo/jadwal, dsb (399 titik pemakaian — spot check
  representative pages).
- Cek varian opacity (`/5`, `/10`, `/20`, ...) ikut berubah otomatis.
- Cek RS dengan `warna_primary = null` → tampilan tidak berubah sama sekali.
- Pastikan Filament admin panel **tidak terpengaruh** (sistem warna Filament
  terpisah dari `--color-primary` portal).

## 6. Out of Scope (planning ini)
- Theming Filament admin panel per RS (Filament punya sistem shade-scale
  warna sendiri via `FilamentColor::register()`, terpisah total dari
  `--color-*` di `app.css`).
- Dark mode (portal RS belum pakai dark mode).
- Auto-extract warna dari logo RS.

## 7. Pertanyaan untuk Didiskusikan Sebelum Eksekusi
1. Token mana saja yang perlu ditema per RS — cukup `primary` (+ auto-derive
   3 lainnya), atau RS perlu kontrol penuh ke-4 token secara manual?
2. Apakah `tertiary`/`on-tertiary` (warna aksen di halaman jadwal praktek,
   dll) ikut masuk fase 1, atau ditunda?
3. Siapa yang berhak ubah warna — superadmin saja, atau admin per-RS juga
   (mengikuti pola widget popup yang sudah ada)?
4. Perlu live-preview di form Filament sebelum simpan, atau cukup simpan →
   refresh halaman publik untuk lihat hasil?
