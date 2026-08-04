# Plan Implementasi: Tanya Dokter — Konsultasi Chat Real-Time Bersesi

> **Catatan:** Dokumen ini menggantikan draf awal (versi async/tiket tanpa sesi). Setelah didiskusikan, arah yang dipilih adalah **chat real-time berbasis sesi bertimer per dokter** (mirip Halodoc), bukan model tiket sederhana. Rencana ini jauh lebih besar — dipecah menjadi beberapa fase agar bisa dibangun & diuji secara bertahap.

## Keputusan Desain (hasil diskusi)

| Aspek | Keputusan |
|---|---|
| **Mode interaksi** | Real-time chat, berbasis **sesi** dengan durasi terbatas (mis. 30 menit), durasi **bisa berbeda per dokter** |
| **Siapa yang membalas** | Hybrid — **kadang dokter sendiri** (login & balas langsung), **kadang admin/CS atas nama dokter**. Keduanya mengakses dashboard konsultasi yang sama, dengan otorisasi berbeda |
| **Identitas pasien** | **Tanpa akun** — pasien isi nama + kontak, sistem membuatkan **token sesi unik** (mirip link Zoom). Token = kunci akses ke sesi tsb |
| **Toggle per RS** | Tetap mengikuti pola `executive_clinic` — fitur dapat diaktif/nonaktifkan per cabang RS via Filament |
| **Realtime engine** | **Laravel Reverb** (WebSocket first-party Laravel 12, gratis & self-hosted) — bukan Pusher (berbayar) atau polling (kurang responsif untuk chat live) |

---

## Ikhtisar Arsitektur

```
RumahSakit (tanya_dokter_aktif)
└── Dokter (tersedia_konsultasi, durasi_sesi_menit, user_id?)
    └── SesiKonsultasi (token, status, mulai_at, berakhir_at, dibalas_oleh)
        └── KonsultasiPesan (pengirim: PASIEN | DOKTER, isi)
```

**Alur singkat:**
1. Pasien buka `/{rumahsakit}/tanya-dokter` → lihat daftar dokter yang **sedang tersedia** (`tersedia_konsultasi = true`)
2. Pilih dokter → isi nama & kontak → sistem buat `SesiKonsultasi` (status `MENUNGGU`) + token unik → redirect ke `/{rumahsakit}/konsultasi/{token}`
3. Dokter (atau admin/CS atas nama dokter) melihat permintaan masuk di dashboard Filament → klik **Terima** → status jadi `BERLANGSUNG`, timer mulai berjalan (durasi sesuai pengaturan dokter)
4. Chat berjalan real-time via Reverb hingga timer habis atau salah satu pihak mengakhiri sesi
5. Job terjadwal menutup otomatis sesi yang kedaluwarsa (timer habis tanpa diakhiri manual, atau permintaan tak direspon)

---

## Pemecahan Fase

| Fase | Cakupan | Output yang bisa diuji |
|---|---|---|
| **Fase 0** | Infrastruktur broadcasting (Reverb) | Event broadcast & diterima di browser (uji coba sederhana) |
| **Fase 1** | Skema data, model, enum, role `dokter`, toggle | Data dapat dibuat & dilihat lewat Tinker/Filament |
| **Fase 2** | Sisi pasien: landing pilih dokter, buat sesi, halaman chat | Pasien bisa membuat sesi & mengirim pesan |
| **Fase 3** | Sisi dokter/admin: dashboard konsultasi, terima/balas/akhiri | Dokter/admin bisa merespons real-time |
| **Fase 4** | Otomasi: timer enforcement, auto-expire, antrian | Sesi tertutup otomatis, antrian FIFO berjalan |

---

# FASE 0 — Infrastruktur Broadcasting (Laravel Reverb)

- [ ] **0.1** Install Reverb: `composer require laravel/reverb` lalu `php artisan reverb:install`
- [ ] **0.2** Install Echo client: `npm install --save-dev laravel-echo pusher-js`
- [ ] **0.3** Set `BROADCAST_CONNECTION=reverb` di `.env` (saat ini `log` — belum ada broadcasting aktif)
- [ ] **0.4** Konfigurasi `resources/js/echo.js` & import di `app.js`
- [ ] **0.5** Tambahkan kredensial Reverb baru ke `.env.example` (`REVERB_APP_ID`, `REVERB_APP_KEY`, dll) dengan komentar penjelasan
- [ ] **0.6** Uji broadcast sederhana (mis. event dummy) untuk memastikan WebSocket jalan di lokal sebelum lanjut ke fase berikutnya

**Catatan operasional:** Reverb butuh proses server terpisah yang harus selalu berjalan (`php artisan reverb:start`) — perlu disiapkan di production (mis. via Supervisor/systemd), beda dengan `php artisan serve` biasa. Ini perlu dicatat di README sebagai prasyarat deployment baru.

---

# FASE 1 — Skema Data, Model, Enum, Role

## Checklist

