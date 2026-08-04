# Issue: Tes Teknis — Photo Sphere Viewer di Modal Preline

## Status

**Planning — siap diimplementasikan.** Ini bukan fitur final, tapi **tes teknis/spike**
sebelum membangun fitur penuh "Preview 360 Kamar Rawat Inap"
(lihat [revisi/preview-rawat-inap-360.txt](../revisi/preview-rawat-inap-360.txt)). Tujuannya
cuma memvalidasi: apakah Photo Sphere Viewer bisa diintegrasikan dengan stack project ini
(Vite 6, Preline 4.2, Tailwind v4) dan tampil benar di dalam modal, sebelum invest waktu bikin
skema database, Filament RelationManager, dll.

## Tujuan

1. Pasang library **Photo Sphere Viewer** lewat npm + Vite (belum ada di project sama sekali).
2. Satu tombol di halaman publik → klik → modal Preline terbuka → di dalamnya viewer 360
   langsung load foto test `public/img/360.jpg`.
3. Tidak ada perubahan database, tidak ada Filament, tidak ada upload — foto test di-hardcode
   dulu lewat `asset('img/360.jpg')`.

## Catatan Soal Foto Test

User sudah taruh `public/img/360.jpg` — foto ini **horizontal-only** (tidak ada cakupan
atas/bawah penuh seperti equirectangular asli 2:1). Photo Sphere Viewer secara default
mengharapkan foto equirectangular utuh, jadi kemungkinan ada distorsi/stretching di bagian
atas & bawah viewer. Ini diterima untuk tes awal ("tidak apa apa kita coba dulu") — tujuannya
validasi integrasi teknis, bukan kualitas visual akhir. Foto asli kamar nanti (saat fitur
penuh dikerjakan) harus equirectangular proper dari kamera 360 (lihat catatan di
`preview-rawat-inap-360.txt`).

---

## Library: Photo Sphere Viewer v5

Dikonfirmasi lewat dokumentasi resmi (`photo-sphere-viewer.js.org`):

- Nama package npm: **`@photo-sphere-viewer/core`** (bukan nama lama `photo-sphere-viewer`
  yang sudah deprecated di v5).
- **`three`** (Three.js) wajib di-install terpisah sebagai dependency — tidak dibundle otomatis
  di dalam `@photo-sphere-viewer/core`.

```bash
npm install @photo-sphere-viewer/core three
```

Import dasar (ES module, sesuai cara Vite handle import):

```js
import { Viewer } from '@photo-sphere-viewer/core';
import '@photo-sphere-viewer/core/index.css';

const viewer = new Viewer({
    container: document.getElementById('viewer-360-test'),
    panorama: 'path/ke/foto.jpg',
});
```

---

## Konvensi yang Diikuti (Sesuai Codebase Existing)

Project ini sudah punya pola jelas untuk integrasi library JS pihak ketiga — lihat
`resources/js/app.js`:

```js
import Swiper from 'swiper/bundle';
window.Swiper = Swiper;
```

Library di-import sekali secara global di `app.js`, diekspos lewat `window.X`, lalu tiap
halaman Blade yang butuh library itu menulis `<script>` inline kecil yang mereferensikan
`window.X` langsung — bukan import ES module per halaman. Photo Sphere Viewer akan mengikuti
pola yang sama persis:

```js
// resources/js/app.js — tambahkan
import { Viewer as PSVViewer } from '@photo-sphere-viewer/core';
import '@photo-sphere-viewer/core/index.css';

window.PSVViewer = PSVViewer;
```

CSS-nya otomatis ikut masuk lewat Vite karena di-`import` dari file JS (Vite handle CSS-in-JS
import secara native, tidak perlu config tambahan).

---

## Gotcha Teknis Penting: Init Viewer HANYA Saat Modal Sudah Terbuka

Modal Preline secara default `hidden` (di-`display:none`) sampai dibuka. Kalau Photo Sphere
Viewer di-`new Viewer(...)` saat container masih `hidden`, viewer akan mengukur ukuran
container jadi 0x0 dan render rusak/blank.

**Solusi**: jangan init viewer saat halaman load. Init viewer di event `click` tombol trigger
modal yang sama (tombol yang juga punya `data-hs-overlay="#modal-id"`), dibungkus
`requestAnimationFrame` atau `setTimeout(fn, 50)` kecil supaya modal sempat benar-benar
tampil dulu sebelum Photo Sphere Viewer mengukur container. Tambahkan flag sederhana (mis.
`let viewerInitialized = false`) supaya tidak bikin instance baru tiap kali tombol diklik
ulang (modal dibuka-tutup berkali-kali) — re-init cukup sekali per page load.

---

## Rencana Implementasi (Spike, Minimal)

1. `npm install @photo-sphere-viewer/core three`.
2. Tambah import + `window.PSVViewer` di `resources/js/app.js`.
3. Pilih 1 halaman publik yang sudah ada untuk taruh tombol test — rekomendasi:
   `resources/views/rumah_sakit/pages/rawat-inap.blade.php` (paling relevan secara konteks,
   karena ini memang halaman tujuan fitur penuhnya nanti), ditaruh di bagian atas halaman,
   diberi label jelas sementara seperti **"[TEST] Preview 360"** supaya jelas ini bukan UI
   final dan mudah ditemukan untuk dihapus/dirapikan nanti.
4. Markup tombol trigger + modal Preline (`data-hs-overlay`/`hs-overlay`), pola standar
   Preline — ini akan jadi modal pertama di codebase ini (belum ada modal Preline lain yang
   dipakai sejauh ini, sejauh dicek di seluruh `resources/views`).
5. Di dalam modal: `<div id="viewer-360-test" style="width:100%;height:70vh;"></div>` —
   Photo Sphere Viewer butuh container dengan tinggi eksplisit (tidak bisa 100% dari parent
   yang tidak punya tinggi pasti), jadi pakai `height` fix atau viewport unit (`vh`).
6. Inline `<script>` di halaman yang sama: pada event click tombol trigger, `setTimeout` kecil,
   lalu `new window.PSVViewer({ container: ..., panorama: '{{ asset("img/360.jpg") }}' })`
   sekali saja (flag `viewerInitialized`).
7. Test manual: buka halaman di browser, klik tombol, pastikan modal terbuka dan foto 360
   bisa di-drag untuk lihat sekeliling (minimal arah horizontal, accept distorsi vertikal
   karena foto test bukan equirectangular penuh).

## Yang TIDAK Termasuk Spike Ini

- Tidak ada migrasi/skema database baru.
- Tidak ada perubahan Filament Resource.
- Tidak ada penyesuaian RS-scoping (tombol test ini sementara sama untuk semua RS, statis).
- Tidak menghapus/mengubah komponen galeri foto datar yang sudah ada di
  `rawat-inap.blade.php`/`components/rawat-inap.blade.php` — tombol test cuma ditambahkan,
  bukan menggantikan apa pun.

## Setelah Spike Ini Berhasil

Kalau tes ini jalan baik secara teknis, lanjutkan ke rencana penuh di
[preview-rawat-inap-360.txt](../revisi/preview-rawat-inap-360.txt) (skema database per kamar,
upload lewat Filament, viewer dinamis per data, bukan hardcode 1 foto test).
