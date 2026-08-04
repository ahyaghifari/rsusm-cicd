# Planning Data Dokter

## 1. Database & Migrasi
Buatkan tabel migrasi untuk `dokter` dengan spesifikasi kolom sebagai berikut:
- `id`: integer, auto increment, primary key
- `nama`: varchar(255)
- `slug`: text, unique
- `foto`: varchar(255), nullable
- `deskripsi`: TEXT, nullable
- `aktif`: boolean, default true
- `pendidikan`: TEXT, nullable
- `pelatihan`: TEXT, nullable
- `rumah_sakit_id`: integer, foreign key ke tabel `rumah_sakit`
- `spesialis_id`: integer, foreign key ke tabel `spesialis`
- `timestamps`: (`created_at`, `updated_at`)

## 2. Eloquent Model
- Buat model dengan nama `Dokter`.
- Izinkan semua kolom (allowed fields / mass assignable) kecuali kolom `id` (`$guarded = ['id']`).
- Atur Route Model Binding agar menggunakan kolom `slug` (bukan `id`).
- Buat relasi ke model `RumahSakit` dan `Spesialis`.

## 3. Filament Resource
- Buat Resource Filament dengan nama `DokterResource` menggunakan flag `--generate`.
- Konfigurasi form:
  - Kolom `foto` harus menggunakan komponen FileUpload untuk menerima upload gambar.
  - Untuk `rumah_sakit_id` dan `spesialis_id`, gunakan komponen Select.
  - Untuk field `pendidikan` dan `pelatihan`, **gunakan komponen Rich Text Editor dari Filament**.
- Konfigurasi redirect: Setelah selesai melakukan aksi *create* atau *update*, halaman harus otomatis kembali ke halaman *index* (daftar dokter).

## 4. Database Seeder
- Buat seeder dengan nama `DokterSeeder`.
- Gunakan Faker (bahasa Indonesia) untuk men-generate data (seperti nama, deskripsi, dsb.).
- Untuk gambar/foto, bisa menggunakan URL gambar online *dummy* (contoh: via faker image provider) untuk sementara waktu.
- Slug harus digenerate secara otomatis berdasarkan nama dokter.
- **Logika Relasi Seeder:**
  - Tabel `rumah_sakit` saat ini memiliki 2 data (ID 1 dan 2).
  - Buatkan **masing-masing 5 dokter** untuk setiap rumah sakit.
  - Untuk `spesialis_id`, pilih ID secara acak dari data di tabel `spesialis` yang **memiliki `rumah_sakit_id` sesuai dengan rumah sakit dokter tersebut**.
  - Sesuaikan nama tabel (`rumah_sakit` dan `spesialis`) pada penulisan logika database/model jika diperlukan.
