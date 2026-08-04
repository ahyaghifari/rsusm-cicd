# Broadcasting Pertama: Event, Channel Publik, dan Mendengarkan di Livewire (Fase 2)

**Tanggal:** 2026-06-08
**Status:** ✅ Selesai & terverifikasi end-to-end (penggunaan Reverb pertama yang sungguhan, bukan sekadar uji coba)

> Lihat [00-konsep-dasar.md](00-konsep-dasar.md) untuk istilah dasar, dan [01-setup-instalasi.md](01-setup-instalasi.md) untuk cara Reverb terpasang & diuji pertama kali.

---

## Apa yang dibangun di sesi ini?

Alur lengkap "Tanya Dokter": pasien memilih dokter → mengisi nama & kontak → sesi chat dibuat → pasien diarahkan ke halaman chat (`/{rumahsakit}/konsultasi/{token}`) yang **hidup secara real-time** lewat Reverb — termasuk ruang tunggu, chat aktif dengan timer, dan transkrip setelah sesi berakhir.

Tiga konsep Reverb baru dipakai di sini: **Event broadcast**, **Channel publik**, dan **listener di Livewire**. Ketiganya dijelaskan satu per satu di bawah.

---

## 1. Event Broadcast — "surat pemberitahuan" yang dikirim lewat WebSocket

Di Laravel, *event* biasa hanya berjalan di server (mis. "user mendaftar" → kirim email). **Event broadcast** adalah event yang juga **dipancarkan ke browser** lewat Reverb begitu terjadi.

Kita membuat dua event baru di `app/Events/`:

```php
class PesanDikirim implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public SesiKonsultasi $sesi,
        public KonsultasiPesan $pesan,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('konsultasi.' . $this->sesi->token)];
    }

    public function broadcastAs(): string
    {
        return 'PesanDikirim';
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->pesan->id,
            'pengirim'   => $this->pesan->pengirim->value,
            'isi'        => $this->pesan->isi,
            'created_at' => $this->pesan->created_at->toIso8601String(),
        ];
    }
}
```

Yang kedua, `SesiStatusBerubah`, strukturnya sama tapi membawa data status sesi (dipakai nanti saat dokter "menerima" sesi di Fase 3 — lihat catatan di bagian akhir).

### `ShouldBroadcastNow` vs `ShouldBroadcast`

Laravel punya dua interface untuk event broadcast:
- **`ShouldBroadcast`** — event masuk **antrian (queue)** dulu, baru dipancarkan. Cocok untuk hal yang tidak mendesak (mis. notifikasi).
- **`ShouldBroadcastNow`** — dipancarkan **seketika**, tanpa antrian. Kita pakai ini karena chat harus terasa instan — menunggu giliran di antrian akan terasa "lemot".

### `broadcastOn()` — "ke channel mana surat ini dikirim?"

Mengembalikan daftar `Channel` (atau `PrivateChannel`/`PresenceChannel`) tempat event akan dipancarkan. Di sini: `konsultasi.{token-sesi}` — satu channel unik per sesi chat, supaya pasien A tidak menerima pesan milik sesi pasien B.

### `broadcastAs()` — nama event di sisi browser

Tanpa method ini, Laravel mengirim nama event lengkap dengan namespace (`App\Events\PesanDikirim` jadi `.app.events.pesan-dikirim` atau semacamnya — cukup merepotkan untuk dicocokkan di JS). `broadcastAs()` memberi nama pendek (`PesanDikirim`) yang dipakai browser untuk "mencocokkan" listener-nya.

### `broadcastWith()` — apa isi suratnya?

Tanpa method ini, Laravel akan men-serialize **seluruh model** (termasuk relasi yang ter-load, timestamp internal, dll) sebagai payload. Itu boros & berpotensi membocorkan data yang tidak perlu sampai ke browser. `broadcastWith()` membiarkan kita memilih sendiri field apa saja yang dikirim — di sini cukup empat field untuk merender satu gelembung chat.

