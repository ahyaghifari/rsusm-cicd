# Implementasi: Artikel & Berita Per Rumah Sakit (+ Kategori Artikel)

> **Cara pakai dokumen ini**: Ikuti tiap langkah **berurutan dari atas ke bawah**. Tiap langkah
> punya path file lengkap dan isi file lengkap (copy-paste apa adanya, ganti tanggal migrasi
> kalau perlu). Jangan loncat urutan — `kategori_artikel` HARUS dibuat sebelum `artikel` karena
> ada foreign key. Setelah semua langkah selesai, jalankan bagian **Verifikasi** di paling akhir.

Konteks: link "Artikel & Berita" akan ditaruh di dropdown navigasi **"Media Informasi"**
(sejajar Syifa Magazine & FAQ). Lihat [revisi/revisi-belum-selesai.md](../revisi/revisi-belum-selesai.md).

Pola yang diikuti:
- List + Detail (2 halaman publik) — sama seperti Promo (`PromoResource`, `Pages\Promo`, `Pages\PromoDetail`)
- Filament Resource pakai `--generate` (3 halaman terpisah) — sama seperti `FaqResource`, karena field `konten` butuh ruang penuh
- Kategori pakai Resource simpel (`--simple`, modal) — sama seperti `PromoResource`
- Scoping rumah sakit: kalau user login punya `rumah_sakit_id`, otomatis terisi & field disembunyikan. Kalau superadmin, wajib pilih dulu dari dropdown. Pola ini sudah baku di seluruh resource lain via `BaseRumahSakitResource`.

---

## Ringkasan Kolom

### Tabel `kategori_artikel`

| Kolom | Tipe |
|---|---|
| `id` | bigint PK |
| `rumah_sakit_id` | FK → rumah_sakit, cascade delete |
| `nama` | string(100) |
| `slug` | string(100), composite unique dengan `rumah_sakit_id` |
| `timestamps` | |

### Tabel `artikel`

| Kolom | Tipe |
|---|---|
| `id` | bigint PK |
| `rumah_sakit_id` | FK → rumah_sakit, cascade delete |
| `kategori_artikel_id` | FK → kategori_artikel, nullable, set null kalau kategori dihapus |
| `judul` | string(255) |
| `slug` | string(255), composite unique dengan `rumah_sakit_id` |
| `ringkasan` | text, nullable |
| `konten` | longText |
| `gambar` | string(255), nullable |
| `penulis` | string(100), nullable |
| `tanggal_publish` | date, default hari ini |
| `unggulan` | boolean, default false |
| `aktif` | boolean, default true |
| `timestamps` | |

**Kenapa `slug` composite unique `(slug, rumah_sakit_id)`, bukan unique global?** Kalau dua RS
beda punya artikel dengan judul (jadi slug) yang sama, unique global bikin RS kedua gagal
insert. Ini bug yang sama yang sudah diperbaiki di tabel `dokter` — lihat
`database/migrations/2026_06_17_000002_fix_slug_unique_to_composite_dokter.php` sebagai contoh
nyata pola perbaikannya.

**Kenapa kategori jadi tabel terpisah + FK, bukan kolom string/enum?** Supaya tiap RS bisa punya
daftar kategori sendiri (multi-tenant) dan admin bisa tambah/ubah/hapus kategori sendiri lewat
Filament tanpa perlu ubah kode.

**Tidak ada `sort_order`** — beda dari FAQ. Artikel diurutkan berdasarkan `tanggal_publish`
(terbaru dulu), bukan urutan manual drag-and-drop.

---

## Langkah 1 — Migrasi `kategori_artikel`

Jalankan:
```bash
php artisan make:migration create_kategori_artikel_table --create=kategori_artikel
```

