# Security: Session & Cookie — Enkripsi dan Secure Flag

**Dibuat:** 2026-06-02  
**Status:** Done (SESSION_SECURE_COOKIE ditunda — set true saat production)  
**Prioritas:** Medium  
**Label:** security

---

## Latar Belakang

Konfigurasi session saat ini di `.env`:

```
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
# SESSION_SECURE_COOKIE tidak diset
```

Dua masalah:

### 1. Session Encryption Dinonaktifkan

`SESSION_ENCRYPT=false` berarti payload session di tabel `sessions` tersimpan dalam teks biasa. Jika ada kebocoran database (dump, backup yang tidak aman), isi session bisa dibaca langsung.

### 2. Secure Cookie Tidak Diset

Tanpa `SESSION_SECURE_COOKIE=true`, cookie session bisa dikirim lewat HTTP biasa (bukan HTTPS saja). Di environment production yang sudah HTTPS, ini membuka celah jika ada request HTTP yang tidak di-redirect.

---

## Perubahan yang Diperlukan

### `.env` Production

```env
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict   # upgrade dari lax ke strict jika tidak ada kebutuhan cross-site
```

### `.env.example`

Tambahkan placeholder agar tidak terlupa saat setup ulang:

```env
SESSION_ENCRYPT=false           # Set true di production
SESSION_SECURE_COOKIE=false     # Set true di production (HTTPS)
SESSION_SAME_SITE=lax
```

---

## Catatan

- `SESSION_ENCRYPT=true` tidak ada performance penalty signifikan — Laravel menggunakan enkripsi simetris
- `SESSION_SAME_SITE=strict` mencegah cookie dikirim pada request cross-site (termasuk dari link eksternal) — pastikan tidak ada kebutuhan OAuth/callback cross-site sebelum diaktifkan
- `SESSION_SECURE_COOKIE=true` hanya efektif jika server production sudah full HTTPS
- Dev lokal tidak perlu mengaktifkan ini (HTTP localhost)

---

## File yang Diubah

| File | Perubahan |
|---|---|
| `.env` (production) | Set `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true` |
| `.env.example` | Tambah dokumentasi kedua variabel |

---

## Acceptance Criteria

- [ ] `SESSION_ENCRYPT=true` di production
- [ ] `SESSION_SECURE_COOKIE=true` di production
- [ ] `.env.example` mendokumentasikan kedua variabel
- [ ] Login admin masih berfungsi normal setelah perubahan