- [x] **1.1** Migrasi: `tanya_dokter_aktif` ke `rumah_sakit`
- [x] **1.2** Migrasi: kolom konsultasi ke `dokter` (`tersedia_konsultasi`, `durasi_sesi_menit`, `user_id`)
- [x] **1.3** Migrasi: tabel `sesi_konsultasi`
- [x] **1.4** Migrasi: tabel `konsultasi_pesan`
- [x] **1.5** Enum `StatusSesiKonsultasi`
- [x] **1.6** Enum `PengirimPesan`
- [x] **1.7** Model `SesiKonsultasi` + `KonsultasiPesan`
- [x] **1.8** Update model `RumahSakit` & `Dokter` (`$fillable`/`$guarded`, `$casts`, relasi)
- [x] **1.9** Tambah role `dokter` di `RolesAndPermissionsSeeder`
- [x] **1.10** Update `RumahSakitResource` — toggle `tanya_dokter_aktif`
- [x] **1.11** Update `DokterResource` — field `tersedia_konsultasi`, `durasi_sesi_menit`, `user_id`

> ✅ **Selesai & terverifikasi** (2026-06-08) — migrasi dijalankan, role `dokter` ter-seed, dan diuji lewat Tinker: berhasil membuat `SesiKonsultasi` + `KonsultasiPesan`, cast enum bekerja (`status`, `pengirim`), route-key `token` berfungsi, relasi `pesan()`/`dokter()`/`rumahSakit()` terhubung. Kedua Filament Resource (toggle RS & section Dokter) ter-load tanpa error.

## 1.1 Migrasi — Toggle di `rumah_sakit`

```php
Schema::table('rumah_sakit', function (Blueprint $table) {
    $table->boolean('tanya_dokter_aktif')->default(false)->after('executive_clinic');
});
```

## 1.2 Migrasi — Kolom Konsultasi di `dokter`

```php
Schema::table('dokter', function (Blueprint $table) {
    $table->boolean('dapat_konsultasi')->default(false)->after('aktif');
    $table->boolean('tersedia_konsultasi')->default(false)->after('dapat_konsultasi');
    $table->unsignedInteger('durasi_sesi_menit')->default(30)->after('tersedia_konsultasi');
    $table->foreignId('user_id')->nullable()->after('durasi_sesi_menit')
        ->constrained('users')->nullOnDelete();
});
```

**Kenapa dua status terpisah (`dapat_konsultasi` vs `tersedia_konsultasi`)?** Awalnya direncanakan satu kolom saja, tapi ternyata ada dua pertanyaan berbeda yang perlu dijawab terpisah:

| Kolom | Pertanyaan yang dijawab | Siapa yang atur & seberapa sering |
|---|---|---|
| `dapat_konsultasi` | "Apakah dokter ini **ikut serta** dalam program Tanya Dokter?" — semacam status keanggotaan/eligibilitas | Admin, lewat `DokterResource` — jarang berubah |
| `tersedia_konsultasi` | "Apakah dokter ini **sedang online & siap** menerima sesi chat **saat ini**?" — status real-time | Dokter atau admin/CS, bisa berubah tiap hari (mis. quick-toggle di dashboard Fase 3) |

Memisahkan keduanya mencegah kerancuan: seorang dokter bisa "terdaftar ikut telemedicine" (`dapat_konsultasi = true`) tapi "sedang offline" (`tersedia_konsultasi = false`) — keduanya valid & perlu ditampilkan beda ke pasien (lihat badge "Tersedia"/"Tidak Tersedia" di halaman landing Fase 2). Form di `DokterResource` menonaktifkan toggle `tersedia_konsultasi` selama `dapat_konsultasi` masih nonaktif, supaya urutan pengisian masuk akal.

**Kenapa `durasi_sesi_menit` per dokter (bukan konstanta global)?** Sesuai keputusan — durasi sesi bisa berbeda antar dokter (mis. dokter spesialis 20 menit, dokter umum 30 menit). Disimpan sebagai kolom agar admin bisa atur lewat Filament tanpa ubah kode.

**Kenapa `user_id` nullable?** Tidak semua dokter perlu login sendiri — sesuai keputusan hybrid (kadang dokter sendiri, kadang admin/CS atas nama dokter). Kolom ini menghubungkan profil `Dokter` ke akun `User` **hanya jika** dokter tsb memang akan login & membalas sendiri.

## 1.3 Migrasi — Tabel `sesi_konsultasi`

```php
Schema::create('sesi_konsultasi', function (Blueprint $table) {
    $table->id();
    $table->foreignId('rumah_sakit_id')->constrained('rumah_sakit')->cascadeOnDelete();
    $table->foreignId('dokter_id')->constrained('dokter')->cascadeOnDelete();
    $table->uuid('token')->unique();
    $table->string('nama_pasien', 100);
    $table->string('kontak_pasien', 100);
    $table->string('status', 20)->default('MENUNGGU');
    $table->unsignedInteger('durasi_menit'); // snapshot dari Dokter->durasi_sesi_menit saat sesi dibuat
    $table->foreignId('dibalas_oleh')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('mulai_at')->nullable();
    $table->timestamp('berakhir_at')->nullable();
    $table->timestamps();

    $table->index(['dokter_id', 'status']);
});
```

