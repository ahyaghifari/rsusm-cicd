# RSU Syifa Medika — Sistem Informasi Rumah Sakit

Sistem informasi multi-tenant untuk manajemen dan portal publik rumah sakit. Satu instalasi dapat melayani beberapa rumah sakit sekaligus, masing-masing dengan slug tersendiri, konten independen, dan akun admin yang terisolasi.

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Framework | Laravel 12 |
| Admin Panel | Filament 3.x |
| Reactive UI | Livewire 3 |
| Real-Time | Laravel Reverb (WebSocket self-hosted) + Laravel Echo (Pusher protocol) |
| Web Push | minishlink/web-push v10 (VAPID, server-side push notification saat browser tertutup) |
| Styling | Tailwind CSS v4 |
| Asset Bundler | Vite |
| Database | MySQL (dev & prod) |
| Icons | Material Symbols (Google Fonts) |
| Animasi | AOS (Animate On Scroll) |
| Searchable Select | Tom Select 2.x (CDN) |
| Slider | Swiper.js |
| Full-text Search | MySQL FULLTEXT index (LIKE fallback SQLite) |

---

## Arsitektur

Project ini menggunakan arsitektur **multi-tenant berbasis kolom** (`rumah_sakit_id`). Semua entitas konten dimiliki oleh satu `RumahSakit`. Akses data dibatasi di level resource Filament dan middleware route.

```
RumahSakit (slug, executive_clinic, ranap_kode_api, link_antrian, google_place_id)
├── User (admin rumah sakit, terikat ke 1 RS)
├── Dokter → Spesialis                          (SoftDeletes)
├── Spesialis                                   (SoftDeletes)
├── PoliKlinik                                  (SoftDeletes, slug unik per RS)
│   └── JadwalPraktek (per poliklinik, per hari, opsional per dokter, is_executive)
├── JadwalHarian (override harian per tanggal, is_executive)
│   └── JadwalHarianPerubahan (1-to-1, tracking perubahan status)
├── PosterTemplate (background PNG, logo, shape_poli, config zona JSON)
├── KelasRawatInap (master kelas, id_kelas_api opsional, is_vip)
├── RawatInap → Gedung (kelas_rawat_inap_id FK ke KelasRawatInap)
│   ├── GambarRawatInap
│   └── FasilitasRawatInap
├── LayananUnggulan
├── FasilitasPendukung
├── PenunjangMedis
├── Banner
├── Promo (slug unik per RS)
├── Halaman statis (slug unik per RS, field: kata_kunci)
├── Magazine
├── Faq (field: kata_kunci)
├── Partner
├── Kontak (kategori: SOSIAL MEDIA | OPERASIONAL | PENDAFTARAN)
└── LinkLayanan
```

> **Arsitektur jadwal:** `JadwalPraktek` adalah jadwal rawat jalan per **poliklinik** (bukan per dokter).
> `JadwalHarian` adalah override untuk tanggal spesifik, dapat dimuat otomatis dari `JadwalPraktek` via cron.
>
> **Executive Clinic:** Flag `executive_clinic` di `RumahSakit` mengaktifkan fitur jadwal executive. Field `is_executive` di setiap baris jadwal menandai sesi executive dengan tampilan warna brand `#e8cd84`.

### Role

| Role | Akses |
|---|---|
| `super_admin` | Semua rumah sakit, semua resource |
| `admin` | Hanya rumah sakit milik akun tersebut |
| `humas` | Akses terbatas (konten publik) |
| `informasi` | Akses terbatas (informasi umum) |

---

## Fitur

### Halaman Landing (`/`)

- Welcome section bergradasi dengan background gambar RS
- Search & filter: pilih RS + filter spesialis dokter (Tom Select, AJAX)
- Promo aktif semua RS dengan filter per RS
- jQuery **dihapus** — semua interaksi vanilla JavaScript

### Portal Publik (`/{slug-rs}/...`)

| Route | Halaman |
|---|---|
| `/` | Beranda rumah sakit |
| `/dokter-kami` | Daftar & cari dokter (urut A–Z, filter nama + spesialis) |
| `/dokter-kami/{dokter}` | Profil dokter + jadwal praktek |
| `/jadwal-praktek` | Jadwal praktek (Per Hari / Per Poli) |
| `/rawat-jalan` | Daftar poliklinik |
| `/rawat-jalan/{poliklinik}` | Detail poliklinik + jadwal |
| `/rawat-inap` | Informasi kelas rawat inap |
| `/ketersediaan-rawat-inap` | Ketersediaan kamar real-time (per bed, filter kelas & nama kamar) |
| `/unggulan` | Layanan unggulan |
| `/fasilitas-pendukung` | Fasilitas pendukung |
| `/penunjang-medis` | Penunjang medis |
| `/partner-kami` | Mitra rumah sakit |
| `/hubungi-kami` | Kontak & lokasi |
| `/promo` | Daftar promo aktif |
| `/promo/{slug}` | Detail promo |
| `/info/{slug}` | Halaman statis CMS |
| `/magazine` | Arsip majalah digital |
| `/artikel` | Daftar artikel & berita |
| `/artikel/{slug}` | Detail artikel |
| `/tanya-dokter` | Mulai sesi konsultasi chat dengan dokter |
| `/konsultasi/{token}` | Jendela chat sesi konsultasi (akses via token, tanpa login) |
| `/faq` | FAQ |

#### Halaman Jadwal Praktek

Dua mode tampilan:

| Mode | Deskripsi |
|---|---|
| **Per Hari** | Tab SENIN–MINGGU, kartu per poliklinik. Filter poliklinik via Tom Select. |
| **Per Poli** | Pilih poliklinik → tampilkan semua 7 hari (grid horizontal di desktop). |

- Badge **Sesuai Perjanjian** (hijau) ditampilkan jika `sesuai_perjanjian = true`
- Badge **Executive Clinic** (warna brand `#e8cd84`) ditampilkan jika RS mengaktifkan `executive_clinic` dan sesi memiliki `is_executive = true`
- Sesi executive dikelompokkan per dokter — semua slot jam tampil sekaligus dalam satu baris
- Di bawah jadwal terdapat **disclaimer** dengan nomor kontak PENDAFTARAN (klik langsung `tel:` / WhatsApp)
- Nama dokter yang terhubung ke profil dapat diklik → navigasi ke profil dokter
- Tombol **"Daftar Sekarang"** (link ke `link_pendaftaran_online` milik RS) tampil di halaman ini dan di halaman profil dokter (`dokter/show.blade.php`)

#### Global Search

- Tombol search di navbar atau shortcut **Ctrl+K**
- Spotlight modal real-time, debounce 350ms
- Mencari: Dokter (nama + spesialis), Poliklinik, Promo, FAQ, Halaman Statis
- MySQL: FULLTEXT boolean mode dengan partial match (`word*`)
- SQLite/test: fallback LIKE
- Hasil dikelompokkan per kategori, klik langsung navigasi

