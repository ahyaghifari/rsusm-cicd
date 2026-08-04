# Push Notification untuk Pasien Konsultasi

**Tanggal:** 2026-06-09
**Status:** ✅ Diimplementasikan

> Lanjutan dari rangkaian fitur Konsultasi Chat Real-Time. Setelah WebSocket (Reverb/Echo) berhasil menyampaikan pesan secara real-time ke pasien yang **sedang membuka tab chat**, muncul kebutuhan baru: bagaimana jika pasien berpindah halaman, melihat tab lain, atau bahkan menutup browser — apakah mereka tetap bisa tahu ada pesan baru dari dokter?

---

## 1. Masalah: WebSocket hanya bekerja jika tab aktif dan terhubung

Sampai sebelum fitur ini dibuat, perjalanan sebuah pesan dari dokter ke pasien adalah:

```
Dokter kirim pesan
   → KonsultasiDashboard::kirimBalasan()
   → broadcast(new PesanDikirim($sesi, $pesan))->toOthers()
   → Reverb meneruskan ke semua subscriber channel "konsultasi.{token}"
   → KonsultasiChat::pesanMasuk() dipanggil → Livewire re-render
   → Pesan muncul di layar pasien ✅
```

Ini bekerja sempurna **selama** pasien membuka tab chat. Tapi ada tiga skenario di mana ini gagal:

| Skenario | Apa yang terjadi |
|---|---|
| Pasien membuka halaman lain di website | WebSocket channel `konsultasi.{token}` tidak disubscribe → pesan tidak sampai |
| Pasien melihat tab lain | Tab chat mungkin masih tersambung, tapi tidak ada notifikasi visual/audio |
| Pasien menutup browser | Koneksi WebSocket putus total → pesan tidak bisa sampai sama sekali |

---

## 2. Solusi: tiga lapisan notifikasi

Fitur ini menerapkan **tiga lapisan** yang bekerja bersama, masing-masing menangani skenario berbeda:

```
┌─────────────────────────────────────────────────────────────┐
│                    LAPISAN 1: In-app Toast                   │
│  Pasien di halaman lain dalam website yang sama              │
│  → Alpine.js subscribe Echo channel via localStorage token   │
│  → Toast muncul di pojok kanan bawah                        │
│  → Bekerja selama tab website masih terbuka                 │
├─────────────────────────────────────────────────────────────┤
│                LAPISAN 2: Browser Notification               │
│  Pasien sedang melihat tab lain (tab website masih ada)     │
│  → Notifications API: new Notification(...)                  │
│  → Notifikasi sistem operasi muncul secara instan           │
│  → Bekerja jika document.hidden === true                    │
├─────────────────────────────────────────────────────────────┤
│                 LAPISAN 3: Web Push Notification             │
│  Browser sepenuhnya ditutup                                 │
│  → Service Worker menerima push dari server                 │
│  → Notifikasi sistem operasi muncul tanpa browser aktif     │
│  → Klik notifikasi membuka/focus tab chat                   │
└─────────────────────────────────────────────────────────────┘
```

Lapisan 1 dan 2 menggunakan koneksi **Echo/WebSocket yang sudah ada** — tidak ada infrastruktur baru. Lapisan 3 membutuhkan infrastruktur baru: **VAPID keys, Service Worker, dan Web Push API**.

---

## 3. Konsep dasar: bagaimana Web Push bekerja

Sebelum masuk ke implementasi, penting untuk memahami bagaimana Web Push bekerja secara fundamental, karena ini berbeda dari WebSocket.

### 3.1 Aktor-aktor yang terlibat

