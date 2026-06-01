# RSU Syifa Medika — Sistem Informasi Rumah Sakit

Sistem informasi multi-tenant untuk manajemen dan portal publik rumah sakit. Satu instalasi dapat melayani beberapa rumah sakit sekaligus, masing-masing dengan subdomain/slug tersendiri, konten independen, dan akun admin yang terisolasi.

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Framework | Laravel 12 |
| Admin Panel | Filament 3.x |
| Reactive UI | Livewire 3 |
| Styling | Tailwind CSS v4 |
| Asset Bundler | Vite |
| Database | SQLite (dev) / MySQL / PostgreSQL |
| Icons | Material Symbols (Google Fonts) |
| Animasi | AOS (Animate On Scroll) |
| Searchable Select | Tom Select 2.x (CDN) |
| Slider | Swiper.js |

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
├── Halaman (halaman statis)
├── Magazine
├── Faq
├── Partner
├── Kontak
└── LinkLayanan
```

> **Perubahan arsitektur jadwal:** `JadwalPraktek` kini adalah jadwal rawat jalan per poliklinik (bukan per dokter). `JadwalLayanan` telah dihapus. `JadwalLayananHarian` telah diganti menjadi `JadwalHarian`.

### Role

| Role | Akses |
|---|---|
| `superadmin` | Semua rumah sakit, semua resource |
| `admin` | Hanya rumah sakit milik akun tersebut |

---

## Fitur

### Halaman Landing (`/`)

Halaman pemilihan rumah sakit dengan:
- Welcome section bergradasi dengan background gambar RS
- Search & filter: pilih RS + filter spesialis dokter (Tom Select, AJAX)
- Card setiap RS: gambar, alamat, nomor emergency/hotline (tombol klik)
- Promo aktif semua RS dengan filter per RS
- jQuery **dihapus** — semua interaksi via vanilla JavaScript

### Portal Publik (`/{slug-rs}/...`)

| Route | Halaman |
|---|---|
| `/` | Beranda rumah sakit |
| `/dokter-kami` | Daftar & cari dokter (filter nama + spesialis) |
| `/dokter-kami/{dokter}` | Profil dokter + jadwal praktek |
| `/jadwal-praktek` | Jadwal praktek dokter |
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
| `/info/{slug}` | Halaman statis |
| `/magazine` | Arsip majalah/publikasi digital |
| `/faq` | Pertanyaan yang sering diajukan |

Semua halaman ditenagai **Livewire 3** — tidak ada full page reload.

#### Halaman Jadwal Praktek

Dua mode tampilan:

| Mode | Deskripsi |
|---|---|
| **Per Hari** | Tab SENIN–MINGGU, kartu per poliklinik dengan baris dokter style WhatsApp. Filter poliklinik via Tom Select. |
| **Per Poli** | Pilih poliklinik → tampilkan semua 7 hari (grid horizontal di desktop). Hari kosong tetap ditampilkan. |

#### Homepage RS (Beranda)

Seksi-seksi berurutan:
1. Hero Carousel (welcome slide + banner)
2. Link Layanan Digital
3. Tentang Kami
4. Layanan Unggulan
5. Dokter Kami (3 random)
6. **CTA "Siap Melayani"** — gradient tertiary, tombol buat janji + emergency + hotline
7. Partner & Rekanan (Swiper slider)
8. **Promo & Penawaran** (kondisional)

### Admin Panel (`/admin`)

| Modul | Keterangan |
|---|---|
| Rumah Sakit | CRUD data RS, logo, gambar, about |
| Dokter | Manajemen dokter beserta foto & deskripsi |
| Spesialis | Spesialisasi per rumah sakit |
| Unit Layanan | Kelompok layanan (poli, IGD, dll.) |
| Poliklinik | Klinik per unit layanan (dengan gambar/ikon) |
| **Jadwal Praktek** | Jadwal rawat jalan per poliklinik — 2 mode: Per Hari & Per Dokter |
| **Jadwal Harian** | Override jadwal untuk tanggal tertentu — tabel & AG Grid Excel |
| Rawat Inap | Kelas kamar, fasilitas, galeri foto |
| Gedung | Manajemen gedung rumah sakit |
| Banner | Spanduk promosi beranda |
| Promo | Manajemen promosi + popup |
| Halaman Statis | CMS halaman statis |
| Majalah | Upload majalah digital (cover + PDF) |
| FAQ | Kelola FAQ dengan sort order |
| Layanan Unggulan | Highlight layanan andalan |
| Fasilitas Pendukung | Fasilitas non-medis |
| Penunjang Medis | Lab, radiologi, farmasi, dll. |
| Partner | Mitra & rekanan |
| Kontak | Nomor telepon, WhatsApp, email, alamat |
| Link Layanan | Tautan cepat di halaman RS |
| User | Manajemen akun admin + penugasan RS |

#### Halaman Jadwal Praktek — Fitur Khusus

- **Mode Per Hari**: tab SENIN–MINGGU, tabel baris editable (poliklinik, dokter, jam, perjanjian, catatan), simpan replace-all per hari
- **Mode Per Dokter**: pilih dokter (searchable + preload), tabel jadwal lintas semua hari, kolom hari bisa dipilih bebas
- **Layar Penuh**: sembunyikan sidebar Filament untuk ruang kerja maksimal
- **Gate Unit Layanan**: jika RS punya >1 unit layanan, wajib pilih unit dulu sebelum jadwal tampil

#### Halaman Jadwal Harian — Fitur Khusus

- Navigasi tanggal (kemarin/besok/date picker)
- **Muat dari Jadwal Praktek Mingguan**: prefill baris dari JadwalPraktek hari yang sesuai
- AG Grid Excel (via CDN) untuk edit seperti spreadsheet
- Replace-all per tanggal × scope poliklinik RS

---

## Instalasi

### Prasyarat

- PHP 8.2+
- Composer
- Node.js 20+ & npm
- SQLite / MySQL / PostgreSQL

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

# 4. Konfigurasi database di .env
# DB_CONNECTION=sqlite  ← buat file database/database.sqlite
# atau atur MySQL/PostgreSQL

# 5. Migrasi & seed
php artisan migrate:fresh --seed

# 6. Storage link
php artisan storage:link

# 7. Build assets
npm run build
# atau untuk development:
npm run dev

# 8. Jalankan
php artisan serve
```

