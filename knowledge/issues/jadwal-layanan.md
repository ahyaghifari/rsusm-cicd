# Planning Data Jadwal Layanan

Berikut adalah instruksi teknis untuk mengimplementasikan modul data Jadwal Layanan menggunakan Laravel (dan Filament jika diperlukan). Instruksi ini siap dikerjakan oleh programmer atau AI Model.

## 1. Migrasi Database

Buatkan file migrasi baru untuk tabel `jadwal_layanan` dengan menjalankan perintah:
`php artisan make:migration create_jadwal_layanan_table`

Spesifikasi kolom di dalam method `up()` adalah sebagai berikut:

- `id` (integer, auto increment, primary key)
- `poliklinik_id` (foreignId, constrained ke tabel `poliklinik`, cascade on delete)
- `tanggal` (date)
- `dokter_id` (foreignId, nullable, constrained ke tabel `dokter` atau disesuaikan dengan tabel master dokter yang ada, null on delete)
- `nama_dokter` (varchar 255, nullable)
- `jam_mulai` (time, nullable)
- `jam_selesai` (time, nullable)
- `status_layanan` (enum dengan nilai `['BUKA', 'LIBUR']`, berikan default `'BUKA'`)
- `catatan` (text, nullable)
- `timestamps` (created_at, updated_at)

## 2. Model Eloquent `JadwalLayanan`

Buatkan model Eloquent dengan menjalankan perintah:
`php artisan make:model JadwalLayanan`

Konfigurasi di dalam class Model:

- **Table Name**: Pastikan mendefinisikan `$table = 'jadwal_layanan';` secara eksplisit.
- **Fillable**: Izinkan mass-assignment untuk semua kolom di atas _kecuali_ `id`.
  ```php
  protected $fillable = [
      'poliklinik_id',
      'tanggal',
      'dokter_id',
      'nama_dokter',
      'jam_mulai',
  ```
