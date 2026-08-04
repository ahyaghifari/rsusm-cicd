# Syifa Magazine — PDF Flipbook

## Tujuan
Menampilkan edisi-edisi Syifa Magazine dalam format flipbook interaktif berbasis PDF yang di-upload langsung ke server, dirender oleh PDF.js di browser dan ditampilkan menggunakan StPageFlip — semua di dalam satu halaman (modal overlay, tanpa navigasi ke halaman baru).

---

## Stack & Library

- **Backend**: Laravel 12, Filament 3, `BaseRumahSakitResource`
- **Frontend**: Livewire 3, Tailwind CSS v4, Preline UI
- **PDF Renderer**: [PDF.js](https://mozilla.github.io/pdf.js/) (CDN) — render halaman PDF ke `<canvas>`
- **Flipbook**: [StPageFlip](https://github.com/Nodlik/StPageFlip) (CDN) — efek balik halaman dari array gambar

---

## Alur Kerja

```
Admin upload PDF via Filament
    → disimpan di storage/app/public/magazine/pdf/
    → cover thumbnail upload terpisah di magazine/cover/

Visitor buka /{rumahsakit}/magazine
    → grid semua edisi (cover, judul, tanggal)

Visitor klik satu cover/card
    → modal overlay terbuka (JS, tanpa navigasi halaman)
    → PDF.js fetch PDF dari storage URL (dari data-pdf-url pada card)
    → render tiap halaman jadi canvas → toDataURL('image/jpeg')
    → StPageFlip.loadFromImages(arrayDataUrl)
    → flipbook tampil di dalam modal, bisa drag/klik prev-next
    → tombol X untuk tutup modal & destroy flipbook instance
```

---

## Database — Tabel `magazines`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `rumah_sakit_id` | FK → rumah_sakit | Multi-tenant |
| `judul` | string(255) | Judul edisi, misal "Edisi Juni 2025" |
| `slug` | string(100) | Auto dari judul, unique per RS |
| `cover` | string nullable | Path gambar cover (thumbnail) |
| `file_pdf` | string | Path file PDF di storage |
| `deskripsi` | text nullable | Deskripsi singkat edisi |
| `aktif` | boolean default true | Publish / draft |
| `published_at` | date nullable | Tanggal terbit (display saja) |
| `timestamps` | | |

**Unique constraint**: `(rumah_sakit_id, slug)`

---

## File yang Dibuat / Dimodifikasi

### Baru
1. `database/migrations/xxxx_create_magazines_table.php`
2. `app/Models/Magazine.php`
3. `app/Filament/Resources/MagazineResource.php`
4. `app/Filament/Resources/MagazineResource/Pages/ManageMagazines.php`
5. `app/Livewire/Pages/Magazines.php`
6. `resources/views/rumah_sakit/pages/magazines.blade.php`

### Dimodifikasi
7. `routes/web.php` — tambah 1 route magazine
8. `resources/views/rumah_sakit/nav.blade.php` — tambah link Magazine di navigasi

---

## Filament Resource

- Extends `BaseRumahSakitResource`
- Simple resource (`ManageMagazines` — satu halaman)
- `mutateFormDataBeforeCreate` / `mutateFormDataBeforeSave` untuk set `rumah_sakit_id` non-superadmin
- Auto-slug dari judul (pattern sama seperti PromoResource)

### Form Fields
```
rsFormField()               ← rumah_sakit_id (superadmin only)
TextInput judul             ← required, live(onBlur), afterStateUpdated → set slug
TextInput slug              ← required, unique per RS, helperText
DatePicker published_at     ← nullable, label "Tanggal Terbit"
Textarea deskripsi          ← nullable, rows 3
FileUpload cover            ← image, disk public, directory magazine/cover
FileUpload file_pdf         ← acceptedFileTypes ['application/pdf'], disk public,
                               directory magazine/pdf, maxSize 20480 (20MB)
Toggle aktif                ← default true
```

### Table Columns
```
ImageColumn cover           ← square
TextColumn judul            ← searchable, sortable
rsTableColumn()             ← Rumah Sakit (superadmin only)
TextColumn published_at     ← date format d M Y
IconColumn aktif            ← boolean
```

### Filters
```
rsTableFilter()             ← Rumah Sakit (superadmin only)
TernaryFilter aktif
```

---

## Route

```php
// Hanya 1 route — tidak ada show page terpisah
Route::get('magazine', App\Livewire\Pages\Magazines::class)
    ->name('rumahsakit.magazine');
```

---

## Livewire Component — `Pages\Magazines`

Lokasi: `app/Livewire/Pages/Magazines.php`

```php
class Magazines extends Component
{
    public function render()
    {
        $rs = current_rumahsakit();
        $magazines = Magazine::where('rumah_sakit_id', $rs->id)
            ->where('aktif', true)
            ->orderByDesc('published_at')
            ->get();

        return view('rumah_sakit.pages.magazines', compact('magazines'));
    }
}
```

> Tidak perlu pagination untuk saat ini — jumlah edisi magazine biasanya tidak banyak.

---

## View — `pages/magazines.blade.php`

### Layout Grid
```
grid-cols-2 md:grid-cols-3 lg:grid-cols-4
```
Proporsi card: `aspect-[3/4]` — portrait seperti majalah fisik

### Per Card
```html
<div class="cursor-pointer magazine-card"
     data-pdf-url="{{ Storage::url($magazine->file_pdf) }}"
     data-judul="{{ $magazine->judul }}">
    <img src="{{ Storage::url($magazine->cover) }}" ... />
    <div>{{ $magazine->judul }}</div>
    <div>{{ $magazine->published_at?->format('d M Y') }}</div>
</div>
```

### Modal Overlay (flipbook viewer)
```html
<!-- Overlay fullscreen -->
<div id="magazine-modal" class="hidden fixed inset-0 z-50 bg-black/90 flex flex-col items-center justify-center">

    <!-- Header modal: judul + tombol tutup -->
    <div class="flex justify-between w-full px-4 py-2">
        <span id="modal-judul" class="text-white font-bold"></span>
        <button id="modal-close">✕</button>
    </div>

    <!-- Loading indicator -->
    <div id="flipbook-loading" class="text-white text-center">
        <div>Memuat magazine...</div>
        <div id="loading-progress">0%</div>
    </div>

    <!-- Flipbook container -->
    <div id="flipbook-container" class="hidden">
        <div id="flipbook"></div>
    </div>

    <!-- Kontrol navigasi -->
    <div id="flipbook-controls" class="hidden flex gap-4 items-center mt-4 text-white">
        <button id="btn-prev">← Sebelumnya</button>
        <span id="page-counter">Hal 1 / 1</span>
        <button id="btn-next">Berikutnya →</button>
    </div>
</div>
```

---

## JavaScript Flow (di dalam view)

```javascript
// CDN
// PDF.js  : cdnjs - pdf.min.js + pdf.worker.min.js
// StPageFlip : jsdelivr - page-flip.browser.min.js

let pageFlipInstance = null;

// Klik card → buka modal & load PDF
document.querySelectorAll('.magazine-card').forEach(card => {
    card.addEventListener('click', () => {
        const pdfUrl = card.dataset.pdfUrl;
        const judul  = card.dataset.judul;
        openMagazine(pdfUrl, judul);
    });
});

async function openMagazine(pdfUrl, judul) {
    // Tampilkan modal, set judul
    document.getElementById('magazine-modal').classList.remove('hidden');
    document.getElementById('modal-judul').textContent = judul;
    document.getElementById('flipbook-loading').classList.remove('hidden');
    document.getElementById('flipbook-container').classList.add('hidden');
    document.getElementById('flipbook-controls').classList.add('hidden');

    // Load PDF dengan PDF.js
    const pdfjsLib = window['pdfjs-dist/build/pdf'];
    pdfjsLib.GlobalWorkerOptions.workerSrc = WORKER_URL;

    const pdf = await pdfjsLib.getDocument(pdfUrl).promise;
    const total = pdf.numPages;
    const images = [];
    let pageWidth, pageHeight;

    for (let i = 1; i <= total; i++) {
        const page = await pdf.getPage(i);
        const viewport = page.getViewport({ scale: 1.5 });
        pageWidth  = viewport.width;
        pageHeight = viewport.height;

        const canvas = document.createElement('canvas');
        canvas.width  = pageWidth;
        canvas.height = pageHeight;
        await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
        images.push(canvas.toDataURL('image/jpeg', 0.85));

        // Update progress
        document.getElementById('loading-progress').textContent =
            `${Math.round((i / total) * 100)}%`;
    }

    // Init StPageFlip
    document.getElementById('flipbook-loading').classList.add('hidden');
    document.getElementById('flipbook-container').classList.remove('hidden');
    document.getElementById('flipbook-controls').classList.remove('hidden');

    pageFlipInstance = new St.PageFlip(document.getElementById('flipbook'), {
        width: pageWidth,
        height: pageHeight,
        showCover: true,
        mobileScrollSupport: false,
    });
    pageFlipInstance.loadFromImages(images);

    // Update counter saat flip
    pageFlipInstance.on('flip', (e) => {
        document.getElementById('page-counter').textContent =
            `Hal ${e.data + 1} / ${total}`;
    });
}

// Tutup modal
document.getElementById('modal-close').addEventListener('click', () => {
    document.getElementById('magazine-modal').classList.add('hidden');
    if (pageFlipInstance) {
        pageFlipInstance.destroy();
        pageFlipInstance = null;
    }
    // Reset flipbook div agar bisa dipakai ulang
    document.getElementById('flipbook').innerHTML = '';
});

// Prev / Next
document.getElementById('btn-prev').addEventListener('click', () => pageFlipInstance?.flipPrev());
document.getElementById('btn-next').addEventListener('click', () => pageFlipInstance?.flipNext());
```

---

## CDN yang Dipakai

```html
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.min.js"></script>
```
Worker URL (diset via JS):
```
https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js
```

---

## Navigasi

Tambahkan di `nav.blade.php` link "Magazine" di dalam **dropdown "Tentang Kami"**, posisi tepat **di atas "Partner Kami"**.

Struktur dropdown Tentang Kami setelah perubahan:
```
Tentang Kami ▾
  ├── [halaman statis dari DB — dynamic loop]
  ├── Magazine           ← BARU, di sini
  └── Partner Kami       ← sudah ada, tetap di bawah
```

---

## Catatan & Batasan

- PDF besar (>10MB, >50 halaman) → render awal lambat → wajib ada **progress bar**
- StPageFlip butuh **ukuran fixed** — diambil dari viewport halaman pertama PDF
- Di **mobile**: StPageFlip otomatis single-page mode, navigasi via tombol prev/next
- File PDF harus accessible via URL publik (disk `public`, `storage:link` sudah ada)
- Destroy flipbook instance saat modal ditutup agar tidak ada memory leak
- `maxSize` FileUpload Filament = 20480 KB (20MB) — bisa disesuaikan
