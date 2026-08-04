# Pesan Pertama Pasien Tidak Muncul Live di Dashboard Dokter (Race Condition Subscribe Channel)

**Tanggal:** 2026-06-08
**Status:** ✅ Diperbaiki — menunggu konfirmasi pengujian dari pengguna di browser (lihat bagian 5)

> Lanjutan dari [05-mismatch-namespace-echo-broadcastas.md](05-mismatch-namespace-echo-broadcastas.md). Setelah bug *namespace* diperbaiki, update real-time mulai berjalan di kedua arah — tapi pengguna menemukan satu gejala baru yang lebih halus: **pesan pertama dari pasien tidak muncul live di dashboard dokter** (harus reload sekali), padahal pesan-pesan berikutnya muncul live tanpa masalah, dan arah sebaliknya (balasan dokter → pasien) selalu berhasil sejak pesan pertama.

---

## 1. Gejalanya: hanya pesan **pertama** dari pasien yang "hilang", lalu semuanya normal

Pola yang dilaporkan pengguna:

1. Pasien mulai sesi → dokter menerima sesi (status berubah jadi "Berlangsung", live, tanpa reload — ini sudah benar).
2. Pasien mengirim **pesan pertama** → ❌ **tidak muncul** di dashboard dokter. Dokter harus me-*refresh* halaman untuk melihatnya.
3. Setelah *refresh*, pesan pertama itu **muncul** (jadi datanya sebenarnya sudah tersimpan dengan benar di database — bukan masalah backend).
4. Pasien mengirim pesan **kedua, ketiga, dst.** → ✅ **semuanya muncul live** tanpa reload sama sekali.
5. Sebaliknya: balasan **pertama** dokter ke pasien selalu langsung muncul live di sisi pasien — tidak pernah ada masalah serupa di arah ini.

Petunjuk paling penting di sini adalah **asimetrinya**: kenapa hanya *pesan pertama dari pasien* yang bermasalah, sementara *pesan pertama dari dokter* selalu lancar? Jawabannya ada pada **kapan masing-masing sisi mulai "mendengarkan" channel chat itu**.

---

## 2. Akar masalah: channel chat di sisi dokter baru disubscribe *persis* saat sesi diterima

### Sisi pasien: subscribe terjadi di awal, jauh sebelum chat dimulai

Komponen `KonsultasiChat` (halaman pasien) di-*mount* dengan `$sesi` yang sudah final — token sesi sudah ada di URL sejak pasien pertama kali membuka halaman ini (tepat setelah `mulaiSesi()` me-*redirect* ke `/konsultasi/{token}`):

```php
#[On('echo:konsultasi.{sesi.token},PesanDikirim')]
public function pesanMasuk(array $payload): void { ... }
```

`{sesi.token}` di sini **tidak pernah berubah** selama komponen hidup — jadi *channel* `konsultasi.{token}` sudah ter-*subscribe* sejak halaman dimuat pertama kali, biasanya **jauh** sebelum dokter sempat menerima sesi & mengetik balasan pertamanya. Saat balasan pertama dokter disiarkan, pasien sudah lama "siap mendengarkan".

### Sisi dokter: subscribe baru terjadi *tepat* saat tombol "Terima Sesi" diklik

Komponen `KonsultasiDashboard` (dashboard dokter) berbeda — ia menangani **banyak sesi** sekaligus (antrean), jadi `sesiAktifToken` adalah properti **dinamis** yang berubah setiap kali dokter memilih/menerima sesi yang berbeda:

```php
public string $sesiAktifToken = '';

#[On('echo:konsultasi.{sesiAktifToken},PesanDikirim')]
public function pesanMasuk(): void { ... }

public function terima(int $sesiId): void
{
    $sesi->update(['status' => StatusSesiKonsultasi::BERLANGSUNG, /* ... */]);
    broadcast(new SesiStatusBerubah($sesi))->toOthers();
    $this->pilihSesiInternal($sesi->id);   // <-- sesiAktifToken berubah DI SINI
}
```

Begini urutan kejadiannya saat dokter klik "Terima Sesi":

