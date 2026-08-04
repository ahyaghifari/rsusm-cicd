# Private Channel Pertama: Dashboard Konsultasi Dokter (Fase 3)

**Tanggal:** 2026-06-08
**Status:** ✅ Selesai & terverifikasi end-to-end (penggunaan **private channel** pertama di project ini)

> Lihat [02-events-channel-livewire.md](02-events-channel-livewire.md) untuk dasar event broadcast & channel publik — dokumen ini melanjutkan dengan konsep yang **belum pernah dipakai sebelumnya**: private channel + otorisasinya.

---

## Apa yang dibangun di sesi ini?

Sisi **dokter**: panel Filament terpisah (`/dokter`) berisi satu halaman *dashboard* (`KonsultasiDashboard`) tempat dokter melihat antrean sesi konsultasi yang masuk, menerima sesi, membalas chat, mengakhiri sesi, dan mengatur status ketersediaan — semuanya **hidup secara real-time** tanpa perlu refresh halaman, persis seperti sisi pasien di Fase 2.

Bedanya dengan Fase 2: pasien hanya boleh "mendengar" satu sesi (miliknya sendiri, lewat token rahasia di URL). Tapi seorang **dokter** perlu "mendengar" notifikasi *setiap kali ada sesi baru masuk untuknya* — dan ini **tidak bisa** dilakukan lewat channel publik, karena siapapun yang menebak nama channel-nya bisa ikut menguping. Di sinilah **private channel** dibutuhkan.

---

## 1. Kenapa Channel Publik Tidak Cukup Lagi?

Di Fase 2, channel `konsultasi.{token-sesi}` aman secara publik karena **tokennya sendiri adalah rahasianya** — token berupa UUID acak yang hanya diketahui pasien yang bersangkutan (dikirim lewat URL unik). Menebak UUID orang lain praktis mustahil.

Sekarang bandingkan dengan kebutuhan dashboard dokter: kita ingin sebuah channel bernama semacam `konsultasi.dokter.35` (untuk dokter dengan id 35) yang akan menyiarkan "ada sesi baru untuk dokter ini". Tapi:

- **Angka `35` mudah ditebak** — tinggal coba 1, 2, 3, ... dst.
- Kalau channel ini publik, **siapapun** yang tahu/menebak ID dokter bisa subscribe dan mengintip kapan ada pasien baru meminta konsultasi ke dokter tersebut, beserta detail apa pun yang kita siarkan di sana.

Jadi kita butuh channel yang **mewajibkan otorisasi** sebelum seseorang boleh subscribe — itulah **private channel**.

---

## 2. Private Channel — "ruangan dengan penjaga pintu"

Kalau channel publik adalah ruangan terbuka untuk umum, **private channel** adalah ruangan dengan penjaga pintu yang memeriksa identitas setiap orang yang ingin masuk.

Secara teknis, bedanya cuma dua:

1. **Penamaan**: harus diawali `private-` (tapi di Laravel, kita cukup memakai kelas `PrivateChannel` — Laravel menambahkan prefiksnya secara otomatis).
2. **Wajib didaftarkan otorisasinya** di `routes/channels.php` — kalau tidak, Laravel/Reverb akan menolak setiap permintaan subscribe ke channel itu (`403 Forbidden`).

### Mendaftarkan otorisasi di `routes/channels.php`

Ini **pertama kalinya** file ini diisi dengan sesuatu selain bawaan Laravel di project kita:

```php
Broadcast::channel('konsultasi.dokter.{dokterId}', function ($user, $dokterId) {
    return $user->bisaMenanganiDokter((int) $dokterId);
});
```

Cara bacanya: "Setiap kali ada **user yang sedang login** mencoba subscribe ke channel `konsultasi.dokter.<angka>`, jalankan closure ini. Kalau closure mengembalikan `true`, izinkan; kalau `false` (atau melempar exception), tolak."

Beberapa hal penting:

- **`$user` otomatis terisi** dari sesi login Laravel — inilah kenapa private channel *mewajibkan* pengguna untuk login (tidak seperti channel publik yang bisa diakses tamu/guest).
- **`{dokterId}`** adalah *route model binding*-style placeholder, sama persis seperti di routing HTTP biasa — nilainya diambil dari nama channel yang diminta browser dan dioper sebagai parameter ke closure.
- Closure ini **dijalankan di server**, jadi aman untuk memuat logika otorisasi sekompleks apapun — tidak bisa dimanipulasi dari sisi browser.

### Helper `User::bisaMenanganiDokter()`

Daripada menulis logika otorisasi langsung di closure (yang akan sulit dipakai ulang), kita taruh aturannya sebagai method di model `User`:

```php
public function bisaMenanganiDokter(int $dokterId): bool
{
    $dokter = Dokter::find($dokterId);

    if (! $dokter) {
        return false;
    }

    // Dokter menangani sesi miliknya sendiri
    if ($this->hasRole('dokter') && $this->id === $dokter->user_id) {
        return true;
    }

    // Admin/Humas RS yang sama bisa menangani atas nama dokter mana pun di RS-nya
    if ($this->hasAnyRole(['super_admin', 'admin', 'humas'])
        && ($this->hasRole('super_admin') || $this->rumah_sakit_id === $dokter->rumah_sakit_id)) {
        return true;
    }

    return false;
}
```

Keuntungan menaruhnya di model: aturan yang sama bisa dipakai ulang di **dua tempat sekaligus** — otorisasi channel (`routes/channels.php`) dan nanti query untuk menyaring "sesi mana saja yang boleh dilihat user ini" di dashboard. Satu sumber kebenaran, tidak ada risiko kedua tempat itu "berbeda pendapat".

> **Catatan untuk fase mendatang:** saat ini akun dengan role `dokter` hanya bisa menangani `Dokter` miliknya sendiri (lewat `user_id`). Cabang logika untuk admin/humas sudah disiapkan di helper ini sesuai rencana awal, tapi panel `/dokter` saat ini **hanya** mengizinkan role `dokter` masuk (lihat bagian 4). Kalau suatu saat admin/humas perlu menangani sesi "atas nama" dokter, helper ini sudah siap dipakai — tinggal buka aksesnya.

---

## 3. Menyiarkan ke Private Channel — `PrivateChannel` di `broadcastOn()`

Event `SesiStatusBerubah` (dibuat di Fase 2 untuk memberitahu pasien kalau status sesinya berubah) sekarang **disiarkan ke dua channel sekaligus**:

```php
public function broadcastOn(): array
{
    return [
        new Channel('konsultasi.' . $this->sesi->token),               // ① untuk halaman pasien
        new PrivateChannel('konsultasi.dokter.' . $this->sesi->dokter_id), // ② untuk dashboard dokter
    ];
}
```

Satu event, dua "alamat surat" — Reverb akan mengirim salinan payload yang sama persis ke siapapun yang sedang subscribe ke salah satu (atau kedua) channel tersebut. Ini berguna karena kejadian "status sesi berubah" memang relevan untuk **dua pihak yang berbeda**:

- **Pasien** yang sedang membuka halaman chat-nya → channel ①, supaya UI-nya ikut berubah (mis. dari "menunggu" jadi "sedang berlangsung").
- **Dokter** yang membuka dashboard-nya → channel ②, supaya antreannya ter-update otomatis (sesi baru muncul, atau sesi yang sudah selesai hilang dari daftar).

Event ini sekarang dipancarkan di **dua momen**:
1. Saat pasien membuat sesi baru (`TanyaDokter::mulaiSesi()`) — status awal `MENUNGGU` → ini yang memicu **notifikasi sesi baru** muncul di dashboard dokter secara real-time.
2. Saat dokter menerima/mengakhiri sesi (`KonsultasiDashboard::terima()` / `akhiri()`) — supaya halaman pasien & dashboard dokter sama-sama tahu statusnya berubah.

---

## 4. Panel Filament Terpisah untuk Dokter

Sebelum membahas dashboard-nya, ada satu keputusan arsitektur yang perlu dicatat: dokter mendapat **panel Filament-nya sendiri** (`/dokter`), terpisah total dari panel admin (`/manage`).