**Kenapa snapshot `durasi_menit` di sesi (bukan baca langsung dari `Dokter`)?** Supaya jika admin mengubah durasi default dokter di kemudian hari, sesi yang sedang/sudah berjalan tidak ikut berubah — pola ini konsisten dengan snapshot `*_asli` di `JadwalHarianPerubahan`.

**Kenapa `token` (UUID) sebagai kunci akses, bukan `id`?** Karena pasien tidak punya akun — token unik & sulit ditebak menjadi "kunci" satu-satunya untuk mengakses sesinya sendiri (mirip link rapat Zoom). Route `konsultasi/{token}` memakai ini sebagai route key.

## 1.4 Migrasi — Tabel `konsultasi_pesan`

```php
Schema::create('konsultasi_pesan', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sesi_id')->constrained('sesi_konsultasi')->cascadeOnDelete();
    $table->string('pengirim', 20); // PASIEN | DOKTER
    $table->text('isi');
    $table->timestamp('dibaca_at')->nullable();
    $table->timestamps();

    $table->index('sesi_id');
});
```

**Kenapa `pengirim` cukup 2 nilai (PASIEN/DOKTER), bukan menyimpan `user_id` pengirim?** Dari sudut pandang pasien, tidak relevan apakah yang membalas dokter sungguhan atau admin/CS atas nama dokter — keduanya tampil sebagai "Dokter" di chat. Identitas pembalas sebenarnya tetap tercatat di level sesi lewat `dibalas_oleh` (untuk audit/akuntabilitas internal), bukan per pesan.

## 1.5 Enum `StatusSesiKonsultasi`

**File:** `app/Enums/StatusSesiKonsultasi.php` (pola sama dengan `StatusLayanan`)

```php
enum StatusSesiKonsultasi: string implements HasLabel, HasColor
{
    case MENUNGGU    = 'MENUNGGU';
    case BERLANGSUNG = 'BERLANGSUNG';
    case SELESAI     = 'SELESAI';
    case KEDALUWARSA = 'KEDALUWARSA';

    public function getLabel(): ?string
    {
        return match($this) {
            self::MENUNGGU    => 'Menunggu Dokter',
            self::BERLANGSUNG => 'Sedang Berlangsung',
            self::SELESAI     => 'Selesai',
            self::KEDALUWARSA => 'Kedaluwarsa',
        };
    }

    public function getColor(): string|array|null
    {
        return match($this) {
            self::MENUNGGU    => 'warning',
            self::BERLANGSUNG => 'success',
            self::SELESAI     => 'gray',
            self::KEDALUWARSA => 'danger',
        };
    }
}
```

## 1.6 Enum `PengirimPesan`

```php
enum PengirimPesan: string implements HasLabel
{
    case PASIEN = 'PASIEN';
    case DOKTER = 'DOKTER';

    public function getLabel(): ?string
    {
        return match($this) {
            self::PASIEN => 'Pasien',
            self::DOKTER => 'Dokter',
        };
    }
}
```

## 1.7 Model `SesiKonsultasi`

```php
class SesiKonsultasi extends Model
{
    protected $table = 'sesi_konsultasi';

    protected $fillable = [
        'rumah_sakit_id', 'dokter_id', 'token', 'nama_pasien', 'kontak_pasien',
        'status', 'durasi_menit', 'dibalas_oleh', 'mulai_at', 'berakhir_at',
    ];

    protected $casts = [
        'status'      => StatusSesiKonsultasi::class,
        'mulai_at'    => 'datetime',
        'berakhir_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'token';
    }

    public function rumahSakit() { return $this->belongsTo(RumahSakit::class); }
    public function dokter()     { return $this->belongsTo(Dokter::class); }
    public function pembalas()   { return $this->belongsTo(User::class, 'dibalas_oleh'); }
    public function pesan()      { return $this->hasMany(KonsultasiPesan::class, 'sesi_id'); }

    public function sisaDetik(): int
    {
        if (! $this->berakhir_at) return 0;
        return max(0, now()->diffInSeconds($this->berakhir_at, false));
    }
}
```

## 1.8 Update Model `RumahSakit` & `Dokter`

- `RumahSakit`: tambah `tanya_dokter_aktif` ke `$fillable` & `$casts` (boolean)
- `Dokter`: tambah `tersedia_konsultasi`, `durasi_sesi_menit`, `user_id` (model pakai `$guarded = ['id']` jadi otomatis mass-assignable). Tambah cast `tersedia_konsultasi` → boolean, dan relasi:

```php
public function user() { return $this->belongsTo(User::class); }
public function sesiKonsultasi() { return $this->hasMany(SesiKonsultasi::class, 'dokter_id'); }
```

## 1.9 Role `dokter`

**File:** `database/seeders/RolesAndPermissionsSeeder.php`

