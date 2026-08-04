# Bug Kedua yang Tersembunyi: Namespace Echo vs `broadcastAs()`

**Tanggal:** 2026-06-08
**Status:** ✅ Diperbaiki & sudah diverifikasi end-to-end (otomatis, lewat Puppeteer) — update real-time kini berjalan di kedua arah tanpa reload.

> Lanjutan dari [04-window-echo-hilang-di-panel-filament.md](04-window-echo-hilang-di-panel-filament.md). Setelah `window.Echo` berhasil dimuat di panel Filament, harapannya update real-time langsung berjalan. Tapi ternyata **masih belum** — pengguna melaporkan: sesi baru pasien tetap tidak muncul live di dashboard dokter, dan balasan dokter tetap tidak muncul live di sisi pasien. Dokumen ini membahas bug kedua yang jauh lebih tersembunyi: **ketidakcocokan nama event antara konfigurasi Echo dan `broadcastAs()`**.

---

## 1. Gejalanya: koneksi WebSocket sudah benar, tapi listener tetap diam

Setelah perbaikan di dokumen 04 diterapkan:

- `window.Echo` **sudah ada** dan **`connected`** (terverifikasi: `state: "connected"`, ada `socketId` valid).
- Channel WebSocket **sudah ter-subscribe** dengan benar (`private-konsultasi.dokter.{id}`, `konsultasi.{token}`, dst).
- Reverb (`php artisan reverb:start --debug`) **menunjukkan broadcast benar-benar terkirim** — log menunjukkan `Broadcasting To private-konsultasi.dokter.35` lengkap dengan payload `SesiStatusBerubah` yang benar.
- ...tapi Livewire **tidak pernah memperbarui tampilan**. Tidak ada error di console. Hening total.

Ini adalah jenis bug paling menjengkelkan: **semua lapisan infrastruktur terlihat benar**, tapi hasil akhirnya tetap tidak berfungsi. Untuk kasus seperti ini, kita perlu melihat lebih dalam dari sekadar "apakah pesannya terkirim?" — menjadi "apakah pesan yang terkirim itu **benar-benar cocok** dengan apa yang didengarkan?"

---

## 2. Teknik penelusuran: memasang "telinga mentah" di channel WebSocket

Untuk membuktikan apakah pesan WebSocket benar-benar **sampai ke browser** (terlepas dari apakah Livewire meresponnya atau tidak), kita bisa memasang listener mentah langsung ke *subscription* pusher-js, melewati `Echo.listen()` sepenuhnya:

```js
const channels = window.Echo.connector.channels;
Object.entries(channels).forEach(([name, c]) => {
    c.subscription?.bind_global?.((event, data) => {
        console.log('[RAW EVENT pada ' + name + ']', event, JSON.stringify(data));
    });
});
```

`bind_global` adalah API dari pusher-js yang memanggil *callback* untuk **setiap event** yang masuk ke sebuah channel — apapun namanya, tanpa filter apapun. Ini berbeda dari `channel.listen('NamaEvent', cb)` (dipakai Echo/Livewire di balik layar) yang **hanya** memanggil *callback* kalau nama event-nya **cocok persis**.

Hasil dari teknik ini saat pasien menekan "Mulai Sesi Chat":

```
[RAW EVENT pada private-konsultasi.dokter.1] SesiStatusBerubah {"sesi":{"id":15, ...}}
```

**Pesan itu sampai!** Nama event mentahnya persis `SesiStatusBerubah`, dengan payload yang benar. Jadi masalahnya sudah pasti **bukan** di Reverb, broadcasting, otorisasi channel, ataupun koneksi WebSocket — semuanya bekerja sempurna. Masalahnya ada di **satu langkah setelah pesan ini tiba**: bagaimana Echo mencocokkan nama event mentah ini dengan nama yang didaftarkan oleh listener Livewire.

---

## 3. Akar masalah: `EventFormatter` Echo menambahkan *namespace* secara diam-diam

### Bagaimana Livewire benar-benar mendaftarkan listener-nya

Atribut `#[On('echo:konsultasi.{sesi.token},SesiStatusBerubah')]` di komponen Livewire pada akhirnya diterjemahkan (oleh `supportLaravelEcho.js` di dalam bundel Livewire) menjadi sesuatu seperti:

```js
window.Echo.channel('konsultasi.ABCD1234').listen('SesiStatusBerubah', (data) => { ... })
```