#### Chatbot Asisten ("Tanya Syifa")

- **Mobile**: tombol di bottom bar, panel fullscreen
- **Desktop**: FAB floating bottom-right dengan tooltip animasi, panel setinggi layar
- State persisten: cabang, riwayat chat (max 100 pesan), session key — bertahan saat navigasi maupun reload
- Typing indicator (3 dots) saat menunggu respons AI
- Smart scroll: scroll ke awal bubble bot saat respons panjang
- Input langsung kosong saat kirim, disabled saat loading
- AI backend via N8N webhook (`N8N_URL` env)
- **Rate limiting 2 lapis**: burst (maks. pesan dalam jendela singkat) + kuota harian — keduanya reset otomatis via `RateLimiter`, angka diatur sebagai konstanta agar mudah disesuaikan dengan kuota Gemini
- **Opsi pemulihan saat respons gagal**: tombol restart percakapan, kirim ulang pesan terakhir, dan daftar kontak langsung (kategori OPERASIONAL/PENDAFTARAN — di luar emergency, hotline, sosial media)

#### Tanya Dokter — Konsultasi Chat Real-Time

- **Tanpa login**: pasien mengisi nama + kontak, pilih dokter yang `tersedia_konsultasi`, lalu sesi (`SesiKonsultasi`) dibuat dengan token UUID — diakses lewat URL `/{rumahsakit}/konsultasi/{token}` (`getRouteKeyName() => 'token'`)
- **Real-time via Laravel Reverb**: status sesi (`MENUNGGU` → `BERLANGSUNG` → `SELESAI`/`KEDALUWARSA`) dan pesan chat (`KonsultasiPesan`) tersinkron langsung di kedua sisi tanpa reload — event `SesiStatusBerubah` & `PesanDikirim` di-broadcast lewat channel publik `konsultasi.{token}` dan private channel `konsultasi.dokter.{dokterId}`
- **1 sesi aktif per pasien**: cookie `konsultasi_sesi_{rumah_sakit_id}` (umur = `durasi_sesi_menit` milik dokter) menyimpan token sesi aktif pasien — membuka kembali halaman "Tanya Dokter" saat masih ada sesi aktif akan otomatis redirect ke sesi tsb, alih-alih membuat sesi baru
- **Push notification 3 lapisan** saat dokter membalas: (1) in-app toast via Alpine.js global listener (`globalKonsultasiListener` di layout `rumah_sakit.blade.php`) saat pasien di halaman lain dalam tab yang sama; (2) `new Notification()` browser notification API jika tab tidak aktif (`document.hidden`); (3) Web Push via service worker (`public/sw.js`) + VAPID + `minishlink/web-push` saat browser tertutup sepenuhnya — subscription disimpan di kolom `sesi_konsultasi.push_subscription`, pengiriman via `SendWebPushNotification` job (queue)
- **Banner izin push notifikasi**: custom Alpine component `pushPermisiBanner(token)` — gradient violet/indigo dengan animasi bel, muncul hanya jika `Notification.permission === 'default'` dan belum di-skip via `localStorage`; browser dialog hanya dipicu saat klik tombol "Aktifkan", bukan otomatis
- **Kesimpulan/catatan dokter**: dokter mengisi kesimpulan di form modal sebelum mengakhiri sesi; pasien dapat membaca di halaman chat setelah sesi selesai; dokter dapat mengedit kembali dari halaman Riwayat
- **Rate limiting pesan pasien**: 10 pesan per 60 detik per token sesi via `RateLimiter`
- **Dokter — KonsultasiDashboard**: toggle ketersediaan, antrean sesi live dengan preview pesan terakhir + badge unread merah (`belum_dibaca` via `withCount` + `whereRaw` berdasarkan `dokter_baca_at`), warna kartu berbeda untuk BERLANGSUNG vs MENUNGGU, terima sesi, jendela chat balasan (`wire:poll.visible.5s` sebagai jaring pengaman — lihat [reverb/06](../reverb/06-race-condition-subscribe-channel-dinamis.md)), form kesimpulan sebelum akhiri
- **Dokter — RiwayatKonsultasi**: daftar sesi selesai/kedaluwarsa, pencarian nama pasien, transkrip percakapan, edit kesimpulan
- Dokumentasi belajar lengkap (konsep dasar Reverb/Pusher/Echo, push notification VAPID, dan setiap bug nyata yang ditemukan & cara memperbaikinya) ada di [reverb/](../reverb/)
- **Panel Filament terpisah untuk Dokter** (`DokterPanelProvider`, path `/dokter`, login sendiri) — KonsultasiDashboard & RiwayatKonsultasi di atas diakses lewat panel ini, bukan panel admin utama

#### Artikel & Berita

- CRUD per RS: `ArtikelResource` (rich text editor, gambar cover, kategori, tanggal publish, toggle unggulan/aktif) + `KategoriArtikelResource` (CRUD sederhana via modal)
- Halaman publik list (`/artikel`): artikel unggulan ditonjolkan di atas, grid 3 kolom untuk sisanya, pagination
- Halaman detail (`/artikel/{slug}`): konten lengkap + section "Artikel Lainnya" (3 artikel terbaru selain yang sedang dibuka)
- Diurutkan berdasarkan `tanggal_publish` (terbaru dulu) — **tidak** ada `sort_order` manual seperti Magazine/FAQ
- Slug unik per RS (composite), bukan global — sama seperti Promo/Spesialis/Halaman/PoliKlinik
- Link ada di dropdown navigasi "Media Informasi" (sejajar Syifa Magazine & FAQ), juga di grid menu mobile
- Detail implementasi lengkap: [issues/artikel-berita.md](../issues/artikel-berita.md)

#### Popup Otomatis (Homepage)

- **Popup Jadwal Poliklinik**: admin upload gambar (`jadwal_poliklinik_gambar` — biasanya hasil dari Generate Poster) + toggle `jadwal_poliklinik_aktif` lewat widget dashboard Filament (`JadwalPoliklinikPopupWidget`). Saat aktif, tampil sebagai modal fullscreen di homepage publik setelah delay 1.8 detik
- **Popup Promo**: widget dashboard serupa (`PromoPopupWidget`) untuk mengaktifkan/nonaktifkan popup promo yang sudah ada di homepage
- Kedua widget hanya terlihat untuk role `super_admin`, `admin`, `humas`, `informasi`

#### CTA Google Review

- Tombol "Tulis Ulasan Anda" & "Lihat Ulasan Lainnya" di homepage (bawah section FAQ) dan di footer (bawah embed Google Maps)
- Redirect langsung ke halaman resmi Google Business Profile RS berdasarkan `google_place_id` — **tanpa** form survei/filter kepuasan perantara (pasien tidak puas pun bisa langsung menulis ulasan publik)
- Rating yang didorong adalah rating **per rumah sakit** (mengikuti Google Business Profile), bukan breakdown per dokter/layanan individual
- Detail keputusan scope: [issues/google-review.md](../issues/google-review.md)

