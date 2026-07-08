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

    // Toggle "Layar Penuh" — disimpan di server (bukan variabel Alpine lokal) supaya
    // tidak ter-reset ke false setiap kali komponen ini re-render (mis. tiap keystroke
    // di kolom jadwal), yang sebelumnya membuat sidebar "muncul kembali" sendiri.
    public bool $isFullscreen = false;

    // Executive clinic filter: 'all' | 'reguler' | 'eksekutif'
    public string $executiveClinicFilter = 'reguler';

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

                Forms\Components\Select::make('executiveClinicFilter')
                    ->label('Filter Klinik')
                    ->options([
                        'all'       => 'Semua',
                        'reguler'   => 'Reguler',
                        'eksekutif' => 'Eksekutif',
                    ])
                    ->default('reguler')
                    ->visible(fn () => $this->hasExecutiveClinic() && $this->hasJadwalHarianData())
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
        return false;
    }

    public function canEditJadwal(): bool
    {
        return (bool) auth()->user()?->can('update_jadwal::harian');
    }

    public function hasExecutiveClinic(): bool
    {
        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId) return false;
        return (bool) RumahSakit::where('id', $rsId)->value('executive_clinic');
    }

    public function hasJadwalHarianData(): bool
    {
        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId || ! $this->activeTanggal) return false;

        return JadwalHarian::whereDate('tanggal', $this->activeTanggal)
            ->whereHas('poliklinik', fn ($q) => $q->where('rumah_sakit_id', $rsId))
            ->exists();
    }

    /**
     * True kalau tampilan saat ini sedang dipersempit ke Reguler/Eksekutif saja
     * (bukan "Semua") — dipakai untuk menyembunyikan tombol "Kosongkan" supaya
     * tidak mengosongkan hanya sebagian data sambil terlihat seperti mengosongkan semua.
     */
    public function isJadwalFiltered(): bool
    {
        return $this->hasExecutiveClinic() && $this->executiveClinicFilter !== 'all';
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

        return PoliKlinik::where('rumah_sakit_id', $rsId)
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
            ->whereHas('poliklinik', function ($q) use ($rsId) {
                $q->where('rumah_sakit_id', $rsId);
            })
            ->when($this->executiveClinicFilter === 'reguler',   fn ($q) => $q->where('is_executive', 0))
            ->when($this->executiveClinicFilter === 'eksekutif', fn ($q) => $q->where('is_executive', 1))
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
                'is_executive'   => (bool) $j->is_executive,
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
        abort_unless($this->canEditJadwal(), 403);

        // Tombol ini seharusnya sudah disembunyikan di UI kalau data sudah ada —
        // guard ini cuma jaring pengaman kalau method dipanggil langsung.
        if ($this->hasJadwalHarianData()) {
            Notification::make()
                ->title('Jadwal harian untuk tanggal ini sudah ada')
                ->body('Kosongkan jadwal yang ada terlebih dahulu sebelum memuat ulang dari jadwal mingguan.')
                ->warning()->send();
            return;
        }

        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId) return;

        $hari     = $this->getHariDariTanggal();
        $namaHari = Hari::from($hari)->getLabel();

        $jadwals = JadwalPraktek::where('hari', $hari)
            ->whereHas('poliklinik', function ($q) use ($rsId) {
                $q->where('rumah_sakit_id', $rsId);
            })
            ->when($this->executiveClinicFilter === 'reguler',   fn ($q) => $q->where('is_executive', 0))
            ->when($this->executiveClinicFilter === 'eksekutif', fn ($q) => $q->where('is_executive', 1))
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
                'is_executive'   => (bool) $j->is_executive,
                'sumber'         => 'GENERATE',
            ];
        }

        $this->rows = $newRows;

        if (! $this->persistJadwal()) {
            // Validasi gagal (mis. ada baris tanpa jam mulai) — baris tetap tampil
            // di tabel supaya bisa diperbaiki lalu disimpan manual lewat tombol Simpan.
            return;
        }

        Notification::make()
            ->title("{$jadwals->count()} baris dimuat & disimpan dari jadwal praktek {$namaHari}")
            ->success()->send();
    }

    // =========================================================================
    // LIVEWIRE HOOKS
    // =========================================================================

    public function updatedSelectedRumahSakitId(): void
    {
        $this->rowsCache             = [];
        $this->selectedUnitLayananId = null;
        $this->rows                  = [];
        $this->executiveClinicFilter = 'reguler';

        if ($this->selectedRumahSakitId) {
            $this->loadRows();
        }
    }

    public function updatedExecutiveClinicFilter(): void
    {
        $this->rowsCache = [];
        $this->rows      = [];

        if ($this->getActiveRumahSakitId() && $this->activeTanggal) {
            $this->loadRows();
        }
    }

    public function updatedSelectedUnitLayananId(): void
    {
        // Unit layanan sudah dihapus — hook dipertahankan agar state lama tidak crash.
    }

    // =========================================================================
    // ROW MANAGEMENT
    // =========================================================================

    public function addRow(): void
    {
        abort_unless($this->canEditJadwal(), 403);

        $isExecutive = $this->executiveClinicFilter === 'eksekutif';

        $this->rows[(string) Str::uuid()] = [
            'id'             => null,
            'poliklinik_id'  => null,
            'dokter_id'      => null,
            'nama_dokter'    => null,
            'jam_mulai'      => null,
            'jam_selesai'    => null,
            'status_layanan' => 'BUKA',
            'catatan'        => null,
            'is_executive'   => $isExecutive,
            'sumber'         => 'MANUAL',
        ];
    }

    public function removeRow(string $key): void
    {
        abort_unless($this->canEditJadwal(), 403);

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
        abort_unless($this->canEditJadwal(), 403);

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
        abort_unless($this->canEditJadwal(), 403);

        if (! $this->persistJadwal()) {
            return;
        }

        $tanggalFormatted = Carbon::parse($this->activeTanggal)->translatedFormat('d F Y');
        Notification::make()
            ->title("Jadwal {$this->getNamaHariAktif()}, {$tanggalFormatted} berhasil disimpan")
            ->success()->send();
    }

    /**
     * Validasi + simpan $this->rows ke database (replace-all per tanggal + scope filter).
     * Dipakai oleh saveJadwal() (tombol Simpan) maupun muatDariJadwalMingguan() (auto-save).
     */
    private function persistJadwal(): bool
    {
        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId) {
            Notification::make()->title('Rumah sakit tidak teridentifikasi')->danger()->send();
            return false;
        }

        foreach ($this->rows as $i => $row) {
            if (empty($row['poliklinik_id'])) {
                Notification::make()
                    ->title("Baris ke-" . ($i + 1) . " belum lengkap")
                    ->body('Pilih poliklinik atau hapus baris tersebut sebelum menyimpan.')
                    ->warning()->send();
                return false;
            }
        }

        foreach ($this->rows as $i => $row) {
            if (empty($row['jam_mulai'])) {
                Notification::make()
                    ->title("Baris ke-" . ($i + 1) . ": Jam Mulai wajib diisi")
                    ->danger()->send();
                return false;
            }
            if (empty($row['status_layanan'])) {
                Notification::make()
                    ->title("Baris ke-" . ($i + 1) . ": Status Layanan wajib diisi")
                    ->danger()->send();
                return false;
            }
        }

        $poliIds = PoliKlinik::where('rumah_sakit_id', $rsId)
            ->pluck('id')
            ->toArray();

        // Snapshot kondisi & perubahan EXISTING sebelum dihapus — dipakai sebagai
        // acuan "nilai asli" tanpa perlu menengok lagi ke JadwalPraktek.
        $existingIds = collect($this->rows)->pluck('id')->filter()->all();
        $existingJadwal = JadwalHarian::with('perubahan')
            ->whereIn('id', $existingIds)
            ->get()
            ->keyBy('id');

        DB::transaction(function () use ($poliIds, $existingJadwal) {
            JadwalHarian::whereDate('tanggal', $this->activeTanggal)
                ->whereIn('poliklinik_id', $poliIds)
                ->when($this->executiveClinicFilter === 'reguler',   fn ($q) => $q->where('is_executive', 0))
                ->when($this->executiveClinicFilter === 'eksekutif', fn ($q) => $q->where('is_executive', 1))
                ->delete();

            $userId = Auth::id();

            foreach ($this->rows as $row) {
                $sumber = $row['sumber'] ?? 'MANUAL';

                $old = $row['id'] ? $existingJadwal->get($row['id']) : null;
                $existingPerubahan = $old?->perubahan;

                // "Balik ke semula" hanya relevan untuk baris yang berasal dari
                // GENERATE — baris MANUAL tidak punya acuan "asli" untuk dibandingkan.
                $kembaliKeSemula = false;
                $nilaiAsli = null;

                if ($old && $old->sumber === 'GENERATE') {
                    // Baris sudah pernah tersimpan sebagai GENERATE. Acuan asli:
                    // kalau sudah pernah berubah, pakai nilai yang sudah terkunci di
                    // record perubahan (tidak boleh ditimpa lagi); kalau belum pernah
                    // berubah, acuan asli = kondisi $old saat ini (sebelum edit ini).
                    $nilaiAsli = $existingPerubahan
                        ? [
                            'jam_mulai'      => $existingPerubahan->jam_mulai_asli?->format('H:i'),
                            'jam_selesai'    => $existingPerubahan->jam_selesai_asli?->format('H:i'),
                            'status_layanan' => $existingPerubahan->status_layanan_asli,
                          ]
                        : [
                            'jam_mulai'      => $old->jam_mulai?->format('H:i'),
                            'jam_selesai'    => $old->jam_selesai?->format('H:i'),
                            'status_layanan' => $old->status_layanan->value,
                          ];
                } elseif (! $old && $sumber === 'GENERATE') {
                    // Baris baru hasil "muat dari jadwal mingguan" — belum pernah
                    // tersimpan, sehingga belum punya snapshot DB. Acuan aslinya:
                    // jam saat ini (asumsi belum diubah) dengan status BUKA, karena
                    // baris GENERATE selalu dimuat dalam kondisi BUKA.
                    $nilaiAsli = [
                        'jam_mulai'      => $row['jam_mulai'],
                        'jam_selesai'    => $row['jam_selesai'] ?: null,
                        'status_layanan' => 'BUKA',
                    ];
                }

                if ($nilaiAsli) {
                    $kembaliKeSemula =
                        $nilaiAsli['jam_mulai'] === $row['jam_mulai']
                        && $nilaiAsli['jam_selesai'] === ($row['jam_selesai'] ?: null)
                        && $nilaiAsli['status_layanan'] === $row['status_layanan'];

                    if ($kembaliKeSemula) {
                        $sumber = 'GENERATE';
                    }
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
                    'is_executive'   => (bool) ($row['is_executive'] ?? false),
                    'sumber'         => $sumber,
                ]);

                // Catat ke JadwalHarianPerubahan hanya jika ada acuan asli, baris ini
                // menyimpang darinya (belum "balik ke semula"), dan statusnya berubah.
                if ($nilaiAsli && ! $kembaliKeSemula && $row['status_layanan'] !== 'BUKA') {
                    JadwalHarianPerubahan::create([
                        'jadwal_harian_id'    => $jh->getKey(),
                        'user_id'             => $userId,
                        'jam_mulai'           => $row['jam_mulai'] ?: null,
                        'jam_selesai'         => $row['jam_selesai'] ?: null,
                        'status_layanan'      => $row['status_layanan'],
                        'jam_mulai_asli'      => $nilaiAsli['jam_mulai'],
                        'jam_selesai_asli'    => $nilaiAsli['jam_selesai'],
                        'status_layanan_asli' => $nilaiAsli['status_layanan'],
                        'catatan'             => $row['catatan'] ?: null,
                    ]);
                }
            }
        });

        $this->rowsCache[$this->activeTanggal] = null;
        $this->loadRows();

        return true;
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
              ->whereHas('poliklinik', function ($q2) use ($rsId) {
                  $q2->where('rumah_sakit_id', $rsId);
              })
              ->when($this->executiveClinicFilter === 'reguler',   fn ($q2) => $q2->where('is_executive', 0))
              ->when($this->executiveClinicFilter === 'eksekutif', fn ($q2) => $q2->where('is_executive', 1));
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
