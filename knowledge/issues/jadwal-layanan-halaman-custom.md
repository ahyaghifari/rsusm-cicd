# Planning: Halaman Custom Jadwal Layanan (Filament)

Halaman khusus di Filament untuk mengelola `JadwalLayanan` dengan tampilan spreadsheet
menyerupai Excel. Admin RS terbiasa dengan format spreadsheet, sehingga pendekatan ini
dipilih untuk memudahkan input data jadwal poliklinik per hari.

---

## Konsep Utama

- Hari (SENIN–MINGGU dari `App\Enums\Hari`) ditampilkan sebagai **tab/sheet**
- Setiap tab menampilkan **tabel baris** berisi jadwal poliklinik di hari tersebut
- Mekanisme simpan menggunakan **replace-all per hari**: saat klik "Simpan", seluruh
  jadwal hari aktif dihapus lalu diinsert ulang dari state tabel saat ini
- Tidak ada logika update/diff — lebih sederhana dan tidak rentan bug
- Tersedia mode **fullscreen** agar admin bisa fokus mengisi jadwal tanpa distraksi sidebar

---

## Pola Role: Super Admin vs Admin RS

Mengikuti pola yang sudah ada di `DokterResource` (via `BaseResource`):

| Kondisi | Perilaku |
|---|---|
| Super admin (`isSuperAdmin() = true`) | Tampilkan dropdown pilih RS. RS belum dipilih → tabel tidak muncul |
| Admin RS (`isSuperAdmin() = false`) | `rumah_sakit_id` langsung diambil dari `JadwalLayananResource::rumahSakitId()` (yaitu `auth()->user()->rumah_sakit_id`). Tidak ada dropdown RS |

Referensi helper yang tersedia via `BaseResource`:
```php
JadwalLayananResource::isSuperAdmin()   // true jika super admin
JadwalLayananResource::rumahSakitId()   // ambil rumah_sakit_id dari user yang login
```

Pola ini identik dengan `CreateDokter::mutateFormDataBeforeCreate()` dan
`DokterResource` form yang memakai `visible(fn() => static::isSuperAdmin())`.

---

## File yang Dibuat / Dimodifikasi

```
[BARU]   app/Filament/Resources/JadwalLayananResource/Pages/JadwalLayananPage.php
[BARU]   resources/views/filament/resources/jadwal-layanan-resource/pages/jadwal-layanan-page.blade.php
[UBAH]   app/Filament/Resources/JadwalLayananResource.php  ← ganti route index ke page baru
```

---

## 1. JadwalLayananPage.php

### Properties (State Livewire)

| Property | Tipe | Default | Keterangan |
|---|---|---|---|
| `$selectedRumahSakitId` | `?int` | `null` | Super admin: RS yang dipilih via dropdown. Admin RS: diisi otomatis di `mount()` dari `rumahSakitId()`, tidak pernah berubah |
| `$selectedUnitLayananId` | `?int` | `null` | Filter unit layanan. Disembunyikan jika RS hanya punya 1 unit aktif |
| `$activeHari` | `string` | `'SENIN'` | Tab hari yang sedang aktif |
| `$rows` | `array` | `[]` | Array baris tabel. Tiap item adalah 1 calon jadwal layanan |
| `$isFullscreen` | `bool` | `false` | Toggle fullscreen — mengubah class container di blade |

Struktur satu item `$rows`:
```php
[
    'poliklinik_id'  => null,   // ID poliklinik (wajib diisi)
    'dokter_id'      => null,   // ID dokter dari tabel dokter (opsional, untuk auto-fill nama)
    'nama_dokter'    => null,   // Nama dokter — bisa diisi manual atau auto-fill dari dokter_id
    'jam_mulai'      => null,   // Format HH:MM (wajib diisi)
    'jam_selesai'    => null,   // Format HH:MM (opsional — jika kosong tampil "Selesai" di frontend)
    'status_layanan' => 'BUKA', // Enum StatusLayanan: BUKA|LIBUR (wajib diisi)
]
```

---

### Methods

#### `mount(): void`
```
- Cek role user:
  - Jika admin RS (bukan super admin):
      → $selectedRumahSakitId = JadwalLayananResource::rumahSakitId()
        (langsung dikunci, tidak muncul dropdown RS di UI)
  - Jika super admin:
      → $selectedRumahSakitId = null (menunggu pilihan dropdown)
- Set $activeHari = 'SENIN'
- Panggil loadRows() — jika super admin dan belum pilih RS, rows tetap kosong
```

#### `getActiveRumahSakitId(): ?int`
```
- Helper internal untuk mendapatkan RS id yang sedang aktif
- Return $selectedRumahSakitId (berlaku untuk keduanya karena admin RS
  sudah di-assign di mount())
```

#### `getPoliklinikOptions(): array`
```
- Kembalikan ['id' => 'nama'] poliklinik yang aktif
- Filter: whereHas unitLayanan → rumah_sakit_id = getActiveRumahSakitId()
- Jika $selectedUnitLayananId ada → tambah filter unit_layanan_id
- Digunakan untuk dropdown kolom Poliklinik di setiap baris tabel
```