#### Ketersediaan Rawat Inap (Real-Time, Tanpa Database)

- Data ketersediaan kamar (per **tempat tidur**, bukan per kamar — 1 kamar bisa punya beberapa bed) diambil **langsung** dari API Ranap setiap kali halaman di-render (termasuk tiap `wire:poll.30s`) — **tidak ada tabel cache**, supaya tidak ada risiko data basi
- **Multi-tenant per RS**: tiap RS punya identifier sendiri di kolom `rumah_sakit.ranap_kode_api` (mis. `"rsa"`), disambung `RanapApiClient` jadi `{base_url}/{kode}/bed`. RS yang kolomnya masih kosong otomatis fallback ke fixture lokal (`storage/app/mock/ranap-ketersediaan.json`) — jadi halaman tetap bisa di-demo sebelum kode API resmi didapat dari pihak Ranap
- **Status bed**: 1 Kosong, 2 Reservasi, 3 Terisi, 6 Sedang Perbaikan (enum `StatusKetersediaanKamar`) — kode di luar daftar ini dapat label fallback "Status Tidak Dikenal", status `0` di-skip total dari hasil
- **`KelasRawatInap`**: tabel master kelas per RS (gantikan kolom `kelas` string bebas yang lama di `RawatInap`) — dipakai untuk dropdown kelas di admin **dan** untuk resolve nama kelas dari `idKelas` milik API (`id_kelas_api`, nullable, unique per RS)
- **Filter**: dropdown Kelas & Nama Kamar pakai Tom Select (`_searchable-select` partial, sama seperti filter spesialis di Dokter Kami) — opsi diambil dari hasil fetch saat render, diterapkan di memori (Collection PHP), bukan query SQL
- **Toggle tampilan**: Per Kamar (default) atau Per Kelas — mengubah pengelompokan grid hasil
- **Countdown visual** 30 detik selaras dengan `wire:poll` — pakai trik `wire:key` yang berubah tiap render supaya Alpine di-mount ulang dan timer reset, bukan cuma berhenti di 0
- Tombol silang-tautan dengan halaman `/rawat-inap` (masing-masing mengarah ke satu sama lain)
- Detail desain & keputusan: [issues/ketersediaan-rawat-inap-plan.md](../issues/ketersediaan-rawat-inap-plan.md), [issues/link-layanan-static-dan-ranap-multi-tenant.md](../issues/link-layanan-static-dan-ranap-multi-tenant.md)

#### Preview 360° Kamar Rawat Inap

- **`RawatInap.foto_360`** (string, nullable) — path foto panorama equirectangular (disk `public`), 1 kamar maksimal 1 foto. Diupload admin lewat `RawatInapResource` (section "Fasilitas & Tampilan"), dilengkapi panduan crop rasio 2:1 (`imageEditorAspectRatios`) dan batas ukuran 10MB
- **Badge "360°"** tampil di kartu kamar publik (`components/rawat-inap.blade.php`) hanya kalau `foto_360` terisi — kamar yang belum difoto ulang (progres fisik masih berjalan terpisah) tetap tampil normal tanpa elemen kosong/error
- **Viewer**: Photo Sphere Viewer (`@photo-sphere-viewer/core`, di-bundle global via `resources/js/app.js` → `window.PSVViewer`, mengikuti pola integrasi library pihak ketiga yang sudah baku di project ini — lihat `Swiper`/`window.Swiper`). Validasi integrasi awal dilakukan lewat spike teknis di route `/test-360-viewer` sebelum schema & Filament field dibangun
- **Modal kustom (bukan Preline `hs-overlay`)**: sempat dicoba pakai `hs-overlay`, tapi menyebabkan beberapa instance `PSVViewer` ter-create bersamaan (kedip-kedip/glitch) — karena script init viewer di halaman ini ikut di-render ulang Livewire tiap filter kelas diubah, dan `document.addEventListener` jadi menumpuk listener tiap kali script-nya ikut di-reinsert oleh morph DOM. Diganti modal vanilla (atribut `onclick` di markup + `function` declaration biasa, bukan `addEventListener`) — redefinisi function aman/idempotent, beda sifat dari `addEventListener`
- **Modal dirender di level halaman** (`rawat-inap.blade.php`), bukan di dalam kartu kamar — kartu punya `hover:-translate-y-1` (CSS `transform`), dan ancestor dengan transform jadi *containing block* untuk descendant `position: fixed`, sehingga modal yang ditaruh di dalam kartu akan "fixed" relatif ke kartu yang ter-hover (bukan ke viewport)
- **Aksesibilitas**: `role="dialog"` + `aria-modal` + `aria-labelledby`, fokus otomatis ke tombol tutup saat modal terbuka dan dikembalikan ke tombol trigger saat ditutup, focus trap (Tab tidak bisa keluar dialog), `motion-reduce:transition-none` untuk pengguna yang mengaktifkan *reduce motion*
- Detail implementasi, keputusan scope (1 kolom vs tabel galeri terpisah), dan saran lanjutan: [issues/preview-360-kamar-rawat-inap.md](../issues/preview-360-kamar-rawat-inap.md), spike awal: [issues/test-360-viewer.md](../issues/test-360-viewer.md)

#### Live Antrian Poliklinik per Dokter

- **`Dokter.nomor_poli_antrian`** (integer, nullable) — identifier dokter ini di sistem antrian eksternal. `Dokter.kuota_pasien` (text, nullable) — info kuota/ketersediaan rawat jalan, free text, tampil di profil dokter publik
- **`AntrianApiClient`**: fetch live ke `{rumah_sakit.link_antrian}/api/public/poli/{nomor_poli_antrian}` — base URL-nya **reuse kolom `link_antrian`** yang sama dengan kartu "Pantauan Antrian" (bukan kolom/env terpisah), jadi otomatis multi-tenant per RS
- **Basic Auth**: kredensial **global** (sama untuk semua RS) dari `config('services.antrian.username'/'password')` (env `ANTRIAN_API_USERNAME`/`ANTRIAN_API_PASSWORD`)
- **Tanpa cache**: status diambil langsung tiap halaman profil dokter di-render, sama seperti pola `RanapApiClient`. Kalau base URL kosong, request gagal, atau timeout — fungsi return `null` (dicatat sebagai log warning) dan blok status di halaman publik otomatis tidak ikut tampil, tidak ada error yang terlihat pengunjung
- **Admin (`DokterResource`)**: section "API Antrian" — field `nomor_poli_antrian` + tombol "Tes" (`suffixAction`) yang langsung fetch API pakai nilai yang sedang diketik (belum perlu disimpan) dan tampilkan respons mentah (ID, nama poli, nama dokter, status) via notifikasi — supaya admin tahu nomornya benar sebelum disimpan. Section ini **hanya terlihat untuk role `super_admin` dan `admin`**
- Respons API yang diharapkan: `{id, nama_poli, nama_dokter, status}` — field `id` ditafsirkan sebagai nomor antrian yang sedang berjalan

#### Homepage RS

