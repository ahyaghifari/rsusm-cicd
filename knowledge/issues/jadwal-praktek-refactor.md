# Refactor: JadwalPraktek — Restrukturisasi Jadwal Rawat Jalan

**Dibuat:** 2026-06-01  
**Status:** Planning

---

## Latar Belakang

Pada awal development, `JadwalPraktek` dimodelkan sebagai jadwal dokter menemui pasien rawat inap (1 dokter → 7 baris hari, per dokter). Setelah diskusi dengan pihak rumah sakit, ditemukan bahwa:

- **JadwalPraktek sesungguhnya = jadwal rawat jalan**, yaitu jadwal per poliklinik (1 sesi → 1 dokter di 1 poliklinik pada 1 hari).
- Struktur ini identik dengan `JadwalLayanan` yang sudah ada, sehingga `JadwalLayanan` **harus dihapus** dan `JadwalPraktek` **direkonstruksi** untuk mengambil perannya.
- `JadwalLayananHarian` tetap ada untuk saat ini, rename menjadi `JadwalHarian` dikerjakan di fase berikutnya.

---

## Ruang Lingkup Perubahan (Fase 1)

### 1. Database

#### 1a. Modifikasi tabel `jadwal_praktek`

| Kolom | Sebelum | Sesudah | Keterangan |
|---|---|---|---|
| `id` | ✅ ada | ✅ tetap | — |
| `dokter_id` | FK ke `dokter`, **NOT NULL** | FK ke `dokter`, **NULLABLE** | Sesi bisa tanpa dokter terikat |
| `hari` | enum SENIN–MINGGU | enum SENIN–MINGGU | Tetap |
| `waktu_mulai` | time NOT NULL | time **NULLABLE** | Sesi bisa belum ada jam |
| `waktu_selesai` | time NULLABLE | time NULLABLE | Tetap |
| `sesuai_perjanjian` | boolean | boolean | Tetap |
| `libur` | boolean | ❌ **DIHAPUS** | Digantikan oleh JadwalHarian kelak |
| `poliklinik_id` | ❌ tidak ada | ✅ **DITAMBAH** | FK ke `poliklinik`, NOT NULL |
| `nama_dokter` | ❌ tidak ada | ✅ **DITAMBAH** | string nullable, nama bebas jika tidak ada dokter terdaftar |
| `catatan` | ❌ tidak ada | ✅ **DITAMBAH** | text nullable |
| `timestamps` | ✅ ada | ✅ tetap | — |

**Migration baru** yang diperlukan:
```
2026_06_01_XXXXX_restructure_jadwal_praktek_table.php
```
- `$table->dropColumn('libur')`
- `$table->foreignId('poliklinik_id')->constrained('poliklinik')->cascadeOnDelete()->after('id')`
- `$table->string('nama_dokter', 255)->nullable()->after('dokter_id')`
- `$table->text('catatan')->nullable()->after('sesuai_perjanjian')`
- `$table->foreignId('dokter_id')->nullable()->change()` (ubah ke nullable)
- `$table->time('waktu_mulai')->nullable()->change()`

> ⚠️ **Data migration**: Semua data lama di `jadwal_praktek` tidak kompatibel (tidak ada `poliklinik_id`). Data lama harus di-truncate pada saat migration karena tidak bisa dipertahankan.

#### 1b. Hapus tabel `jadwal_layanan`

**Migration baru**:
```
2026_06_01_XXXXX_drop_jadwal_layanan_table.php
```
- `Schema::dropIfExists('jadwal_layanan')`

> ⚠️ Data di `jadwal_layanan` perlu dipindahkan ke `jadwal_praktek` secara manual sebelum drop, atau lewat seeder jika ada.

---

### 2. Models

#### 2a. `App\Models\JadwalPraktek` — Rewrite

Perubahan:
- Ganti `$guarded = ['id']` dengan `$fillable` eksplisit (semua kolom baru)
- Tambah `casts()`: `hari` → `Hari::class`, `waktu_mulai`/`waktu_selesai` → `datetime:H:i`, `sesuai_perjanjian` → `boolean`
- Hapus static `$hari` array (ganti dengan `Hari::cases()` di consumer code)
- Tambah relasi `poliklinik(): BelongsTo`
- Ubah relasi `dokter()` tetap ada tapi nullable

```php
// Struktur baru
protected $fillable = [
    'poliklinik_id', 'hari', 'dokter_id', 'nama_dokter',
    'waktu_mulai', 'waktu_selesai', 'sesuai_perjanjian', 'catatan',
];

protected function casts(): array {
    return [
        'hari'              => Hari::class,
        'waktu_mulai'       => 'datetime:H:i',
        'waktu_selesai'     => 'datetime:H:i',
        'sesuai_perjanjian' => 'boolean',
    ];
}

public function poliklinik(): BelongsTo { ... }
public function dokter(): BelongsTo { ... } // nullable
```

#### 2b. `App\Models\JadwalLayanan` — HAPUS

File: `app/Models/JadwalLayanan.php` → **delete**