#### `getDokterOptions(): array`
```
- Kembalikan ['id' => 'nama'] dokter yang aktif milik RS aktif
- Filter: rumah_sakit_id = getActiveRumahSakitId(), aktif = true
- Digunakan untuk dropdown kolom Dokter di setiap baris tabel
```

#### `loadRows(): void`
```
- Jika getActiveRumahSakitId() null → set $rows = [], return (belum ada RS dipilih)
- Query JadwalLayanan:
    → WHERE hari = $activeHari
    → whereHas poliklinik.unitLayanan WHERE rumah_sakit_id = getActiveRumahSakitId()
    → jika $selectedUnitLayananId ada: tambah WHERE unit_layanan_id = $selectedUnitLayananId
    → with(['dokter']) untuk eager load relasi dokter
- Map hasil ke format array $rows (ambil field sesuai struktur rows di atas)
- Jika kosong → $rows = []
```

#### `updatedActiveHari(): void`
- Hook Livewire: otomatis dipanggil saat nilai `$activeHari` berubah (tab diklik)
- Panggil `loadRows()` untuk memuat data hari yang baru dipilih

#### `updatedSelectedRumahSakitId(): void`
- Reset `$selectedUnitLayananId = null` dan `$rows = []`
- Dipanggil saat super admin mengganti pilihan RS di dropdown

#### `updatedSelectedUnitLayananId(): void`
- Panggil `loadRows()` ulang setelah filter unit layanan berubah

#### `updatedRows(mixed $value, string $key): void`
```
- Hook Livewire: dipanggil otomatis setiap kali nilai dalam $rows berubah
- $key berformat "{index}.{field}", contoh: "0.dokter_id" atau "2.poliklinik_id"
- Yang perlu ditangani: jika field yang berubah adalah 'dokter_id':
    → Parse index dari $key
    → Jika $value (dokter_id) tidak null:
        cari nama dokter: Dokter::find($value)?->nama
        set $this->rows[$index]['nama_dokter'] = nama dokter tersebut
    → Jika $value null:
        set $this->rows[$index]['nama_dokter'] = null (kosongkan)
- Ini adalah auto-fill: pilih dokter → nama_dokter langsung terisi otomatis,
  tapi pengguna tetap bisa edit nama_dokter secara manual setelahnya
```

#### `addRow(): void`
```
- Push 1 item kosong ke $rows dengan default status_layanan = 'BUKA'
- Baris ini belum ada di DB — hanya di state Livewire sampai saveJadwal() dipanggil
```

#### `removeRow(int $index): void`
```
- Hapus item di $rows[$index]
- Reindex array dengan array_values() agar tidak ada gap index
  (gap index bisa menyebabkan wire:model salah bind)
```

#### `toggleFullscreen(): void`
```
- Toggle nilai $isFullscreen (true ↔ false)
- Di blade: jika $isFullscreen = true, container halaman diberi class CSS
  yang menjadikannya fixed fullscreen (lihat bagian Blade View)
```

#### `saveJadwal(): void`
Logika utama simpan jadwal (replace-all per hari):

```
1. Guard: getActiveRumahSakitId() harus tidak null → abort 403 jika tidak ada

2. Filter baris blank: lewati baris di mana poliklinik_id = null
   (baris yang ditambah tapi belum diisi sama sekali — tidak dihitung, tidak error)

3. Validasi baris yang tersisa (poliklinik_id tidak null):
   - jam_mulai wajib ada
   - status_layanan wajib ada
   → Jika ada yang invalid: kirim Filament Notification error dengan info baris ke-berapa,
     lakukan return (batalkan save, tidak ada perubahan di DB)

4. DB::transaction:
   a. Kumpulkan semua poliklinik_id yang ada di RS + unit layanan aktif
      (untuk scope DELETE agar tidak kena RS lain)
   b. DELETE FROM jadwal_layanan
      WHERE hari = $activeHari
      AND poliklinik_id IN (daftar poli RS aktif)
   c. INSERT ulang tiap baris valid dari $rows ke tabel jadwal_layanan

5. Reload rows dari DB via loadRows() (sinkronisasi state dengan DB)
6. Kirim Filament Notification sukses
```

Catatan `jam_selesai`: jika kosong, disimpan sebagai `null` di DB.
Di tampilan frontend nanti nilai null ini akan ditampilkan sebagai **"Selesai"**.

---

## 2. Blade View

### Struktur Layout

