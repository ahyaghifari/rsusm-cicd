# Fitur: Export PDF Jadwal Praktek

**Dibuat:** 2026-06-03  
**Status:** Done  
**Prioritas:** Medium  
**Label:** feature, export, pdf, filament

---

## Latar Belakang

Halaman `JadwalPraktekPage` (admin) sudah memiliki dua mode tampilan (Per Hari & Per Dokter) dengan filter RS, Unit Layanan, dan Dokter. Admin perlu bisa mengekspor jadwal yang sedang ditampilkan ke PDF — misalnya untuk dicetak dan ditempel di papan pengumuman RS.

---

## Prosedur Implementasi

### Step 1 — Install Library PDF

```bash
composer require barryvdh/laravel-dompdf
```

Pilihan `barryvdh/laravel-dompdf` karena:
- Paling populer di ekosistem Laravel
- Tidak butuh binary eksternal (tidak seperti wkhtmltopdf/snappy)
- Sudah mature dan aktif di-maintain

Alternatif yang tidak dipilih:
- `spatie/laravel-pdf` — lebih baru tapi sama basisnya (DomPDF)
- `wkhtmltopdf` — butuh binary install di server

---

### Step 2 — Buat Blade View PDF

**File baru:** `resources/views/pdf/jadwal-praktek.blade.php`

View ini akan merender tabel jadwal dalam format yang cocok untuk PDF:
- Header: nama RS, unit layanan (jika ada), tanggal generate
- Mode Per Hari: nama hari yang dipilih + tabel (Poliklinik | Dokter | Jam Mulai | Jam Selesai | Perjanjian | Catatan)
- Mode Per Dokter: nama dokter + tabel (Hari | Poliklinik | Jam Mulai | Jam Selesai | Perjanjian | Catatan)
- Footer: "Dicetak pada: {datetime}" + nama RS

CSS inline/embedded (DomPDF tidak support semua CSS modern — gunakan table-based layout sederhana dengan inline style).

---

### Step 3 — Tambah Method `exportPdf()` di `JadwalPraktekPage`

Method ini akan:
1. Validasi filter sudah lengkap (RS & unit dipilih jika perlu)
2. Query data jadwal menggunakan filter yang sama persis dengan yang sedang ditampilkan
3. Render Blade view PDF dengan data tersebut
4. Return `StreamedResponse` untuk download langsung

```php
public function exportPdf(): StreamedResponse
{
    $rsId   = $this->getActiveRumahSakitId();
    $rsName = RumahSakit::find($rsId)?->nama ?? '-';
    $unit   = $this->selectedUnitLayananId
        ? UnitLayanan::find($this->selectedUnitLayananId)?->nama
        : null;

    if ($this->viewMode === 'per_hari') {
        // Query sama dengan loadRows()
        $jadwals = JadwalPraktek::where('hari', $this->activeHari)
            ->whereHas('poliklinik.unitLayanan', function ($q) use ($rsId) {
                $q->where('rumah_sakit_id', $rsId);
                if ($this->selectedUnitLayananId) {
                    $q->where('id', $this->selectedUnitLayananId);
                }
            })
            ->orderBy('waktu_mulai')
            ->with('poliklinik', 'dokter')
            ->get();

        $filename = "jadwal-{$this->activeHari}-" . now()->format('Ymd') . ".pdf";
        $title    = "Jadwal Praktek — " . Hari::from($this->activeHari)->getLabel();

    } else {
        // Per dokter
        $dokter   = Dokter::find($this->selectedDokterId);
        $jadwals  = JadwalPraktek::where('dokter_id', $this->selectedDokterId)
            ->whereIn('poliklinik_id', $this->getPoliIds())
            ->with('poliklinik')
            ->get()
            ->sortBy(fn ($j) => array_search($j->hari->value, ['SENIN','SELASA','RABU','KAMIS','JUMAT','SABTU','MINGGU']));

        $filename = "jadwal-dokter-" . Str::slug($dokter?->nama ?? 'unknown') . "-" . now()->format('Ymd') . ".pdf";
        $title    = "Jadwal Praktek — " . ($dokter?->nama ?? '');
    }

    $pdf = Pdf::loadView('pdf.jadwal-praktek', [
        'jadwals'  => $jadwals,
        'title'    => $title,
        'rsName'   => $rsName,
        'unit'     => $unit,
        'viewMode' => $this->viewMode,
        'tanggal'  => now()->translatedFormat('d F Y H:i'),
    ])->setPaper('a4', 'landscape');

    return response()->streamDownload(
        fn () => print ($pdf->output()),
        $filename,
        ['Content-Type' => 'application/pdf']
    );
}
```

---

### Step 4 — Tambah Tombol Export di View

