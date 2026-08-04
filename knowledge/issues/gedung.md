# Planning Data Gedung

Berikut adalah instruksi teknis untuk mengimplementasikan modul data Gedung menggunakan Laravel dan Filament. Instruksi ini siap dikerjakan oleh programmer atau AI Model yang murah atau gratis.

## 1. Database & Migrasi
Buatkan file migrasi untuk tabel `gedung` dengan spesifikasi kolom sebagai berikut:
- `id`: integer, auto increment, primary key
- `rumah_sakit_id`: integer, foreign key ke tabel `rumah_sakit` (menggunakan `constrained('rumah_sakit')->cascadeOnDelete()`)
- `nama`: varchar(255)
- `alias`: varchar(255), nullable
- `timestamps`: (`created_at`, `updated_at`)

## 2. Eloquent Model
Buatkan model Eloquent dengan nama `Gedung`.
- **Allowed Fields**: Izinkan semua kolom untuk mass assignment kecuali kolom `id` (`$guarded = ['id']`).
- **Relasi**: Definisikan relasi `belongsTo` ke model `RumahSakit` menggunakan foreign key `rumah_sakit_id`.

## 3. Filament Resource
Generate resource Filament untuk model `Gedung` dengan menggunakan flag `--simple`.
- **Command**: `php artisan make:filament-resource Gedung --simple`
- **Konfigurasi Resource**:
  - Atur agar form dan tabel menampilkan dropdown/kolom `rumah_sakit_id` berelasi dengan model `RumahSakit` (menampilkan nama rumah sakit).
  - Tampilkan input text untuk `nama` dan `alias`.

## 4. Database Seeder
Buatkan seeder dengan nama `GedungSeeder` dengan 3 (tiga) data awal sebagai berikut:

### Data 1
- **rumah_sakit_id**: 1
- **nama**: Gedung Shofa
- **alias**: Gedung A

### Data 2
- **rumah_sakit_id**: 1
- **nama**: Gedung Marwah
- **alias**: Gedung B

### Data 3
- **rumah_sakit_id**: 1
- **nama**: Gedung Muzdalifah
- **alias**: Gedung C
