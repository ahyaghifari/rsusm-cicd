# Security: Trust Proxies — Jangan Percaya Semua IP

**Dibuat:** 2026-06-02  
**Status:** Done (TRUSTED_PROXIES via env — set IP spesifik saat production)  
**Prioritas:** Medium-High  
**Label:** security

---

## Latar Belakang

Di `bootstrap/app.php` saat ini:

```php
$middleware->trustProxies(at: '*');
```

`'*'` berarti **semua IP dipercaya sebagai proxy**, termasuk attacker. Dampaknya:

- Attacker bisa spoof header `X-Forwarded-For` untuk memanipulasi IP yang terdeteksi Laravel
- Rate limiter berbasis IP menjadi **tidak efektif** — attacker tinggal ganti header tiap request
- Log access dan audit trail menampilkan IP palsu

---

## Perubahan yang Diperlukan

### Production (ada load balancer / Nginx proxy)

Ganti `'*'` dengan IP spesifik proxy:

```php
// bootstrap/app.php
$middleware->trustProxies(at: '203.0.113.10'); // IP load balancer
// atau CIDR range:
$middleware->trustProxies(at: '10.0.0.0/8');
```

### Development / Tanpa Proxy

Jika tidak ada proxy di depan server, hapus baris ini sama sekali atau set ke string kosong:

```php
$middleware->trustProxies(at: '');
```

### Via Environment Variable (disarankan)

Agar bisa beda config antara dev dan prod:

```php
$middleware->trustProxies(at: env('TRUSTED_PROXIES', ''));
```

Lalu di `.env` production:
```
TRUSTED_PROXIES=10.0.0.1
```

---

## File yang Diubah

| File | Perubahan |
|---|---|
| `bootstrap/app.php` | Ganti `'*'` dengan IP spesifik atau env variable |
| `.env` + `.env.example` | Tambah `TRUSTED_PROXIES` |

---

## Catatan

- Perubahan ini harus dikonfirmasi dengan konfigurasi hosting/server production
- Jika menggunakan Cloudflare, gunakan CIDR range Cloudflare resmi
- Wajib diselesaikan **sebelum** rate limiter aktif agar rate limit tidak bisa di-bypass

---

## Acceptance Criteria

- [ ] `trustProxies` tidak menggunakan `'*'`
- [ ] IP proxy dikonfigurasi via env variable
- [ ] Rate limiter tidak bisa di-bypass dengan spoof `X-Forwarded-For`
