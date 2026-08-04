# Planning Data Link Layanan

Berikut adalah instruksi teknis untuk mengimplementasikan modul data Link Layanan menggunakan Laravel dan Filament. Instruksi ini siap dikerjakan oleh programmer atau AI Model.

## 1. Migrasi Database

Buatkan file migrasi untuk tabel `link_layanan` dengan spesifikasi kolom sebagai berikut:

- **id** (integer, auto increment, primary key)
- **rumah_sakit_id** (integer, foreign key ke tabel `rumah_sakit`)
- **label** (varchar 255)
- **value** (varchar 255)
- **gambar** (varchar 255, nullable)
- **deskripsi_singkat** (text, nullable)
- **link** (longtext)
- **aktif** (boolean, default true)
- **timestamps** (created_at, updated_at)

> **Catatan Hubungan Data:** Hubungkan foreign key `rumah_sakit_id` ke tabel `rumah_sakit` (bukan `rawat_inap`) agar relasi database benar.

## 2. Model `LinkLayanan`

Buatkan model Eloquent dengan nama `LinkLayanan`.

- **Fillable** : Semua kolom di atas kecuali `id` (`$guarded = ['id']`).
- **Relasi** : Definisikan relasi `belongsTo` ke model `RumahSakit`.

## 3. Filament Resource

Generate resource Filament untuk model `LinkLayanan` menggunakan flag `--simple`.

- **Command** : `php artisan make:filament-resource LinkLayananResource --simple`
- **Konfigurasi Form** :
    - **rumah_sakit_id** : Menggunakan field `Select` yang berelasi ke model `RumahSakit` (menampilkan nama rumah sakit).
    - **label** : Text input (max length 255).
    - **value** : Text input (max length 255).
    - **gambar** : Wajib diatur sebagai field yang menerima upload gambar menggunakan `FileUpload::make('gambar')->image()` agar user bisa mengunggah file gambar.
    - **deskripsi_singkat** : Textarea untuk teks singkat deskripsi layanan.
    - **link** : Textarea atau Text input panjang (longtext).
    - **aktif** : Toggle atau Checkbox dengan default value `true`.
- **Konfigurasi Tabel** :
    - Tampilkan kolom-kolom penting seperti nama rumah sakit (relasi), label, value, dan status aktif.
    - Tambahkan filter berdasarkan `rumah_sakit_id` dan `aktif`.

## 4. Database Seeder

Buatkan seeder dengan nama `LinkLayananSeeder`.

- Semua data memiliki `rumah_sakit_id = 1` (RSU Syifa Medika Banjarbaru).
- Kolom `gambar` diisi `null` untuk sementara.
- Kolom `aktif` diisi `true` untuk semua data.
- Data diambil dari bagian bawah hero halaman https://rsusyifamedika.co.id/ yaitu 3 item layanan cepat.

### Data Seeder

| # | Label | Value | Deskripsi Singkat | Link |
|---|-------|-------|-------------------|------|
| 1 | Ketersediaan Ruang Rawat | Ketersediaan Ruang Rawat | Cek ketersediaan kamar rawat inap secara realtime | https://simgos.rsusyifamedika.co.id/apps/BedOnline/ |
| 2 | Jadwal Praktek Dokter | Jadwal Praktek Dokter | Lihat jadwal praktik seluruh dokter spesialis | https://simgos.rsusyifamedika.co.id/apps/JadwalOnline/ |
| 3 | Pantauan Antrian | Pantauan Antrian | Pantau antrian poliklinik secara langsung | https://simgos.rsusyifamedika.co.id/apps/AntrianOnline/ |

> **Catatan:** Verifikasi URL di atas dengan membuka langsung https://rsusyifamedika.co.id/ dan cek link pada masing-masing tombol/card di bawah hero. Sesuaikan jika ada perbedaan.

## 5. Registrasi Seeder

Tambahkan `LinkLayananSeeder` ke dalam `DatabaseSeeder.php` setelah seeder yang sudah ada:

```php
$this->call([
    // seeder lainnya...
    LinkLayananSeeder::class,
]);
```
