# Issue: Kuesioner Kepuasan Pasien → Google Review

## Status

**Planning — belum diimplementasi.**

## Sumber

Foto 2 (`WhatsApp Image 2026-06-18 at 17.07.24.jpeg`), baris 5, PIC: IT Programmer:

> **Pembahasan**: Perlu ditambahkan fitur survei/kuesioner kepuasan yang terhubung dengan Google
> Review.
> **Action Plan**: Menambahkan fitur kuesioner yang dapat mengarahkan pengguna secara otomatis
> ke halaman Google Review setelah pengisian.

Catatan singkat di foto 1 (`...17.07.23.jpeg`), "Menambahkan sistem peratingan", disimpulkan
sebagai poin yang sama dari pembahasan yang lebih awal/ringkas — bukan fitur terpisah.

Fondasi teknis (URL redirect ke Google) sudah dibangun lebih dulu di
[issues/google-review.md](google-review.md) — `RumahSakit::googleWriteReviewUrl()` dan
`googleReviewsUrl()`, keduanya tanpa API, cuma diturunkan dari `google_place_id`. Issue ini
melengkapi bagian yang belum ada: **kuesionernya sendiri**.

---

## Masalah yang Dipecahkan

Kalau kuesioner menyuruh pasien menulis ulasan teks lengkap, lalu setelah submit diarahkan lagi
ke Google untuk menulis ulasan **lagi** — pasien menulis 2 kali, capek, kemungkinan besar salah
satu di-skip. Solusinya: kuesioner cuma jadi **gerbang rating cepat (1 pertanyaan, tanpa kolom
teks)**, bukan tempat menulis ulasan. Tempat menulis teks panjang cuma ada di satu sisi:

- **Rating tinggi** → tidak perlu menulis apa-apa di kita, langsung diarahkan ke Google untuk
  menulis ulasan (di sana, sekali saja).
- **Rating rendah** → diarahkan ke form keluhan singkat **di website kita** (bukan Google) —
  supaya masukan negatif ditangani langsung secara internal, bukan jadi ulasan publik.

Jadi tidak pernah ada submit teks dua kali untuk hal yang sama.

---

## Alur Pengguna

```
Buka halaman kuesioner
        │
        ▼
"Seberapa puas Anda dengan pelayanan kami?" (1-5 bintang/emoji, TANPA kolom teks)
        │
        ├── Rating tinggi (4-5) ──► Ucapan terima kasih + tombol "Tulis Ulasan di Google"
        │                            (pakai googleWriteReviewUrl() yang sudah ada)
        │
        └── Rating rendah (1-3) ──► Form keluhan singkat (textarea, opsional nama/kontak)
                                     → submit → tersimpan ke database internal
                                     → ucapan terima kasih, TIDAK diarahkan ke Google
```

---

## Perubahan Skema

Migrasi baru, tabel `kuesioner_kepuasan`:

| Kolom | Tipe | Catatan |
|---|---|---|
| `rumah_sakit_id` | FK → rumah_sakit, cascade delete | |
| `rating` | tinyint unsigned (1-5) | Wajib diisi, ini langkah pertama yang selalu terjadi |
| `komentar` | text, nullable | Hanya terisi kalau rating rendah (form keluhan) |
| `nama` | string(100), nullable | Opsional — pasien boleh anonim |
| `kontak` | string(50), nullable | Opsional, untuk humas follow-up keluhan kalau pasien berkenan |
| `created_at` | timestamp | |

Tidak perlu `updated_at` secara fungsional (record ini append-only, tidak pernah diedit), tapi
tetap pakai timestamps default Laravel untuk konsistensi dengan tabel lain di codebase ini.

---

## Model (`app/Models/KuesionerKepuasan.php`)

- `$fillable`: `rumah_sakit_id`, `rating`, `komentar`, `nama`, `kontak`.
- `$casts`: `rating` → `integer`.
- `rumahSakit()`: `belongsTo(RumahSakit::class)`.
- `scopeRendah($query)` / `scopeTinggi($query)`: helper query `where('rating', '<=', 3)` /
  `where('rating', '>=', 4)` — dipakai di Filament untuk filter cepat.

