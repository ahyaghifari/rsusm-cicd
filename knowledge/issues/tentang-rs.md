# Planning: Tentang RS — Section Homepage "Kenapa Memilih Kami"

Berikut adalah instruksi teknis untuk mengimplementasikan konten dinamis section "Kenapa Memilih Kami" pada halaman beranda rumah sakit. Instruksi ini siap dikerjakan oleh programmer atau AI Model.

## Latar Belakang

Section "Kenapa Memilih Kami" di `resources/views/rumah_sakit/index.blade.php` saat ini menggunakan teks dan gambar yang hardcoded. Karena sistem ini multi-tenant (banyak RS), setiap RS perlu memiliki teks dan gambar yang berbeda. Solusinya adalah menyimpan konten ini langsung di tabel `rumah_sakit` sebagai kolom tambahan — tidak perlu tabel baru.

---

## 1. Migrasi Database

Buat file migrasi baru untuk menambahkan dua kolom ke tabel `rumah_sakit` yang sudah ada:

```
nama file: tambahkan_tentang_ke_rumah_sakit (gunakan format tanggal migrasi Laravel)
```

- `tentang_kami` (text, nullable) — paragraf deskripsi RS untuk section "Kenapa Memilih Kami"
- `gambar_tentang` (varchar 255, nullable) — path foto/gambar pendukung section tersebut (upload via Filament)

Gunakan `Schema::table()` (bukan `Schema::create()`).

---

## 2. Update Model `RumahSakit`

Tambahkan dua kolom baru ke array `$fillable` model `RumahSakit`:

```php
'tentang_kami',
'gambar_tentang',
```

---

## 3. Update Filament `RumahSakitResource`

Tambahkan dua field baru ke form `RumahSakitResource`, dalam tab atau section tersendiri bernama **"Tentang RS"**, ditempatkan setelah field-field utama yang sudah ada.

### Field yang ditambahkan:

1. **`gambar_tentang`** — gunakan `FileUpload::make('gambar_tentang')->image()->label('Gambar Tentang RS')->nullable()`
2. **`tentang_kami`** — gunakan `RichEditor::make('tentang_kami')->label('Tentang / Kenapa Memilih Kami')->nullable()`

---

## 4. Update View `index.blade.php`

Lokasi section yang diubah: `resources/views/rumah_sakit/index.blade.php`, section dengan komentar `<!-- kenapa memilih RSU -->`.

### Perubahan:

1. Bungkus seluruh section dengan kondisi `@if($rs->tentang_kami)` sehingga section tidak muncul jika konten belum diisi.
2. Ganti teks hardcoded dengan `{!! str($rs->tentang_kami)->sanitizeHtml() !!}`.
3. Ganti gambar hardcoded `asset('img/syifa-medika.webp')` dengan:
   - Jika `$rs->gambar_tentang` ada → `Storage::url($rs->gambar_tentang)`
   - Jika tidak ada → tampilkan `asset('img/syifa-medika.webp')` sebagai fallback

### Contoh struktur view setelah diubah:

```blade
@if($rs->tentang_kami)
<section class="flex flex-col lg:grid lg:grid-cols-2 mt-24">
    <div>
        <img src="{{ $rs->gambar_tentang ? Storage::url($rs->gambar_tentang) : asset('img/syifa-medika.webp') }}"
             class="w-full h-full object-cover" alt="">
    </div>
    <div class="p-6 relative">
        ...
        {!! str($rs->tentang_kami)->sanitizeHtml() !!}
    </div>
</section>
@endif
```

> **Catatan**: Variabel `$rs` sudah tersedia di view melalui `current_rumahsakit()` yang di-share oleh `RumahSakitMiddleware`.
