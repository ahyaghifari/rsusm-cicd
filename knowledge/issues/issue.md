# Issue Tracker - RSU Syifa Medika

Berikut adalah daftar tugas/issue dan status implementasinya pada aplikasi RSU Syifa Medika:

- [x] **Setup Data Rumah Sakit** (planning di `issues/rumah-sakit.md`)
  - [x] Buat tabel database `rumah_sakit` lewat migrasi
  - [x] Buat model Eloquent `RumahSakit` dengan properti `$fillable`, cast `aktif`, dan route model binding berbasis `slug`
  - [x] Buat Filament Resource `RumahSakitResource` dengan generator auto-schema (`--generate`)
  - [x] Konfigurasi form upload file gambar pada resource untuk field `gambar` dan `logo`
  - [x] Buat database seeder `RumahSakitSeeder` dengan data awal Banjarbaru dan Barabai
  - [x] Daftarkan seeder di `DatabaseSeeder` dan jalankan seeding

- [x] **Tentang RS — Section Homepage "Kenapa Memilih Kami"** (planning di `issues/tentang-rs.md`)
  - [x] Migrasi: tambah kolom `tentang_kami` (text) dan `gambar_tentang` (varchar 255) ke tabel `rumah_sakit`
  - [x] Update model `RumahSakit` — tambah ke `$fillable`
  - [x] Update `RumahSakitResource` — tambah section "Tentang RS" dengan field `gambar_tentang` dan `tentang_kami`
  - [x] Update `index.blade.php` — ganti hardcode dengan data dari `$rs->tentang_kami` dan `$rs->gambar_tentang`, section disembunyikan jika null
  - [x] Update `RumahSakitSeeder` — tambah data `tentang_kami` untuk Banjarbaru dan Barabai
  - [x] Update data RS existing di database via tinker

- [x] **Sistem Promo — Popup, Nav, List & Detail** (planning di `issues/promo-fitur.md`)
  - [x] Tambah route `promo` dan `promo/{promo}` di `web.php`
  - [x] Update `RumahSakitMiddleware` — share `$promo_popup` ke semua view
  - [x] Tambah popup Alpine.js + localStorage 24 jam di `layout.blade.php`
  - [x] Pastikan `@stack('scripts')` ada di `layout.blade.php`
  - [x] Tambah tombol floating di `index.blade.php` (homepage)
  - [x] Tambah link pill "Promo" di `nav.blade.php` (selalu tampil, tidak ikut collapse)
  - [x] Buat `app/Livewire/Pages/Promo.php`
  - [x] Buat `resources/views/rumah_sakit/pages/promo.blade.php` (magazine layout)
  - [x] Buat `app/Livewire/Pages/PromoDetail.php`
  - [x] Buat `resources/views/rumah_sakit/pages/promo-detail.blade.php`

- [x] **Halaman Statis Per RS (Mini-CMS)** (planning di `issues/halaman-statis-plan.md`)
  - [x] Migrasi: buat tabel `halaman` (`rumah_sakit_id`, `slug`, `judul`, `konten` longText, `aktif`)
  - [x] Buat model `Halaman` dengan cast `aktif` boolean, relasi `belongsTo` RumahSakit
  - [x] Buat Filament `HalamanResource` (--generate), extends BaseRumahSakitResource, RichEditor untuk konten
  - [x] Tambah route `/{rumahsakit}/info/{slug}` di `web.php`
  - [x] Buat Livewire `Pages\HalamanStatis`
  - [x] Buat view `rumah_sakit/pages/halaman-statis.blade.php` (render RichEditor HTML)
  - [x] Share `$halaman_nav` di `RumahSakitMiddleware`, dropdown Tentang Kami jadi dinamis
  - [x] Buat seeder `HalamanSeeder` dengan data contoh (profil-perusahaan, visi-misi)