Tapi `Echo.listen()` **tidak langsung** mendaftarkan nama `'SesiStatusBerubah'` apa adanya ke pusher-js. Ia lebih dulu memprosesnya lewat sebuah kelas bernama `EventFormatter` (lihat `node_modules/laravel-echo/dist/echo.common.js`):

```js
class EventFormatter {
    constructor(namespace) {
        this.namespace = namespace;
    }

    format(event) {
        if ([".", "\\"].includes(event.charAt(0))) {
            return event.substring(1);
        }
        if (this.namespace) {
            event = this.namespace + "." + event;
        }
        return event.replace(/\.(\w+)$/, (m, group) => "\\" + group)... // kira-kira: ganti "." jadi "\"
    }
}
```

Logikanya:

1. Kalau nama event **diawali** `.` atau `\` → pakai apa adanya (tanda "saya sudah menulis nama lengkapnya sendiri, jangan diutak-atik").
2. Kalau **tidak** → **tambahkan *namespace* di depan**, lalu ganti semua `.` jadi `\` (meniru konvensi *namespace* PHP, `App\Events\NamaEvent`).

Dan inilah bagian krusialnya: **`namespace` defaultnya adalah string `"App.Events"`** kalau tidak diisi secara eksplisit di konfigurasi `new Echo({...})`.

Jadi ketika Livewire memanggil `Echo.channel(...).listen('SesiStatusBerubah', ...)`, yang **sebenarnya** didaftarkan ke pusher-js adalah:

```
App\Events\SesiStatusBerubah
```

### Bagaimana nama event "asli" ditentukan di sisi server

Di `app/Events/SesiStatusBerubah.php` (begitu juga `PesanDikirim.php`), ada method:

```php
public function broadcastAs(): string
{
    return 'SesiStatusBerubah';   // nama pendek, TANPA namespace
}
```

`broadcastAs()` inilah yang menentukan nama event yang **benar-benar dikirim** lewat WebSocket. Dan nama yang dipilih di sini adalah nama pendek — **tanpa** prefiks `App\Events\`.

### Pertemuan dua nama yang tidak pernah cocok

| Sisi | Nama event yang dipakai |
|---|---|
| Server menyiarkan (`broadcastAs()`) | `SesiStatusBerubah` |
| Browser mendengarkan (`Echo.listen()` + `EventFormatter` dgn namespace default) | `App\Events\SesiStatusBerubah` |

**Dua string ini tidak akan pernah sama persis** — dan pusher-js mencocokkan nama event secara *exact match* (persis sama), bukan "mengandung" atau "mirip". Akibatnya:

- Pesan **tiba** di channel (terbukti lewat `bind_global`).
- Tapi *callback* yang didaftarkan Livewire **tidak pernah terpicu** — karena ia menunggu nama `App\Events\SesiStatusBerubah`, sementara yang lewat adalah `SesiStatusBerubah`.
- **Tidak ada error sama sekali** — ini hanyalah dua *string* yang berbeda; dari sudut pandang pusher-js, "tidak ada yang cocok" adalah kondisi normal yang tidak perlu dilaporkan.

Inilah yang membuat bug ini begitu sulit ditemukan: **semua lapisan di bawahnya bekerja sempurna**, dan satu-satunya yang salah adalah *string* nama event yang berbeda satu karakter pun bisa berakibat fatal — sebuah **mismatch yang sepenuhnya diam (silent)**.

> **Catatan penting:** ini bukan bug yang "kadang muncul kadang tidak" atau dipengaruhi oleh urutan operasi. Ini adalah **mismatch permanen** — selama `broadcastAs()` mengembalikan nama pendek dan Echo memakai *namespace* default `App.Events`, listener Livewire **tidak akan pernah** terpicu, di kondisi apapun.

---

## 4. Perbaikan: kosongkan `namespace` di konfigurasi Echo

Di `resources/js/echo.js`, tambahkan opsi `namespace: ''`:

```js
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    namespace: '',   // <-- kuncinya
});
```

Dengan `namespace: ''` (string kosong, dianggap "falsy" oleh `if (this.namespace)`), `EventFormatter.format()` **tidak lagi menambahkan prefiks apapun** — nama event yang didaftarkan persis sama dengan yang ditulis di `#[On(...)]`, yaitu `SesiStatusBerubah`. Karena ini **persis sama** dengan apa yang dikembalikan `broadcastAs()` di server, akhirnya nama event di kedua sisi **cocok**, dan *callback* Livewire pun terpicu dengan benar.

