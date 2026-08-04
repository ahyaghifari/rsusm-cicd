# Planning Data Penunjang Medis

Berikut adalah instruksi teknis untuk mengimplementasikan modul data Penunjang Medis menggunakan Laravel dan Filament. Instruksi ini siap dikerjakan oleh programmer atau AI Model.

## 1. Migrasi Database

Buatkan file migrasi untuk tabel `penunjang_medis` dengan spesifikasi kolom sebagai berikut:

- **id** (integer, auto increment, primary key)
- **rumah_sakit_id** (integer, foreign key ke tabel `rumah_sakit`)
- **nama** (varchar 255)
- **gambar** (varchar 255, nullable)
- **deskripsi** (text)
- **aktif** (boolean, default true)
- **timestamps** (created_at, updated_at)

> **Catatan Hubungan Data:** Hubungkan foreign key `rumah_sakit_id` ke tabel `rumah_sakit` agar tidak terjadi error relasi database.

## 2. Model PenunjangMedis

Buatkan model Eloquent dengan nama `PenunjangMedis`.

- **Fillable** : Semua kolom di atas kecuali `id` (`$guarded = ['id']`).
- **Relasi** : Definisikan relasi `belongsTo` ke model `RumahSakit`.

## 3. Filament Resource

Generate resource Filament untuk model `PenunjangMedis` menggunakan flag `--simple`.

- **Command** : `php artisan make:filament-resource PenunjangMedisResource --simple`
- **Konfigurasi Form** :
    - **rumah_sakit_id** : Menggunakan field `Select` yang berelasi ke model `RumahSakit` (menampilkan nama rumah sakit).
    - **nama** : Text input (max length 255).
    - **gambar** : Wajib diatur sebagai field yang menerima upload gambar menggunakan media upload image (`FileUpload::make('gambar')->image()`) agar user bisa mengunggah file.
    - **deskripsi** : Textarea.
    - **aktif** : Toggle atau Checkbox dengan default value `true`.
- **Konfigurasi Tabel** :
    - Tampilkan kolom-kolom penting seperti nama rumah sakit (relasi), nama penunjang medis, status aktif, dan thumbnail gambar.

## 4. Database Seeder

Buatkan seeder dengan nama `PenunjangMedisSeeder`.

- Semua data memiliki `rumah_sakit_id = 1` (RSU Syifa Medika Banjarbaru).
- Kolom `gambar` diisi `null` untuk sementara.
- Data diambil dari halaman https://rsusyifamedika.co.id/penunjang-medis/

Data yang harus dimasukkan:

1. **Instalasi Radiologi**
   - Deskripsi: Layanan pemeriksaan radiologi dengan peralatan canggih untuk mendukung diagnosis dokter, mencakup: General X-Ray (Digital Radiography), USG (Ultrasonografi), Dental X-Ray, Panoramic Dental X-ray, CT-SCAN (80 slice).
   - Gambar: null
   - Aktif: true

2. **Instalasi Laboratorium (Patologi Klinik)**
   - Deskripsi: Unit pelayanan yang membantu penegakan diagnosis dan monitoring penyakit. Pelayanan Laboratorium RSU Syifa Medika Banjarbaru buka 24 jam termasuk hari minggu, dengan layanan mencakup: Urinalisa, Analisis Feses, Immunologi & Serologi, Hematologi, Fungsi Hati, Ginjal, dan Lemak, Panel Jantung, Diabetes, Mikrobiologi, Infeksi, Alergi & Parasitologi, Analisa Sperma, Tuberkulosis.
   - Gambar: null
   - Aktif: true

3. **Instalasi Farmasi**
   - Deskripsi: Melayani distribusi obat kepada pasien rawat jalan, rawat inap, dan IGD selama 24 jam, dengan konsultasi farmasi klinik mengenai informasi dan penggunaan obat.
   - Gambar: null
   - Aktif: true