---

## Livewire Component (`app/Livewire/Pages/KuesionerKepuasan.php`)

`RsPortalComponent`, dengan state mesin sederhana (bukan multi-page, satu komponen 2-3 tampilan):

- Property `?int $rating = null`, `string $step = 'rating'` (`rating` → `redirect_google` |
  `feedback_form` → `selesai`).
- `submitRating(int $rating)`: simpan `$this->rating`; kalau `>= 4` set `step =
  'redirect_google'`; kalau `<= 3` set `step = 'feedback_form'`. Belum simpan ke DB di langkah
  ini kalau rating tinggi (tidak perlu — tidak ada komentar yang perlu disimpan)... **atau**
  tetap simpan baris rating-only ke DB supaya humas tetap punya data agregat rating walau pasien
  puas (lihat Pertanyaan Terbuka).
- `submitFeedback()`: validasi `komentar` wajib (kalau kosong, minimal kasih placeholder/required
  basic), simpan row ke `kuesioner_kepuasan` dengan `rating` + `komentar` + `nama`/`kontak`
  opsional, set `step = 'selesai'`.

---

## View (`resources/views/rumah_sakit/pages/kuesioner-kepuasan.blade.php`)

3 kondisi tampilan berdasar `$step`, pola `x-data`/`wire:click` sederhana (mirip toggle viewMode
di `jadwal-praktek.blade.php`):

1. **`rating`**: 5 tombol bintang/emoji besar, `wire:click="submitRating(n)"`.
2. **`redirect_google`**: pesan terima kasih + tombol besar "Tulis Ulasan di Google" (gaya CTA
   tertiary yang sudah dipakai di tombol "Daftar Sekarang"/CTA Google Review beranda) →
   `$rs->googleWriteReviewUrl()`, `target="_blank"`. Kalau RS belum punya `google_place_id`,
   tampilkan ucapan terima kasih polos tanpa tombol (fallback aman).
3. **`feedback_form`**: textarea keluhan (required) + input nama/kontak (nullable) + tombol
   submit → `submitFeedback()`.
4. **`selesai`** (setelah submit feedback): ucapan terima kasih, tidak ada tombol Google.

---

## Filament Resource (`app/Filament/Resources/KuesionerKepuasanResource.php`)

Read-only untuk humas/admin — bukan tempat input data (data masuk dari publik lewat Livewire),
jadi:

- `extends BaseRumahSakitResource` (RS-scoped seperti biasa).
- `canCreate()`: `false` — tidak ada tombol "Buat", data hanya masuk dari form publik.
- `canEdit()`: `false` — feedback pasien tidak diedit, cuma dibaca.
- `canDeleteAny()`/`canDelete()`: boleh `true` untuk superadmin (mis. hapus spam/data uji).
- Table: kolom `rating` (badge warna — hijau 4-5, kuning 3, merah 1-2), `komentar`, `nama`,
  `kontak`, `created_at`. Filter `TernaryFilter`/`SelectFilter` rating rendah vs tinggi (pakai
  scope `rendah()`/`tinggi()`).
- `navigationGroup`: bisa digabung ke grup yang sama dengan FAQ/Media Informasi, atau grup baru
  "Kepuasan Pasien" — lihat Pertanyaan Terbuka.

---

## Routes

```php
Route::get('kuesioner-kepuasan', App\Livewire\Pages\KuesionerKepuasan::class)
    ->name('rumahsakit.kuesioner_kepuasan');
```

---

## Hubungan dengan CTA Google Review yang Sudah Ada

