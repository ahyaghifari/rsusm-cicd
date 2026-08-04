# Security: APP_DEBUG — Pastikan False di Production

**Dibuat:** 2026-06-02  
**Status:** Open  
**Prioritas:** Low (wajib sebelum go-live)  
**Label:** security, deployment

---

## Latar Belakang

`.env` saat ini:

```
APP_DEBUG=true
```

Jika `APP_DEBUG=true` aktif di production dan terjadi error, Laravel akan menampilkan:
- Full stack trace di browser
- Nilai environment variables
- Konfigurasi database, cache, mail
- Struktur direktori dan nama file

Ini memberikan informasi sangat berharga bagi attacker untuk reconnaissance.

---

## Yang Perlu Dilakukan

### Saat Deploy ke Production

```env
APP_DEBUG=false
APP_ENV=production
```

### Konfigurasi Error Logging yang Benar

Pastikan error tetap ter-log meski debug dimatikan. Di `config/logging.php`, pastikan channel production mengarah ke file atau external service (Bugsnag, Sentry, dll.):

```env
LOG_CHANNEL=stack
LOG_LEVEL=error   # hanya catat error ke atas, bukan debug/info
```

---

## Catatan

- `APP_DEBUG=true` boleh di environment local/development
- Ini bukan perubahan kode — murni konfigurasi environment production
- Cukup **checklist deployment**, tapi perlu didokumentasikan agar tidak terlupa

---

## Acceptance Criteria

- [ ] `APP_DEBUG=false` di `.env` production
- [ ] `APP_ENV=production` di `.env` production
- [ ] Error production ter-log ke file atau external service
- [ ] Halaman error production menampilkan pesan generik, bukan stack trace
