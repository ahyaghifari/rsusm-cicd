# Planning Data Kontak

Berikut adalah instruksi teknis untuk mengimplementasikan modul data Kontak menggunakan Laravel dan Filament. Instruksi ini siap dikerjakan oleh programmer atau AI Model.

## 1. Migrasi Database

Buatkan file migrasi untuk tabel `kontak` dengan spesifikasi kolom sebagai berikut:

- **id** (integer, auto increment, primary key)
- **rumah_sakit_id** (integer, foreign key ke tabel `rumah_sakit`)
- **label** (varchar 255)
- **value** (varchar 255)
- **gambar** (varchar 255, nullable)
- **logo** (longtext, nullable) — untuk menyimpan konten SVG atau path file gambar logo
- **link** (longtext, nullable)
- **kategori** (enum: `SOSIAL MEDIA`, `OPERASIONAL`)
- **aktif** (boolean, default true)
- **timestamps** (created_at, updated_at)

> **Catatan Hubungan Data:** Hubungkan foreign key `rumah_sakit_id` ke tabel `rumah_sakit` agar tidak terjadi error relasi database.

## 2. Model Kontak

Buatkan model Eloquent dengan nama `Kontak`.

- **Fillable** : Semua kolom di atas kecuali `id` (`$guarded = ['id']`).
- **Relasi** : Definisikan relasi `belongsTo` ke model `RumahSakit`.

## 3. Filament Resource

Generate resource Filament untuk model `Kontak` menggunakan flag `--simple`.

- **Command** : `php artisan make:filament-resource KontakResource --simple`
- **Konfigurasi Form** :
    - **rumah_sakit_id** : Menggunakan field `Select` yang berelasi ke model `RumahSakit` (menampilkan nama rumah sakit).
    - **label** : Text input (max length 255).
    - **value** : Text input (max length 255).
    - **gambar** : Field upload gambar menggunakan `FileUpload::make('gambar')->image()`.
    - **logo** : Wajib diatur sebagai field yang menerima upload gambar menggunakan media upload image (`FileUpload::make('logo')->image()`) agar user bisa mengunggah file logo/ikon.
    - **link** : Textarea atau Text input panjang (longtext).
    - **kategori** : Menggunakan field `Select` dengan pilihan statis `SOSIAL MEDIA` dan `OPERASIONAL` (tidak dari relasi, cukup array options).
    - **aktif** : Toggle atau Checkbox dengan default value `true`.
- **Konfigurasi Tabel** :
    - Tampilkan kolom-kolom penting seperti nama rumah sakit (relasi), label, value, kategori, dan status aktif.
    - Tambahkan filter berdasarkan `kategori` dan `rumah_sakit_id`.

## 4. Database Seeder

Buatkan seeder dengan nama `KontakSeeder`.

- Semua data memiliki `rumah_sakit_id = 1` (RSU Syifa Medika Banjarbaru).
- Kolom `gambar` dan `logo` diisi `null` untuk sementara.
- Kolom `aktif` diisi `true` untuk semua data.
- Data diambil dari halaman https://rsusyifamedika.co.id/hubungi-kami/

### Kategori: OPERASIONAL

| # | Label | Value | Link |
|---|-------|-------|------|
| 1 | Ambulans 24 Jam | 0811 504 2424 | https://api.whatsapp.com/send/?phone=628115042424&text=Halo+Admin+IGD+RSU+Syifa+Medika+Banjarbaru+Saya+memerlukan+Ambulance+Sekarang |
| 2 | Operator | 0511 5910 889 | null |
| 3 | Pendaftaran Poliklinik | 0821 5342 4447 | https://api.whatsapp.com/send/?phone=6282153424447&text=Halo |
| 4 | Pendaftaran MCU | 0821 5551 8563 | https://api.whatsapp.com/send/?phone=6282155518563&text=Halo |
| 5 | Poli Eksekutif / Vaksin / Fertilitas | 0821 5461 8061 | https://api.whatsapp.com/send/?phone=6282154618061&text=Halo |
| 6 | Homecare | 0821 5421 2947 | https://api.whatsapp.com/send/?phone=6282154212947&text=Halo |
| 7 | Email | info.rsusyifamedika@gmail.com | null |
| 8 | Email Alternatif | rs.syifamedika@gmail.com | null |
| 9 | Pendaftaran Online | simgos.rsusyifamedika.co.id | https://simgos.rsusyifamedika.co.id/apps/RegOnline/ |
| 10 | Alamat | Jl. R.O. Ulin No. 93, Loktabat Selatan, Kota Banjarbaru | https://maps.app.goo.gl/ijm4pkR693c9sCeC7 |

### Kategori: SOSIAL MEDIA

> **Catatan:** Data sosial media belum berhasil diekstrak dari halaman website. Silakan cek footer halaman https://rsusyifamedika.co.id/ atau https://rsusyifamedika.co.id/hubungi-kami/ secara manual untuk mendapatkan link Instagram, Facebook, YouTube, TikTok, dan platform lainnya, lalu tambahkan ke seeder dengan format berikut:

| # | Label | Value | Link |
|---|-------|-------|------|
| 1 | Instagram | @rsusyifamedika (contoh, sesuaikan) | https://instagram.com/rsusyifamedika |
| 2 | Facebook | RSU Syifa Medika (contoh, sesuaikan) | https://facebook.com/rsusyifamedika |
| 3 | YouTube | RSU Syifa Medika (contoh, sesuaikan) | https://youtube.com/@rsusyifamedika |

> Ganti data di atas dengan data asli yang ditemukan di website.

## 5. Registrasi Seeder

Tambahkan `KontakSeeder` ke dalam `DatabaseSeeder.php` setelah seeder yang sudah ada:

```php
$this->call([
    // seeder lainnya...
    KontakSeeder::class,
]);
```
