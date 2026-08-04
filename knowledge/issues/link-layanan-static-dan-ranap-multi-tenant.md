# Issue: Static-kan 3 Kartu Link Layanan + Kolom API Ranap per RS

## Status

**Implemented.** Bagian A & B sudah dikerjakan sesuai rencana di bawah, kecuali bagian yang
memang ditandai "menunggu konfirmasi" (kode API Ranap resmi & endpoint asli — masih fixture).

---

## Latar Belakang

Saat dibuat (lihat [link-layanan.md](link-layanan.md)), modul `LinkLayanan` dipakai untuk 3 kartu
"Informasi & Layanan" di halaman index RS, dengan link mengarah ke sistem eksternal SIMGOS:

| # | Label | Link saat ini (SIMGOS) |
|---|-------|-------------------------|
| 1 | Ketersediaan Ruang Rawat | `https://simgos.rsusyifamedika.co.id/apps/BedOnline/` |
| 2 | Jadwal Praktek Dokter | `https://simgos.rsusyifamedika.co.id/apps/JadwalOnline/` |
| 3 | Pantauan Antrian | `https://simgos.rsusyifamedika.co.id/apps/AntrianOnline/` |

Sekarang situasinya berubah:

- **Ketersediaan Ruang Rawat** dan **Jadwal Praktek Dokter** sudah punya halaman & route sendiri
  di portal ini (`rumahsakit.ketersediaan_rawat_inap`, `rumahsakit.jadwal_praktek`), jadi tidak
  perlu lagi keluar ke SIMGOS.
- **Pantauan Antrian** belum ada halaman internal (rencana scraping situs eksternal, belum
  dikerjakan) — untuk sekarang tetap link keluar, tapi URL-nya per RS, bukan hardcode SIMGOS milik
  RS Syifa Medika saja (ada multi-tenant, RS lain akan punya URL pantauan antrian berbeda).

Instruksi user (verbatim, jadi acuan utama):

> "...kita tidak perlu lagi link layanan ini, tapi kita tidak perlu menghapus model serta table di
> database, hanya kita ubah menjadi static saja, hal ini sama di halaman portal, menggunakan link
> layanan, kita bisa ubah static saja"

**Constraint keras**: model `LinkLayanan`, tabel `link_layanan`, Filament resource, dan seeder-nya
**TIDAK dihapus**. Hanya *pemakaiannya* di 2 tempat (index RS + halaman portal) yang diganti jadi
markup statis. Data lama yang sudah ada di tabel tetap ada tapi tidak lagi tampil otomatis di 2
tempat ini (tidak masalah — tidak ada penghapusan data, hanya tidak lagi dirender di situ).

---

## Bagian A — Static-kan 2 Lokasi Pemakaian `LinkLayanan`

### A.1 — Index RS: `resources/views/rumah_sakit/index.blade.php` (baris 221–294)

Section "Informasi & Layanan" saat ini di-loop dari `$link_layanan` (collection model, di-inject dari
`App\Livewire\RumahSakit\Index::render()` baris ~61–97). Ganti jadi 3 kartu statis dengan layout &
styling yang sama (grid 3 kolom, glassmorphism di atas gradient primary→secondary), tapi:

| # | Label | Icon | Href |
|---|-------|------|------|
| 1 | Ketersediaan Ruang Rawat | `bed` (atau `hotel`, existing) | `{{ rumahsakit_route('rumahsakit.ketersediaan_rawat_inap') }}` — route internal, **tanpa** `target="_blank"` |
| 2 | Jadwal Praktek Dokter | `calendar_clock` | `{{ rumahsakit_route('rumahsakit.jadwal_praktek') }}` — route internal, **tanpa** `target="_blank"` |
| 3 | Pantauan Antrian | `confirmation_number` (atau `groups`) | `{{ $rs->link_antrian }}` — eksternal, **pakai** `target="_blank" rel="noopener noreferrer"` (lihat Bagian B untuk kolom ini) |

Catatan implementasi:

- Section ini sebaiknya tetap muncul terus (bukan dibungkus `@if($link_layanan->count() > 0)` lagi),
  karena 2 dari 3 link sekarang selalu valid (route internal selalu ada). Untuk kartu "Pantauan
  Antrian", sembunyikan kartunya saja kalau `$rs->link_antrian` kosong (`@if($rs->link_antrian)`),
  supaya tidak ada link mati untuk RS yang belum diisi.
- Hapus variable `$fallbackIcons` (tidak perlu lagi karena cuma 3 icon fixed, tidak dirotasi).
- `Storage::url($layanan->gambar)` dihapus (tidak ada lagi upload gambar custom per kartu — pakai
  icon Material Symbols saja, konsisten dengan kartu nav lain).
