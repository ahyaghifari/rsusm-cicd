# Security: Rate Limiting — Proteksi Public Routes & API

**Dibuat:** 2026-06-02  
**Status:** Done  
**Prioritas:** High  
**Label:** security

---

## Latar Belakang

Tidak ada rate limiting di seluruh public routes. Semua endpoint bisa dihit tanpa batas oleh siapapun.

| Endpoint | Risiko |
|---|---|
| `/cari-spesialis` | JSON API publik, bisa dienumerasi |
| `/{rs}/dokter-kami` | Query `LIKE` ke DB, bisa dihammer |
| Semua portal Livewire | Setiap action = round-trip ke server + DB query |
| `/admin/login` | Brute force tanpa throttle eksplisit |

---

## Perubahan yang Diperlukan

### 1. Daftarkan Named Rate Limiters di `AppServiceProvider`

```php
// app/Providers/AppServiceProvider.php — dalam boot()

RateLimiter::for('public-api', function (Request $request) {
    return Limit::perMinute(30)->by($request->ip());
});

RateLimiter::for('portal', function (Request $request) {
    return Limit::perMinute(120)->by($request->ip());
});
```

### 2. Terapkan ke `routes/web.php`

```php
Route::get('/cari-spesialis', ...)->middleware('throttle:public-api')->name('cari_spesialis');

Route::prefix('{rumahsakit}')
    ->middleware([RumahSakitMiddleware::class, 'throttle:portal'])
    ->group(...);
```

### 3. Custom 429 Response untuk Livewire (opsional)

Tambah handler di `bootstrap/app.php` agar response 429 tidak membingungkan user Livewire:

```php
$exceptions->render(function (ThrottleRequestsException $e, Request $request) {
    if ($request->header('X-Livewire')) {
        return response()->json(['message' => 'Terlalu banyak permintaan. Coba lagi sebentar.'], 429);
    }
});
```

---

## File yang Diubah

| File | Perubahan |
|---|---|
| `app/Providers/AppServiceProvider.php` | Tambah `RateLimiter::for(...)` di `boot()` |
| `routes/web.php` | Tambah middleware throttle |
| `bootstrap/app.php` | Handler exception 429 (opsional) |

---

## Catatan

- Rate limit berbasis IP — efektif hanya jika proxy trust sudah benar (lihat `security-proxy-trust.md`)
- Pastikan `CACHE_STORE` bukan `array` di production — gunakan `redis` atau `database`
- Angka 30 dan 120 per menit adalah titik awal, sesuaikan setelah monitoring

---

## Acceptance Criteria

- [ ] `/cari-spesialis` → HTTP 429 setelah >30 req/menit dari satu IP
- [ ] Semua `/{rs}/*` → HTTP 429 setelah >120 req/menit dari satu IP
- [ ] Response 429 pada Livewire tidak error aneh di UI