```
┌─────────┐        ┌─────────────────┐        ┌──────────────────┐
│ Browser │        │  Push Service   │        │  Server Laravel  │
│ Pasien  │        │ (milik browser, │        │  (kita)          │
│         │        │  Google/Mozilla)│        │                  │
└────┬────┘        └────────┬────────┘        └────────┬─────────┘
     │                      │                          │
     │  1. subscribe()      │                          │
     │─────────────────────>│                          │
     │  ← endpoint URL      │                          │
     │                      │                          │
     │  2. kirim endpoint   │                          │
     │  ke Laravel server   │──────────────────────────│
     │                      │                          │ 3. simpan di DB
     │                      │                          │    push_subscription
     │                      │          4. HTTP POST    │
     │                      │<─────────────────────────│ (saat dokter kirim pesan)
     │                      │                          │
     │  5. push event       │                          │
     │<─────────────────────│                          │
     │  (service worker)    │                          │
     │                      │                          │
     │  6. showNotification │                          │
     │─────────────────────>│ (ke sistem operasi)      │
```

**Yang penting dipahami:**
- Server Laravel **tidak langsung** menghubungi browser pasien
- Server mengirim pesan ke **Push Service** milik pembuat browser (Google untuk Chrome, Mozilla untuk Firefox)
- Push Service-lah yang meneruskan ke browser pasien — bahkan jika browser sedang tertutup
- Ini alasan kenapa Web Push bisa bekerja tanpa browser aktif

### 3.2 VAPID Keys — "surat kuasa" server ke Push Service

VAPID (*Voluntary Application Server Identification*) adalah sepasang kunci kriptografi yang membuktikan bahwa push yang dikirim benar-benar dari server kita, bukan dari pihak lain yang menyadap *endpoint* pasien.

```
VAPID_PUBLIC_KEY   → dibagikan ke browser (untuk subscribe)
VAPID_PRIVATE_KEY  → rahasia di server (untuk menandatangani push)
```

Keduanya adalah kunci EC (Elliptic Curve) P-256, dikodekan dalam format URL-safe Base64. Dibuat sekali dan disimpan di `.env` — **jangan pernah di-commit ke git**.

### 3.3 Push Subscription — "alamat pengiriman" browser pasien

Saat browser melakukan `pushManager.subscribe()`, ia mendapatkan kembali sebuah objek `PushSubscription` dengan bentuk seperti ini:

```json
{
  "endpoint": "https://fcm.googleapis.com/fcm/send/APA91bHPRgkFLJu...",
  "keys": {
    "p256dh": "BEn7LbISGdbkc...",
    "auth":   "tBHItJI5svbpez7..."
  }
}
```

- `endpoint` — URL unik milik browser ini di Push Service
- `keys.p256dh` — kunci publik untuk enkripsi payload push
- `keys.auth` — secret untuk autentikasi

Objek ini disimpan di database (`sesi_konsultasi.push_subscription`) dalam format JSON. Server menggunakannya untuk mengirim push.

---

## 4. Alur kerja lengkap — langkah per langkah

### 4.1 Saat pasien membuka halaman chat

```
Browser memuat /konsultasi/{token}
   │
   ├─ KonsultasiChat::mount() dipanggil
   │
   ├─ Blade render → script initKonsultasiPush() dijalankan
   │
   ├─ localStorage.setItem('konsultasi_sesi', {token, url, dokterNama})
   │   └─ Ini yang memungkinkan Lapisan 1 (toast) bekerja di halaman lain
   │
   ├─ navigator.serviceWorker.register('/sw.js')
   │   └─ Browser mengunduh dan menginstal service worker
   │
   ├─ Notification.requestPermission()
   │   └─ Browser menampilkan dialog "Izinkan notifikasi dari situs ini?"
   │
   ├─ [jika diizinkan] pushManager.subscribe({applicationServerKey: VAPID_PUBLIC_KEY})
   │   └─ Browser menghubungi Push Service → mendapat endpoint unik
   │
   └─ @this.simpanPushSubscription(JSON.stringify(sub))
       └─ Livewire call → KonsultasiChat::simpanPushSubscription()
           └─ UPDATE sesi_konsultasi SET push_subscription = '...' WHERE id = ?
```

### 4.2 Saat pasien berpindah ke halaman lain

