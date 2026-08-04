# Planning Final: Tracking Perubahan JadwalHarian (1-to-1)

**Dibuat:** 2026-06-04  
**Status:** Done  
**Prioritas:** High  
**Label:** feature, tracking, jadwal

---

## Desain

Dua perubahan database:

1. **Kolom `sumber`** di `jadwal_harian` — asal baris (cron atau manual)
2. **Tabel `jadwal_harian_perubahan`** — relasi **1-to-1** dengan `jadwal_harian`

---

## Perubahan 1: Kolom `sumber` di `jadwal_harian`

```php
$table->enum('sumber', ['GENERATE', 'MANUAL'])->default('GENERATE')->after('catatan');
```

| Nilai | Artinya |
|---|---|
| `GENERATE` | Dibuat otomatis oleh cron dari JadwalPraktek |
| `MANUAL` | Ditambah langsung oleh admin |

---

## Perubahan 2: Tabel `jadwal_harian_perubahan` (1-to-1)

```php
Schema::create('jadwal_harian_perubahan', function (Blueprint $table) {
    $table->id();

    $table->foreignId('jadwal_harian_id')
          ->unique()                          // 1-to-1: satu baris = satu record
          ->constrained('jadwal_harian')
          ->cascadeOnDelete();                // hapus jadwal_harian → record ikut hapus

    $table->enum('jenis', ['GENERATE', 'TAMBAH', 'UBAH']);

    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

    // Nilai BARU setelah perubahan — diisi untuk UBAH
    $table->time('jam_mulai')->nullable();
    $table->time('jam_selesai')->nullable();
    $table->enum('status_layanan', ['BUKA', 'LIBUR'])->nullable();

    $table->text('catatan')->nullable();      // alasan perubahan

    $table->timestamps();
});
```

> `tanggal` dan `poliklinik_id` tidak disimpan di sini — sudah ada di `jadwal_harian` (parent). Cukup JOIN via `jadwal_harian_id` saat query.

---

## Tiga Jenis Record

| Jenis | Kapan | user_id | jam/status |
|---|---|---|---|
| `GENERATE` | Cron insert baris baru | null | null |
| `TAMBAH` | Admin tambah baris MANUAL | auth user | null |
| `UBAH` | Admin ubah nilai baris manapun | auth user | nilai BARU |

> Tidak ada `HAPUS` — ketika baris `jadwal_harian` dihapus, record perubahan ikut terhapus otomatis via `cascadeOnDelete`. Tidak ada tracking untuk row yang sudah dihapus.

---

## Aturan Perilaku

### Cron — Generate baris baru
```
INSERT jadwal_harian (sumber=GENERATE)
INSERT jadwal_harian_perubahan (jenis=GENERATE, user_id=null)
```

### Admin — Tambah baris manual
```
INSERT jadwal_harian (sumber=MANUAL)
INSERT jadwal_harian_perubahan (jenis=TAMBAH, user_id=auth)
```

### Admin — Ubah nilai (GENERATE maupun MANUAL)
```
UPDATE jadwal_harian (nilai baru)
UPSERT jadwal_harian_perubahan:
  updateOrCreate(
    ['jadwal_harian_id' => X],
    ['jenis' => 'UBAH', 'user_id' => auth, jam/status/catatan baru]
  )
```

> Karena 1-to-1 dan upsert, ubah berkali-kali = record yang sama diupdate terus. Hanya perubahan TERAKHIR yang tersimpan.

### Admin — Hapus baris (GENERATE maupun MANUAL)
```
DELETE jadwal_harian
  → cascadeOnDelete → jadwal_harian_perubahan ikut terhapus otomatis
```

---

## Membaca Perubahan di UI (Modal "Lihat Perubahan")

Query `jadwal_harian_perubahan WHERE tanggal = activeTanggal` + scope poliklinik RS:

```php
$perubahan = JadwalHarianPerubahan::whereHas('jadwalHarian', function ($q) use ($rsId) {
        $q->where('tanggal', $this->activeTanggal)
          ->whereHas('poliklinik.unitLayanan', fn ($q2) => $q2->where('rumah_sakit_id', $rsId));
    })
    ->with('jadwalHarian.poliklinik', 'user')
    ->get();

$ditambah = $perubahan->where('jenis', 'TAMBAH');    // sumber=MANUAL
$diubah   = $perubahan->where('jenis', 'UBAH');      // diubah dari aslinya
// GENERATE tidak ditampilkan di modal (sudah normal/default)
```

---

## Tampilan Modal

```
┌───────────────────────────────────────────────────────────┐
│  Perubahan Jadwal — Kamis, 5 Juni 2026                     │
├───────────────────────────────────────────────────────────┤
│  🟢 DITAMBAH MANUAL (1)                                   │
│  ┌───────────────────────────────────────────────────┐    │
│  │ Poli Gigi — dr. Budi — 13:00–16:00              │    │
│  │ Ditambah oleh: admin2 — 08:15                   │    │
│  └───────────────────────────────────────────────────┘    │
│                                                           │
│  🟡 DIUBAH (2)                                            │
│  ┌───────────────────────────────────────────────────┐    │
│  │ Poli Kandungan → Status: LIBUR                   │    │
│  │ Catatan: "dr. sakit"                             │    │
│  │ Diubah oleh: admin1 — 09:00                     │    │
│  ├───────────────────────────────────────────────────┤    │
│  │ Poli THT → Jam: 10:00–13:00                     │    │
│  │ Diubah oleh: admin2 — 11:15                     │    │
│  └───────────────────────────────────────────────────┘    │
│                                                           │
│  ✅ 5 baris lainnya masih sesuai generate awal            │
│                                          [Tutup]          │
└───────────────────────────────────────────────────────────┘
```

---

## File yang Perlu Dibuat / Diubah

| File | Aksi |
|---|---|
| Migration (1) | Tambah kolom `sumber` ke `jadwal_harian` |
| Migration (2) | Buat tabel `jadwal_harian_perubahan` |
| `app/Models/JadwalHarianPerubahan.php` | Model baru |
| `app/Models/JadwalHarian.php` | Fillable `sumber` + relasi `hasOne(JadwalHarianPerubahan)` |
| `GenerateJadwalHarian.php` | Insert GENERATE record saat generate |
| `JadwalHarianPage.php` | Update `addRow()`, `saveJadwal()`, tambah `openPerubahan()` |
| `jadwal-harian-page.blade.php` | Tombol + modal perubahan |

---

## Acceptance Criteria

- [ ] `jadwal_harian.sumber` (GENERATE/MANUAL) ada
- [ ] Tabel `jadwal_harian_perubahan` dengan unique constraint pada `jadwal_harian_id`
- [ ] Cron insert GENERATE record untuk setiap baris yang di-generate
- [ ] Admin tambah → sumber=MANUAL + TAMBAH record
- [ ] Admin ubah → UPSERT record (jenis=UBAH, simpan nilai baru + user + waktu)
- [ ] Hapus jadwal_harian → cascade hapus perubahan record otomatis
- [ ] Tombol "Lihat Perubahan" di header JadwalHarianPage (jika filter valid)
- [ ] Modal tampilkan TAMBAH + UBAH saja (GENERATE tidak perlu ditampilkan)
- [ ] Baris tanpa perubahan tampil sebagai "X baris sesuai generate awal"
