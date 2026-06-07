# RSU Syifa Medika — Sistem Informasi Rumah Sakit

Sistem informasi multi-tenant untuk manajemen dan portal publik rumah sakit. Satu instalasi dapat melayani beberapa rumah sakit sekaligus, masing-masing dengan slug tersendiri, konten independen, dan akun admin yang terisolasi.

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Framework | Laravel 12 |
| Admin Panel | Filament 3.x |
| Reactive UI | Livewire 3 |
| Styling | Tailwind CSS v4 |
| Asset Bundler | Vite |
| Database | MySQL (dev & prod) |
| Icons | Material Symbols (Google Fonts) |
| Animasi | AOS (Animate On Scroll) |
| Searchable Select | Tom Select 2.x (CDN) |
| Slider | Swiper.js |
| Full-text Search | MySQL FULLTEXT index (LIKE fallback SQLite) |

---

## Arsitektur

Project ini menggunakan arsitektur **multi-tenant berbasis kolom** (`rumah_sakit_id`). Semua entitas konten dimiliki oleh satu `RumahSakit`. Akses data dibatasi di level resource Filament dan middleware route.

```
RumahSakit (slug, executive_clinic)
├── User (admin rumah sakit, terikat ke 1 RS)
├── Dokter → Spesialis                          (SoftDeletes)
├── Spesialis                                   (SoftDeletes)
├── PoliKlinik                                  (SoftDeletes, slug unik per RS)
│   └── JadwalPraktek (per poliklinik, per hari, opsional per dokter, is_executive)
├── JadwalHarian (override harian per tanggal, is_executive)
│   └── JadwalHarianPerubahan (1-to-1, tracking perubahan status)
├── PosterTemplate (background PNG, logo, shape_poli, config zona JSON)
├── RawatInap → Gedung
│   ├── GambarRawatInap
│   └── FasilitasRawatInap
├── LayananUnggulan
├── FasilitasPendukung
├── PenunjangMedis
├── Banner
├── Promo (slug unik per RS)
├── Halaman statis (slug unik per RS, field: kata_kunci)
├── Magazine
├── Faq (field: kata_kunci)
├── Partner
├── Kontak (kategori: SOSIAL MEDIA | OPERASIONAL | PENDAFTARAN)
└── LinkLayanan
```

> **Arsitektur jadwal:** `JadwalPraktek` adalah jadwal rawat jalan per **poliklinik** (bukan per dokter).
> `JadwalHarian` adalah override untuk tanggal spesifik, dapat dimuat otomatis dari `JadwalPraktek` via cron.
>
> **Executive Clinic:** Flag `executive_clinic` di `RumahSakit` mengaktifkan fitur jadwal executive. Field `is_executive` di setiap baris jadwal menandai sesi executive dengan tampilan warna brand `#e8cd84`.

### Role

| Role | Akses |
|---|---|
| `super_admin` | Semua rumah sakit, semua resource |
| `admin` | Hanya rumah sakit milik akun tersebut |
| `humas` | Akses terbatas (konten publik) |
| `informasi` | Akses terbatas (informasi umum) |

---

## Fitur

### Halaman Landing (`/`)

- Welcome section bergradasi dengan background gambar RS
- Search & filter: pilih RS + filter spesialis dokter (Tom Select, AJAX)
- Promo aktif semua RS dengan filter per RS
- jQuery **dihapus** — semua interaksi vanilla JavaScript

### Portal Publik (`/{slug-rs}/...`)

| Route | Halaman |
|---|---|
| `/` | Beranda rumah sakit |
| `/dokter-kami` | Daftar & cari dokter (urut A–Z, filter nama + spesialis) |
| `/dokter-kami/{dokter}` | Profil dokter + jadwal praktek |
| `/jadwal-praktek` | Jadwal praktek (Per Hari / Per Poli) |
| `/rawat-jalan` | Daftar poliklinik |
| `/rawat-jalan/{poliklinik}` | Detail poliklinik + jadwal |
| `/rawat-inap` | Informasi kelas rawat inap |
| `/unggulan` | Layanan unggulan |
| `/fasilitas-pendukung` | Fasilitas pendukung |
| `/penunjang-medis` | Penunjang medis |
| `/partner-kami` | Mitra rumah sakit |
| `/hubungi-kami` | Kontak & lokasi |
| `/promo` | Daftar promo aktif |
| `/promo/{slug}` | Detail promo |
| `/info/{slug}` | Halaman statis CMS |
| `/magazine` | Arsip majalah digital |
| `/faq` | FAQ |

