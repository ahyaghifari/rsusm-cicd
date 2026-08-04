<?php

namespace App\Filament\Pages;

use App\Filament\Resources\BaseResource;
use App\Models\Dokter;
use App\Models\JadwalHarian;
use App\Models\PoliKlinik;
use App\Models\RumahSakit;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Input jadwal untuk poliklinik yang tidak punya pola mingguan tetap
 * (mis. poli umum yang jadwal dokternya beda tiap bulan). Beda dari
 * Jadwal Harian (yang terkunci ke 1 tanggal per layar), di sini tiap
 * baris punya tanggalnya sendiri — jadi bisa input banyak tanggal
 * berbeda dalam satu kali simpan.
 *
 * Poliklinik + bulan/tahun dipilih sekali di level halaman (bukan per
 * baris) — supaya bisa dipakai buat menampilkan jadwal yang sudah
 * tersimpan untuk poliklinik & periode tsb, bukan cuma form input buta.
 *
 * Semua baris di sini otomatis dianggap "executive" (prioritas) —
 * tidak ada toggle manual, langsung di-set true saat simpan.
 *
 * Data mendarat langsung ke tabel `jadwal_harian` yang sama — supaya
 * poster, halaman publik, dan fitur lain yang sudah baca dari situ
 * otomatis ikut menampilkan tanpa perubahan tambahan. Manajemen lanjut
 * (edit/hapus per baris) tetap lewat resource Jadwal Harian yang sudah ada.
 */
