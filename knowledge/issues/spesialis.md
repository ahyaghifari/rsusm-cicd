# Spesialis Feature Implementation Plan

Silakan implementasikan fitur data Spesialis pada proyek Laravel ini dengan detail langkah-langkah berikut:

## 1. Database Migration
Buat file migrasi untuk tabel `spesialis` dengan definisi kolom sebagai berikut:
- `id` (integer, auto increment, primary key)
- `nama` (varchar 100, unique)
- `slug` (varchar 100, unique)
- `logo` (varchar 255, nullable)
- `aktif` (boolean, default true)
- `created_at` dan `updated_at` (timestamp standar)

## 2. Eloquent Model
Buatkan model dengan nama `Spesialis` (berada di `app/Models/Spesialis.php`).
- Atur agar semua kolom diizinkan untuk diisi (mass assignable) kecuali kolom `id` (bisa menggunakan `$guarded = ['id'];` atau sebutkan secara eksplisit pada `$fillable`).
- Atur **Route Model Binding** agar pencarian record default menggunakan kolom `slug` alih-alih `id`. Tambahkan method berikut:
  ```php
  public function getRouteKeyName()
  {
      return 'slug';
  }
  ```

## 3. Filament Resource
Buatkan resource Filament untuk model `Spesialis` dengan nama `SpesialisResource`.
- Gunakan flag `--simple` saat membuat resource melalui artisan command (contoh: `php artisan make:filament-resource Spesialis --simple`).
- Pada konfigurasi form di `SpesialisResource`, pastikan kolom `logo` menggunakan komponen FileUpload yang dikonfigurasi untuk menerima unggahan berformat gambar (misal: `FileUpload::make('logo')->image()`).

## 4. Database Seeder
Buatkan seeder dengan nama `SpesialisSeeder`.
- Gunakan helper `Str::slug($nama)` agar nilai untuk kolom `slug` dapat digenerate secara otomatis dari masing-masing `nama`.
- Masukkan data list spesialis berikut ke dalam tabel:
  - Spesialis Anak
  - Spesialis Penyakit Dalam
  - Spesialis Bedah
  - Spesialis Orthopaedi & Traumatologi
  - Spesialis Kebidanan & Kandungan
  - Spesialis Saraf
  - Spesialis Paru
  - Spesialis THT
  - Spesialis Jantung
  - Spesialis Kulit & Kelamin
  - Spesialis Urologi
  - Spesialis Mata
  - Spesialis Kejiwaan (Psikiater)
  - Spesialis Bedah Saraf
  - Spesialis Rehabilitasi Medik / Fisik & Rehabilitasi
  - Spesialis Gizi Klinik
  - Layanan Konsultasi Psikologi
  - Dokter Gigi Umum
  - Spesialis Dokter Gigi Anak
  - Spesialis Konservasi Gigi
  - Spesialis Gigi Prostodonsi
  - Spesialis Gigi Ortodonsi
  - Spesialis Gigi Bedah Mulut & Maksilofasial
