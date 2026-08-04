# Planning Data Unit Layanan

[cite_start]Berikut adalah instruksi teknis untuk mengimplementasikan modul data Unit Layanan menggunakan Laravel dan Filament[cite: 16]. [cite_start]Instruksi ini siap dikerjakan oleh programmer atau AI Model[cite: 17].

## 1. Migrasi Database

[cite_start]Buatkan file migrasi untuk tabel `unit_layanan` dengan spesifikasi kolom sebagai berikut[cite: 18]:

- [cite_start]**id** (integer, auto increment, primary key) [cite: 18]
- **rumah_sakit_id** (integer, foreign key ke tabel `rumah_sakit`)
- **nama** (varchar 255)
- **deskripsi** (text, nullable)
- **gambar** (varchar 255, nullable)
- [cite_start]**aktif** (boolean, default true) [cite: 18]
- [cite_start]**timestamps** (created_at, updated_at) [cite: 18]

## 2. Model UnitLayanan

[cite_start]Buatkan model Eloquent dengan nama `UnitLayanan`[cite: 19].

- [cite_start]**Fillable / Allowed Fields:** Semua kolom di atas kecuali kolom `id`[cite: 19].

## 3. Filament Resource

[cite_start]Generate resource Filament untuk model `UnitLayanan`[cite: 20].

- [cite_start]**Command:** `php artisan make:filament-resource UnitLayananResource --simple` [cite: 20]
- **Konfigurasi Form:**
  - [cite_start]**rumah_sakit_id:** Menggunakan field `Select` yang berelasi ke model `RumahSakit` (menampilkan nama rumah sakit)[cite: 20].
  - [cite_start]**nama:** Text input (max length 255)[cite: 21].
  - [cite_start]**deskripsi:** Textarea, nullable[cite: 23].
  - [cite_start]**gambar:** Wajib diatur sebagai field yang menerima upload gambar menggunakan media upload image (`FileUpload::make('gambar')->image()`) agar user bisa mengunggah file[cite: 22].
  - [cite_start]**aktif:** Toggle atau Checkbox dengan default value `true`[cite: 23].
- **Konfigurasi Tabel:**
  - [cite_start]Tampilkan kolom-kolom penting seperti nama rumah sakit (relasi), nama unit layanan, status aktif, dan thumbnail gambar[cite: 24].

## 4. Seeder Database

Buatkan seeder dengan nama `UnitLayananSeeder` beserta 3 (tiga) baris data awal berikut:

**Data 1**

- **Rumah Sakit ID:** 1
- **Nama:** Rawat Jalan
- **Deskripsi:** null
- **Gambar:** null
- **Aktif:** true

**Data 2**

- **Rumah Sakit ID:** 1
- **Nama:** Aurora Executive Clinic
- **Deskripsi:** null
- **Gambar:** null
- **Aktif:** true

**Data 3**

- **Rumah Sakit ID:** 2
- **Nama:** Rawat Jalan
- **Deskripsi:** null
- **Gambar:** null
- **Aktif:** true