---

## 2. Channel Publik vs Private — kenapa kita pilih publik?

Laravel punya tiga jenis channel:

| Jenis | Siapa yang bisa subscribe? | Perlu didaftarkan di `routes/channels.php`? |
|---|---|---|
| `Channel` (publik) | **Siapa saja** yang tahu nama channel-nya | Tidak |
| `PrivateChannel` | Hanya user **terautentikasi** yang lolos cek otorisasi | Ya — pakai *closure* otorisasi |
| `PresenceChannel` | Sama seperti private, plus daftar "siapa saja yang sedang online" di channel itu | Ya |

Pasien di fitur "Tanya Dokter" **tidak punya akun** (sengaja dibuat begitu — lihat [issues/tanya-dokter-plan.md](../issues/tanya-dokter-plan.md), bagian "akses tanpa akun"). Karena `PrivateChannel` mensyaratkan login, pilihan jatuh ke **channel publik**.

**Lalu, apa yang menjaga keamanannya?** Nama channel-nya sendiri: `konsultasi.{token}`, di mana `token` adalah **UUID v4** (122-bit, praktis mustahil ditebak — sama seperti cara kerja link undangan Zoom/Google Meet). Siapa pun yang memegang token bisa subscribe ke channel itu, tapi untuk *mendapatkan* token, mereka harus melalui alur resmi (membuat sesi via `TanyaDokter::mulaiSesi()`).

Karena bersifat publik, **tidak ada baris baru yang perlu ditambahkan ke `routes/channels.php`** — item 2.6 di rencana selesai "secara otomatis" begitu kita memilih `new Channel(...)` (bukan `new PrivateChannel(...)`).

> **Untuk nanti (Fase 3):** sisi dokter/admin **akan** pakai `PrivateChannel`, karena mereka login lewat Filament dan harus diverifikasi ("apakah user ini benar dokter yang menangani sesi ini?"). Itulah saatnya `routes/channels.php` mulai terisi.

---

## 3. Mendengarkan Event di Livewire — `#[On('echo:channel,Event')]`

Bagian paling "ajaib" dari Livewire: kita bisa mendengarkan event broadcast langsung dari dalam komponen PHP, tanpa menulis JavaScript sama sekali.

```php
class KonsultasiChat extends RsPortalComponent
{
    public SesiKonsultasi $sesi;
    public array $riwayat = [];

    #[On('echo:konsultasi.{sesi.token},PesanDikirim')]
    public function pesanMasuk(array $payload): void
    {
        $this->riwayat[] = $payload;
    }

    #[On('echo:konsultasi.{sesi.token},SesiStatusBerubah')]
    public function statusBerubah(): void
    {
        $this->sesi->refresh();
    }
}
```

### Bagaimana cara kerjanya di balik layar?

1. Saat komponen pertama kali dirender, Livewire membaca atribut `#[On(...)]` dan **menerjemahkannya menjadi instruksi untuk `window.Echo`** (yang sudah kita siapkan di [01-setup-instalasi.md](01-setup-instalasi.md)) — semacam "tolong subscribe ke channel ini, dan kalau ada event bernama itu, panggil method ini".
2. Browser membuka koneksi WebSocket ke Reverb dan subscribe ke channel publik `konsultasi.<token-asli>`.
3. Begitu server memanggil `broadcast(new PesanDikirim(...))`, Reverb meneruskannya ke semua browser yang subscribe ke channel itu.
4. Echo menerima event, mencocokkan namanya (`PesanDikirim`, hasil dari `broadcastAs()`), lalu memanggil method `pesanMasuk()` di komponen Livewire — **dengan `broadcastWith()` sebagai argumen `$payload`**.
5. Livewire merender ulang bagian halaman yang berubah (di sini: daftar `$riwayat` bertambah satu gelembung chat) — semua **tanpa reload halaman**.

