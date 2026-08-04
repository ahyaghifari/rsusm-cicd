# Fitur: Global Search — Spotlight Modal dengan MySQL FULLTEXT

**Dibuat:** 2026-06-02  
**Status:** Done  
**Prioritas:** Medium  
**Label:** feature, search, livewire

---

## Latar Belakang

Nav sudah punya tombol search (ikon kaca pembesar) tapi belum fungsional. User perlu bisa mencari dokter, poliklinik, promo, dan FAQ langsung dari mana saja tanpa harus pindah halaman.

Pendekatan: **MySQL FULLTEXT index** + **Livewire Spotlight Modal** — tanpa service tambahan, data tetap di MySQL.

---

## Cara Kerja

### 1. FULLTEXT Index di Database

MySQL FULLTEXT adalah tipe index khusus untuk pencarian teks. Berbeda dengan index biasa yang hanya cocok untuk nilai exact atau prefix, FULLTEXT memecah teks menjadi token kata-kata dan mengindeksnya.

```
Query: "ahmad jantung"
FULLTEXT tokenize → ["ahmad", "jantung"]
Cocokkan ke index → baris yang mengandung kata-kata tersebut
Return dengan relevance score
```

**Kelebihan vs LIKE:**
- `LIKE '%ahmad%'` → full table scan, tidak pakai index
- `MATCH(nama) AGAINST('ahmad')` → pakai FULLTEXT index, jauh lebih cepat

**Catatan MySQL:** Minimum panjang token default adalah **3 karakter** (`innodb_ft_min_token_size=3`). Query di bawah 3 karakter diabaikan MySQL. Solusi: tampilkan pesan "ketik minimal 3 huruf" di UI.

### 2. Alur Request

```
User ketik di search modal
        ↓
Livewire debounce 350ms (tidak query tiap ketukan)
        ↓
GlobalSearch::updatedQuery()
        ↓  
Query FULLTEXT ke 5 tabel sekaligus (semua di-scope ke RS aktif)
        ↓
Return hasil dikelompokkan per kategori (max 5 per grup)
        ↓
Livewire re-render hasil tanpa full page reload
```

### 3. Cara Membuka Modal

- Klik tombol search di nav (yang sudah ada)
- Keyboard shortcut `Ctrl+K` / `Cmd+K`
- Tekan `Esc` untuk tutup

---

## Yang Di-search

| Tabel | Kolom FULLTEXT | Kolom yang Ditampilkan | Route Tujuan |
|---|---|---|---|
| `dokter` | `nama` | nama, spesialis.nama, foto | `rumahsakit.dokter_show` |
| `poliklinik` | `nama`, `deskripsi` | nama, unitLayanan.nama, gambar | `rumahsakit.rawat_jalan_show` |
| `promo` | `judul`, `deskripsi` | judul, gambar | `rumahsakit.promo_detail` |
| `faq` | `judul`, `deskripsi` | judul | `rumahsakit.faq` |
| `halaman` | `judul` | judul | `rumahsakit.halaman_statis` |

Semua query di-scope ke `rumah_sakit_id` dari RS aktif. Data tidak bisa bocor antar RS.

---

## Tampilan Hasil (Wireframe)

```
┌─────────────────────────────────────────────┐
│  🔍  Cari dokter, poliklinik, promo...  [✕] │
├─────────────────────────────────────────────┤
│                                             │
│  DOKTER                                     │
│  ┌─────────────────────────────────────┐   │
│  │ 👤  dr. Ahmad Fauzi, Sp.JP          │   │
│  │     Spesialis Jantung               │   │
│  └─────────────────────────────────────┘   │
│  ┌─────────────────────────────────────┐   │
│  │ 👤  dr. Siti Rahayu, Sp.OG         │   │
│  │     Spesialis Kandungan             │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  POLIKLINIK                                 │
│  ┌─────────────────────────────────────┐   │
│  │ 🏥  Poli Jantung & Pembuluh Darah   │   │
│  │     Rawat Jalan                     │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  FAQ                                        │
│  ┌─────────────────────────────────────┐   │
│  │ ❓  Cara mendaftar via BPJS         │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  Ketik minimal 3 huruf untuk mencari        │
└─────────────────────────────────────────────┘
```

