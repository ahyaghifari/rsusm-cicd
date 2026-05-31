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
| Icons | FontAwesome (via blade-fontawesome) |
| Animasi | AOS (Animate On Scroll) |

---

## Arsitektur

Project ini menggunakan arsitektur **multi-tenant berbasis kolom** (`rumah_sakit_id`). Semua entitas konten dimiliki oleh satu `RumahSakit`. Akses data dibatasi di level resource Filament dan middleware route.

```
RumahSakit
├── User (admin rumah sakit, terikat ke 1 RS)
├── Dokter → Spesialis
├── UnitLayanan → PoliKlinik
│   └── JadwalLayanan (mingguan)
│       └── JadwalLayananHarian (harian)
├── JadwalPraktek (per dokter)
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

### Role

| Role | Akses |
|---|---|
| `superadmin` | Semua rumah sakit, semua resource |
| `admin` | Hanya rumah sakit milik akun tersebut |

---

## Fitur

### Portal Publik (`/{slug-rs}/...`)

| Route | Halaman |
|---|---|
| `/` | Beranda rumah sakit |
| `/dokter-kami` | Daftar & cari dokter |
| `/dokter-kami/{dokter}` | Profil dokter |
| `/jadwal-praktek` | Jadwal praktek dokter |
| `/rawat-jalan` | Daftar poliklinik & jadwal layanan |
| `/rawat-jalan/{poliklinik}` | Detail poliklinik |
| `/rawat-inap` | Informasi kelas rawat inap |
| `/unggulan` | Layanan unggulan |
| `/fasilitas-pendukung` | Fasilitas pendukung |
| `/penunjang-medis` | Penunjang medis |
| `/partner-kami` | Mitra rumah sakit |
| `/hubungi-kami` | Kontak & lokasi |
| `/promo` | Daftar promo aktif |
| `/promo/{slug}` | Detail promo |
| `/info/{slug}` | Halaman statis (tentang, kebijakan, dll.) |
| `/magazine` | Arsip majalah/publikasi digital |
| `/faq` | Pertanyaan yang sering diajukan |

Semua halaman ditenagai **Livewire 3** — tidak ada full page reload.

### Admin Panel (`/admin`)

| Modul | Keterangan |
|---|---|
| Rumah Sakit | CRUD data rumah sakit, logo, gambar, about |
| Dokter | Manajemen dokter beserta foto & deskripsi |
| Spesialis | Spesialisasi per rumah sakit |
| Unit Layanan | Kelompok layanan (poli, IGD, dll.) |
| Poliklinik | Klinik per unit layanan |
| Jadwal Layanan | Jadwal mingguan per poliklinik — tampilan tabel & **AG Grid Excel** |
| Jadwal Layanan Harian | Override jadwal untuk tanggal tertentu — tampilan tabel & **AG Grid Excel** |
| Jadwal Praktek | Jadwal praktek dokter |
| Rawat Inap | Kelas kamar, fasilitas, galeri foto |
| Gedung | Manajemen gedung rumah sakit |
| Banner | Spanduk promosi beranda |
| Promo | Manajemen promosi + popup |
| Halaman Statis | CMS halaman statis (slug unik per RS) |
| Majalah | Upload majalah digital (cover + PDF) |
| FAQ | Kelola FAQ dengan sort order |
| Layanan Unggulan | Highlight layanan andalan |
| Fasilitas Pendukung | Fasilitas non-medis |
| Penunjang Medis | Lab, radiologi, farmasi, dll. |
| Partner | Mitra & rekanan |
| Kontak | Nomor telepon, WhatsApp, email, alamat |
| Link Layanan | Tautan cepat di halaman RS |
| User | Manajemen akun admin + penugasan RS |

#### Halaman Khusus Jadwal (AG Grid)

Halaman **Jadwal Layanan · Excel** dan **Jadwal Harian · Excel** menggunakan [AG Grid Community](https://www.ag-grid.com/) untuk pengalaman edit seperti spreadsheet:
- Klik sel untuk edit langsung
- Dropdown poliklinik, dokter, status layanan
- Time picker untuk jam mulai/selesai
- Highlight kuning pada kolom wajib yang kosong
- Tambah/hapus baris tanpa reload
- Simpan semua sekaligus (replace-all per hari/tanggal)

### Chatbot

Widget chatbot mengambang terintegrasi di portal publik. Interaktif berbasis Livewire dengan panel slide-in.

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
# DB_CONNECTION=sqlite (default, buat file database/database.sqlite)
# atau atur MySQL/PostgreSQL

# 5. Migrasi & seed
php artisan migrate
# php artisan db:seed   ← jika tersedia seeder

# 6. Storage link
php artisan storage:link

# 7. Build assets
npm run build
# atau untuk development:
npm run dev

# 8. Jalankan
php artisan serve
```

Admin panel tersedia di `/admin`. Buat akun superadmin via Tinker:

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
├── Enums/              # Hari, StatusLayanan
├── Filament/
│   └── Resources/      # 24+ resource Filament
│       ├── JadwalLayananResource/
│       │   └── Pages/  # JadwalLayananPage, JadwalLayananExcel
│       └── JadwalLayananHarianResource/
│           └── Pages/  # JadwalLayananHarianPage, JadwalLayananHarianExcel
├── Livewire/
│   ├── Chatbot/        # Floating, Panel
│   ├── Dokter/         # Find, Show
│   ├── Pages/          # 13 halaman portal
│   └── RumahSakit/     # Index
├── Models/             # 24 model Eloquent
└── Http/
    ├── Controllers/    # PortalController
    └── Middleware/     # RumahSakitMiddleware

resources/views/
├── layouts/            # rumah_sakit.blade.php, portal-layout.blade.php
├── components/         # page-hero, rawat-inap, header, footer portal
├── rumah_sakit/        # semua view portal publik
│   ├── pages/          # 14 halaman
│   ├── dokter/
│   ├── chatbot/
│   └── partials/       # mobile-bottom-bar, promo-popup
└── filament/           # view kustom Filament (jadwal grid)
```

---

## Konvensi Kode

- Resource Filament extends `BaseResource` atau `BaseRumahSakitResource` untuk scoping otomatis
- Super admin cek via `BaseResource::isSuperAdmin()`
- Halaman jadwal kustom extends parent page class, override hook navigasi untuk sync AG Grid
- Blade portal menggunakan Livewire full-page components; semua properti reaktif
- Tailwind v4: gunakan `bg-linear-to-r` (bukan `bg-gradient-to-r`), `scheme-dark` (bukan `[color-scheme:dark]`)
- AG Grid di-load dari CDN; integrasi via `window.namaFungsi = function(){}` (bukan `@script`) karena timing Alpine

---

## Status Pengembangan

### Selesai
- [x] Arsitektur multi-tenant
- [x] Admin panel Filament lengkap (24+ resource)
- [x] Portal publik semua halaman
- [x] Jadwal layanan mingguan (tabel + AG Grid Excel)
- [x] Jadwal layanan harian (tabel + AG Grid Excel)
- [x] Jadwal praktek dokter
- [x] Chatbot widget
- [x] Promo dengan popup
- [x] Halaman statis CMS
- [x] Majalah digital
- [x] FAQ

### Dalam Pertimbangan
- [ ] SEO meta tags (`artesaos/seotools`)
- [ ] Sitemap otomatis per rumah sakit
- [ ] Notifikasi jadwal (email/WhatsApp)
- [ ] Export jadwal ke PDF/Excel
- [ ] Dark mode portal publik