### `{sesi.token}` — *placeholder* dinamis

Pertanyaan yang muncul saat merancang ini: nama channel-nya unik per sesi (`konsultasi.<token>`), tapi atribut PHP (`#[On(...)]`) ditulis sekali saja di class — bagaimana ia tahu token milik sesi yang sedang dibuka?

Jawabannya: Livewire mendukung **placeholder** berbentuk `{nama.properti}` di dalam string listener. Saya memverifikasi ini dengan membaca langsung kode sumber `vendor/livewire/livewire/src/Features/SupportEvents/SupportEvents.php` — ada method `replaceDynamicPlaceholders()` yang mengganti `{sesi.token}` dengan `data_get($component, 'sesi.token')`, alias **nilai `$this->sesi->token` yang sebenarnya**, sebelum listener didaftarkan ke Echo.

Hasilnya bisa dilihat langsung di "memo" Livewire saat halaman dimuat (diambil saat pengujian end-to-end di bawah):

```json
"listeners": [
    "echo:konsultasi.b8b061f5-4792-4d6e-9412-d630da8470ab,PesanDikirim",
    "echo:konsultasi.b8b061f5-4792-4d6e-9412-d630da8470ab,SesiStatusBerubah"
]
```

Placeholder `{sesi.token}` **sudah berubah menjadi token UUID asli** sebelum dikirim ke browser — persis seperti yang direncanakan. Ini konfirmasi nyata (bukan cuma asumsi dari membaca dokumentasi) bahwa pola ini bekerja sesuai desain.

---

## 4. Sisi Pengirim — `broadcast(...)->toOthers()`

Saat pasien mengirim pesan (`KonsultasiChat::kirim()`):

```php
public function kirim(): void
{
    $this->validate();
    abort_unless($this->sesi->status === StatusSesiKonsultasi::BERLANGSUNG, 403);

    $pesan = $this->sesi->pesan()->create([
        'pengirim' => PengirimPesan::PASIEN,
        'isi'      => $this->pesanBaru,
    ]);

    // Tambahkan ke riwayat lokal dulu — supaya pengirim langsung melihat pesannya sendiri
    $this->riwayat[] = [...];
    $this->reset('pesanBaru');

    // Lalu pancarkan ke browser LAIN yang sedang membuka sesi yang sama
    broadcast(new PesanDikirim($this->sesi, $pesan))->toOthers();
}
```

**Kenapa `->toOthers()`?** Tanpa ini, broadcast juga akan dikirim balik ke browser pengirim sendiri — menyebabkan pesannya **muncul dua kali** (sekali dari hasil aksi Livewire langsung, sekali lagi dari WebSocket). `->toOthers()` memberitahu Reverb: "kirim ke semua yang subscribe, **kecuali** koneksi yang memicu aksi ini".

---

## 5. Timer Real-Time — dihitung dari server, bukan browser

Sesi chat punya batas waktu (`durasi_menit`). Supaya refresh halaman atau koneksi terputus-sambung tidak "mereset" hitungan mundur, server menyimpan **`berakhir_at`** (waktu absolut kapan sesi berakhir) — bukan "sisa detik" yang harus terus disinkronkan.

Browser cukup menghitung selisih sekali saat halaman dimuat, lalu menjalankan hitungan mundur sendiri di Alpine.js:

```js
function konsultasiTimer(berakhirAt) {
    return {
        label: '--:--',
        target: berakhirAt ? new Date(berakhirAt).getTime() : null,
        start() { this.tick(); this.timer = setInterval(() => this.tick(), 1000); },
        tick() {
            const diff = Math.max(0, Math.floor((this.target - Date.now()) / 1000));
            this.label = String(Math.floor(diff/60)).padStart(2,'0') + ':' + String(diff%60).padStart(2,'0');
            if (diff <= 0) { this.habis = true; clearInterval(this.timer); }
        }
    };
}
```

