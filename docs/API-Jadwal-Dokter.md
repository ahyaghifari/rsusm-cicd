# API Jadwal Satu Dokter

Jadwal satu dokter yang dicari berdasarkan kodenya: jadwal pada tanggal tertentu (default hari ini) dan jadwal praktek rutin mingguan. Poli reguler dan executive digabung, dibedakan lewat field `jenis`.

## Endpoint

```
GET {base_url}/api/{rs}/dokter/{kode}/jadwal
```

| Parameter | Lokasi | Wajib | Keterangan |
|-----------|--------|-------|------------|
| `rs` | path | ya | Slug rumah sakit |
| `kode` | path | ya | Kode dokter (8 karakter, tidak peka huruf besar-kecil) |
| `tanggal` | query | tidak | Format `Y-m-d`. Default hari ini. Hanya memengaruhi `harian` |

Tidak ada autentikasi. Dibatasi rate limiter `public-api` (HTTP 429 jika terlewati).

Kode dokter bisa didapat dari field `kode` di endpoint jadwal lain, atau dari command `php artisan dokter:export-csv "<nama rs>"`.

## Contoh request

```
GET https://contoh.id/api/rsu-syifa-medika/dokter/AB12CD34/jadwal?tanggal=2026-11-10
```

## Contoh respons (200)

```json
{
  "rumah_sakit": "RSU Syifa Medika",
  "dokter": {
    "kode": "AB12CD34",
    "nama": "dr. Contoh, Sp.A"
  },
  "tanggal": "2026-11-10",
  "harian": [
    {
      "poliklinik": "Anak",
      "jenis": "REGULER",
      "jam_mulai": "08:00",
      "jam_selesai": "12:00",
      "status": "BUKA",
      "sesuai_perjanjian": false,
      "catatan": ""
    }
  ],
  "bulanan": [
    {
      "poliklinik": "Anak",
      "jenis": "REGULER",
      "hari": "SENIN",
      "jam_mulai": "08:00",
      "jam_selesai": "12:00",
      "sesuai_perjanjian": false,
      "catatan": ""
    },
    {
      "poliklinik": "Anak Executive",
      "jenis": "EXECUTIVE",
      "hari": "RABU",
      "jam_mulai": "16:00",
      "jam_selesai": "19:00",
      "sesuai_perjanjian": false,
      "catatan": ""
    }
  ]
}
```

## Field respons

| Field | Tipe | Keterangan |
|-------|------|------------|
| `rumah_sakit` | string | Nama rumah sakit |
| `dokter.kode` / `dokter.nama` | string | Identitas dokter |
| `tanggal` | string | Tanggal untuk `harian` (`Y-m-d`) |
| `harian[]` | array | Jadwal dokter pada `tanggal`, diurutkan menurut jam mulai. Kosong jika tidak praktek atau jadwal belum digenerate |
| `bulanan[]` | array | Jadwal praktek rutin mingguan, diurutkan menurut hari |

Field pada `harian[]`:

| Field | Tipe | Keterangan |
|-------|------|------------|
| `poliklinik` | string | Nama poliklinik |
| `jenis` | string | `REGULER` atau `EXECUTIVE` |
| `jam_mulai` | string \| null | Format `H:i` |
| `jam_selesai` | string | Format `H:i`, atau `Selesai` |
| `status` | string | Status layanan, mis. `BUKA`. Perubahan jadwal harian didahulukan |
| `sesuai_perjanjian` | boolean | `true` jika berdasarkan perjanjian |
| `catatan` | string | Catatan jadwal |

Field pada `bulanan[]` sama dengan `harian[]`, kecuali tidak ada `status` dan ada `hari` (`SENIN` sampai `MINGGU`).

## Catatan

- `bulanan` berasal dari jadwal praktek master (tabel `jadwal_praktek`), yaitu pola mingguan yang berulang, bukan jadwal per tanggal dalam satu bulan kalender.
- Jadwal yang hanya memiliki nama dokter tanpa relasi ke data dokter (`dokter_id` kosong) tidak muncul di endpoint ini.

## Error

| HTTP | Penyebab |
|------|----------|
| 404 | Slug rumah sakit tidak ditemukan, atau kode dokter tidak ada di rumah sakit tersebut (`{"message": "Dokter tidak ditemukan."}`) |
| 422 | Format `tanggal` bukan `Y-m-d` |
| 429 | Melewati batas rate limiter |

## Kode sumber

- `JadwalHarianController::dokter` di `app/Http/Controllers/Api/JadwalHarianController.php`
- Rute: `routes/api.php`
