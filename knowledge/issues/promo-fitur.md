# Planning: Sistem Promo — Popup, Nav, List & Detail

Berikut adalah instruksi teknis untuk mengimplementasikan fitur promo secara lengkap. Instruksi ini siap dikerjakan oleh programmer atau AI Model.

## Latar Belakang

Model `Promo` sudah ada dengan kolom: `id`, `rumah_sakit_id`, `judul`, `deskripsi`, `gambar`, `popup` (boolean), `aktif` (boolean). Fitur yang perlu dibangun:
1. Popup otomatis untuk promo bertanda `popup = true`, muncul sekali per 24 jam (berbasis localStorage)
2. Tombol floating di homepage untuk membuka ulang popup promo
3. Link "Promo" di navbar yang selalu tampil (tidak ikut collapse di mobile)
4. Halaman list semua promo aktif dengan design magazine-style
5. Halaman detail promo individual

---

## 1. Route Baru

Tambahkan dua route baru di dalam group `{rumahsakit}` di `routes/web.php`:

```php
Route::get('promo', App\Livewire\Pages\Promo::class)->name('rumahsakit.promo');
Route::get('promo/{promo}', App\Livewire\Pages\PromoDetail::class)->name('rumahsakit.promo_detail');
```

`{promo}` menggunakan `id` (route model binding default, tidak perlu slug baru).

---

## 2. Update `RumahSakitMiddleware`

Tambahkan share data `$promo_popup` di `app/Http/Middleware/RumahSakitMiddleware.php` — setelah data RS di-resolve — agar tersedia di semua view RS tanpa query ulang:

```php
$promoPopup = \App\Models\Promo::where('rumah_sakit_id', $rs->id)
    ->aktif()
    ->popup()
    ->orderByDesc('created_at')
    ->get();

View::share('promo_popup', $promoPopup);
```

---

## 3. Popup Promo — di `layout.blade.php`

### Konsep
- Tampil otomatis setelah delay **1.5 detik**
- Tidak muncul kembali dalam **24 jam** menggunakan `localStorage`
  - Key: `promo_popup_{slug_rs}` → value: timestamp Unix saat popup terakhir ditutup
  - Cek: jika key tidak ada ATAU `now - timestamp > 86400` detik → tampilkan
- Ditutup via tombol X, klik backdrop, atau link "Lihat Detail" (tetap ditutup setelah navigasi)
- Jika ada **lebih dari 1 promo popup** → tampilkan sebagai carousel dots sederhana (Alpine.js)

### Implementasi

Tambahkan blok berikut di akhir `resources/views/rumah_sakit/layout.blade.php`, sebelum `</body>`:

```blade
@if(isset($promo_popup) && $promo_popup->isNotEmpty())
<div
    x-data="promoPopup('{{ $currentRumahSakit->slug }}')"
    x-show="visible"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[200] flex items-center justify-center p-4"
    style="display:none;">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="close()"></div>

    {{-- Modal card --}}
    <div class="relative z-10 w-full max-w-lg bg-white rounded-3xl overflow-hidden shadow-2xl">

        {{-- Tombol tutup --}}
        <button @click="close()"
            class="absolute top-3 right-3 z-20 w-8 h-8 bg-black/25 hover:bg-black/50
                   text-white rounded-full flex items-center justify-center transition-colors duration-150">
            <span class="material-symbols-outlined text-[18px]">close</span>
        </button>

        <div x-data="{ current: 0 }">
            @foreach($promo_popup as $i => $p)
            <div x-show="current === {{ $i }}" x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <a href="{{ route('rumahsakit.promo_detail', ['rumahsakit' => $currentRumahSakit->slug, 'promo' => $p->id]) }}"
                   @click="close()">
                    @if($p->gambar)
                        <div class="relative h-56 overflow-hidden">
                            <img src="{{ Storage::url($p->gambar) }}" alt="{{ $p->judul }}"
                                 class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-linear-to-t from-black/40 to-transparent"></div>
                        </div>
                    @endif
                    <div class="p-6">
                        <span class="inline-block text-xs font-bold uppercase tracking-widest
                                     text-primary bg-primary/10 px-3 py-1 rounded-full mb-3">
                            Promo
                            @if($promo_popup->count() > 1) · {{ $i + 1 }}/{{ $promo_popup->count() }} @endif
                        </span>
                        <h3 class="text-xl font-bold text-on-surface leading-snug mb-2">{{ $p->judul }}</h3>
                        @if($p->deskripsi)
                            <p class="text-sm text-on-surface-variant line-clamp-3 leading-relaxed">
                                {{ strip_tags($p->deskripsi) }}
                            </p>
                        @endif
                        <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-primary">
                            Lihat Detail <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach

            {{-- Dots navigator (hanya jika > 1 promo) --}}
            @if($promo_popup->count() > 1)
            <div class="flex justify-center gap-2 pb-5">
                @foreach($promo_popup as $i => $p)
                <button @click="current = {{ $i }}"
                    :class="current === {{ $i }}
                        ? 'bg-primary w-6'
                        : 'bg-outline-variant w-2.5'"
                    class="h-2.5 rounded-full transition-all duration-300"></button>
                @endforeach
            </div>
            @endif
        </div>

    </div>
</div>

@push('scripts')
<script>
function promoPopup(rsSlug) {
    return {
        visible: false,
        storageKey: `promo_popup_${rsSlug}`,
        init() {
            const last = localStorage.getItem(this.storageKey);
            const now  = Math.floor(Date.now() / 1000);
            if (!last || now - parseInt(last) > 86400) {
                setTimeout(() => { this.visible = true; }, 1500);
            }
            window.__promoPopup = this;
        },
        close() {
            this.visible = false;
            localStorage.setItem(this.storageKey, String(Math.floor(Date.now() / 1000)));
        },
        open() {
            this.visible = true;
        }
    };
}
</script>
@endpush
@endif
```