1. Server memproses `terima()`: status sesi di-update, `SesiStatusBerubah` disiarkan ke pasien, dan `$this->sesiAktifToken` diisi dengan token sesi yang baru diterima.
2. Response Livewire (HTML baru) dikirim balik ke browser dokter.
3. Browser me-*morph* DOM-nya — dan **baru di titik inilah** Livewire (lewat `supportLaravelEcho.js`) menyadari listener `#[On('echo:konsultasi.{sesiAktifToken},...')]` kini menunjuk ke channel yang berbeda, lalu memanggil `window.Echo.channel('konsultasi.' + token)`.
4. *Subscribe* ke channel baru ini **bukan operasi instan** — perlu *round-trip* lewat WebSocket (`pusher:subscribe` → server membalas `pusher_internal:subscription_succeeded`) sebelum channel benar-benar siap menerima event.

**Sementara itu**, di sisi pasien: begitu menerima broadcast `SesiStatusBerubah` (yang dikirim di langkah 1 — bahkan *sebelum* dashboard dokter selesai *subscribe*!), status sesi pasien langsung berubah jadi "Berlangsung", kotak chat langsung aktif, dan pasien — yang sudah menunggu — bisa langsung mengetik & mengirim pesan pertamanya **dalam hitungan saat**.

Kalau pesan pertama itu disiarkan **sebelum** langkah 4 selesai, maka:

- Reverb tetap mengirimkannya — tapi hanya ke koneksi yang **sudah** ter-*subscribe* ke channel itu.
- Dashboard dokter belum (atau baru saja mulai) ter-*subscribe* → **pesan itu tidak pernah sampai** ke listener `pesanMasuk()`.
- Tidak ada *retry* atau *replay* — begitu momen itu lewat, pesannya hilang dari sudut pandang *real-time* (meski tetap tersimpan aman di database).
- *Callback* `pesanMasuk()` tidak pernah terpanggil → tidak ada re-render → tampilan tetap menampilkan riwayat chat yang lama, sampai ada pemicu lain (refresh manual, atau — sebelum perbaikan ini — tidak ada apa pun).

Inilah **race condition**: dua proses berjalan hampir bersamaan (dokter *subscribe* ke channel baru vs. pasien mengirim pesan pertama), dan hasilnya bergantung pada **siapa yang sampai duluan** — sesuatu yang bisa berbeda-beda setiap kali dicoba (kadang "beruntung" pesan pertama tetap masuk kalau pasien sedikit lebih lambat mengetik, kadang tidak).

> **Kenapa pesan kedua dst. selalu aman?** Karena begitu *subscribe* selesai (yang hanya makan waktu pecahan detik), channel-nya sudah aktif penuh — semua pesan setelahnya tiba seperti biasa.

---

## 3. Kenapa bukan bug *namespace* (dokumen 05) lagi?

Penting dibedakan dari bug sebelumnya:

| | Bug *namespace* (dok. 05) | Bug *race condition* (dok. ini) |
|---|---|---|
| Siapa yang kena | **Semua** event, di **semua** kondisi | **Hanya** event pertama setelah *channel* baru di-*subscribe* |
| Sifatnya | **Permanen** — selalu gagal, 100% dari waktu | **Kadang-kadang** — bergantung siapa "menang" |
| Penyebab | Nama event yang didengarkan ≠ nama yang disiarkan (*string mismatch*) | *Channel* belum selesai *subscribe* saat event pertama tiba (*timing*) |
| Solusi | Konfigurasi statis (`namespace: ''`) | Mekanisme *fallback* yang berjalan independen dari *listener* WebSocket |

Inilah kenapa bug ini baru terlihat **setelah** bug *namespace* diperbaiki — sebelumnya, *semua* listener mati total sehingga "semuanya butuh refresh" terlihat seperti satu masalah besar yang seragam. Begitu listener mulai berfungsi, baru terlihat ada satu celah kecil yang tersisa: jendela waktu sempit di awal setiap sesi chat baru.

---

## 4. Perbaikan: `wire:poll` sebagai jaring pengaman di sisi dokter

Alih-alih mencoba "memenangkan" perlombaan itu (misalnya dengan menunda tombol kirim pasien — solusi yang rapuh dan terasa lambat bagi pengguna), pendekatan yang lebih kokoh adalah: **berikan dashboard dokter cara independen untuk "menengok ulang" data terbaru**, yang tidak bergantung sama sekali pada listener WebSocket yang mungkin terlewat.