Timer ini di-"resync" otomatis setiap kali event `SesiStatusBerubah` diterima (karena Livewire merender ulang dengan `berakhir_at` terbaru dari server).

---

## Verifikasi — Uji Coba End-to-End

Dijalankan dengan tiga proses berjalan bersamaan (persis seperti yang dijelaskan di [01-setup-instalasi.md](01-setup-instalasi.md) — `reverb:start` adalah proses jangka panjang):

```bash
php artisan serve --port=8123
php artisan reverb:start
npm run dev
```

Yang diuji & hasilnya:

| Yang diuji | Cara | Hasil |
|---|---|---|
| Landing `tanya-dokter` menampilkan dokter & tombol aktif | `curl` ke halaman, cek HTML | ✅ HTTP 200, tombol `wire:click="pilihDokter(1)"` & badge "Tersedia" muncul |
| Pembuatan sesi (`mulaiSesi`) | Simulasi langsung lewat Tinker (membuat `SesiKonsultasi` dengan token UUID) | ✅ Token tergenerasi, redirect URL benar |
| Halaman chat — status `MENUNGGU` | `curl` ke `/konsultasi/{token}` | ✅ Tampilan ruang tunggu ("Menunggu dokter menerima sesi Anda…") muncul |
| Halaman chat — status `BERLANGSUNG` | Ubah status via Tinker + `broadcast(new SesiStatusBerubah(...))->toOthers()`, lalu reload | ✅ Antarmuka chat aktif + `konsultasiTimer('2026-06-08T11:15:13+08:00')` ter-inisialisasi dengan `berakhir_at` yang benar |
| Pengiriman & penerimaan pesan | Simulasi `broadcast(new PesanDikirim(...))->toOthers()` untuk pesan pasien & dokter | ✅ Kedua gelembung chat tampil dengan gaya berbeda (`rounded-br-[4px]` utk pasien, `rounded-bl-[4px]` utk dokter, `data-msg="pasien"`/`"dokter"`) |
| **Listener dinamis `{sesi.token}`** | Inspeksi `wire:effects` di HTML hasil render | ✅ **Placeholder ter-resolve ke token asli**: `"echo:konsultasi.b8b061f5-...,PesanDikirim"` |
| Halaman chat — status `SELESAI` | Ubah status via Tinker, reload | ✅ Transkrip read-only + pesan penutup "Sesi konsultasi telah selesai" |
| Halaman chat — status `KEDALUWARSA` | Ubah status via Tinker, reload | ✅ Pesan "Sesi ini telah kedaluwarsa karena tidak direspons tepat waktu" |
| Guard keamanan: sesi dari RS lain | `curl` ke `/{rumahsakit-lain}/konsultasi/{token}` | ✅ HTTP 404 (`abort_if($sesi->rumah_sakit_id !== $this->rs->id, 404)`) |
| Guard keamanan: token tidak dikenal | `curl` dengan UUID acak | ✅ HTTP 404 (route model binding otomatis 404 jika tidak ditemukan) |

Semua data uji (sesi, pesan, perubahan status dokter) **sudah dibersihkan/dikembalikan** setelah pengujian selesai — basis data kembali ke kondisi semula.

---

## Catatan untuk Fase 3 (sisi dokter/admin)

Sesi yang dibuat pasien akan **tetap berstatus `MENUNGGU`** sampai ada aksi "Terima" dari dokter/admin — bagian ini belum dibangun (lihat checklist Fase 3 di [issues/tanya-dokter-plan.md](../issues/tanya-dokter-plan.md)). Begitu Fase 3 selesai dan memanggil `broadcast(new SesiStatusBerubah($sesi))`, halaman chat yang sudah dibangun di sini akan **otomatis berpindah** ke tampilan chat aktif — tanpa perlu perubahan apa pun di sisi pasien. Inilah keuntungan mendesain alur status & broadcasting lebih dulu: potongan-potongan ini saling terhubung begitu semuanya terpasang.
