<?php

namespace App\Filament\Resources\JadwalLayananHarianResource\Pages;

use App\Enums\Hari;
use App\Filament\Resources\JadwalLayananHarianResource;
use App\Models\Dokter;
use App\Models\JadwalLayanan;
use App\Models\JadwalLayananHarian;
use App\Models\PoliKlinik;
use App\Models\RumahSakit;
use App\Models\UnitLayanan;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class JadwalLayananHarianPage extends Page
{
    protected static string $resource = JadwalLayananHarianResource::class;

    protected static string $view = 'filament.resources.jadwal-layanan-harian-resource.pages.jadwal-layanan-harian-page';

    protected static ?string $title = 'Jadwal Layanan Harian';

    // =========================================================================
    // STATE / PROPERTIES
    // =========================================================================

    // RS yang sedang aktif (pola sama dengan JadwalLayananPage)
    public ?int $selectedRumahSakitId = null;

    // Filter unit layanan opsional
    public ?int $selectedUnitLayananId = null;

    // Tanggal aktif yang sedang ditampilkan, format Y-m-d. Default: hari ini.
    public string $activeTanggal = '';

    // Baris tabel yang sedang ditampilkan untuk $activeTanggal.
    // Tiap elemen = 1 calon record JadwalLayananHarian.
    public array $rows = [];

    // Cache baris per tanggal agar tidak hilang saat pindah tanggal.
    // Key = tanggal (Y-m-d), value = array rows atau null (belum di-load).
    public array $rowsCache = [];

    // =========================================================================
    // PETA HARI: Carbon dayOfWeek → Hari enum value
    // Carbon: 0=Minggu, 1=Senin, 2=Selasa, 3=Rabu, 4=Kamis, 5=Jumat, 6=Sabtu
    // =========================================================================
    protected const DAY_MAP = [
        0 => 'MINGGU',
        1 => 'SENIN',
        2 => 'SELASA',
        3 => 'RABU',
        4 => 'KAMIS',
        5 => 'JUMAT',
        6 => 'SABTU',
    ];

    // =========================================================================
    // LIFECYCLE
    // =========================================================================

    public function mount(): void
    {
        // Default ke hari ini
        $this->activeTanggal = now()->format('Y-m-d');

        if (! JadwalLayananHarianResource::isSuperAdmin()) {
            // Admin RS: kunci langsung dari user login
            $this->selectedRumahSakitId = JadwalLayananHarianResource::rumahSakitId();
            $this->loadRows();
        }
        // Super admin: tunggu pilihan dropdown
    }

    // =========================================================================
    // FILAMENT FILTER FORM (pola sama dengan JadwalLayananPage)
    // =========================================================================

    protected function getForms(): array
    {
        return ['filterForm'];
    }

    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('selectedRumahSakitId')
                    ->label('Rumah Sakit')
                    ->placeholder('— Pilih Rumah Sakit —')
                    ->options(fn () => RumahSakit::orderBy('nama')->pluck('nama', 'id'))
                    ->required(fn () => JadwalLayananHarianResource::isSuperAdmin())
                    ->visible(fn () => JadwalLayananHarianResource::isSuperAdmin())
                    ->live(),

                Forms\Components\Select::make('selectedUnitLayananId')
                    ->label('Unit Layanan')
                    ->placeholder('— Semua Unit Layanan —')
                    ->options(fn () => $this->getUnitLayananOptions())
                    ->visible(fn () => count($this->getUnitLayananOptions()) > 1)
                    ->live(),
            ])
            ->statePath('') // State langsung ke property komponen
            ->columns(2);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function getActiveRumahSakitId(): ?int
    {
        return $this->selectedRumahSakitId;
    }

    // Kembalikan nama hari Indonesia dari tanggal aktif (misal: "Senin")
    public function getNamaHariAktif(): string
    {
        if (! $this->activeTanggal) return '';
        $hariValue = self::DAY_MAP[Carbon::parse($this->activeTanggal)->dayOfWeek];
        return Hari::from($hariValue)->getLabel();
    }

    // Kembalikan nilai Hari enum dari tanggal aktif (misal: "SENIN")
    protected function getHariDariTanggal(?string $tanggal = null): string
    {
        $tgl = $tanggal ?? $this->activeTanggal;
        return self::DAY_MAP[Carbon::parse($tgl)->dayOfWeek];
    }

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

    // Muat JadwalLayananHarian dari DB untuk $activeTanggal, lalu cache hasilnya.
    public function loadRows(): void
    {
        $rsId = $this->getActiveRumahSakitId();

        if (! $rsId || ! $this->activeTanggal) {
            $this->rows = [];
            return;
        }

        $jadwals = JadwalLayananHarian::where('tanggal', $this->activeTanggal)
            ->whereHas('poliklinik.unitLayanan', function ($q) use ($rsId) {
                $q->where('rumah_sakit_id', $rsId);
                if ($this->selectedUnitLayananId) {
                    $q->where('id', $this->selectedUnitLayananId);
                }
            })
            ->get();

        $this->rows = $jadwals->map(fn ($j) => [
            'poliklinik_id'     => $j->poliklinik_id,
            'jadwal_layanan_id' => $j->jadwal_layanan_id, // referensi ke jadwal mingguan (bisa null)
            'dokter_id'         => $j->dokter_id,
            'nama_dokter'       => $j->nama_dokter,
            'jam_mulai'         => $j->jam_mulai?->format('H:i'),
            'jam_selesai'       => $j->jam_selesai?->format('H:i'),
            'status_layanan'    => $j->status_layanan->value,
            'catatan'           => $j->catatan,
        ])->toArray();

        // Simpan ke cache tanggal ini
        $this->rowsCache[$this->activeTanggal] = $this->rows;
    }

    // =========================================================================
    // NAVIGASI TANGGAL
    // =========================================================================

    // Dipanggil dari blade (input date onChange) atau method prevDay/nextDay.
    // Menyimpan rows tanggal aktif ke cache sebelum switch, lalu load tanggal baru.
    public function setActiveTanggal(string $tanggal): void
    {
        if (! $tanggal) return;

        // Simpan rows tanggal saat ini ke cache sebelum berpindah
        $this->rowsCache[$this->activeTanggal] = $this->rows;

        // Pindah ke tanggal baru
        $this->activeTanggal = $tanggal;

        // Gunakan cache jika ada, hindari re-query DB
        if (isset($this->rowsCache[$tanggal]) && $this->rowsCache[$tanggal] !== null) {
            $this->rows = $this->rowsCache[$tanggal];
        } else {
            $this->loadRows();
        }
    }

    // Navigasi ke hari sebelumnya
    public function prevDay(): void
    {
        $this->setActiveTanggal(
            Carbon::parse($this->activeTanggal)->subDay()->format('Y-m-d')
        );
    }

    // Navigasi ke hari berikutnya
    public function nextDay(): void
    {
        $this->setActiveTanggal(
            Carbon::parse($this->activeTanggal)->addDay()->format('Y-m-d')
        );
    }

    // =========================================================================
    // MUAT DARI JADWAL MINGGUAN
    // Mengisi tabel dengan template dari JadwalLayanan berdasarkan hari
    // yang sesuai dengan $activeTanggal (misal: tanggal Senin → load jadwal SENIN).
    // Baris yang dimuat belum tersimpan ke DB — admin bisa review & edit dulu.
    // =========================================================================

    public function muatDariJadwalMingguan(): void
    {
        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId) return;

        $hari      = $this->getHariDariTanggal();
        $namaHari  = Hari::from($hari)->getLabel();

        $jadwals = JadwalLayanan::where('hari', $hari)
            ->whereHas('poliklinik.unitLayanan', function ($q) use ($rsId) {
                $q->where('rumah_sakit_id', $rsId);
                if ($this->selectedUnitLayananId) {
                    $q->where('id', $this->selectedUnitLayananId);
                }
            })
            ->get();

        if ($jadwals->isEmpty()) {
            Notification::make()
                ->title("Tidak ada jadwal mingguan untuk hari {$namaHari}")
                ->warning()
                ->send();
            return;
        }

        // Timpa rows dengan data dari jadwal mingguan.
        // jadwal_layanan_id diisi agar ada referensi ke jadwal induk.
        $this->rows = $jadwals->map(fn ($j) => [
            'poliklinik_id'     => $j->poliklinik_id,
            'jadwal_layanan_id' => $j->id,
            'dokter_id'         => $j->dokter_id,
            'nama_dokter'       => $j->nama_dokter,
            'jam_mulai'         => $j->jam_mulai?->format('H:i'),
            'jam_selesai'       => $j->jam_selesai?->format('H:i'),
            'status_layanan'    => $j->status_layanan->value,
            'catatan'           => $j->catatan,
        ])->toArray();

        Notification::make()
            ->title("{$jadwals->count()} baris dimuat dari jadwal {$namaHari}")
            ->body('Silakan review dan edit sebelum menyimpan.')
            ->success()
            ->send();
    }

    // =========================================================================
    // LIVEWIRE HOOKS
    // =========================================================================

    public function updatedSelectedRumahSakitId(): void
    {
        // Hapus semua cache karena RS berubah
        $this->rowsCache           = [];
        $this->selectedUnitLayananId = null;
        $this->rows                = [];

        if ($this->selectedRumahSakitId) {
            $this->loadRows();
        }
    }

    public function updatedSelectedUnitLayananId(): void
    {
        // Hapus semua cache karena filter unit berubah
        $this->rowsCache = [];
        $this->loadRows();
    }

    // Auto-fill nama_dokter saat kolom dokter_id dipilih
    public function updatedRows(mixed $value, string $key): void
    {
        if (! str_ends_with($key, '.dokter_id')) return;

        $index = (int) explode('.', $key)[0];

        if ($value) {
            $this->rows[$index]['nama_dokter'] = Dokter::find($value)?->nama;
        } else {
            $this->rows[$index]['nama_dokter'] = null;
        }
    }

    // =========================================================================
    // ROW MANAGEMENT
    // =========================================================================

    public function addRow(): void
    {
        $this->rows[] = [
            'poliklinik_id'     => null,
            'jadwal_layanan_id' => null,
            'dokter_id'         => null,
            'nama_dokter'       => null,
            'jam_mulai'         => null,
            'jam_selesai'       => null,
            'status_layanan'    => 'BUKA',
            'catatan'           => null,
        ];
    }

    public function removeRow(int $index): void
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
    }

    // =========================================================================
    // RESET JADWAL — Kosongkan semua baris tanpa menyimpan ke DB
    // =========================================================================

    public function resetJadwal(): void
    {
        $this->rows = [];
        $this->rowsCache[$this->activeTanggal] = [];

        Notification::make()
            ->title('Jadwal dikosongkan')
            ->body('Semua baris telah dihapus dari tampilan. Belum ada perubahan yang tersimpan ke database.')
            ->info()
            ->send();
    }

    // =========================================================================
    // SAVE — Replace-All per Tanggal
    // =========================================================================

    public function saveJadwal(): void
    {
        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId) {
            Notification::make()->title('Rumah sakit tidak teridentifikasi')->danger()->send();
            return;
        }

        // Cek baris kosong (poliklinik_id null) — tidak boleh disimpan
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

        // Validasi field wajib
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

        // Kumpulkan ID poliklinik scope RS/unit untuk batas DELETE
        $poliIds = PoliKlinik::whereHas('unitLayanan', function ($q) use ($rsId) {
                $q->where('rumah_sakit_id', $rsId);
                if ($this->selectedUnitLayananId) {
                    $q->where('id', $this->selectedUnitLayananId);
                }
            })
            ->pluck('id')
            ->toArray();

        // Replace-all dalam transaksi — hapus jadwal tanggal ini lalu insert ulang
        DB::transaction(function () use ($poliIds) {
            JadwalLayananHarian::where('tanggal', $this->activeTanggal)
                ->whereIn('poliklinik_id', $poliIds)
                ->delete();

            foreach ($this->rows as $row) {
                JadwalLayananHarian::create([
                    'poliklinik_id'     => $row['poliklinik_id'],
                    'tanggal'           => $this->activeTanggal,
                    'jadwal_layanan_id' => $row['jadwal_layanan_id'] ?: null,
                    'dokter_id'         => $row['dokter_id'] ?: null,
                    'nama_dokter'       => $row['nama_dokter'] ?: null,
                    'jam_mulai'         => $row['jam_mulai'],
                    'jam_selesai'       => $row['jam_selesai'] ?: null,
                    'status_layanan'    => $row['status_layanan'],
                    'catatan'           => $row['catatan'] ?: null,
                ]);
            }
        });

        // Invalidasi cache tanggal ini lalu reload
        $this->rowsCache[$this->activeTanggal] = null;
        $this->loadRows();

        $tanggalFormatted = Carbon::parse($this->activeTanggal)->translatedFormat('d F Y');
        Notification::make()
            ->title("Jadwal {$this->getNamaHariAktif()}, {$tanggalFormatted} berhasil disimpan")
            ->success()
            ->send();
    }
}
