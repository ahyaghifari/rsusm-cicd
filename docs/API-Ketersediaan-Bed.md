# API Ketersediaan Bed Rawat Inap

Menampilkan ketersediaan bed rawat inap satu rumah sakit, dikelompokkan per kelas lalu per kamar. Data tidak disimpan di database sistem ini: setiap request mengambilnya langsung dari sistem Ranap, jadi hasilnya selalu data terkini.

## Endpoint

```
GET {base_url}/api/{rs}/ketersediaan-bed
```

| Parameter | Lokasi | Keterangan |
|-----------|--------|------------|
| `rs` | path | Slug rumah sakit (kolom `slug` di tabel `rumah_sakit`) |

Tidak ada query parameter dan tidak ada autentikasi. Endpoint dibatasi oleh rate limiter `public-api` (sama dengan API jadwal); melewati batas menghasilkan HTTP 429.

## Contoh request

```
GET https://contoh.id/api/rsu-syifa-medika/ketersediaan-bed
```

## Contoh respons (200)

```json
{
  "rumah_sakit": "RSU Syifa Medika",
  "diambil_pada": "2026-11-06T10:00:00+07:00",
  "ringkasan": {
    "kosong": 5,
    "reservasi": 1,
    "terisi": 12,
    "perbaikan": 0
  },
  "data": [
    {
      "kelas": "VIP",
      "ringkasan": {
        "kosong": 2,
        "reservasi": 0,
        "terisi": 3,
        "perbaikan": 0
      },
      "kamar": [
        {
          "nama_kamar": "Melati 1",
          "bed": [
            {
              "tempat_tidur": "A",
              "status": "Kosong",
              "keterangan": null,
              "tanggal": "2026-11-06 08:30:00"
            }
          ]
        }
      ]
    }
  ]
}
```

## Field respons

| Field | Tipe | Keterangan |
|-------|------|------------|
| `rumah_sakit` | string | Nama rumah sakit |
| `diambil_pada` | string | Waktu respons dibuat (ISO 8601) |
| `ringkasan` | object | Jumlah bed per status untuk seluruh rumah sakit |
| `data[]` | array | Satu elemen per kelas, diurutkan menurut nama kelas |
| `data[].kelas` | string | Nama kelas rawat inap |
| `data[].ringkasan` | object | Jumlah bed per status untuk kelas tersebut |
| `data[].kamar[]` | array | Kamar dalam kelas, diurutkan menurut nama kamar |
| `data[].kamar[].nama_kamar` | string | Nama kamar |
| `data[].kamar[].bed[]` | array | Bed dalam kamar, diurutkan menurut `tempat_tidur` |
| `bed[].tempat_tidur` | string | Identitas tempat tidur |
| `bed[].status` | string | Status bed (lihat tabel di bawah) |
| `bed[].keterangan` | string \| null | Catatan dari sistem Ranap |
| `bed[].tanggal` | string \| null | Waktu status terakhir berubah, apa adanya dari sistem Ranap |

### Status bed

| Nilai `status` | Kunci di `ringkasan` | Kode Ranap |
|----------------|----------------------|------------|
| Kosong | `kosong` | 1 |
| Reservasi | `reservasi` | 2 |
| Terisi | `terisi` | 3 |
| Sedang Perbaikan | `perbaikan` | 6 |
| Status Tidak Dikenal | (tidak dihitung) | selain di atas |

## Bed yang tidak ditampilkan

Aturannya sama dengan halaman publik Ketersediaan Rawat Inap:

- Bed berstatus `0` (nonaktif di Ranap).
- Bed dengan `idKelas` yang belum dipetakan ke Kelas Rawat Inap (kolom `id_kelas_api`).
- Bed milik kelas yang `public = false`.

## Error

| HTTP | Penyebab | Contoh body |
|------|----------|-------------|
| 404 | Slug rumah sakit tidak ditemukan | `{"message": "Rumah sakit tidak ditemukan."}` |
| 429 | Melewati batas rate limiter | - |
| 502 | Sistem Ranap tidak dapat dihubungi | `{"message": "Sistem Ranap tidak dapat dihubungi."}` |

## Konfigurasi

- `RANAP_API_BASE_URL` di `.env`: base URL sistem Ranap, tanpa kode RS dan tanpa trailing slash.
- `rumah_sakit.ranap_kode_api`: identifier RS di URL Ranap (mis. `rsa`). Sistem memanggil `{RANAP_API_BASE_URL}/{ranap_kode_api}/bed`.
- Jika salah satu kosong, endpoint membaca data contoh dari `storage/app/mock/ranap-ketersediaan.json`, jadi respons 200 belum tentu data asli.

## Kode sumber

- Controller: `app/Http/Controllers/Api/KetersediaanBedController.php`
- Client Ranap: `app/Services/RanapApiClient.php`
- Rute: `routes/api.php`
