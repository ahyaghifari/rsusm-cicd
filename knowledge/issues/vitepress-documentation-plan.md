# Plan Implementasi: Dokumentasi Project via VitePress (`/documentation`)

Dokumen ini adalah rencana implementasi untuk menyediakan situs dokumentasi developer
yang dibangun dengan [VitePress](https://vitepress.dev/), diakses lewat route
`/documentation` pada aplikasi Laravel, dan dilindungi oleh middleware `auth` yang
sudah ada (sama seperti pola `poster-preview` di [routes/web.php:9-16](../rsu-syifamedika/routes/web.php#L9-L16)).

**Keputusan yang sudah disepakati bersama user:**
- Route akhir: `/documentation` (single build, bukan per-RS)
- Proteksi akses: bungkus dengan sistem `auth` Laravel yang sudah ada (bukan auth terpisah)

---

## Mengapa pendekatan ini

VitePress adalah static site generator (Markdown → HTML statis pre-rendered, hydrated
jadi SPA Vue 3). Ia **tidak punya sistem auth bawaan** — hanya menghasilkan file statis.
Supaya proteksi `auth` Laravel benar-benar berfungsi, hasil build:

- **tidak boleh** ditaruh di `public/` — folder itu di-serve langsung oleh web server,
  sehingga middleware Laravel (termasuk `auth`) di-bypass sepenuhnya
- harus disajikan lewat **route + handler Laravel** yang membaca file dari lokasi di
  luar `public/` (mis. `storage/app/documentation`), supaya permintaan selalu melewati
  middleware `auth` lebih dulu

Source VitePress juga ditaruh di **folder terisolasi dengan `package.json` sendiri**
supaya tidak bentrok dengan setup Vite 6 + Tailwind v4 + `laravel-vite-plugin` yang
sudah dipakai untuk build aset utama Laravel ([package.json](../rsu-syifamedika/package.json)).

---

## Checklist Implementasi

- [ ] **1. Scaffold VitePress** — folder `docs/` terisolasi dengan `package.json` sendiri
- [ ] **2. Konfigurasi** — `.vitepress/config.ts` dengan `base: '/documentation/'`
- [ ] **3. Tulis konten awal** — halaman index + beberapa halaman dasar (arsitektur, setup, dll)
- [ ] **4. Build & tentukan lokasi output** — arahkan `outDir` ke folder di luar `public/`
- [ ] **5. Route + Handler Laravel** — `GET /documentation/{path?}` dengan `middleware('auth')`
- [ ] **6. Uji coba** — akses tanpa login (harus redirect ke halaman login), akses setelah login (harus tampil dokumentasi + asset termuat benar)
- [ ] **7. Dokumentasikan workflow build** — catat cara update & build ulang dokumentasi di README

---

## 1. Scaffold VitePress

**Lokasi:** `rsu-syifamedika/docs/` (folder baru, terpisah dari `resources/`)

```bash
cd rsu-syifamedika/docs
npm init -y
npm add -D vitepress
```

Struktur awal:
```
docs/
├── package.json          (terpisah, hanya berisi devDependency vitepress)
├── .vitepress/
│   └── config.ts
└── index.md
```

**Catatan:** folder `docs/node_modules` & hasil build perlu ditambahkan ke `.gitignore`
root maupun `rsu-syifamedika/.gitignore` (sesuaikan, jangan commit `node_modules`).

---

## 2. Konfigurasi `.vitepress/config.ts`

Poin penting:

```ts
import { defineConfig } from 'vitepress'

export default defineConfig({
  title: 'RSU Syifa Medika — Docs',
  description: 'Dokumentasi teknis project',
  base: '/documentation/',     // WAJIB cocok dengan prefix route Laravel
  outDir: '../../storage/app/documentation',  // build ke luar public/
  themeConfig: {
    nav: [...],
    sidebar: [...],
  },
})
```

- `base: '/documentation/'` — supaya semua URL asset hasil build (`/documentation/assets/...`)
  cocok dengan prefix route yang akan dibuat di Laravel
- `outDir` — diarahkan ke `storage/app/documentation` (di luar `public/`), sehingga
  **hanya bisa diakses lewat route Laravel** yang dilindungi `auth`

---

## 3. Konten Awal

Halaman dasar yang disarankan untuk permulaan (bisa dikembangkan bertahap):

- `index.md` — overview project, tech stack (rujuk [README.md](../README.md))
- `arsitektur.md` — model utama, multi-tenant, struktur URL
- `setup.md` — quick start / instalasi development
- `konvensi.md` — catatan kode (rujuk bagian "Catatan Kode" di README)

---

## 4. Build & Output

```bash
cd rsu-syifamedika/docs
npm run docs:build   # alias untuk `vitepress build`
```

Output akan masuk ke `storage/app/documentation/` sesuai `outDir`. Pastikan folder ini
**tidak** disymlink oleh `php artisan storage:link` (symlink itu menunjuk ke
`storage/app/public`, bukan `storage/app/documentation`, jadi aman secara default —
namun perlu diverifikasi saat implementasi).

---

## 5. Route + Handler Laravel

Pola mengikuti route `poster-preview` yang sudah ada
([routes/web.php:9-16](../rsu-syifamedika/routes/web.php#L9-L16)) — closure sederhana
dengan `middleware('auth')`, tanpa perlu controller baru jika logikanya ringkas:

```php
Route::get('/documentation/{path?}', function (?string $path = null) {
    $path = $path ?? 'index.html';
    $base = storage_path('app/documentation');
    $full = realpath($base . '/' . $path);

    // cegah path traversal — file harus berada di dalam $base
    abort_if(! $full || ! str_starts_with($full, realpath($base)), 404);

    // fallback ke index.html untuk route ala-SPA (mis. /documentation/arsitektur)
    if (is_dir($full)) {
        $full = $full . '/index.html';
    }
    if (! file_exists($full) && ! pathinfo($path, PATHINFO_EXTENSION)) {
        $full = $base . '/' . trim($path, '/') . '.html';
    }

    abort_if(! file_exists($full), 404);

    return response(file_get_contents($full))
        ->header('Content-Type', File::mimeType($full));
})->where('path', '.*')->middleware('auth')->name('documentation');
```

**Hal yang perlu dipastikan saat implementasi:**
- Validasi path traversal (`realpath` + `str_starts_with`) — wajib, karena `{path}`
  berasal dari input user
- MIME type benar untuk `.css`, `.js`, `.svg`, `.woff2`, dll — gunakan
  `Illuminate\Support\Facades\File::mimeType()` atau `mime_content_type()`
- Fallback `index.html` untuk clean URL VitePress (mis. `/documentation/arsitektur/`
  → `arsitektur/index.html`)
- Cache-Control header (opsional) — file statis aman di-cache cukup lama

---

## 6. Uji Coba

- [ ] Akses `/documentation` tanpa login → harus redirect ke halaman login (`auth` middleware aktif)
- [ ] Login lalu akses `/documentation` → halaman tampil, CSS/JS/asset termuat (cek Network tab, tidak ada 404)
- [ ] Navigasi antar halaman dokumentasi (client-side routing VitePress) berfungsi normal
- [ ] Cek header `Content-Type` benar untuk berbagai jenis asset

---

## 7. Workflow Update Dokumentasi

Tambahkan catatan singkat di README (`rsu-syifamedika/README.md`) tentang cara update:

```bash
# edit file .md di rsu-syifamedika/docs/
cd rsu-syifamedika/docs
npm run docs:build
# hasil otomatis ter-build ke storage/app/documentation — langsung live
```

Pertimbangkan juga apakah build perlu di-otomasi (mis. via composer script atau CI)
atau cukup manual oleh tim developer — ini bisa didiskusikan saat implementasi.

---

## Risiko & Hal yang Perlu Diperhatikan

- **Path traversal**: handler membaca file berdasarkan input URL — wajib validasi `realpath`
- **Versi Vite**: VitePress punya dependency Vite sendiri; folder terisolasi (`docs/`
  dengan `package.json` sendiri) mencegah konflik dengan Vite 6 di project utama
- **`.gitignore`**: pastikan `docs/node_modules` & (opsional) hasil build tidak ter-commit
- **Ukuran repo**: hasil build VitePress relatif kecil (HTML+JS+CSS statis), tapi tetap
  perlu dipantau jika konten dokumentasi berkembang besar