Seksi berurutan:
1. Hero Carousel (Banner)
2. Link Layanan Digital
3. Tentang Kami
4. Layanan Unggulan
5. Dokter Kami (3 random)
6. CTA "Siap Melayani" — tombol buat janji + emergency + hotline
7. Partner & Rekanan (Swiper slider)
8. Promo & Penawaran (kondisional)

#### Footer

- **Kiri**: Logo RS, alamat, ikon sosial media (kategori `SOSIAL MEDIA`)
- **Kanan**: Kontak operasional sebagai card dengan link `tel:`/`wa.me`

#### Mobile Bottom Bar

Bar sticky di bawah layar (mobile only) dengan tombol:
- **Emergency** — klik langsung `tel:`
- **Hotline** — klik langsung `tel:`
- **Asisten** — buka/tutup chatbot

---

### Admin Panel

Path admin dikonfigurasi via env: `ADMIN_PATH=manage` (default). Akses di `/{ADMIN_PATH}`.

> **Keamanan**: Jangan gunakan `/admin` sebagai path di production. Set `ADMIN_PATH` ke nilai yang tidak mudah ditebak.

| Modul | Keterangan |
|---|---|
| Rumah Sakit | CRUD data RS, logo, gambar, about, `executive_clinic` (super admin only) |
| Dokter | Manajemen dokter + foto + deskripsi + kuota pasien (SoftDelete + Restore). Section "API Antrian" hanya untuk role `super_admin`/`admin` |
| Spesialis | Spesialisasi per RS (SoftDelete + Restore) |
| Poliklinik | Klinik per RS (SoftDelete + Restore) |
| **Jadwal Praktek** | Jadwal rawat jalan per poliklinik — 2 mode editable, kolom `is_executive` |
| **Jadwal Harian** | Override jadwal harian + tracking perubahan status, kolom `is_executive` |
| **Poster Template** | Upload background PNG, logo, shape; zone editor drag-drop interaktif |
| **Generate Poster** | Form pilih template + tanggal → generate PNG 1080×1920 via Browsershot |
| Artikel & Berita | CRUD artikel + rich text editor, kategori, gambar cover, toggle unggulan/aktif |
| Kategori Artikel | CRUD kategori per RS (modal sederhana) |
| Rawat Inap | Kelas kamar (relasi ke Kelas Rawat Inap), fasilitas, galeri foto |
| **Kelas Rawat Inap** | Master data kelas per RS, opsional `id_kelas_api` (cocokkan ke API Ranap) + toggle `is_vip` |
| Gedung | Manajemen gedung |
| Banner | Spanduk promosi beranda |
| Promo | Promosi + popup (slug unik per RS) |
| Halaman Statis | CMS halaman statis (kata_kunci, slug unik per RS) |
| Majalah | Upload majalah digital |
| FAQ | Kelola FAQ dengan `sort_order` dan `kata_kunci` |
| Layanan Unggulan | Highlight layanan andalan (drag-drop sort) |
| Fasilitas Pendukung | Fasilitas non-medis (drag-drop sort) |
| Penunjang Medis | Lab, radiologi, farmasi (drag-drop sort) |
| Partner | Mitra & rekanan |
| Kontak | Nomor telepon, WhatsApp, sosial media (4 kategori, termasuk RAWAT INAP) |
| Link Layanan | Tautan cepat (drag-drop sort) — **model/resource tetap ada**, tapi sudah tidak ditampilkan otomatis di homepage/portal (lihat catatan di bawah) |
| User | Manajemen akun admin + penugasan RS |

> **Catatan**: section "Informasi & Layanan" di homepage & kartu RS di portal listing sekarang **statis** (3 link fixed: Ketersediaan Kamar, Jadwal Praktek, Pantauan Antrian via `link_antrian`), bukan lagi di-loop dari `LinkLayanan`. Tabel/model/resource `LinkLayanan` **tidak dihapus** — datanya tetap bisa dikelola, hanya tidak lagi otomatis tampil di 2 lokasi itu. Detail: [issues/link-layanan-static-dan-ranap-multi-tenant.md](../issues/link-layanan-static-dan-ranap-multi-tenant.md)

#### Jadwal Praktek — Fitur Khusus

- **Urutan filter**: RS → Mode (Per Hari / Per Dokter)
- **Mode Per Hari**: tab SENIN–MINGGU, tabel baris editable (HTML table + Tom Select)
  - Poliklinik dan Dokter menggunakan Tom Select untuk kemudahan pencarian
  - Checkbox `sesuai_perjanjian` dan `is_executive` sync langsung via `$wire.set()`
  - Simpan: replace-all per hari × scope poliklinik RS
- **Mode Per Dokter**: dropdown dokter di area konten, tabel jadwal lintas semua hari
  - Poliklinik menggunakan Tom Select
  - Simpan: replace-all WHERE `dokter_id` dalam scope RS
- **Validasi `waktu_mulai`**: wajib diisi kecuali `sesuai_perjanjian` dicentang (berlaku di form Resource maupun kedua mode tabel — `saveJadwal()` & `saveDokterJadwal()`)
- **Layar Penuh**: sembunyikan sidebar Filament
- Rows menggunakan UUID key untuk identifikasi unik

#### Jadwal Harian — Fitur Khusus

- **Tom Select** untuk poliklinik dan dokter di setiap baris
- **Status Layanan**: BUKA / LIBUR (enum `StatusLayanan`)
- **Sumber**: GENERATE (dari cron) / MANUAL (input admin)
- **Tracking Perubahan** via `JadwalHarianPerubahan` (1-to-1 per baris):
  - Record perubahan dibuat HANYA jika sumber GENERATE dan status bukan BUKA
  - Manual tidak membuat record perubahan
  - **Snapshot nilai asli** (`jam_mulai_asli`, `jam_selesai_asli`, `status_layanan_asli`): di-capture sekali saat perubahan pertama kali terjadi, dan tidak ditimpa pada edit berikutnya
  - **Deteksi "kembali ke semula"**: dibandingkan terhadap kolom `*_asli` di `jadwal_harian_perubahan` sendiri — **bukan** dengan query ke `JadwalPraktek` (agar histori tidak bergantung pada perubahan jadwal mingguan di kemudian hari). Jika sama persis, record perubahan dihapus otomatis dan `sumber` kembali ke `GENERATE`
- **Penanda visual Executive**: baris `is_executive = true` di tabel admin diberi highlight background amber + ikon bintang pada kolom No, agar mudah dibedakan dari baris reguler
- **Cron**: `php artisan jadwal:generate-harian` berjalan otomatis `daily at 00:05`
  - Memuat dari JadwalPraktek sesuai hari
  - Skip jika jadwal harian untuk tanggal + poliklinik sudah ada
- Query tanggal menggunakan `whereDate()` agar kompatibel dengan MySQL dan SQLite
- Rows menggunakan UUID key

#### Sort Order

