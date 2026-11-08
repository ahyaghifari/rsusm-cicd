# API Promo (Semua Rumah Sakit)

Menampilkan semua promo aktif dari seluruh rumah sakit dalam satu daftar.

## Endpoint

```
GET {base_url}/api/promo
GET {base_url}/api/promo?limit=5
```

| Parameter | Lokasi | Keterangan |
|-----------|--------|------------|
| `limit` | query, opsional | Jumlah maksimal promo yang dikembalikan. Tanpa `limit`, semua promo aktif dikembalikan. Nilai dibatasi maksimal `100`. |

Tidak ada autentikasi. Rate limiter `mobile-api` (300 request/menit per IP) — lebih longgar dari API lain karena dipasang di aplikasi mobile.

## Contoh respons (200)

```json
{
  "data": [
    {
      "judul": "Promo Medical Check Up",
      "gambar": "https://contoh.id/storage/promo/mcu.jpg",
      "deskripsi": "<p>Diskon 20% untuk paket MCU selama November.</p>",
      "rumah_sakit": "RSU Syifa Medika"
    }
  ]
}
```

## Field respons

| Field | Tipe | Keterangan |
|-------|------|------------|
| `data[].judul` | string | Judul promo |
| `data[].gambar` | string \| null | URL penuh gambar promo |
| `data[].deskripsi` | string | Deskripsi promo, HTML (rich text) apa adanya dari admin |
| `data[].rumah_sakit` | string | Nama rumah sakit pemilik promo |

Hanya promo dengan `aktif = true` yang ditampilkan, diurutkan dari yang terbaru dibuat.

## Kode sumber

- Controller: `app/Http/Controllers/Api/PromoController.php`
- Model: `app/Models/Promo.php`
- Rute: `routes/api.php`