Ganti isi file yang baru dibuat (di `database/migrations/`, nama file diawali timestamp
otomatis) jadi:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_artikel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rumah_sakit_id')->constrained('rumah_sakit')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->string('slug', 100);
            $table->timestamps();

            $table->unique(['slug', 'rumah_sakit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_artikel');
    }
};
```

---

## Langkah 2 — Migrasi `artikel`

Jalankan (HARUS setelah Langkah 1, karena ada FK ke `kategori_artikel`):
```bash
php artisan make:migration create_artikel_table --create=artikel
```

Ganti isi file yang baru dibuat jadi:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artikel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rumah_sakit_id')->constrained('rumah_sakit')->cascadeOnDelete();
            $table->foreignId('kategori_artikel_id')->nullable()->constrained('kategori_artikel')->nullOnDelete();
            $table->string('judul', 255);
            $table->string('slug', 255);
            $table->text('ringkasan')->nullable();
            $table->longText('konten');
            $table->string('gambar', 255)->nullable();
            $table->string('penulis', 100)->nullable();
            $table->date('tanggal_publish')->default(now());
            $table->boolean('unggulan')->default(false);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['slug', 'rumah_sakit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artikel');
    }
};
```

---

## Langkah 3 — Jalankan migrasi

```bash
php artisan migrate
```

---

## Langkah 4 — Model `KategoriArtikel`

Buat file baru: `app/Models/KategoriArtikel.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriArtikel extends Model
{
    protected $table = 'kategori_artikel';

    protected $fillable = ['rumah_sakit_id', 'nama', 'slug'];

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class);
    }

    public function artikel(): HasMany
    {
        return $this->hasMany(Artikel::class);
    }
}
```

---

## Langkah 5 — Model `Artikel`

Buat file baru: `app/Models/Artikel.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Artikel extends Model
{
    protected $table = 'artikel';

    protected $fillable = [
        'rumah_sakit_id', 'kategori_artikel_id', 'judul', 'slug', 'ringkasan', 'konten',
        'gambar', 'penulis', 'tanggal_publish', 'unggulan', 'aktif',
    ];

    protected $casts = [
        'unggulan'        => 'boolean',
        'aktif'           => 'boolean',
        'tanggal_publish' => 'date',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class);
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriArtikel::class, 'kategori_artikel_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
```

---

## Langkah 6 — Filament Resource `KategoriArtikelResource`

Jalankan:
```bash
php artisan make:filament-resource KategoriArtikel --simple
```

Ini akan membuat `app/Filament/Resources/KategoriArtikelResource.php` dan
`app/Filament/Resources/KategoriArtikelResource/Pages/ManageKategoriArtikel.php`. **Timpa
seluruh isi kedua file itu** dengan kode di bawah.

### `app/Filament/Resources/KategoriArtikelResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KategoriArtikelResource\Pages;
use App\Models\KategoriArtikel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class KategoriArtikelResource extends BaseRumahSakitResource
{
    protected static ?string $model = KategoriArtikel::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Kategori Artikel';

    protected static ?string $modelLabel = 'Kategori Artikel';

    protected static ?string $navigationGroup = 'Media Informasi';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::rsFormField()->live(),

                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Forms\Set $set, $record) {
                        if (! $record) {
                            $set('slug', \Illuminate\Support\Str::slug($state));
                        }
                    }),

                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(100)
                    ->unique(KategoriArtikel::class, 'slug', ignoreRecord: true, modifyRuleUsing: function ($rule, Forms\Get $get) {
                        return $rule->where('rumah_sakit_id', static::isSuperAdmin() ? $get('rumah_sakit_id') : static::rumahSakitId());
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('artikel_count')
                    ->label('Jumlah Artikel')
                    ->state(fn (KategoriArtikel $record): int => $record->artikel()->count()),

                static::rsTableColumn(),
            ])
            ->filters([
                static::rsTableFilter(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(fn (array $data, KategoriArtikel $record): array => static::mutateFormDataBeforeSave($data, $record)),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (! static::isSuperAdmin()) {
            $data['rumah_sakit_id'] = static::rumahSakitId();
        }
        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data, KategoriArtikel $record): array
    {
        if (! static::isSuperAdmin()) {
            $data['rumah_sakit_id'] = static::rumahSakitId();
        }
        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageKategoriArtikel::route('/'),
        ];
    }
}
```

### `app/Filament/Resources/KategoriArtikelResource/Pages/ManageKategoriArtikel.php`

```php
<?php