#### Halaman Jadwal Praktek

Dua mode tampilan:

| Mode | Deskripsi |
|---|---|
| **Per Hari** | Tab SENIN–MINGGU, kartu per poliklinik. Filter poliklinik via Tom Select. |
| **Per Poli** | Pilih poliklinik → tampilkan semua 7 hari (grid horizontal di desktop). |

- Badge **Sesuai Perjanjian** (hijau) ditampilkan jika `sesuai_perjanjian = true`
- Badge **Executive Clinic** (warna brand `#e8cd84`) ditampilkan jika RS mengaktifkan `executive_clinic` dan sesi memiliki `is_executive = true`
- Sesi executive dikelompokkan per dokter — semua slot jam tampil sekaligus dalam satu baris
- Di bawah jadwal terdapat **disclaimer** dengan nomor kontak PENDAFTARAN (klik langsung `tel:` / WhatsApp)
- Nama dokter yang terhubung ke profil dapat diklik → navigasi ke profil dokter

#### Global Search

- Tombol search di navbar atau shortcut **Ctrl+K**
- Spotlight modal real-time, debounce 350ms
- Mencari: Dokter (nama + spesialis), Poliklinik, Promo, FAQ, Halaman Statis
- MySQL: FULLTEXT boolean mode dengan partial match (`word*`)
- SQLite/test: fallback LIKE
- Hasil dikelompokkan per kategori, klik langsung navigasi

#### Chatbot Asisten (Syifa Medika Assistant)

- **Mobile**: tombol di bottom bar, panel fullscreen
- **Desktop**: FAB floating bottom-right dengan tooltip animasi, panel setinggi layar
- State persisten: cabang, riwayat chat (max 100 pesan), session key — bertahan saat navigasi maupun reload
- Typing indicator (3 dots) saat menunggu respons AI
- Smart scroll: scroll ke awal bubble bot saat respons panjang
- Input langsung kosong saat kirim, disabled saat loading
- AI backend via N8N webhook (`N8N_URL` env)
- **Rate limiting 2 lapis**: burst (maks. pesan dalam jendela singkat) + kuota harian — keduanya reset otomatis via `RateLimiter`, angka diatur sebagai konstanta agar mudah disesuaikan dengan kuota Gemini
- **Opsi pemulihan saat respons gagal**: tombol restart percakapan, kirim ulang pesan terakhir, dan daftar kontak langsung (kategori OPERASIONAL/PENDAFTARAN — di luar emergency, hotline, sosial media)

#### Homepage RS

Seksi berurutan:
1. Hero Carousel (Banner)
2. Link Layanan Digital
3. Tentang Kami
4. Layanan Unggulan
5. Dokter Kami (3 random)
6. CTA "Siap Melayani" — tombol buat janji + emergency + hotline
7. Partner & Rekanan (Swiper slider)
8. Promo & Penawaran (kondisional)

#### Footer

- **Kiri**: Logo RS, alamat, ikon sosial media (kategori `SOSIAL MEDIA`)
- **Kanan**: Kontak operasional sebagai card dengan link `tel:`/`wa.me`

#### Mobile Bottom Bar

Bar sticky di bawah layar (mobile only) dengan tombol:
- **Emergency** — klik langsung `tel:`
- **Hotline** — klik langsung `tel:`
- **Asisten** — buka/tutup chatbot

---

### Admin Panel

Path admin dikonfigurasi via env: `ADMIN_PATH=manage` (default). Akses di `/{ADMIN_PATH}`.

> **Keamanan**: Jangan gunakan `/admin` sebagai path di production. Set `ADMIN_PATH` ke nilai yang tidak mudah ditebak.