```php
$dokter = Role::firstOrCreate(['name' => 'dokter', 'guard_name' => 'web']);
```

Beri akses terbatas — hanya ke halaman/dashboard konsultasi, **tidak** ke resource admin lain. Ini perlu didefinisikan saat membangun Fase 3 (lihat catatan otorisasi di sana).

## 1.10 Update `RumahSakitResource`

Sama seperti rencana sebelumnya — tambah Toggle `tanya_dokter_aktif` (sejajar `executive_clinic`):

```php
Forms\Components\Toggle::make('tanya_dokter_aktif')
    ->label('Aktifkan Fitur Tanya Dokter')
    ->helperText('Jika nonaktif, menu & halaman konsultasi tidak muncul di portal publik RS ini.')
    ->default(false),
```

## 1.11 Update `DokterResource`

Tambah section "Konsultasi Chat":

```php
Forms\Components\Section::make('Konsultasi Chat')
    ->schema([
        Forms\Components\Toggle::make('tersedia_konsultasi')
            ->label('Tersedia untuk Konsultasi Chat')
            ->helperText('Aktifkan saat dokter siap menerima sesi chat dari pasien.'),
        Forms\Components\TextInput::make('durasi_sesi_menit')
            ->label('Durasi Sesi (menit)')
            ->numeric()
            ->default(30)
            ->required(),
        Forms\Components\Select::make('user_id')
            ->label('Akun Login Dokter (opsional)')
            ->relationship('user', 'name')
            ->searchable()
            ->helperText('Hubungkan ke akun User jika dokter ini akan login & membalas chat sendiri. Kosongkan jika hanya admin/CS yang akan membalas atas nama dokter.'),
    ]),
```

---

# FASE 2 — Sisi Pasien: Pilih Dokter & Chat

## Checklist

- [x] **2.1a** Route `tanya-dokter` (landing daftar dokter) — *`konsultasi/{sesi:token}` menyusul bersama 2.3*
- [x] **2.1b** Route `konsultasi/{sesi:token}` — route model binding via `getRouteKeyName() = 'token'`
- [x] **2.2a** Livewire `Pages\TanyaDokter` — **versi landing-only**: daftar dokter (`dapat_konsultasi = true`) dengan badge status (`tersedia_konsultasi`). Form "mulai sesi" & redirect ke chat **sengaja ditunda** — tombol tampil "Segera Hadir" (disabled) agar tidak ada alur setengah jadi sebelum halaman chat (2.3) siap
- [x] **2.2b** Lengkapi `Pages\TanyaDokter` — form pilih dokter (modal) + `pilihDokter()`/`batalkanPilihan()`/`mulaiSesi()` aktif: validasi nama/kontak, re-cek ketersediaan dokter (cegah race condition), buat `SesiKonsultasi`, redirect ke halaman chat
- [x] **2.3** Livewire `Pages\KonsultasiChat` — ruang tunggu (`MENUNGGU`) + chat real-time dengan bubble pasien/dokter (`BERLANGSUNG`) + countdown timer berbasis `berakhir_at` + transkrip read-only (`SELESAI`/`KEDALUWARSA`)
- [x] **2.4a** View landing `tanya-dokter.blade.php` — *view chat menyusul bersama 2.3*
- [x] **2.4b** View `konsultasi-chat.blade.php` — keempat status sesi dirender sesuai desain (ruang tunggu, chat aktif + timer Alpine, transkrip selesai/kedaluwarsa)
- [x] **2.5** Broadcasting event `PesanDikirim` & `SesiStatusBerubah` (keduanya `ShouldBroadcastNow`, `broadcastAs`, `broadcastWith` payload ringkas)
- [x] **2.6** Channel publik berbasis token — *tidak perlu didaftarkan di `routes/channels.php`* karena `new Channel(...)` (publik), bukan `PrivateChannel`; keamanan bertumpu pada UUID token
- [x] **2.7** Nav — link "Tanya Dokter" kondisional berdasarkan `tanya_dokter_aktif` (mobile grid + desktop bar di `nav.blade.php`)
- [x] **2.8** Rate limiting pembuatan sesi — burst (2 / 30 menit) & harian (5 / 24 jam), key gabungan IP + session ID (pola sama dengan `Chatbot\Panel`)

> ✅ **Alur lengkap sudah live** (2026-06-08): pasien memilih dokter "Tersedia" → isi nama & kontak di modal → `SesiKonsultasi` dibuat → redirect ke `/{rumahsakit}/konsultasi/{token}` → ruang tunggu (`MENUNGGU`) → chat real-time via Reverb (`BERLANGSUNG`, dengan timer countdown) → transkrip read-only setelah `SELESAI`/`KEDALUWARSA`.
>
> **Diuji end-to-end** lewat `php artisan serve` + `reverb:start` + `npm run dev`: landing page (HTTP 200, badge & tombol "Mulai Konsultasi" tampil benar), keempat status halaman chat dirender dengan benar via simulasi Tinker (transisi status + `broadcast(...)->toOthers()`), **dynamic placeholder `#[On('echo:konsultasi.{sesi.token},...')]` terbukti ter-resolve ke token asli** (terlihat di `wire:effects` → `"echo:konsultasi.b8b061f5-...,PesanDikirim"`), serta guard keamanan (akses sesi dari RS lain → 404, token tidak dikenal → 404). Data uji (sesi, pesan, toggle dokter) sudah dibersihkan/dikembalikan setelah pengujian.
>
> **Catatan untuk Fase 3**: sesi akan tetap di status `MENUNGGU` sampai ada aksi "Terima" dari sisi dokter/admin (belum dibangun) — ini sesuai desain, halaman chat sudah siap menampilkan status `BERLANGSUNG` begitu Fase 3 mengubah status & broadcast `SesiStatusBerubah`.