Solusinya hanya **satu baris** ditambahkan di `resources/views/filament/dokter/pages/konsultasi-dashboard.blade.php`, pada `<div>` yang menampilkan riwayat chat sesi aktif:

```blade
<div
    id="dokter-chat-msgs"
    class="flex flex-col gap-3 h-[55vh] min-h-[360px] overflow-y-auto pr-1"
    wire:poll.visible.5s
    x-init="..."
>
```

### Apa yang dilakukan `wire:poll.visible.5s`?

`wire:poll` adalah fitur bawaan Livewire: ia membuat browser mengirim *request* ke server **secara berkala**, memicu re-render komponen — persis seperti yang terjadi saat sebuah event Echo berhasil ditangkap. Karena `getViewData()` di `KonsultasiDashboard` **selalu** membaca ulang `sesiAktif()` (dan relasi `pesan`-nya) langsung dari database setiap kali komponen dirender — *apapun* yang memicu render itu — maka *polling* ini otomatis "menangkap" pesan mana pun yang mungkin terlewat oleh WebSocket, termasuk pesan pertama yang menjadi korban *race condition* di atas.

Penjelasan setiap bagiannya:

- **`5s`** — jeda antar polling. Cukup singkat agar pesan yang terlewat tetap terasa "hampir real-time" (maksimal nunggu ~5 detik), tapi cukup jarang agar tidak membebani server dengan *request* yang berlebihan.
- **`.visible`** — modifier yang menghentikan polling saat tab/jendela browser **tidak terlihat** (di-*minimize* atau pindah tab), dan otomatis melanjutkannya saat terlihat lagi. Ini mencegah dashboard dokter terus menerus memukul server dengan *request* AJAX padahal tidak sedang dilihat siapa pun.
- **Diletakkan di dalam blok `@else`** (hanya muncul saat `$sesiAktif` ada isinya) — artinya polling **hanya aktif saat dokter sedang membuka sebuah sesi chat**, bukan terus-menerus berjalan di latar belakang dashboard. Sesuai kebutuhan: jaring pengaman ini memang hanya relevan persis di momen rawan (channel baru saja berganti).

### Kenapa pendekatan ini, bukan yang lain?

Beberapa alternatif yang dipertimbangkan dan kenapa tidak dipilih:

1. **Menunda tombol kirim pasien beberapa detik setelah status berubah jadi "Berlangsung"** — secara teknis bisa "memenangkan" perlombaannya, tapi terasa aneh & lambat dari sudut pandang pasien (kenapa harus menunggu untuk mengetik?), dan rapuh — berapa lama jeda yang "cukup aman" akan selalu jadi tebakan yang bisa salah di kondisi jaringan lambat.
2. **Menunggu konfirmasi *subscribe* lewat `Echo.channel(...).subscribed(callback)` sebelum menganggap dashboard "siap"** — ini adalah solusi paling "tepat sasaran" secara teori (langsung menutup celah race condition-nya), tapi memerlukan JavaScript kustom yang cukup rumit (membaca *internal state* `pusher-js`, menghindari *double-subscribe* dengan listener bawaan Livewire, dll.) — kompleksitas yang besar untuk masalah yang sebenarnya bisa diatasi jauh lebih sederhana.
3. **`wire:poll` (dipilih)** — satu baris, memakai fitur bawaan Livewire yang sudah teruji, dan **otomatis** ikut menutupi *race condition* serupa di masa depan (mis. kalau nanti ditambah fitur baru yang juga bergantung pada *channel* dinamis) tanpa perlu dipikirkan ulang. Ini adalah pola umum di aplikasi *real-time* produksi: **WebSocket untuk kecepatan, *polling* sebagai jaring pengaman keandalan** — keduanya saling melengkapi, bukan saling menggantikan.

> **Catatan:** solusi ini sengaja **tidak** mengubah `KonsultasiChat` (sisi pasien) — karena di sana `{sesi.token}` bersifat statis sejak `mount()`, *channel*-nya sudah ter-*subscribe* jauh sebelum ada pesan apapun yang mungkin terlewat. Menambahkan `wire:poll` di sana hanya akan menambah beban server tanpa menutup celah apapun (karena memang tidak ada celah di sisi itu).

