# Security: Input Validation — Endpoint `/cari-spesialis`

**Dibuat:** 2026-06-02  
**Status:** Done  
**Prioritas:** Medium  
**Label:** security

---

## Latar Belakang

Di `PortalController::spesialis()`:

```php
public function spesialis(Request $request)
{
    $rsSlug = $request->input('rs'); // tidak divalidasi sama sekali
    
    $daftarSpesialis = Spesialis::whereHas('rumahsakit',
        fn($q) => $q->where('slug', $rsSlug))
        ->whereHas('dokter')
        ->get();
    
    return response()->json($daftarSpesialis);
}
```

Masalah:
- Tidak ada validasi format atau panjang input `rs`
- Bisa menerima string sangat panjang (memory/CPU overhead)
- Jika slug tidak ditemukan, mengembalikan array kosong `[]` tanpa keterangan — bisa digunakan untuk enumerasi slug RS yang valid

---

## Perubahan yang Diperlukan

```php
public function spesialis(Request $request)
{
    $validated = $request->validate([
        'rs' => ['required', 'string', 'max:100', 'alpha_dash'],
    ]);

    $daftarSpesialis = Spesialis::whereHas('rumahsakit',
        fn($q) => $q->where('slug', $validated['rs'])->where('aktif', true))
        ->whereHas('dokter')
        ->get(['id', 'nama']); // select kolom minimal, jangan select *
    
    return response()->json($daftarSpesialis);
}
```

Perubahan:
1. `validate()` — format wajib `alpha_dash` (huruf, angka, strip, underscore), max 100 karakter
2. Tambah filter `->where('aktif', true)` agar slug RS tidak aktif tidak bisa dienumerasi
3. `get(['id', 'nama'])` — expose kolom minimal yang diperlukan Tom Select, bukan seluruh kolom

---

## File yang Diubah

| File | Perubahan |
|---|---|
| `app/Http/Controllers/PortalController.php` | Tambah validasi + select kolom + filter aktif |

---

## Acceptance Criteria

- [ ] Request tanpa parameter `rs` → HTTP 422
- [ ] Request dengan `rs` berisi karakter non-alphadash → HTTP 422
- [ ] Request dengan `rs` > 100 karakter → HTTP 422
- [ ] RS tidak aktif tidak muncul di response
- [ ] Response hanya berisi `id` dan `nama`, bukan seluruh kolom spesialis
