# Planning: FAQ Per Rumah Sakit

Instruksi teknis untuk mengimplementasikan fitur FAQ (Frequently Asked Questions) per rumah sakit.

---

## 1. Migrasi Database

Buat file migrasi untuk tabel `faq`. Tambahkan kolom `sort_order` untuk fitur reorder drag-and-drop di Filament.

```php
Schema::create('faq', function (Blueprint $table) {
    $table->id();
    $table->foreignId('rumah_sakit_id')->constrained('rumah_sakit')->cascadeOnDelete();
    $table->string('judul', 255);
    $table->longText('deskripsi');
    $table->smallInteger('sort_order')->default(0);
    $table->boolean('aktif')->default(true);
    $table->timestamps();
});
```

Kolom:
- `id` — primary key
- `rumah_sakit_id` — foreign key ke `rumah_sakit`, cascade delete
- `judul` (varchar 255) — teks pertanyaan
- `deskripsi` (longText) — teks jawaban (rich text)
- `sort_order` (smallInteger, default 0) — untuk drag-and-drop reorder
- `aktif` (boolean, default true)
- `timestamps`

---

## 2. Model `Faq`

Buat `app/Models/Faq.php`, tabel `faq`.

```php
class Faq extends Model
{
    protected $table = 'faq';

    protected $fillable = ['rumah_sakit_id', 'judul', 'deskripsi', 'sort_order', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(RumahSakit::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
```

---

## 3. Filament Resource `FAQResource`

### Generate command:

```bash
php artisan make:filament-resource Faq --generate
```

Flag `--generate` menghasilkan tiga halaman terpisah: `ListFaqs`, `CreateFaq`, `EditFaq` (bukan modal).

### Struktur resource:

Extends `BaseRumahSakitResource`, bukan `Resource`. Navigation group: `'Konten'`.

### Form schema:

```php
static::rsFormField(),

TextInput::make('judul')
    ->label('Pertanyaan')
    ->required()
    ->maxLength(255)
    ->columnSpanFull(),

RichEditor::make('deskripsi')
    ->label('Jawaban')
    ->required()
    ->columnSpanFull(),

Toggle::make('aktif')
    ->default(true),
```

### Table:

```php
$table
    ->reorderable('sort_order')          // drag-and-drop reorder
    ->defaultSort('sort_order', 'asc')
    ->columns([
        TextColumn::make('sort_order')
            ->label('#')
            ->sortable(),

        TextColumn::make('judul')
            ->label('Pertanyaan')
            ->searchable()
            ->limit(60),

        static::rsTableColumn(),

        ToggleColumn::make('aktif'),

        TextColumn::make('created_at')
            ->dateTime('d M Y')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true),
    ])
    ->filters([
        static::rsTableFilter(),
        TernaryFilter::make('aktif')->label('Status Aktif'),
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
```

### mutateFormData (sama seperti BannerResource / PromoResource):

```php
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
```

### getPages:

```php
public static function getPages(): array
{
    return [
        'index'  => Pages\ListFaqs::route('/'),
        'create' => Pages\CreateFaq::route('/create'),
        'edit'   => Pages\EditFaq::route('/{record}/edit'),
    ];
}
```

---

## 4. Halaman Pages — Redirect ke Index

Setelah create atau update, redirect kembali ke halaman list (bukan ke halaman edit record).

### `app/Filament/Resources/FAQResource/Pages/CreateFaq.php`:

```php
protected function getRedirectUrl(): string
{
    return $this->getResource()::getUrl('index');
}
```

### `app/Filament/Resources/FAQResource/Pages/EditFaq.php`:

```php
protected function getRedirectUrl(): string
{
    return $this->getResource()::getUrl('index');
}
```

`ListFaqs.php` tidak perlu diubah, biarkan seperti hasil generate.

---

## 5. Livewire Component `Pages\Faq`

Buat `app/Livewire/Pages/Faq.php`.

