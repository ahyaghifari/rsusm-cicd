<?php

namespace App\Filament\Resources\JadwalPraktekResource\Pages;

use App\Enums\Hari;
use App\Filament\Resources\JadwalPraktekResource;
use App\Models\Dokter;
use App\Models\JadwalPraktek;
use App\Models\PoliKlinik;
use App\Models\RumahSakit;
use App\Models\UnitLayanan;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        return ['filterForm', 'dokterForm', 'rowsForm', 'dokterRowsForm'];
    }

    public function rowsForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Repeater::make('rows')
                    ->schema([
                        Forms\Components\Select::make('poliklinik_id')
                            ->label('Poliklinik')
                            ->options(fn () => $this->getPoliklinikOptions())
                            ->searchable()
                            ->required()
                            ->columnSpan(2),

                        Forms\Components\Select::make('dokter_id')
                            ->label('Dokter')
                            ->options(fn () => $this->getDokterOptions())
                            ->searchable()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set, ?int $state) =>
                                $set('nama_dokter', $state ? Dokter::find($state)?->nama : null)
                            )
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('nama_dokter')
                            ->label('Nama Dokter')
                            ->nullable()
                            ->columnSpan(2),

                        Forms\Components\TimePicker::make('waktu_mulai')
                            ->label('Jam Mulai')
                            ->seconds(false)
                            ->nullable(),

                        Forms\Components\TimePicker::make('waktu_selesai')
                            ->label('Jam Selesai (opsional)')
                            ->seconds(false)
                            ->nullable()
                            ->placeholder('—'),

                        Forms\Components\Toggle::make('sesuai_perjanjian')
                            ->label('Perjanjian')
                            ->default(false),

                        Forms\Components\TextInput::make('catatan')
                            ->label('Catatan')
                            ->nullable()
                            ->columnSpan(5),

                        Forms\Components\Hidden::make('sumber')->default('GENERATE'),
                        Forms\Components\Hidden::make('id')->default(null),
                    ])
                    ->columns(5)
                    ->defaultItems(0)
                    ->addActionLabel('+ Tambah Baris')
                    ->reorderable(false)
                    ->itemLabel(fn (array $state): ?string =>
                        $state['poliklinik_id']
                            ? (PoliKlinik::find($state['poliklinik_id'])?->nama ?? null)
                            : null
                    ),
            ])
            ->statePath('');
    }

    public function dokterRowsForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Repeater::make('dokterRows')
                    ->schema([
                        Forms\Components\Select::make('hari')
                            ->label('Hari')
                            ->options(Hari::class)
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('poliklinik_id')
                            ->label('Poliklinik')
                            ->options(fn () => $this->getPoliklinikOptions())
                            ->searchable()
                            ->required()
                            ->columnSpan(2),

                        Forms\Components\TimePicker::make('waktu_mulai')
                            ->label('Jam Mulai')
                            ->seconds(false)
                            ->nullable(),

                        Forms\Components\TimePicker::make('waktu_selesai')
                            ->label('Jam Selesai (opsional)')
                            ->seconds(false)
                            ->nullable()
                            ->placeholder('—'),

                        Forms\Components\Toggle::make('sesuai_perjanjian')
                            ->label('Perjanjian')
                            ->default(false),

                        Forms\Components\TextInput::make('catatan')
                            ->label('Catatan')
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->defaultItems(0)
                    ->addActionLabel('+ Tambah Baris')
                    ->reorderable(false),
            ])
            ->statePath('');
    }

    public function filterForm(Form $form): Form
    {
        $rsSet = (bool) $this->getActiveRumahSakitId();

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
                    ->columnSpanFull(),

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

                // ── Unit Layanan (jika > 1, muncul setelah mode dipilih) ───
                Forms\Components\Select::make('selectedUnitLayananId')
                    ->label('Unit Layanan')
                    ->placeholder('— Pilih Unit Layanan —')
                    ->options(fn () => $this->getUnitLayananOptions())
                    ->required(fn () => count($this->getUnitLayananOptions()) > 1)
                    ->visible(fn () => $rsSet && count($this->getUnitLayananOptions()) > 1)
                    ->live()
                    ->columnSpanFull(),
            ])
            ->statePath('')
            ->columns(2);
    }

    // Dokter selector ditaruh terpisah agar bisa dirender di area konten
    public function dokterForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('selectedDokterId')
                    ->label('Pilih Dokter')
                    ->placeholder('— Cari & pilih dokter —')
                    ->options(fn () => $this->getDokterOptions())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->columnSpanFull(),
            ])
            ->statePath('');
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function getActiveRumahSakitId(): ?int
    {
        return $this->selectedRumahSakitId;
    }

    // RS punya >1 unit layanan tapi belum memilih → jadwal belum boleh tampil
    public function mustPickUnit(): bool
    {
        return (bool) $this->getActiveRumahSakitId()
            && count($this->getUnitLayananOptions()) > 1
            && ! $this->selectedUnitLayananId;
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
                    'sesuai_perjanjian' => (bool) ($row['sesuai_perjanjian'] ?? false),
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
                    'sesuai_perjanjian' => (bool) ($row['sesuai_perjanjian'] ?? false),
                    'catatan'           => $row['catatan'] ?: null,
                ]);
            }
        });

        $this->loadDokterRows();

        Notification::make()
            ->title("Jadwal {$dokter->nama} berhasil disimpan")
            ->success()->send();
    }

    // =========================================================================
    // EXPORT PDF
    // =========================================================================

    public function exportPdf(): StreamedResponse
    {
        $rsId = $this->getActiveRumahSakitId();

        if (! $rsId) {
            Notification::make()->title('Pilih Rumah Sakit terlebih dahulu')->warning()->send();
            return response()->streamDownload(fn () => '', 'error.pdf');
        }

        $rs   = RumahSakit::find($rsId);
        $unit = $this->selectedUnitLayananId
            ? UnitLayanan::find($this->selectedUnitLayananId)?->nama
            : null;

        if ($this->viewMode === 'per_hari') {
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

            $hariLabel  = Hari::from($this->activeHari)->getLabel();
            $title      = "Jadwal Praktek — {$hariLabel}";
            $filename   = "jadwal-" . strtolower($this->activeHari) . "-" . now()->format('Ymd') . ".pdf";
            $dokterNama = null;

        } else {
            if (! $this->selectedDokterId) {
                Notification::make()->title('Pilih Dokter terlebih dahulu')->warning()->send();
                return response()->streamDownload(fn () => '', 'error.pdf');
            }

            $dokter     = Dokter::find($this->selectedDokterId);
            $hariOrder  = array_flip(['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'MINGGU']);

            $jadwals = JadwalPraktek::where('dokter_id', $this->selectedDokterId)
                ->whereIn('poliklinik_id', $this->getPoliIds())
                ->with('poliklinik')
                ->get()
                ->sortBy(fn ($j) => $hariOrder[$j->hari->value] ?? 9)
                ->values();

            $dokterNama = $dokter?->nama ?? '-';
            $hariLabel  = null;
            $title      = "Jadwal Praktek — {$dokterNama}";
            $filename   = "jadwal-dokter-" . Str::slug($dokterNama) . "-" . now()->format('Ymd') . ".pdf";
        }

        $pdf = Pdf::loadView('pdf.jadwal-praktek', [
            'jadwals'    => $jadwals,
            'title'      => $title,
            'rsName'     => $rs?->nama ?? '-',
            'unit'       => $unit,
            'viewMode'   => $this->viewMode,
            'hariLabel'  => $hariLabel ?? '',
            'dokterNama' => $dokterNama ?? '',
            'tanggal'    => now()->translatedFormat('d F Y, H:i') . ' WITA',
        ])->setPaper('a4', 'landscape')
          ->setOption('margin_top', 10)
          ->setOption('margin_bottom', 10)
          ->setOption('margin_left', 12)
          ->setOption('margin_right', 12);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
