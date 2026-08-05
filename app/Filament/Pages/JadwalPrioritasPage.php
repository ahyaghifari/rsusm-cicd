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
 * baris punya poliklinik & tanggalnya sendiri — jadi bisa input banyak
 * poliklinik/tanggal berbeda dalam satu kali simpan.
 *
 * Rumah Sakit + Bulan/Tahun dipilih sekali di level halaman — dipakai
 * buat menampilkan jadwal yang sudah tersimpan pada periode itu, bukan
 * cuma form input buta.
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

    /** Format 'Y-m' — dari input HTML native <input type="month">. */
    public ?string $periode = null;

    public array $rows = [];

    public function mount(): void
    {
        if (! BaseResource::isSuperAdmin()) {
            $this->selectedRumahSakitId = BaseResource::rumahSakitId();
        }

        $this->periode = now()->format('Y-m');

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

    /** [bulan, tahun] dari properti $periode ('Y-m'). Null-null kalau belum diisi/invalid. */
    private function bulanTahun(): array
    {
        if (! $this->periode) return [null, null];

        try {
            $tgl = Carbon::createFromFormat('Y-m', $this->periode);
        } catch (\Exception) {
            return [null, null];
        }

        return [(int) $tgl->format('n'), (int) $tgl->format('Y')];
    }

    // ── Filter form (pilih RS) ──────────────────────────────────────────────

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
                    ->afterStateUpdated(fn () => $this->resetRows()),
            ])
            ->statePath('');
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
        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId) return [];

        return Dokter::where('rumah_sakit_id', $rsId)
            ->where('aktif', true)
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->toArray();
    }

    // ── Jadwal yang sudah tersimpan — RS + periode aktif ─────────────────────

    public function getExistingJadwal(): Collection
    {
        $rsId = $this->getActiveRumahSakitId();
        [$bulan, $tahun] = $this->bulanTahun();
        if (! $rsId || ! $bulan || ! $tahun) {
            return collect();
        }

        return JadwalHarian::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->whereHas('poliklinik', fn ($q) => $q->where('rumah_sakit_id', $rsId)->where('jadwal_tidak_tetap', true))
            ->with(['poliklinik', 'dokter'])
            ->orderBy('tanggal')
            ->get();
    }

    // ── Row management ───────────────────────────────────────────────────────

    public function addRow(): void
    {
        $this->rows[(string) Str::uuid()] = [
            'poliklinik_id' => null,
            'dokter_id'     => null,
            'nama_dokter'   => null,
            'tanggal'       => null,
            'jam_mulai'     => null,
            'jam_selesai'   => null,
        ];
    }

    /** Muat 1 jadwal tersimpan ke baris input supaya bisa diubah lalu disimpan ulang (upsert). */
    public function editExisting(int $id): void
    {
        abort_unless(auth()->user()?->can('update_jadwal::harian'), 403);

        $jadwal = JadwalHarian::find($id);
        if (! $jadwal) return;

        $this->rows[(string) Str::uuid()] = [
            'poliklinik_id' => $jadwal->poliklinik_id,
            'dokter_id'     => $jadwal->dokter_id,
            'nama_dokter'   => $jadwal->nama_dokter,
            'tanggal'       => $jadwal->tanggal->format('Y-m-d'),
            'jam_mulai'     => $jadwal->jam_mulai?->format('H:i'),
            'jam_selesai'   => $jadwal->jam_selesai?->format('H:i'),
        ];

        Notification::make()
            ->title('Jadwal dimuat ke form input')
            ->body('Ubah lalu klik "Simpan Semua" untuk menyimpan perubahan.')
            ->info()->send();
    }

    public function hapusExisting(int $id): void
    {
        abort_unless(auth()->user()?->can('update_jadwal::harian'), 403);

        JadwalHarian::whereKey($id)->delete();

        Notification::make()->title('Jadwal dihapus')->success()->send();
    }

    public function removeRow(string $key): void
    {
        unset($this->rows[$key]);
    }

    private function resetRows(): void
    {
        $this->rows = [];
        $this->addRow();
    }

    public function updatedRows(mixed $value, string $key): void
    {
        if (! str_ends_with($key, '.dokter_id')) return;

        $uuidKey  = explode('.', $key)[0];
        $dokterId = $value ? (int) $value : null;

        $this->rows[$uuidKey]['nama_dokter'] = $dokterId ? Dokter::find($dokterId)?->nama : null;
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

        if (empty($this->rows)) {
            Notification::make()->title('Belum ada baris untuk disimpan')->warning()->send();
            return;
        }

        foreach (array_values($this->rows) as $i => $row) {
            if (empty($row['poliklinik_id']) || empty($row['tanggal'])) {
                Notification::make()
                    ->title('Baris ke-' . ($i + 1) . ' belum lengkap')
                    ->body('Poliklinik dan tanggal wajib diisi.')
                    ->warning()->send();
                return;
            }
            if (empty($row['dokter_id'])) {
                Notification::make()
                    ->title('Baris ke-' . ($i + 1) . ': Dokter wajib dipilih')
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
            // sudah ada, update jamnya — bukan bikin baris duplikat.
            $matchKeys = [
                'poliklinik_id' => $row['poliklinik_id'],
                'tanggal'       => $row['tanggal'],
                'dokter_id'     => $row['dokter_id'],
            ];

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