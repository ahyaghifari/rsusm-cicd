# Revisi — Status Pengerjaan (per 2026-06-19)

Sumber: 2 foto catatan rapat di folder ini (`WhatsApp Image 2026-06-18 at 17.07.23.jpeg` &
`...17.07.24.jpeg`).

## Sudah Selesai

- [x] **Nama pada fitur Assistant/chatbot** — FAB chatbot desktop & mobile bottom bar diberi
      label "Tanya Syifa" (sebelumnya tanpa nama / "Syifa Assistant").
- [x] **Tombol "Daftar Sekarang" pada halaman jadwal** — ditambahkan di dua tempat, link ke
      `link_pendaftaran_online` milik RS:
      - Halaman profil dokter (`dokter/show.blade.php`) — di bawah jadwal praktek dokter
      - Halaman Jadwal Praktek umum (`pages/jadwal-praktek.blade.php`) — di bawah daftar jadwal
- [x] **Halaman Artikel & Berita** — sudah diimplementasikan lengkap (migrasi `kategori_artikel`
      & `artikel`, model, `ArtikelResource` + `KategoriArtikelResource` di Filament, halaman
      publik list & detail dengan search + filter kategori + pagination, link di dropdown
      "Media Informasi" pada navigasi). Lihat [issues/artikel-berita.md](../issues/artikel-berita.md)
      untuk detail implementasi. Sudah diisi data contoh lewat `ArtikelSeeder` (3 kategori x 3
      artikel per rumah sakit).
- [x] **Sistem peratingan (rating)** untuk dokter/layanan, digabung dengan **survei/kuesioner
      kepuasan pasien yang otomatis redirect ke Google Review** — keduanya disepakati sebagai
      tujuan yang sama (dorong pasien memberi rating/ulasan), diselesaikan lewat CTA Google
      Review: tombol "Tulis Ulasan Anda" & "Lihat Ulasan Lainnya" di halaman beranda (di bawah
      FAQ) dan di footer (di bawah embed Google Maps), me-redirect langsung ke halaman resmi
      Google tanpa form survei perantara. Lihat [issues/google-review.md](../issues/google-review.md).
      *Catatan scope: rating-nya per rumah sakit (mengikuti Google Business Profile), bukan
      breakdown per dokter/layanan individual — dan tidak ada langkah survei/filter kepuasan
      sebelum diarahkan ke Google (pasien tidak puas bisa langsung menulis ulasan publik juga).*
- [x] **Foto 360° kamar rawat inap** — kolom `foto_360` (nullable) ditambahkan ke tabel
      `rawat_inap`, diupload admin lewat `RawatInapResource` (Filament, dilengkapi panduan
      crop rasio 2:1 & batas ukuran file). Badge "360°" tampil di kartu kamar halaman publik
      `/rawat-inap` hanya kalau kamar itu sudah punya foto panorama — kamar yang belum difoto
      ulang (masih banyak, karena task fotografi fisiknya berjalan terpisah) tetap tampil
      normal tanpa elemen kosong/error. Viewer pakai Photo Sphere Viewer
      (`@photo-sphere-viewer/core`, divalidasi dulu lewat spike di
      [issues/test-360-viewer.md](../issues/test-360-viewer.md)), dibuka lewat modal custom
      gelap full-bleed (bukan Preline `hs-overlay` — sempat dicoba tapi bikin glitch karena
      script init-nya ikut di-render ulang Livewire tiap filter kelas diubah), sudah dilengkapi
      focus trap & pengembalian fokus ke tombol trigger (aksesibilitas dialog WAI-ARIA). Detail
      implementasi & keputusan scope: [issues/preview-360-kamar-rawat-inap.md](../issues/preview-360-kamar-rawat-inap.md).
      *Catatan scope: 1 kamar = maksimal 1 foto 360 (kolom tunggal, bukan tabel galeri
      terpisah seperti draft awal) — kalau nanti perlu lebih dari 1 foto per kamar, perlu
      dimigrasikan ke tabel terpisah (lihat saran di issue tersebut).*
- [x] **Live antrian** — `AntrianApiClient` fetch live (Basic Auth global) ke
      `{rumah_sakit.link_antrian}/api/public/poli/{nomor_poli_antrian}`, tanpa cache, status
      tampil di profil dokter publik. Field `Dokter.nomor_poli_antrian` + tombol "Tes" di admin
      (`DokterResource`, role `super_admin`/`admin`) untuk verifikasi nomor sebelum disimpan.
      *Catatan scope: status antrian tampil di profil dokter, bukan di dalam flow chatbot
      "Tanya Syifa" — kalau yang dimaksud "disambungkan ke chatbot" adalah chatbot bisa
      menjawab pertanyaan soal antrian, itu belum dikerjakan terpisah.*
- [x] **Info ketersediaan kamar rawat inap real-time** — halaman `/ketersediaan-rawat-inap`,
      data diambil langsung dari API Ranap tiap render (termasuk tiap `wire:poll.30s`, tanpa
      tabel cache), fallback ke fixture lokal kalau RS belum punya `ranap_kode_api`. Filter
      Kelas/Nama Kamar/Status, toggle tampilan Per Kamar/Per Kelas, dan disclaimer konfirmasi
      ke resepsionis + kontak kategori `RAWAT INAP` (dedicated, bukan reuse `PENDAFTARAN`).
      Detail: [issues/ketersediaan-rawat-inap-plan.md](../issues/ketersediaan-rawat-inap-plan.md),
      [issues/link-layanan-static-dan-ranap-multi-tenant.md](../issues/link-layanan-static-dan-ranap-multi-tenant.md)
- [x] **Tampilan website disempurnakan sesuai masukan** — item umum/terus-menerus, dijalankan
      bertahap lewat revisi UI: nav dropdown, promo popup, redesign tombol filter kelas &
      kartu ketersediaan kamar di halaman Rawat Inap/Ketersediaan Rawat Inap, header
      desktop Emergency/Hotline jadi clickable, redesign section "Dokter Kami" & "Informasi
      & Layanan" di homepage, badge Tanya Syifa di mobile bottom bar, hingga modal preview
      360° kamar (aksesibel, konsisten dengan bahasa visual GLightbox yang sudah ada). *Catatan:
      sifatnya memang berkelanjutan — kalau ada masukan tampilan baru lagi nanti, wajar untuk
      dibuka ulang sebagai item baru, bukan berarti "selesai selamanya".*

## Belum Dikerjakan

- [ ] **Fitur generate poster** untuk upload ke sosial media — *catatan: ini kemungkinan sudah
      sebagian dikerjakan terpisah, lihat [poster-jadwal-poliklinik.md](../issues/poster-jadwal-poliklinik.md)
      dan diskusi lanjutan di [poster-multi-cabang-layout-dan-scoping.md](../issues/poster-multi-cabang-layout-dan-scoping.md)
      (status: belum diimplementasi). Rencana implementasi lanjutan (scoping fix, split layout
      grid_shape/list_polos, styling internal card per poli) dirangkum di
      [poster-generate-planning.md](poster-generate-planning.md).*

## Catatan Tambahan (bukan dari foto, ditemukan saat eksplorasi terkait)

- `PosterTemplateResource` belum ter-scope per rumah sakit (humas RS A bisa lihat/edit
  template RS B) — gap lama yang ditemukan saat membahas poster, didokumentasikan di
  [poster-multi-cabang-layout-dan-scoping.md](../issues/poster-multi-cabang-layout-dan-scoping.md),
  belum diperbaiki.