Admin panel tersedia di `/admin`. Akun superadmin dibuat otomatis oleh `DatabaseSeeder`:
- **Email**: `test@example.com`
- **Password**: `password`

Atau buat manual via Tinker:

```bash
php artisan tinker

\App\Models\User::create([
    'name'     => 'Super Admin',
    'email'    => 'admin@example.com',
    'password' => bcrypt('password'),
    'role'     => 'superadmin',
]);
```

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
├── Livewire/
│   ├── Dokter/             # Find, Show
│   ├── Pages/              # 12 halaman portal
│   └── RumahSakit/         # Index
├── Models/                 # 22 model Eloquent
└── Http/
    ├── Controllers/        # PortalController
    └── Middleware/         # RumahSakitMiddleware

resources/views/
├── welcome.blade.php       # Landing page pilih RS (non-Livewire)
├── layouts/                # rumah_sakit.blade.php, portal-layout.blade.php
├── components/             # page-hero, rawat-inap, header, footer portal
├── rumah_sakit/            # semua view portal publik
│   ├── pages/              # 12 halaman + partials (_jadwal-praktek-row, _searchable-select)
│   ├── dokter/
│   └── partials/           # mobile-bottom-bar, promo-popup
└── filament/               # view kustom Filament (jadwal grid)
    ├── brand.blade.php     # Logo custom di login Filament
    └── resources/
        ├── jadwal-praktek-resource/pages/
        └── jadwal-harian-resource/pages/
```

---

## Konvensi Kode

- Resource Filament extends `BaseResource` untuk scoping otomatis
- Super admin cek via `BaseResource::isSuperAdmin()`
- Halaman jadwal custom extends parent page class (pattern cache per hari/tanggal)
- Blade portal menggunakan Livewire full-page components; semua properti reaktif
- Tailwind v4: gunakan `bg-linear-to-r` (bukan `bg-gradient-to-r`), warna via CSS custom property
- AG Grid di-load dari CDN untuk halaman Excel
- Tom Select di-load via CDN untuk searchable select (portal + admin)
- Fallback warna unit layanan: `tertiary (#4d51b2)` — semua kartu poliklinik dan aksen jadwal

---

## Status Pengembangan

### Selesai
- [x] Arsitektur multi-tenant
- [x] Admin panel Filament (21 resource)
- [x] Portal publik semua halaman
- [x] **JadwalPraktek per poliklinik** (redesign arsitektur jadwal)
- [x] Jadwal harian override (AG Grid Excel)
- [x] Halaman jadwal 2 mode (Per Hari + Per Dokter)
- [x] Filter poliklinik searchable (Tom Select)
- [x] Promo dengan popup
- [x] Halaman statis CMS
- [x] Majalah digital
- [x] FAQ
- [x] SEO meta tags (artesaos/seotools)
- [x] Landing page redesign (hero, Tom Select, no jQuery)
- [x] Homepage RS: CTA + Promo section
- [x] Login Filament custom (2 logo)
- [x] Portal header dengan logo

### Dalam Pertimbangan
- [ ] Sitemap otomatis per rumah sakit
- [ ] Notifikasi jadwal (email/WhatsApp)
- [ ] Export jadwal ke PDF/Excel
- [ ] Dark mode portal publik
- [ ] JadwalHarian rename & cleanup lanjutan
