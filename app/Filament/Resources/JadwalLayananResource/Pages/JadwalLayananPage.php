<?php

namespace App\Filament\Resources\JadwalLayananResource\Pages;

use App\Enums\Hari;
use App\Filament\Resources\JadwalLayananResource;
use App\Models\Dokter;
use App\Models\JadwalLayanan;
use App\Models\PoliKlinik;
use App\Models\RumahSakit;
use App\Models\UnitLayanan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class JadwalLayananPage extends Page
{
    protected static string $resource = JadwalLayananResource::class;

    protected static string $view = 'filament.resources.jadwal-layanan-resource.pages.jadwal-layanan-page';

    // =========================================================================
    // STATE / PROPERTIES
    // =========================================================================

    // RS yang sedang aktif.
    // Admin RS: diisi otomatis di mount() dari data user login, tidak bisa diubah.
    // Super admin: diisi oleh Filament filterForm saat dropdown dipilih.
    public ?int $selectedRumahSakitId = null;

    // Filter unit layanan opsional. Null = tampilkan semua poli dari RS aktif.
    // Disembunyikan di UI jika RS hanya punya 1 unit layanan aktif.
    public ?int $selectedUnitLayananId = null;

    // Tab hari yang sedang aktif, default Senin saat halaman pertama dibuka.
    public string $activeHari = 'SENIN';

    // Baris tabel yang sedang ditampilkan (sesuai $activeHari).
    // Struktur tiap elemen: lihat addRow() di bawah.
    public array $rows = [];

    // Cache baris per hari agar tidak hilang saat pindah tab.
    // Key = nilai Hari enum (mis. 'SENIN'), value = array rows atau null (belum di-load).
    // null = belum pernah di-load dari DB untuk hari tersebut.
    public array $rowsCache = [];

    // =========================================================================
    // LIFECYCLE
    // =========================================================================

    public function mount(): void
    {
        // Inisialisasi cache untuk semua hari dengan null (belum di-load)
        foreach (Hari::cases() as $h) {
            $this->rowsCache[$h->value] = null;
        }

        if (! JadwalLayananResource::isSuperAdmin()) {
            // Admin RS: RS sudah diketahui dari data user login — kunci langsung.
            $this->selectedRumahSakitId = JadwalLayananResource::rumahSakitId();
            $this->loadRows();
        }
        // Super admin: tunggu pilihan dropdown di filterForm, rows tetap kosong.
    }

    // =========================================================================
    // FILAMENT FORM: Filter RS & Unit Layanan
    // Menggunakan statePath('') agar field form langsung terikat ke property komponen,
    // bukan ke array 'data'. Pola ini sama dengan JadwalPraktekDokter.
    // =========================================================================

    protected function getForms(): array
    {
        return ['filterForm'];
    }

    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([
                // Dropdown RS: hanya muncul untuk super admin
                Forms\Components\Select::make('selectedRumahSakitId')
                    ->label('Rumah Sakit')
                    ->placeholder('— Pilih Rumah Sakit —')
                    ->options(fn () => RumahSakit::orderBy('nama')->pluck('nama', 'id'))
                    ->required(fn () => JadwalLayananResource::isSuperAdmin())
                    ->visible(fn () => JadwalLayananResource::isSuperAdmin())
                    ->live(),

                // Dropdown Unit Layanan: muncul jika RS aktif punya lebih dari 1 unit
                Forms\Components\Select::make('selectedUnitLayananId')
                    ->label('Unit Layanan')
                    ->placeholder('— Semua Unit Layanan —')
                    ->options(fn () => $this->getUnitLayananOptions())
                    ->visible(fn () => count($this->getUnitLayananOptions()) > 1)
                    ->live(),
            ])
            ->statePath('') // State langsung ke property komponen, bukan ke $data
            ->columns(2);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function getActiveRumahSakitId(): ?int
    {
        return $this->selectedRumahSakitId;
    }

    // Kembalikan [id => nama] poliklinik aktif milik RS + unit layanan yang aktif.
    public function getPoliklinikOptions(): array
    {
        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId) return [];

        return PoliKlinik::whereHas('unitLayanan', function ($q) use ($rsId) {
                $q->where('rumah_sakit_id', $rsId);
                if ($this->selectedUnitLayananId) {
                    $q->where('id', $this->selectedUnitLayananId);
                }
            })
            ->where('aktif', true)
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->toArray();
    }

    // Kembalikan [id => nama] dokter aktif milik RS aktif.
    public function getDokterOptions(): array
    {
        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId) return [];

        return Dokter::where('rumah_sakit_id', $rsId)
            ->where('aktif', true)
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->toArray();
    }

    // Kembalikan [id => nama] unit layanan aktif milik RS aktif.
    // Digunakan filterForm untuk menentukan apakah dropdown unit perlu ditampilkan.
    public function getUnitLayananOptions(): array
    {
        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId) return [];

        return UnitLayanan::where('rumah_sakit_id', $rsId)
            ->where('aktif', true)
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->toArray();
    }

    // =========================================================================
    // DATA LOADING
    // =========================================================================

    // Muat data JadwalLayanan dari DB ke $rows dan simpan ke cache $activeHari.
    public function loadRows(): void
    {
        $rsId = $this->getActiveRumahSakitId();

        if (! $rsId) {
            $this->rows = [];
            return;
        }

        $jadwals = JadwalLayanan::where('hari', $this->activeHari)
            ->whereHas('poliklinik.unitLayanan', function ($q) use ($rsId) {
                $q->where('rumah_sakit_id', $rsId);
                if ($this->selectedUnitLayananId) {
                    $q->where('id', $this->selectedUnitLayananId);
                }
            })
            ->get();

        $this->rows = $jadwals->map(fn ($j) => [
            'poliklinik_id'  => $j->poliklinik_id,
            'dokter_id'      => $j->dokter_id,
            'nama_dokter'    => $j->nama_dokter,
            'jam_mulai'      => $j->jam_mulai?->format('H:i'),
            'jam_selesai'    => $j->jam_selesai?->format('H:i'),
            'status_layanan' => $j->status_layanan->value,
            'catatan'        => $j->catatan,
        ])->toArray();

        // Simpan hasil load ke cache hari ini
        $this->rowsCache[$this->activeHari] = $this->rows;
    }

    // =========================================================================
    // TAB HARI — Pergantian tab dengan preservasi cache
    // =========================================================================

    // Dipanggil dari blade saat tab hari diklik.
    // Menyimpan rows saat ini ke cache SEBELUM switch, lalu load dari cache atau DB.
    // Ini yang mencegah rows hilang saat pindah tab.
    public function setActiveHari(string $hari): void
    {
        // Simpan rows hari aktif saat ini ke cache sebelum berpindah
        $this->rowsCache[$this->activeHari] = $this->rows;

        // Pindah ke hari baru
        $this->activeHari = $hari;

        // Gunakan cache jika sudah ada (tidak null), hindari re-query DB yang sia-sia
        if ($this->rowsCache[$hari] !== null) {
            $this->rows = $this->rowsCache[$hari];
        } else {
            // Belum pernah di-load untuk hari ini → ambil dari DB
            $this->loadRows();
        }
    }

    // =========================================================================
    // LIVEWIRE HOOKS (updated*)
    // =========================================================================

    // Dipanggil oleh Filament filterForm saat super admin mengganti RS
    public function updatedSelectedRumahSakitId(): void
    {
        // Hapus semua cache karena RS berubah — data lama tidak relevan
        foreach (Hari::cases() as $h) {
            $this->rowsCache[$h->value] = null;
        }
        $this->selectedUnitLayananId = null;
        $this->rows = [];

        if ($this->selectedRumahSakitId) {
            $this->loadRows();
        }
    }

    // Dipanggil oleh Filament filterForm saat filter unit layanan berubah
    public function updatedSelectedUnitLayananId(): void
    {
        // Hapus semua cache karena filter unit berubah — semua hari perlu di-load ulang
        foreach (Hari::cases() as $h) {
            $this->rowsCache[$h->value] = null;
        }
        $this->loadRows();
    }

    // Dipanggil otomatis setiap kali nilai dalam array $rows berubah.
    // $key formatnya: "{index}.{field}", contoh: "0.dokter_id"
    public function updatedRows(mixed $value, string $key): void
    {
        // Hanya tangani kolom dokter_id untuk fitur auto-fill nama_dokter
        if (! str_ends_with($key, '.dokter_id')) {
            return;
        }

        $index = (int) explode('.', $key)[0];

        if ($value) {
            // Pilih dokter → isi nama_dokter otomatis dari tabel dokter
            $this->rows[$index]['nama_dokter'] = Dokter::find($value)?->nama;
        } else {
            $this->rows[$index]['nama_dokter'] = null;
        }
    }

    // =========================================================================
    // ROW MANAGEMENT
    // =========================================================================

    // Tambah 1 baris kosong ke bagian bawah tabel
    public function addRow(): void
    {
        $this->rows[] = [
            'poliklinik_id'  => null,
            'dokter_id'      => null,
            'nama_dokter'    => null,
            'jam_mulai'      => null,
            'jam_selesai'    => null,
            'status_layanan' => 'BUKA',
            'catatan'        => null,
        ];
    }

    // Hapus baris dan reindex agar wire:model binding tidak kacau akibat gap index
    public function removeRow(int $index): void
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
    }

    // =========================================================================
    // SAVE — Replace-All Logic
    // =========================================================================

    public function saveJadwal(): void
    {
        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId) {
            Notification::make()->title('Rumah sakit tidak teridentifikasi')->danger()->send();
            return;
        }

        // Langkah 1: Periksa baris kosong (poliklinik_id null).
        // Tidak diizinkan menyimpan jika masih ada baris yang belum diisi polikliniknya.
        foreach ($this->rows as $i => $row) {
            if (empty($row['poliklinik_id'])) {
                $nomor = $i + 1;
                Notification::make()
                    ->title("Baris ke-{$nomor} belum lengkap")
                    ->body('Pilih poliklinik atau hapus baris tersebut sebelum menyimpan.')
                    ->warning()
                    ->send();
                return;
            }
        }

        // Langkah 2: Validasi field wajib lain pada baris yang sudah ada polikliniknya
        foreach ($this->rows as $i => $row) {
            $nomor = $i + 1;

            if (empty($row['jam_mulai'])) {
                Notification::make()
                    ->title("Baris ke-{$nomor}: Jam Mulai wajib diisi")
                    ->danger()->send();
                return;
            }

            if (empty($row['status_layanan'])) {
                Notification::make()
                    ->title("Baris ke-{$nomor}: Status Layanan wajib diisi")
                    ->danger()->send();
                return;
            }
        }

        // Langkah 3: Kumpulkan ID poliklinik scope RS/unit untuk batas DELETE
        $poliIds = PoliKlinik::whereHas('unitLayanan', function ($q) use ($rsId) {
                $q->where('rumah_sakit_id', $rsId);
                if ($this->selectedUnitLayananId) {
                    $q->where('id', $this->selectedUnitLayananId);
                }
            })
            ->pluck('id')
            ->toArray();

        // Langkah 4: Replace-all dalam transaksi
        DB::transaction(function () use ($poliIds) {
            JadwalLayanan::where('hari', $this->activeHari)
                ->whereIn('poliklinik_id', $poliIds)
                ->delete();

            foreach ($this->rows as $row) {
                JadwalLayanan::create([
                    'poliklinik_id'  => $row['poliklinik_id'],
                    'hari'           => $this->activeHari,
                    'dokter_id'      => $row['dokter_id'] ?: null,
                    'nama_dokter'    => $row['nama_dokter'] ?: null,
                    'jam_mulai'      => $row['jam_mulai'],
                    'jam_selesai'    => $row['jam_selesai'] ?: null,
                    'status_layanan' => $row['status_layanan'],
                    'catatan'        => $row['catatan'] ?: null,
                ]);
            }
        });

        // Invalidasi cache hari ini lalu reload dari DB untuk sinkronisasi
        $this->rowsCache[$this->activeHari] = null;
        $this->loadRows();

        Notification::make()
            ->title('Jadwal ' . Hari::from($this->activeHari)->getLabel() . ' berhasil disimpan')
            ->success()
            ->send();
    }
}
