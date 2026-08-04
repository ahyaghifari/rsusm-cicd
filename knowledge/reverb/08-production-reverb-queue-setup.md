# Setup Production: Reverb & Queue Worker

**Tanggal:** 2026-06-09

> Catatan ini khusus untuk konfigurasi Supervisor agar `reverb:start` dan `queue:work` berjalan otomatis di production. Setup lainnya (Nginx, PHP-FPM, SSL, `npm run build`, `php artisan optimize`) sudah aktif.

---

## Yang perlu berjalan sebagai daemon

| Proses | Perintah | Fungsi |
|---|---|---|
| Reverb | `php artisan reverb:start` | WebSocket server — real-time chat, status sesi, toast notifikasi |
| Queue | `php artisan queue:work` | Memproses job — termasuk `SendWebPushNotification` (push saat browser tertutup) |

Keduanya harus selalu aktif dan otomatis restart jika crash → gunakan **Supervisor**.

---

## Konfigurasi Supervisor

Buat file `/etc/supervisor/conf.d/rsusm.conf`:

```ini
[program:rsusm-reverb]
command=php /var/www/rsusm-syifamedika/artisan reverb:start --host=0.0.0.0 --port=8080
directory=/var/www/rsusm-syifamedika
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/rsusm-reverb.log

[program:rsusm-queue]
command=php /var/www/rsusm-syifamedika/artisan queue:work --sleep=3 --tries=3 --timeout=60
directory=/var/www/rsusm-syifamedika
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/rsusm-queue.log
```

Sesuaikan `directory` dengan path project di server.

---

## Aktivasi setelah file config dibuat

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start rsusm-reverb
sudo supervisorctl start rsusm-queue
```

Cek status:

```bash
sudo supervisorctl status
```

Output yang diharapkan:

```
rsusm-queue    RUNNING   pid 1234, uptime 0:00:05
rsusm-reverb   RUNNING   pid 5678, uptime 0:00:05
```

---

## Nginx: proxy WebSocket ke Reverb

Tambahkan blok `location` ini ke dalam konfigurasi server block yang sudah ada:

```nginx
location /app {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_cache_bypass $http_upgrade;
}
```

---

## `.env` production untuk Reverb

```dotenv
REVERB_HOST=domain-kamu.com
REVERB_PORT=443
REVERB_SCHEME=https

VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Port 443 + scheme https karena browser mengakses Reverb lewat Nginx (yang sudah handle SSL) — bukan langsung ke port 8080.

---

## Setelah deploy ulang (update kode)

Queue worker perlu di-restart agar memuat kode terbaru — jika tidak, worker lama masih menjalankan kode lama:

```bash
sudo supervisorctl restart rsusm-queue
# Reverb tidak perlu restart kecuali ada perubahan konfigurasi broadcasting
```

Atau pakai artisan shortcut:

```bash
php artisan queue:restart
```

Perintah ini memberi sinyal ke worker yang sedang berjalan untuk berhenti dengan graceful setelah job yang sedang diproses selesai, lalu Supervisor otomatis menjalankan ulang.
