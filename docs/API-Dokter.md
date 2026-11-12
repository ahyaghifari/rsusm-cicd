# API Dokter (Semua Rumah Sakit)

Menampilkan semua dokter aktif dari seluruh rumah sakit: nama, spesialis, dan rumah sakitnya.

## Endpoint

```
GET {base_url}/api/dokter
```

## Autentikasi

**HTTP Basic Auth** — wajib. Kredensial global (sama untuk semua RS), diset di `.env`:

```
DOKTER_API_USERNAME=
DOKTER_API_PASSWORD=
```

Contoh request dengan `curl`:

```bash
curl -u username:password {base_url}/api/dokter
```

Tanpa kredensial atau kredensial salah → HTTP `401 Unauthorized`.

Rate limiter `mobile-api` (300 request/menit per IP).

## Contoh respons (200)

```json
{
  "data": [
    {
      "nama": "dr. Ahmad Rezqi Fadhillah, Sp.PD",
      "spesialis": "Penyakit Dalam",
      "rumah_sakit": "RSU Syifa Medika"
    }
  ]
}
```

## Field respons

| Field | Tipe | Keterangan |
|-------|------|------------|
| `data[].nama` | string | Nama dokter |
| `data[].spesialis` | string \| null | Nama spesialis dokter |
| `data[].rumah_sakit` | string \| null | Nama rumah sakit tempat dokter praktik |

Hanya dokter dengan `aktif = true` yang ditampilkan, diurutkan berdasarkan nama.

## Kode sumber

- Controller: `app/Http/Controllers/Api/DokterController.php`
- Middleware Basic Auth: `app/Http/Middleware/BasicAuthApi.php` (alias rute: `dokter.basic`)
- Kredensial: `config/services.php` → `dokter_api`
- Rute: `routes/api.php`
