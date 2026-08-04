# Konsep Dasar: Reverb, Pusher Protocol, dan Echo

Dokumen ini menjelaskan istilah-istilah yang akan sering muncul saat kita membangun fitur chat — ditulis untuk yang baru pertama kali bersentuhan dengan Reverb/broadcasting di Laravel.

---

## 1. Kenapa kita butuh "broadcasting"?

Untuk fitur chat, pesan harus muncul **instan** di layar lawan bicara — tanpa perlu refresh halaman.

Cara kerja web biasa (HTTP) itu seperti **mengirim surat**: browser "bertanya" ke server, server "menjawab", lalu hubungan ditutup. Kalau mau tahu apakah ada pesan baru, browser harus terus-menerus bertanya ulang ("ada pesan baru? ada pesan baru?") — ini boros dan tetap terasa "lambat".

**Broadcasting** adalah pola di mana server bisa **mendorong** data ke browser kapan saja terjadi sesuatu — tanpa menunggu browser bertanya. Ini butuh jenis koneksi yang berbeda: **WebSocket**.

## 2. WebSocket — koneksi dua arah yang tetap terbuka

WebSocket adalah jenis koneksi yang, sekali terbentuk, **tetap terbuka** selama dibutuhkan (seperti telepon yang tersambung terus, dibanding kirim SMS bolak-balik). Lewat koneksi ini:
- Browser bisa mengirim data ke server kapan saja
- **Server juga bisa mengirim data ke browser kapan saja** — inilah kunci agar chat terasa instan

## 3. Apa itu Laravel Reverb?

**Reverb** adalah server WebSocket buatan tim Laravel sendiri (dirilis 2024, terintegrasi penuh di Laravel 11/12). Tugasnya sederhana: menerima koneksi WebSocket dari banyak browser sekaligus, lalu meneruskan pesan ("event") dari aplikasi Laravel kita ke browser-browser yang relevan.

Sebelum Reverb ada, developer Laravel umumnya memakai layanan pihak ketiga seperti **Pusher** (berbayar, di-hosting orang lain) untuk fungsi ini. Reverb membuat kita bisa **self-host** sendiri — tanpa biaya bulanan ke pihak luar, dan data tetap di server kita sendiri (relevan untuk data kesehatan yang sensitif).

## 4. Lho, kenapa kata "Pusher" masih muncul di kode kita?

Ini bagian yang sering membingungkan di awal!

Reverb **sengaja dibuat kompatibel dengan protokol Pusher** — artinya Reverb "berbicara" dalam format pesan yang **identik** dengan layanan Pusher.

> Analoginya: Pusher itu seperti "bahasa Inggris" yang sudah dipakai banyak orang & banyak alat penerjemah (`pusher-js` untuk browser, `pusher/pusher-php-server` untuk PHP) sudah dibuat untuk bahasa ini. Daripada membuat "bahasa baru" dan alat penerjemah baru, Reverb memilih "berbicara bahasa Inggris" juga — sehingga semua alat yang sudah ada bisa langsung dipakai, cukup diarahkan untuk "bicara" ke server kita sendiri (bukan ke server Pusher milik orang lain).

Jadi: **"Pusher" di sini adalah nama protokol/format komunikasi**, bukan berarti kita memakai/membayar layanan Pusher. Kita 100% self-hosted lewat Reverb.

## 5. Apa itu Laravel Echo?

**Echo** adalah library JavaScript di sisi browser yang membungkus `pusher-js` agar lebih mudah dipakai dari konteks Laravel. Berkat Echo, kita cukup menulis kode sederhana seperti:

```js
Echo.channel('nama-channel').listen('NamaEvent', (data) => {
    console.log('Pesan baru:', data);
});
```

...untuk "mendengarkan" event tertentu yang dikirim dari Laravel — tanpa perlu menangani detail teknis koneksi WebSocket secara manual.

## 6. Bagaimana semuanya saling terhubung?

```
 [Laravel / PHP]                [Server Reverb]                [Browser]
       │                              │                             │
       │   1. broadcast(Event)        │                             │
       │ ────────────────────────────▶│                             │
       │   (dikirim via HTTP,         │                             │
       │    format protokol Pusher)   │                             │
       │                              │  2. teruskan ke semua       │
       │                              │     subscriber channel      │
       │                              │     via WebSocket           │
       │                              │ ───────────────────────────▶│
       │                              │   (diterima oleh Echo +     │
       │                              │    pusher-js di browser)    │
```

1. **Laravel** memicu sebuah *event* (mis. "ada pesan baru masuk") dan mengirimkannya ke server Reverb lewat HTTP biasa — tapi dengan format pesan ala Pusher
2. **Reverb** menerima event tsb, lalu meneruskannya secara real-time ke semua browser yang sedang "berlangganan" (*subscribe*) channel terkait — lewat koneksi WebSocket yang sudah terbuka sejak awal
3. **Browser** (lewat Echo, dibantu `pusher-js`) menerima event tsb secara instan dan menjalankan kode JavaScript yang sudah kita siapkan — misalnya menambahkan bubble chat baru ke layar tanpa refresh

## 7. Istilah-istilah penting (akan sering muncul)

| Istilah | Penjelasan sederhana |
|---|---|
| **Channel** | "Saluran" topik tertentu yang bisa di-*subscribe*. Contoh rencana kita: `konsultasi.{token}` — satu channel khusus per sesi konsultasi, supaya pesan sesi A tidak "bocor" ke sesi B |
| **Event** | Nama kejadian yang di-*broadcast*, mis. `PesanDikirim`, `SesiStatusBerubah`. Browser "mendengarkan" event tertentu pada channel tertentu |
| **Broadcast** | Aksi mengirim event dari Laravel agar diteruskan ke seluruh subscriber channel terkait |
| **Public channel** | Channel yang bisa di-*subscribe* siapa saja tanpa proses login/otorisasi. *(Rencana kita pakai ini untuk pasien — karena tanpa akun, "kunci"-nya adalah token unik yang sulit ditebak)* |
| **Private channel** | Channel yang hanya bisa di-*subscribe* user yang sudah login & lolos pengecekan otorisasi (didefinisikan di `routes/channels.php`). *(Rencana kita pakai ini untuk dashboard dokter/admin)* |
| **Socket ID** | ID unik untuk tiap koneksi WebSocket browser. Berguna untuk hal seperti `->toOthers()` — supaya pengirim pesan tidak "menerima balik" pesannya sendiri lewat broadcast |

---

➡️ Lanjut ke [01-setup-instalasi.md](01-setup-instalasi.md) untuk catatan instalasi & uji coba yang sudah dilakukan.