class JadwalPrioritasPage extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Jadwal Tidak Tetap';
    protected static ?string $title           = 'Input Jadwal Tidak Tetap';
    protected static ?string $navigationGroup = 'Poliklinik / Rawat Jalan';
    protected static ?int    $navigationSort  = 4;
    protected static string  $view            = 'filament.pages.jadwal-prioritas-page';

    protected ?string $maxContentWidth = 'full';

    public ?int $selectedRumahSakitId = null;
    public ?int $selectedPoliklinikId = null;
    public ?int $selectedBulan = null;
    public ?int $selectedTahun = null;

    public array $rows = [];

    /** @var array<string, array<int, array{tanggal:string, jam:string}>> keyed by row uuid */
    public array $dokterJadwalPreview = [];

    public function mount(): void
    {
        if (! BaseResource::isSuperAdmin()) {
            $this->selectedRumahSakitId = BaseResource::rumahSakitId();
        }

        $this->selectedBulan = (int) now()->format('n');
        $this->selectedTahun = (int) now()->format('Y');

        $this->addRow();
    }

    public static function canAccess(): bool
    {
        // Reuse permission Jadwal Harian yang sudah ada — halaman ini
        // secara konsep adalah cara lain untuk mengisi tabel yang sama.
        return (bool) auth()->user()?->can('update_jadwal::harian');
    }

    // ── Auth helpers ──────────────────────────────────────────────────────────

    private function isSuperAdmin(): bool
    {
        return BaseResource::isSuperAdmin();
    }

    public function getActiveRumahSakitId(): ?int
    {
        return $this->isSuperAdmin()
            ? $this->selectedRumahSakitId
            : BaseResource::rumahSakitId();
    }

    // ── Filter form (pilih RS + poliklinik + periode) ───────────────────────

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
                    ->required(fn () => $this->isSuperAdmin())
                    ->visible(fn () => $this->isSuperAdmin())
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function () {
                        $this->selectedPoliklinikId = null;
                        $this->resetRows();
                    }),

                Forms\Components\Select::make('selectedPoliklinikId')
                    ->label('Poliklinik')
                    ->placeholder('— Pilih Poliklinik —')
                    ->options(fn () => $this->getPoliklinikOptions())
                    ->required()
                    ->disabled(fn () => ! $this->getActiveRumahSakitId())
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetRows()),

                Forms\Components\Select::make('selectedBulan')
                    ->label('Bulan')
                    ->placeholder('— Pilih Bulan —')
                    ->options(collect(range(1, 12))->mapWithKeys(
                        fn ($m) => [$m => Carbon::create()->month($m)->translatedFormat('F')]
                    ))
                    ->required()
                    ->visible(fn () => (bool) $this->selectedPoliklinikId)
                    ->live(),

                Forms\Components\Select::make('selectedTahun')
                    ->label('Tahun')
                    ->placeholder('— Pilih Tahun —')
                    ->options(collect(range((int) now()->format('Y') - 1, (int) now()->format('Y') + 1))
                        ->mapWithKeys(fn ($y) => [$y => (string) $y]))
                    ->required()
                    ->visible(fn () => (bool) $this->selectedPoliklinikId)
                    ->live(),
            ])
            ->statePath('')
            ->columns(4);
    }

    // ── Options ──────────────────────────────────────────────────────────────

    public function getPoliklinikOptions(): array
    {
        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId) return [];

        // Hanya poliklinik yang ditandai "Jadwal Tidak Tetap" — supaya scope-nya
        // benar-benar terpisah dari Jadwal Praktek/Jadwal Harian biasa.
        return PoliKlinik::where('rumah_sakit_id', $rsId)
            ->where('aktif', true)
            ->where('jadwal_tidak_tetap', true)
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->toArray();
    }

    public function getDokterOptions(): array
    {
        if ($this->selectedPoliklinikId) {
            return PoliKlinik::find($this->selectedPoliklinikId)?->dokter()
                ->where('aktif', true)
                ->orderBy('nama')
                ->pluck('nama', 'id')
                ->toArray() ?? [];
        }

        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId) return [];

        return Dokter::where('rumah_sakit_id', $rsId)
            ->where('aktif', true)
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->toArray();
    }

    // ── Jadwal yang sudah tersimpan — poliklinik + periode aktif ────────────

    public function getExistingJadwal(): Collection
    {
        if (! $this->selectedPoliklinikId || ! $this->selectedBulan || ! $this->selectedTahun) {
            return collect();
        }

        return JadwalHarian::where('poliklinik_id', $this->selectedPoliklinikId)
            ->whereYear('tanggal', $this->selectedTahun)
            ->whereMonth('tanggal', $this->selectedBulan)
            ->with('dokter')
            ->orderBy('tanggal')
            ->get();
    }

    // ── Row management ───────────────────────────────────────────────────────

    public function addRow(): void
    {
        $this->rows[(string) Str::uuid()] = [
            'dokter_id'   => null,
            'nama_dokter' => null,
            'tanggal'     => null,
            'jam_mulai'   => null,
            'jam_selesai' => null,
        ];
    }

    public function removeRow(string $key): void
    {
        unset($this->rows[$key]);
        unset($this->dokterJadwalPreview[$key]);
    }

    private function resetRows(): void
    {
        $this->rows = [];
        $this->dokterJadwalPreview = [];
        $this->addRow();
    }

    public function updatedRows(mixed $value, string $key): void
    {
        if (! str_ends_with($key, '.dokter_id')) return;

        $uuidKey  = explode('.', $key)[0];
        $dokterId = $value ? (int) $value : null;

        $this->rows[$uuidKey]['nama_dokter'] = $dokterId ? Dokter::find($dokterId)?->nama : null;
        $this->loadDokterJadwalPreview($uuidKey, $dokterId);
    }

    /** Tampilkan jadwal harian dokter yang dipilih (bulan/tahun aktif) — biar kelihatan bentrok atau tidak. */
    private function loadDokterJadwalPreview(string $rowKey, ?int $dokterId): void
    {
        if (! $dokterId || ! $this->selectedBulan || ! $this->selectedTahun) {
            unset($this->dokterJadwalPreview[$rowKey]);
            return;
        }

        $this->dokterJadwalPreview[$rowKey] = JadwalHarian::where('dokter_id', $dokterId)
            ->whereYear('tanggal', $this->selectedTahun)
            ->whereMonth('tanggal', $this->selectedBulan)
            ->with('poliklinik')
            ->orderBy('tanggal')
            ->get()
            ->map(fn (JadwalHarian $j) => [
                'tanggal' => $j->tanggal->translatedFormat('d M Y'),
                'poliklinik' => $j->poliklinik?->nama ?? '-',
                'jam' => $j->jam_mulai?->format('H:i') . '–' . ($j->jam_selesai?->format('H:i') ?? 'selesai'),
            ])
            ->toArray();
    }

    // ── Save ─────────────────────────────────────────────────────────────────

    public function simpan(): void
    {
        abort_unless(auth()->user()?->can('update_jadwal::harian'), 403);

        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId) {
            Notification::make()->title('Pilih rumah sakit terlebih dahulu')->warning()->send();
            return;
        }

        if (! $this->selectedPoliklinikId) {
            Notification::make()->title('Pilih poliklinik terlebih dahulu')->warning()->send();
            return;
        }

        if (empty($this->rows)) {
            Notification::make()->title('Belum ada baris untuk disimpan')->warning()->send();
            return;
        }

        foreach (array_values($this->rows) as $i => $row) {
            if (empty($row['tanggal'])) {
                Notification::make()
                    ->title('Baris ke-' . ($i + 1) . ' belum lengkap')
                    ->body('Tanggal wajib diisi.')
                    ->warning()->send();
                return;
            }
            if (empty($row['jam_mulai'])) {
                Notification::make()
                    ->title('Baris ke-' . ($i + 1) . ': Jam Mulai wajib diisi')
                    ->danger()->send();
                return;
            }
        }

        $saved = 0;

        foreach ($this->rows as $row) {
            // Upsert: kalau dokter yang sama di poliklinik & tanggal yang sama
            // sudah ada, update jamnya — bukan bikin baris duplikat. Match pakai
            // nama_dokter kalau dokter_id kosong (free text), supaya beberapa
            // dokter tanpa akun master di tanggal+poliklinik sama tidak saling timpa.
            $matchKeys = [
                'poliklinik_id' => $this->selectedPoliklinikId,
                'tanggal'       => $row['tanggal'],
            ];
            $matchKeys += $row['dokter_id']
                ? ['dokter_id' => $row['dokter_id']]
                : ['nama_dokter' => $row['nama_dokter'] ?: null];

            JadwalHarian::updateOrCreate(
                $matchKeys,
                [
                    'nama_dokter'       => $row['nama_dokter'] ?: null,
                    'jam_mulai'         => $row['jam_mulai'],
                    'jam_selesai'       => $row['jam_selesai'] ?: null,
                    'status_layanan'    => 'BUKA',
                    'is_executive'      => true,
                    'sesuai_perjanjian' => false,
                    'sumber'            => 'MANUAL',
                ]
            );
            $saved++;
        }

        $this->resetRows();

        Notification::make()
            ->title("{$saved} jadwal berhasil disimpan")
            ->body('Kelola/ubah lagi lewat halaman Jadwal Harian.')
            ->success()->send();
    }
}