> **Catatan**: Pastikan `layout.blade.php` sudah memiliki `@stack('scripts')` sebelum `</body>`.

---

## 4. Tombol Floating di Homepage

Tambahkan di `resources/views/rumah_sakit/index.blade.php`, sebelum tag `</div>` penutup paling luar:

```blade
@if(isset($promo_popup) && $promo_popup->isNotEmpty())
<div class="fixed bottom-6 left-6 z-[150]">
    <button
        onclick="window.__promoPopup && window.__promoPopup.open()"
        class="relative group flex items-center gap-2 bg-primary text-white
               px-4 py-3 rounded-2xl shadow-xl shadow-primary/40
               hover:shadow-primary/60 hover:-translate-y-0.5
               transition-all duration-200">
        <span class="material-symbols-outlined text-[20px]">local_offer</span>
        <span class="text-sm font-bold pr-1">Promo</span>
        <span class="absolute -top-2 -right-2 w-5 h-5 bg-yellow-400 text-primary text-[10px]
                     font-black rounded-full flex items-center justify-center ring-2 ring-white
                     animate-bounce">
            {{ $promo_popup->count() }}
        </span>
    </button>
</div>
@endif
```

---

## 5. Link "Promo" di Navbar

Di `resources/views/rumah_sakit/nav.blade.php`, di dalam `<div class="sm:order-3 flex items-center gap-x-2">`, tambahkan link promo **sebelum** tombol hamburger. Link ini selalu tampil (tidak masuk collapse):

```blade
{{-- Promo pill — selalu tampil di semua ukuran layar --}}
<a wire:navigate
   href="{{ rumahsakit_route('rumahsakit.promo') }}"
   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold
          bg-yellow-400 text-primary hover:bg-yellow-300 transition-colors duration-150
          shadow-sm">
    <span class="material-symbols-outlined text-[14px]">local_offer</span>
    <span>Promo</span>
    @if(isset($promo_popup) && $promo_popup->isNotEmpty())
        <span class="bg-primary text-white text-[10px] font-black w-4 h-4 rounded-full
                     flex items-center justify-center ml-0.5">
            {{ $promo_popup->count() }}
        </span>
    @endif
</a>
```

---

## 6. Livewire `Pages\Promo`

**File**: `app/Livewire/Pages/Promo.php`

```php
namespace App\Livewire\Pages;

use App\Models\Promo as PromoModel;
use App\Models\RumahSakit;
use Livewire\Component;

class Promo extends Component
{
    public function render()
    {
        $rs = current_rumahsakit();

        $promos = PromoModel::where('rumah_sakit_id', $rs->id)
            ->aktif()
            ->orderByDesc('popup')
            ->orderByDesc('created_at')
            ->get();

        return view('rumah_sakit.pages.promo', [
            'promos'  => $promos,
            'rsSlug'  => $rs->slug,
        ]);
    }
}
```

### Design View `pages/promo.blade.php` — Magazine Layout

```
[x-page-hero title="Promo & Penawaran" icon="local_offer"]

Jika kosong → empty state dengan ikon

Jika 1 promo → full-width hero card

Jika 2 promo → dua kolom setara

Jika 3+ promo:
┌────────────────────────────┬───────────────┐
│  FEATURED — promo pertama  │  promo ke-2   │
│  (gambar penuh + overlay)  │  (card kecil) │
│  tinggi ~400px             ├───────────────┤
│                            │  promo ke-3   │
└────────────────────────────┴───────────────┘
[Grid 3 kolom untuk sisa promo (ke-4 dst)]
```

