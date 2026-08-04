# Setup & Instalasi Reverb (Fase 0)

**Tanggal:** 2026-06-08
**Status:** ✅ Selesai & terverifikasi

> Lihat [00-konsep-dasar.md](00-konsep-dasar.md) dulu kalau istilah Reverb/Pusher/Echo masih asing.

---

## Langkah-Langkah yang Dilakukan

### 1. Install package `laravel/reverb`

```bash
composer require laravel/reverb
```

Menambahkan Reverb sebagai dependency Laravel. Paket ini berisi server WebSocket itu sendiri + integrasi konfigurasinya ke framework.

### 2. Publish konfigurasi

Perintah resminya `php artisan reverb:install` — di lingkungan otomatis ini sempat gagal di tengah proses karena ada *prompt* interaktif ("pilih driver broadcasting mana?") yang tidak bisa dijawab tanpa terminal interaktif. **Untungnya**, sebelum gagal, perintah ini sempat berhasil menerbitkan semua file yang dibutuhkan:

| File/perubahan | Fungsi |
|---|---|
| `config/broadcasting.php` | Daftar koneksi broadcasting yang tersedia (termasuk koneksi baru bernama `reverb`) |
| `config/reverb.php` | Pengaturan server Reverb itu sendiri (port, app id/key/secret, dll) |
| `routes/channels.php` | Tempat mendaftarkan *private channel* & aturan otorisasi siapa yang boleh subscribe |
| `bootstrap/app.php` | Ditambah baris `channels: __DIR__.'/../routes/channels.php'` agar Laravel tahu di mana mendaftarkan rute channel |
| `.env` | Digenerate kredensial acak: `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`, `REVERB_HOST`, `REVERB_PORT`, `REVERB_SCHEME`, beserta salinannya berprefix `VITE_*` |

Satu langkah yang **tidak** otomatis selesai (karena proses terhenti di prompt): mengubah `BROADCAST_CONNECTION` di `.env`. Ini saya lakukan manual di langkah berikut.

### 3. Aktifkan Reverb sebagai driver broadcasting

Ubah satu baris di `.env`:

```diff
- BROADCAST_CONNECTION=log
+ BROADCAST_CONNECTION=reverb
```

**Penjelasan `log`:** ini adalah driver "kosong" — broadcast tidak benar-benar terkirim ke mana pun, hanya dicatat ke file log. Cocok untuk lingkungan yang belum butuh broadcasting sungguhan. Sekarang kita ganti ke `reverb` agar broadcast benar-benar diteruskan lewat WebSocket.

### 4. Install library sisi browser

```bash
npm install --save-dev laravel-echo pusher-js
```

- **`laravel-echo`** — wrapper JavaScript yang menyederhanakan cara "mendengarkan" event dari Laravel di sisi browser
- **`pusher-js`** — library client WebSocket berprotokol Pusher. Ini "mesin" yang sebenarnya melakukan koneksi; Echo memakainya "di balik layar". (Ingat dari `00-konsep-dasar.md`: Reverb kompatibel dengan protokol Pusher, jadi library ini bisa langsung dipakai)

### 5. Buat `resources/js/echo.js`

File baru untuk mengonfigurasi koneksi Echo ke server Reverb kita:

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

**Kenapa `window.Echo` (variabel global)?** Supaya bisa dipanggil dari mana saja — termasuk nanti dari dalam komponen Livewire/Alpine.js — tanpa perlu import ulang di tiap file.

**Kenapa `import.meta.env.VITE_...`?** Ini cara Vite (asset bundler project ini) membaca variabel environment saat build. Hanya variabel berprefix `VITE_` yang "diekspos" ke kode frontend — sisanya (mis. `REVERB_APP_SECRET`) sengaja **tidak** diekspos karena rahasia (hanya boleh diketahui server).

Lalu didaftarkan agar dimuat di semua halaman, lewat `resources/js/app.js`:

```diff
  import './bootstrap';
+ import './echo';
  import 'preline';
```

### 6. Tambah dokumentasi variabel env baru ke `.env.example`

Supaya developer lain yang meng-clone project tahu variabel apa saja yang perlu digenerate ulang (lewat `php artisan reverb:install` di environment mereka sendiri — kredensial **tidak boleh** disalin antar environment).

---

## Variabel `.env` Baru — Penjelasan Tiap Variabel

