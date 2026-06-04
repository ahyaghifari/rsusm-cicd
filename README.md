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
RumahSakit
├── User (admin rumah sakit, terikat ke 1 RS)
├── Dokter → Spesialis
├── UnitLayanan → PoliKlinik
│   └── JadwalPraktek (per poliklinik, per hari, opsional per dokter)
│       → table: jadwal_praktek
├── JadwalHarian (override harian per tanggal)
│   → table: jadwal_harian
├── RawatInap → Gedung
│   ├── GambarRawatInap
│   └── FasilitasRawatInap
├── LayananUnggulan
├── FasilitasPendukung
├── PenunjangMedis
├── Banner
├── Promo
├── Halaman (halaman statis, field: kata_kunci)
├── Magazine
├── Faq (field: kata_kunci)
├── Partner
├── Kontak (kategori: SOSIAL MEDIA | OPERASIONAL | PENDAFTARAN)
└── LinkLayanan
```

> **Arsitektur jadwal:** `JadwalPraktek` adalah jadwal rawat jalan per **poliklinik** (bukan per dokter). `JadwalLayanan` telah dihapus. `JadwalLayananHarian` diganti menjadi `JadwalHarian`.

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
| `/rawat-jalan` | Daftar poliklinik per unit layanan |
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

Setiap jadwal menampilkan badge **Sesuai Perjanjian** jika berlaku. Di bawah jadwal terdapat **disclaimer** dengan nomor kontak PENDAFTARAN yang bisa diklik langsung (`tel:` / WhatsApp).

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
- State persisten: cabang, riwayat chat (max 100 pesan), session key — bertahan saat navigasi halaman maupun full reload
- Session key unik di-generate saat pesan pertama, tidak berubah selama sesi
- Typing indicator (3 dots) saat menunggu respons AI
- Smart scroll: scroll ke awal bubble bot saat respons panjang
- Input langsung kosong saat kirim, disabled saat loading
- AI backend via N8N webhook (`N8N_URL` env)

#### Homepage RS

Seksi berurutan:
1. Hero Carousel
2. Link Layanan Digital
3. Tentang Kami
4. Layanan Unggulan
5. Dokter Kami (3 random)
6. CTA "Siap Melayani" — tombol buat janji + emergency + hotline
7. Partner & Rekanan (Swiper slider)
8. Promo & Penawaran (kondisional)

#### Footer

- **Kiri**: Logo RS, alamat, ikon sosial media (kategori `SOSIAL MEDIA`)
- **Kanan**: Kontak operasional sebagai card dengan link `tel:`/`wa.me` (kategori `OPERASIONAL` + `PENDAFTARAN`)

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
| Rumah Sakit | CRUD data RS, logo, gambar, about |
| Dokter | Manajemen dokter dengan foto & deskripsi |
| Spesialis | Spesialisasi per RS |
| Unit Layanan | Kelompok layanan (poli, IGD, dll.) |
| Poliklinik | Klinik per unit layanan (gambar/ikon) |
| **Jadwal Praktek** | Jadwal rawat jalan per poliklinik — 2 mode |
| **Jadwal Harian** | Override jadwal harian — tabel & AG Grid Excel |
| Rawat Inap | Kelas kamar, fasilitas, galeri foto |
| Gedung | Manajemen gedung |
| Banner | Spanduk promosi beranda |
| Promo | Promosi + popup |
| Halaman Statis | CMS halaman statis (dengan `kata_kunci`) |
| Majalah | Upload majalah digital |
| FAQ | Kelola FAQ dengan `sort_order` dan `kata_kunci` |
| Layanan Unggulan | Highlight layanan andalan |
| Fasilitas Pendukung | Fasilitas non-medis |
| Penunjang Medis | Lab, radiologi, farmasi |
| Partner | Mitra & rekanan |
| Kontak | Nomor telepon, WhatsApp, sosial media (3 kategori) |
| Link Layanan | Tautan cepat |
| User | Manajemen akun admin + penugasan RS |

#### Jadwal Praktek — Fitur Khusus

- **Urutan filter**: RS → Mode (Per Hari / Per Dokter) → Unit Layanan
- **Mode Per Hari**: tab SENIN–MINGGU, tabel baris editable, simpan replace-all per hari
- **Mode Per Dokter**: dropdown dokter di area konten, tabel jadwal lintas semua hari
- **Layar Penuh**: sembunyikan sidebar Filament
- **Gate Unit Layanan**: wajib pilih unit jika RS punya >1 unit
- **`sesuai_perjanjian`**: disimpan dengan cast boolean yang benar

#### Kontak — Kategori

| Kategori | Tampil di |
|---|---|
| `SOSIAL MEDIA` | Ikon di footer kiri |
| `OPERASIONAL` | Card di footer kanan (Hubungi Kami) |
| `PENDAFTARAN` | Disclaimer halaman jadwal + footer kanan |

---

## Security

Fitur keamanan yang sudah diimplementasi:

| Fitur | Detail |
|---|---|
| Rate Limiting | `public-api`: 20 req/menit, `portal`: 100 req/menit (per IP) |
| Security Headers | `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `X-XSS-Protection`, `Permissions-Policy` |
| Session Encryption | `SESSION_ENCRYPT=true` — payload session dienkripsi di DB |
| Proxy Trust | `TRUSTED_PROXIES` via env (bukan hardcode `'*'`) |
| Admin Path | Dikonfigurasi via `ADMIN_PATH` env, default `manage` |
| Livewire `#[Locked]` | Semua property server-side yang dipakai sebagai filter query dilindungi |
| Input Validation | `/cari-spesialis`: `alpha_dash`, max 100 chars |
| Custom 429 Page | Halaman Too Many Requests dengan countdown |

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
# ADMIN_PATH=manage          # path admin panel (ganti di production)
# SESSION_ENCRYPT=true
# TRUSTED_PROXIES=           # IP load balancer di production
# N8N_URL=https://...        # Webhook N8N untuk chatbot AI

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