| Modul | Keterangan |
|---|---|
| Rumah Sakit | CRUD data RS, logo, gambar, about, `executive_clinic` (super admin only) |
| Dokter | Manajemen dokter + foto + deskripsi (SoftDelete + Restore) |
| Spesialis | Spesialisasi per RS (SoftDelete + Restore) |
| Poliklinik | Klinik per RS (SoftDelete + Restore) |
| **Jadwal Praktek** | Jadwal rawat jalan per poliklinik — 2 mode editable, kolom `is_executive` |
| **Jadwal Harian** | Override jadwal harian + tracking perubahan status, kolom `is_executive` |
| **Poster Template** | Upload background PNG, logo, shape; zone editor drag-drop interaktif |
| **Generate Poster** | Form pilih template + tanggal → generate PNG 1080×1920 via Browsershot |
| Rawat Inap | Kelas kamar, fasilitas, galeri foto |
| Gedung | Manajemen gedung |
| Banner | Spanduk promosi beranda |
| Promo | Promosi + popup (slug unik per RS) |
| Halaman Statis | CMS halaman statis (kata_kunci, slug unik per RS) |
| Majalah | Upload majalah digital |
| FAQ | Kelola FAQ dengan `sort_order` dan `kata_kunci` |
| Layanan Unggulan | Highlight layanan andalan (drag-drop sort) |
| Fasilitas Pendukung | Fasilitas non-medis (drag-drop sort) |
| Penunjang Medis | Lab, radiologi, farmasi (drag-drop sort) |
| Partner | Mitra & rekanan |
| Kontak | Nomor telepon, WhatsApp, sosial media (3 kategori) |
| Link Layanan | Tautan cepat (drag-drop sort) |
| User | Manajemen akun admin + penugasan RS |

#### Jadwal Praktek — Fitur Khusus

- **Urutan filter**: RS → Mode (Per Hari / Per Dokter)
- **Mode Per Hari**: tab SENIN–MINGGU, tabel baris editable (HTML table + Tom Select)
  - Poliklinik dan Dokter menggunakan Tom Select untuk kemudahan pencarian
  - Checkbox `sesuai_perjanjian` dan `is_executive` sync langsung via `$wire.set()`
  - Simpan: replace-all per hari × scope poliklinik RS
- **Mode Per Dokter**: dropdown dokter di area konten, tabel jadwal lintas semua hari
  - Poliklinik menggunakan Tom Select
  - Simpan: replace-all WHERE `dokter_id` dalam scope RS
- **Layar Penuh**: sembunyikan sidebar Filament
- Rows menggunakan UUID key untuk identifikasi unik

#### Jadwal Harian — Fitur Khusus

- **Tom Select** untuk poliklinik dan dokter di setiap baris
- **Status Layanan**: BUKA / LIBUR (enum `StatusLayanan`)
- **Sumber**: GENERATE (dari cron) / MANUAL (input admin)
- **Tracking Perubahan** via `JadwalHarianPerubahan` (1-to-1 per baris):
  - Record perubahan dibuat HANYA jika sumber GENERATE dan status bukan BUKA
  - Manual tidak membuat record perubahan
- **Cron**: `php artisan jadwal:generate-harian` berjalan otomatis `daily at 00:05`
  - Memuat dari JadwalPraktek sesuai hari
  - Skip jika jadwal harian untuk tanggal + poliklinik sudah ada
- Query tanggal menggunakan `whereDate()` agar kompatibel dengan MySQL dan SQLite
- Rows menggunakan UUID key

#### Sort Order

6 resource mendukung drag-drop reorder: Magazine, LinkLayanan, LayananUnggulan, FasilitasPendukung, PenunjangMedis, Gedung. Kolom `sort_order` tidak muncul di form — dikelola via drag.

#### Kontak — Kategori

| Kategori | Tampil di |
|---|---|
| `SOSIAL MEDIA` | Ikon di footer kiri |
| `OPERASIONAL` | Card di footer kanan + halaman Hubungi Kami |
| `PENDAFTARAN` | Disclaimer halaman jadwal + footer kanan |

#### Slug Uniqueness

Slug bersifat unik **per RS** (composite unique), bukan global:
- `promo`: unique `(slug, rumah_sakit_id)`
- `spesialis`: unique `(slug, rumah_sakit_id)`
- `halaman`: unique `(slug, rumah_sakit_id)`
- `poliklinik`: unique `(slug, rumah_sakit_id)`

---

## Security

