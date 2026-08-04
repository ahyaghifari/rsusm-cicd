# Issue: Poster — Dukung Gaya Desain Berbeda Antar Cabang + Perbaikan Scoping RS

## Status

**Diskusi — belum diimplementasi.** Dokumen ini merangkum pembahasan lanjutan setelah fitur
Generate Poster ([poster-jadwal-poliklinik.md](poster-jadwal-poliklinik.md)) sudah berjalan
di kode (`PosterTemplate`, `PosterTemplateResource`, `GeneratePosterPage`,
`jadwal-template.blade.php`, zone editor). Pembahasan dihentikan dulu atas permintaan user,
belum ada kode yang diubah untuk topik di dokumen ini.

---

## Latar Belakang

Folder referensi `poster/` di root project berisi contoh poster nyata dari 2 cabang yang
desainnya **sangat berbeda** — karena tiap cabang RS punya humas dan desainer sendiri:

| | Banjarbaru (Aurora EC) | Barabai |
|---|---|---|
| Foto hero | Ada, besar di atas | Tidak ada |
| Layout jadwal | Grid 2 kolom + `shape_poli` PNG custom | List 1 kolom, teks polos |
| Badge Executive Clinic | Ada (beberapa dokter) | Tidak ada sama sekali |
| Gaya warna | Gradasi ungu/pink mencolok | Biru/putih, sederhana |

Sistem yang sudah ada (`tinggi_hero` config) sudah otomatis menangani "hero opsional" —
set `tinggi_hero: 0` sudah cukup untuk menyembunyikan layer hero. Tapi layout jadwal masih
hardcode ke grid 2 kolom + shape, dan form `PosterTemplateResource` mewajibkan upload
`shape_poli` — jadi template gaya Barabai (tanpa shape) **tidak bisa dibuat** sama sekali
hari ini.

---

## Temuan: Badge Executive Clinic Sudah Otomatis, Tidak Perlu Logic Baru

Awalnya dikira ini perbedaan desain yang perlu ditangani khusus per layout. Setelah ditelusuri,
ternyata **sudah ditangani otomatis lewat data**, bukan lewat tampilan:

1. `RumahSakit.executive_clinic` adalah flag per cabang, default `false`
2. Kalau `false`, toggle "Executive Clinic" di form `JadwalPraktekResource` /
   `JadwalHarianResource` otomatis disembunyikan
   (`app/Filament/Resources/JadwalPraktekResource.php:99-105`)
3. Karena toggle disembunyikan, dokter di RS tanpa fitur EC **tidak akan pernah** punya
   `is_executive = true` di database
4. Badge di poster (`jadwal-template.blade.php:384-386`) cuma render kalau `is_executive`
   true

**Kesimpulan**: kalau Barabai memang RS dengan `executive_clinic = false`, badge EC otomatis
tidak pernah muncul — bukan karena ada mode tampilan khusus, tapi karena datanya memang tidak
pernah mengandung EC. Implikasinya: partial Blade baru untuk layout polos **wajib reuse**
markup baris dokter yang sama (jam + badge EC kondisional) dari partial grid yang sudah ada,
supaya kalau suatu saat ada cabang baru yang pakai layout polos TAPI juga punya executive
clinic, badge-nya tetap otomatis jalan tanpa perlu kode tambahan.

---

## Opsi yang Dipertimbangkan untuk Layout Berbeda

### Opsi A — Terus tambah flag ke sistem yang sama (ditolak)

Tambah toggle satu-satu (shape opsional, kolom 1/2, background header opsional, dst) ke
`PosterTemplateResource` form + `jadwal-template.blade.php` yang sama. **Masalah**: makin
banyak gaya cabang baru, makin banyak flag, file Blade jadi penuh `@if`, dan Zone Editor jadi
penuh toggle yang sebagian besar tidak relevan untuk template tertentu — membingungkan humas.

### Opsi B — 2 class kode terpisah per nama cabang (ditolak)

User mengusulkan: `PosterTemplateResourceBanjarbaru`, `PosterTemplateResourceBarabai`, dst.
**Ditolak** karena:
- Menduplikasi seluruh logic yang sebenarnya identik antar cabang: `GeneratePosterPage`
  (pilih tanggal, load poli, upload hero, `buildHtml()`, render Browsershot, download PNG),
  table listing, policy, navigasi
- Bug di proses generate harus diperbaiki di tiap class — risiko drift/lupa sinkron
- Tidak skalabel: tiap RS baru (sistem ini multi-tenant, bukan cuma 2 cabang) butuh class
  baru lagi, didaftarkan manual
- Menyimpang dari pola yang sudah konsisten dipakai semua resource lain di codebase ini
  (`DokterResource`, `PoliKlinikResource`, `JadwalPraktekResource`, dst — semua **satu class
  generik** yang di-scope per RS lewat `BaseResource`/`rumahSakitId()`, bukan di-hardcode per
  nama cabang)

### Opsi C — Satu sistem, dipecah berdasarkan `layout` (gaya), bukan nama cabang ✅ Disepakati arahnya

Tambah field discriminator `config.layout`: `grid_shape` (gaya Banjarbaru) atau `list_polos`
(gaya Barabai). Tetap **satu** `PosterTemplate`, **satu** `PosterTemplateResource`, **satu**
`GeneratePosterPage` — cuma pecah di 2 titik yang memang strukturnya beda:

1. Sebagian field form `PosterTemplateResource` (`shape_poli`, kontrol kolom) disembunyikan
   via `->visible(fn (Get $get) => $get('config.layout') !== 'list_polos')` — fitur bawaan
   Filament, bukan file baru
