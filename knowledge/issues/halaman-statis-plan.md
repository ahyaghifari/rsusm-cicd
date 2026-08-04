# Plan Implementasi: Halaman Statis Per RS (Mini-CMS)

Dokumen ini adalah rencana implementasi final berdasarkan diskusi dan penyesuaian dari `halaman-statis.md`.

**Keputusan desain yang berubah dari proposal awal:**
- Kolom `key` → diganti `slug` (konsisten dengan konvensi codebase: Promo, Dokter, PoliKlinik)
- Kolom `konten` → `longText` biasa + `RichEditor` di Filament (bukan JSON), supaya admin tidak perlu tahu format JSON
- View tidak perlu `@switch` per key — cukup render `{!! $halaman->konten !!}` karena format sudah ditangani editor

---

## Checklist Implementasi

- [ ] **1. Migrasi** — buat tabel `halaman`
- [ ] **2. Model** — buat `app/Models/Halaman.php`
- [ ] **3. Filament Resource** — buat `HalamanResource` (--simple)
- [ ] **4. Route** — tambah `info/{slug}` di `web.php`
- [ ] **5. Livewire Component** — buat `app/Livewire/Pages/HalamanStatis.php`
- [ ] **6. View** — buat `resources/views/rumah_sakit/pages/halaman-statis.blade.php`
- [ ] **7. Nav** — dropdown "Tentang Kami" dinamis dari tabel `halaman` via middleware share
- [ ] **8. Seeder** — buat `HalamanSeeder` (opsional)
- [ ] **9. Update `issue.md`** — tandai sub-task selesai

---

## 1. Migrasi

**File:** `database/migrations/YYYY_MM_DD_000001_create_halaman_table.php`

```php
Schema::create('halaman', function (Blueprint $table) {
    $table->id();
    $table->foreignId('rumah_sakit_id')->constrained('rumah_sakit')->cascadeOnDelete();
    $table->string('slug', 100);
    $table->string('judul', 255);
    $table->longText('konten')->nullable();
    $table->boolean('aktif')->default(true);
    $table->timestamps();

    $table->unique(['rumah_sakit_id', 'slug']);
});
```

---

## 2. Model `Halaman`

**File:** `app/Models/Halaman.php`

- Tabel: `halaman`
- Fillable: `rumah_sakit_id`, `slug`, `judul`, `konten`, `aktif`
- Casts: `aktif` → `boolean`
- Relasi: `belongsTo(RumahSakit::class)`

```php
class Halaman extends Model
{
    protected $table = 'halaman';

    protected $fillable = [
        'rumah_sakit_id', 'slug', 'judul', 'konten', 'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function rumahSakit()
    {
        return $this->belongsTo(RumahSakit::class);
    }
}
```

---

## 3. Filament Resource `HalamanResource`

**Command:** `php artisan make:filament-resource Halaman --generate`

**Extends:** `BaseRumahSakitResource` (bukan `Resource`) agar data otomatis difilter per RS.

### Form Schema

```php
Forms\Components\Select::make('rumah_sakit_id')
    ->relationship('rumahSakit', 'nama')
    ->required()
    ->hidden(fn () => !static::isSuperAdmin()), // hanya superadmin yang bisa pilih RS

Forms\Components\TextInput::make('slug')
    ->required()
    ->maxLength(100)
    ->helperText('Huruf kecil, pisah dengan tanda hubung. Contoh: profil-perusahaan, visi-misi')
    ->unique(table: 'halaman', column: 'slug', ignoreRecord: true),

Forms\Components\TextInput::make('judul')
    ->required()
    ->maxLength(255),

Forms\Components\RichEditor::make('konten')
    ->nullable()
    ->columnSpanFull()
    ->helperText('Format konten menggunakan toolbar editor. Gunakan Heading untuk judul, List untuk poin-poin.'),

Forms\Components\Toggle::make('aktif')
    ->default(true),
```

### Table Columns

```php
Tables\Columns\TextColumn::make('rumahSakit.nama')->label('Rumah Sakit')->toggleable(),
Tables\Columns\TextColumn::make('slug')->searchable(),
Tables\Columns\TextColumn::make('judul')->searchable(),
Tables\Columns\IconColumn::make('aktif')->boolean(),
Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
```

### Table Filters

```php
Tables\Filters\SelectFilter::make('rumah_sakit_id')
    ->relationship('rumahSakit', 'nama')
    ->label('Rumah Sakit')
    ->visible(fn () => static::isSuperAdmin()),

Tables\Filters\TernaryFilter::make('aktif'),
```

