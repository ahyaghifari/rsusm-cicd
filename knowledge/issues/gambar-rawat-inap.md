# Planning Data Gambar Rawat Inap

Berikut adalah instruksi teknis untuk mengimplementasikan modul data Gambar Rawat Inap menggunakan Laravel dan Filament. Instruksi ini siap dikerjakan oleh programmer atau AI Model yang murah atau gratis.

## 1. Database & Migrasi
Buatkan file migrasi untuk tabel `gambar_rawat_inap` dengan spesifikasi kolom sebagai berikut:
- `id`: integer, auto increment, primary key
- `rawat_inap_id`: integer, foreign key ke tabel `rawat_inap` (menggunakan `constrained('rawat_inap')->cascadeOnDelete()`)
- `gambar`: varchar(255)
- `deskripsi`: text, nullable
- `sort_order`: integer
- `aktif`: boolean, default true
- `timestamps`: (`created_at`, `updated_at`)

## 2. Eloquent Model
Buatkan model Eloquent dengan nama `GambarRawatInap`.
- **Table**: `gambar_rawat_inap`
- **Allowed Fields**: Izinkan semua kolom untuk mass assignment kecuali kolom `id` (`$guarded = ['id']`).
- **Casts**: `aktif` ke `boolean`.
- **Relasi**: 
  - Definisikan relasi `belongsTo` ke model `RawatInap` pada model `GambarRawatInap`.
  - Definisikan relasi `hasMany` ke model `GambarRawatInap` pada model `RawatInap`.

## 3. Filament Resource & Relation Manager
Generate resource Filament untuk model `GambarRawatInap` menggunakan flag `--generate`.
- **Command**: `php artisan make:filament-resource GambarRawatInap --generate`
- **Relation Manager**:
  - Hubungkan modul `RawatInap` dengan `GambarRawatInap` agar gambar-gambar kamar dapat dikelola langsung dari halaman detail/edit kamar rawat inap.
  - Buat Relation Manager dengan perintah:
    ```bash
    php artisan make:filament-relation-manager RawatInapResource gambarRawatInap gambar
    ```
  - Daftarkan Relation Manager tersebut di dalam kelas `RawatInapResource` pada fungsi `getRelations()`.

## 4. Drag & Drop Sorting (Sort Order)
Untuk mempermudah pengurutan gambar dengan drag and drop:
- Aktifkan fitur reorderable bawaan Filament pada tabel `GambarRawatInapResource` dan pada tabel Relation Manager-nya menggunakan `->reorderable('sort_order')`.
- Urutkan data secara default berdasarkan `sort_order` menaik (`asc`) menggunakan `->defaultSort('sort_order', 'asc')`.
