# Security: HTTP Security Headers

**Dibuat:** 2026-06-02  
**Status:** Done  
**Prioritas:** Low-Medium  
**Label:** security

---

## Latar Belakang

Saat ini tidak ada HTTP security headers yang diset secara eksplisit. Semua header datang dari default Laravel/PHP, yang tidak mencakup header-header penting untuk proteksi browser.

---

## Header yang Perlu Ditambahkan

| Header | Nilai | Fungsi |
|---|---|---|
| `X-Content-Type-Options` | `nosniff` | Cegah browser menebak MIME type — proteksi MIME sniffing attack |
| `X-Frame-Options` | `SAMEORIGIN` | Cegah halaman diembed di iframe dari domain lain (clickjacking) |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Batasi info referrer yang dikirim ke situs lain |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` | Nonaktifkan fitur browser sensitif yang tidak dipakai |
| `X-XSS-Protection` | `0` | Nonaktifkan XSS auditor lama browser (sudah deprecated, lebih baik dimatikan) |

### Content-Security-Policy (CSP) — Fase Berikutnya

CSP adalah header paling kuat tapi paling kompleks karena harus mendaftarkan semua sumber konten yang diizinkan. Project ini menggunakan banyak CDN (Tom Select, AG Grid, Swiper, AOS, Material Symbols) sehingga CSP perlu direncanakan matang.

Rekomendasinya: terapkan 5 header di atas dulu, CSP di issue terpisah setelah semua CDN terdokumentasi.

---

## Implementasi: Custom Middleware

Buat middleware baru `app/Http/Middleware/SecurityHeaders.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        
        return $response;
    }
}
```

Daftarkan sebagai global middleware di `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->trustProxies(at: env('TRUSTED_PROXIES', ''));
    $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
})
```

---

## File yang Diubah

| File | Perubahan |
|---|---|
| `app/Http/Middleware/SecurityHeaders.php` | File baru — middleware security headers |
| `bootstrap/app.php` | Daftarkan middleware sebagai global |

---

## Catatan

- Middleware ini ringan, tidak ada query ke DB — aman sebagai global middleware
- `X-Frame-Options: SAMEORIGIN` memungkinkan embed di halaman yang sama domain (misal: admin & portal di domain yang sama)
- Jika ada kebutuhan embed dari domain lain di masa depan, ubah ke `ALLOW-FROM https://domain.com`

---

## Acceptance Criteria

- [ ] Setiap response (portal & admin) mengandung 5 header security
- [ ] Bisa diverifikasi via browser DevTools → Network → Response Headers
- [ ] Tidak ada fitur portal/admin yang rusak akibat header ini