- Karena `Livewire\RumahSakit\Index::render()` tidak lagi butuh meng-query `LinkLayanan` untuk
  section ini, hapus baris query `$linkLayanan = LinkLayanan::...` dan key `'link_layanan'` dari
  array view data (baris ~61 & ~97) — **tapi jangan hapus model/relasi/migration-nya**, hanya
  hapus pemakaiannya di komponen ini.

### A.2 — Halaman Portal: `resources/views/welcome.blade.php` (baris 243–250)

Di kartu setiap RS pada listing portal (`PortalController::index`), bagian "Quick links" saat ini:

```php
@foreach($rs->linkLayanan as $ll)
<a href="{{ $ll->link }}" target="_blank" rel="noopener noreferrer" ...>{{ $ll->label }}</a>
@endforeach
```

Ganti jadi 3 chip statis, konsisten dengan 2 chip yang sudah statis di atasnya ("Dokter Kami",
"Jadwal Praktek" — baris 229–242):

```php
<a href="/{{ $rs->slug }}/rawat-inap/ketersediaan-rawat-inap" onclick="event.stopPropagation()" ...>
    Ketersediaan Kamar
</a>
{{-- "Jadwal Praktek" chip sudah ada di baris 236-242, tidak perlu duplikat --}}
@if($rs->link_antrian)
<a href="{{ $rs->link_antrian }}" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation()" ...>
    Pantauan Antrian
</a>
@endif
```

Catatan: chip "Jadwal Praktek" sudah ada secara statis di kartu ini (baris 236–242) — jangan
duplikat, cukup tambahkan "Ketersediaan Kamar" dan "Pantauan Antrian" sebagai chip baru. Sesuaikan
href "Ketersediaan Kamar" dengan helper route yang benar untuk path multi-tenant (cek
`rumahsakit_route()` / cara `welcome.blade.php` membentuk href RS lain, contoh baris 229/236 pakai
`/{{ $rs->slug }}/...` literal — ikuti pola yang sama, bukan `rumahsakit_route()` yang bergantung
pada `currentRumahSakit` binding).

### A.3 — Yang TIDAK berubah

- `app/Models/LinkLayanan.php` — tetap ada, tidak diedit.
- Migration `2026_05_23_000000_create_link_layanan_table.php` — tidak ada migration baru untuk
  drop tabel ini.
- `app/Filament/Resources/LinkLayananResource.php`, `LinkLayananPolicy.php`,
  `database/seeders/LinkLayananSeeder.php` — tetap ada, admin masih bisa kelola data lama lewat
  Filament walau tidak tampil di publik lagi (bisa dipakai lagi nanti kalau ada kebutuhan lain).

---

## Bagian B — Kolom Baru di Tabel `rumah_sakit`

### Konteks

API real Ranap (ketersediaan rawat inap) memberi data per RS lewat path yang mengandung identifier
unik per cabang, contoh: `{base_url}/rsa/bed`, `{base_url}/rsb/bed`. Implementasi saat ini
(`app/Services/RanapApiClient.php` + `config/services.ranap`) masih single-tenant: 1 URL absolut
di `.env`, dan gating "RS mana yang boleh lihat data" pakai `RANAP_RUMAH_SAKIT_ID` (band-aid
sementara, lihat komentar di `KetersediaanRawatInap.php` baris 56–58 & `config/services.php`).

Dengan kolom identifier per RS, setiap RS bisa punya endpoint Ranap masing-masing tanpa perlu
gating manual via `.env`.

### B.1 — Migration

Ikuti pola "add column" yang sudah ada untuk tabel `rumah_sakit` (contoh:
`2026_06_19_144134_add_google_place_id_to_rumah_sakit_table.php`). Buat 1 migration baru,
timestamp setelah `2026_06_19_222808`:

```php
Schema::table('rumah_sakit', function (Blueprint $table) {
    $table->string('ranap_kode_api', 50)->nullable()
        ->comment('Identifier RS di URL API Ranap, contoh: "rsa" → {base_url}/rsa/bed');
    $table->string('link_antrian')->nullable()
        ->comment('URL eksternal pantauan antrian poliklinik per RS, untuk kartu "Pantauan Antrian"');
});
```

Nama kolom `ranap_kode_api` adalah usulan — tujuannya jelas membedakan dari `google_place_id` (beda
sistem). Kalau user punya preferensi nama lain (mis. `ranap_username`, `kode_ranap`), tinggal
disesuaikan sebelum migration dijalankan.

### B.2 — Model `RumahSakit.php`

Tambahkan `ranap_kode_api` dan `link_antrian` ke `$fillable` (baris 24–43).

### B.3 — `RanapApiClient` & config — ubah dari single-tenant ke per-RS

Perubahan di `app/Services/RanapApiClient.php`:

- `fetch()` perlu menerima parameter kode RS (`fetch(?string $kodeApi = null)`), lalu membentuk URL
  `"{$baseUrl}/{$kodeApi}/bed"` kalau `$kodeApi` tidak kosong. Base URL pindah jadi
  `config('services.ranap.base_url')` (bukan URL lengkap seperti sekarang).
