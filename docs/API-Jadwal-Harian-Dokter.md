# API Jadwal Harian per Dokter

Jadwal dokter pada satu tanggal, dengan poli reguler dan executive digabung dan dikelompokkan per dokter. Satu dokter yang praktek di beberapa poli (atau di reguler dan executive sekaligus) muncul satu kali dengan beberapa baris `jadwal`. Dengan parameter `kode`, hasilnya dibatasi satu dokter dan ditambah jadwal praktek rutin mingguan (`bulanan`).

## Endpoint

```
GET {base_url}/api/{rs}/jadwal-harian-dokter
```

| Parameter | Lokasi | Wajib | Keterangan |
|-----------|--------|-------|------------|
| `rs` | path | ya | Slug rumah sakit |
| `tanggal` | query | tidak | Format `Y-m-d`. Default hari ini |
| `kode` | query | tidak | Kode dokter (8 karakter, tidak peka huruf besar-kecil). Jika diisi, hanya dokter tersebut yang dikembalikan, beserta `bulanan` |

Tidak ada autentikasi. Dibatasi rate limiter `public-api` (HTTP 429 jika terlewati).

Kode dokter ada di field `kode` pada respons, atau dari command `php artisan dokter:export-csv "<nama rs>"`.

## Contoh request

Semua dokter pada satu tanggal:

```
GET https://contoh.id/api/rsu-syifa-medika/jadwal-harian-dokter?tanggal=2026-11-10
```

Satu dokter (hari ini, plus jadwal rutin mingguan):

```
GET https://contoh.id/api/rsu-syifa-medika/jadwal-harian-dokter?kode=AB12CD34
```

## Contoh respons tanpa `kode` (200)

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

## Contoh respons dengan `kode` (200)

Strukturnya sama, `data` berisi satu dokter, dan ada tambahan `bulanan`:

```json
{
  "tanggal": "2026-11-10",
  "rumah_sakit": "RSU Syifa Medika",
  "data": [
    {
      "kode": "AB12CD34",
      "nama": "dr. Contoh, Sp.A",
      "jadwal": [
        {"poliklinik": "Anak", "jenis": "REGULER", "jam_mulai": "08:00", "jam_selesai": "12:00",
         "status": "BUKA", "sesuai_perjanjian": false, "catatan": ""}
      ],
      "bulanan": [
        {"poliklinik": "Anak", "jenis": "REGULER", "hari": "SENIN", "jam_mulai": "08:00",
         "jam_selesai": "12:00", "sesuai_perjanjian": false, "catatan": ""},
        {"poliklinik": "Anak Executive", "jenis": "EXECUTIVE", "hari": "RABU", "jam_mulai": "16:00",
         "jam_selesai": "19:00", "sesuai_perjanjian": false, "catatan": ""}
      ]
    }
  ]
}
```

Jika dokter tidak praktek pada tanggal tersebut, ia tetap dikembalikan dengan `jadwal` kosong dan `bulanan` terisi.

## Field respons

| Field | Tipe | Keterangan |
|-------|------|------------|
| `tanggal` | string | Tanggal yang diminta (`Y-m-d`) |
| `rumah_sakit` | string | Nama rumah sakit |
| `data[]` | array | Satu elemen per dokter, diurutkan menurut nama |
| `data[].kode` | string \| null | Kode unik dokter. `null` jika jadwal hanya punya nama dokter tanpa relasi ke data dokter |
| `data[].nama` | string | Nama dokter |
| `data[].jadwal[]` | array | Jadwal pada `tanggal`, diurutkan menurut jam mulai |
| `data[].bulanan[]` | array | Hanya ada jika `kode` diisi. Jadwal praktek rutin mingguan, diurutkan menurut hari |

Field pada `jadwal[]`:

| Field | Tipe | Keterangan |
|-------|------|------------|
| `poliklinik` | string | Nama poliklinik |
| `jenis` | string | `REGULER` atau `EXECUTIVE` |
| `jam_mulai` | string \| null | Format `H:i` |
| `jam_selesai` | string | Format `H:i`, atau `Selesai` jika tidak ada jam selesai |
| `status` | string | Status layanan, mis. `BUKA`. Jika ada perubahan jadwal harian, nilai perubahan yang dipakai |
| `sesuai_perjanjian` | boolean | `true` jika praktek berdasarkan perjanjian |
| `catatan` | string | Catatan jadwal (catatan perubahan didahulukan) |

Field pada `bulanan[]` sama dengan `jadwal[]`, kecuali tidak ada `status` dan ada `hari` (`SENIN` sampai `MINGGU`).

## Catatan

- Jam, status, dan catatan `jadwal` sudah memperhitungkan perubahan jadwal harian, sama seperti endpoint `jadwal-harian`.
- Dokter dikelompokkan menurut `dokter_id`; jika kosong, menurut nama dokter pada jadwal. Jadwal tanpa `dokter_id` tidak bisa dicari lewat `kode`.
- `jadwal` berasal dari tabel `jadwal_harian`, yang diisi oleh command `jadwal:generate-harian`. Tanggal yang belum digenerate menghasilkan `jadwal` kosong.
- `bulanan` berasal dari jadwal praktek master (tabel `jadwal_praktek`), yaitu pola mingguan yang berulang, bukan jadwal per tanggal dalam satu bulan kalender.
- `catatan` pada `bulanan[]` berisi catatan jadwal master, lalu ditambah keterangan cuti jika dokter cuti pada hari tersebut dalam 30 hari ke depan, mis. `"Cuti 27-30 November 2026: Ibadah umroh"`. Beberapa keterangan dipisah dengan `; `. Hanya hari praktek yang jatuh di dalam rentang cuti yang ditandai. Aturan yang sama berlaku untuk endpoint `jadwal-bulanan` dan `jadwal-bulanan-executive`.

## Error

| HTTP | Penyebab |
|------|----------|
| 404 | Slug rumah sakit tidak ditemukan (`{"message": "Rumah sakit tidak ditemukan."}`), atau `kode` tidak ada di rumah sakit tersebut (`{"message": "Dokter tidak ditemukan."}`) |
| 422 | Format `tanggal` bukan `Y-m-d`, atau `kode` lebih dari 8 karakter |
| 429 | Melewati batas rate limiter |

## Kode sumber

- `JadwalHarianController::perDokter` di `app/Http/Controllers/Api/JadwalHarianController.php`
- Rute: `routes/api.php`
