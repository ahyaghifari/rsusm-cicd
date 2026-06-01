<?php

namespace App\Filament\Resources\JadwalPraktekResource\Pages;

use App\Enums\Hari;
use App\Filament\Resources\JadwalPraktekResource;
use App\Models\Dokter;
use App\Models\JadwalPraktek;
use App\Models\PoliKlinik;
use App\Models\RumahSakit;
use App\Models\UnitLayanan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class JadwalPraktekPage extends Page
{
    protected static string $resource = JadwalPraktekResource::class;
    protected static string $view     = 'filament.resources.jadwal-praktek-resource.pages.jadwal-praktek-page';
    protected static ?string $title   = 'Jadwal Praktek';

    // =========================================================================
    // STATE
    // =========================================================================

    public ?int    $selectedRumahSakitId  = null;
    public ?int    $selectedUnitLayananId = null;
    public string  $viewMode             = 'per_hari'; // 'per_hari' | 'per_dokter'
    public ?int    $selectedDokterId      = null;

    // Per-hari state
    public string $activeHari   = 'SENIN';
    public array  $rows         = [];
    public array  $rowsCache    = [];

    // Per-dokter state
    public array $dokterRows = [];

    // =========================================================================
    // LIFECYCLE
    // =========================================================================

    public function mount(): void
    {
        foreach (Hari::cases() as $h) {
            $this->rowsCache[$h->value] = null;
        }

        if (! JadwalPraktekResource::isSuperAdmin()) {
            $this->selectedRumahSakitId = JadwalPraktekResource::rumahSakitId();
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
        $rsSet      = (bool) $this->getActiveRumahSakitId();
        $multiUnit  = $rsSet && count($this->getUnitLayananOptions()) > 1;
        $perDokter  = $this->viewMode === 'per_dokter';

        return $form
            ->schema([
                // ── Rumah Sakit (superadmin only) ──────────────────────────
                Forms\Components\Select::make('selectedRumahSakitId')
                    ->label('Rumah Sakit')
                    ->placeholder('— Pilih Rumah Sakit —')
                    ->options(fn () => RumahSakit::orderBy('nama')->pluck('nama', 'id'))
                    ->required(fn () => JadwalPraktekResource::isSuperAdmin())
                    ->visible(fn () => JadwalPraktekResource::isSuperAdmin())
                    ->live()
                    ->columnSpan($multiUnit ? 1 : 2),

                // ── Unit Layanan (jika > 1) ────────────────────────────────
                Forms\Components\Select::make('selectedUnitLayananId')
                    ->label('Unit Layanan')
                    ->placeholder('— Semua Unit Layanan —')
                    ->options(fn () => $this->getUnitLayananOptions())
                    ->visible(fn () => count($this->getUnitLayananOptions()) > 1)
                    ->live(),

                // ── Mode View ──────────────────────────────────────────────
                Forms\Components\ToggleButtons::make('viewMode')
                    ->label('Mode Tampilan')
                    ->options([
                        'per_hari'   => 'Per Hari',
                        'per_dokter' => 'Per Dokter',
                    ])
                    ->icons([
                        'per_hari'   => 'heroicon-o-calendar-days',
                        'per_dokter' => 'heroicon-o-user',
                    ])
                    ->inline()
                    ->live()
                    ->visible(fn () => $rsSet)
                    ->columnSpanFull(),

                // ── Pilih Dokter (per_dokter mode) ─────────────────────────
                Forms\Components\Select::make('selectedDokterId')
                    ->label('Dokter')
                    ->placeholder('— Cari & pilih dokter —')
                    ->options(fn () => $this->getDokterOptions())
                    ->searchable()
                    ->live()
                    ->visible(fn () => $rsSet && $perDokter)
                    ->columnSpanFull(),
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

    protected function getPoliIds(): array
    {
        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId) return [];

        return PoliKlinik::whereHas('unitLayanan', function ($q) use ($rsId) {
                $q->where('rumah_sakit_id', $rsId);
                if ($this->selectedUnitLayananId) {
                    $q->where('id', $this->selectedUnitLayananId);
                }
            })
            ->pluck('id')
            ->toArray();
    }

    // =========================================================================
    // DATA LOADING — Per Hari
    // =========================================================================

    public function loadRows(): void
    {
        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId) { $this->rows = []; return; }

        $jadwals = JadwalPraktek::where('hari', $this->activeHari)
            ->whereHas('poliklinik.unitLayanan', function ($q) use ($rsId) {
                $q->where('rumah_sakit_id', $rsId);
                if ($this->selectedUnitLayananId) {
                    $q->where('id', $this->selectedUnitLayananId);
                }
            })
            ->orderBy('waktu_mulai')
            ->get();

        $this->rows = $jadwals->map(fn ($j) => [
            'poliklinik_id'     => $j->poliklinik_id,
            'dokter_id'         => $j->dokter_id,
            'nama_dokter'       => $j->nama_dokter,
            'waktu_mulai'       => $j->waktu_mulai?->format('H:i'),
            'waktu_selesai'     => $j->waktu_selesai?->format('H:i'),
            'sesuai_perjanjian' => $j->sesuai_perjanjian ? '1' : '0',
            'catatan'           => $j->catatan,
        ])->toArray();

        $this->rowsCache[$this->activeHari] = $this->rows;
    }

    // =========================================================================
    // DATA LOADING — Per Dokter
    // =========================================================================

    public function loadDokterRows(): void
    {
        if (! $this->selectedDokterId || ! $this->getActiveRumahSakitId()) {
            $this->dokterRows = [];
            return;
        }

        $hariOrder = array_flip(['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'MINGGU']);

        $jadwals = JadwalPraktek::where('dokter_id', $this->selectedDokterId)
            ->whereIn('poliklinik_id', $this->getPoliIds())
            ->get();

        $this->dokterRows = $jadwals
            ->map(fn ($j) => [
                'hari'              => $j->hari->value,
                'poliklinik_id'     => $j->poliklinik_id,
                'waktu_mulai'       => $j->waktu_mulai?->format('H:i'),
                'waktu_selesai'     => $j->waktu_selesai?->format('H:i'),
                'sesuai_perjanjian' => $j->sesuai_perjanjian ? '1' : '0',
                'catatan'           => $j->catatan,
            ])
            ->sortBy(fn ($r) => $hariOrder[$r['hari']] ?? 9)
            ->values()
            ->toArray();
    }

    // =========================================================================
    // TAB HARI
    // =========================================================================

    public function setActiveHari(string $hari): void
    {
        $this->rowsCache[$this->activeHari] = $this->rows;
        $this->activeHari = $hari;

        if ($this->rowsCache[$hari] !== null) {
            $this->rows = $this->rowsCache[$hari];
        } else {
            $this->loadRows();
        }
    }

    // =========================================================================
    // LIVEWIRE HOOKS
    // =========================================================================

    public function updatedSelectedRumahSakitId(): void
    {
        foreach (Hari::cases() as $h) { $this->rowsCache[$h->value] = null; }
        $this->selectedUnitLayananId = null;
        $this->selectedDokterId      = null;
        $this->rows                  = [];
        $this->dokterRows            = [];

        if ($this->selectedRumahSakitId) {
            $this->loadRows();
        }
    }

    public function updatedSelectedUnitLayananId(): void
    {
        foreach (Hari::cases() as $h) { $this->rowsCache[$h->value] = null; }
        $this->selectedDokterId = null;
        $this->dokterRows       = [];
        $this->loadRows();
    }

    public function updatedViewMode(): void
    {
        $this->selectedDokterId = null;
        $this->dokterRows       = [];
        foreach (Hari::cases() as $h) { $this->rowsCache[$h->value] = null; }

        if ($this->viewMode === 'per_hari') {
            $this->loadRows();
        }
    }

    public function updatedSelectedDokterId(): void
    {
        $this->loadDokterRows();
    }

    public function updatedRows(mixed $value, string $key): void
    {
        if (! str_ends_with($key, '.dokter_id')) return;
        $index = (int) explode('.', $key)[0];
        $this->rows[$index]['nama_dokter'] = $value ? Dokter::find($value)?->nama : null;
    }

    // =========================================================================
    // ROW MANAGEMENT — Per Hari
    // =========================================================================

    public function addRow(): void
    {
        $this->rows[] = [
            'poliklinik_id'     => null,
            'dokter_id'         => null,
            'nama_dokter'       => null,
            'waktu_mulai'       => null,
            'waktu_selesai'     => null,
            'sesuai_perjanjian' => '0',
            'catatan'           => null,
        ];
    }

    public function removeRow(int $index): void
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
    }

    // =========================================================================
    // ROW MANAGEMENT — Per Dokter
    // =========================================================================

    public function addDokterRow(): void
    {
        $this->dokterRows[] = [
            'hari'              => 'SENIN',
            'poliklinik_id'     => null,
            'waktu_mulai'       => null,
            'waktu_selesai'     => null,
            'sesuai_perjanjian' => '0',
            'catatan'           => null,
        ];
    }

    public function removeDokterRow(int $index): void
    {
        unset($this->dokterRows[$index]);
        $this->dokterRows = array_values($this->dokterRows);
    }

    // =========================================================================
    // SAVE — Per Hari
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
                    ->body('Pilih poliklinik atau hapus baris tersebut.')
                    ->warning()->send();
                return;
            }
        }

        $poliIds = $this->getPoliIds();

        DB::transaction(function () use ($poliIds) {
            JadwalPraktek::where('hari', $this->activeHari)
                ->whereIn('poliklinik_id', $poliIds)
                ->delete();

            foreach ($this->rows as $row) {
                JadwalPraktek::create([
                    'poliklinik_id'     => $row['poliklinik_id'],
                    'hari'              => $this->activeHari,
                    'dokter_id'         => $row['dokter_id'] ?: null,
                    'nama_dokter'       => $row['nama_dokter'] ?: null,
                    'waktu_mulai'       => $row['waktu_mulai'] ?: null,
                    'waktu_selesai'     => $row['waktu_selesai'] ?: null,
                    'sesuai_perjanjian' => ($row['sesuai_perjanjian'] ?? '0') === '1',
                    'catatan'           => $row['catatan'] ?: null,
                ]);
            }
        });

        $this->rowsCache[$this->activeHari] = null;
        $this->loadRows();

        Notification::make()
            ->title('Jadwal ' . Hari::from($this->activeHari)->getLabel() . ' berhasil disimpan')
            ->success()->send();
    }

    // =========================================================================
    // SAVE — Per Dokter
    // =========================================================================

    public function saveDokterJadwal(): void
    {
        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId || ! $this->selectedDokterId) {
            Notification::make()->title('Data tidak lengkap')->danger()->send();
            return;
        }

        $dokter = Dokter::find($this->selectedDokterId);
        if (! $dokter || (int) $dokter->rumah_sakit_id !== $rsId) {
            abort(403);
        }

        foreach ($this->dokterRows as $i => $row) {
            if (empty($row['poliklinik_id'])) {
                Notification::make()
                    ->title("Baris ke-" . ($i + 1) . ": Poliklinik wajib dipilih")
                    ->warning()->send();
                return;
            }
        }

        $poliIds = $this->getPoliIds();

        DB::transaction(function () use ($poliIds, $dokter) {
            JadwalPraktek::where('dokter_id', $this->selectedDokterId)
                ->whereIn('poliklinik_id', $poliIds)
                ->delete();

            foreach ($this->dokterRows as $row) {
                JadwalPraktek::create([
                    'poliklinik_id'     => $row['poliklinik_id'],
                    'hari'              => $row['hari'],
                    'dokter_id'         => $this->selectedDokterId,
                    'nama_dokter'       => $dokter->nama,
                    'waktu_mulai'       => $row['waktu_mulai'] ?: null,
                    'waktu_selesai'     => $row['waktu_selesai'] ?: null,
                    'sesuai_perjanjian' => ($row['sesuai_perjanjian'] ?? '0') === '1',
                    'catatan'           => $row['catatan'] ?: null,
                ]);
            }
        });

        $this->loadDokterRows();

        Notification::make()
            ->title("Jadwal {$dokter->nama} berhasil disimpan")
            ->success()->send();
    }
}
