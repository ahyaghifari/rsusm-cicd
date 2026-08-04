# Planning: Halaman Statis Per RS (Mini-CMS)

Berikut adalah instruksi teknis untuk mengimplementasikan sistem halaman statis generik per rumah sakit. Instruksi ini siap dikerjakan oleh programmer atau AI Model.

## Latar Belakang

Setiap RS perlu memiliki halaman statis seperti "Profil Perusahaan", "Visi & Misi", "Profil Rumah Sakit", dll. Konten tiap halaman berbeda per RS dan jarang berubah. Daripada membuat tabel terpisah per jenis halaman, digunakan satu tabel `halaman` generik yang menampung semua jenis halaman statis untuk semua RS — tidak perlu migrasi baru setiap kali ada jenis halaman baru.

---

## 1. Migrasi Database

Buat file migrasi untuk tabel `halaman` dengan spesifikasi kolom berikut:

- `id` (integer, auto increment, primary key)
- `rumah_sakit_id` (integer, foreign key ke tabel `rumah_sakit`, cascade on delete)
- `key` (varchar 100) — identifier unik per RS, contoh: `profil-perusahaan`, `visi-misi`, `profil-rs`
- `judul` (varchar 255) — judul yang ditampilkan di halaman
- `konten` (JSON, nullable) — konten terstruktur, struktur bebas per jenis halaman (lihat contoh di bawah)
- `aktif` (boolean, default true)
- `timestamps` (created_at, updated_at)

**Unique constraint**: kombinasi `rumah_sakit_id` + `key` harus unik (satu RS tidak boleh punya dua halaman dengan key yang sama).

```php
$table->unique(['rumah_sakit_id', 'key']);
```

### Contoh isi kolom `konten` per key:

```json
// key: "profil-perusahaan"
{
  "sejarah": "RSU Syifa Medika berdiri sejak tahun...",
  "akreditasi": "KARS Paripurna 2023",
  "gambar": "halaman/profil-banjarbaru.jpg"
}

// key: "visi-misi"
{
  "visi": "Menjadi rumah sakit pilihan utama masyarakat Kalimantan Selatan...",
  "misi": [
    "Memberikan pelayanan kesehatan yang profesional dan terpercaya",
    "Mengutamakan keselamatan dan kenyamanan pasien",
    "Mengembangkan SDM yang kompeten dan berintegritas"
  ],
  "nilai": ["Jujur", "Ikhlas", "Profesional", "Terpercaya"]
}

// key: "profil-rs"
{
  "tentang": "RSU Syifa Medika Banjarbaru hadir sebagai...",
  "gambar": "halaman/gedung-banjarbaru.jpg"
}
```

---

## 2. Model `Halaman`

Buat model Eloquent dengan nama `Halaman`, tabel `halaman`.

- **Fillable**: `rumah_sakit_id`, `key`, `judul`, `konten`, `aktif`
- **Casts**: `konten` di-cast sebagai `array`, `aktif` sebagai `boolean`
- **Relasi**: `belongsTo` ke `RumahSakit` melalui `rumah_sakit_id`

---

## 3. Filament Resource `HalamanResource`

Generate resource Filament untuk model `Halaman` menggunakan flag `--simple` (modal-based CRUD).

- **Command**: `php artisan make:filament-resource HalamanResource --simple`

### Konfigurasi Form:

1. **Filter Rumah Sakit** (tidak disimpan, hanya untuk filter `key` existing):
   - `Select::make('rumah_sakit_id')` dengan opsi dari tabel `rumah_sakit`, `->live()`, `->required()`

2. **`key`** — `TextInput::make('key')` dengan helper text: "Contoh: profil-perusahaan, visi-misi, profil-rs. Huruf kecil, pisah dengan tanda hubung."

3. **`judul`** — `TextInput::make('judul')->required()`

4. **`konten`** — `KeyValue::make('konten')` atau `Textarea::make('konten')` untuk input JSON. Tambahkan helper text yang menjelaskan format JSON sesuai key yang digunakan.