**Karakteristik card (berbeda dari halaman lain):**
- Tidak ada background putih + border — seluruh card adalah **gambar** dengan gradient overlay
- Judul teks tampil **di atas gambar** (bottom overlay gelap)
- Badge "★ Unggulan" (kuning) di pojok kiri atas jika `popup = true`
- Hover: gambar zoom in (scale-110) + overlay lebih gelap + ikon arrow muncul dari bawah
- Rounded corners besar (`rounded-2xl` atau `rounded-3xl`)
- Jika tidak ada gambar: background gradient primer + ikon `local_offer` besar di tengah

---

## 7. Livewire `Pages\PromoDetail`

**File**: `app/Livewire/Pages/PromoDetail.php`

```php
namespace App\Livewire\Pages;

use App\Models\Promo;
use App\Models\RumahSakit;
use Livewire\Component;

class PromoDetail extends Component
{
    public Promo $promo;
    public $promoLainnya;

    public function mount(Promo $promo): void
    {
        $rs = current_rumahsakit();
        if ($promo->rumah_sakit_id !== $rs->id || !$promo->aktif) {
            abort(404);
        }
        $this->promo = $promo;

        // 3 promo lain selain yang sedang dibuka
        $this->promoLainnya = Promo::where('rumah_sakit_id', $rs->id)
            ->aktif()
            ->where('id', '!=', $promo->id)
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();
    }

    public function render()
    {
        return view('rumah_sakit.pages.promo-detail', [
            'promo'        => $this->promo,
            'promoLainnya' => $this->promoLainnya,
            'rsSlug'       => current_rumahsakit()->slug,
        ]);
    }
}
```

### Design View `pages/promo-detail.blade.php`

```
[Hero gambar full-width, h-[65vh], posisi: relative overflow-hidden]
  - Gambar sebagai bg cover
  - Gradient overlay dari bawah (from-black/70 to-transparent dari bawah ke atas)
  - Di kiri bawah overlay: badge "Promo" + judul besar (text-4xl, bold, putih)

[Konten card "mengambang" — -mt-16 dari hero]
  - max-w-3xl mx-auto bg-white rounded-3xl shadow-2xl p-8 md:p-12
  - Isi: deskripsi lengkap (render HTML aman dengan sanitizeHtml)
  - Tombol "← Kembali ke Promo" di bagian bawah konten

[Section "Promo Lainnya" — jika ada]
  - Heading section + horizontal scroll cards (3 card)
  - Card style sama dengan list promo (gambar + overlay)
```

---

## 8. Update `issue.md`

Tambahkan entry baru di `issues/issue.md`:

```markdown
- [ ] **Sistem Promo — Popup, Nav, List & Detail** (planning di `issues/promo-fitur.md`)
  - [ ] Tambah route `promo` dan `promo/{promo}` di `web.php`
  - [ ] Update `RumahSakitMiddleware` — share `$promo_popup` ke semua view
  - [ ] Tambah popup Alpine.js di `layout.blade.php`
  - [ ] Tambah `@stack('scripts')` di `layout.blade.php` jika belum ada
  - [ ] Tambah tombol floating di `index.blade.php` (homepage)
  - [ ] Tambah link "Promo" pill di `nav.blade.php` (selalu tampil, tidak collapse)
  - [ ] Buat `app/Livewire/Pages/Promo.php`
  - [ ] Buat `resources/views/rumah_sakit/pages/promo.blade.php` (magazine layout)
  - [ ] Buat `app/Livewire/Pages/PromoDetail.php`
  - [ ] Buat `resources/views/rumah_sakit/pages/promo-detail.blade.php`
```

---

## Ringkasan File yang Dibuat / Dimodifikasi

| File | Aksi |
|---|---|
| `routes/web.php` | Tambah 2 route |
| `app/Http/Middleware/RumahSakitMiddleware.php` | Share `$promo_popup` ke views |
| `resources/views/rumah_sakit/layout.blade.php` | Tambah popup + `@stack('scripts')` |
| `resources/views/rumah_sakit/index.blade.php` | Tambah tombol floating |
| `resources/views/rumah_sakit/nav.blade.php` | Tambah link promo pill |
| `app/Livewire/Pages/Promo.php` | Buat baru |
| `resources/views/rumah_sakit/pages/promo.blade.php` | Buat baru (magazine layout) |
| `app/Livewire/Pages/PromoDetail.php` | Buat baru |
| `resources/views/rumah_sakit/pages/promo-detail.blade.php` | Buat baru |