---

## Rencana Implementasi

### Fase 1 — Database

**File baru:** `database/migrations/XXXX_add_fulltext_indexes_for_search.php`

Tambahkan FULLTEXT index ke 5 tabel:

```php
// dokter — cari berdasarkan nama
DB::statement('ALTER TABLE dokter ADD FULLTEXT INDEX ft_dokter_nama (nama)');

// poliklinik — cari nama dan deskripsi
DB::statement('ALTER TABLE poliklinik ADD FULLTEXT INDEX ft_poliklinik_search (nama, deskripsi)');

// promo — cari judul dan deskripsi (hanya yang aktif)
DB::statement('ALTER TABLE promo ADD FULLTEXT INDEX ft_promo_search (judul, deskripsi)');

// faq — cari judul dan deskripsi
DB::statement('ALTER TABLE faq ADD FULLTEXT INDEX ft_faq_search (judul, deskripsi)');

// halaman — cari judul saja (konten HTML terlalu noise)
DB::statement('ALTER TABLE halaman ADD FULLTEXT INDEX ft_halaman_judul (judul)');
```

> Catatan: `deskripsi` di Faq mengacu kolom `deskripsi` (isi jawaban FAQ). Verifikasi nama kolom di migration sebelum eksekusi.

---

### Fase 2 — Livewire Component

**File baru:** `app/Livewire/GlobalSearch.php`

```php
class GlobalSearch extends Component
{
    #[Locked]
    public int $rumahSakitId;

    public string $query = '';
    public bool $isOpen  = false;

    public function mount(): void
    {
        $this->rumahSakitId = current_rumahsakit()->id;
    }

    public function open(): void  { $this->isOpen = true; }
    public function close(): void { $this->isOpen = false; $this->query = ''; }

    public function updatedQuery(): void
    {
        // reset — hasil dihitung di render()
    }

    public function render(): View
    {
        $results = ['dokter' => [], 'poliklinik' => [], 'promo' => [], 'faq' => [], 'halaman' => []];

        if (mb_strlen(trim($this->query)) >= 3) {
            $q   = $this->query;
            $rsId = $this->rumahSakitId;

            $results['dokter'] = Dokter::whereFullText('nama', $q)
                ->where('rumah_sakit_id', $rsId)
                ->where('aktif', true)
                ->with('spesialis')
                ->limit(5)
                ->get(['id', 'nama', 'slug', 'foto', 'spesialis_id']);

            $results['poliklinik'] = PoliKlinik::whereFullText(['nama', 'deskripsi'], $q)
                ->where('aktif', true)
                ->whereHas('unitLayanan', fn ($u) => $u->where('rumah_sakit_id', $rsId))
                ->with('unitLayanan')
                ->limit(5)
                ->get(['id', 'nama', 'slug', 'gambar', 'unit_layanan_id']);

            $results['promo'] = Promo::whereFullText(['judul', 'deskripsi'], $q)
                ->where('rumah_sakit_id', $rsId)
                ->aktif()
                ->limit(5)
                ->get(['id', 'judul', 'slug', 'gambar']);

            $results['faq'] = Faq::whereFullText(['judul', 'deskripsi'], $q)
                ->where('rumah_sakit_id', $rsId)
                ->aktif()
                ->limit(5)
                ->get(['id', 'judul']);

            $results['halaman'] = Halaman::whereFullText('judul', $q)
                ->where('rumah_sakit_id', $rsId)
                ->where('aktif', true)
                ->limit(5)
                ->get(['id', 'judul', 'slug']);
        }

        return view('livewire.global-search', ['results' => $results]);
    }
}
```

