# Planning Data Promo

Berikut adalah instruksi teknis untuk mengimplementasikan modul data Promo menggunakan Laravel dan Filament. Instruksi ini siap dikerjakan oleh programmer atau AI Model.

## 1. Migrasi Database

Buatkan file migrasi untuk tabel `promo` dengan spesifikasi kolom sebagai berikut:

- **id** (integer, auto increment, primary key)
- **rumah_sakit_id** (integer, foreign key ke tabel `rumah_sakit`)
- **judul** (varchar 255)
- **deskripsi** (longtext, nullable)
- **gambar** (varchar 255, nullable)
- **tipe** (enum: `POPUP`, `SLIDER`)
- **sort_order** (smallint, default 0)
- **aktif** (boolean, default true)
- **timestamps** (created_at, updated_at)

> **Catatan Hubungan Data:** Hubungkan foreign key `rumah_sakit_id` ke tabel `rumah_sakit` (bukan `rawat_inap`) agar relasi database benar.

## 2. Model `Promo`

Buatkan model Eloquent dengan nama `Promo`.

- **Fillable** : Semua kolom di atas kecuali `id` (`$guarded = ['id']`).
- **Relasi** : Definisikan relasi `belongsTo` ke model `RumahSakit`.

## 3. Filament Resource

Generate resource Filament untuk model `Promo` menggunakan flag `--simple`.

- **Command** : `php artisan make:filament-resource PromoResource --simple`
- **Konfigurasi Form** :
    - **rumah_sakit_id** : Menggunakan field `Select` yang berelasi ke model `RumahSakit` (menampilkan nama rumah sakit).
    - **judul** : Text input (max length 255).
    - **deskripsi** : Wajib menggunakan `RichEditor` dari Filament (`RichEditor::make('deskripsi')`) agar mendukung format teks kaya (bold, italic, list, dll). Set nullable.
    - **gambar** : Wajib diatur sebagai field yang menerima upload gambar menggunakan `FileUpload::make('gambar')->image()` agar user bisa mengunggah file gambar.
    - **tipe** : Menggunakan field `Select` dengan pilihan statis `POPUP` dan `SLIDER` (tidak dari relasi, cukup array options).
    - **sort_order** : Numeric input (`TextInput::make('sort_order')->numeric()->default(0)`).
    - **aktif** : Toggle atau Checkbox dengan default value `true`.
- **Konfigurasi Tabel** :
    - Tampilkan kolom-kolom penting: nama rumah sakit (relasi), judul, tipe, sort_order, dan status aktif.
    - Tambahkan filter berdasarkan `tipe`, `rumah_sakit_id`, dan `aktif`.
    - Urutkan default berdasarkan `sort_order` ascending.

## 4. Registrasi Seeder

Modul ini tidak memiliki data awal (seeder). Data promo diisi langsung melalui admin panel Filament oleh operator.

Namun, pastikan model `Promo` sudah terdaftar dengan benar dan dapat diakses via admin panel di `/admin`.

## 5. Catatan Tambahan

- **Tipe `POPUP`** : Promo yang ditampilkan sebagai modal/popup saat pengguna pertama kali membuka halaman portal rumah sakit.
- **Tipe `SLIDER`** : Promo yang ditampilkan sebagai slide pada bagian carousel/banner di halaman utama portal.
- **sort_order** : Digunakan untuk mengatur urutan tampil promo, nilai terkecil tampil lebih dulu.
- Kolom `deskripsi` menyimpan HTML dari RichEditor, pastikan saat menampilkan di frontend menggunakan `{!! $promo->deskripsi !!}` (bukan `{{ }}`) agar tag HTML ter-render dengan benar.