Filament 3 mendukung banyak panel dalam satu aplikasi — masing-masing punya `id()`, `path()`, branding, dan daftar resource/page sendiri, tapi **tetap memakai model `User` dan sesi login yang sama**. Jadi satu akun bisa (secara teori) punya akses ke beberapa panel sekaligus — yang membedakan **panel mana yang boleh diakses siapa** adalah method `canAccessPanel()` di model `User`:

```php
public function canAccessPanel(Panel $panel): bool
{
    return match ($panel->getId()) {
        'dokter' => $this->hasRole('dokter'),
        default  => $this->hasAnyRole(['super_admin', 'admin', 'humas', 'informasi']),
    };
}
```

Akun dengan role `dokter` **hanya** bisa masuk ke panel `/dokter` (dan di dalamnya pun cuma ada satu halaman: dashboard konsultasi). Akun admin/humas/dst tetap seperti biasa di `/manage`. Dua "rumah" yang terpisah, satu "KTP" (akun) yang sama.

---

## 5. `KonsultasiDashboard` — Menggabungkan Semua Konsep

Halaman dashboard (`app/Filament/Dokter/Pages/KonsultasiDashboard.php`) memakai **kombinasi** dari channel publik (Fase 2) dan private channel (baru):

```php
// ① Notifikasi real-time untuk SEMUA perubahan terkait dokter ini — private channel baru
#[On('echo-private:konsultasi.dokter.{dokter.id},SesiStatusBerubah')]
public function antreanBerubah(): void { /* ... */ }

// ② Pesan masuk untuk SESI YANG SEDANG DIBUKA — channel publik berbasis token, sama seperti sisi pasien
#[On('echo:konsultasi.{sesiAktifToken},PesanDikirim')]
public function pesanMasuk(): void { /* ... */ }

#[On('echo:konsultasi.{sesiAktifToken},SesiStatusBerubah')]
public function sesiAktifBerubah(): void { /* ... */ }
```

Perhatikan **satu kata kunci baru**: `echo-private:` (bukan `echo:`). Inilah cara Livewire memberitahu Laravel Echo "ini bukan channel publik biasa — lakukan proses otorisasi dulu sebelum subscribe". Tanpa awalan ini, Echo akan mencoba subscribe sebagai channel publik dan **gagal diam-diam** (tidak ada error, tapi juga tidak pernah menerima siaran apapun) — Reverb menolak permintaan subscribe-nya di belakang layar.

Pola "dua tingkat mendengarkan" ini masuk akal kalau dipikir-pikir: dokter perlu tahu **kapan ada sesi baru/berubah** (di seluruh antreannya — private channel khusus dia), tapi **isi obrolan** hanya relevan untuk sesi yang sedang ia buka (channel publik per-token, sama seperti pasien).

### "Jebakan" yang ditemukan: placeholder dinamis tidak boleh `null`

Saat menulis `#[On('echo:konsultasi.{sesiAktifToken},...')]`, awalnya `$sesiAktifToken` didefinisikan sebagai `?string $sesiAktifToken = null` (karena di awal, sebelum dokter memilih sesi mana pun, memang belum ada token). Ternyata Livewire melempar error:

```
Unable to evaluate dynamic event name placeholder: {sesiAktifToken}
```

Penyebabnya: Livewire memakai `data_get($component, 'sesiAktifToken', $fallbackYangMelemparException)` untuk mengisi placeholder — dan `data_get()` menganggap nilai `null` sebagai "tidak ditemukan", lalu menjalankan fallback-nya (yang sengaja dibuat melempar exception oleh Livewire, supaya developer sadar ada placeholder yang salah ketik).

**Solusinya**: jadikan propertinya selalu bertipe `string` (bukan `?string`), dengan default `''` (string kosong) alih-alih `null`. Channel `konsultasi.` (dengan token kosong) memang valid secara nama, hanya saja **tidak akan pernah ada yang menyiarkan ke sana** — jadi efeknya sama seperti "belum mendengarkan apapun", tapi tanpa membuat Livewire bingung.

```php
public string $sesiAktifToken = '';   // bukan ?string $sesiAktifToken = null
```