CTA langsung ("Tulis Ulasan Anda"/"Lihat Ulasan Lainnya") di beranda & footer (dari
[google-review.md](google-review.md)) **tetap dipertahankan apa adanya** — itu jalur cepat untuk
pasien yang sudah pasti mau menulis ulasan tanpa perlu ditanya dulu. Kuesioner ini jadi **jalur
kedua**, untuk kasus yang ingin lebih dulu menyaring sentimen sebelum mengarahkan ke Google
(sesuai action plan asli). Keduanya tidak saling menggantikan.

---

## Batasan & Risiko

- **Kebijakan Google soal "review gating"**: secara ketat, Google melarang menyaring secara
  selektif siapa yang diarahkan menulis ulasan publik berdasarkan rating internal dulu. Ini
  praktik umum dipakai bisnis kecil-menengah (comment card/kiosk serupa), risiko penegakan rendah
  untuk skala RS ini, tapi tetap perlu disadari sebagai nuansa kebijakan, bukan dianggap 100%
  "aman menurut aturan resmi".
- Pasien rating rendah tetap **tidak dilarang** menulis ulasan publik kalau memang mau — kuesioner
  ini hanya tidak *menyarankan* jalur Google untuk mereka, sementara CTA langsung (lihat section
  di atas) tetap ada di tempat lain buat siapa pun yang mau langsung ke Google tanpa lewat
  kuesioner ini.

---

## Keputusan yang Sudah Disepakati

- [x] Kuesioner cuma 1 pertanyaan rating (bintang/emoji), **tanpa kolom teks** di langkah
      pertama — supaya tidak ada duplikasi menulis ulasan 2 kali.
- [x] Rating tinggi → redirect ke Google (pakai accessor yang sudah ada, tanpa API).
- [x] Rating rendah → form keluhan singkat, disimpan internal, **tidak** dikirim ke Google.
- [x] CTA Google Review langsung yang sudah ada di beranda/footer **tidak dihapus** — kuesioner
      ini fitur tambahan, jalur kedua.

## Pertanyaan Terbuka

- [ ] **Simpan rating pasien puas juga ke DB, atau cukup redirect tanpa nyimpen apa-apa?**
      Kalau disimpan, humas bisa lihat agregat rating keseluruhan (termasuk yang puas) — bukan
      cuma daftar keluhan. Kalau tidak, tabel `kuesioner_kepuasan` isinya cuma keluhan (lebih
      simpel, tapi tidak ada gambaran rating keseluruhan).
- [ ] **Field `nama`/`kontak` di form keluhan — wajib, opsional, atau dihapus sama sekali (anonim
      total)?** Wajib bikin pasien lebih nyaman jujur kalau anonim, tapi humas tidak bisa
      follow-up keluhan personal. Opsional adalah jalan tengah yang sudah ditulis di rencana ini.
- [ ] **Di mana link ke halaman kuesioner ini ditaruh?** Beranda (section terpisah dari CTA
      Google yang sudah ada), footer, atau dikirim manual lewat WhatsApp/SMS pasca-kunjungan
      (di luar scope website)? Catatan rapat tidak menyebutkan titik masuknya secara spesifik.
- [ ] **Navigation group Filament untuk `KuesionerKepuasanResource`** — gabung ke grup yang sudah
      ada atau grup baru "Kepuasan Pasien"?

---

## Tahap Implementasi

1. Migrasi: buat tabel `kuesioner_kepuasan`.
2. Model `KuesionerKepuasan` + factory untuk testing.
3. Livewire component `KuesionerKepuasan` (3 state: rating → redirect_google / feedback_form →
   selesai).
4. View `kuesioner-kepuasan.blade.php` — 4 kondisi tampilan sesuai `$step`.
5. Route `rumahsakit.kuesioner_kepuasan`.
6. Filament Resource read-only untuk humas/superadmin review keluhan.
7. Tentukan & pasang link masuk ke halaman ini (tergantung jawaban Pertanyaan Terbuka).
8. Unit test model (cast rating, scope rendah/tinggi, RS-scoping, cascade delete) — mengikuti
   pola test yang sudah ada di `tests/Unit/Models/`.
