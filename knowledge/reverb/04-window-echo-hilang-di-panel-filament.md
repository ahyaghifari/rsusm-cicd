# Kenapa `window.Echo` Tidak Ada di Panel Filament? (Bug "Harus Refresh Terus")

**Tanggal:** 2026-06-08
**Status:** ✅ Perbaikan ini benar & memang dibutuhkan — tapi ternyata **belum cukup sendirian**. Setelah `window.Echo` berhasil dimuat, masih ada **satu bug lagi** yang membuat listener Livewire tetap diam tanpa update live (lihat [05-mismatch-namespace-echo-broadcastas.md](05-mismatch-namespace-echo-broadcastas.md)). Update real-time baru benar-benar berjalan setelah *kedua* perbaikan diterapkan bersama — sudah diverifikasi end-to-end.

> Lanjutan dari [03-private-channel-dan-dashboard-dokter.md](03-private-channel-dan-dashboard-dokter.md). Di dokumen itu kita sudah memasang listener `#[On('echo:...')]` & `#[On('echo-private:...')]` di `KonsultasiDashboard`. Tapi setelah dicoba di dunia nyata — ternyata **tidak ada satupun update yang masuk secara live**. Dokumen ini membahas root cause-nya: bukan masalah broadcasting/Reverb sama sekali, melainkan **aset front-end yang tidak pernah dimuat**.

---

## 1. Gejalanya: "kerja kalau di-refresh, diam kalau live"

Setelah Fase 3 selesai dan pengguna mencobanya sendiri:

- Pasien menekan "Mulai Sesi Chat" → **dokter tidak langsung melihat sesi baru** di dashboard-nya. Begitu dokter me-*refresh* halaman, sesi barulah muncul.
- Dokter membalas chat → **pasien tidak langsung melihat balasannya** sampai me-*refresh*.

Pola ini adalah **petunjuk besar**: kalau data sudah benar di database dan baru muncul setelah *reload* (yaitu request HTTP biasa ke server), tapi **tidak pernah muncul tanpa reload**, maka kemungkinan besar bukan masalah di backend (event broadcast, otorisasi channel, dsb) — melainkan **jalur pengiriman real-time-nya** (WebSocket) yang tidak pernah benar-benar tersambung di browser.

---

## 2. Proses penelusuran (sebelum ketemu akar masalahnya)

Beberapa kemungkinan sempat diperiksa dan disingkirkan satu per satu:

### a. Apakah Reverb server-nya jalan dobel?
Sempat ditemukan **dua proses `reverb:start` berjalan bersamaan** menempati port `8080` yang sama (hal ini *mungkin* terjadi di Windows — OS akan membagi koneksi masuk secara acak ke salah satu proses). Akibatnya, publisher (server yang menyiarkan event) dan subscriber (browser yang mendengarkan) bisa jadi tersambung ke instance Reverb yang berbeda — pesan terkirim ke instance A, padahal browser mendengarkan di instance B. Proses-proses dobel ini sudah dimatikan, tapi masalah **tetap ada** — jadi ini bukan akar masalahnya (meskipun tetap berbahaya dan baik untuk dibersihkan).

### b. Apakah event broadcast-nya benar?
Diperiksa ulang `SesiStatusBerubah` — sudah `implements ShouldBroadcastNow` (artinya tidak bergantung pada *queue worker* yang menjalankan job di belakang layar; broadcast dikirim seketika). Pemanggilan `broadcast(new SesiStatusBerubah($sesi))` juga sudah ada di tempat yang benar (`TanyaDokter::mulaiSesi()`, `KonsultasiDashboard::terima()`, dsb). **Backend-nya sudah benar.**

### c. Petunjuk pamungkas: console browser
Pengguna membuka *developer console* di browser pada halaman dashboard dokter, dan menemukan pesan:

> **"Laravel Echo cannot be found"**

Inilah titik terang yang langsung mengarahkan ke akar masalah sesungguhnya.

---

## 3. Akar Masalah: `window.Echo` Tidak Pernah Dimuat di Panel Filament

### Bagaimana Livewire "mendengarkan" event broadcast?

Ingat dari [02-events-channel-livewire.md](02-events-channel-livewire.md): atribut `#[On('echo:channel,EventName')]` dan `#[On('echo-private:channel,EventName')]` di komponen Livewire **bukan sihir** — di balik layar, Livewire menjalankan kode JavaScript di browser yang memanggil:

```js
window.Echo.channel('nama-channel').listen('.EventName', (data) => { ... })
// atau untuk private:
window.Echo.private('nama-channel').listen('.EventName', (data) => { ... })
```

