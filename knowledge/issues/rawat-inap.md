# Planning Data Rawat Inap

Berikut adalah instruksi teknis untuk mengimplementasikan modul data Rawat Inap menggunakan Laravel dan Filament. Instruksi ini siap dikerjakan oleh programmer atau AI Model.

## 1. Database & Migrasi
Buatkan file migrasi untuk tabel `rawat_inap` dengan spesifikasi kolom sebagai berikut:
- `id`: integer, auto increment, primary key
- `rumah_sakit_id`: integer, foreign key ke tabel `rumah_sakit`
- `gedung_id`: integer, foreign key ke tabel `gedung`, nullable
- `nama`: varchar(255)
- `kelas`: varchar(255)
- `harga`: decimal(10,2)
- `kapasitas`: smallint (diambil dari jumlah bed pasien)
- `fasilitas`: text (berisi HTML)
- `thumbnail`: varchar(255), nullable
- `sort_order`: int
- `aktif`: boolean, default true
- `timestamps`: (`created_at`, `updated_at`)

## 2. Eloquent Model
Buatkan model Eloquent dengan nama `RawatInap`.
- **Allowed Fields**: Izinkan semua kolom untuk mass assignment kecuali kolom `id` (`$guarded = ['id']`).
- **Relasi**: Definisikan relasi `belongsTo` ke model `RumahSakit` dan `Gedung`.

## 3. Filament Resource
Generate resource Filament untuk model `RawatInap` menggunakan flag `--generate`.
- **Command**: `php artisan make:filament-resource RawatInap --generate`
- **Konfigurasi Form**:
  - Untuk `fasilitas`, gunakan **Rich Text Editor**.
  - Kolom relasi `rumah_sakit_id` dan `gedung_id` menggunakan Select.
- **Konfigurasi Tabel & Sort Order**:
  - Implementasikan fitur *drag and drop* untuk mengubah `sort_order`. (Bisa memanfaatkan fitur `reorderable('sort_order')` bawaan tabel Filament atau halaman custom sesuai kebutuhan).

## 4. Database Seeder
Buatkan seeder dengan nama `RawatInapSeeder` dengan ketentuan:
- Semua data memiliki `rumah_sakit_id = 1`.
- ID Gedung: Shofa (1), Marwah (2), Muzdalifah (3).
- Nilai kapasitas diekstrak dari jumlah "Bed Pasien".

Data yang harus dimasukkan:

1. **Paviliun Firdaus - VVIP Ar-Raudhah**
   - Gedung: Marwah (2)
   - Kelas: VVIP
   - Harga: 1199619
   - Fasilitas: 1 Bed Pasien Elektrik, 1 Sofa Bed Penunggu, AC, Smart TV, Kulkas, Dispender, Wifi, Kamar Mandi Dalam, Water Heater, Lemari Penyimpanan, Meja Makan Pasien
   - Kapasitas: 1
   - Sort Order: 1

2. **Paviliun Firdaus - VIP Al-Karim**
   - Gedung: Marwah (2)
   - Kelas: VIP
   - Harga: 863999
   - Fasilitas: 1 Bed Pasien, Sofa Penunggu, AC, TV, Kulkas, Wifi, Kamar Mandi Dalam, Lemari Penyimpanan
   - Kapasitas: 1
   - Sort Order: 2

3. **VIP Al-Hakim**
   - Gedung: Marwah (2)
   - Kelas: VIP
   - Harga: 949817
   - Fasilitas: 1 Bed Pasien, AC, TV, Kulkas, Sofa Penunggu, Wifi, Kamar Mandi Dalam
   - Kapasitas: 1
   - Sort Order: 1 (reset per gedung/grup atau dilanjutkan, sesuaikan)

4. **Kelas I As-Salam**
   - Gedung: Marwah (2)
   - Kelas: Kelas I
   - Harga: 713076
   - Fasilitas: 2 Bed Pasien, AC, TV, Kamar Mandi Dalam, Lemari Penyimpanan
   - Kapasitas: 2
   - Sort Order: 2

5. **Kelas I An-Nur**
   - Gedung: Muzdalifah (3)
   - Kelas: Kelas I
   - Harga: 713076
   - Fasilitas: 2 Bed Pasien, AC, TV, Kamar Mandi Dalam
   - Kapasitas: 2
   - Sort Order: 1

6. **Kelas I An-Nur (204, 210-215)**
   - Gedung: Muzdalifah (3)
   - Kelas: Kelas I
   - Harga: 692307
   - Fasilitas: 2 Bed Pasien, AC, Kamar Mandi Dalam
   - Kapasitas: 2
   - Sort Order: 2

7. **VIP Plus An-Nisa (OBGYN)**
   - Gedung: Shofa (1)
   - Kelas: VIP Plus
   - Harga: 949817
   - Fasilitas: 1 Bed Pasien, Sofa Penunggu, AC, TV, Kulkas, Wifi, Kamar Mandi Dalam, Area Khusus OBGYN
   - Kapasitas: 1
   - Sort Order: 1

8. **VIP Ar-Rahman**
   - Gedung: Shofa (1)
   - Kelas: VIP
   - Harga: 830768
   - Fasilitas: 1 Bed Pasien, AC, TV, Sofa Penunggu, Kamar Mandi Dalam
   - Kapasitas: 1
   - Sort Order: 2

9. **Kelas I Al-Kautsar**
   - Gedung: Shofa (1)
   - Kelas: Kelas I
   - Harga: 692307
   - Fasilitas: 1 Bed Pasien, AC, TV, Kamar Mandi Dalam
   - Kapasitas: 1
   - Sort Order: 3

10. **Kelas II Al-Furqon**
    - Gedung: Shofa (1)
    - Kelas: Kelas II
    - Harga: 415384
    - Fasilitas: 2 Bed Pasien, Kipas Angin, Kamar Mandi Dalam
    - Kapasitas: 2
    - Sort Order: 4

11. **Kelas III Al-Fath**
    - Gedung: Shofa (1)
    - Kelas: Kelas III
    - Harga: 242307
    - Fasilitas: 3 Bed Pasien, Kipas Angin, Kamar Mandi Bersama
    - Kapasitas: 3
    - Sort Order: 5
