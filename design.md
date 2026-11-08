# Design System — RSU Syifa Medika Portal

Dokumentasi visual/design untuk frontend publik (`rumah_sakit/*`, `components/*`). Panel admin pakai Filament default (theme di `resources/css/filament/admin/theme.css`), tidak dibahas di sini.

## Sumber Token

Semua token warna, font, dan spacing didefinisikan via Tailwind v4 `@theme` di [resources/css/app.css](resources/css/app.css). Token ini digenerate dari skema **Material Design 3 (M3)** — jadi penamaan warna ikut konvensi M3 (`primary`, `on-primary`, `surface-container-high`, dst), bukan skema custom.

## Warna

| Role | Variable | Hex |
|---|---|---|
| Primary | `--color-primary` | `#d606b0` (magenta) |
| On Primary | `--color-on-primary` | `#ffffff` |
| Primary Container | `--color-primary-container` | `#b649a9` |
| Secondary | `--color-secondary` | `#006c4b` (hijau tua) |
| Secondary Container | `--color-secondary-container` | `#96f6c8` |
| Tertiary | `--color-tertiary` | `#4d51b2` (indigo) |
| Error | `--color-error` | `#ba1a1a` |
| Background | `--color-background` | `#f8f9ff` |
| Surface | `--color-surface` | `#f8f9ff` |
| On Surface | `--color-on-surface` | `#0b1c30` |
| Outline | `--color-outline` | `#84727e` |
| Outline Variant | `--color-outline-variant` | `#d6c0ce` |

Dipakai di Tailwind sebagai class biasa: `bg-primary`, `text-on-surface`, `border-outline-variant`, `shadow-primary`, dll. Ada juga catatan historis: baris `--color-primary` lama (`#006c4b`, hijau) dikomentari, lalu diganti magenta — jadi kalau ketemu referensi hijau sebagai "primary" di komponen lama, itu residu sebelum ganti warna.

Catatan: nama variable M3 kadang tidak match makna visualnya sendiri secara intuitif (misal `--color-secondary-fixed-dim: #7ad9ad` justru lebih terang dari `--color-secondary: #006c4b`) — ini bawaan generator M3 token, jangan dianggap typo.

## Tipografi

- Font tunggal: **Plus Jakarta Sans**, dimuat dari Google Fonts di layout (`layouts/rumah_sakit.blade.php`, `layouts/portal-layout.blade.php`).
- Semua token `--font-*` (headline-lg, body-md, label-md, dst — skala M3 typography) diset ke font yang sama, cuma size/weight yang beda via class Tailwind biasa (`text-xl font-bold`, dll), bukan lewat token font terpisah.

## Spacing / Layout

| Token | Value | Guna |
|---|---|---|
| `--spacing-unit` | 8px | base unit |
| `--spacing-margin-desktop` | 40px | margin kiri-kanan desktop |
| `--spacing-margin-mobile` | 16px | margin kiri-kanan mobile |
| `--spacing-section-gap` | 80px | jarak antar section |
| `--spacing-container-max` | 1280px | max-width container |
| `--spacing-gutter` | 24px | gutter grid/kolom |

## Shape (Border Radius)

Hierarki radius dari class yang paling sering dipakai di seluruh frontend:

- `rounded-full` — paling dominan, untuk pill/badge/avatar/button bulat.
- `rounded-2xl` — radius default untuk card besar (hero, section card).
- `rounded-xl` / `rounded-lg` — untuk elemen medium (input, dropdown, sub-card).
- `rounded-3xl` — dipakai terbatas, untuk container hero/feature besar.

Konsisten dengan gaya M3: shape sangat rounded, hampir tidak ada elemen bersiku tajam.

## Shadow

Tailwind shadow standar dominan: `shadow-sm` > `shadow-lg` > `shadow-md` > `shadow-xl`. Ada juga shadow ber-warna custom (`shadow-primary`, `shadow-tertiary`, `shadow-green`, `shadow-red`) yang kemungkinan dari preset M3/Filament — dipakai untuk efek glow sesuai warna elemen (misal CTA primary pakai `shadow-primary`).

## Komponen Berulang

Lokasi: [resources/views/components/](resources/views/components/)

- `dokter-card.blade.php` — kartu profil dokter (dipakai di listing & jadwal).
- `header-portal.blade.php` / `footer-portal.blade.php` — header/footer khusus layout portal (beda dari `rumah_sakit/header.blade.php` & `footer.blade.php` yang dipakai layout utama).
- `page-hero.blade.php` — hero section generik, dipakai lintas halaman statis.
- `portal-pagination.blade.php` — pagination custom gantiin pagination default Laravel.
- `rawat-inap.blade.php` — komponen khusus info ketersediaan rawat inap.

## Styling Khusus (bukan Tailwind utility biasa)

Didefinisikan manual di `app.css`:

- **Focus ring sitewide** — banyak komponen pakai `focus:outline-hidden` tanpa pengganti visual, jadi ada aturan global `*:focus-visible` pakai `outline: 2px solid var(--color-primary)` sebagai jaring pengaman aksesibilitas keyboard. Jangan dihapus tanpa pastiin semua komponen punya focus indicator sendiri.
- **Tom Select portal theme** (`.ts-portal-wrapper`) — override tampilan dropdown search/select pihak ketiga (Tom Select) biar konsisten sama design system (rounded-xl, shadow, warna primary saat hover/selected/focus).
- **`.tentang-kami-prose strong`** — highlight teks bold di rich text "Kenapa Memilih Kami" pakai background tint primary + padding, bukan sekadar bold polos.
- **`.animate-fade-in`** — animasi fade+slide-up 0.35s, dipakai untuk reveal konten saat load/scroll.

## Library Visual Eksternal

- **Swiper** (`swiper/css`, `swiper/css/autoplay`) — carousel/slider.
- **Preline UI** — komponen interaktif Tailwind (dropdown, tabs, dll), di-source lewat `@source` biar class-nya kebaca Tailwind JIT.
- **Tom Select** — dropdown/select searchable, di-style ulang manual (lihat atas).

## Catatan untuk Perubahan Desain

- Ubah warna brand → edit `@theme` di `app.css`, jangan hardcode hex di blade/component.
- Tambah halaman baru → reuse `page-hero`, ikuti radius (`rounded-2xl`) dan shadow (`shadow-sm`/`shadow-lg`) yang sudah dominan biar konsisten, jangan introduce radius/shadow baru tanpa alasan.
- Font tetap satu keluarga (Plus Jakarta Sans) di semua token `--font-*` — kalau mau tambah font kedua (misal buat heading beda), perlu tambah variable baru, bukan override yang ada.
