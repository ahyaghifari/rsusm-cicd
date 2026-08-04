# Planning Data Partner

Berikut adalah instruksi teknis untuk mengimplementasikan modul data Partner menggunakan Laravel dan Filament. Instruksi ini siap dikerjakan oleh programmer atau AI Model.

## 1. Migrasi Database

Buatkan file migrasi untuk tabel `partner` dengan spesifikasi kolom sebagai berikut:

- **id** (integer, auto increment, primary key)
- **rumah_sakit_id** (integer, foreign key ke tabel `rumah_sakit`)
- **nama** (varchar 255)
- **logo** (varchar 255, nullable)
- **kategori** (enum: `ASURANSI`, `PERUSAHAAN`)
- **aktif** (boolean, default true)
- **timestamps** (created_at, updated_at)

> **Catatan Hubungan Data:** Hubungkan foreign key `rumah_sakit_id` ke tabel `rumah_sakit` agar tidak terjadi error relasi database.

## 2. Model Partner

Buatkan model Eloquent dengan nama `Partner`.

- **Fillable** : Semua kolom di atas kecuali `id` (`$guarded = ['id']`).
- **Relasi** : Definisikan relasi `belongsTo` ke model `RumahSakit`.

## 3. Filament Resource

Generate resource Filament untuk model `Partner` menggunakan flag `--simple`.

- **Command** : `php artisan make:filament-resource PartnerResource --simple`
- **Konfigurasi Form** :
    - **rumah_sakit_id** : Menggunakan field `Select` yang berelasi ke model `RumahSakit` (menampilkan nama rumah sakit).
    - **nama** : Text input (max length 255).
    - **logo** : Wajib diatur sebagai field yang menerima upload gambar menggunakan media upload image (`FileUpload::make('logo')->image()`) agar user bisa mengunggah file.
    - **kategori** : Menggunakan field `Select` dengan pilihan statis `ASURANSI` dan `PERUSAHAAN` (tidak dari relasi, cukup array options).
    - **aktif** : Toggle atau Checkbox dengan default value `true`.
- **Konfigurasi Tabel** :
    - Tampilkan kolom-kolom penting seperti nama rumah sakit (relasi), nama partner, kategori, status aktif, dan thumbnail logo.
    - Tambahkan filter berdasarkan `kategori`.

## 4. Database Seeder

Buatkan seeder dengan nama `PartnerSeeder`.

- Semua data memiliki `rumah_sakit_id = 1` (RSU Syifa Medika Banjarbaru).
- Kolom `logo` diisi `null` untuk sementara.
- Kolom `aktif` diisi `true` untuk semua data.
- Data diambil dari halaman https://rsusyifamedika.co.id/partner-kami/

### Kategori: ASURANSI

| # | Nama |
|---|------|
| 1 | BPJS KESEHATAN |
| 2 | BPJS KETENAGAKERJAAN |
| 3 | JASA RAHARJA |
| 4 | APLN |
| 5 | MANDIRI INHEALTH INDEMNITY |
| 6 | MANDIRI INHEALTH MANAGED CARE |
| 7 | MANDIRI INHEALTH I-PRO |
| 8 | PRUDENTIAL LIFE INSURANCE |
| 9 | PRUDENTIAL SHARIA INSURANCE |
| 10 | AVRIST ASSURANCE |
| 11 | BNI LIFE INSURANCE |
| 12 | AXA SERVICES INDONESIA |
| 13 | GARDA MEDIKA |
| 14 | SUNDAY INSURANCE |
| 15 | SINAR MAS MSIG |
| 16 | PACIFIC CROSS |
| 17 | ISOMEDIK |
| 18 | LIPPO LIFE INSURANCE |
| 19 | HALODOC |
| 20 | RELIANCE INDONESIA |
| 21 | SINAR MAS |
| 22 | TUGU KRESNA PRATAMA |
| 23 | ASTRA BUANA |
| 24 | JIWA ADISARANA WANAARTHA |
| 25 | AA INTERNATIONAL INDONESIA |
| 26 | TAKAFUL |
| 27 | FWD |
| 28 | GLOBAL ASSISTANCE & HEALTHCARE |
| 29 | HIGEA MEDIKA INSURA SOLUSI INDONESIA |
| 30 | MULTI ARTHA GUNA (MAG) |
| 31 | MANULIFE INDONESIA |
| 32 | BRI LIFE |
| 33 | DOC DOC HEALTHCARE INDONESIA |
| 34 | ALLIANZ LIFE INDONESIA |
| 35 | ALLIANZ LIFE SYARIAH INDONESIA |
| 36 | YAYASAN KESEHATAN PERTAMINA |
| 37 | ZURICH ASURANSI INDONESIA |
| 38 | CHUBB LIFE INSURANCE |
| 39 | FULLERTON HEALTH INDONESIA |
| 40 | AJ CENTRAL ASIA RAYA (AJ CAR) |
| 41 | PRIMA SARANA JASA |
| 42 | MEDIKA PLAZA |
| 43 | MEDILUM |
| 44 | TELKOMEDIKA |
| 45 | YKP BANK BJB |
| 46 | MEDLINX ASIA TEKNOLOGI |
| 47 | SYNTECH MITRA INTEGRASI |
| 48 | PT ASURANSI CAKRAWALA PROTEKSI INDONESIA (ACPI) |
| 49 | PT ASURANSI CENTRAL ASIA (ACA) |
| 50 | CENTRAL ASIA RAYA (CAR) |
| 51 | OWLEXA |
| 52 | MEDILINK DIGITAL MEDIKA |
| 53 | ADMEDIKA |
| 54 | MEDITAP |
| 55 | HEALTHMETRICS |

### Kategori: PERUSAHAAN

| # | Nama |
|---|------|
| 1 | PT. TRAKINDO |
| 2 | PT. INDOFOOD CBF SUKSES MAKMUR Tbk NOODLE DIVISION |
| 3 | PT. CJ CHEILJEDANG FEED KALIMANTAN |
| 4 | PT. PAMA PERSADA NUSANTARA |
| 5 | PT. TRINAKA ESTU MANUNGGAL |
| 6 | PT. BHUMI RANTAU ENERGI (PT. BRE) |
| 7 | PT. TAPIN SUTHRA BERJAYA |
| 8 | PT. TALENTA BUMI |
| 9 | PT. JAYA MANDIRI SUKSES |
| 10 | PT. KALIMANTAN PRIMA PERSADA (PT. KPP) |
| 11 | PT. HEXINDO ADIPERKASA TBK |
| 12 | PT. CHITRA PARATAMA |
| 13 | PT. NUSA KONTRUKSI ENJINIRING TBK |
| 14 | PT. TRI SWARDANA UTAMA (PT. TSU) |
| 15 | PT. JAMBO MUTIARA PERMATA |
| 16 | AIRNAV |
| 17 | PT. JAPFA COMFEED INDONESIA, Tbk (Unit Bjm) |
| 18 | PT. CITRA PRIMA UTAMA (PT. CPU) |
| 19 | PT. PETROSEA Tbk |
| 20 | PT. ASTRA INTERNATIONAL-Tbk ISUZU Cab. Bjm |
| 21 | PT. CIPTA KRIDATAMA |
