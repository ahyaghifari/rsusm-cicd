# Plan: Deteksi "Balik ke Semula" tanpa Bergantung ke JadwalPraktek

**Status:** Draft (belum dieksekusi)
**Prioritas:** Medium
**Label:** refactor, tracking, jadwal
**Konteks:** Lanjutan dari [jadwal-harian-tracking-perubahan.md](jadwal-harian-tracking-perubahan.md) (Done)

---

## Masalah

Logika saat ini di `saveJadwal()` ([JadwalHarianPage.php:391-408](../rsu-syifamedika/app/Filament/Resources/JadwalHarianResource/Pages/JadwalHarianPage.php#L391-L408))
mendeteksi apakah sebuah baris "sudah balik ke kondisi normal" dengan cara
**membandingkannya ke `JadwalPraktek`**:

```php
$isSamaDenganAsli = JadwalPraktek::where('hari', $hariValue)
    ->where('poliklinik_id', $row['poliklinik_id'])
    ->where('dokter_id', $row['dokter_id'])
    ->whereTime('waktu_mulai', $jamMulaiFormat)
    ->when($jamSelesaiFormat, fn($q, $v) => $q->whereTime('waktu_selesai', $v),
                              fn($q) => $q->whereNull('waktu_selesai'))
    ->exists();

if ($isSamaDenganAsli && $row['status_layanan'] === 'BUKA') {
    $sumber = 'GENERATE'; // dianggap bukan perubahan
}
```

**Kenapa ini bermasalah:**
- `JadwalPraktek` (jadwal mingguan) bisa berubah kapan saja oleh admin — sehingga acuan
  "asli" untuk tanggal yang sudah lewat jadi tidak stabil/tidak akurat secara historis
- `jadwal_harian` semestinya berperan sebagai *historic snapshot* dari `JadwalPraktek`
  pada saat di-generate — seharusnya tidak perlu menengok lagi ke tabel sumbernya
  setelah snapshot itu dibuat
- Menambah satu query ke tabel lain hanya untuk mengecek "apakah ini perubahan"

---

## Solusi yang Disepakati

Simpan nilai **"asli" (sebelum berubah)** langsung di tabel `jadwal_harian_perubahan`
itu sendiri — sehingga deteksi "balik ke semula" cukup membandingkan input baru dengan
nilai asli yang sudah tersimpan di record `perubahan`, **tanpa menyentuh `JadwalPraktek`
sama sekali**.

> Catatan: sempat dipertimbangkan juga opsi menjadikan `jadwal_harian` immutable
> (snapshot tidak boleh ditimpa saat edit), tapi itu berarti merombak pola
> hapus-lalu-buat-ulang di `saveJadwal()`. Pendekatan menambah kolom `*_asli` di
> `jadwal_harian_perubahan` jauh lebih kecil scope-nya dan tetap mencapai tujuan yang
> sama: independen dari tabel lain yang bisa berubah.

### `is_executive` — TIDAK perlu ditambahkan

Sempat dipertimbangkan apakah `jadwal_harian_perubahan` perlu kolom `is_executive`.
**Kesimpulan: tidak.** Kolom itu sudah bisa diakses lewat relasi
`belongsTo(JadwalHarian)` (`$perubahan->jadwalHarian->is_executive`), dan secara desain
`is_executive` adalah atribut tetap dari slot jadwal — bukan sesuatu yang "diubah" lewat
mekanisme tracking perubahan harian ini.

---

## Checklist Implementasi

- [ ] **1. Migration** — tambah 3 kolom nullable ke `jadwal_harian_perubahan`: `jam_mulai_asli`, `jam_selesai_asli`, `status_layanan_asli`
- [ ] **2. Logika capture nilai asli** — isi kolom `*_asli` HANYA saat record `perubahan` pertama kali dibuat untuk sebuah `jadwal_harian`
- [ ] **3. Logika deteksi revert** — ganti `isSamaDenganAsli` (yang membandingkan ke `JadwalPraktek`) dengan perbandingan ke kolom `*_asli`
- [ ] **4. Logika hapus saat revert** — kalau nilai baru === nilai `*_asli`, hapus record `jadwal_harian_perubahan` (dianggap kembali normal)
- [ ] **5. Update Acceptance Criteria / dokumentasi terkait**

---

## 1. Migration

**File baru:** `database/migrations/YYYY_MM_DD_HHMMSS_add_nilai_asli_to_jadwal_harian_perubahan_table.php`

```php
Schema::table('jadwal_harian_perubahan', function (Blueprint $table) {
    $table->time('jam_mulai_asli')->nullable()->after('jadwal_harian_id');
    $table->time('jam_selesai_asli')->nullable()->after('jam_mulai_asli');
    $table->enum('status_layanan_asli', ['BUKA', 'LIBUR'])->nullable()->after('jam_selesai_asli');
});
```

Update juga `app/Models/JadwalHarianPerubahan.php`:
- Tambah `jam_mulai_asli`, `jam_selesai_asli`, `status_layanan_asli` ke `$fillable`
- Tambah cast `jam_mulai_asli` / `jam_selesai_asli` → `datetime:H:i`

---

## 2. Logika Capture Nilai Asli (sekali saja)

Di `saveJadwal()`, sebelum membuat/memperbarui record `jadwal_harian_perubahan`, cek
apakah `JadwalHarian` ini **sudah** punya record perubahan sebelumnya:

```php
$existingPerubahan = JadwalHarianPerubahan::where('jadwal_harian_id', $jhIdLama)->first();
// $jhIdLama = id baris jadwal_harian SEBELUM dihapus oleh proses delete-recreate,
// atau — lebih baik — query berdasarkan (tanggal, poliklinik_id) sebelum transaksi delete dimulai
```

**Poin kritis:** karena `saveJadwal()` saat ini menghapus seluruh baris `jadwal_harian`
pada tanggal tsb sebelum membuat ulang ([JadwalHarianPage.php:371-373](../rsu-syifamedika/app/Filament/Resources/JadwalHarianResource/Pages/JadwalHarianPage.php#L371-L373)),
data "kondisi sebelum disimpan" untuk tiap baris **harus diambil sebelum proses delete
berjalan** — misalnya dengan query snapshot di awal `saveJadwal()`:

```php
// Sebelum DB::transaction(...) — ambil kondisi & perubahan existing per poliklinik
$existingByPoli = JadwalHarian::whereDate('tanggal', $this->activeTanggal)
    ->whereIn('poliklinik_id', $poliIds)
    ->with('perubahan')
    ->get()
    ->keyBy('poliklinik_id');
```

Lalu saat membuat record `jadwal_harian_perubahan` baru untuk sebuah baris:

- **Jika baris lama BELUM punya record `perubahan`** → capture `jam_mulai_asli`,
  `jam_selesai_asli`, `status_layanan_asli` dari nilai `jadwal_harian` yang LAMA
  (sebelum diedit jadi nilai baru)
- **Jika baris lama SUDAH punya record `perubahan`** → salin nilai `*_asli` yang sudah
  ada (jangan timpa — itulah nilai asli yang harus dipertahankan)

---

## 3 & 4. Logika Deteksi Revert + Hapus Record

Ganti blok `isSamaDenganAsli` (baris 391-408) dengan perbandingan ke `*_asli`:

```php
$nilaiAsli = $existingPerubahan
    ? [
        'jam_mulai'      => $existingPerubahan->jam_mulai_asli,
        'jam_selesai'    => $existingPerubahan->jam_selesai_asli,
        'status_layanan' => $existingPerubahan->status_layanan_asli,
      ]
    : null; // belum pernah berubah → tidak ada acuan "asli" untuk dibandingkan, berarti baris ini memang baru/GENERATE murni

$kembaliKeSemula = $nilaiAsli
    && $nilaiAsli['jam_mulai']      == $row['jam_mulai']
    && $nilaiAsli['jam_selesai']    == ($row['jam_selesai'] ?: null)
    && $nilaiAsli['status_layanan'] === $row['status_layanan'];

if ($kembaliKeSemula) {
    // tidak buat record perubahan baru — anggap normal kembali
    $sumber = 'GENERATE';
    // record jadwal_harian_perubahan lama (kalau ada) otomatis tidak dibuat ulang
    // karena seluruh baris jadwal_harian dihapus & dibuat ulang tiap save
}
```

> Karena pola saat ini adalah hapus-semua-lalu-buat-ulang, "menghapus record
> `jadwal_harian_perubahan`" terjadi otomatis lewat `cascadeOnDelete` ketika baris
> `jadwal_harian` lama dihapus. Yang perlu dipastikan: record baru **tidak dibuat lagi**
> kalau `$kembaliKeSemula === true`, sehingga hasil akhirnya benar-benar "tidak ada
> perubahan tercatat".

---

## Ringkasan Perbedaan dengan Logika Lama

| | Lama | Baru |
|---|---|---|
| Acuan "asli" | `JadwalPraktek` (bisa berubah seiring waktu) | Kolom `*_asli` di `jadwal_harian_perubahan` (snapshot tetap, diisi sekali) |
| Query tambahan | Query ke `JadwalPraktek` tiap baris saat save | Tidak ada — cukup baca relasi `perubahan` yang sudah di-load |
| Stabilitas historis | Tidak stabil (jadwal mingguan bisa berubah) | Stabil — nilai asli dikunci sejak perubahan pertama kali terjadi |
| Dependensi tabel lain | Ya (`JadwalPraktek`) | Tidak |

---

## Acceptance Criteria

- [ ] Kolom `jam_mulai_asli`, `jam_selesai_asli`, `status_layanan_asli` ada di `jadwal_harian_perubahan` (nullable)
- [ ] Saat sebuah jadwal berubah pertama kali → kolom `*_asli` terisi dengan nilai SEBELUM berubah
- [ ] Edit berikutnya pada baris yang sama → kolom `*_asli` TIDAK berubah/tertimpa
- [ ] Saat nilai yang disimpan kembali identik dengan `*_asli` → record `jadwal_harian_perubahan` tidak dibuat/dipertahankan, `sumber` kembali ke `GENERATE`
- [ ] Tidak ada lagi query ke `JadwalPraktek` di dalam `saveJadwal()` untuk mendeteksi revert
- [ ] Modal "Lihat Perubahan" tetap berfungsi normal (DITAMBAH + DIUBAH)
- [ ] Tidak menambahkan kolom `is_executive` ke `jadwal_harian_perubahan`
