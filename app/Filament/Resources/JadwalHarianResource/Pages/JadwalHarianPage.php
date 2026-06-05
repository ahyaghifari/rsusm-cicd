<?php

namespace App\Filament\Resources\JadwalHarianResource\Pages;

use App\Enums\Hari;
use App\Enums\StatusLayanan;
use App\Filament\Resources\JadwalHarianResource;
use App\Models\Dokter;
use App\Models\JadwalHarian;
use App\Models\JadwalHarianPerubahan;
use App\Models\JadwalPraktek;
use App\Models\PoliKlinik;
use App\Models\RumahSakit;
use App\Models\UnitLayanan;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JadwalHarianPage extends Page
{
    protected static string $resource = JadwalHarianResource::class;

    protected static string $view = 'filament.resources.jadwal-harian-resource.pages.jadwal-harian-page';

    protected static ?string $title = 'Jadwal Harian';

    // =========================================================================
    // STATE / PROPERTIES
    // =========================================================================

    public ?int $selectedRumahSakitId = null;
    public ?int $selectedUnitLayananId = null;
    public string $activeTanggal = '';
    public array $rows = [];
    public array $rowsCache = [];

    // Modal perubahan
    public bool  $showPerubahan  = false;
    public array $dataPerubahan  = ['ditambah' => [], 'diubah' => []];

    protected const DAY_MAP = [
        0 => 'MINGGU', 1 => 'SENIN', 2 => 'SELASA', 3 => 'RABU',
        4 => 'KAMIS',  5 => 'JUMAT', 6 => 'SABTU',
    ];

    // =========================================================================
    // LIFECYCLE
    // =========================================================================

    public function mount(): void
    {
        $this->activeTanggal = now()->format('Y-m-d');

        if (! JadwalHarianResource::isSuperAdmin()) {
            $this->selectedRumahSakitId = JadwalHarianResource::rumahSakitId();
            $this->loadRows();
        }
    }

    // =========================================================================
    // FILTER FORM
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
                    ->required(fn () => JadwalHarianResource::isSuperAdmin())
                    ->visible(fn () => JadwalHarianResource::isSuperAdmin())
                    ->live(),

                Forms\Components\Select::make('selectedUnitLayananId')
                    ->label('Unit Layanan')
                    ->placeholder('— Semua Unit Layanan —')
                    ->options(fn () => $this->getUnitLayananOptions())
                    ->visible(fn () => count($this->getUnitLayananOptions()) > 1)
                    ->live(),
            ])
            ->statePath('')
            ->columns(2);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function getActiveRumahSakitId(): ?int
    {
        return $this->selectedRumahSakitId;
    }

    public function mustPickUnit(): bool
    {
        return (bool) $this->getActiveRumahSakitId()
            && count($this->getUnitLayananOptions()) > 1
            && ! $this->selectedUnitLayananId;
    }

    public function getNamaHariAktif(): string
    {
        if (! $this->activeTanggal) return '';
        $hariValue = self::DAY_MAP[Carbon::parse($this->activeTanggal)->dayOfWeek];
        return Hari::from($hariValue)->getLabel();
    }

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

    public function loadRows(): void
    {
        $rsId = $this->getActiveRumahSakitId();

        if (! $rsId || ! $this->activeTanggal) {
            $this->rows = [];
            return;
        }

        $jadwals = JadwalHarian::whereDate('tanggal', $this->activeTanggal)
            ->whereHas('poliklinik.unitLayanan', function ($q) use ($rsId) {
                $q->where('rumah_sakit_id', $rsId);
                if ($this->selectedUnitLayananId) {
                    $q->where('id', $this->selectedUnitLayananId);
                }
            })
            ->get();

        $newRows = [];
        foreach ($jadwals as $j) {
            $newRows[(string) Str::uuid()] = [
                'id'             => $j->id,
                'poliklinik_id'  => $j->poliklinik_id,
                'dokter_id'      => $j->dokter_id,
                'nama_dokter'    => $j->nama_dokter,
                'jam_mulai'      => $j->jam_mulai?->format('H:i'),
                'jam_selesai'    => $j->jam_selesai?->format('H:i'),
                'status_layanan' => $j->status_layanan->value,
                'catatan'        => $j->catatan,
                'sumber'         => $j->sumber,
            ];
        }

        $this->rows = $newRows;

        $this->rowsCache[$this->activeTanggal] = $this->rows;
    }

    // =========================================================================
    // NAVIGASI TANGGAL
    // =========================================================================

    public function setActiveTanggal(string $tanggal): void
    {
        if (! $tanggal) return;

        $this->rowsCache[$this->activeTanggal] = $this->rows;
        $this->activeTanggal = $tanggal;

        if (isset($this->rowsCache[$tanggal]) && $this->rowsCache[$tanggal] !== null) {
            $this->rows = $this->rowsCache[$tanggal];
        } else {
            $this->loadRows();
        }
    }

    public function prevDay(): void
    {
        $this->setActiveTanggal(Carbon::parse($this->activeTanggal)->subDay()->format('Y-m-d'));
    }

    public function nextDay(): void
    {
        $this->setActiveTanggal(Carbon::parse($this->activeTanggal)->addDay()->format('Y-m-d'));
    }

    // =========================================================================
    // MUAT DARI JADWAL PRAKTEK MINGGUAN
    // =========================================================================

    public function muatDariJadwalMingguan(): void
    {
        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId) return;

        $hari     = $this->getHariDariTanggal();
        $namaHari = Hari::from($hari)->getLabel();

        $jadwals = JadwalPraktek::where('hari', $hari)
            ->whereHas('poliklinik.unitLayanan', function ($q) use ($rsId) {
                $q->where('rumah_sakit_id', $rsId);
                if ($this->selectedUnitLayananId) {
                    $q->where('id', $this->selectedUnitLayananId);
                }
            })
            ->get();

        if ($jadwals->isEmpty()) {
            Notification::make()
                ->title("Tidak ada jadwal praktek untuk hari {$namaHari}")
                ->warning()->send();
            return;
        }

        $newRows = [];
        foreach ($jadwals as $j) {
            $newRows[(string) Str::uuid()] = [
                'id'             => null,
                'poliklinik_id'  => $j->poliklinik_id,
                'dokter_id'      => $j->dokter_id,
                'nama_dokter'    => $j->nama_dokter,
                'jam_mulai'      => $j->waktu_mulai?->format('H:i'),
                'jam_selesai'    => $j->waktu_selesai?->format('H:i'),
                'status_layanan' => 'BUKA',
                'catatan'        => $j->catatan,
                'sumber'         => 'GENERATE',
            ];
        }

        $this->rows = $newRows;

        Notification::make()
            ->title("{$jadwals->count()} baris dimuat dari jadwal praktek {$namaHari}")
            ->body('Silakan review dan edit sebelum menyimpan.')
            ->success()->send();
    }

    // =========================================================================
    // LIVEWIRE HOOKS
    // =========================================================================

    public function updatedSelectedRumahSakitId(): void
    {
        $this->rowsCache           = [];
        $this->selectedUnitLayananId = null;
        $this->rows                = [];

        if ($this->selectedRumahSakitId) {
            $this->loadRows();
        }
    }

    public function updatedSelectedUnitLayananId(): void
    {
        $this->rowsCache = [];
        $this->loadRows();
    }

    // =========================================================================
    // ROW MANAGEMENT
    // =========================================================================

    public function addRow(): void
    {
        $this->rows[(string) Str::uuid()] = [
            'id'             => null,
            'poliklinik_id'  => null,
            'dokter_id'      => null,
            'nama_dokter'    => null,
            'jam_mulai'      => null,
            'jam_selesai'    => null,
            'status_layanan' => 'BUKA',
            'catatan'        => null,
            'sumber'         => 'MANUAL',
        ];
    }

    public function removeRow(string $key): void
    {
        unset($this->rows[$key]);
    }

    public function updatedRows(mixed $value, string $key): void
    {
        if (! str_ends_with($key, '.dokter_id')) return;
        $uuidKey = explode('.', $key)[0];
        $this->rows[$uuidKey]['nama_dokter'] = $value ? Dokter::find($value)?->nama : null;
    }

    public function resetJadwal(): void
    {
        $this->rows = [];
        $this->rowsCache[$this->activeTanggal] = [];

        Notification::make()
            ->title('Jadwal dikosongkan')
            ->body('Semua baris telah dihapus dari tampilan. Belum ada perubahan yang tersimpan ke database.')
            ->info()->send();
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

        foreach ($this->rows as $i => $row) {
            if (empty($row['poliklinik_id'])) {
                Notification::make()
                    ->title("Baris ke-" . ($i + 1) . " belum lengkap")
                    ->body('Pilih poliklinik atau hapus baris tersebut sebelum menyimpan.')
                    ->warning()->send();
                return;
            }
        }

        foreach ($this->rows as $i => $row) {
            if (empty($row['jam_mulai'])) {
                Notification::make()
                    ->title("Baris ke-" . ($i + 1) . ": Jam Mulai wajib diisi")
                    ->danger()->send();
                return;
            }
            if (empty($row['status_layanan'])) {
                Notification::make()
                    ->title("Baris ke-" . ($i + 1) . ": Status Layanan wajib diisi")
                    ->danger()->send();
                return;
            }
        }

        $poliIds = PoliKlinik::whereHas('unitLayanan', function ($q) use ($rsId) {
                $q->where('rumah_sakit_id', $rsId);
                if ($this->selectedUnitLayananId) {
                    $q->where('id', $this->selectedUnitLayananId);
                }
            })
            ->pluck('id')
            ->toArray();

        DB::transaction(function () use ($poliIds) {
            JadwalHarian::whereDate('tanggal', $this->activeTanggal)
                ->whereIn('poliklinik_id', $poliIds)
                ->delete();

            $userId = Auth::id();
            $hariValue = $this->getHariDariTanggal();

            foreach ($this->rows as $row) {
                $sumber = $row['sumber'] ?? 'MANUAL';

                // Format jam untuk pencarian (tambahkan detik jika belum ada)
                $jamMulaiRaw = $row['jam_mulai'];
                $jamMulaiFormat = (strlen($jamMulaiRaw) === 5) ? $jamMulaiRaw . ':00' : $jamMulaiRaw;
                
                $jamSelesaiFormat = null;
                if (!empty($row['jam_selesai'])) {
                    $jamSelesaiRaw = $row['jam_selesai'];
                    $jamSelesaiFormat = (strlen($jamSelesaiRaw) === 5) ? $jamSelesaiRaw . ':00' : $jamSelesaiRaw;
                }

                // Cek apakah data ini identik dengan JadwalPraktek (jadwal asli mingguan)
                $isSamaDenganAsli = JadwalPraktek::where('hari', $hariValue)
                    ->where('poliklinik_id', $row['poliklinik_id'])
                    ->where('dokter_id', $row['dokter_id'])
                    ->whereTime('waktu_mulai', $jamMulaiFormat)
                    ->when($jamSelesaiFormat, function ($q, $v) {
                        $q->whereTime('waktu_selesai', $v);
                    }, function ($q) {
                        $q->whereNull('waktu_selesai');
                    })
                    ->exists();

                // Jika identik dengan nilai awal mingguan dan status BUKA,
                // maka ini BUKAN perubahan. Set sumber kembali ke GENERATE agar tidak
                // terhitung sebagai data MANUAL/DITAMBAH.
                if ($isSamaDenganAsli && $row['status_layanan'] === 'BUKA') {
                    $sumber = 'GENERATE';
                }

                $jh = JadwalHarian::create([
                    'poliklinik_id'  => $row['poliklinik_id'],
                    'tanggal'        => $this->activeTanggal,
                    'dokter_id'      => $row['dokter_id'] ?: null,
                    'nama_dokter'    => $row['nama_dokter'] ?: null,
                    'jam_mulai'      => $row['jam_mulai'],
                    'jam_selesai'    => $row['jam_selesai'] ?: null,
                    'status_layanan' => $row['status_layanan'],
                    'catatan'        => $row['catatan'] ?: null,
                    'sumber'         => $sumber,
                ]);

                $jhId = $jh->getKey();

                // Catat ke JadwalHarianPerubahan hanya jika sumber GENERATE dan status bukan BUKA
                if ($sumber === 'GENERATE' && $row['status_layanan'] !== 'BUKA') {
                    JadwalHarianPerubahan::create([
                        'jadwal_harian_id' => $jhId,
                        'user_id'          => $userId,
                        'jam_mulai'        => $row['jam_mulai'] ?: null,
                        'jam_selesai'      => $row['jam_selesai'] ?: null,
                        'status_layanan'   => $row['status_layanan'],
                        'catatan'          => $row['catatan'] ?: null,
                    ]);
                }
            }
        });

        $this->rowsCache[$this->activeTanggal] = null;
        $this->loadRows();

        $tanggalFormatted = Carbon::parse($this->activeTanggal)->translatedFormat('d F Y');
        Notification::make()
            ->title("Jadwal {$this->getNamaHariAktif()}, {$tanggalFormatted} berhasil disimpan")
            ->success()->send();
    }

    // =========================================================================
    // MODAL PERUBAHAN
    // =========================================================================

    public function openPerubahan(): void
    {
        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId) return;

        $scope = function ($q) use ($rsId) {
            $q->whereDate('tanggal', $this->activeTanggal)
              ->whereHas('poliklinik.unitLayanan', function ($q2) use ($rsId) {
                  $q2->where('rumah_sakit_id', $rsId);
                  if ($this->selectedUnitLayananId) {
                      $q2->where('id', $this->selectedUnitLayananId);
                  }
              });
        };

        // DITAMBAH: baris manual dari jadwal_harian langsung
        $ditambah = JadwalHarian::where('sumber', 'MANUAL')
            ->where($scope)
            ->with('poliklinik')
            ->get();

        // DIUBAH: semua record di jadwal_harian_perubahan (semuanya adalah perubahan)
        $diubah = JadwalHarianPerubahan::whereHas('jadwalHarian', $scope)
            ->with('jadwalHarian.poliklinik', 'user')
            ->get();

        $this->dataPerubahan = [
            'ditambah' => $ditambah->toArray(),
            'diubah'   => $diubah->toArray(),
        ];

        $this->showPerubahan = true;
    }

    public function closePerubahan(): void
    {
        $this->showPerubahan = false;
    }
}