6 resource mendukung drag-drop reorder: Magazine, LinkLayanan, LayananUnggulan, FasilitasPendukung, PenunjangMedis, Gedung. Kolom `sort_order` tidak muncul di form — dikelola via drag.

#### Kontak — Kategori

| Kategori | Tampil di |
|---|---|
| `SOSIAL MEDIA` | Ikon di footer kiri |
| `OPERASIONAL` | Card di footer kanan + halaman Hubungi Kami |
| `PENDAFTARAN` | Disclaimer halaman Jadwal Praktek + footer kanan |
| `RAWAT INAP` | Disclaimer halaman Ketersediaan Rawat Inap (dedicated, **bukan** reuse `PENDAFTARAN` lagi) + ikut tampil di footer kanan/Hubungi Kami |

#### Slug Uniqueness

Slug bersifat unik **per RS** (composite unique), bukan global:
- `promo`: unique `(slug, rumah_sakit_id)`
- `spesialis`: unique `(slug, rumah_sakit_id)`
- `halaman`: unique `(slug, rumah_sakit_id)`
- `poliklinik`: unique `(slug, rumah_sakit_id)`
- `artikel`, `kategori_artikel`: unique `(slug, rumah_sakit_id)`
- `dokter`: **diperbaiki** dari unique global → composite `(slug, rumah_sakit_id)` — bug lama yang menyebabkan RS kedua gagal insert dokter dengan nama sama (lihat migrasi `consolidate_dokter_table_alterations.php` — sejak pembersihan migrasi 2026-06-21, lihat [issues/migration-cleanup-plan.md](../issues/migration-cleanup-plan.md))

---

## Security

| Fitur | Detail |
|---|---|
| Rate Limiting | `public-api`: 20 req/menit, `portal`: 100 req/menit (per IP) |
| Rate Limiting Chatbot AI | 2 lapis via `RateLimiter`: burst (maks. N pesan / X menit) + kuota harian (maks. N pesan / 24 jam), key gabungan IP + session, reset otomatis. Angka diatur sebagai konstanta di `Chatbot\Panel` |
| Security Headers | `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `X-XSS-Protection`, `Permissions-Policy` |
| Content-Security-Policy | Mode Report-Only (default) atau enforced via env `CSP_ENFORCE=true`, daftar sumber eksternal di `SecurityHeaders` middleware |
| Session Encryption | `SESSION_ENCRYPT=true` — payload session dienkripsi di DB |
| Proxy Trust | `TRUSTED_PROXIES` via env (bukan hardcode `'*'`) |
| Admin Path | Dikonfigurasi via `ADMIN_PATH` env, default `manage` |
| Livewire `#[Locked]` | Semua property server-side yang dipakai sebagai filter query dilindungi |
| Input Validation | `/cari-spesialis`: `alpha_dash`, max 100 chars |
| Custom 429 Page | Halaman Too Many Requests dengan countdown |

---

## Performa & SEO

| Fitur | Detail |
|---|---|
| Sitemap XML | Otomatis dari database per cabang RS aktif (`/sitemap.xml` index + `/{rumahsakit}/sitemap.xml`), di-cache 6 jam via `Cache::remember` |
| Lazy Loading Gambar | `loading="lazy"` pada gambar below-the-fold (kartu, list, popup) di seluruh halaman publik — hero/logo above-the-fold tetap eager agar tidak menunda render awal |

---

## Instalasi

### Prasyarat

- PHP 8.2+
- Composer
- Node.js 20+ & npm
- MySQL (rekomendasi) / PostgreSQL / SQLite

### Langkah

```bash
# 1. Clone & masuk direktori
git clone <repo-url>
cd rsu-syifamedika

# 2. Install dependencies
composer install
npm install

# 3. Salin environment
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_DATABASE=rsusm_db
# DB_USERNAME=root
# DB_PASSWORD=...
#
# ADMIN_PATH=manage           # path admin panel (ganti di production)
# SESSION_ENCRYPT=true
# TRUSTED_PROXIES=            # IP load balancer di production
# N8N_URL=https://...         # Webhook N8N untuk chatbot AI

# 5. Migrasi & seed
php artisan migrate:fresh --seed

# 6. Storage link
php artisan storage:link

# 7. Build assets
npm run build
# atau development:
npm run dev

# 8. Jalankan
php artisan serve

# 9. (Opsional) Fitur Tanya Dokter — jalankan di terminal terpisah:
php artisan reverb:start            # WebSocket server (real-time chat)
php artisan queue:work              # Worker untuk Web Push notification (Layer 3)
# Di production: gunakan Supervisor — lihat reverb/08-production-reverb-queue-setup.md
```

Admin panel tersedia di `/{ADMIN_PATH}` (default: `/manage`).

Akun yang dibuat otomatis oleh `DatabaseSeeder`:
- **Superadmin** — `test@example.com` / `password`
- **Admin RS** — `ahyaghifari288@gmail.com` / `password`

### Testing

```bash
php artisan test
# 301+ tests passing (Unit + Feature)
```

---

## Konvensi Kode