5. **`aktif`** — `Toggle::make('aktif')->default(true)`

### Konfigurasi Table (list):

Tampilkan kolom: nama RS, `key`, `judul`, `aktif` (badge), `updated_at`.
Tambahkan filter berdasarkan `rumah_sakit_id` dan `aktif`.

---

## 4. Route

Tambahkan satu route generik di dalam group `{rumahsakit}` di `routes/web.php`:

```php
Route::get('info/{key}', App\Livewire\Pages\HalamanStatis::class)
    ->name('rumahsakit.halaman_statis');
```

Letakkan di bawah route-route yang sudah ada dalam group tersebut.

---

## 5. Livewire Component `Pages\HalamanStatis`

Buat file `app/Livewire/Pages/HalamanStatis.php`.

- **Property**: `public ?Halaman $halaman = null;`
- **Mount**: ambil data dari tabel `halaman` berdasarkan `rumah_sakit_id` dan `key` (dari parameter URL). Jika tidak ditemukan atau `aktif = false`, lakukan `abort(404)`.

```php
public function mount(string $key): void
{
    $rs = current_rumahsakit();
    $this->halaman = \App\Models\Halaman::where('rumah_sakit_id', $rs->id)
        ->where('key', $key)
        ->where('aktif', true)
        ->firstOrFail();
}
```

- **Render**: kembalikan view `rumah_sakit.pages.halaman-statis` dengan variabel `$halaman`.

---

## 6. View `halaman-statis.blade.php`

Buat file `resources/views/rumah_sakit/pages/halaman-statis.blade.php`.

View ini bersifat generik — menampilkan konten berdasarkan `key` halaman. Gunakan `@switch($halaman->key)` atau `@if` untuk merender layout yang sesuai per jenis halaman.

### Struktur umum:

```blade
<div>
    <x-page-hero :title="$halaman->judul" />

    <div class="w-10/12 mx-auto py-16">

        @switch($halaman->key)

            @case('visi-misi')
                {{-- Tampilkan visi, misi (list), nilai (badge) --}}
                @break

            @case('profil-perusahaan')
            @case('profil-rs')
                {{-- Tampilkan sejarah/tentang + gambar --}}
                @break

            @default
                {{-- Fallback: tampilkan konten dalam format key-value sederhana --}}

        @endswitch

    </div>
</div>
```

**Detail tiap case:**

- **`visi-misi`**: tampilkan `konten['visi']` sebagai paragraf besar, `konten['misi']` sebagai list dengan ikon centang, `konten['nilai']` sebagai badge-badge.
- **`profil-perusahaan` / `profil-rs`**: tampilkan `konten['sejarah']` atau `konten['tentang']` sebagai teks panjang, `konten['gambar']` sebagai foto (jika ada).
- **default**: loop `$halaman->konten` dan tampilkan tiap key-value secara sederhana.

---

## 7. Update Nav Link "Profil Perusahaan"

Di `resources/views/rumah_sakit/nav.blade.php`, ubah link "Profil Perusahaan" yang saat ini kosong menjadi:

```blade
<a wire:navigate
   href="{{ rumahsakit_route('rumahsakit.halaman_statis', ['key' => 'profil-perusahaan']) }}"
   class="flex items-center gap-x-3.5 py-2 px-3 ...">
    Profil Perusahaan
</a>
```

---

## 8. Seeder (Opsional)

Buat seeder `HalamanSeeder` dengan data contoh untuk RS Banjarbaru (rumah_sakit_id = 1):

| key | judul | konten |
|---|---|---|
| `profil-perusahaan` | Profil Perusahaan | `{"sejarah": "RSU Syifa Medika Banjarbaru berdiri sejak..."}` |
| `visi-misi` | Visi & Misi | `{"visi": "...", "misi": ["...", "..."], "nilai": ["Jujur", "Profesional"]}` |

Daftarkan seeder di `DatabaseSeeder.php`.