| Variabel | Fungsi |
|---|---|
| `BROADCAST_CONNECTION` | Driver broadcasting yang aktif. Kita set ke `reverb` |
| `REVERB_APP_ID` / `REVERB_APP_KEY` / `REVERB_APP_SECRET` | Kredensial unik yang membuat Laravel & Reverb saling mengenali ("siapa kamu, dan apakah kamu berhak mengirim pesan ke sini?"). Mirip pasangan username/password antar sistem |
| `REVERB_HOST` / `REVERB_PORT` / `REVERB_SCHEME` | Alamat server Reverb. Default lokal: `localhost:8080` via `http` |
| `VITE_REVERB_*` | Salinan sebagian variabel di atas yang **boleh** diketahui browser (key publik, host, port, scheme — bukan secret). Prefix `VITE_` membuat Vite menyertakannya saat build JS |

---

## Menjalankan Reverb di Lokal

```bash
php artisan reverb:start
# atau dengan log detail untuk keperluan debugging:
php artisan reverb:start --debug
```

> **Penting — beda dari proses web biasa:** `php artisan serve` melayani halaman web seperti biasa (request datang → dijawab → selesai). Tapi `reverb:start` adalah proses **jangka panjang** yang harus terus menerus berjalan selama fitur chat dipakai — sama seperti server database yang harus selalu hidup. Di production nanti, proses ini perlu "dijaga" otomatis (Supervisor/systemd) agar restart sendiri jika crash.

---

## Verifikasi — Uji Coba End-to-End

Sekadar "terpasang" belum tentu berarti "berfungsi". Jadi saya melakukan uji coba nyata untuk membuktikan jalur komunikasi penuh:

**Skenario uji:**
1. Jalankan server Reverb (`reverb:start --debug`)
2. Buat *client* WebSocket sungguhan memakai Node.js + `pusher-js` (mensimulasikan apa yang nanti dilakukan browser pasien/dokter) — *subscribe* ke sebuah channel uji coba (`my-test-channel`)
3. Dari Laravel (lewat `php artisan tinker`), trigger sebuah event ke channel tersebut
4. Amati apakah client menerimanya

**Hasil:**
```
[CLIENT] Terhubung ke Reverb via WebSocket
[CLIENT] Berhasil subscribe ke channel "my-test-channel"
[CLIENT] EVENT: my-test-event {"msg":"Halo dari Laravel jam 09:57:57"}
```

✅ Pesan diterima **dalam hitungan milidetik** setelah dikirim dari Laravel — membuktikan jalur **Laravel → Reverb → Browser** sudah tersambung penuh dan siap dipakai untuk membangun fitur chat sungguhan.

File-file uji coba (`test-reverb-client.cjs` dan log sementara) sudah dihapus setelah verifikasi — tidak menjadi bagian permanen aplikasi.

---

## Ringkasan File yang Berubah

| File | Jenis perubahan |
|---|---|
| `composer.json`, `composer.lock` | + dependency `laravel/reverb` |
| `package.json`, `package-lock.json` | + dependency `laravel-echo`, `pusher-js` |
| `config/broadcasting.php` *(baru)* | Konfigurasi koneksi broadcasting |
| `config/reverb.php` *(baru)* | Konfigurasi server Reverb |
| `routes/channels.php` *(baru)* | Pendaftaran private channel & otorisasi |
| `bootstrap/app.php` | + registrasi `channels` routing |
| `.env` | + kredensial Reverb, ubah `BROADCAST_CONNECTION` |
| `.env.example` | + dokumentasi variabel env baru untuk instalasi lain |
| `resources/js/echo.js` *(baru)* | Konfigurasi koneksi Echo ↔ Reverb |
| `resources/js/app.js` | + import `./echo` |

---

## Status Checklist (Fase 0 dari rencana utama)

- [x] Install Reverb & Echo
- [x] Konfigurasi `.env` & `.env.example`
- [x] Buat & daftarkan `echo.js`
- [x] Build asset berhasil tanpa error
- [x] Uji coba broadcasting end-to-end **berhasil**

➡️ Selanjutnya: **Fase 1** — membangun skema database (tabel `sesi_konsultasi`, `konsultasi_pesan`, dll). Akan didokumentasikan di file berikutnya begitu dikerjakan.