## 2.1 Routes

```php
Route::get('tanya-dokter', App\Livewire\Pages\TanyaDokter::class)
    ->middleware('throttle:portal')
    ->name('rumahsakit.tanya_dokter');

Route::get('konsultasi/{sesi:token}', App\Livewire\Pages\KonsultasiChat::class)
    ->middleware('throttle:portal')
    ->name('rumahsakit.konsultasi');
```

`{sesi:token}` memakai route model binding dengan `getRouteKeyName() = 'token'` dari model `SesiKonsultasi` (lihat 1.7).

## 2.2 Livewire `TanyaDokter` (Landing)

```php
class TanyaDokter extends RsPortalComponent
{
    private const BURST_LIMIT   = 2;   // maks. sesi baru dalam jendela singkat
    private const BURST_MINUTES = 30;
    private const DAILY_LIMIT   = 5;
    private const DAILY_HOURS   = 24;

    public ?int $dokterDipilih = null;

    #[Validate('required|string|max:100')]
    public string $nama = '';

    #[Validate('required|string|max:100')]
    public string $kontak = '';

    public function mount(): void
    {
        abort_unless($this->rs->tanya_dokter_aktif, 404);
        $this->seo('Tanya Dokter', 'Konsultasi chat langsung dengan dokter kami.');
    }

    public function mulaiSesi(): void
    {
        $this->validate();

        $burstKey = 'konsultasi_burst:' . request()->ip();
        $dailyKey = 'konsultasi_daily:' . request()->ip();

        if (RateLimiter::tooManyAttempts($burstKey, self::BURST_LIMIT) ||
            RateLimiter::tooManyAttempts($dailyKey, self::DAILY_LIMIT)) {
            $this->addError('nama', 'Anda telah mencapai batas pembuatan sesi konsultasi. Coba lagi nanti.');
            return;
        }

        $dokter = Dokter::where('rumah_sakit_id', $this->rs->id)
            ->where('dapat_konsultasi', true)
            ->where('tersedia_konsultasi', true)
            ->findOrFail($this->dokterDipilih);

        RateLimiter::hit($burstKey, self::BURST_MINUTES * 60);
        RateLimiter::hit($dailyKey, self::DAILY_HOURS * 3600);

        $sesi = SesiKonsultasi::create([
            'rumah_sakit_id' => $this->rs->id,
            'dokter_id'      => $dokter->id,
            'token'          => Str::uuid(),
            'nama_pasien'    => $this->nama,
            'kontak_pasien'  => $this->kontak,
            'status'         => StatusSesiKonsultasi::MENUNGGU,
            'durasi_menit'   => $dokter->durasi_sesi_menit,
        ]);

        $this->redirect(route('rumahsakit.konsultasi', [
            'rumahsakit' => $this->rs->slug,
            'sesi'       => $sesi->token,
        ]));
    }

    public function render()
    {
        // Tampilkan semua dokter yang IKUT program telemedicine (dapat_konsultasi),
        // bukan hanya yang sedang online — supaya pasien tetap bisa melihat siapa
        // saja dokternya & memilih untuk menunggu/coba lagi nanti.
        // Status real-time (tersedia_konsultasi) ditampilkan sbg badge di view,
        // dan divalidasi ulang saat mulaiSesi() (lihat catatan keamanan di bawah).
        $dokter = Dokter::where('rumah_sakit_id', $this->rs->id)
            ->where('aktif', true)
            ->where('dapat_konsultasi', true)
            ->with('spesialis')
            ->orderByDesc('tersedia_konsultasi')
            ->orderBy('nama')
            ->get();

        return view('rumah_sakit.pages.tanya-dokter', compact('dokter'));
    }
}
```

**Catatan keamanan:** validasi ulang `tersedia_konsultasi = true` saat `mulaiSesi()` (bukan hanya saat render) — mencegah race condition jika dokter baru saja nonaktifkan diri.

## 2.3 Livewire `KonsultasiChat`