- Kalau `$kodeApi` kosong (RS belum diisi `ranap_kode_api`) → fallback fixture lokal seperti
  sekarang (mock_path), supaya RS yang belum onboarding API tetap bisa demo dengan data dummy.

Perubahan di `app/Livewire/Pages/KetersediaanRawatInap.php` (baris 59–71): ganti gating
`$ranapRumahSakitId !== $this->rumah_sakit_id` (cek 1 ID hardcode dari `.env`) jadi cek langsung
`$rs->ranap_kode_api` ada isinya atau tidak — kalau kosong, tampilkan state kosong (bukan error),
kalau ada isi, panggil `RanapApiClient::fetch($rs->ranap_kode_api)`.

Perubahan di `config/services.php`:

```php
'ranap' => [
    'base_url' => env('RANAP_API_BASE_URL'), // tanpa trailing slash, tanpa kode RS
    'mock_path' => 'app/mock/ranap-ketersediaan.json',
],
```

`RANAP_RUMAH_SAKIT_ID` dihapus dari config & `.env.example` setelah migrasi ke pendekatan per-RS
selesai (tidak lagi relevan — sumber kebenaran sekarang `rumah_sakit.ranap_kode_api`, bukan 1 ID
global).

### B.4 — Filament: `RumahSakitResource.php`

Tambah 1 Section baru (pola sama seperti Section "Google", baris 84–97 — `collapsible()`,
superadmin-only karena ini kredensial/identifier teknis sensitif):

```php
Forms\Components\Section::make('Integrasi Eksternal')
    ->description('Identifier API ketersediaan rawat inap & link pantauan antrian, khusus RS ini.')
    ->collapsible()
    ->visible(fn () => static::isSuperAdmin())
    ->schema([
        Forms\Components\TextInput::make('ranap_kode_api')
            ->label('Kode API Ranap')
            ->maxLength(50)
            ->nullable()
            ->helperText('Identifier RS di sistem Ranap, contoh: "rsa". Kosongkan jika RS ini belum terhubung ke API Ranap (akan pakai data contoh/fixture).'),
        Forms\Components\TextInput::make('link_antrian')
            ->label('Link Pantauan Antrian')
            ->url()
            ->maxLength(255)
            ->nullable()
            ->helperText('URL situs eksternal pantauan antrian poliklinik RS ini. Ditampilkan sebagai kartu "Pantauan Antrian" di halaman utama & portal.'),
    ]),
```

### B.5 — Data untuk RS Syifa Medika Banjarbaru (RS id 1)

Setelah migration jalan, isi manual (lewat Filament atau seeder/tinker, sesuai preferensi saat
implementasi):

- `link_antrian` → `https://simgos.rsusyifamedika.co.id/apps/AntrianOnline/` (link SIMGOS yang
  sudah ada, lihat tabel di Latar Belakang).
- `ranap_kode_api` → menunggu konfirmasi kode resmi dari pemilik sistem Ranap (belum diketahui di
  planning ini — isi setelah dikonfirmasi, sebelum itu biarkan `null` supaya fallback fixture tetap
  jalan seperti sekarang).

---

## Urutan Implementasi yang Disarankan

1. Migration + model (`Bagian B.1–B.2`) — tidak ada efek visual, aman dijalankan duluan.
2. Filament Section "Integrasi Eksternal" (`B.4`) — supaya kolom bisa diisi manual sebelum lanjut.
3. Isi `link_antrian` RS Banjarbaru (`B.5`) — supaya kartu "Pantauan Antrian" di Bagian A punya data
   untuk ditest.
4. Static-kan index RS (`A.1`) dan halaman portal (`A.2`) — sekarang `$rs->link_antrian` sudah ada
   isinya untuk ditest.
5. `RanapApiClient` + `KetersediaanRawatInap` per-RS (`B.3`) — paling kompleks, dikerjakan terakhir
   karena butuh kode API asli dari Ranap untuk ditest end-to-end (sebelum itu tetap jalan di mode
   fixture, tidak blocking langkah 1–4).

## Yang Belum Diketahui / Perlu Konfirmasi Sebelum Eksekusi

- Nama kolom final: `ranap_kode_api` vs alternatif lain.
- Bentuk URL API Ranap yang sebenarnya — apakah selalu `{base}/{kode}/bed`, atau ada path lain
  (auth, query param) yang belum disebutkan.
- Kode `ranap_kode_api` resmi untuk RS Syifa Medika Banjarbaru (RS id 1) — belum ada di planning
  ini, perlu didapat dari pihak Ranap.
- Status "Pantauan Antrian" — dokumen ini menganggap masih link keluar (scraping belum dibangun).
  Kalau scraping internal mau dikerjakan duluan, kartu ini bisa langsung diarahkan ke route internal
  baru, bukan `link_antrian` eksternal — tapi itu di luar scope planning ini.