- Resource Filament extends `BaseResource` / `BaseRumahSakitResource` untuk scoping otomatis
- Super admin cek via `BaseResource::isSuperAdmin()`
- Livewire public property yang dipakai sebagai filter query **wajib** diberi `#[Locked]`
- Livewire `boot()` dipakai (bukan `mount()`) untuk restore RS context lintas request
- Blade portal menggunakan Livewire full-page components
- Tailwind v4: `bg-linear-to-r` (bukan `bg-gradient-to-r`), warna via CSS custom property
- Route di Livewire view: gunakan `route('name', ['rumahsakit' => $rsSlug])`, **bukan** `rumahsakit_route()` (tidak bekerja di AJAX)
- Tom Select via CDN — diintegrasikan dengan Livewire via `wire:ignore` + `$wire.set()` eksplisit
- Checkbox di tabel editable: gunakan `@change="$wire.set(...)"` bukan `wire:model` (menghindari timing conflict dengan Tom Select)
- Query kolom tanggal: selalu gunakan `whereDate()` bukan `where()` (kompatibel MySQL & SQLite)
- `sesuai_perjanjian` dan `is_executive` disimpan dengan `(bool)` cast — **bukan** perbandingan `=== '1'`
- Rows di JadwalPraktekPage dan JadwalHarianPage menggunakan UUID string sebagai array key
- Poster: asset lokal dikonversi ke data URI base64 sebelum dirender di Browsershot (file:// tidak diizinkan)
- Reverb/Echo: `namespace: ''` **wajib** di konfigurasi `new Echo({...})` (`resources/js/echo.js`) — Echo defaultnya menambahkan prefiks `App.Events` yang tidak cocok dengan `broadcastAs()` nama pendek; nama event di `broadcastAs()` & `#[On('echo:...')]` harus sama persis (lihat [reverb/05](../reverb/05-mismatch-namespace-echo-broadcastas.md))
- Reverb/Echo: listener Livewire dengan placeholder channel **dinamis** (`#[On('echo:topik.{propertiYangBerubah},Event')]`) punya jendela rawan *race condition* saat propertinya berubah — pertimbangkan `wire:poll.visible.Ns` sebagai jaring pengaman (lihat [reverb/06](../reverb/06-race-condition-subscribe-channel-dinamis.md))
- Web Push/VAPID: `sesiAktifToken` di `KonsultasiDashboard` harus `string` (bukan `?string`) karena placeholder `#[On('echo:konsultasi.{sesiAktifToken},...')]` tidak bisa di-resolve Livewire jika nilainya null — nilai kosong menghasilkan channel yang tidak pernah dipakai
- Web Push: route yang di-pass ke job (`SendWebPushNotification`) wajib menyertakan parameter `rumahsakit` (slug) karena URI multi-tenant — eager-load `rumahSakit` di `sesiAktif()` sebelum dispatch
- `push_subscription` disimpan sebagai TEXT JSON mentah; dibersihkan dari DB otomatis saat endpoint kadaluwarsa (laporan `sendNotification` dicek di job)
- **`RsPortalComponent` + `wire:poll`/interaksi AJAX berulang tidak cocok**: `RumahSakitMiddleware` (yang men-set binding `currentRumahSakit` ke container) cuma jalan di request awal (full page load), **tidak** jalan di `/livewire/update` (request AJAX untuk `wire:poll` & tiap `wire:click`/`wire:model` setelahnya) — pakai `RsPortalComponent::boot()` di komponen yang sering re-render via AJAX akan `BindingResolutionException` setelah interaksi pertama. Solusi: simpan `rumah_sakit_id` sebagai `#[Locked] public int` lalu re-bind manual di `boot()` komponen itu sendiri (`if (! app()->bound('currentRumahSakit')) { ... }`) — lihat `KetersediaanRawatInap.php` & `RawatInap.php` (kedua Livewire page ini sengaja **tidak** extends `RsPortalComponent` karena alasan ini)
- `RumahSakit.link_antrian` dipakai **dua kali** untuk dua tujuan berbeda: (1) URL klik langsung untuk kartu publik "Pantauan Antrian", dan (2) base URL `AntrianApiClient` untuk fetch JSON live status. Kalau salah satu kebutuhan berubah formatnya, kolom ini perlu dipecah jadi dua
- Test Filament `Panel` butuh instance yang benar-benar terdaftar (`Filament\Facades\Filament::getPanel('admin')`), **bukan** `app(\Filament\Panel::class)` — instance kosong dari container tidak punya `id()`, akan error `Exception: A panel has been registered without an id()` begitu kode memanggil `$panel->getId()` (mis. di `User::canAccessPanel()`)
- **Modal Preline (`hs-overlay`) tidak cocok untuk komponen yang sering di-render ulang Livewire** (mis. halaman dengan filter `wire:click`/`wire:model`) kalau init JS-nya pakai `document.addEventListener` — script yang sama ikut di-render ulang tiap interaksi, dan morph DOM Livewire kadang me-reinsert tag `<script>` itu sehingga browser mengeksekusinya ulang, menambah listener baru (bukan mengganti yang lama). Listener menumpuk → efek sampingnya tergantung apa yang di-trigger (di kasus viewer 360 rawat inap: beberapa instance `PSVViewer` dibuat bersamaan ke container yang sama, kelihatan kedip-kedip cepat). Solusi yang dipakai: modal vanilla dengan trigger lewat atribut `onclick` di markup (diparse ulang dari HTML, tidak pernah menumpuk) + `function namedFunction() {}` biasa (redefinisi idempotent, beda sifat dari `addEventListener`) — lihat `rawat-inap.blade.php` (`openModal360`/`closeModal360`) dan [issues/preview-360-kamar-rawat-inap.md](../issues/preview-360-kamar-rawat-inap.md)
- **Ancestor dengan CSS `transform` jadi *containing block* untuk descendant `position: fixed`** — kartu/elemen apa pun yang punya `hover:scale-*`/`hover:-translate-y-*` dkk akan membuat modal/elemen `fixed` di dalamnya "nempel" relatif ke elemen itu (bukan ke viewport) selama transform-nya aktif (mis. saat di-hover), bukan benar-benar full-screen. Modal yang perlu benar-benar `fixed` ke viewport harus dirender di luar elemen manapun yang punya `transform`, biasanya di level halaman/root, bukan di dalam komponen kartu yang reusable
- **Migrasi yang sudah pernah `migrate` di environment manapun (termasuk production) tidak
  boleh di-rename atau diedit isinya begitu saja** — Laravel melacak migrasi yang sudah jalan
  berdasarkan *nama file* di tabel `migrations`. Tapi file LAMA tetap bisa diganti dengan **file
  baru yang idempotent**: tulis `up()`-nya defensif (`Schema::hasColumn()`, `Schema::hasIndex()`,
  `Schema::getColumns()/getIndexes()` untuk cek kondisi sebelum ubah skema), lalu hapus file
  lama dan ganti file baru (nama+timestamp baru). Karena defensif, migrasi baru itu otomatis
  no-op aman di environment yang sudah punya kolom/index-nya (production), dan tetap lengkap
  membuat skema dari nol di environment baru/fresh yang tidak punya file lama itu lagi — **tidak
  perlu mengubah apa pun di tabel `migrations` production**, baris lama di sana dibiarkan begitu
  saja (Laravel tidak butuh filenya ada lagi untuk migrasi yang sudah tercatat selesai). Trade-off
  yang diterima: `migrate:rollback` spesifik ke migrasi lama itu jadi tidak bisa lagi (filenya
  sudah tidak ada) — jarang dibutuhkan, dan kontennya tetap ada di backup +
  histori git. `down()` di migrasi konsolidasi semacam ini sebaiknya dikosongkan (tidak ada cara
  aman membedakan "kolom ini saya buat atau sudah ada dari migrasi lama").
  Lihat contoh nyata di `consolidate_dokter_table_alterations`,
  `consolidate_kontak_table_alterations`, dkk — direktori migrasi sekarang cuma berisi
  `*_create_*` dan `*_consolidate_*`, tidak ada lagi `add_*`/`fix_*`/`refactor_*`. Migrasi yang
  menyentuh banyak tabel untuk **satu fitur** (mis. rollout fulltext index, soft delete) tetap
  sah jadi satu file — dikelompokkan per fitur, bukan dipaksa per tabel. Detail analisis,
  pemetaan file lama→baru, dan opsi-opsi yang dipertimbangkan:
  [issues/migration-cleanup-plan.md](../issues/migration-cleanup-plan.md)

---

## Environment Variables Penting

```env
# Admin
ADMIN_PATH=manage                  # Path admin panel (ganti di production)

# Security
SESSION_ENCRYPT=true               # Enkripsi payload session
SESSION_SECURE_COOKIE=false        # Set true di production (HTTPS)
TRUSTED_PROXIES=                   # IP load balancer/proxy di production
CSP_ENFORCE=false                  # true = Content-Security-Policy ditegakkan (blocking), false = Report-Only

# Chatbot
N8N_URL=https://...                # Webhook N8N untuk AI chatbot
# Batas pemakaian AI (burst & harian) diatur sebagai konstanta di app/Livewire/Chatbot/Panel.php

# Cache (untuk rate limiter)
CACHE_STORE=database               # Gunakan redis di production untuk performa

# Laravel Reverb (WebSocket self-hosted)
REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Web Push — VAPID keys (generate via web-push library atau Node.js crypto)
# Lihat reverb/07-push-notification-pasien.md untuk cara generate
VAPID_PUBLIC_KEY=...               # EC P-256 public key (URL-safe base64)
VAPID_PRIVATE_KEY=...              # EC P-256 private key (URL-safe base64)
VAPID_SUBJECT="${APP_URL}"         # Identitas pengirim (URL atau mailto:)
VITE_VAPID_PUBLIC_KEY="${VAPID_PUBLIC_KEY}"  # Diekspos ke frontend via Vite

# Ketersediaan Rawat Inap — base URL API Ranap (tanpa kode RS, tanpa trailing slash)
# Kosongkan untuk fallback ke fixture storage/app/mock/ranap-ketersediaan.json
# Identifier per RS diisi di kolom rumah_sakit.ranap_kode_api (Filament, bukan .env)
RANAP_API_BASE_URL=

# Live Antrian Poliklinik — base URL-nya dari kolom rumah_sakit.link_antrian (per RS),
# bukan env. Kredensial Basic Auth di bawah ini global (sama untuk semua RS).
ANTRIAN_API_USERNAME=
ANTRIAN_API_PASSWORD=
```

---

## Status Pengembangan

### Selesai

- [x] Arsitektur multi-tenant (slug URL per RS)
- [x] Admin panel Filament (21 resource + 1 page)
- [x] Portal publik semua halaman
- [x] Refactor: `UnitLayanan` dihapus, `PoliKlinik` langsung ke `RumahSakit`
- [x] Executive Clinic — flag `executive_clinic` di RS, `is_executive` di jadwal, tampilan warna brand `#e8cd84`
- [x] Jadwal portal — groupBy dokter, semua slot jam tampil per baris dokter
- [x] JadwalPraktek — 2 mode (Per Hari & Per Dokter), HTML table + Tom Select
- [x] JadwalPraktek — checkbox `sesuai_perjanjian` dan `is_executive` sync via `$wire.set()`
- [x] JadwalHarian — override harian + tracking perubahan status (JadwalHarianPerubahan)
- [x] JadwalHarian — Tom Select untuk poliklinik & dokter, kolom `is_executive`
- [x] Cron GenerateJadwalHarian (daily 00:05, skip jika sudah ada)
- [x] SoftDelete + Restore: Dokter, Spesialis, PoliKlinik
- [x] Composite slug uniqueness per RS (Promo, Spesialis, Halaman, PoliKlinik)
- [x] Halaman jadwal 2 mode (Per Hari + Per Poli)
- [x] Nama dokter di kartu jadwal dapat diklik → profil dokter
- [x] Badge Sesuai Perjanjian (hijau) di portal — jadwal, detail poli, profil dokter
- [x] Global search Ctrl+K (FULLTEXT MySQL + LIKE fallback)
- [x] Chatbot AI (persistent session, fullscreen mobile, smart scroll)
- [x] Security hardening (rate limit, headers, session encrypt, Livewire Locked, admin path)
- [x] Disclaimer jadwal dengan nomor kontak PENDAFTARAN
- [x] Lightbox galeri foto (GLightbox)
- [x] Sort order drag-drop di 6 resource
- [x] Redirect ke list setelah Create/Edit di semua resource
- [x] FAQ section di homepage RS
- [x] Kontak 3 kategori (SOSIAL MEDIA, OPERASIONAL, PENDAFTARAN)
- [x] Footer dengan ikon sosmed + card kontak
- [x] Mobile bottom bar (Emergency + Hotline + Chatbot)
- [x] SEO meta tags (artesaos/seotools)
- [x] Landing page (hero, Tom Select, no jQuery)
- [x] Test suite (Unit + Feature, 242+ passing)
- [x] PosterTemplate — CRUD upload asset (background, logo, shape), zone editor drag-drop
- [x] GeneratePosterPage — form + download PNG 1080×1920 via Browsershot
- [x] Sitemap XML otomatis per rumah sakit (`/sitemap.xml` index + `/{rumahsakit}/sitemap.xml`, di-cache 6 jam)
- [x] Content-Security-Policy (CSP) header — mode Report-Only dengan toggle `CSP_ENFORCE`
- [x] Chatbot — rate limiting AI 2 lapis (burst per menit + kuota harian, via `RateLimiter`, angka dikonfigurasi sebagai konstanta)
- [x] Chatbot — opsi pemulihan saat respons gagal (restart percakapan, kirim ulang pesan, daftar kontak non-emergency)
- [x] Lazy loading (`loading="lazy"`) pada gambar below-the-fold di seluruh halaman publik
- [x] JadwalHarianPerubahan — snapshot nilai asli (`*_asli`) untuk deteksi "kembali ke semula" tanpa bergantung pada `JadwalPraktek`
- [x] JadwalHarian — penanda visual baris Executive (highlight background amber + ikon bintang) di tabel admin
- [x] JadwalPraktek — validasi `waktu_mulai` wajib diisi kecuali `sesuai_perjanjian` dicentang (form & kedua mode tabel)
- [x] Tanya Dokter — konsultasi chat real-time via Laravel Reverb (token UUID, tanpa login, broadcasting dua arah pasien ↔ dokter, panel Filament terpisah untuk dokter)
- [x] Tanya Dokter — 1 sesi aktif per pasien via cookie (redirect otomatis ke sesi aktif, umur cookie = durasi sesi dokter)
- [x] Tanya Dokter — push notification 3 lapisan: in-app toast (Alpine.js global), browser Notification API, Web Push VAPID via service worker (`public/sw.js`) + `minishlink/web-push` job
- [x] Tanya Dokter — banner izin push notifikasi kustom (gradient violet/indigo, animasi bel, dipicu hanya saat klik "Aktifkan")
- [x] Tanya Dokter — kesimpulan/catatan dokter (form modal sebelum akhiri sesi, tampil di chat pasien, dapat diedit dari Riwayat)
- [x] Tanya Dokter — rate limiting pesan pasien: 10 pesan per 60 detik per token sesi
- [x] Tanya Dokter — KonsultasiDashboard: preview pesan terakhir per sesi di kartu antrean
- [x] Tanya Dokter — KonsultasiDashboard: badge unread merah per sesi (via `dokter_baca_at` + `withCount`)
- [x] Tanya Dokter — KonsultasiDashboard: warna kartu berbeda BERLANGSUNG vs MENUNGGU
- [x] Tanya Dokter — RiwayatKonsultasi: daftar sesi selesai/kedaluwarsa, cari nama pasien, transkrip, edit kesimpulan
- [x] Tanya Dokter — hapus semua kelas `dark:` dari halaman dokter (konsultasi-dashboard & riwayat-konsultasi)
- [x] Tanya Dokter — Panel Filament terpisah untuk Dokter (`/dokter`, `DokterPanelProvider`)
- [x] Generate Poster — fix bug field `hariIni`, `jam_mulai`/`waktu_mulai`, dan `libur` antar model
- [x] Generate Poster — implementasi `previewPoster()` (render HTML preview + buka di tab baru)
- [x] Artikel & Berita — CRUD + kategori, halaman publik list (unggulan + grid + pagination) & detail, link di dropdown "Media Informasi"
- [x] Popup Jadwal Poliklinik — admin upload gambar (mis. hasil Generate Poster) + toggle aktif via widget dashboard, tampil modal di homepage publik
- [x] Popup Promo — widget dashboard untuk toggle popup promo homepage
- [x] CTA Google Review — tombol redirect ke Google Business Profile (homepage + footer), tanpa survei perantara
- [x] Tombol "Daftar Sekarang" di halaman profil dokter & Jadwal Praktek (link ke `link_pendaftaran_online`)
- [x] Chatbot — nama tampilan diubah jadi "Tanya Syifa" (FAB & bottom bar)
- [x] Fix slug `dokter` dari unique global → composite per RS
- [x] Ketersediaan Rawat Inap — fetch live dari API Ranap per render (tanpa tabel cache), multi-tenant via `rumah_sakit.ranap_kode_api`, fallback fixture lokal kalau RS belum onboarding
- [x] `KelasRawatInap` — tabel master kelas per RS, gantikan kolom `kelas` string bebas di `RawatInap`
- [x] Fix bug `RsPortalComponent` + `wire:poll` — `KetersediaanRawatInap` & `RawatInap` pindah ke `boot()` re-bind manual supaya tidak `BindingResolutionException` di request AJAX lanjutan
- [x] Link Layanan — static-kan 3 kartu di homepage & portal listing (model/resource lama tetap ada, cuma tidak ditampilkan otomatis lagi)
- [x] Kolom `rumah_sakit.link_antrian` — link eksternal pantauan antrian per RS (kartu ke-3 di "Informasi & Layanan")
- [x] Live Antrian per Dokter — `AntrianApiClient` fetch live (Basic Auth global) ke `{link_antrian}/api/public/poli/{nomor_poli_antrian}`, tanpa cache, tampil di profil dokter publik
- [x] `Dokter.kuota_pasien` — info kuota/ketersediaan rawat jalan (free text), tampil di profil dokter publik di atas Jadwal Praktek
- [x] `DokterResource` — section "API Antrian" dibatasi hanya untuk role `super_admin`/`admin`; field `kuota_pasien` & toggle `aktif` dirapikan ke layout full-width di luar 2-kolom section
- [x] Fix 3 test pra-eksisting yang gagal: `UserTest` (pakai `Filament::getPanel('admin')` yang benar-benar terdaftar, bukan `Panel` kosong) & `ChatbotPanelTest` (`MAX_MESSAGES` disesuaikan ke nilai aktual 50)
- [x] Header desktop — Emergency & Hotline dibuat clickable (`tel:` link) dengan badge chip warna + shadow (bukan animasi, supaya tidak terlalu mencolok)
- [x] Hero homepage — Emergency/Hotline dihapus dari hero (sudah ada di header), tombol "Buat Janji Dokter" digabung ke baris Quick Nav
- [x] Section "Informasi & Layanan" homepage — versi mobile dibuat tile ringkas 3 kolom (ikon + label saja), versi desktop (kartu lengkap) tidak diubah
- [x] Section "Dokter Kami" homepage — kartu diselaraskan dengan style halaman Dokter Kami (gradient border, badge spesialis di foto), tanpa deskripsi; 3 kolom konsisten dari mobile (lebar tiap kartu dikecilkan & dipusatkan) sampai desktop
- [x] Mobile bottom bar — redesign badge Tanya Syifa (avatar bulat putih, meniru header panel chat) agar setara bobot visualnya dengan badge Emergency/Hotline; tambah efek hover per nav
- [x] Fix bug filter di halaman Jadwal Praktek: `JadwalPraktek.php` (Livewire) pindah dari `RsPortalComponent` ke `Component` biasa + re-bind manual `currentRumahSakit` di `boot()` — pola sama dengan `KetersediaanRawatInap.php`, supaya filter hari/poli (lewat `/livewire/update`) tidak `BindingResolutionException`
- [x] Kategori Kontak baru `RAWAT INAP` — dedicated untuk disclaimer halaman Ketersediaan Rawat Inap, tidak lagi reuse `PENDAFTARAN`
- [x] Pembersihan migrasi database: 64 → 47 file, direktori `database/migrations/` sekarang cuma `*_create_*` dan `*_consolidate_*` (tidak ada lagi `add_*`/`fix_*`/`refactor_*`). Migrasi lama yang sudah pernah jalan tidak diubah/dihapus dari riwayat — diganti migrasi baru yang idempotent (`Schema::hasColumn`/`hasIndex`) sehingga aman dijalankan di production tanpa menyentuh tabel `migrations`. Detail & cara verifikasi: [issues/migration-cleanup-plan.md](../issues/migration-cleanup-plan.md)
- [x] Preview 360° kamar rawat inap — kolom `RawatInap.foto_360`, upload via `RawatInapResource`, badge "360°" di kartu publik (tampil hanya kalau ada foto), viewer Photo Sphere Viewer dalam modal kustom (bukan Preline `hs-overlay`, lihat catatan di bagian Fitur), dilengkapi focus trap & pengembalian fokus (aksesibilitas dialog). Detail: [issues/preview-360-kamar-rawat-inap.md](../issues/preview-360-kamar-rawat-inap.md)

### Dalam Pengerjaan

- [ ] Poster — dukung gaya layout berbeda antar cabang (`grid_shape` vs `list_polos`), karena humas/desainer tiap cabang berbeda gaya — lihat [issues/poster-multi-cabang-layout-dan-scoping.md](../issues/poster-multi-cabang-layout-dan-scoping.md)
- [ ] `PosterTemplateResource` belum ter-scope per RS (humas RS A bisa lihat/edit template RS B) — gap di dokumen yang sama
- [ ] Kode `ranap_kode_api` resmi untuk RS Banjarbaru — menunggu konfirmasi dari pihak Ranap, sementara masih fallback fixture

### Dalam Pertimbangan

- [ ] Live antrian konsultasi disambungkan ke chatbot
- [ ] Pantauan Antrian internal (scraping) — saat ini masih link keluar via `link_antrian`, bisa diganti route internal kalau scraping dibangun
- [ ] Notifikasi jadwal (email/WhatsApp)
- [ ] Export jadwal ke PDF/Excel
- [ ] Optimasi gambar (resize/kompresi otomatis saat upload — `intervention/image` atau WebP)
