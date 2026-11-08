<?php

namespace App\Filament\Resources\CutiDokterResource\Pages;

use App\Filament\Resources\CutiDokterResource;
use App\Models\CutiDokter;
use App\Models\Dokter;
use App\Models\RumahSakit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class CutiDokterPage extends Page
{
    protected static string $resource = CutiDokterResource::class;

    protected static string $view = 'filament.resources.cuti-dokter-resource.pages.cuti-dokter-page';

    protected static ?string $title = 'Cuti / Libur Dokter';

    protected ?string $maxContentWidth = 'full';

    public ?int $selectedRumahSakitId = null;
    public ?int $selectedDokterId = null;

    // Disimpan di server (bukan Alpine lokal) supaya tidak reset tiap re-render, sama seperti Jadwal Harian.
    public bool $isFullscreen = false;

    // Form cuti
    public ?int $editingId = null;
    public string $mode = 'satu_hari'; // satu_hari | rentang | per_tanggal
    public ?string $tanggalSatuHari = null;
    public ?string $tanggalMulai = null;
    public ?string $tanggalSelesai = null;
    /** @var array<int, string|null> */
    public array $tanggalList = [null];
    public string $alasan = 'Cuti';

    public function mount(): void
    {
        if (! CutiDokterResource::isSuperAdmin()) {
            $this->selectedRumahSakitId = CutiDokterResource::rumahSakitId();
        }
    }

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
                    ->required(fn () => CutiDokterResource::isSuperAdmin())
                    ->visible(fn () => CutiDokterResource::isSuperAdmin())
                    ->live(),
            ])
            ->statePath('');
    }

    public function updatedSelectedRumahSakitId(): void
    {
        $this->selectedDokterId = null;
        $this->resetForm();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function canEditCuti(): bool
    {
        return (bool) auth()->user()?->can('create_cuti::dokter');
    }

    /** @return \Illuminate\Support\Collection<int, Dokter> dengan atribut cuti_mendatang */
    public function getDokterList()
    {
        if (! $this->selectedRumahSakitId) return collect();

        $mendatang = CutiDokter::where('rumah_sakit_id', $this->selectedRumahSakitId)
            ->whereDate('tanggal_selesai', '>=', today())
            ->selectRaw('dokter_id, count(*) as total')
            ->groupBy('dokter_id')
            ->pluck('total', 'dokter_id');

        return Dokter::where('rumah_sakit_id', $this->selectedRumahSakitId)
            ->where('aktif', true)
            ->orderBy('nama')
            ->get(['id', 'nama'])
            ->each(fn ($d) => $d->cuti_mendatang = $mendatang[$d->id] ?? 0);
    }

    /** Semua cuti mendatang (hari ini dst, tanpa batas akhir) di RS terpilih, lintas dokter. */
    public function getCutiMendatang()
    {
        if (! $this->selectedRumahSakitId) return collect();

        return CutiDokter::with('dokter:id,nama')
            ->where('rumah_sakit_id', $this->selectedRumahSakitId)
            ->whereDate('tanggal_selesai', '>=', today())
            ->orderBy('tanggal_mulai')
            ->get();
    }

    public function getSelectedDokter(): ?Dokter
    {
        return $this->selectedDokterId
            ? Dokter::where('rumah_sakit_id', $this->selectedRumahSakitId)->find($this->selectedDokterId)
            : null;
    }

    public function getCutiList()
    {
        if (! $this->getSelectedDokter()) return collect();

        return CutiDokter::where('dokter_id', $this->selectedDokterId)
            ->orderByDesc('tanggal_mulai')
            ->get();
    }

    // ── Aksi ─────────────────────────────────────────────────────────────────

    public function pilihDokter(int $id): void
    {
        $this->selectedDokterId = $id;
        $this->resetForm();
    }

    public function hapusPilihan(): void
    {
        $this->selectedDokterId = null;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editingId       = null;
        $this->mode            = 'satu_hari';
        $this->tanggalSatuHari = null;
        $this->tanggalMulai    = null;
        $this->tanggalSelesai  = null;
        $this->tanggalList     = [null];
        $this->alasan          = 'Cuti';
        $this->resetValidation();
    }

    public function editCuti(int $id): void
    {
        $cuti = $this->findCuti($id);

        $this->editingId = $cuti->id;
        $this->alasan     = $cuti->alasan;

        if ($cuti->tanggal_mulai->equalTo($cuti->tanggal_selesai)) {
            $this->mode            = 'satu_hari';
            $this->tanggalSatuHari = $cuti->tanggal_mulai->toDateString();
        } else {
            $this->mode            = 'rentang';
            $this->tanggalMulai    = $cuti->tanggal_mulai->toDateString();
            $this->tanggalSelesai  = $cuti->tanggal_selesai->toDateString();
        }

        $this->dispatch('open-modal', id: 'cuti-form-modal');
    }

    public function bukaFormTambah(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', id: 'cuti-form-modal');
    }

    public function tambahTanggal(): void
    {
        $this->tanggalList[] = null;
    }

    public function hapusTanggal(int $index): void
    {
        unset($this->tanggalList[$index]);
        $this->tanggalList = array_values($this->tanggalList) ?: [null];
    }

    public function simpanCuti(): void
    {
        abort_unless($this->canEditCuti(), 403);
        abort_unless($this->getSelectedDokter(), 422);

        $pesanTanggal = [
            'required' => 'Tanggal wajib diisi.',
            'date'     => 'Tanggal tidak valid.',
        ];

        $rentang = match ($this->mode) {
            'satu_hari' => $this->validate([
                'tanggalSatuHari' => ['required', 'date'],
                'alasan'          => ['required', 'string', 'max:255'],
            ], ['tanggalSatuHari.required' => $pesanTanggal['required']])
                ? [[$this->tanggalSatuHari, $this->tanggalSatuHari]]
                : [],

            'rentang' => $this->validate([
                'tanggalMulai'   => ['required', 'date'],
                'tanggalSelesai' => ['required', 'date', 'after_or_equal:tanggalMulai'],
                'alasan'         => ['required', 'string', 'max:255'],
            ], [
                'tanggalMulai.required'         => $pesanTanggal['required'],
                'tanggalSelesai.required'       => 'Tanggal sampai wajib diisi.',
                'tanggalSelesai.after_or_equal' => 'Tanggal sampai tidak boleh sebelum tanggal mulai.',
            ])
                ? [[$this->tanggalMulai, $this->tanggalSelesai]]
                : [],

            'per_tanggal' => (function () use ($pesanTanggal) {
                $this->validate([
                    'tanggalList'   => ['required', 'array', 'min:1'],
                    'tanggalList.*' => ['required', 'date'],
                    'alasan'        => ['required', 'string', 'max:255'],
                ], ['tanggalList.*.required' => $pesanTanggal['required']]);

                return collect($this->tanggalList)->unique()->sort()
                    ->map(fn ($tgl) => [$tgl, $tgl])->values()->all();
            })(),
        };

        if ($this->editingId) {
            [$mulai, $selesai] = $rentang[0];
            $this->findCuti($this->editingId)->update([
                'tanggal_mulai'   => $mulai,
                'tanggal_selesai' => $selesai,
                'alasan'          => $this->alasan,
            ]);
        } else {
            foreach ($rentang as [$mulai, $selesai]) {
                CutiDokter::create([
                    'tanggal_mulai'   => $mulai,
                    'tanggal_selesai' => $selesai,
                    'alasan'          => $this->alasan,
                    'dokter_id'       => $this->selectedDokterId,
                ]);
            }
        }

        Notification::make()
            ->title('Cuti disimpan')
            ->body('Jadwal harian pada tanggal tersebut sudah diisi Libur.')
            ->success()->send();

        $this->resetForm();
        $this->dispatch('close-modal', id: 'cuti-form-modal');
    }

    public function hapusCuti(int $id): void
    {
        abort_unless(auth()->user()?->can('delete_cuti::dokter'), 403);

        $this->findCuti($id)->delete();

        if ($this->editingId === $id) $this->resetForm();

        Notification::make()
            ->title('Cuti dihapus')
            ->body('Jadwal harian pada tanggal tersebut dikembalikan ke Buka.')
            ->success()->send();
    }

    /** Cuti hanya boleh milik dokter terpilih di RS aktif (cegah id dari RS lain). */
    private function findCuti(int $id): CutiDokter
    {
        return CutiDokter::where('rumah_sakit_id', $this->selectedRumahSakitId)
            ->where('dokter_id', $this->selectedDokterId)
            ->findOrFail($id);
    }
}