---

## 5. Cara mengujinya (silakan coba sendiri di browser)

Skenario yang **secara khusus** memicu *race condition* ini (penting: lakukan secepat mungkin setelah menerima sesi, supaya benar-benar menguji jendela waktu yang sempit):

1. Buka dashboard dokter di satu browser/tab, dan halaman pasien (mulai sesi konsultasi) di tab/browser lain — posisikan keduanya berdampingan.
2. Sebagai pasien, isi formulir & klik "Mulai Sesi Chat".
3. Sebagai dokter, klik "Terima Sesi" pada sesi yang baru muncul.
4. **Secepat mungkin** (idealnya dalam &lt;1 detik setelah klik terima), sebagai pasien langsung ketik pesan pendek & kirim.
5. Amati dashboard dokter **tanpa melakukan apapun** (jangan refresh, jangan klik apapun) — pesan pertama itu seharusnya **tetap muncul** dalam waktu maksimal ~5 detik (berkat `wire:poll.visible.5s`), meskipun mungkin tidak se-instan pesan-pesan berikutnya.
6. Lanjutkan kirim beberapa pesan susulan dari pasien — semuanya harus tetap muncul **lebih cepat** (nyaris instan, lewat listener WebSocket yang sudah aktif), bukan menunggu siklus polling 5 detik berikutnya.

> Kalau kamu masih melihat pesan pertama "telat" sampai beberapa detik (bukan instan), itu **wajar dan diharapkan** — itu tandanya jaring pengaman *polling*-nya yang bekerja (bukan WebSocket-nya), persis seperti yang dirancang. Yang penting: **tidak perlu reload manual lagi**.

---

## 6. Pelajaran penting dari kasus ini

1. **"Kadang jalan, kadang tidak" adalah ciri khas *race condition*** — berbeda dari bug *namespace* (dok. 05) yang gagal 100% dari waktu secara konsisten, *race condition* hanya muncul ketika dua proses kebetulan tumpang tindih dalam jendela waktu yang sangat sempit. Ini membuatnya jauh lebih sulit di-*reproduce* secara konsisten, dan sering "tersembunyi" sampai pengguna nyata membuat kondisi yang pas (di sini: pasien yang sudah siap mengetik begitu status berubah).
2. **Listener dinamis (`{propertiYangBerubah}`) punya jendela waktu rawan setiap kali nilainya berubah** — channel lama ditinggalkan, channel baru baru mulai di-*subscribe*, dan ada celah singkat di antaranya di mana event bisa "lewat tanpa terdengar". Kalau sebuah komponen sering berganti-ganti channel yang didengarkannya (seperti dashboard dokter yang melayani banyak sesi), pertimbangkan jaring pengaman semacam ini sejak awal.
3. **WebSocket murni tidak menjamin pengiriman (*no delivery guarantee*)** — Reverb (seperti kebanyakan sistem *publish-subscribe* berbasis WebSocket) hanya mengirim ke koneksi yang **sedang** ter-*subscribe* pada saat pesan disiarkan. Tidak ada antrean, tidak ada *replay*, tidak ada "pesan tertunda menunggu pendengar siap". Untuk fitur yang *harus* selalu konsisten (seperti riwayat chat), kombinasikan dengan mekanisme yang membaca langsung dari sumber kebenaran (database) secara berkala — `wire:poll` adalah cara termudah melakukan ini di Livewire.
4. **`wire:poll.visible` adalah pola "WebSocket + polling" yang umum di produksi** — bukan tanda bahwa WebSocket-nya "gagal" atau tidak berguna. WebSocket tetap memberi pengalaman *instan* untuk kasus normal (99% dari waktu), sementara *polling* hanya menjadi jaring pengaman untuk kasus tepi yang jarang terjadi. Keduanya bekerja sama, bukan saling menggantikan.
5. **Asimetri antara dua sisi (pasien vs. dokter) adalah petunjuk berharga** — begitu disadari bahwa hanya satu arah yang bermasalah, pertanyaan kuncinya jadi jelas: "apa yang **berbeda** antara kedua sisi ini?" Jawabannya — channel statis vs. dinamis — langsung mengarahkan ke akar masalah tanpa perlu menebak-nebak banyak kemungkinan lain.
