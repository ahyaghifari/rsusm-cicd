# Panduan Integrasi API Promo — Tim Mobile

Dokumen ini untuk tim/aplikasi yang akan **memanggil** API promo (konsumen API), bukan dokumentasi endpoint teknis. Untuk detail field respons lihat [API-Promo.md](API-Promo.md).

## Ringkasan

| | |
|---|---|
| Base URL | `https://{domain-project}/api` |
| Endpoint | `GET /promo` |
| Autentikasi | Tidak ada (public) |
| Rate limit | 300 request/menit per IP (limiter `mobile-api`) |
| Format | JSON |

Endpoint ini mengembalikan **semua promo aktif dari semua rumah sakit** dalam satu list — tidak perlu filter per rumah sakit, tidak ada pagination.

## Request

```
GET https://{domain-project}/api/promo
```

Tidak butuh header khusus (tidak ada `Authorization`, tidak ada API key).

Contoh `curl`:

```bash
curl https://{domain-project}/api/promo
```

Contoh Flutter/Dart (`http` package):

```dart
final res = await http.get(Uri.parse('https://{domain-project}/api/promo'));
if (res.statusCode == 200) {
  final data = jsonDecode(res.body)['data'] as List;
}
```

## Response

Selalu HTTP 200 dengan body:

```json
{
  "data": [
    {
      "judul": "Promo Medical Check Up",
      "gambar": "https://{domain-project}/storage/promo/mcu.jpg",
      "deskripsi": "<p>Diskon 20% untuk paket MCU selama November.</p>",
      "rumah_sakit": "RSU Syifa Medika"
    }
  ]
}
```

Kalau tidak ada promo aktif, `data` berupa array kosong `[]` — bukan error, jadi UI harus bisa menampilkan state kosong.

### Catatan field

- **`gambar`** — URL absolut siap pakai langsung di `Image.network()` / `<img>`. Bisa `null` kalau admin belum upload gambar — UI harus handle null (tampilkan placeholder).
- **`deskripsi`** — berisi **HTML** (hasil rich text editor admin), bukan plain text. Di mobile, render pakai widget HTML (mis. `flutter_html`), jangan ditampilkan mentah sebagai teks biasa karena akan muncul tag `<p>`, `<strong>`, dll.
- **`rumah_sakit`** — nama rumah sakit pemilik promo, untuk ditampilkan sebagai label/badge kalau app menampilkan promo dari banyak RS sekaligus.
- Urutan `data` dari yang **terbaru dibuat** ke yang terlama. Tidak ada field tanggal publish, jadi urutan ini yang jadi acuan "terbaru".

## Error handling

| Kondisi | HTTP | Body | Yang harus dilakukan app |
|---|---|---|---|
| Normal, promo kosong | 200 | `{"data": []}` | Tampilkan empty state |
| Melebihi rate limit | 429 | — | Tunggu, retry dengan backoff (jangan retry langsung) |
| Server error | 5xx | — | Tampilkan error umum, boleh retry manual |

## Caching & polling

- Data promo tidak sering berubah — disarankan **cache di app** (misal 15–30 menit) daripada fetch tiap buka halaman.
- Jangan polling terus-menerus; 300 request/menit per IP cukup longgar untuk pemakaian normal (buka app, refresh manual), tapi auto-refresh tiap beberapa detik tetap tidak disarankan.

## Kontak kalau ada masalah

Kalau field respons berubah / butuh field tambahan (misal perlu `tanggal_mulai`/`tanggal_selesai` promo), koordinasi ke tim backend sebelum endpoint diubah — perubahan field bisa breaking buat app yang sudah rilis.
