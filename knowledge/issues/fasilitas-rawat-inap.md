# Planning Data Fasilitas Rawat Inap

Berikut adalah instruksi teknis untuk mengubah dan mengimplementasikan modul data Fasilitas Rawat Inap menggunakan Laravel dan Filament. Instruksi ini siap dikerjakan oleh programmer atau AI Model yang murah atau gratis.

## 1. Perubahan Migrasi Rawat Inap
Hapus kolom `fasilitas` pada file migrasi pembuatan tabel `rawat_inap` (atau buat migrasi alter table baru untuk menghapus kolom tersebut) karena pengelolaannya akan diganti dengan relasi pada tabel yang baru dibuat.

## 2. Database & Migrasi Baru
Buatkan file migrasi untuk tabel `fasilitas_rawat_inap` dengan spesifikasi kolom sebagai berikut:
- `id`: integer, auto increment, primary key
- `rawat_inap_id`: integer, foreign key ke tabel `rawat_inap` (menggunakan `constrained('rawat_inap')->cascadeOnDelete()`)
- `nama`: varchar(255)
- `aktif`: boolean, default true
- `timestamps`: (`created_at`, `updated_at`)

## 3. Eloquent Model
Buatkan model Eloquent dengan nama `FasilitasRawatInap`.
- **Table**: `fasilitas_rawat_inap`
- **Allowed Fields**: Izinkan semua kolom untuk mass assignment kecuali kolom `id` (`$guarded = ['id']`).
- **Casts**: `aktif` ke `boolean`.
- **Relasi**:
  - Definisikan relasi `belongsTo` ke model `RawatInap` pada model `FasilitasRawatInap`.
  - Definisikan relasi `hasMany` ke model `FasilitasRawatInap` pada model `RawatInap`.

## 4. Database Seeder
Buatkan seeder dengan nama `FasilitasRawatInapSeeder`.
- Isinya dibuat dengan mengekstrak data fasilitas yang sebelumnya menjadi satu field (berisi HTML/teks) di tabel `rawat_inap`.
- Pecah teks fasilitas tersebut menjadi baris rekaman terpisah (satu nama fasilitas = satu row) sesuai dengan id rawat inapnya. 
- Daftarkan seeder ini di `DatabaseSeeder` setelah `RawatInapSeeder`.

## 5. Filament Resource & Relation Manager
- **Resource Baru**: Generate resource Filament untuk model `FasilitasRawatInap` menggunakan flag `--simple`.
  - **Command**: `php artisan make:filament-resource FasilitasRawatInap --simple`
  - Konfigurasi form dan tabel untuk menyesuaikan manajemen fasilitas (termasuk filter/search).
- **Relation Manager**:
  - Lakukan relasi Filament antara `RawatInap` dengan `FasilitasRawatInap` agar fasilitas dapat ditambah, diedit, atau dihapus langsung dari halaman detail Rawat Inap.
  - Buat Relation Manager dengan perintah:
    ```bash
    php artisan make:filament-relation-manager RawatInapResource fasilitasRawatInap nama
    ```
  - Daftarkan Relation Manager tersebut di dalam kelas `RawatInapResource` pada fungsi `getRelations()`.
  - Sesuaikan (atau hapus) form field lama bernama `fasilitas` di `RawatInapResource` karena kini sudah diganti ke tabel terpisah.