```
Pasien klik link ke halaman lain (misalnya: Beranda, Jadwal Dokter, dll.)
   │
   ├─ Livewire:navigated event fired
   │
   └─ Alpine globalKonsultasiListener._subscribe() dipanggil ulang
       │
       ├─ Baca 'konsultasi_sesi' dari localStorage
       │   └─ Token masih ada → subscribe ke Echo channel "konsultasi.{token}"
       │
       └─ Channel tersambung → siap menerima event PesanDikirim
```

### 4.3 Saat dokter mengirim pesan

```
Dokter ketik pesan → kirimBalasan() dipanggil
   │
   ├─ pesan disimpan ke DB
   │
   ├─ broadcast(new PesanDikirim($sesi, $pesan))->toOthers()
   │   └─ Reverb meneruskan ke semua subscriber channel "konsultasi.{token}"
   │       ├─ [Lapisan 1/2] Alpine listener menerima event
   │       │   ├─ isOnChat === false → tampilkan toast di pojok kanan bawah
   │       │   └─ document.hidden === true → new Notification(...)
   │       └─ [Lapisan 1/2, jika pasien masih di tab chat] KonsultasiChat::pesanMasuk()
   │
   └─ [Lapisan 3] if ($sesi->push_subscription)
       └─ SendWebPushNotification::dispatch(...)
           └─ Job masuk queue → diproses oleh queue worker
               └─ WebPush::queueNotification() → HTTP POST ke endpoint Push Service
                   └─ Push Service → Service Worker pasien → showNotification()
```

---

## 5. File-file yang dibuat dan dimodifikasi

### File baru

| File | Peran |
|---|---|
| `public/sw.js` | Service Worker: menangkap `push` event, menampilkan notifikasi, menangani klik notifikasi |
| `app/Jobs/SendWebPushNotification.php` | Laravel Job: dikirim ke queue saat dokter kirim pesan, berisi logika pengiriman Web Push ke Push Service |
| `config/webpush.php` | Konfigurasi VAPID keys (public, private, subject) yang dibaca dari `.env` |
| `database/migrations/2026_06_09_090606_add_push_subscription_to_sesi_konsultasi_table.php` | Menambah kolom `push_subscription TEXT NULL` ke tabel `sesi_konsultasi` |

### File yang dimodifikasi

| File | Perubahan |
|---|---|
| `.env` | Tambah `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT`, `VITE_VAPID_PUBLIC_KEY` |
| `app/Models/SesiKonsultasi.php` | Tambah `'push_subscription'` ke `$fillable` |
| `app/Livewire/Pages/KonsultasiChat.php` | Tambah `simpanPushSubscription()` dan `dispatch('sesi-berakhir')` di `statusBerubah()` |
| `resources/views/rumah_sakit/pages/konsultasi-chat.blade.php` | Tambah script `initKonsultasiPush()`: SW registration, push subscription, localStorage |
| `app/Filament/Dokter/Pages/KonsultasiDashboard.php` | Tambah `SendWebPushNotification::dispatch(...)` di `kirimBalasan()` |
| `resources/views/layouts/rumah_sakit.blade.php` | Tambah komponen Alpine `globalKonsultasiListener` (toast UI + Echo listener global) |

---

## 6. Service Worker secara detail (`public/sw.js`)

```js
// Install: skipWaiting() memastikan SW langsung aktif tanpa menunggu tab ditutup
self.addEventListener('install', () => self.skipWaiting());

// Activate: clients.claim() memastikan SW langsung mengontrol semua tab yang terbuka
self.addEventListener('activate', (e) => e.waitUntil(clients.claim()));
```

**Event `push`** — dijalankan saat Push Service mengirimkan pesan ke browser:

```js
self.addEventListener('push', (event) => {
    const data = event.data?.json() ?? {};

    event.waitUntil(
        self.registration.showNotification(data.title, {
            body:     data.body,
            icon:     '/img/favicon.png',
            tag:      'konsultasi-' + data.token,   // tag mencegah duplikasi
            renotify: true,                           // tetap berbunyi meski tag sama
            vibrate:  [200, 100, 200],
            data:     { url: data.url },
        })
    );
});
```