**File edit:** `resources/views/filament/resources/jadwal-praktek-resource/pages/jadwal-praktek-page.blade.php`

Tambahkan tombol di area header/action — sejajar dengan tombol "Layar Penuh":

```blade
{{-- Export PDF — hanya muncul jika filter sudah lengkap dan ada data --}}
@if($this->getActiveRumahSakitId() && !$this->mustPickUnit())
    @if($viewMode === 'per_hari' || ($viewMode === 'per_dokter' && $selectedDokterId))
        <a href="{{ route('filament.admin.resources.jadwal-prakteks.export-pdf', [...params...]) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium
                  bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 transition">
            <svg ...><!-- pdf icon --></svg>
            Export PDF
        </a>
    @endif
@endif
```

Atau lebih baik menggunakan Livewire action langsung:

```blade
<button wire:click="exportPdf"
        wire:loading.attr="disabled"
        wire:target="exportPdf"
        ...>
    Export PDF
</button>
```

---

### Step 5 — Register Route (jika pakai route terpisah)

Jika memilih pendekatan route GET (lebih clean untuk download):

```php
// routes/web.php atau di dalam Filament panel route
Route::get('/manage/jadwal-prakteks/export-pdf', ...)
    ->middleware(['auth', 'manage'])
    ->name('filament.admin.resources.jadwal-prakteks.export-pdf');
```

Namun pendekatan `wire:click="exportPdf"` dengan `StreamedResponse` lebih sederhana dan tidak butuh route tambahan.

---

## File yang Perlu Dibuat / Diubah

| File | Aksi |
|---|---|
| `composer.json` | Tambah `barryvdh/laravel-dompdf` |
| `resources/views/pdf/jadwal-praktek.blade.php` | **Buat baru** — template PDF |
| `app/Filament/Resources/JadwalPraktekResource/Pages/JadwalPraktekPage.php` | **Edit** — tambah `exportPdf()` method + import |
| `resources/views/filament/resources/jadwal-praktek-resource/pages/jadwal-praktek-page.blade.php` | **Edit** — tambah tombol Export PDF |

---

## Pertimbangan Teknis

### DomPDF dan CSS
DomPDF tidak mendukung semua CSS modern (Tailwind, Flexbox terbatas). View PDF harus menggunakan:
- Tabel HTML `<table>` dengan `border-collapse`
- `style="..."` inline
- Warna solid, tidak ada gradient/backdrop-blur
- Font: gunakan font yang di-embed atau fallback ke sans-serif

### StreamedResponse vs Route GET
| Pendekatan | Keunggulan | Kelemahan |
|---|---|---|
| `wire:click` + `StreamedResponse` | Tidak butuh route baru, state langsung dari Livewire | Livewire harus handle file response |
| Route GET dengan query param | URL shareable, lebih clean | Butuh re-query dan validasi ulang dari scratch |

**Rekomendasi:** `wire:click` + `StreamedResponse` karena state (filter aktif) sudah tersedia langsung di component.

### Keamanan
- `exportPdf()` harus memvalidasi `rumah_sakit_id` — admin tidak boleh export jadwal RS lain
- Validasi sudah ada via `getActiveRumahSakitId()` yang ter-scope per user

---

## Tampilan PDF yang Diharapkan

```
+--------------------------------------------------+
| [Logo RS]  JADWAL PRAKTEK — SENIN                |
| RSU Syifa Medika Banjarbaru                      |
| Unit: Poli Umum          Cetak: 03 Jun 2026      |
+--------------------------------------------------+
| No | Poliklinik     | Dokter        | Jam        |
|----|----------------|---------------|------------|
|  1 | Poli Jantung   | dr. Ahmad     | 08:00–12:00|
|  2 | Poli Kandungan | dr. Siti      | Perjanjian |
+--------------------------------------------------+
| * Jadwal dapat berubah sewaktu-waktu             |
+--------------------------------------------------+
```

---

## Acceptance Criteria

- [ ] `composer require barryvdh/laravel-dompdf` berhasil
- [ ] Tombol "Export PDF" muncul hanya saat filter sudah valid (ada RS + unit jika perlu + hari/dokter)
- [ ] Klik export → file PDF langsung terdownload
- [ ] PDF mode Per Hari: judul hari, tabel poliklinik-dokter-jam-perjanjian
- [ ] PDF mode Per Dokter: judul nama dokter, tabel hari-poliklinik-jam-perjanjian
- [ ] Header PDF mencantumkan nama RS, unit layanan, tanggal cetak
- [ ] Footer PDF mencantumkan disclaimer jadwal
- [ ] Admin RS tidak bisa export jadwal RS lain
- [ ] PDF bisa dibuka dan dicetak di browser/PDF viewer