### Kenapa tidak mengubah `broadcastAs()` saja agar mengembalikan `App\Events\SesiStatusBerubah`?

Bisa secara teori, tapi **tidak disarankan**:

- `broadcastAs()` dengan nama pendek adalah **konvensi umum** di ekosistem Laravel — banyak contoh, paket pihak ketiga, dan dokumentasi resmi memakai pola ini.
- Mengubahnya berarti nama event yang terkirim lewat WebSocket akan memuat karakter `\` (backslash) — tidak ramah dibaca saat *debugging* lewat `reverb:start --debug` atau alat pemantauan WebSocket lain.
- `namespace: ''` adalah **satu baris konfigurasi**, sementara mengubah `broadcastAs()` berarti menyentuh **setiap event broadcast** yang ada (dan akan ada) di project — jelas `namespace: ''` jauh lebih sederhana dan lebih mudah dikelola ke depannya.

---

## 5. Cara mengujinya

1. Pastikan sudah menjalankan `npm run build` (atau `npm run dev`) setelah mengubah `echo.js`, supaya bundel baru ter-*compile*.
2. Buka *developer console* di halaman dashboard dokter, ketik `window.Echo.options.namespace` (atau `window.Echo.connector.options.namespace`) — seharusnya menghasilkan `""` (string kosong), bukan `undefined`/`"App.Events"`.
3. Ulangi skenario di [bagian 5 dokumen 04](04-window-echo-hilang-di-panel-filament.md#5-cara-mengujinya): pasien mulai sesi → harus langsung muncul di antrean dokter; dokter terima & balas → status dan pesan harus langsung berubah di sisi pasien. **Semuanya tanpa reload.**

> **Sudah diverifikasi:** pengujian end-to-end otomatis (Puppeteer, dua sesi browser terpisah untuk dokter & pasien) sudah dijalankan setelah perbaikan ini diterapkan, dan ketiga skenario di atas **lolos semua** — sesi baru muncul live di antrean dokter, status sesi berubah live di sisi pasien, dan balasan chat dokter langsung muncul live di sisi pasien. Akun & data uji yang dibuat selama pengujian sudah dihapus kembali.

---

## 6. Pelajaran penting dari kasus ini

1. **"Koneksi sukses" tidak sama dengan "fitur berfungsi".** `window.Echo` bisa `connected`, channel bisa ter-*subscribe*, broadcast bisa benar-benar terkirim — dan fitur **tetap** tidak berjalan, kalau ada *string* di tengah jalan yang tidak cocok. Selalu verifikasi **ujung-ke-ujung**, bukan hanya "apakah komponennya hidup".
2. **Library punya konfigurasi *default* yang tidak terlihat di kode kita** — `namespace: "App.Events"` tidak pernah kita tulis, tapi tetap aktif dan memengaruhi perilaku. Saat *debugging* masalah "tidak cocok" semacam ini, baca kode sumber library (`node_modules/...`) untuk menemukan nilai *default* yang diam-diam berperan.
3. **Teknik `bind_global` adalah alat diagnosis yang sangat berharga** untuk masalah broadcasting — ia membuktikan **dengan pasti** apakah pesan benar-benar tiba di browser, melewati seluruh lapisan pencocokan nama Echo/Livewire. Kalau `bind_global` menangkap event tapi `Echo.listen()`/listener Livewire tidak, maka **hampir pasti** masalahnya ada di pencocokan nama event — persis seperti kasus ini.
4. **Dua bug independen bisa bertumpuk dan saling menyamarkan.** Bug `window.Echo` hilang (dokumen 04) membuat *segalanya* diam — termasuk menyembunyikan bug *namespace mismatch* ini di baliknya. Setelah bug pertama diperbaiki, baru bug kedua "muncul ke permukaan". Ini mengapa penting untuk **menguji ulang dari awal** setiap kali sebuah perbaikan diterapkan, bukan berasumsi "satu bug ditemukan = semua beres".
5. **`broadcastAs()` dengan nama pendek + `namespace: ''` di Echo adalah pasangan konfigurasi yang harus selalu berjalan beriringan** di project ini. Kalau suatu saat menambahkan event broadcast baru, pastikan nama di `broadcastAs()` (sisi server) dan nama di `#[On('echo:...')]` (sisi Livewire) **sama persis** — karena `namespace` sudah dikosongkan, tidak ada lagi "penerjemahan" otomatis di antara keduanya.