`event.waitUntil()` wajib digunakan agar browser tidak "mematikan" service worker sebelum notifikasi selesai ditampilkan.

**Event `notificationclick`** — dijalankan saat pengguna mengklik notifikasi:

```js
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetUrl = event.notification.data?.url ?? '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((windowClients) => {
                // Cari tab yang sudah membuka URL chat
                for (const client of windowClients) {
                    if (client.url === targetUrl && 'focus' in client) {
                        return client.focus();   // focus tab yang sudah ada
                    }
                }
                return clients.openWindow(targetUrl);  // buka tab baru
            })
    );
});
```

Logika ini memastikan klik tidak membuka tab duplikat jika tab chat sudah ada (meski ter-minimize).

---

## 7. Global listener dan toast (`rumah_sakit.blade.php`)

Alpine component `globalKonsultasiListener` dipasang di layout utama portal — sehingga **berjalan di semua halaman** tanpa perlu diinisialisasi ulang di setiap page.

```
Layout dimuat
   └─ Alpine init() dipanggil
       └─ _subscribe() membaca localStorage
           └─ [jika ada token] window.Echo.channel('konsultasi.{token}')
               └─ .listen('PesanDikirim', handler)
```

Saat Livewire navigate (pindah halaman tanpa full reload):

```
livewire:navigated event
   └─ _subscribe() dipanggil ulang
       └─ Cek: apakah sudah subscribe channel yang sama?
           ├─ Ya → tidak melakukan apa-apa (cegah double-subscribe)
           └─ Tidak → leave channel lama, subscribe channel baru
```

**Kenapa pengecekan channel penting?**

Tanpa pengecekan `this.channel?.name === 'konsultasi.' + sesi.token`, setiap navigasi Livewire (yang fired `livewire:navigated`) akan membuat subscription baru ke channel yang sama, sehingga setiap pesan masuk handler dipanggil berkali-kali → toast ganda, notifikasi ganda.

**Toast auto-dismiss:**

```js
_showToast(title, body, url) {
    const id = ++this._nextId;
    this.toasts.push({ id, title, body, url, visible: true });
    setTimeout(() => this.tutupToast(id), 7000);  // hilang otomatis setelah 7 detik
}
```

---

## 8. Mengapa localStorage, bukan cookie atau session?

Pilihan `localStorage` untuk menyimpan token sesi bukan kebetulan:

| Opsi | Masalah |
|---|---|
| **PHP session / cookie** | Tidak bisa dibaca oleh JavaScript tanpa endpoint tambahan (sisi server). Juga tidak "reaktif" — perlu request baru untuk tahu apakah sesi masih aktif |
| **Alpine store** | Hilang saat full-page refresh. Tidak persist antar navigasi yang melibatkan reload |
| **localStorage** ✅ | Persist selama browser tidak ditutup, bisa dibaca oleh JS di mana pun, sederhana |

Untuk keamanan: yang disimpan hanya `token` (string acak, bukan data sensitif), `url` halaman chat, dan `dokterNama`. Tidak ada data medis atau identitas pasien yang tersimpan di localStorage.

---

## 9. Job queue — mengapa tidak langsung di kirimBalasan()?

`SendWebPushNotification` adalah `ShouldQueue` Job, bukan proses sinkron. Artinya pengiriman Web Push **tidak memblokir** response Livewire ke dokter.

```
kirimBalasan() selesai → response langsung ke dokter
   └─ [di background] queue worker memproses SendWebPushNotification
       └─ HTTP POST ke Push Service (bisa lambat: 100–500ms)
       └─ Push Service meneruskan ke browser pasien
```

Jika pengiriman push ditulis sinkron langsung di `kirimBalasan()`, dokter harus menunggu respons dari Push Service (yang bisa lambat atau timeout) setiap kali mengirim pesan — pengalaman yang buruk.

**Catatan:** Queue worker harus berjalan agar Lapisan 3 (browser tertutup) bekerja:

```bash
php artisan queue:work
```

Lapisan 1 dan 2 (toast + tab notification) **tidak bergantung pada queue** — keduanya bekerja via WebSocket yang sudah tersambung.

---

## 10. Siklus hidup push subscription

```
┌─────────────────────────────────────────────────────────┐
│ Pasien buka /konsultasi/{token}                         │
│   → SW terdaftar                                        │
│   → Subscription baru (atau pakai yang sudah ada)       │
│   → Disimpan ke sesi_konsultasi.push_subscription       │
└────────────────────────┬────────────────────────────────┘
                         │
                 [Selama sesi berlangsung]
                 push dikirim saat dokter
                 mengirim pesan
                         │
┌────────────────────────▼────────────────────────────────┐
│ Sesi berakhir (SELESAI / KEDALUWARSA)                   │
│   → SesiStatusBerubah diterima                          │
│   → dispatch('sesi-berakhir')                           │
│   → localStorage 'konsultasi_sesi' dihapus              │
│   → Echo channel di-leave                               │
│   → push_subscription di DB tetap ada (tidak dihapus)  │
│     tapi tidak akan dipakai lagi karena sesi sudah SELESAI│
└─────────────────────────────────────────────────────────┘
```

**Subscription kadaluwarsa:** Jika Push Service melaporkan subscription sudah tidak valid (`isSubscriptionExpired()`), kolom `push_subscription` otomatis di-set `null` oleh job sehingga tidak ada percobaan push yang gagal di masa depan.

---

## 11. Keterbatasan dan hal yang perlu diketahui

**iOS Safari:** Web Push di iOS baru didukung mulai Safari 16.4 (iOS 16.4+, 2023) dan hanya bekerja jika website di-install sebagai PWA (Add to Home Screen). Untuk pengguna iOS yang hanya buka lewat browser biasa, Lapisan 3 tidak akan bekerja — Lapisan 1 dan 2 tetap berfungsi selama tab terbuka.

**Localhost / HTTP:** Browser modern hanya mengizinkan Web Push di HTTPS atau `localhost`. Di lingkungan development dengan `localhost:8000`, push subscription bisa diuji. Di staging/production, wajib HTTPS.

**Izin notifikasi tidak bisa diminta dua kali:** Jika pengguna menolak (`Notification.permission === 'denied'`), browser tidak akan pernah menampilkan dialog izin lagi untuk domain yang sama kecuali pengguna mereset secara manual di pengaturan browser. Karena itu, dialog izin sebaiknya muncul di momen yang tepat (saat pasien jelas-jelas butuh notifikasi — yaitu tepat saat membuka halaman chat).

**Queue harus jalan:** Tanpa `php artisan queue:work`, Lapisan 3 tidak bekerja. Lapisan 1 dan 2 tidak terpengaruh.

---

## 12. Cara menguji

### Lapisan 1 (In-app toast)

1. Buka halaman chat `/konsultasi/{token}` di browser pasien
2. Izinkan notifikasi saat diminta
3. Buka tab/window baru dalam website yang sama (misalnya halaman Beranda)
4. Dari dashboard dokter, kirim pesan
5. **Expected:** Toast muncul di pojok kanan bawah halaman Beranda dalam 1–2 detik

### Lapisan 2 (Browser notification, tab lain)

1. Buka halaman chat, izinkan notifikasi
2. Pindah ke tab lain (YouTube, Google, dll.)
3. Dari dashboard dokter, kirim pesan
4. **Expected:** Notifikasi sistem operasi muncul ("Pesan dari dr. X: ...")

### Lapisan 3 (Web Push, browser tertutup)

1. Buka halaman chat, izinkan notifikasi, tunggu hingga subscription tersimpan (cek DB)
2. **Tutup browser sepenuhnya**
3. Pastikan `php artisan queue:work` berjalan
4. Dari dashboard dokter, kirim pesan
5. **Expected:** Notifikasi sistem operasi muncul dalam beberapa detik. Klik → browser membuka halaman chat