#### 2c. `App\Models\PoliKlinik` — Update relasi

- Hapus `jadwalLayanan(): HasMany`
- Tambah `jadwalPraktek(): HasMany` (ke `JadwalPraktek`)

---

### 3. Filament Admin Panel

#### 3a. `JadwalPraktekResource` — Rewrite Total

**Halaman utama** (`JadwalPraktekDokter` → rename ke `JadwalPraktekPage`):
- UI sama seperti `JadwalLayananPage` yang sudah ada (tabel + tab hari, sudah bagus)
- Filter: pilih RS (superadmin), pilih Unit Layanan (opsional)
- Tab hari: SENIN – MINGGU
- Per tab: tabel baris editable (poliklinik, dokter, nama dokter, jam mulai, jam selesai, sesuai perjanjian, catatan)
- Tombol Tambah Baris, Simpan (replace-all per hari × poliklinik scope)
- Logic save: DELETE jadwal_praktek WHERE `hari = $activeHari` AND `poliklinik_id IN $poliIds`, lalu INSERT

**Excel page**: Tetap ada (AG Grid), pattern sama dengan `JadwalLayananExcel`

**Kolom tambahan vs JadwalLayanan**:
- `sesuai_perjanjian` (checkbox/toggle di tabel)
- `catatan`

#### 3b. `JadwalLayananResource` + semua Pages — HAPUS

Files to delete:
- `app/Filament/Resources/JadwalLayananResource.php`
- `app/Filament/Resources/JadwalLayananResource/Pages/JadwalLayananPage.php`
- `app/Filament/Resources/JadwalLayananResource/Pages/JadwalLayananExcel.php`
- `app/Filament/Resources/JadwalLayananResource/Pages/ManageJadwalLayanans.php`
- `resources/views/filament/resources/jadwal-layanan-resource/pages/jadwal-layanan-page.blade.php`
- `resources/views/filament/resources/jadwal-layanan-resource/pages/jadwal-layanan-excel.blade.php`

> ⚠️ `JadwalLayananHarianResource` tetap ada untuk saat ini (fase 2 nanti).

---

### 4. Portal Publik — Livewire Pages

#### 4a. `App\Livewire\Pages\JadwalPraktek` — Rewrite

**Konsep UI baru** (mengadopsi desain terbaik dari `JadwalPoliklinik`):

**Layout utama:**
- Tab hari (SENIN–MINGGU), default = hari ini
- Filter poliklinik (dropdown, opsional)
- Filter unit layanan (hanya jika RS punya > 1 unit layanan aktif)
- Kartu per poliklinik, mirip kartu `JadwalPoliklinik` (header berwarna = warna unit layanan, fallback tertiary)
- Di dalam kartu: list baris dokter per sesi, mirip daftar kontak WhatsApp:
  - Kiri: avatar foto dokter (jika `dokter_id` ada dan dokter punya foto), else placeholder lingkaran
  - Tengah: nama dokter (dari `nama_dokter` atau `dokter.nama`)
  - Kanan: jam praktek (`waktu_mulai` – `waktu_selesai` atau "Selesai")
  - Badge "Perjanjian" jika `sesuai_perjanjian = true`

**Data fetching** di `render()`:
```
JadwalPraktek::where('hari', $activeHari)
    ->whereHas('poliklinik.unitLayanan', fn => where RS + filter unit)
    ->with(['poliklinik.unitLayanan', 'dokter'])
    ->orderBy('waktu_mulai')
    ->get()
    ->groupBy('poliklinik_id')
```

**Properties Livewire**:
```php
public string $activeHari;
public string $unitLayananId = '';
public string $poliklinikId  = '';
```

#### 4b. `App\Livewire\Pages\JadwalPoliklinik` — HAPUS

File: `app/Livewire/Pages/JadwalPoliklinik.php` → **delete**  
View: `resources/views/rumah_sakit/pages/jadwal-poliklinik.blade.php` → **delete**

#### 4c. `App\Livewire\Dokter\Show` — Update jadwal section

**Logika baru**:
1. Fetch semua `JadwalPraktek` milik dokter ini, group by `hari`
2. Jika RS punya **1 unit layanan**: tampilkan semua hari langsung dalam accordion/tabel
3. Jika RS punya **> 1 unit layanan**: tampilkan per unit layanan → per hari

**Tampilan per sesi**:
- Nama poliklinik (karena sekarang jadwal terikat poliklinik)
- Jam: `waktu_mulai – waktu_selesai`
- Badge "Perjanjian" jika `sesuai_perjanjian = true`

**Data fetching** di `render()`:
```
JadwalPraktek::where('dokter_id', $dokter->id)
    ->with('poliklinik.unitLayanan')
    ->get()
    ->groupBy(fn ($j) => $j->hari->value) // atau per unit layanan
```

---

### 5. Routes & Navigation

#### 5a. `routes/web.php`
- **Hapus**: `Route::get('jadwal-poliklinik', ...)` → nama route `rumahsakit.jadwal_poliklinik`
- Route `jadwal-praktek` tetap ada