```php
class Faq extends Component
{
    public ?RumahSakit $rs = null;

    public function mount(): void
    {
        $this->rs = current_rumahsakit();
    }

    public function render()
    {
        $faqs = \App\Models\Faq::where('rumah_sakit_id', $this->rs->id)
            ->aktif()
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();

        return view('rumah_sakit.pages.faq', compact('faqs'));
    }
}
```

---

## 6. Route

Tambahkan di `routes/web.php` dalam group `{rumahsakit}`, setelah route `hubungi-kami`:

```php
Route::get('faq', App\Livewire\Pages\Faq::class)->name('rumahsakit.faq');
```

---

## 7. View `faq.blade.php`

Buat `resources/views/rumah_sakit/pages/faq.blade.php`. Gunakan accordion Preline UI (`hs-accordion`).

```blade
<div>
    <x-page-hero
        title="FAQ"
        subtitle="Pertanyaan yang sering ditanyakan"
        icon="help"
    />

    <section class="w-10/12 mx-auto py-12">
        @if($faqs->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <span class="material-symbols-outlined text-6xl text-outline-variant mb-4">help</span>
                <p class="text-lg font-semibold text-on-surface-variant">Belum ada FAQ tersedia</p>
            </div>
        @else
            <div class="hs-accordion-group flex flex-col gap-3 max-w-3xl mx-auto">
                @foreach($faqs as $faq)
                <div class="hs-accordion bg-white border border-outline-variant/30 rounded-xl overflow-hidden shadow-sm"
                     id="faq-{{ $faq->id }}">
                    <button
                        class="hs-accordion-toggle w-full flex justify-between items-center gap-4 px-6 py-4 text-left font-semibold text-on-surface hover:bg-surface-container transition-colors"
                        aria-expanded="false"
                        aria-controls="faq-body-{{ $faq->id }}">
                        {{ $faq->judul }}
                        <span class="material-symbols-outlined shrink-0 text-primary hs-accordion-active:rotate-180 transition-transform duration-200">
                            expand_more
                        </span>
                    </button>
                    <div id="faq-body-{{ $faq->id }}"
                         class="hs-accordion-content hidden overflow-hidden transition-all duration-300"
                         role="region"
                         aria-labelledby="faq-{{ $faq->id }}">
                        <div class="px-6 pb-5 text-on-surface-variant leading-relaxed text-sm">
                            {!! str($faq->deskripsi)->sanitizeHtml() !!}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
```

---

## 8. Navigasi

### Desktop (`nav.blade.php` — bagian `hidden sm:flex`):

Tambahkan setelah link "Hubungi Kami":

```blade
<a class="text-sm text-navbar-nav-foreground hover:text-primary-hover focus:outline-hidden focus:text-primary-focus"
   wire:navigate href="{{ rumahsakit_route('rumahsakit.faq') }}">FAQ</a>
```

### Mobile (`nav.blade.php` — bagian `sm:hidden grid`):

Tambahkan setelah item "Hubungi Kami":

```blade
<a wire:navigate href="{{ rumahsakit_route('rumahsakit.faq') }}" class="{{ $gridItemClass }}">
    <span class="{{ $gridIconClass }}">help</span>
    <span class="{{ $gridLabelClass }}">FAQ</span>
</a>
```

---

## Checklist Implementasi

- [ ] Buat migrasi `create_faq_table` (dengan kolom `sort_order`)
- [ ] Jalankan `php artisan migrate`
- [ ] Buat model `app/Models/Faq.php`
- [ ] Generate resource: `php artisan make:filament-resource Faq --generate`
- [ ] Ubah `FAQResource` extends ke `BaseRumahSakitResource`, sesuaikan form, table, mutateFormData
- [ ] Tambahkan `getRedirectUrl()` di `CreateFaq.php` dan `EditFaq.php`
- [ ] Buat `app/Livewire/Pages/Faq.php`
- [ ] Buat `resources/views/rumah_sakit/pages/faq.blade.php`
- [ ] Tambahkan route di `routes/web.php`
- [ ] Update `nav.blade.php` — link desktop dan item grid mobile