namespace App\Filament\Resources\KategoriArtikelResource\Pages;

use App\Filament\Resources\KategoriArtikelResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageKategoriArtikel extends ManageRecords
{
    protected static string $resource = KategoriArtikelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(fn (array $data): array => KategoriArtikelResource::mutateFormDataBeforeCreate($data)),
        ];
    }
}
```

> **Penting**: karena pakai `ManageRecords` (1 halaman, modal), hook `mutateFormDataBeforeCreate`
> / `mutateFormDataBeforeSave` di Resource **tidak otomatis terpanggil** oleh Filament. Itu
> kenapa harus di-wiring manual lewat `->mutateFormDataUsing(...)` di `CreateAction` (file Page
> di atas) dan `EditAction` (di method `table()` Resource). Kalau langkah ini terlewat,
> `rumah_sakit_id` tidak akan otomatis terisi untuk user non-superadmin dan akan error
> "NOT NULL constraint failed". (Ini bug nyata yang pernah terjadi di `PromoResource` dan sudah
> diperbaiki dengan pola yang sama persis seperti di atas.)

---

## Langkah 7 — Filament Resource `ArtikelResource`

Jalankan:
```bash
php artisan make:filament-resource Artikel --generate
```

Ini akan membuat `app/Filament/Resources/ArtikelResource.php` dan 3 file di
`app/Filament/Resources/ArtikelResource/Pages/`: `ListArtikels.php`, `CreateArtikel.php`,
`EditArtikel.php`. **Timpa seluruh isi ke-4 file itu** dengan kode di bawah.

### `app/Filament/Resources/ArtikelResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArtikelResource\Pages;
use App\Models\Artikel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class ArtikelResource extends BaseRumahSakitResource
{
    protected static ?string $model = Artikel::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationLabel = 'Artikel & Berita';

    protected static ?string $navigationGroup = 'Media Informasi';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::rsFormField()->live(),

                TextInput::make('judul')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Forms\Set $set, $record) {
                        if (! $record) {
                            $set('slug', \Illuminate\Support\Str::slug($state));
                        }
                    })
                    ->columnSpanFull(),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(Artikel::class, 'slug', ignoreRecord: true, modifyRuleUsing: function ($rule, Forms\Get $get) {
                        return $rule->where('rumah_sakit_id', static::isSuperAdmin() ? $get('rumah_sakit_id') : static::rumahSakitId());
                    })
                    ->helperText('Otomatis dari judul. Bisa diubah manual.'),

                Select::make('kategori_artikel_id')
                    ->label('Kategori')
                    ->relationship(
                        name: 'kategori',
                        titleAttribute: 'nama',
                        modifyQueryUsing: function ($query, Forms\Get $get) {
                            return $query->where(
                                'rumah_sakit_id',
                                static::isSuperAdmin() ? $get('rumah_sakit_id') : static::rumahSakitId()
                            );
                        },
                    )
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->createOptionForm([
                        TextInput::make('nama')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        TextInput::make('slug')
                            ->required(),
                    ]),

                FileUpload::make('gambar')
                    ->image()
                    ->disk('public')
                    ->directory('artikel')
                    ->imageEditor()
                    ->nullable(),

                TextInput::make('penulis')
                    ->maxLength(100)
                    ->nullable(),

                DatePicker::make('tanggal_publish')
                    ->default(now())
                    ->required(),

                Textarea::make('ringkasan')
                    ->rows(3)
                    ->maxLength(300)
                    ->columnSpanFull()
                    ->helperText('Ditampilkan di card listing & meta description.'),

                RichEditor::make('konten')
                    ->required()
                    ->columnSpanFull(),

                Toggle::make('unggulan')
                    ->label('Artikel Unggulan')
                    ->default(false),

                Toggle::make('aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal_publish', 'desc')
            ->columns([
                ImageColumn::make('gambar')
                    ->disk('public')
                    ->square(),

                TextColumn::make('judul')
                    ->searchable()
                    ->limit(60),

                TextColumn::make('kategori.nama')
                    ->label('Kategori')
                    ->badge()
                    ->toggleable(),

                TextColumn::make('penulis')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tanggal_publish')
                    ->date('d M Y')
                    ->sortable(),

                static::rsTableColumn(),

                IconColumn::make('unggulan')
                    ->boolean()
                    ->sortable(),

                ToggleColumn::make('aktif'),

                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                static::rsTableFilter(),
                SelectFilter::make('kategori_artikel_id')
                    ->label('Kategori')
                    ->relationship('kategori', 'nama'),
                TernaryFilter::make('aktif')->label('Status Aktif'),
                TernaryFilter::make('unggulan')->label('Artikel Unggulan'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (! static::isSuperAdmin()) {
            $data['rumah_sakit_id'] = static::rumahSakitId();
        }
        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        if (! static::isSuperAdmin()) {
            $data['rumah_sakit_id'] = static::rumahSakitId();
        }
        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListArtikels::route('/'),
            'create' => Pages\CreateArtikel::route('/create'),
            'edit'   => Pages\EditArtikel::route('/{record}/edit'),
        ];
    }
}
```

> Beda dengan `KategoriArtikelResource` (`ManageRecords`), `ArtikelResource` pakai `--generate`
> (halaman terpisah: `ListArtikels`, `CreateArtikel`, `EditArtikel`). Untuk pola halaman
> terpisah ini, Filament **otomatis** memanggil `mutateFormDataBeforeCreate` /
> `mutateFormDataBeforeSave` — **tidak perlu** wiring manual `mutateFormDataUsing` seperti di
> `KategoriArtikelResource`.

### `app/Filament/Resources/ArtikelResource/Pages/ListArtikels.php`

```php
<?php