#### 5b. `resources/views/rumah_sakit/nav.blade.php`
- Hapus link "Jadwal Poliklinik" (desktop dropdown + mobile grid)
- "Jadwal Praktek" tetap ada

---

### 6. Views

#### 6a. `jadwal-praktek.blade.php` — Rewrite
- Desain baru: kartu per poliklinik, baris dokter WhatsApp-style
- Filter hari (tab), filter poliklinik, filter unit layanan
- State kosong yang informatif

#### 6b. `dokter/show.blade.php` — Update section "Jadwal Praktek"
- Tampilkan jadwal per unit layanan (jika >1) atau flat (jika 1)
- Tampilkan nama poliklinik di setiap baris jadwal
- Hilangkan tampilan "Libur" (sudah tidak ada kolom libur)

#### 6c. `jadwal-poliklinik.blade.php` — HAPUS

---

## Urutan Pengerjaan

```
1. Migration: restructure jadwal_praktek + drop jadwal_layanan
2. Model: update JadwalPraktek, hapus JadwalLayanan, update PoliKlinik
3. Filament: rewrite JadwalPraktekResource + Page, hapus JadwalLayananResource
4. Livewire: rewrite JadwalPraktek (portal), update Dokter/Show
5. Views: rewrite blade views
6. Routes + Nav: hapus jadwal-poliklinik
7. Testing: pastikan semua referensi ke JadwalLayanan sudah hilang
```

---

## File Index: Akan Dihapus

| File | Alasan |
|---|---|
| `app/Models/JadwalLayanan.php` | Digantikan JadwalPraktek |
| `app/Filament/Resources/JadwalLayananResource.php` | Dihapus |
| `app/Filament/Resources/JadwalLayananResource/Pages/*.php` (3 file) | Dihapus |
| `resources/views/filament/resources/jadwal-layanan-resource/pages/*.blade.php` (2 file) | Dihapus |
| `app/Livewire/Pages/JadwalPoliklinik.php` | Digantikan JadwalPraktek baru |
| `resources/views/rumah_sakit/pages/jadwal-poliklinik.blade.php` | Dihapus |

## File Index: Akan Dibuat/Ditulis Ulang

| File | Keterangan |
|---|---|
| `database/migrations/2026_06_01_..._restructure_jadwal_praktek.php` | Migrasi schema baru |
| `database/migrations/2026_06_01_..._drop_jadwal_layanan_table.php` | Hapus tabel |
| `app/Models/JadwalPraktek.php` | Rewrite |
| `app/Models/PoliKlinik.php` | Update relasi |
| `app/Filament/Resources/JadwalPraktekResource.php` | Rewrite |
| `app/Filament/Resources/JadwalPraktekResource/Pages/JadwalPraktekPage.php` | Rewrite (dari pola JadwalLayananPage) |
| `app/Filament/Resources/JadwalPraktekResource/Pages/JadwalPraktekExcel.php` | Baru |
| `resources/views/filament/resources/jadwal-praktek-resource/pages/jadwal-praktek-page.blade.php` | Rewrite |
| `resources/views/filament/resources/jadwal-praktek-resource/pages/jadwal-praktek-excel.blade.php` | Baru |
| `app/Livewire/Pages/JadwalPraktek.php` | Rewrite total |
| `resources/views/rumah_sakit/pages/jadwal-praktek.blade.php` | Rewrite total |
| `app/Livewire/Dokter/Show.php` | Update |
| `resources/views/rumah_sakit/dokter/show.blade.php` | Update section jadwal |
| `routes/web.php` | Hapus jadwal-poliklinik route |
| `resources/views/rumah_sakit/nav.blade.php` | Hapus jadwal-poliklinik link |

---

## Tidak Berubah (Fase 1)

- `JadwalLayananHarian` / `JadwalLayananHarianResource` — tetap ada, rename ke `JadwalHarian` dikerjakan Fase 2
- `PoliKlinikDetail` — tetap ada (referensi ke jadwal mingguan poliklinik akan diupdate ke JadwalPraktek)
- Semua resource lain tidak terpengaruh

---

## Catatan Desain

### Kartu Poliklinik (jadwal-praktek)
- Warna header kartu = `unitLayanan->warnaHex()` (atau `#4d51b2` jika tidak ada)
- Jika poliklinik tidak punya unit layanan (seharusnya tidak mungkin), fallback ke `tertiary`

### Baris Dokter (WhatsApp-style)
```
[ foto/avatar ]  Nama Dokter         08:00 – 12:00
                 [ Perjanjian badge ]
```
- Avatar: `Storage::url($dokter->foto)` jika ada foto, else lingkaran placeholder dengan inisial atau icon `person`
- Jika `dokter_id` null: tampilkan `nama_dokter` saja, avatar placeholder

### Filter
- **Tab hari**: selalu tampil, default = hari ini
- **Filter Poliklinik**: dropdown, tampil selalu
- **Filter Unit Layanan**: tampil hanya jika `UnitLayanan::count() > 1` untuk RS ini