| Fitur | Detail |
|---|---|
| Rate Limiting | `public-api`: 20 req/menit, `portal`: 100 req/menit (per IP) |
| Rate Limiting Chatbot AI | 2 lapis via `RateLimiter`: burst (maks. N pesan / X menit) + kuota harian (maks. N pesan / 24 jam), key gabungan IP + session, reset otomatis. Angka diatur sebagai konstanta di `Chatbot\Panel` |
| Security Headers | `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `X-XSS-Protection`, `Permissions-Policy` |
| Content-Security-Policy | Mode Report-Only (default) atau enforced via env `CSP_ENFORCE=true`, daftar sumber eksternal di `SecurityHeaders` middleware |
| Session Encryption | `SESSION_ENCRYPT=true` — payload session dienkripsi di DB |
| Proxy Trust | `TRUSTED_PROXIES` via env (bukan hardcode `'*'`) |
| Admin Path | Dikonfigurasi via `ADMIN_PATH` env, default `manage` |
| Livewire `#[Locked]` | Semua property server-side yang dipakai sebagai filter query dilindungi |
| Input Validation | `/cari-spesialis`: `alpha_dash`, max 100 chars |
| Custom 429 Page | Halaman Too Many Requests dengan countdown |

---

## Performa & SEO

| Fitur | Detail |
|---|---|
| Sitemap XML | Otomatis dari database per cabang RS aktif (`/sitemap.xml` index + `/{rumahsakit}/sitemap.xml`), di-cache 6 jam via `Cache::remember` |
| Lazy Loading Gambar | `loading="lazy"` pada gambar below-the-fold (kartu, list, popup) di seluruh halaman publik — hero/logo above-the-fold tetap eager agar tidak menunda render awal |

---

## Instalasi

### Prasyarat

- PHP 8.2+
- Composer
- Node.js 20+ & npm
- MySQL (rekomendasi) / PostgreSQL / SQLite

### Langkah

```bash
# 1. Clone & masuk direktori
git clone <repo-url>
cd rsu-syifamedika

# 2. Install dependencies
composer install
npm install

# 3. Salin environment
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_DATABASE=rsusm_db
# DB_USERNAME=root
# DB_PASSWORD=...
#
# ADMIN_PATH=manage           # path admin panel (ganti di production)
# SESSION_ENCRYPT=true
# TRUSTED_PROXIES=            # IP load balancer di production
# N8N_URL=https://...         # Webhook N8N untuk chatbot AI

# 5. Migrasi & seed
php artisan migrate:fresh --seed

# 6. Storage link
php artisan storage:link

# 7. Build assets
npm run build
# atau development:
npm run dev

# 8. Jalankan
php artisan serve
```

Admin panel tersedia di `/{ADMIN_PATH}` (default: `/manage`).

Akun yang dibuat otomatis oleh `DatabaseSeeder`:
- **Superadmin** — `test@example.com` / `password`
- **Admin RS** — `ahyaghifari288@gmail.com` / `password`

### Testing

```bash
php artisan test
# 235+ tests passing (Unit + Feature)
```

---

## Konvensi Kode

