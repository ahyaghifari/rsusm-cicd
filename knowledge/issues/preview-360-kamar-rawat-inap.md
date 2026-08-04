# Implementasi: Preview 360° Kamar Rawat Inap

## Status

**Siap diimplementasikan.** Lanjutan dari spike teknis di
[test-360-viewer.md](test-360-viewer.md) (integrasi Photo Sphere Viewer ke stack Vite/Tailwind
project ini sudah tervalidasi lewat route `/test-360-viewer`). Item ini berasal dari
[revisi/revisi-belum-selesai.md](../revisi/revisi-belum-selesai.md) — "Foto 360° untuk setiap
kamar rawat inap".

## Keputusan Scope (Beda dari Draft Awal)

Draft awal di [revisi/preview-rawat-inap-360.txt](../revisi/preview-rawat-inap-360.txt)
merekomendasikan tabel terpisah `gambar_rawat_inap_360` (mengikuti pola `gambar_rawat_inap`),
supaya 1 kamar bisa punya >1 foto 360 (mis. kamar tidur + kamar mandi terpisah).

**Keputusan final: 1 kolom `foto_360` (nullable, string) langsung di tabel `rawat_inap`** —
1 kamar = maksimal 1 foto panorama. Lebih sederhana untuk kebutuhan saat ini (progres foto
ulang fisik per kamar masih berjalan, baru sebagian kamar yang akan punya foto 360 dalam waktu
dekat). Lihat catatan trade-off di bagian **Saran** di bawah.

## Skema

Tambah kolom ke tabel `rawat_inap` (migrasi baru, **bukan** edit migrasi lama yang sudah
pernah `migrate`):

| Kolom | Tipe | Catatan |
|---|---|---|
| `foto_360` | string(255), nullable | Path file foto panorama equirectangular, disk `public` |

Tidak perlu ubah model `RawatInap` — sudah pakai `$guarded = ['id']`, jadi `foto_360` otomatis
mass-assignable.

## Filament — `RawatInapResource`

Tambah `FileUpload::make('foto_360')` di section "Fasilitas & Tampilan" (sebelah `thumbnail`):
- `->image()->directory('rawat-inap/foto-360')->disk('public')`
- Nullable (kamar yang belum difoto ulang tetap bisa disimpan tanpa foto 360)
- Helper text mengingatkan admin: foto harus **equirectangular 360°** (rasio 2:1) dari kamera
  360, bukan foto biasa — kalau foto biasa diupload, viewer akan menampilkan distorsi (sama
  seperti catatan di spike `test-360-viewer.md` soal foto test `360.jpg`).

## Frontend Publik

- Tombol **"Preview 360°"** di kartu kamar (`components/rawat-inap.blade.php`), **hanya tampil
  kalau `$kamarInap->foto_360` tidak null**.
- Klik tombol → modal **Preline** (`hs-overlay`) terbuka — ini modal Preline pertama di
  codebase ini (spike sebelumnya pakai modal vanilla JS supaya yang ditest cuma viewer-nya).
- Viewer (`window.PSVViewer`, sudah ter-bundle via `resources/js/app.js`) baru di-`new` saat
  modal benar-benar terbuka (event `open.hs.overlay`), bukan saat halaman load — modal Preline
  default `hidden`, kalau viewer di-init saat container masih `display:none` ukurannya terbaca
  0×0 dan render rusak (gotcha yang sama persis seperti dicatat di spike).
- Inisialisasi viewer dipasang **sekali** secara delegated di level halaman
  (`rawat-inap.blade.php`, lewat `document.addEventListener('open.hs.overlay', ...)` yang
  mengecek `id` modal diawali `modal-360-`) — bukan inline script per kartu di dalam
  `@foreach`, supaya tidak duplikat listener kalau ada banyak kamar di satu halaman. Dilengkapi
  flag `dataset.psvInitialized` per container supaya tidak re-init kalau modal dibuka-tutup
  berulang.
- Modal & viewer container diberi id unik per kamar (`modal-360-{id}`, `viewer-360-{id}`)
  supaya tidak kolisi antar kartu.

## Saran

1. **Trade-off 1 kolom vs tabel terpisah**: kalau nanti ada kebutuhan riil 1 kamar punya >1
   foto 360 (mis. kamar tidur + kamar mandi terpisah, seperti draft awal antisipasi), kolom
   `foto_360` ini perlu dimigrasikan ke tabel terpisah (mirip `gambar_rawat_inap`). Untuk
   sekarang ini sengaja diterima sebagai keterbatasan yang oke, tapi dicatat di sini supaya
   tidak mengejutkan kalau requirement berubah.
2. **Validasi rasio gambar**: Filament `FileUpload` tidak otomatis menolak foto yang bukan
   equirectangular 2:1. Pertimbangkan tambahkan `->imageEditorAspectRatios(['2:1'])` supaya
   admin setidaknya dapat panduan crop 2:1 saat upload, mengurangi risiko foto biasa ke-upload
   tanpa sadar.
3. **Batas ukuran file**: foto panorama equirectangular biasanya beresolusi besar (4096×2048
   atau lebih). Pertimbangkan `->maxSize(10240)` (10MB) di field upload supaya tidak membengkak
   storage/bandwidth publik tanpa kontrol.
4. **Bersihkan spike setelah fitur ini live**: route `/test.360_viewer`
   (`routes/web.php`) dan `resources/views/test-360-viewer.blade.php` beserta
   `public/img/360.jpg`/`361.jpg` adalah artefak spike teknis — setelah fitur penuh ini
   dipasang dan dipastikan jalan, sebaiknya dihapus supaya tidak ada route tes nyangkut di
   production.
5. **Aksesibilitas/UX**: kalau dalam satu halaman banyak kamar punya foto 360 sekaligus,
   pastikan setiap tombol trigger punya `aria-label` yang jelas (mis. "Preview 360° kamar
   {nama}") karena teksnya sama ("Preview 360°") di semua kartu.

## Test

Ikuti pola `tests/Feature/RawatInapTest.php`/`GambarRawatInapTest.php` yang sudah ada:
- Kamar dengan `foto_360` terisi → tombol "Preview 360°" muncul di halaman publik.
- Kamar dengan `foto_360` null → tombol tidak muncul, tidak ada elemen viewer kosong/error.
