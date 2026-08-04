# Planning — Lanjutan Fitur Generate Poster (Diskusi 2026-06-23)

## Konteks

Lanjutan dari item **"Fitur generate poster"** di [revisi-belum-selesai.md](revisi-belum-selesai.md)
yang statusnya masih "Belum Dikerjakan". Kode dasarnya sudah ada dari pengembangan sebelumnya
(`PosterTemplate`, `PosterTemplateResource`, `GeneratePosterPage`, `jadwal-template.blade.php`,
zone editor) — didokumentasikan di [issues/poster-jadwal-poliklinik.md](../issues/poster-jadwal-poliklinik.md)
dan [issues/poster-multi-cabang-layout-dan-scoping.md](../issues/poster-multi-cabang-layout-dan-scoping.md).
Pembahasan di dokumen kedua sempat terhenti sebelum disepakati penuh — sesi ini melanjutkan
dan menambah beberapa keputusan baru. **Belum ada kode yang diubah, ini masih planning.**

---

## Verifikasi Ulang Kode (per hari ini)

Dicek langsung ke file, status terkonfirmasi:

- `PosterTemplateResource` masih `extends Resource` langsung
  ([PosterTemplateResource.php:14](../rsu-syifamedika/app/Filament/Resources/PosterTemplateResource.php#L14)),
  **tidak ada** filter `rumah_sakit_id` — humas RS A masih bisa lihat/edit template RS B.
- Field `rumah_sakit_id` di form template adalah Select semua RS aktif, bukan auto-isi RS milik
  user (beda dari pola `BaseRumahSakitResource::rsFormField()` yang sudah dipakai resource lain).
- `PosterTemplatePolicy` cuma cek permission generik, tidak cek kepemilikan RS.
- `GeneratePosterPage::form()` dropdown "Pilih Template" query semua RS, tidak difilter.
- Tidak ada field `layout` sama sekali di config/model/form/blade — render jadwal 100% hardcode
  ke gaya grid 2 kolom + shape (gaya Banjarbaru). `shape_poli` upload masih `->required()` mutlak,
  jadi template gaya Barabai (tanpa shape) **tidak bisa dibuat** hari ini.
- Referensi visual `poster/af70a84b-65ef-46a6-8d6e-de2307192959.jpeg` (Banjarbaru) dan
  `poster/Screenshot 2026-06-07 215153.png` (Barabai) dicek ulang — konfirmasi gaya Barabai
  benar-benar tanpa background/shape apapun di belakang nama poli (teks polos).

---

## Keputusan & Rekomendasi Sesi Ini

### 1. Scoping per RS — bug isolasi data, fix kecil & jelas

Tidak ada keputusan desain yang menggantung. Pola perbaikannya tinggal niru
`BaseRumahSakitResource` yang sudah dipakai resource lain (`JadwalPraktekResource`, dst):

- `PosterTemplateResource extends BaseRumahSakitResource` (bukan `Resource` langsung)
- Field `rumah_sakit_id` manual diganti `static::rsFormField()`
- Tambah `static::rsTableColumn()` / `static::rsTableFilter()` di table
- `GeneratePosterPage::form()` → filter opsi "Pilih Template" ke `rumah_sakit_id` user login
  (kecuali superadmin)

Tidak perlu migrasi. Risiko rendah — murni mengikuti pola yang sudah konsisten di codebase.

### 2. Split layout `grid_shape` vs `list_polos` — Opsi C disepakati ulang

Opsi modul/class terpisah per cabang (Opsi B di dokumen sebelumnya) ditolak ulang dengan alasan
yang sama: `GeneratePosterPage` (pilih tanggal, load poli, render Browsershot, download), table,
policy, navigasi itu ~95% identik antar cabang — modul terpisah akan menduplikasi logic itu dan
makin tidak terskala untuk RS baru (sistem multi-tenant).

**Pertanyaan terbuka yang sebelumnya menggantung** (background di belakang nama poli untuk
`list_polos`) — **sudah terjawab** dari referensi visual Barabai: tidak ada background sama
sekali, jadi cukup teks polos pakai field warna/font yang sudah ada di config (`warna_nama_poli`,
`font_nama_poli`, `size_nama_poli`), **tidak perlu** color picker tambahan.

Struktur file yang direncanakan:

```
app/Models/PosterTemplate.php
  └── defaultConfig(): tambah 'layout' => 'grid_shape'

app/Filament/Resources/PosterTemplateResource.php
  └── tambah Radio::make('config.layout') (grid_shape | list_polos), ->live()
  └── shape_poli: ->visible()/->required() jadi conditional ke config.layout

resources/views/filament/components/poster-zone-editor.blade.php
  └── x-show slider "Shape Scale" + mini-preview shape, sembunyi kalau layout = list_polos

resources/views/filament/resources/poster-jadwal-resource/pages/
  ├── jadwal-template.blade.php          (shell, @include partial sesuai layout)
  └── partials/
      ├── jadwal-dokter-list.blade.php    ← BARU, shared: loop dokter (LIBUR + badge EC),
      │                                      di-@include oleh 2 partial di bawah supaya
      │                                      markup baris dokter TIDAK terduplikasi
      ├── jadwal-grid-shape.blade.php     ← pindahkan markup grid existing apa adanya
      └── jadwal-list-polos.blade.php     ← BARU: heading nama poli polos (reuse config
                                             warna/font yang sama), lalu @include dokter-list
```

`GeneratePosterPage.php` tidak perlu diubah — switching layout 100% terjadi di Blade.

### 3. Banyak template beda warna per RS (kasus Banjarbaru) — sudah didukung arsitektur existing

Warna poster melekat ke tiap row `PosterTemplate` (`template_png` upload sendiri + field warna
di `config`), bukan hardcode di kode — jadi "banyak warna" = banyak row, sudah didukung tanpa
field/logic baru. Ini ortogonal terhadap `config.layout` (yang murni soal struktur, bukan warna).

**Saran tambahan** (belum disepakati untuk dikerjakan, masih ide): tambah
`Tables\Actions\ReplicateAction` (bawaan Filament) di `PosterTemplateResource` table — supaya
humas Banjarbaru yang sering bikin variant warna baru tidak perlu mengatur ulang semua posisi
Zone Editor dari nol setiap kali, tinggal duplikat template lama lalu ganti asset/warna.

### 4. Styling internal per-card poli (variasi 1–3 dokter per poli)

Posisi card tetap auto via CSS Grid (tidak berubah jadi manual zone per poli — sudah diputuskan
TIDAK pakai opsi itu karena kompleksitasnya tidak sepadan: butuh konsep "slot" generik + batas
jumlah poli = jumlah slot). Penyebab tampilan kurang rapi: CSS Grid men-stretch tinggi semua card
di baris yang sama secara default, tapi isi `.poli-dokter` tidak ada vertical-align — kalau poli
dengan 1 dokter sebaris dengan poli 3 dokter, dokter yang sedikit itu nempel di atas dengan ruang
kosong di bawah.

Field baru di `grid` config (hanya relevan untuk layout `grid_shape`):

| Field | Fungsi |
|---|---|
| `dokter_valign` | `top` (default, sama seperti sekarang) atau `center` |
| `dokter_row_gap` | Jarak antar baris dokter (px) — sekarang hardcode `margin-bottom: 2px` |
| `card_min_height` | Tinggi minimum card (px, 0 = tidak dipakai) |

Diekspos di Zone Editor pada section yang sama dengan card styling yang sudah ada
(`card_bg_warna`, `card_radius`, dst).

---

## Revisi Prioritas — Fokus Banjarbaru Dulu

Diputuskan: kerjakan dulu yang dibutuhkan untuk gaya **Banjarbaru** (`grid_shape` + hero),
tunda dulu layout `list_polos` (Barabai). User sadar ini berarti kemungkinan ada **rewrite**
nanti saat `list_polos` digarap, supaya kedua gaya kembali seimbang/konsisten secara struktur
kode — itu trade-off yang disepakati secara sadar (kecepatan dulu untuk kebutuhan Banjarbaru
yang mendesak, generalisasi dirapikan belakangan).

Scope yang dikerjakan sekarang (semua dari rencana di atas, minus split layout):

1. **Scoping per RS** (poin 1) — tidak terkait gaya tertentu, tetap dikerjakan apa adanya.
2. **Field `config.layout`** — tetap ditambahkan di model (`defaultConfig()`) sebagai
   discriminator dengan nilai default `'grid_shape'`, supaya pondasinya sudah ada saat
   `list_polos` digarap nanti. **Tidak** menambah Radio di form / conditional di `shape_poli`
   / partial `list_polos` / `x-show` di zone editor dulu — itu bagian yang ditunda.
3. **Styling internal card** (poin 4: `dokter_valign`, `dokter_row_gap`, `card_min_height`)
   — dikerjakan penuh, langsung di markup grid existing (belum dipecah ke partial
   `jadwal-grid-shape.blade.php` karena partial split itu baru relevan saat ada 2 layout).
4. **`ReplicateAction`** (poin 3, saran tambahan) — dikerjakan, langsung bermanfaat untuk
   workflow banyak-warna Banjarbaru.

Yang ditunda ke fase berikutnya (saat `list_polos`/Barabai mulai digarap):
- Radio `config.layout` di form + conditional `shape_poli`
- Pecah blade jadi 3 partial (`jadwal-dokter-list`, `jadwal-grid-shape`, `jadwal-list-polos`)
- `x-show` shape-specific di zone editor

---

## Status

**Belum diimplementasi.** Menunggu instruksi eksplisit untuk mulai coding. Urutan yang
direkomendasikan saat mulai (scope dipersempit ke Banjarbaru dulu, lihat "Revisi Prioritas"
di atas): (1) scoping fix → (2) field `config.layout` (default only) + styling internal card
+ `ReplicateAction`, digabung jadi satu paket karena saling terkait di file yang sama.
