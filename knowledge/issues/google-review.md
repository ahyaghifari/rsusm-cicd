# Issue: CTA Google Review (tanpa API)

## Status

**Planning final — siap diimplementasikan.** Dokumen ini awalnya merencanakan integrasi penuh
Google Places API (lihat riwayat di [revisi/google-review.txt](../revisi/google-review.txt)),
tapi **dipivot total** setelah ditemukan bahwa Google Maps Platform tetap mewajibkan kartu kredit
& aktivasi billing account meskipun pemakaiannya tidak menembus kuota gratis. Keputusan akhir:
**tidak pakai API Google sama sekali** — cukup CTA statis yang mengarahkan pengguna ke Google.

---

## Tujuan (revisi setelah pivot)

1. ~~Menampilkan rating & jumlah ulasan secara live~~ — **dibatalkan**, karena butuh API
   berbayar/billing untuk data live.
2. Tombol "Lihat Semua Review" → buka halaman review Google tempat tersebut.
3. Tombol "Tulis Review" → buka halaman resmi Google
   (`https://search.google.com/local/writereview?placeid={PLACE_ID}`).
4. Tampilan CTA didesain menarik & tidak membosankan, dengan nuansa gaya Google (4 warna brand
   Google, bintang emas) — bukan kotak abu-abu polos.
5. Tidak membangun sistem review internal — review tetap 100% di Google, website hanya
   mengarahkan (social proof + dorongan menulis ulasan).

---

## Mengapa Tanpa API

Google Maps Platform (termasuk Places API New maupun Place Details Legacy) mewajibkan billing
account aktif (kartu kredit terpasang) untuk *semua* pemanggilan API, walaupun pemakaian sebenarnya
tidak pernah menembus kredit gratis $200/bulan. Ini bukan keputusan teknis tapi keputusan
operasional — RS perlu memasang kartu kredit korporat di Google Cloud Console hanya untuk
menampilkan 2 angka (rating & jumlah ulasan) yang sebenarnya tidak wajib live.