Pelajaran umum: kalau memakai placeholder dinamis `{properti}` di `#[On(...)]`, pastikan propertinya **tidak pernah** bernilai `null` selama komponen hidup — beri nilai default yang valid secara tipe meskipun "kosong secara makna".

---

## 6. Alur Lengkap (Ringkasan)

1. **Pasien** mengisi form di "Tanya Dokter" → `SesiKonsultasi` baru dibuat dengan status `MENUNGGU` → `broadcast(new SesiStatusBerubah($sesi))` dipancarkan ke channel token (pasien) **dan** `konsultasi.dokter.{id}` (dokter).
2. **Dashboard dokter** yang sedang terbuka langsung menerima siaran lewat private channel → antrean ter-render ulang, sesi baru langsung muncul tanpa refresh.
3. **Dokter** klik "Terima Sesi" → status jadi `BERLANGSUNG`, `mulai_at`/`berakhir_at` di-set → `SesiStatusBerubah` disiarkan lagi (kedua channel) → halaman pasien otomatis pindah dari "ruang tunggu" ke "chat aktif".
4. **Dokter** mengetik balasan → `KonsultasiPesan` baru tersimpan (`pengirim = DOKTER`) → `broadcast(new PesanDikirim(...))` dipancarkan ke channel token → muncul di chat pasien **dan** dashboard dokter (lewat listener `pesanMasuk` pada channel publik yang sama, karena dokter juga sedang "mendengarkan" sesi yang ia buka).
5. **Dokter** klik "Akhiri Sesi" → status jadi `SELESAI` → `SesiStatusBerubah` disiarkan terakhir kali → kedua sisi tahu percakapan sudah berakhir, beralih ke mode transkrip baca-saja.

---

## 7. Hasil Uji End-to-End

Diuji memakai `Livewire::test()` dengan akun dokter sungguhan (`role: dokter`, ditautkan ke salah satu data `Dokter` lewat `user_id` — dibuat & dihapus lagi sebagai data uji setelah selesai):

| Langkah | Hasil |
|---|---|
| `canAccessPanel('dokter')` untuk akun ber-role `dokter` | ✅ `true` |
| `canAccessPanel('admin')` untuk akun yang sama | ✅ `false` (tidak bisa nyasar ke panel admin) |
| `bisaMenanganiDokter($idDokterSendiri)` | ✅ `true` |
| Buat sesi baru (`MENUNGGU`) → muncul di antrean dashboard | ✅ tampil dengan nama pasien benar |
| `terima()` → status `BERLANGSUNG`, `mulai_at`/`berakhir_at`/`dibalas_oleh` ter-set, broadcast terkirim | ✅ |
| `kirimBalasan()` → `KonsultasiPesan` tersimpan dgn `pengirim=DOKTER`, bubble chat tampil di HTML | ✅ |
| `akhiri()` → status `SELESAI`, sesi aktif ter-reset (`sesiAktifId = null`) | ✅ |
| `toggleTersedia()` dua arah (`true → false → true`) | ✅ |

Tidak ada error broadcasting (Reverb menerima semua siaran tanpa keluhan — `BROADCAST_CONNECTION=reverb` di `.env`).

---

## Istilah Baru di Dokumen Ini

| Istilah | Penjelasan singkat |
|---|---|
| **Private Channel** | Channel yang mewajibkan otorisasi server sebelum boleh di-subscribe — dipakai saat nama channel bisa ditebak/berisi data sensitif |
| **`Broadcast::channel()`** | Tempat mendaftarkan aturan otorisasi private/presence channel, di `routes/channels.php` |
| **`PrivateChannel`** | Kelas PHP untuk menyiarkan event ke private channel (lawannya `Channel` untuk publik) |
| **`echo-private:`** | Awalan pada `#[On(...)]` Livewire yang memberitahu Echo untuk subscribe sebagai private channel (vs `echo:` untuk publik) |
| **Multi-panel Filament** | Satu aplikasi Filament dengan beberapa "area" terpisah (mis. `/manage` untuk admin, `/dokter` untuk dokter), masing-masing dengan akses & tampilan sendiri tapi memakai akun & sesi login yang sama |