**Catatan:** `rumah_sakit_id` pada form harus di-set otomatis dari user yang login (untuk admin RS biasa), sama seperti resource lain. Tambahkan `mutateFormDataBeforeCreate` atau `->default(fn () => static::rumahSakitId())`.

---

## 4. Route

**File:** `routes/web.php` — tambah di dalam group `{rumahsakit}`:

```php
Route::get('info/{slug}', App\Livewire\Pages\HalamanStatis::class)
    ->name('rumahsakit.halaman_statis');
```

---

## 5. Livewire Component `HalamanStatis`

**File:** `app/Livewire/Pages/HalamanStatis.php`

```php
namespace App\Livewire\Pages;

use App\Models\Halaman;
use App\Models\RumahSakit;
use Livewire\Component;

class HalamanStatis extends Component
{
    public ?Halaman $halaman = null;

    public function mount(string $slug): void
    {
        $rs = current_rumahsakit();

        $this->halaman = Halaman::where('rumah_sakit_id', $rs->id)
            ->where('slug', $slug)
            ->where('aktif', true)
            ->firstOrFail();
    }

    public function render()
    {
        return view('rumah_sakit.pages.halaman-statis');
    }
}
```

---

## 6. View `halaman-statis.blade.php`

**File:** `resources/views/rumah_sakit/pages/halaman-statis.blade.php`

Layout sederhana: hero + konten HTML dari RichEditor.

```blade
<div>
    <x-page-hero :title="$halaman->judul" />

    <div class="w-10/12 max-w-4xl mx-auto py-12">
        <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/20 p-8 md:p-12">
            <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                {!! $halaman->konten !!}
            </div>
        </div>
    </div>
</div>
```

**Catatan:** Pastikan `tailwindcss/typography` (`prose`) tersedia, atau ganti dengan styling manual jika plugin tidak dipasang.

---

## 7. Update Nav — Dropdown "Tentang Kami" Dinamis

**File:** `resources/views/rumah_sakit/nav.blade.php`

Saat ini dropdown "Tentang Kami" memiliki link "Profil Perusahaan" yang statis (tanpa `href`) dan "Partner Kami" yang sudah aktif. Rencananya, isi dropdown "Tentang Kami" akan **dibuat dinamis** berdasarkan data tabel `halaman` milik RS yang sedang aktif.

### Mekanisme:

`$halaman_nav` di-share via middleware `RumahSakitMiddleware` (sama seperti `$promo_popup`):

```php
// Di RumahSakitMiddleware
$halaman_nav = \App\Models\Halaman::where('rumah_sakit_id', $rumahSakit->id)
    ->where('aktif', true)
    ->orderBy('judul')
    ->get(['slug', 'judul']);

view()->share('halaman_nav', $halaman_nav);
```

### Tampilan di nav:

```blade
<!-- Dropdown Tentang Kami -->
@foreach($halaman_nav as $h)
    <a wire:navigate
       href="{{ rumahsakit_route('rumahsakit.halaman_statis', ['slug' => $h->slug]) }}"
       class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm
              text-dropdown-item-foreground hover:bg-gray-200">
        {{ $h->judul }}
    </a>
@endforeach

{{-- Link Partner Kami tetap statis di bawah --}}
<a wire:navigate href="{{ rumahsakit_route('rumahsakit.partner_kami') }}"
   class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm
          text-dropdown-item-foreground hover:bg-gray-200">
    Partner Kami
</a>
```

**Hasil:** Setiap halaman statis yang ditambahkan via Filament (misalnya `profil-perusahaan`, `visi-misi`, `struktur-organisasi`) akan **otomatis muncul** di dropdown "Tentang Kami" tanpa perlu ubah kode nav lagi.

---

## 8. Seeder `HalamanSeeder` (Opsional)

**File:** `database/seeders/HalamanSeeder.php`

Data contoh untuk RS Banjarbaru (`rumah_sakit_id = 1`):

| slug | judul |
|---|---|
| `profil-perusahaan` | Profil Perusahaan |
| `visi-misi` | Visi & Misi |

Daftarkan di `DatabaseSeeder.php`.

---

## Catatan Tambahan

- **XSS**: konten dari RichEditor di-render dengan `{!! !!}`. Filament RichEditor menggunakan Tiptap yang sudah sanitize output — aman selama input hanya dari admin panel.
- **`prose` plugin**: jika `@tailwindcss/typography` belum terpasang, styling `prose` tidak akan bekerja. Cek `tailwind.config.js` atau ganti dengan class manual.
- **Slug unik per RS**: constraint `unique(['rumah_sakit_id', 'slug'])` membolehkan slug yang sama di RS berbeda (misal, kedua RS bisa punya `profil-perusahaan`).