---

### Fase 3 — View Modal

**File baru:** `resources/views/livewire/global-search.blade.php`

- Overlay gelap transparan di belakang modal (`bg-black/50`)
- Modal putih di tengah layar, max-width `xl`
- Input search dengan ikon di atas
- Hasil dikelompokkan per kategori dengan header label
- Tiap item: ikon kategori + nama + subtitle (spesialis / unit layanan / dll)
- Kondisi kosong: tampilkan "Tidak ditemukan" jika query ≥ 3 huruf tapi hasil kosong
- Kondisi awal: tampilkan hint "Ketik minimal 3 huruf"
- Klik item → `wire:navigate` ke halaman tujuan + tutup modal

---

### Fase 4 — Integrasi Nav & Layout

**Edit:** `resources/views/layouts/rumah_sakit.blade.php`
- Tambahkan `<livewire:global-search />` sekali di layout (bukan per halaman)

**Edit:** `resources/views/rumah_sakit/nav.blade.php`
- Tombol search yang sudah ada: tambahkan `wire:click="$dispatch('open-search')"` atau event Livewire
- Tambahkan listener keyboard `Ctrl+K` via Alpine.js / vanilla JS

---

## File yang Perlu Dibuat / Diubah

| File | Aksi |
|---|---|
| `database/migrations/XXXX_add_fulltext_indexes_for_search.php` | Buat baru |
| `app/Livewire/GlobalSearch.php` | Buat baru |
| `resources/views/livewire/global-search.blade.php` | Buat baru |
| `resources/views/layouts/rumah_sakit.blade.php` | Edit — tambah komponen |
| `resources/views/rumah_sakit/nav.blade.php` | Edit — wire tombol search |

---

## Hal yang Perlu Diperhatikan

- **Minimum 3 karakter:** MySQL FULLTEXT mengabaikan token < 3 huruf. Handle di UI dengan validasi `mb_strlen >= 3`.
- **Kolom nullable:** `deskripsi` di PoliKlinik bisa null — FULLTEXT tetap aman, hanya tidak akan match.
- **`whereFullText` di SQLite:** Tidak didukung. Di local dev dengan SQLite, query akan error. Karena project ini pakai MySQL di local, aman. Tapi perlu fallback LIKE jika ada developer lain yang pakai SQLite.
- **Debounce:** Gunakan `wire:model.live.debounce.350ms` agar tidak query tiap ketukan.
- **`#[Locked]`** pada `$rumahSakitId` — sudah direncanakan, mencegah user ganti RS via manipulasi Livewire.
- **Konten HTML di `halaman.konten`:** Tidak di-index karena banyak tag HTML yang jadi noise di hasil search.

---

## Hasil yang Diharapkan

- User bisa tekan `Ctrl+K` dari halaman manapun → modal search muncul
- Ketik nama dokter / poliklinik / promo → hasil muncul real-time tanpa reload
- Hasil dikelompokkan rapi per kategori
- Klik hasil → langsung navigasi ke halaman yang tepat
- Tidak ada data RS lain yang bisa muncul
- Performa query cepat berkat FULLTEXT index (tidak full table scan)

---

## Acceptance Criteria

- [ ] Migration FULLTEXT berhasil dijalankan di MySQL
- [ ] Query 3+ karakter mengembalikan hasil relevan dari 5 kategori
- [ ] Query < 3 karakter tidak melakukan DB query (handled di component)
- [ ] Semua hasil ter-scope ke RS aktif — tidak ada data RS lain
- [ ] Modal bisa dibuka via tombol nav dan `Ctrl+K`
- [ ] Modal bisa ditutup via tombol ✕ dan `Esc`
- [ ] `$rumahSakitId` menggunakan `#[Locked]`
- [ ] Berfungsi normal di MySQL (dev & prod)