---

## Struktur Direktori (Ringkasan)

```
app/
├── Enums/                  # Hari, StatusLayanan
├── Filament/
│   └── Resources/          # 21 resource Filament
│       ├── JadwalPraktekResource/
│       │   └── Pages/      # JadwalPraktekPage (2 mode), JadwalPraktekExcel
│       └── JadwalHarianResource/
│           └── Pages/      # JadwalHarianPage, JadwalHarianExcel
├── Http/
│   ├── Controllers/        # PortalController
│   └── Middleware/         # RumahSakitMiddleware, SecurityHeaders
├── Livewire/
│   ├── Chatbot/            # Panel (persistent state), Floating (Alpine store)
│   ├── Dokter/             # Find (A-Z order), Show
│   ├── Pages/              # 12 halaman portal
│   ├── GlobalSearch.php    # Spotlight search (FULLTEXT + LIKE fallback)
│   └── RumahSakit/         # Index
├── Models/                 # 22 model Eloquent
└── Providers/
    └── AppServiceProvider  # Rate limiters terdaftar di sini

resources/views/
├── errors/429.blade.php              # Halaman Too Many Requests
├── livewire/global-search.blade.php  # Spotlight search modal
├── welcome.blade.php                 # Landing page (non-Livewire)
├── layouts/                          # rumah_sakit.blade.php, portal-layout.blade.php
├── rumah_sakit/
│   ├── chatbot/
│   │   ├── floating.blade.php        # Alpine store + FAB desktop
│   │   └── panel.blade.php           # Chat UI (fullscreen mobile)
│   ├── pages/
│   │   ├── _jadwal-disclaimer.blade.php  # Disclaimer kontak PENDAFTARAN
│   │   └── ...
│   └── partials/
│       ├── mobile-bottom-bar.blade.php   # Emergency + Hotline + Chatbot
│       └── ...
└── filament/
    ├── brand.blade.php                   # Logo custom login Filament
    └── resources/
        ├── jadwal-praktek-resource/pages/
        └── jadwal-harian-resource/pages/

tests/
├── Feature/
│   ├── Auth/FilamentRbacTest.php
│   ├── Livewire/
│   │   ├── ChatbotPanelTest.php     # Session persistence, sendMessage, sessionKey
│   │   └── GlobalSearchTest.php    # Search per kategori, min 2 char, scoping
│   ├── Resources/                  # DokterResource, RumahSakitResource, UserResource
│   ├── Security/SecurityHeadersTest.php
│   └── AppServiceProviderTest.php  # Rate limiters, admin path
└── Unit/
    └── Models/                     # Faq, Halaman, JadwalPraktek, Kontak, dll.
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
- AG Grid di-load dari CDN untuk halaman Excel
- Tom Select via CDN untuk searchable select (portal + admin)
- Fallback warna unit layanan: `tertiary (#4d51b2)`
- `sesuai_perjanjian` disimpan dengan `(bool)` cast — **bukan** perbandingan `=== '1'`

---

## Environment Variables Penting

```env
# Admin
ADMIN_PATH=manage                  # Path admin panel (ganti di production)

# Security
SESSION_ENCRYPT=true               # Enkripsi payload session
SESSION_SECURE_COOKIE=false        # Set true di production (HTTPS)
TRUSTED_PROXIES=                   # IP load balancer/proxy di production

# Chatbot
N8N_URL=https://...                # Webhook N8N untuk AI chatbot

# Cache (untuk rate limiter)
CACHE_STORE=database               # Gunakan redis di production untuk performa
```

---

## Status Pengembangan

### Selesai
- [x] Arsitektur multi-tenant
- [x] Admin panel Filament (21 resource)
- [x] Portal publik semua halaman
- [x] JadwalPraktek per poliklinik (redesign arsitektur jadwal)
- [x] Jadwal harian override (AG Grid Excel)
- [x] Halaman jadwal 2 mode (Per Hari + Per Poli)
- [x] Filter poliklinik searchable (Tom Select)
- [x] Global search (FULLTEXT MySQL + LIKE fallback)
- [x] Chatbot AI (persistent session, fullscreen mobile, smart scroll, reset percakapan)
- [x] Security hardening (rate limit, headers, session encrypt, Livewire Locked, admin path)
- [x] Disclaimer jadwal dengan nomor kontak PENDAFTARAN
- [x] Lightbox galeri foto (GLightbox — rawat inap, fasilitas, layanan unggulan, penunjang medis)
- [x] Sort order drag-drop di 6 resource (Magazine, LinkLayanan, LayananUnggulan, FasilitasPendukung, PenunjangMedis, Gedung)
- [x] Redirect ke list setelah Create/Edit di semua resource
- [x] FAQ section di homepage RS
- [x] Redesign card rawat inap & section layanan unggulan
- [x] Test suite (Unit + Feature, 185+ passing)
- [x] Kontak 3 kategori (SOSIAL MEDIA, OPERASIONAL, PENDAFTARAN)
- [x] Kata kunci pencarian di FAQ dan Halaman Statis
- [x] Footer redesign (ikon sosmed + card kontak)
- [x] Mobile bottom bar (Emergency + Hotline + Chatbot)
- [x] Promo dengan popup
- [x] Halaman statis CMS
- [x] Majalah digital
- [x] SEO meta tags (artesaos/seotools)
- [x] Landing page redesign (hero, Tom Select, no jQuery)
- [x] Homepage RS: CTA + Promo section
- [x] Login Filament custom (2 logo)
- [x] Test suite (Unit + Feature, 166+ passing)

### Dalam Pertimbangan
- [ ] Sitemap otomatis per rumah sakit
- [ ] Notifikasi jadwal (email/WhatsApp)
- [ ] Export jadwal ke PDF/Excel
- [ ] Dark mode portal publik
- [ ] Content-Security-Policy (CSP) header
