# Planning Data Fasilitas Pendukung

Berikut adalah instruksi teknis untuk mengimplementasikan modul data Fasilitas Pendukung menggunakan Laravel dan Filament. Instruksi ini siap dikerjakan oleh programmer atau AI Model.

## 1. Migrasi Database

Buatkan file migrasi untuk tabel `fasilitas_pendukung` dengan spesifikasi kolom sebagai berikut:

- **id** (integer, auto increment, primary key)
- **rumah_sakit_id** (integer, foreign key ke tabel `rumah_sakit`)
- **nama** (varchar 255)
- **gambar** (varchar 255)
- **deskripsi** (text)
- **aktif** (boolean, default true)
- **timestamps** (created_at, updated_at)

> **Catatan Hubungan Data:** Hubungkan foreign key `rumah_sakit_id` ke tabel `rumah_sakit` agar tidak terjadi error relasi database.

## 2. Model FasilitasPendukung

Buatkan model Eloquent dengan nama `FasilitasPendukung`.

- **Fillable** : Semua kolom di atas kecuali `id`.

## 3. Filament Resource

Generate resource Filament untuk model `FasilitasPendukung`.

- **Command** : `php artisan make:filament-resource FasilitasPendukungResource --simple`
- **Konfigurasi Form** :
    - **rumah_sakit_id** : Menggunakan field `Select` yang berelasi ke model `RumahSakit` (menampilkan nama rumah sakit).
    - **nama** : Text input (max length 255).
    - **gambar** : Wajib diatur sebagai field yang menerima upload gambar menggunakan media upload image (`FileUpload::make('gambar')->image()`) agar user bisa mengunggah file.
    - **deskripsi** : Textarea.
    - **aktif** : Toggle atau Checkbox dengan default value `true`.
- **Konfigurasi Tabel** :
    - Tampilkan kolom-kolom penting seperti nama rumah sakit (relasi), nama fasilitas, status aktif, dan thumbnail gambar.