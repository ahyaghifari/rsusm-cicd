# Planning: Auto-Generate JadwalHarian dari JadwalPraktek

**Dibuat:** 2026-06-04  
**Status:** Done  
**Prioritas:** High  
**Label:** feature, cron, jadwal

---

## Latar Belakang

`JadwalHarian` sebelumnya berasal dari resource lama (`JadwalLayananHarian`) yang sudah dihapus. Kini sumber kebenarannya adalah `JadwalPraktek` (jadwal mingguan per poliklinik per hari).

Tujuan: setiap hari sistem otomatis membuat **snapshot** `JadwalHarian` dari `JadwalPraktek` yang berlaku pada hari tersebut. Admin tetap bisa:
- Edit `JadwalHarian` manual jika ada perubahan mendadak (dokter tidak masuk, dll.)
- Atau muat manual dari UI (`muatDariJadwalMingguan()` yang sudah ada)

**Aturan utama: jika `JadwalHarian` untuk tanggal + poliklinik tertentu sudah ada → SKIP, tidak overwrite.**

---

## Arsitektur Setelah Implementasi

```
JadwalPraktek (template mingguan, per hari enum)
        │
        │  cron harian (00:05) — hanya jika belum ada
        ↓
JadwalHarian (snapshot per tanggal, bisa diedit admin)
        │
        │  fallback ke JadwalPraktek jika tidak ada snapshot
        ↓
Portal publik (tampilkan jadwal akurat hari ini)
```

---

## Field Mapping: JadwalPraktek → JadwalHarian

| JadwalPraktek | JadwalHarian | Keterangan |
|---|---|---|
| `poliklinik_id` | `poliklinik_id` | Direct |
| `hari` (enum) | — | Digunakan untuk filter, bukan disimpan |
| — | `tanggal` | Tanggal yang di-generate |
| `dokter_id` | `dokter_id` | Nullable |
| `nama_dokter` | `nama_dokter` | Nullable |
| `waktu_mulai` | `jam_mulai` | Format H:i |
| `waktu_selesai` | `jam_selesai` | Nullable, format H:i |
| — | `status_layanan` | Default: `BUKA` |
| `catatan` | `catatan` | Nullable |

> **Catatan:** `sesuai_perjanjian` ada di `JadwalPraktek` tapi tidak di `JadwalHarian`. Sementara field ini diabaikan dalam mapping. Jika dibutuhkan di `JadwalHarian`, perlu migration baru (diputuskan terpisah).

---

## Rencana Implementasi

### Step 1 — Console Command

**File baru:** `app/Console/Commands/GenerateJadwalHarian.php`

```php
// Signature: jadwal:generate-harian {tanggal?}
// Contoh: php artisan jadwal:generate-harian 2026-06-05
// Default: hari ini
```

**Algoritma:**
```
1. Parse tanggal dari argumen atau default today
2. Tentukan hari (SENIN/SELASA/...) dari tanggal
3. Fetch semua RumahSakit aktif
4. Untuk setiap RS:
   a. Ambil semua JadwalPraktek dengan hari = hari_aktif,
      scoped ke poliklinik milik RS ini
   b. Untuk setiap JadwalPraktek:
      - Cek apakah JadwalHarian (tanggal + poliklinik_id) sudah ada
      - Jika SUDAH ADA → skip (jangan overwrite)
      - Jika BELUM ADA → insert baru
5. Log summary: berapa baris di-insert, berapa yang di-skip
```

**Idempoten:** Menjalankan command yang sama dua kali untuk tanggal sama tidak menghasilkan duplikat.

---

### Step 2 — Schedule Harian

**File edit:** `routes/console.php`

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('jadwal:generate-harian')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->runInBackground();
```

Waktu 00:05 dipilih agar cron berjalan tepat setelah hari berganti, sehingga jadwal harian sudah tersedia sejak awal hari.

---

### Step 3 — Penyesuaian Portal (Tahap Berikutnya)

Ini adalah **scope terpisah** setelah cron berjalan:

Saat ini portal (`JadwalPraktek.php`, `PoliKlinikDetail.php`) query langsung ke tabel `jadwal_praktek`. Setelah cron berjalan dan data `jadwal_harian` tersedia, portal bisa diubah untuk:

1. Cek `JadwalHarian` untuk tanggal hari ini terlebih dahulu
2. Jika tidak ada (misalnya cron belum jalan) → fallback ke `JadwalPraktek`

Namun ini memerlukan **pengujian tersendiri** — **tidak diimplementasi bersamaan** dengan cron.

---

## File yang Perlu Dibuat / Diubah

| File | Aksi |
|---|---|
| `app/Console/Commands/GenerateJadwalHarian.php` | **Buat baru** — console command |
| `routes/console.php` | **Edit** — tambah schedule |

---

## Pertimbangan Teknis

### Skip Logic
Pengecekan dilakukan per pasangan `(tanggal, poliklinik_id)`:
```php
$exists = JadwalHarian::where('tanggal', $tanggal)
    ->where('poliklinik_id', $jadwalPraktek->poliklinik_id)
    ->exists();
```

Ini lebih granular — jika admin sudah edit satu poliklinik, poliklinik lain yang belum ada tetap bisa di-generate.

### Transaksi
Setiap RS di-wrap dalam `DB::transaction` tersendiri — jika gagal untuk satu RS, RS lain tetap diproses.

### Logging
Command menggunakan `$this->info()` dan `$this->warn()` untuk output di terminal, plus `Log::info()` untuk audit trail saat dijalankan via cron.

### Poliklinik tidak aktif
`JadwalPraktek` hanya diambil untuk poliklinik yang `aktif = true`.

### JadwalPraktek kosong untuk hari itu
Jika hari Minggu tidak ada JadwalPraktek untuk RS tertentu → tidak ada yang di-generate → normal, tidak ada error.

---

## Acceptance Criteria

- [ ] Command `jadwal:generate-harian` berjalan tanpa error
- [ ] Jika JadwalHarian untuk `(tanggal, poliklinik_id)` sudah ada → SKIP, tidak overwrite
- [ ] Jika JadwalHarian belum ada → INSERT dari JadwalPraktek hari tersebut
- [ ] Status awal selalu `BUKA`
- [ ] Command bisa menerima tanggal spesifik sebagai argumen
- [ ] Default tanggal = hari ini
- [ ] Schedule `dailyAt('00:05')` terdaftar di `routes/console.php`
- [ ] Command idempoten (aman dijalankan berkali-kali)
- [ ] Output menampilkan summary (X baris di-insert, Y di-skip)
- [ ] Log tersimpan untuk audit