2. Satu partial Blade baru untuk bagian render jadwal saja (lihat struktur file di bawah)

Kalau nanti ada cabang ke-3 dengan gaya benar-benar baru, tinggal tambah satu opsi layout +
satu partial baru — tidak menyentuh layout yang sudah jalan untuk Banjarbaru/Barabai.

---

## Rencana Implementasi (Layout)

1. **Field baru `config.layout`** di form `PosterTemplateResource`, Radio/Select
   (`grid_shape` | `list_polos`), `->live()`, ditaruh sebelum section "Upload Asset"
2. **`shape_poli` jadi kondisional**: `->visible()` dan `->required()` cek
   `$get('config.layout') !== 'list_polos'`
3. **Pecah render jadwal jadi 2 partial**:
   - `resources/views/filament/resources/poster-jadwal-resource/pages/partials/jadwal-grid-shape.blade.php`
     — pindahkan markup `.grid-jadwal` yang sudah ada apa adanya
   - `resources/views/filament/resources/poster-jadwal-resource/pages/partials/jadwal-list-polos.blade.php`
     — baru: nama poli jadi heading polos in-flow (pakai `warna_nama_poli`/`font_nama_poli`/
     `size_nama_poli` yang sudah ada di config, left-align, **bukan** `position:absolute`
     dicenter di atas shape yang tidak ada), lalu daftar dokter reuse markup `.dokter-row`
     yang sama (termasuk badge LIBUR & Executive Clinic)
   - Di `jadwal-template.blade.php`, ganti blok grid langsung jadi
     `@include` salah satu partial sesuai `$cfg['layout'] ?? 'grid_shape'`
4. **`PosterTemplate::defaultConfig()`**: tambah `'layout' => 'grid_shape'` — template lama
   yang sudah ada tetap jalan seperti sekarang tanpa migrasi data
5. **Background header saat tanpa shape** (`list_polos`): belum final diputuskan — lihat
   "Pertanyaan Terbuka" di bawah. Cenderung pakai color picker opsional dengan default
   transparan (teks polos), konsisten dengan pola field lain yang sudah ada di config
   (`zona_keterangan.bg_warna`, dst: kosong = transparan, isi = ada warna)

---

## Temuan Terpisah: `PosterTemplateResource` Tidak Ter-scope per RS

Ditemukan saat menelusuri kekhawatiran user soal "humas berbeda per RS" — ternyata
**bukan masalah layout**, tapi gap scoping akses yang sudah ada sejak awal:

- `PosterTemplateResource extends Resource` langsung (bukan `BaseResource`), **tidak ada**
  `getEloquentQuery()` filter `rumah_sakit_id` — beda dari semua resource lain
- `PosterTemplatePolicy` cuma cek permission role generik (`view_poster::template`, dst),
  tidak cek kepemilikan RS sama sekali
- Akibatnya: humas Banjarbaru bisa lihat & edit template milik Barabai dan sebaliknya — **tidak
  ada isolasi**
- `GeneratePosterPage::form()` field "Pilih Template"
  (`app/Filament/Pages/GeneratePosterPage.php:90-96`) menampilkan **semua** template dari
  **semua RS**, bukan cuma RS milik admin yang login

### Rencana Perbaikan Scoping (terpisah dari rencana layout, saling melengkapi)

1. `PosterTemplateResource extends BaseResource` + `getEloquentQuery()` filter
   `rumah_sakit_id` untuk non-superadmin (pola sama seperti `JadwalPraktekResource`)
2. Field `rumah_sakit_id` di form: sembunyikan/auto-isi RS milik user kalau bukan superadmin
3. `GeneratePosterPage`: filter opsi "Pilih Template" ke RS milik user yang login

Dengan ini, humas tiap cabang otomatis cuma lihat & generate dari template RS-nya sendiri —
inilah cara mendapat isolasi per cabang yang user inginkan, tanpa duplikasi kode (Opsi B).

---

## Pertanyaan Terbuka (belum diputuskan)

- [ ] **Background header saat layout `list_polos`**: teks polos tanpa background sama sekali
      (sesuai contoh Barabai persis), atau sediakan color picker opsional dengan default
      transparan (konsisten dengan pola field lain)? — diskusi terhenti sebelum disepakati
- [ ] Kontrol jumlah kolom grid (1/2) untuk layout `grid_shape` — apakah perlu UI di Zone
      Editor, atau cukup lewat config JSON manual untuk kasus yang jarang dipakai?

## Keputusan yang Sudah Disepakati

- [x] Jangan bikin class/file terpisah per nama cabang (Opsi B ditolak)
- [x] Pecah berdasarkan `layout` (gaya desain), bukan nama RS — satu Resource/Page/Model
- [x] Hero tetap pakai mekanisme `tinggi_hero` yang sudah ada (0 = sembunyikan), tidak perlu
      field `pakai_hero` terpisah seperti yang awalnya direncanakan di
      [poster-jadwal-poliklinik.md](poster-jadwal-poliklinik.md)
- [x] Badge Executive Clinic tidak butuh logic khusus per layout — otomatis dari
      `executive_clinic` flag + `is_executive` data, asal partial baru reuse markup baris
      dokter yang sama
- [x] Scoping RS untuk `PosterTemplateResource` + `GeneratePosterPage` adalah perbaikan
      terpisah yang perlu dikerjakan juga (gap yang sudah ada sejak awal, bukan akibat dari
      pembahasan layout)

## Next Step

Lanjutkan implementasi rencana di atas (layout split + perbaikan scoping) saat user siap
melanjutkan pembahasan ini.