```php
class KonsultasiChat extends RsPortalComponent
{
    public SesiKonsultasi $sesi;
    public string $pesanBaru = '';
    public array $riwayat = [];

    public function mount(SesiKonsultasi $sesi): void
    {
        abort_if($sesi->rumah_sakit_id !== $this->rs->id, 404);

        $this->sesi = $sesi;
        $this->riwayat = $sesi->pesan()->orderBy('created_at')->get()->toArray();
        $this->seo('Konsultasi - ' . $sesi->dokter->nama);
    }

    #[On('echo:konsultasi.{sesi.token},PesanDikirim')]
    public function pesanMasuk(array $payload): void
    {
        $this->riwayat[] = $payload;
    }

    #[On('echo:konsultasi.{sesi.token},SesiStatusBerubah')]
    public function statusBerubah(array $payload): void
    {
        $this->sesi->refresh();
    }

    public function kirim(): void
    {
        $this->validate(['pesanBaru' => 'required|string|max:1000']);

        abort_unless($this->sesi->status === StatusSesiKonsultasi::BERLANGSUNG, 403);

        $pesan = $this->sesi->pesan()->create([
            'pengirim' => PengirimPesan::PASIEN,
            'isi'      => $this->pesanBaru,
        ]);

        broadcast(new PesanDikirim($this->sesi, $pesan))->toOthers();

        $this->reset('pesanBaru');
    }

    public function render()
    {
        return view('rumah_sakit.pages.konsultasi-chat');
    }
}
```

**Tampilan disesuaikan dengan status sesi:**
- `MENUNGGU` → "Menunggu dokter menerima sesi Anda…" (live update via event `SesiStatusBerubah`)
- `BERLANGSUNG` → antarmuka chat aktif + countdown timer (dihitung dari `sisaDetik()`)
- `SELESAI` / `KEDALUWARSA` → transkrip read-only + pesan penutup

## 2.4 — 2.6 Broadcasting & Channel

**Event `PesanDikirim`** (implements `ShouldBroadcastNow`):

```php
class PesanDikirim implements ShouldBroadcastNow
{
    public function __construct(public SesiKonsultasi $sesi, public KonsultasiPesan $pesan) {}

    public function broadcastOn(): array
    {
        return [new Channel('konsultasi.' . $this->sesi->token)];
    }
}
```

**Channel publik berbasis token** — *kenapa publik, bukan private?* Karena pasien tidak punya akun untuk diautentikasi terhadap channel private. Keamanan bertumpu pada **UUID token yang sulit ditebak** (sama seperti link Zoom/Meet) — siapa pun yang tahu token bisa join (sesuai sifat akses "tanpa akun" yang dipilih). Channel ini tidak perlu didaftarkan di `routes/channels.php` karena bersifat publik.

Untuk **sisi dokter/admin**, gunakan **private channel** dengan otorisasi (lihat Fase 3) — karena mereka sudah terautentikasi via Filament:

```php
// routes/channels.php
Broadcast::channel('konsultasi.dokter.{dokterId}', function (User $user, int $dokterId) {
    return $user->bisaMenanganiDokter($dokterId); // helper otorisasi, lihat Fase 3
});
```

## 2.7 Nav

Sama seperti rencana sebelumnya — link "Tanya Dokter" muncul hanya jika `$currentRumahSakit->tanya_dokter_aktif`.

## 2.8 Rate Limiting

Karena membuat sesi melibatkan dokter sungguhan (sumber daya manusia terbatas, bukan sekadar baris data), batasnya dibuat **lebih ketat** daripada chatbot AI:

| Lapis | Batas (disarankan awal) |
|---|---|
| Burst | 2 sesi baru / 30 menit |
| Harian | 5 sesi baru / 24 jam |

---

# FASE 3 — Sisi Dokter / Admin: Dashboard Konsultasi

## Checklist

- [x] **3.1** Custom Filament Page `KonsultasiDashboard`
- [x] **3.2** Logika otorisasi "siapa bisa menangani sesi dokter X"
- [x] **3.3** Action Terima / Balas / Akhiri Sesi
- [x] **3.4** Broadcasting notifikasi sesi baru ke dashboard
- [x] **3.5** Quick-toggle `tersedia_konsultasi` dari dashboard

**Catatan implementasi (selesai 2026-06-08):**