```
[Tombol Fullscreen — pojok kanan atas]       [⛶ Fullscreen / ✕ Keluar Fullscreen]

[Panel Filter]
┌────────────────────────────────────────────────────────────────┐
│ Rumah Sakit : [Dropdown ▼]  ← hanya tampil jika super admin   │
│ Unit Layanan: [Dropdown ▼]  ← tampil jika RS punya > 1 unit   │
└────────────────────────────────────────────────────────────────┘

[Tab Hari]
┌──────┬─────────┬──────┬───────┬────────┬────────┬────────┐
│SENIN*│ SELASA  │ RABU │ KAMIS │ JUMAT  │ SABTU  │ MINGGU │
└──────┴─────────┴──────┴───────┴────────┴────────┴────────┘
(* = tab yang aktif, diberi highlight)

[Tabel Jadwal per Hari Aktif]
┌───┬──────────────┬───────────────┬──────────────┬───────────┬─────────────┬──────────┬──────┐
│ # │ Poliklinik * │ Dokter        │ Nama Dokter  │ Jam Mulai*│ Jam Selesai │ Status * │  ─   │
├───┼──────────────┼───────────────┼──────────────┼───────────┼─────────────┼──────────┼──────┤
│ 1 │ [Select ▼]  │ [Select ▼]   │ [Text input] │ [HH:MM]   │ [HH:MM]     │ [Select] │  🗑  │
│ 2 │ [Select ▼]  │ [Select ▼]   │ [Text input] │ [HH:MM]   │ [HH:MM]     │ [Select] │  🗑  │
└───┴──────────────┴───────────────┴──────────────┴───────────┴─────────────┴──────────┴──────┘

                                                            [+ Tambah Baris]

                                                      [💾 Simpan Jadwal SENIN]

Tanda * = kolom wajib diisi
```

### Detail Kolom Input

| Kolom | Tipe Input | Wajib | Keterangan |
|---|---|---|---|
| Poliklinik | `<select wire:model="rows.{i}.poliklinik_id">` | Ya | Option dari `getPoliklinikOptions()` |
| Dokter | `<select wire:model="rows.{i}.dokter_id">` | Tidak | Option dari `getDokterOptions()`. Saat dipilih → `nama_dokter` auto-fill via `updatedRows()` |
| Nama Dokter | `<input type="text" wire:model="rows.{i}.nama_dokter">` | Tidak | Bisa diisi manual atau hasil auto-fill dari kolom Dokter. User tetap bisa edit manual |
| Jam Mulai | `<input type="time" wire:model="rows.{i}.jam_mulai">` | Ya | Format HH:MM |
| Jam Selesai | `<input type="time" wire:model="rows.{i}.jam_selesai">` | Tidak | Kosong = null di DB = tampil "Selesai" di frontend |
| Status Layanan | `<select wire:model="rows.{i}.status_layanan">` | Ya | Opsi: BUKA / LIBUR |
| Hapus (🗑) | `<button wire:click="removeRow({i})">` | — | Hapus baris dari array $rows |

### Fullscreen Mode

Property `$isFullscreen` mengontrol class pada wrapper container di blade:

```blade
{{-- Wrapper utama halaman --}}
<div class="{{ $isFullscreen ? 'fixed inset-0 z-50 overflow-auto bg-white dark:bg-gray-900 p-6' : '' }}">

    {{-- Tombol toggle fullscreen --}}
    <button wire:click="toggleFullscreen">
        @if($isFullscreen) ✕ Keluar Fullscreen @else ⛶ Fullscreen @endif
    </button>

    {{-- ... isi halaman (filter, tab, tabel) ... --}}
</div>
```

Saat `$isFullscreen = true`: container menutupi seluruh viewport (posisi fixed, z-index tinggi),
menyembunyikan sidebar Filament secara visual. User fokus pada tabel jadwal saja.

---

## 3. Aturan Validasi Sebelum Simpan

| Kondisi Baris | Perlakuan |
|---|---|
| `poliklinik_id` null DAN semua field lain kosong | **Skip** — baris blank, diabaikan tanpa error |
| `poliklinik_id` ada, tapi `jam_mulai` kosong | **Error** — notifikasi "Baris ke-N: Jam Mulai wajib diisi", batalkan save |
| `poliklinik_id` ada, tapi `status_layanan` kosong | **Error** — notifikasi "Baris ke-N: Status wajib diisi", batalkan save |
| Semua field wajib terisi, `jam_selesai` kosong | **Valid** — simpan dengan `jam_selesai = null` |
| `dokter_id` diisi, `nama_dokter` auto-terisi | **Valid** — keduanya disimpan |
| `dokter_id` kosong, `nama_dokter` diisi manual | **Valid** — `dokter_id = null`, `nama_dokter = teks` |
| Semua field terisi lengkap | **Valid** — disimpan normal |

---

## 4. Keamanan Multi-Tenancy

- Admin RS: `$selectedRumahSakitId` di-assign di `mount()` dari `JadwalLayananResource::rumahSakitId()`.
  Tidak ada input RS di UI, tidak bisa dimanipulasi
- Super admin: `$selectedRumahSakitId` dari dropdown, required sebelum tabel muncul
- Semua query (load, delete, insert) selalu di-scope ke RS aktif via `getActiveRumahSakitId()`
- Operasi DELETE di-scope ke `poliklinik_id IN (poli milik RS aktif)` — tidak mungkin
  menghapus data RS lain secara tidak sengaja

---

## 5. Modifikasi JadwalLayananResource.php

Ganti `getPages()` agar halaman index mengarah ke halaman custom baru.
Resource CRUD standar (`ManageJadwalLayanans`) digantikan sepenuhnya.

```php
public static function getPages(): array
{
    return [
        // Halaman index sekarang adalah halaman custom spreadsheet
        'index' => Pages\JadwalLayananPage::route('/'),
    ];
}
```