Supaya baris itu bisa berjalan, **objek `window.Echo` harus sudah ada** di halaman *sebelum* Livewire mencoba mendaftarkan listener-nya. Kalau `window.Echo` tidak ada (`undefined`):

- Tidak ada *error* yang dilempar ke layar (Livewire menelan kegagalan ini secara diam-diam).
- Listener **tidak pernah terdaftar** ke server WebSocket.
- Satu-satunya gejala yang terlihat: "kok updatenya gak masuk-masuk ya, padahal kalau di-refresh datanya udah benar". Persis seperti yang dialami pengguna — **inilah definisi sempurna dari "silent failure"**.

### Di mana `window.Echo` biasanya diinisialisasi?

Project ini sudah punya `resources/js/echo.js`:

```js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

File ini di-*import* oleh `resources/js/app.js`:

```js
import './bootstrap';
import './echo';   // 👈 di sinilah window.Echo dibuat
import 'preline';
import Swiper from 'swiper/bundle';
...
```

Dan `app.js` ini hanya dimuat lewat direktif `@vite(['resources/js/app.js'])` di layout-layout **portal publik** — `portal-layout.blade.php`, `rumah_sakit.blade.php`, dll. Sisi pasien (`TanyaDokter`) berjalan di atas layout-layout ini, jadi `window.Echo` selalu ada di sana — **itulah kenapa "Mulai Sesi Chat" sebagai pasien sebenarnya berhasil ter-broadcast** (server-nya bekerja!), hanya saja tidak ada yang "mendengarkan" di sisi dokter.

### Kenapa panel Filament (`/dokter`, `/admin`) tidak ikut memuatnya?

Ini bagian yang tidak intuitif kalau belum tahu cara kerja Filament:

> **Filament punya sistem aset sendiri yang terpisah total dari Vite milik aplikasi.**

Setiap panel Filament memuat bundel CSS/JS-nya sendiri lewat direktif `@filamentStyles` dan `@filamentScripts` di layout bawaannya — file-file ini sudah di-*build* sebelumnya dan disimpan di `public/js/filament/...` & `public/css/filament/...`. Panel **tidak tahu-menahu** soal `resources/js/app.js` milik aplikasi kita, dan tidak otomatis menyertakannya.

Akibatnya: di halaman `/dokter/konsultasi-dashboard`, tidak ada satupun `<script>` yang menjalankan `new Echo({...})` — `window.Echo` benar-benar `undefined`. Inilah pesan persis yang dilihat pengguna di console: **"Laravel Echo cannot be found"**.

```
┌─────────────────────────┐         ┌──────────────────────────┐
│   Layout Portal Publik   │         │     Layout Panel Filament │
│  (TanyaDokter / pasien)  │         │   (KonsultasiDashboard)   │
│                          │         │                           │
│  @vite(['app.js'])  ✅   │         │  @filamentStyles   )      │
│   └─ window.Echo ada     │         │  @filamentScripts  ) ✅   │
│                          │         │   └─ window.Echo TIDAK   │
│  Listener #[On(...)]     │         │      ada — listener      │
│  berfungsi normal        │         │      Livewire diam saja  │
└─────────────────────────┘         └──────────────────────────┘
```

---

## 4. Perbaikan: Memuat `app.js` di Panel Dokter lewat *Render Hook*

Filament menyediakan mekanisme **render hook** — cara resmi untuk "menyuntikkan" potongan HTML/Blade ke titik-titik tertentu di layout panel (`<head>`, sebelum `</body>`, di sidebar, dsb) tanpa perlu menimpa (*override*) seluruh file layout.

Project ini sebenarnya **sudah memakai pola ini** di `AdminPanelProvider.php` — untuk menyuntikkan CSS kustom ke `<head>`:

```php
->renderHook(
    PanelsRenderHook::HEAD_END,
    fn (): string => Blade::render('
        <style> .fi-sidebar-item.fi-active .fi-sidebar-item-button { ... } </style>
    ')
)
```

Solusinya tinggal **meniru pola yang sama**, tapi kali ini menyuntikkan direktif `@vite(['resources/js/app.js'])` (bukan `<style>`) ke `DokterPanelProvider.php`:

```php
// app/Providers/Filament/DokterPanelProvider.php

use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

->renderHook(
    PanelsRenderHook::HEAD_END,
    fn (): string => Blade::render("@vite(['resources/js/app.js'])")
)
```

### Kenapa `Blade::render(...)` dan bukan langsung string `'<script src="...">'`?

Karena `@vite(...)` adalah **direktif Blade**, bukan HTML biasa — ia perlu "dikompilasi" dulu menjadi tag `<script type="module">` (di mode *dev*, mengarah ke server Vite; di *production*, mengarah ke berkas hasil *build* sesuai `manifest.json`). `Blade::render()` adalah cara menjalankan kompilasi Blade itu dari dalam kode PHP biasa (di luar siklus render *view* normal) — sama persis seperti yang sudah dipakai untuk blok `<style>` di `AdminPanelProvider`.

### Kenapa cukup memuat `app.js`, bukan `echo.js` saja?

Karena `echo.js` **bergantung** pada `bootstrap.js` (untuk konfigurasi dasar seperti Axios/CSRF) dan keduanya sudah di-*bundle* jadi satu kesatuan lewat `app.js`. Memuat `app.js` juga aman dari sisi performa: browser meng-*cache* bundel JS berdasarkan URL-nya — jika pengguna sempat membuka halaman portal publik & panel dokter, Vite/browser tidak akan mengunduhnya dua kali.

### Kenapa `HEAD_END` (bukan `BODY_END` / `FOOTER`)?

`@vite()` menghasilkan tag `<script type="module" ...>`. Skrip bertipe `module` **otomatis bersifat `defer`** menurut spesifikasi HTML — artinya browser akan tetap menjalankan parsing HTML terlebih dahulu, baru mengeksekusi skrip ini setelah DOM selesai dibangun. Jadi aman diletakkan di `<head>`; tidak perlu menunggu sampai `</body>` seperti skrip *non-module* zaman dulu. Ini juga konsisten dengan pola `HEAD_END` yang sudah dipakai di `AdminPanelProvider`.

---

## 5. Cara mengujinya

1. Buka halaman `/dokter` → **Konsultasi**, lalu buka *developer console* (F12 → tab Console).
2. Ketik `window.Echo` lalu Enter — seharusnya kini muncul objek `Echo {...}`, bukan lagi `undefined` / pesan error "Laravel Echo cannot be found".
3. Sambil halaman dokter terbuka, buka tab/browser lain sebagai **pasien**, lalu tekan "Mulai Sesi Chat" → **antrean di dashboard dokter langsung bertambah tanpa perlu refresh**.
4. Terima sesi tersebut sebagai dokter, lalu kirim balasan → **pasien langsung menerima balasan tanpa refresh**, dan sebaliknya.
5. Pastikan hanya **satu** proses `reverb:start` yang berjalan (cek lewat `netstat -ano | findstr :8080` di PowerShell) — proses dobel bisa menimbulkan masalah pengiriman yang tidak konsisten seperti dibahas di bagian 2a.

> **Update:** seluruh skenario di atas sudah diuji ulang secara end-to-end (otomatis, lewat Puppeteer) **setelah** perbaikan kedua di [05-mismatch-namespace-echo-broadcastas.md](05-mismatch-namespace-echo-broadcastas.md) ikut diterapkan — dan **semuanya lolos**: sesi baru pasien langsung muncul live di antrean dokter, status sesi & balasan chat dokter langsung muncul live di sisi pasien, semuanya tanpa reload sedikit pun. Akun & data uji yang dibuat selama pengujian sudah dibersihkan kembali. Silakan tetap coba sendiri di browser kapan saja untuk melihatnya langsung.

---

## 6. Pelajaran penting dari kasus ini

1. **"Bekerja setelah refresh, diam saat live" hampir selalu berarti masalah di jalur pengiriman real-time** (WebSocket/`window.Echo`/listener), bukan di backend — karena *refresh* = request HTTP biasa yang tidak melibatkan WebSocket sama sekali.
2. **Kegagalan `window.Echo` yang hilang bersifat *diam* (silent)** — Livewire tidak melempar error ke layar. Satu-satunya jejaknya ada di *developer console* browser (`window.Echo` → `undefined`, atau pesan "Laravel Echo cannot be found"). **Selalu cek console browser** ketika sebuah fitur "kelihatannya jalan tapi tidak live".
3. **Sistem aset Filament terisolasi dari Vite aplikasi** — setiap panel hanya memuat bundel bawaannya sendiri (`@filamentStyles`/`@filamentScripts`). Kalau sebuah panel butuh JS kustom milik aplikasi (Echo, Alpine plugin tambahan, library pihak ketiga, dsb), itu **harus disuntikkan secara eksplisit** lewat *render hook* (`->renderHook(PanelsRenderHook::HEAD_END, ...)`) atau `FilamentAsset::register()` — *tidak* otomatis terbawa.
4. **`Livewire::test()` tidak bisa mendeteksi bug semacam ini** — pengujian sisi server hanya memverifikasi pendaftaran listener & resolusi *placeholder*, tapi tidak pernah menjalankan JavaScript browser sungguhan. Bug seperti "`window.Echo` tidak ada" hanya kelihatan saat diuji di **browser nyata**.