- **Panel terpisah**: Atas permintaan eksplisit user ("untuk dokter itu panel khusus dokter jadi berada di luar adminpanel yang ada (panel baru)"), dibuat panel Filament baru `dokter` (`app/Providers/Filament/DokterPanelProvider.php`, path `/dokter`) — sepenuhnya terpisah dari panel `admin`, hanya berisi satu halaman `KonsultasiDashboard`. Guard & model `User` tetap sama (sesuai rekomendasi multi-panel Filament 3.3); akses dibedakan lewat `User::canAccessPanel()` yang sekarang `match` berdasarkan `$panel->getId()`: role `dokter` → panel `dokter`, role admin/humas/dst → panel `admin`.
- **`User::bisaMenanganiDokter(int $dokterId)`** ditambahkan persis sesuai draft di plan ini (dokter menangani sesi miliknya sendiri via `user_id`; admin/humas RS yang sama / super_admin bisa menangani atas nama dokter manapun) — dipakai sebagai aturan otorisasi untuk private channel.
- **Private channel pertama di proyek ini**: `konsultasi.dokter.{dokterId}` didaftarkan di `routes/channels.php` dengan closure otorisasi yang memanggil `bisaMenanganiDokter()`. Didokumentasikan edukatif di `reverb/03-private-channel-dan-dashboard-dokter.md`.
- **`SesiStatusBerubah`** diperluas untuk broadcast ke DUA channel: channel publik berbasis token (existing, untuk halaman pasien) DAN private channel `konsultasi.dokter.{dokterId}` (baru, untuk dashboard) — payload ditambah `sesi_id` & `token` agar dashboard tahu sesi mana yang berubah tanpa fetch ulang semuanya.
- **`TanyaDokter::mulaiSesi()`** sekarang juga `broadcast(new SesiStatusBerubah($sesi))` saat sesi `MENUNGGU` baru dibuat — inilah pemicu notifikasi real-time ke dashboard dokter.
- **Dashboard** (`app/Filament/Dokter/Pages/KonsultasiDashboard.php` + view `resources/views/filament/dokter/pages/konsultasi-dashboard.blade.php`): dua kolom — antrean (BERLANGSUNG dahulu, lalu MENUNGGU FIFO) dengan tombol "Terima Sesi", dan jendela chat untuk sesi aktif (balas + "Akhiri Sesi"), plus toggle switch ketersediaan di bagian atas.
- **Quirk Livewire yang ditemukan**: placeholder dinamis `#[On('echo:konsultasi.{sesiAktifToken},...')]` melempar exception jika `sesiAktifToken` bernilai `null` (Livewire memakai `data_get()` dengan default callback yang `throw`). Solusinya: properti dijaga selalu `string` (default `''`), bukan `?string` — channel `konsultasi.` (kosong) valid tapi tidak pernah menerima siaran apa pun saat tidak ada sesi aktif.
- **Diuji end-to-end** via `Livewire::test()` dgn akun dokter sungguhan (linked ke `Dokter::find(35)`, dibuat & dihapus sebagai data uji): buat sesi → tampil di antrean → terima → balas → akhiri, serta toggle ketersediaan 2 arah — semua berfungsi dan ter-broadcast tanpa error.

## 3.1 — 3.3 Dashboard & Aksi

Buat **Filament custom page** (bukan Resource standar) — karena tampilannya berupa dashboard interaktif (daftar antrian + jendela chat), bukan tabel CRUD biasa:

```bash
php artisan make:filament-page KonsultasiDashboard
```

Isi halaman menampilkan dua panel:
- **Daftar sesi** yang bisa ditangani user saat ini — dikelompokkan per status (`MENUNGGU` lalu `BERLANGSUNG`), terurut FIFO (`created_at` ASC)
- **Jendela chat** untuk sesi yang sedang dipilih/aktif, dengan input balas + tombol "Akhiri Sesi"

Aksi:
- **Terima** (untuk sesi `MENUNGGU`): set `status = BERLANGSUNG`, `mulai_at = now()`, `berakhir_at = now()->addMinutes($sesi->durasi_menit)`, `dibalas_oleh = auth()->id()` → broadcast `SesiStatusBerubah`
- **Kirim balasan**: buat `KonsultasiPesan` dengan `pengirim = DOKTER` → broadcast `PesanDikirim`
- **Akhiri Sesi**: set `status = SELESAI`, `berakhir_at = now()` (jika diakhiri lebih cepat dari jadwal) → broadcast `SesiStatusBerubah`

## 3.2 Logika Otorisasi — "Siapa Bisa Menangani Sesi Dokter X?"

Ini titik penting karena keputusan "kadang dokter sendiri, kadang admin/CS atas nama dokter". Tambahkan helper di model `User`:

```php
public function bisaMenanganiDokter(int $dokterId): bool
{
    $dokter = Dokter::find($dokterId);
    if (! $dokter) return false;

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

Filter daftar sesi di dashboard memakai aturan yang sama — query `SesiKonsultasi` di-scope ke dokter-dokter yang `bisaMenanganiDokter()`-nya `true` untuk user yang sedang login.

**Catatan peran `dokter`:** akun dengan role ini sebaiknya **hanya** bisa mengakses halaman `KonsultasiDashboard` — tidak ke resource admin lain. Atur lewat kebijakan/permission di `filament-shield` (paket sudah ada di `vendor/bezhansalleh/filament-shield`) saat membangun fase ini.

## 3.4 Notifikasi Sesi Baru

Saat `SesiKonsultasi` baru dibuat (status `MENUNGGU`), broadcast ke private channel `konsultasi.dokter.{dokterId}` agar dashboard yang terbuka langsung menampilkan permintaan baru tanpa refresh — gunakan event `SesiStatusBerubah` yang sama dengan di Fase 2.

## 3.5 Quick-Toggle Ketersediaan

Tambahkan tombol/switch di header dashboard agar dokter (atau admin atas nama dokter tsb) bisa langsung set `tersedia_konsultasi` tanpa membuka `DokterResource` — penting untuk alur kerja sehari-hari ("saya online sekarang" / "saya selesai untuk hari ini").

---

# FASE 4 — Otomasi: Timer, Auto-Expire, Antrian

## Checklist

- [ ] **4.1** Console command `konsultasi:tutup-kedaluwarsa`
- [ ] **4.2** Jadwalkan command via `routes/console.php`
- [ ] **4.3** Indikator antrian untuk pasien yang menunggu
- [ ] **4.4** Tampilan countdown timer (client-side, disinkronkan dari `berakhir_at` server)

## 4.1 — 4.2 Auto-Expire Command

**File:** `app/Console/Commands/TutupSesiKedaluwarsa.php`

Logika (dijalankan tiap menit):
- Sesi `BERLANGSUNG` dengan `berakhir_at < now()` → set `status = SELESAI`, broadcast `SesiStatusBerubah`
- Sesi `MENUNGGU` yang dibuat lebih dari **N menit** lalu (mis. 10 menit) tanpa direspon → set `status = KEDALUWARSA`, broadcast `SesiStatusBerubah` (agar pasien yang menunggu tahu sesinya tidak akan dilayani)

Daftarkan di `routes/console.php` mengikuti pola `jadwal:generate-harian`:

```php
Schedule::command('konsultasi:tutup-kedaluwarsa')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/konsultasi-cron.log'));
```

**Kenapa enforcement via command terjadwal (bukan hanya cek di request)?** Supaya sesi tertutup tepat waktu **meski tidak ada yang sedang membuka halaman** — menjaga integritas data status & durasi, dan mencegah dokter "terkunci" di sesi yang sebenarnya sudah berakhir.

## 4.3 Indikator Antrian

Karena satu dokter hanya menangani satu sesi `BERLANGSUNG` dalam satu waktu, tampilkan ke pasien yang berstatus `MENUNGGU`:
- Posisi dalam antrian: `SesiKonsultasi::where('dokter_id', $dokterId)->where('status', 'MENUNGGU')->where('created_at', '<', $sesi->created_at)->count() + 1`
- Estimasi waktu tunggu kasar: `posisi × durasi_menit` (opsional, beri disclaimer "perkiraan")

## 4.4 Countdown Timer

Timer **dihitung dari `berakhir_at` (server)**, bukan dimulai ulang dari nol di client — supaya refresh halaman atau koneksi terputus-sambung tidak mereset waktu. Client cukup menghitung selisih `berakhir_at - now()` saat load, lalu jalankan countdown lokal (di-resync setiap kali event `SesiStatusBerubah` diterima).

---

## Catatan Keamanan & Privasi

- **Token sebagai kredensial**: token UUID v4 (122 bit entropi) — praktis tidak bisa ditebak. Tetap gunakan `throttle` pada route `konsultasi/{token}` untuk mencegah enumerasi brute-force.
- **Validasi RS scope**: setiap akses sesi divalidasi `sesi->rumah_sakit_id === $this->rs->id` agar token dari RS lain tidak bisa diakses lewat slug RS yang salah.
- **Tidak ada riwayat lintas-sesi untuk pasien**: karena tanpa akun, pasien tidak bisa melihat sesi-sesi sebelumnya kecuali menyimpan link/token-nya sendiri — ini trade-off yang disadari dari keputusan "tanpa akun".
- **Disclaimer medis**: tetap wajib ditampilkan — layanan ini bersifat informasi umum, bukan pengganti pemeriksaan langsung.
- **Audit pembalas**: `dibalas_oleh` mencatat akun mana (dokter sungguhan atau admin/CS) yang menangani tiap sesi — penting untuk akuntabilitas internal meskipun tidak ditampilkan ke pasien.
- **XSS**: `isi` pesan dirender sebagai teks polos via `{{ }}` (auto-escape Blade), bukan `{!! !!}`.

## Catatan Operasional (Penting untuk Deployment)

- **Proses Reverb harus selalu berjalan** di production (`php artisan reverb:start` via Supervisor/systemd) — berbeda dari proses web biasa, butuh penyiapan infrastruktur tambahan
- **`CACHE_STORE`**: README sudah menyarankan `redis` untuk rate limiter di production — makin relevan di sini karena trafik real-time lebih intensif
- Pertimbangkan load test sederhana untuk memastikan WebSocket & broadcasting stabil sebelum rilis ke produksi

---

## Sengaja Di Luar Scope (Roadmap Lanjutan Setelah Fase 1–4 Stabil)

- [ ] Riwayat konsultasi untuk pasien (akan butuh akun — bertentangan dengan keputusan "tanpa akun" saat ini, jadi perlu didiskusikan ulang jika dibutuhkan)
- [ ] Notifikasi WA/email otomatis (link sesi, pengingat sesi akan berakhir)
- [ ] Rating/feedback kualitas konsultasi
- [ ] Lampiran gambar/dokumen dalam chat (mis. foto kondisi/hasil lab)
- [ ] Statistik & laporan penggunaan konsultasi per dokter/RS untuk admin