- Resource Filament extends `BaseResource` / `BaseRumahSakitResource` untuk scoping otomatis
- Super admin cek via `BaseResource::isSuperAdmin()`
- Livewire public property yang dipakai sebagai filter query **wajib** diberi `#[Locked]`
- Livewire `boot()` dipakai (bukan `mount()`) untuk restore RS context lintas request
- Blade portal menggunakan Livewire full-page components
- Tailwind v4: `bg-linear-to-r` (bukan `bg-gradient-to-r`), warna via CSS custom property
- Route di Livewire view: gunakan `route('name', ['rumahsakit' => $rsSlug])`, **bukan** `rumahsakit_route()` (tidak bekerja di AJAX)
- Tom Select via CDN — diintegrasikan dengan Livewire via `wire:ignore` + `$wire.set()` eksplisit
- Checkbox di tabel editable: gunakan `@change="$wire.set(...)"` bukan `wire:model` (menghindari timing conflict dengan Tom Select)
- Query kolom tanggal: selalu gunakan `whereDate()` bukan `where()` (kompatibel MySQL & SQLite)
- `sesuai_perjanjian` dan `is_executive` disimpan dengan `(bool)` cast — **bukan** perbandingan `=== '1'`
- Rows di JadwalPraktekPage dan JadwalHarianPage menggunakan UUID string sebagai array key
- Poster: asset lokal dikonversi ke data URI base64 sebelum dirender di Browsershot (file:// tidak diizinkan)

---

## Environment Variables Penting

```env
# Admin
ADMIN_PATH=manage                  # Path admin panel (ganti di production)

# Security
SESSION_ENCRYPT=true               # Enkripsi payload session
SESSION_SECURE_COOKIE=false        # Set true di production (HTTPS)
TRUSTED_PROXIES=                   # IP load balancer/proxy di production
CSP_ENFORCE=false                  # true = Content-Security-Policy ditegakkan (blocking), false = Report-Only

# Chatbot
N8N_URL=https://...                # Webhook N8N untuk AI chatbot
# Batas pemakaian AI (burst & harian) diatur sebagai konstanta di app/Livewire/Chatbot/Panel.php

# Cache (untuk rate limiter)
CACHE_STORE=database               # Gunakan redis di production untuk performa
```

---

## Status Pengembangan

### Selesai

- [x] Arsitektur multi-tenant (slug URL per RS)
- [x] Admin panel Filament (21 resource + 1 page)
- [x] Portal publik semua halaman
- [x] Refactor: `UnitLayanan` dihapus, `PoliKlinik` langsung ke `RumahSakit`
- [x] Executive Clinic — flag `executive_clinic` di RS, `is_executive` di jadwal, tampilan warna brand `#e8cd84`
- [x] Jadwal portal — groupBy dokter, semua slot jam tampil per baris dokter
- [x] JadwalPraktek — 2 mode (Per Hari & Per Dokter), HTML table + Tom Select
- [x] JadwalPraktek — checkbox `sesuai_perjanjian` dan `is_executive` sync via `$wire.set()`
- [x] JadwalHarian — override harian + tracking perubahan status (JadwalHarianPerubahan)
- [x] JadwalHarian — Tom Select untuk poliklinik & dokter, kolom `is_executive`
- [x] Cron GenerateJadwalHarian (daily 00:05, skip jika sudah ada)
- [x] SoftDelete + Restore: Dokter, Spesialis, PoliKlinik
- [x] Composite slug uniqueness per RS (Promo, Spesialis, Halaman, PoliKlinik)
- [x] Halaman jadwal 2 mode (Per Hari + Per Poli)
- [x] Nama dokter di kartu jadwal dapat diklik → profil dokter
- [x] Badge Sesuai Perjanjian (hijau) di portal — jadwal, detail poli, profil dokter
- [x] Global search Ctrl+K (FULLTEXT MySQL + LIKE fallback)
- [x] Chatbot AI (persistent session, fullscreen mobile, smart scroll)
- [x] Security hardening (rate limit, headers, session encrypt, Livewire Locked, admin path)
- [x] Disclaimer jadwal dengan nomor kontak PENDAFTARAN
- [x] Lightbox galeri foto (GLightbox)
- [x] Sort order drag-drop di 6 resource
- [x] Redirect ke list setelah Create/Edit di semua resource
- [x] FAQ section di homepage RS
- [x] Kontak 3 kategori (SOSIAL MEDIA, OPERASIONAL, PENDAFTARAN)
- [x] Footer dengan ikon sosmed + card kontak
- [x] Mobile bottom bar (Emergency + Hotline + Chatbot)
- [x] SEO meta tags (artesaos/seotools)
- [x] Landing page (hero, Tom Select, no jQuery)
- [x] Test suite (Unit + Feature, 233+ passing)
- [x] PosterTemplate — CRUD upload asset (background, logo, shape), zone editor drag-drop
- [x] GeneratePosterPage — form + download PNG 1080×1920 via Browsershot
- [x] Sitemap XML otomatis per rumah sakit (`/sitemap.xml` index + `/{rumahsakit}/sitemap.xml`, di-cache 6 jam)
- [x] Content-Security-Policy (CSP) header — mode Report-Only dengan toggle `CSP_ENFORCE`
- [x] Chatbot — rate limiting AI 2 lapis (burst per menit + kuota harian, via `RateLimiter`, angka dikonfigurasi sebagai konstanta)
- [x] Chatbot — opsi pemulihan saat respons gagal (restart percakapan, kirim ulang pesan, daftar kontak non-emergency)
- [x] Lazy loading (`loading="lazy"`) pada gambar below-the-fold di seluruh halaman publik

### Dalam Pengerjaan

- [ ] Generate Poster — fix bug field `hariIni`, `jam_mulai`/`waktu_mulai`, dan `libur` antar model
- [ ] Generate Poster — implementasi `previewPoster()` (saat ini stub)

### Dalam Pertimbangan

- [ ] Notifikasi jadwal (email/WhatsApp)
- [ ] Export jadwal ke PDF/Excel
- [ ] Optimasi gambar (resize/kompresi otomatis saat upload — `intervention/image` atau WebP)