**Solusi**: drop kebutuhan data rating numerik sepenuhnya. Tombol "Lihat Semua Review" dan "Tulis
Review" **tidak butuh API apa pun** — keduanya cukup URL biasa yang diturunkan dari `Place ID`
(didapat manual sekali lewat
[Place ID Finder](https://developers.google.com/maps/documentation/places/web-service/place-id)
resmi Google, gratis, tidak perlu API key/billing untuk *mencari* Place ID-nya, hanya untuk
*query* datanya lewat API yang justru tidak kita pakai).

---

## Perubahan Skema (jauh lebih sederhana dari rencana awal)

Migrasi baru (ikuti pola "add column" yang sudah ada untuk tabel `rumah_sakit`, lihat
`2026_06_08_000001_add_tanya_dokter_aktif_to_rumah_sakit_table.php` sebagai contoh):

| Kolom | Tipe | Catatan |
|---|---|---|
| `google_place_id` | string(255), nullable | Satu-satunya kolom baru. Diisi manual superadmin. |

Tidak ada lagi `google_rating`, `google_review_count`, `google_last_sync_at` — semua dihapus dari
rencana karena tidak ada data live yang disinkron.

---

## Perubahan Model (`app/Models/RumahSakit.php`)

- Tambah `google_place_id` ke `$fillable`.
- Tambah 2 accessor, keduanya `return null` kalau `google_place_id` kosong (dipakai untuk
  `->visible()` di Blade agar tombol tidak tampil kalau RS belum diisi Place ID):
  - `googleWriteReviewUrl()`: `https://search.google.com/local/writereview?placeid={$this->google_place_id}`
  - `googleReviewsUrl()`: `https://search.google.com/local/reviews?placeid={$this->google_place_id}`
    (halaman resmi Google yang menampilkan daftar ulasan tempat tersebut)

---

## Perubahan Filament (`app/Filament/Resources/RumahSakitResource.php`)

Tambah 1 field di Section "Google Review" baru (pola `collapsible()` sama seperti Section
"Tentang RS" yang sudah ada):

- `TextInput::make('google_place_id')` + `helperText` cara mendapat Place ID lewat Place ID Finder
  resmi Google.
- **Superadmin-only** — `->visible(fn () => static::isSuperAdmin())`, sama seperti keputusan
  sebelumnya, karena field ini tetap sensitif (salah isi = link review/tulis review menuju RS yang
  salah).

Tidak ada lagi field read-only rating/count/last-sync, tidak ada Action "Sync Sekarang" — semuanya
gugur karena tidak ada proses sync.

---

## ~~Console Command & Scheduler~~ — Dihapus dari Rencana

Tidak perlu `SyncGoogleReviews` command, tidak perlu entry baru di `routes/console.php`, tidak
perlu `GOOGLE_PLACES_API_KEY` di `.env`/`config/services.php`. Seluruh bagian ini gugur karena
tidak ada panggilan API sama sekali.

---

## Tampilan Frontend — CTA Card Bergaya Google

**Penempatan (revisi)**: bukan di footer lagi — pindah ke **halaman beranda**
(`resources/views/rumah_sakit/index.blade.php`), sebagai section baru tepat **di bawah section
FAQ** yang sudah ada (section FAQ berakhir di `@endif` sekitar baris 754, diikuti hanya oleh blok
`<script>` swiper partner — jadi section CTA ini otomatis jadi section konten terakhir di beranda,
sebelum popup `jadwal-poliklinik-popup`).

**Kondisi tampil**: card hanya render kalau `$currentRumahSakit->google_place_id` terisi — RS yang
belum diisi tidak menampilkan card kosong.

**Konsep copy (revisi — lebih ke pengalaman pelayanan, bukan "ajakan ke Google" generik)**:

Pesan diarahkan ke pengalaman pasien terhadap *layanan rumah sakit*, bukan terasa seperti
permintaan rating untuk Google semata. Google cuma jadi "kanal"-nya, bukan subjek pesannya.

- Eyebrow/badge kecil: *"Ulasan Pasien"*
- Headline: **"Bagaimana Pengalaman Anda Bersama Kami?"**
- Subteks: *"Cerita dan masukan Anda membantu kami terus meningkatkan kualitas pelayanan, serta
  membantu calon pasien lain menemukan perawatan terbaik untuk keluarganya."*
- Tombol primary: **"Tulis Ulasan Anda"** (bukan "Tulis Review" — lebih personal, "Anda" bukan
  perintah generik) → link ke `googleWriteReviewUrl()`, `target="_blank"`.
- Tombol secondary: **"Lihat Ulasan Lainnya"** (bukan "Lihat Semua Review") → link ke
  `googleReviewsUrl()`, `target="_blank"`.

**Konsep visual** (tetap mengisi requirement "tidak membosankan" + "nuansa gaya Google" sebagai
identitas kanal, bukan sebagai isi pesan):

- Card dengan aksen 4 warna brand Google (biru `#4285F4`, merah `#EA4335`, kuning `#FBBC05`, hijau
  `#34A853`) — misal gradient garis tipis di salah satu sisi card, atau 4 dot kecil warna-warni di
  pojok, bukan satu warna flat.
- Ikon "G" Google (logo resmi 4 warna, inline SVG) ditaruh kecil & sekunder (mis. di sebelah tombol
  "Tulis Ulasan Anda", bukan jadi elemen termenonjol) — cukup sebagai penanda kanal tujuan, bukan
  fokus visual utama (fokus utama tetap headline soal pengalaman pasien).
- Baris 5 bintang emas (★★★★★) sebagai elemen dekoratif, dengan animasi halus stagger fade-in
  (pola `animate-fade-in` + `animation-delay` yang sudah dipakai di kartu lain, mis.
  `jadwal-praktek.blade.php`).

Tidak perlu komponen Livewire baru — cukup partial Blade statis
(`partials/google-review-cta.blade.php`) yang di-`@include` dari `index.blade.php`, karena tidak
ada interaktivitas/state yang perlu dijaga.

---

## Batasan (tetap berlaku meski tanpa API)

- Website tidak bisa membuat/mengirim review atas nama pengguna — hanya redirect ke halaman resmi
  Google.
- Pengguna wajib login akun Google sendiri untuk menulis review.
- Tidak ada lagi keterbatasan terkait quota/billing API karena memang tidak dipakai.

---

## Keputusan yang Sudah Disepakati

- [x] **Tanpa Google Places API sama sekali** — billing account tetap wajib walau gratis,
      sehingga goal "tampilkan rating live" di-drop, fokus ke CTA "Tulis Review"/"Lihat Review".
- [x] **Akses edit `google_place_id`**: superadmin-only.
- [x] **Penempatan**: halaman beranda, section baru tepat di bawah section FAQ (bukan footer).
- [x] **Gaya visual**: card custom bertema warna Google + bintang emas + 2 tombol, bukan iframe
      bawaan Google maupun teks polos.
- [x] **Copy**: framing pengalaman pelayanan rumah sakit ("Bagaimana Pengalaman Anda Bersama
      Kami?", "Tulis Ulasan Anda") — bukan ajakan generik "share di Google".

## Pertanyaan Terbuka

- [ ] Tidak ada — implementasi tidak butuh dependency eksternal apa pun selain Place ID per RS
      yang bisa dicari sendiri kapan saja lewat Place ID Finder (gratis, tanpa billing).

---

## Tahap Implementasi

1. Migrasi: tambah kolom `google_place_id` (nullable) ke `rumah_sakit`.
2. Update model `RumahSakit`: fillable + 2 accessor (`googleWriteReviewUrl`, `googleReviewsUrl`).
3. Tambah Section "Google Review" (1 field, superadmin-only) di `RumahSakitResource`.
4. Buat partial `resources/views/rumah_sakit/partials/google-review-cta.blade.php` dengan desain
   di atas, `@include` dari `index.blade.php` tepat setelah `@endif` penutup section FAQ.
5. Testing manual: isi `google_place_id` RS Banjarbaru (cari lewat Place ID Finder), cek card
   tampil di beranda RS itu tepat di bawah FAQ, klik kedua tombol pastikan mengarah ke halaman
   Google yang benar untuk tempat yang benar.
6. Minta PIC isi Place ID untuk RS lain via Filament (superadmin).
