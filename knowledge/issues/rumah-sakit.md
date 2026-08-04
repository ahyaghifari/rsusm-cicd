# Planning Data Rumah Sakit

Berikut adalah instruksi teknis untuk mengimplementasikan modul data Rumah Sakit menggunakan Laravel dan Filament. Instruksi ini siap dikerjakan oleh programmer atau AI Model.

## 1. Migrasi Database
Buatkan file migrasi untuk tabel `rumah_sakit` dengan spesifikasi kolom sebagai berikut:
- `id` (integer, auto increment, primary key)
- `nama` (varchar 100, unique)
- `slug` (varchar 100, unique)
- `lokasi` (varchar 100)
- `alamat` (text)
- `no_emergency` (varchar 20, nullable/kosong jika '-')
- `no_hotline` (varchar 20, nullable/kosong jika '-')
- `gambar` (varchar 255, nullable)
- `logo` (varchar 255, nullable)
- `aktif` (boolean, default true)
- `timestamps` (created_at, updated_at)

## 2. Model `RumahSakit`
Buatkan model Eloquent dengan nama `RumahSakit`.
- **Fillable**: Semua kolom di atas kecuali `id`.
- **Route Model Binding**: Atur model agar secara otomatis menggunakan `slug` (bukan `id`) dengan meng-override method `getRouteKeyName()`.

## 3. Filament Resource
Generate resource Filament untuk model `RumahSakit`.
- **Command**: `php artisan make:filament-resource RumahSakitResource --generate`
- **Konfigurasi Form**: 
  - Kolom `gambar` dan `logo` wajib diatur sebagai field yang menerima upload gambar (misalnya menggunakan `FileUpload::make('gambar')->image()`).

## 4. Seeder Database
Buatkan seeder dengan nama `RumahSakitSeeder` dengan 2 (dua) baris data awal:

### Data 1
- **Nama**: RSU Syifa Medika Banjarbaru
- **Slug**: banjarbaru
- **Lokasi**: Banjarbaru
- **Alamat**: Jl. RO Ulin No.93, Loktabat Selatan, Kec. Banjarbaru Selatan, Kota Banjar Baru, Kalimantan Selatan 70712
- **No Emergency**: 0811 504 2424
- **No Hotline**: 0511 5910 889
- **Gambar**: (kosongkan terlebih dahulu, nanti akan menyesuaikan folder `public/assets/gambar`)
- **Logo**: (kosongkan terlebih dahulu, nanti akan menyesuaikan folder `public/assets/logo`)

### Data 2
- **Nama**: RSU Syifa Medika Barabai
- **Slug**: barabai
- **Lokasi**: Barabai
- **Alamat**: Jl Lingkar Walangsi Kapar KM. 5.2, Barabai, Kalimantan Selatan, Indonesia
- **No Emergency**: -
- **No Hotline**: -
- **Gambar**: (kosongkan terlebih dahulu)
- **Logo**: (kosongkan terlebih dahulu)
