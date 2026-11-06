# API Jadwal Harian per Dokter

Jadwal harian satu tanggal untuk seluruh dokter di satu rumah sakit, dengan poli reguler dan executive digabung dan dikelompokkan per dokter. Satu dokter yang praktek di beberapa poli (atau di reguler dan executive sekaligus) muncul satu kali dengan beberapa baris `jadwal`.

## Endpoint

```
GET {base_url}/api/{rs}/jadwal-harian-dokter
```

| Parameter | Lokasi | Wajib | Keterangan |
|-----------|--------|-------|------------|
| `rs` | path | ya | Slug rumah sakit |
| `tanggal` | query | tidak | Format `Y-m-d`. Default hari ini |

Tidak ada autentikasi. Dibatasi rate limiter `public-api` (HTTP 429 jika terlewati).

## Contoh request

```
GET https://contoh.id/api/rsu-syifa-medika/jadwal-harian-dokter?tanggal=2026-11-10
```

## Contoh respons (200)

```json
{
  "tanggal": "2026-11-10",
  "rumah_sakit": "RSU Syifa Medika",
  "data": [
    {
      "kode": "AB12CD34",
      "nama": "dr. Contoh, Sp.A",
      "jadwal": [
        {
          "poliklinik": "Anak",
          "jenis": "REGULER",
          "jam_mulai": "08:00",
          "jam_selesai": "12:00",
          "status": "BUKA",
          "sesuai_perjanjian": false,
          "catatan": ""
        },
        {
          "poliklinik": "Anak Executive",
          "jenis": "EXECUTIVE",
          "jam_mulai": "16:00",
          "jam_selesai": "19:00",
          "status": "BUKA",
          "sesuai_perjanjian": false,
          "catatan": ""
        }
      ]
    }
  ]
}
```

## Field respons

| Field | Tipe | Keterangan |
|-------|------|------------|
| `tanggal` | string | Tanggal yang diminta (`Y-m-d`) |
| `rumah_sakit` | string | Nama rumah sakit |
| `data[]` | array | Satu elemen per dokter, diurutkan menurut nama |
| `data[].kode` | string \| null | Kode unik dokter (8 karakter). `null` jika jadwal hanya punya nama dokter tanpa relasi ke data dokter |
| `data[].nama` | string | Nama dokter |
| `data[].jadwal[]` | array | Jadwal dokter pada tanggal tersebut, diurutkan menurut jam mulai |
| `jadwal[].poliklinik` | string | Nama poliklinik |
| `jadwal[].jenis` | string | `REGULER` atau `EXECUTIVE` |
| `jadwal[].jam_mulai` | string \| null | Format `H:i` |
| `jadwal[].jam_selesai` | string | Format `H:i`, atau `Selesai` jika tidak ada jam selesai |
| `jadwal[].status` | string | Status layanan, mis. `BUKA`. Jika ada perubahan jadwal harian, nilai perubahan yang dipakai |
| `jadwal[].sesuai_perjanjian` | boolean | `true` jika praktek berdasarkan perjanjian |
| `jadwal[].catatan` | string | Catatan jadwal (catatan perubahan didahulukan) |

## Catatan

- Jam, status, dan catatan sudah memperhitungkan perubahan jadwal harian, sama seperti endpoint `jadwal-harian`.
- Dokter dikelompokkan menurut `dokter_id`; jika kosong, menurut nama dokter pada jadwal.
- Data berasal dari tabel `jadwal_harian`, yang diisi oleh command `jadwal:generate-harian`. Tanggal yang belum digenerate menghasilkan `data` kosong.

## Error

| HTTP | Penyebab |
|------|----------|
| 404 | Slug rumah sakit tidak ditemukan (`{"message": "Rumah sakit tidak ditemukan."}`) |
| 422 | Format `tanggal` bukan `Y-m-d` |
| 429 | Melewati batas rate limiter |

## Kode sumber

- `JadwalHarianController::perDokter` di `app/Http/Controllers/Api/JadwalHarianController.php`
- Rute: `routes/api.php`