namespace App\Filament\Resources\ArtikelResource\Pages;

use App\Filament\Resources\ArtikelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListArtikels extends ListRecords
{
    protected static string $resource = ArtikelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
```

### `app/Filament/Resources/ArtikelResource/Pages/CreateArtikel.php`

```php
<?php

namespace App\Filament\Resources\ArtikelResource\Pages;

use App\Filament\Resources\ArtikelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArtikel extends CreateRecord
{
    protected static string $resource = ArtikelResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
```

### `app/Filament/Resources/ArtikelResource/Pages/EditArtikel.php`

```php
<?php

namespace App\Filament\Resources\ArtikelResource\Pages;

use App\Filament\Resources\ArtikelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArtikel extends EditRecord
{
    protected static string $resource = ArtikelResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
```

---

## Langkah 8 — Livewire Component List: `app/Livewire/Pages/Artikel.php`

Buat file baru:

```php
<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\Artikel as ArtikelModel;

class Artikel extends RsPortalComponent
{
    public function mount(): void
    {
        $this->seo('Artikel & Berita', 'Artikel dan berita terbaru dari ' . $this->rs->nama . '.');
    }

    public function render()
    {
        $artikelUnggulan = ArtikelModel::where('rumah_sakit_id', $this->rs->id)
            ->aktif()
            ->where('unggulan', true)
            ->orderByDesc('tanggal_publish')
            ->first();

        $artikelList = ArtikelModel::where('rumah_sakit_id', $this->rs->id)
            ->aktif()
            ->when($artikelUnggulan, fn ($q) => $q->where('id', '!=', $artikelUnggulan->id))
            ->orderByDesc('tanggal_publish')
            ->paginate(9);

        return view('rumah_sakit.pages.artikel', [
            'artikelUnggulan' => $artikelUnggulan,
            'artikelList'     => $artikelList,
        ]);
    }
}
```

> **Catatan namespace**: file ini bernama `Artikel` di namespace `App\Livewire\Pages`, sedangkan
> model juga bernama `Artikel` di namespace `App\Models`. Karena itu model di-import dengan
> alias `as ArtikelModel` — pola yang sama dipakai di `app/Livewire/Pages/Faq.php` (alias
> `FaqModel`) dan `app/Livewire/Pages/Promo.php`. **Jangan** import `App\Models\Artikel` tanpa
> alias di file ini, akan bentrok nama class.

---

## Langkah 9 — Livewire Component Detail: `app/Livewire/Pages/ArtikelDetail.php`

Buat file baru:

```php
<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\Artikel as ArtikelModel;
use Artesaos\SEOTools\Facades\OpenGraph;
use Livewire\Attributes\Locked;

class ArtikelDetail extends RsPortalComponent
{
    #[Locked]
    public ArtikelModel $artikel;

    public function mount(ArtikelModel $artikel): void
    {
        abort_if(
            $artikel->rumah_sakit_id !== $this->rs->id || ! $artikel->aktif,
            404
        );

        $this->artikel = $artikel;

        $this->seo($artikel->judul, $artikel->ringkasan ?? '');

        if ($artikel->gambar) {
            OpenGraph::addImage(asset('storage/' . $artikel->gambar));
        }
    }

    public function render()
    {
        $artikelLainnya = ArtikelModel::where('rumah_sakit_id', $this->rs->id)
            ->aktif()
            ->where('id', '!=', $this->artikel->id)
            ->orderByDesc('tanggal_publish')
            ->limit(3)
            ->get();

        return view('rumah_sakit.pages.artikel-detail', [
            'artikelLainnya' => $artikelLainnya,
        ]);
    }
}
```

> **Catatan tentang slug & route model binding**: route binding `{artikel:slug}` (lihat Langkah
> 12) mencari berdasarkan slug **tanpa filter RS** (Laravel tidak tahu konteks RS saat resolve
> binding), lalu di-guard manual dengan `abort_if(...)` di atas. Ini pola yang sudah dipakai di
> `PromoDetail.php` — bukan hal baru. Risiko: kalau dua RS kebetulan punya slug identik, RS yang
> artikelnya dibuat lebih dulu akan "menang" binding-nya. Risiko kecil & sudah diterima di
> codebase ini, tidak perlu diperbaiki di fitur ini.

---

## Langkah 10 — View List: `resources/views/rumah_sakit/pages/artikel.blade.php`

Buat file baru:

```blade
<div>

<x-page-hero
    title="Artikel & Berita"
    subtitle="Informasi, tips kesehatan, dan berita terbaru dari kami"
/>

<section class="bg-gradient-to-b from-surface-container/40 to-white">
<div class="w-11/12 lg:w-10/12 mx-auto py-12 lg:py-16">

    @if(!$artikelUnggulan && $artikelList->isEmpty())
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-20 h-20 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-5">
                <span class="material-symbols-outlined text-4xl text-primary">newspaper</span>
            </div>
            <p class="text-lg font-semibold text-on-surface">Belum ada artikel tersedia</p>
        </div>
    @else

        {{-- Artikel Unggulan --}}
        @if($artikelUnggulan)
        <a wire:navigate href="{{ rumahsakit_route('rumahsakit.artikel_detail', ['artikel' => $artikelUnggulan->slug]) }}"
           class="group flex flex-col lg:flex-row gap-6 bg-white rounded-2xl overflow-hidden
                  border border-outline-variant/20 shadow-sm hover:shadow-xl transition-all duration-300 mb-12">

            <div class="lg:w-1/2 bg-gray-50 flex items-center justify-center overflow-hidden aspect-video lg:aspect-auto">
                @if($artikelUnggulan->gambar)
                    <img src="{{ Storage::url($artikelUnggulan->gambar) }}" alt="{{ $artikelUnggulan->judul }}"
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                         loading="lazy">
                @else
                    <span class="material-symbols-outlined text-6xl text-outline-variant">newspaper</span>
                @endif
            </div>

            <div class="lg:w-1/2 flex flex-col justify-center p-6 lg:p-8">
                <span class="inline-flex items-center gap-1 text-xs font-bold uppercase tracking-widest
                             bg-yellow-400 text-primary px-3 py-1 rounded-full mb-4 w-fit">
                    <span class="material-symbols-outlined text-[12px]">star</span> Unggulan
                </span>
                @if($artikelUnggulan->kategori)
                    <span class="text-xs font-semibold text-primary mb-2">{{ $artikelUnggulan->kategori->nama }}</span>
                @endif
                <h2 class="text-xl lg:text-2xl font-bold text-on-surface leading-snug mb-3">
                    {{ $artikelUnggulan->judul }}
                </h2>
                @if($artikelUnggulan->ringkasan)
                    <p class="text-sm text-on-surface-variant leading-relaxed line-clamp-3 mb-4">
                        {{ $artikelUnggulan->ringkasan }}
                    </p>
                @endif
                <p class="text-xs text-on-surface-variant">
                    {{ $artikelUnggulan->tanggal_publish->translatedFormat('d F Y') }}
                    @if($artikelUnggulan->penulis) &middot; {{ $artikelUnggulan->penulis }} @endif
                </p>
            </div>
        </a>
        @endif

        {{-- Grid Artikel --}}
        @if($artikelList->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($artikelList as $artikel)
            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.artikel_detail', ['artikel' => $artikel->slug]) }}"
               class="group flex flex-col bg-white rounded-2xl overflow-hidden
                      border border-outline-variant/20 shadow-sm
                      hover:shadow-xl hover:-translate-y-1 transition-all duration-300">

                <div class="relative bg-gray-50 flex items-center justify-center overflow-hidden aspect-video">
                    @if($artikel->gambar)
                        <img src="{{ Storage::url($artikel->gambar) }}" alt="{{ $artikel->judul }}"
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                             loading="lazy">
                    @else
                        <span class="material-symbols-outlined text-5xl text-outline-variant">newspaper</span>
                    @endif
                </div>

                <div class="flex flex-col flex-1 p-5">
                    @if($artikel->kategori)
                        <span class="text-xs font-semibold text-primary mb-1.5">{{ $artikel->kategori->nama }}</span>
                    @endif
                    <h3 class="font-bold text-on-surface text-sm leading-snug mb-2 line-clamp-2">
                        {{ $artikel->judul }}
                    </h3>
                    @if($artikel->ringkasan)
                        <p class="text-xs text-on-surface-variant leading-relaxed line-clamp-2 mb-3">
                            {{ $artikel->ringkasan }}
                        </p>
                    @endif
                    <p class="text-xs text-on-surface-variant mt-auto">
                        {{ $artikel->tanggal_publish->translatedFormat('d F Y') }}
                    </p>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $artikelList->links() }}
        </div>
        @endif

    @endif

</div>
</section>

</div>
```

---

## Langkah 11 — View Detail: `resources/views/rumah_sakit/pages/artikel-detail.blade.php`

Buat file baru:

```blade
<div>

{{-- Breadcrumb --}}
<div class="max-w-340 mx-auto px-4 pt-8 pb-4">
    <a wire:navigate href="{{ rumahsakit_route('rumahsakit.artikel') }}"
       class="inline-flex items-center gap-1.5 text-sm text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
        Semua Artikel
    </a>
</div>

<div class="max-w-340 mx-auto px-4 pb-12">
    <div class="max-w-3xl mx-auto">

        {{-- Gambar cover --}}
        @if($artikel->gambar)
            <div class="rounded-3xl overflow-hidden mb-8 aspect-video bg-gray-50">
                <img src="{{ Storage::url($artikel->gambar) }}" alt="{{ $artikel->judul }}"
                     class="w-full h-full object-cover">
            </div>
        @endif

        {{-- Kategori --}}
        @if($artikel->kategori)
            <span class="inline-flex items-center text-xs font-bold uppercase tracking-widest
                         bg-primary/10 text-primary px-3 py-1.5 rounded-full mb-4">
                {{ $artikel->kategori->nama }}
            </span>
        @endif

        {{-- Judul --}}
        <h1 class="text-2xl md:text-3xl font-bold text-on-surface leading-snug mb-4">
            {{ $artikel->judul }}
        </h1>

        {{-- Meta: tanggal & penulis --}}
        <p class="text-sm text-on-surface-variant mb-6">
            {{ $artikel->tanggal_publish->translatedFormat('d F Y') }}
            @if($artikel->penulis) &middot; Oleh {{ $artikel->penulis }} @endif
        </p>

        <div class="h-1 w-14 bg-yellow-400 rounded-full mb-8"></div>

        {{-- Konten --}}
        <div class="prose max-w-none text-on-surface/80 leading-relaxed
                    prose-headings:font-bold prose-headings:text-on-surface
                    prose-a:text-primary prose-a:no-underline hover:prose-a:underline">
            {!! str($artikel->konten)->sanitizeHtml() !!}
        </div>

    </div>
</div>

{{-- Artikel Lainnya --}}
@if($artikelLainnya->isNotEmpty())
<section class="border-t border-outline-variant/20 max-w-340 mx-auto px-4 py-12">

    <div class="flex items-center gap-3 mb-8">
        <div class="h-1 w-10 bg-yellow-400 rounded-full"></div>
        <h2 class="text-xl font-bold text-on-surface">Artikel Lainnya</h2>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($artikelLainnya as $a)
            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.artikel_detail', ['artikel' => $a->slug]) }}"
               class="group flex flex-col bg-white rounded-2xl overflow-hidden
                      border border-outline-variant/20 shadow-sm
                      hover:shadow-xl hover:-translate-y-1 transition-all duration-300">

                <div class="relative bg-gray-50 flex items-center justify-center overflow-hidden aspect-video">
                    @if($a->gambar)
                        <img src="{{ Storage::url($a->gambar) }}" alt="{{ $a->judul }}"
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                             loading="lazy">
                    @else
                        <span class="material-symbols-outlined text-5xl text-outline-variant">newspaper</span>
                    @endif
                </div>

                <div class="flex flex-col flex-1 p-4">
                    <h3 class="font-bold text-on-surface text-sm leading-snug mb-1 line-clamp-2">{{ $a->judul }}</h3>
                    <div class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-primary">
                        Baca Selengkapnya
                        <span class="material-symbols-outlined text-[13px] transition-transform group-hover:translate-x-1">arrow_forward</span>
                    </div>
                </div>

            </a>
        @endforeach
    </div>

</section>
@endif

</div>
```

---

## Langkah 12 — Route

Buka `routes/web.php`. Cari baris ini (sudah ada di file):

```php
        Route::get('faq', App\Livewire\Pages\Faq::class)->name('rumahsakit.faq');
```

Tambahkan 2 baris baru **tepat setelah** baris itu (masih di dalam `Route::prefix('{rumahsakit}')` group yang sama):

```php
        Route::get('artikel', App\Livewire\Pages\Artikel::class)->name('rumahsakit.artikel');
        Route::get('artikel/{artikel:slug}', App\Livewire\Pages\ArtikelDetail::class)->name('rumahsakit.artikel_detail');
```

---

## Langkah 13 — Navigasi: `resources/views/rumah_sakit/nav.blade.php`

### 13a. Dropdown desktop "Media Informasi"

Cari blok ini (sudah ada di file, sekitar baris 224–245):

```blade
                <!-- Dropdown Media Informasi -->
                <div class="m-1 hs-dropdown [--trigger:hover] relative inline-flex">
                    <button id="hs-dropdown-media-informasi" type="button"
                        class="hs-dropdown-toggle inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-layer text-layer-foreground hover:bg-layer-hover focus:outline-hidden focus:bg-layer-focus disabled:opacity-50 disabled:pointer-events-none"
                        aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                        Media Informasi
                        <svg class="hs-dropdown-open:rotate-180 size-4" xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>
                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-60 bg-white shadow-md rounded-lg mt-2 after:h-4 after:absolute after:-bottom-4 after:inset-s-0 after:w-full before:h-4 before:absolute before:-top-4 before:inset-s-0 before:w-full z-50 text-on-surface"
                        role="menu" aria-orientation="vertical" aria-labelledby="hs-dropdown-media-informasi">
                        <div class="p-1 space-y-0.5">
                            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.magazine') }}"
                               class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-dropdown-item-foreground hover:bg-gray-200">Syifa Magazine</a>
                            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.faq') }}"
                               class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-dropdown-item-foreground hover:bg-gray-200">FAQ</a>
                        </div>
                    </div>
                </div>
```

Ganti bagian `<div class="p-1 space-y-0.5">...</div>` di dalamnya jadi (tambah 1 baris link di
paling atas, sebelum Syifa Magazine):

```blade
                        <div class="p-1 space-y-0.5">
                            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.artikel') }}"
                               class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-dropdown-item-foreground hover:bg-gray-200">Artikel & Berita</a>
                            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.magazine') }}"
                               class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-dropdown-item-foreground hover:bg-gray-200">Syifa Magazine</a>
                            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.faq') }}"
                               class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-dropdown-item-foreground hover:bg-gray-200">FAQ</a>
                        </div>
```

### 13b. Grid mobile

Cari baris ini (sudah ada di file, di dalam `<div class="sm:hidden grid grid-cols-3 ...">`):

```blade
                <a wire:navigate href="{{ rumahsakit_route('rumahsakit.faq') }}" class="{{ $gridItemClass }}">
                    <span class="{{ $gridIconClass }}">help</span>
                    <span class="{{ $gridLabelClass }}">FAQ</span>
                </a>
```

Tambahkan blok baru **tepat sebelum** baris itu:

```blade
                <a wire:navigate href="{{ rumahsakit_route('rumahsakit.artikel') }}" class="{{ $gridItemClass }}">
                    <span class="{{ $gridIconClass }}">newspaper</span>
                    <span class="{{ $gridLabelClass }}">Artikel</span>
                </a>
```

---

## Verifikasi

Setelah semua langkah di atas selesai, cek satu per satu:

1. `php artisan migrate:status` — pastikan `create_kategori_artikel_table` dan
   `create_artikel_table` statusnya `Ran`, dan urutannya `kategori_artikel` lebih dulu.
2. `php artisan route:list --name=rumahsakit.artikel` — harus muncul 2 route:
   `rumahsakit.artikel` dan `rumahsakit.artikel_detail`.
3. Buka `/admin` (Filament) → login sebagai admin RS biasa (bukan superadmin) → menu "Media
   Informasi" → "Kategori Artikel" → coba buat kategori baru → field Rumah Sakit harus
   **tidak terlihat** (otomatis terisi) → simpan → tidak ada error.
4. Masih sebagai admin RS biasa → menu "Artikel & Berita" → buat artikel baru, isi semua field
   wajib (`judul`, `tanggal_publish`, `konten`) → field Rumah Sakit **tidak terlihat** → coba
   pilih/buat kategori lewat dropdown kategori (tombol "+") → simpan → tidak ada error → cek di
   tabel, kolom "Rumah Sakit" terisi otomatis sesuai RS user yang login.
5. Login sebagai **superadmin** → menu "Artikel & Berita" → buat artikel baru → field Rumah
   Sakit **harus terlihat dan wajib dipilih** → pilih salah satu RS → field Kategori harus
   menampilkan kategori milik RS yang baru dipilih (bukan RS lain) → simpan → tidak ada error.
6. Buka halaman publik `/{slug-rumah-sakit}/artikel` → artikel yang baru dibuat (kalau
   `aktif = true`) harus tampil di grid atau di bagian "Unggulan" (kalau `unggulan = true`).
7. Klik salah satu card artikel → harus pindah ke `/{slug-rumah-sakit}/artikel/{slug-artikel}`
   → konten lengkap tampil, section "Artikel Lainnya" di bawah tampil (kalau ada artikel lain).
8. Coba akses artikel milik RS lain lewat URL langsung (ganti slug RS di URL ke RS lain, slug
   artikel tetap) → harus 404.
9. Cek dropdown navigasi "Media Informasi" di halaman publik (desktop, lebar layar ≥ 640px) →
   harus ada 3 item: "Artikel & Berita", "Syifa Magazine", "FAQ". Cek juga grid menu di mobile
   (lebar layar < 640px, klik hamburger) → ada item "Artikel" dengan icon koran.
10. `php artisan view:clear` kalau ada perubahan blade yang belum kelihatan di browser.
